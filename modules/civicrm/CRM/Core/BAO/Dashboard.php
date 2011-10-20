<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

/**
 * Class contains Contact dashboard related functions
 */
require_once 'CRM/Core/DAO/Dashboard.php';

class CRM_Core_BAO_Dashboard extends CRM_Core_DAO_Dashboard
{
    
    /**
     * Function to get the list of ddashlets 
     * ( defaults dashlets defined by admin )
     *
     *  @param boolean $all all or only active  
     *
     * @return array $widgets  array of dashlets
     * @access public
     * @static
     */
    static function getDashlets( $all = true ) {
        $dashlets = array( );
        $dao = new CRM_Core_DAO_Dashboard( );
        
        if ( !$all ) {
            $dao->is_active = 1;
        }
        
        $dao->domain_id = CRM_Core_Config::domainID( );
        
        $dao->find( );
        while( $dao->fetch( ) ) {
            if ( !self::checkPermission( $dao->permission, $dao->permission_operator ) ) {
                continue;
            }
            
            $values = array( );
            CRM_Core_DAO::storeValues( $dao, $values );
            $dashlets[$dao->id] = $values;
        }
        
        return $dashlets;
    }
    
    /**
     * Function to get the list of dashlets for a contact
     * and if there are no dashlets for contact return default dashlets and update 
     * contact's preference entry
     *  
     * @param int $contactID contactID
     *
     * @return array $dashlets  array of dashlets
     * @access public
     * @static
     */
    static function getContactDashlets( $flatFormat = false ) {
        $dashlets = array( );
        
        $session   = CRM_Core_Session::singleton( );
        $contactID = $session->get('userID');
        
        // get contact dashboard dashlets
        $hasDashlets = false;
        require_once 'CRM/Contact/DAO/DashboardContact.php';
        $dao = new CRM_Contact_DAO_DashboardContact( );
        $dao->contact_id = $contactID;
        $dao->orderBy( 'column_no asc, weight asc' );
        $dao->find( );
        while( $dao->fetch( ) ) {
            if ( !$flatFormat ) {
                $hasDashlets = true;
                if ( !$dao->is_active ) {
                    continue;
                }
                // append weight so that order is preserved.
                $dashlets[$dao->column_no]["$dao->weight}-{$dao->dashboard_id}"] = $dao->is_minimized;
            } else {
                $dashlets[$dao->dashboard_id] = $dao->dashboard_id;
            }
        }
        
        if ( $flatFormat ) {
            return $dashlets;
        }
        
        // if empty then make entry in contact dashboard for this contact
        if ( empty( $dashlets ) && !$hasDashlets ) {
            $defaultDashlets = self::getDashlets( );
            
            //now you need make dashlet entries for logged in contact
            // need to optimize this sql
            foreach ( $defaultDashlets as $key => $values ) {
                $valuesArray[] = " ( {$key}, $contactID )";
            } 
            
            if ( !empty( $defaultDashlets ) ) {
                $valuesString = implode( ',', $valuesArray);
                $query = "
                    INSERT INTO civicrm_dashboard_contact ( dashboard_id, contact_id )
                    VALUES {$valuesString}";

                CRM_Core_DAO::executeQuery( $query );
            }            
        }
         
        return $dashlets;
    }
    
    /**
     * Function to check dashlet permission for current user
     *
     * @param string permission string
     * 
     * @return boolean true if use has permission else false
     */
    static function checkPermission( $permission, $operator ) {
        if ( $permission ) {
            $permissions = explode(',', $permission ); 
            $config  = CRM_Core_Config::singleton( );
            
            static $allComponents;
            if ( !$allComponents ) {
                $allComponents = CRM_Core_Component::getNames( );
            }
            
            $hasPermission = false;    
            foreach ( $permissions as $key ) {
                $showDashlet = true;
                
                $componentName = null;
                if ( strpos( $key, 'access' ) === 0 ) { 
                    $componentName = trim( substr( $key, 6 ) );
                    if ( !in_array( $componentName, $allComponents ) ) {
                        $componentName = null;
                    }
                }

                // hack to handle case permissions
                if ( !$componentName && in_array( $key, array( 'access my cases and activities', 'access all cases and activities' ) ) ) { 
                    $componentName = 'CiviCase';
                }
                                
                //hack to determine if it's a component related permission
                if ( $componentName ) {
                    if ( !in_array( $componentName, $config->enableComponents ) || 
                         !CRM_Core_Permission::check( $key ) ) {
                        $showDashlet = false;
                        if ( $operator == 'AND' ) {
                            return $showDashlet;
                        }
                    } else {
                        $hasPermission = true;
                    }
               } else if ( !CRM_Core_Permission::check( $key ) ) {
                     $showDashlet = false;
                     if ( $operator == 'AND' ) {
                         return $showDashlet;
                     }
                } else {
                    $hasPermission = true;
                }
            }
            
            if ( !$showDashlet && !$hasPermission ) {
                return false;
            } else {
                return true;
            }   
        } else {
            // if permission is not set consider everyone has permission to access it.
            return true;
        }        
    }
    
