<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

require_once 'CRM/Core/Form.php';
require_once 'CRM/Campaign/DAO/Campaign.php';

Class CRM_Campaign_BAO_Campaign extends CRM_Campaign_DAO_Campaign
{
    /**
     * takes an associative array and creates a campaign object
     *
     * the function extract all the params it needs to initialize the create a
     * contact object. the params array could contain additional unused name/value
     * pairs
     *
     * @param array  $params (reference ) an assoc array of name/value pairs
     *
     * @return object CRM_Campaign_DAO_Campaign object
     * @access public
     * @static
     */
    static function create( &$params ) 
    {
        if ( empty( $params ) ) {
            return;
        } 
        
        if ( !(CRM_Utils_Array::value('id', $params)) )  {
            
            if ( !(CRM_Utils_Array::value('created_id', $params)) ) {
                $session = CRM_Core_Session::singleton( );
                $params['created_id'] = $session->get( 'userID' );
            }
            
            if ( !(CRM_Utils_Array::value('created_date', $params)) ) {
                $params['created_date'] = date('YmdHis');
            }
            
            if ( !(CRM_Utils_Array::value('name', $params)) ) {
                $params['name'] =  CRM_Utils_String::titleToVar($params['title'], 64 );
            }
        }
        
        $campaign = new CRM_Campaign_DAO_Campaign();
        $campaign->copyValues( $params );
        $campaign->save();
       
        /* Create the campaign group record */
        $groupTableName   = CRM_Contact_BAO_Group::getTableName( );
        require_once 'CRM/Campaign/DAO/CampaignGroup.php';
        $dao = new CRM_Campaign_DAO_CampaignGroup();
       
        if( CRM_Utils_Array::value( 'include', $params['groups'] ) && is_array( $params['groups']['include'] ) ) {                    
             foreach( $params['groups']['include'] as $entityId ) {
                        $dao->reset( );
                        $dao->campaign_id  = $campaign->id;
                        $dao->entity_table = $groupTableName;
                        $dao->entity_id    = $entityId;
                        $dao->group_type   = 'include';
                        $dao->save( );
                    }
        }
              
        return $campaign;
    }
   
    /**
     * function to delete the campaign
     *
     * @param  int $id id of the campaign
     */
    public static function del( $id )
    {
        if ( !$id ) {
            return false;
        }
        $dao     = new CRM_Campaign_DAO_Campaign( );
        $dao->id = $id;
        return $dao->delete( );
    }

    /**
     * Takes a bunch of params that are needed to match certain criteria and
     * retrieves the relevant objects. Typically the valid params are only
     * campaign_id. 
     *
     * @param array  $params   (reference ) an assoc array of name/value pairs
     * @param array  $defaults (reference ) an assoc array to hold the flattened values
     *
     * @access public
     */
    public function retrieve ( &$params, &$defaults ) 
    {
        $campaign = new CRM_Campaign_DAO_Campaign( );
        
        $campaign->copyValues($params);
        
        if( $campaign->find( true ) ) {
            CRM_Core_DAO::storeValues( $campaign, $defaults );
            return $campaign;
        }
        return null;  
    }

    public function getAllCampaign( $id=null ) 
    {
        $campaigns = array( );
        $whereClause = null;
        if ( $id ) {
            $whereClause = " AND c.id != ".$id;
        }
        $campaignParent = array();
        $sql = "
SELECT c.id as id, c.title as title
FROM  civicrm_campaign c
WHERE c.title IS NOT NULL" . $whereClause;
        
        $dao =& CRM_Core_DAO::executeQuery( $sql );
        while ( $dao->fetch() ) {
           $campaigns[$dao->id] = $dao->title;
           
        }
        
        return  $campaigns ;

    }

     /**
     * Function to get Campaigns 
     *
     * @param $all boolean true if campaign is active else returns camapign 
     *
     * @static
     */
    static function getCampaign( $all = false, $id = false) 
    {
       $campaign = array( );
       $dao = new CRM_Campaign_DAO_Campaign( );
       if ( !$all ) {
           $dao->is_active = 1;
       }
       
       if ( $id ) {
           $dao->id = $id;  
       }
       $dao->find( );
       while ( $dao->fetch() ) {
           CRM_Core_DAO::storeValues($dao, $campaign[$dao->id]);
       }
       
       return $campaign;
    }
    
    
    /**
     * Function to get Campaigns groups
     *
     * @param int $campaignId campaign id 
     *
     * @static
     */
    static function getCampaignGroups( $campaignId ) 
    {
        $campaignGroups = array( ); 
        if ( !$campaignId ) return $campaignGroups; 
        
        require_once 'CRM/Campaign/DAO/CampaignGroup.php';
        $campGrp = new CRM_Campaign_DAO_CampaignGroup( );
        $campGrp->campaign_id = $campaignId;
        $campGrp->group_type  = 'Include'; 
        $campGrp->find( );
        while ( $campGrp->fetch() ) {
            CRM_Core_DAO::storeValues( $campGrp, $campaignGroups[$campGrp->id] );
        }
        
        return $campaignGroups;
    }
    

    /**
     * update the is_active flag in the db
     *
     * @param int      $id        id of the database record
     * @param boolean  $is_active value we want to set the is_active field
     *
     * @return Object             DAO object on sucess, null otherwise
     * @static
     */ 
    static function setIsActive( $id, $is_active ) 
    {  
        return CRM_Core_DAO::setFieldValue( 'CRM_Campaign_DAO_Campaign', $id, 'is_active', $is_active );
    }
    
    static function accessCampaignDashboard( ) {
        $allow = false;
        if ( CRM_Core_Permission::check( 'manage campaign' ) ||
             CRM_Core_Permission::check( 'administer CiviCampaign' ) ) {
            $allow = true;
        }
        
        return $allow;
    }
}