    /**
     * Function to get details of each dashlets
     *
     * @param int $dashletID widget ID
     *
     * @return array associted array title and content
     * @access public
     * @static  
     */
    static function getDashletInfo( $dashletID ) {
        $dashletInfo = array( );
        
        $params   = array( 1 => array( $dashletID, 'Integer' ) );
        $query = "SELECT label, url, fullscreen_url, is_fullscreen FROM civicrm_dashboard WHERE id = %1";
        $dashboadDAO = CRM_Core_DAO::executeQuery( $query, $params );
        $dashboadDAO->fetch( );

        // build the content
        require_once 'CRM/Contact/DAO/DashboardContact.php';
        $dao = new CRM_Contact_DAO_DashboardContact( );

        $session = CRM_Core_Session::singleton( ); 
        $dao->contact_id   = $session->get('userID');
        $dao->dashboard_id = $dashletID;
        $dao->find( true );

        //reset content based on the cache time set in config
        $currentDate = strtotime( date('Y-m-d h:i:s') );
        $createdDate = strtotime( $dao->created_date );
        $dateDiff    = round(abs($currentDate - $createdDate) / 60 );

        $config = CRM_Core_Config::singleton( );
        if ( $config->dashboardCacheTimeout <= $dateDiff ) {
            $dao->content = NULL;
        }

        // if content is empty and url is set, retrieve it from url
        if ( !$dao->content && $dashboadDAO->url ) {
            $url = $dashboadDAO->url;
            
            // CRM-7087 
            // -lets use relative url for internal use.
            // -make sure relative url should not be htmlize.
            if ( substr( $dashboadDAO->url, 0, 4 ) != 'http' ) {
                $urlParam = CRM_Utils_System::explode( '&', $dashboadDAO->url, 2 );
                $url = CRM_Utils_System::url( $urlParam[0], $urlParam[1], true, null, false );
            }
            
            //get content from url
            $dao->content = CRM_Utils_System::getServerResponse( $url );
            $dao->created_date = date( "YmdHis" );
            $dao->save( );
        }

        $dashletInfo = array( 'title'      => $dashboadDAO->label,
                              'content'    => $dao->content);

        if ( $dashboadDAO->is_fullscreen ) {                       
            $fullscreenUrl = $dashboadDAO->fullscreen_url;
            if ( substr( $fullscreenUrl, 0, 4 ) != 'http' ) {
                $urlParam = CRM_Utils_System::explode( '&', $dashboadDAO->fullscreen_url, 2 );
                $fullscreenUrl = CRM_Utils_System::url( $urlParam[0], $urlParam[1], true, null, false );
            }
            $dashletInfo['fullscreenUrl'] = $fullscreenUrl;
        }                     
        return $dashletInfo;
    }
     
    /**
     * Function to save changes made by use to the Dashlet
     *
     * @param array $columns associated array
     *
     * @return void
     * @access public
     * @static
     */
    static function saveDashletChanges( $columns ) {
        $session   = CRM_Core_Session::singleton( );
        $contactID = $session->get('userID');
        
        // $columns = array( 0 => array( 2 => 0 ),
        //                   1 => array( 1 => 0 )
        //                  );
        
        //we need to get existing dashletes, so we know when to update or insert
        $contactDashlets = CRM_Core_BAO_Dashboard::getContactDashlets( true );
        
        $dashletIDs = array( );
        if ( is_array( $columns ) ) {
            foreach ( $columns as $colNo => $dashlets ) {
                if( ! is_integer( $colNo ) ) {
                    continue;
                }
                $weight = 1;
                foreach ( $dashlets as $dashletID => $isMinimized ) {
                    $isMinimized = (int) $isMinimized;
                    if ( in_array( $dashletID, $contactDashlets ) ) {
                        $query = " UPDATE civicrm_dashboard_contact 
                                        SET weight = {$weight}, is_minimized = {$isMinimized}, column_no = {$colNo}, is_active = 1
                                      WHERE dashboard_id = {$dashletID} AND contact_id = {$contactID} ";                      
                    } else {
                        $query = " INSERT INTO civicrm_dashboard_contact 
                                        ( weight, is_minimized, column_no, is_active, dashboard_id, contact_id )
                                     VALUES( {$weight},  {$isMinimized},  {$colNo}, 1, {$dashletID}, {$contactID} )";
                        
                    }
                    // fire update query for each column
                    $dao = CRM_Core_DAO::executeQuery( $query );
                    
                    $dashletIDs[] = $dashletID;
                    $weight++;
                }              
            }
        }
        
        if ( !empty( $dashletIDs ) ) {
            // we need to disable widget that removed
            $updateQuery = " UPDATE civicrm_dashboard_contact 
                               SET is_active = 0
                               WHERE dashboard_id NOT IN  ( " . implode( ',', $dashletIDs ). ") AND contact_id = {$contactID}";
        } else {
            // this means all widgets are disabled
            $updateQuery = " UPDATE civicrm_dashboard_contact 
                               SET is_active = 0
                               WHERE contact_id = {$contactID}";
        }
        
        CRM_Core_DAO::executeQuery( $updateQuery );
    }
    
    /**
     * Function to add dashlets
     *  
     * @param array $params associated array
     * 
     * @return object $dashlet returns dashlet object
     * @access public
     * @static
     */
    static function addDashlet( &$params ) {
        
        // special case to handle duplicate entires for report instances
        $dashboardID = null;
        if ( CRM_Utils_Array::value( 'instanceURL', $params ) ) {
            $query = "SELECT id
                        FROM `civicrm_dashboard`
                        WHERE url LIKE '" . CRM_Utils_Array::value( 'instanceURL', $params ) ."&%'";
            $dashboardID = CRM_Core_DAO::singleValueQuery( $query );  
        }
        
        require_once "CRM/Core/DAO/Dashboard.php";
        $dashlet  = new CRM_Core_DAO_Dashboard( );
        
        if ( !$dashboardID ) {
            // check url is same as exiting entries, if yes just update existing
            $dashlet->url = CRM_Utils_Array::value( 'url', $params );
            $dashlet->find( true );
        } else {
            $dashlet->id = $dashboardID;
        }
        
        if ( is_array( $params['permission'] )) {           
            $params['permission'] = implode( ',', $params['permission'] );
        }
        $dashlet->copyValues( $params );
        
        $dashlet->domain_id = CRM_Core_Config::domainID( );
        
        $dashlet->save( );
        
        // now we need to make dashlet entries for each contact
        self::addContactDashlet( $dashlet );
        
        return $dashlet;
    }
    
    /**
     * Update contact dashboard with new dashlet
     *
     */
    static function addContactDashlet( &$dashlet ) {
        $admin = CRM_Core_Permission::check( 'administer CiviCRM' );
        
        // if dashlet is created by admin then you need to add it all contacts.
        // else just add to contact who is creating this dashlet
        $contactIDs = array( );
        if ( $admin ) {
            $query = "SELECT distinct( contact_id ) 
                        FROM civicrm_dashboard_contact 
                        WHERE contact_id NOT IN ( 
                            SELECT distinct( contact_id ) 
                            FROM civicrm_dashboard_contact WHERE dashboard_id = {$dashlet->id}
                            )";
            
            $dao = CRM_Core_DAO::executeQuery( $query );
            while( $dao->fetch( ) ) {
                $contactIDs[] = $dao->contact_id;
            }
        } else {
            //Get the id of Logged in User
            $session = CRM_Core_Session::singleton( );
            $contactIDs[]  = $session->get( 'userID' );
        }
        
        if ( !empty( $contactIDs ) ) {
            foreach ( $contactIDs as $contactID ) {
                $valuesArray[] = " ( {$dashlet->id}, {$contactID} )";
            }
            
            $valuesString = implode( ',', $valuesArray );
            $query = "
                  INSERT INTO civicrm_dashboard_contact ( dashboard_id, contact_id )
                  VALUES {$valuesString}";
            
            CRM_Core_DAO::executeQuery( $query );
        } 
    }
    
    /**
     * Function to reset dashlet cache
     *
     * @return void
     * @static 
     */
    static function resetDashletCache( ) {
        $query = "UPDATE civicrm_dashboard_contact SET content = NULL";
        $dao = CRM_Core_DAO::executeQuery( $query );
    }
    
    /**
     * Delete Dashlet
     *
     * @return void
     * @static
     */
    static function deleteDashlet( $dashletID ) {
        require_once "CRM/Core/DAO/Dashboard.php";
        $dashlet     = new CRM_Core_DAO_Dashboard( );
        $dashlet->id = $dashletID;
        $dashlet->delete( );
    }
}
