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

require_once 'CRM/Profile/Selector/Listings.php';
require_once 'CRM/Core/Selector/Controller.php';
require_once 'CRM/Core/Page.php';

/**
 * This implements the profile page for all contacts. It uses a selector
 * object to do the actual dispay. The fields displayd are controlled by
 * the admin
 */
class CRM_Profile_Page_Listings extends CRM_Core_Page {

    /**
     * all the fields that are listings related
     *
     * @var array
     * @access protected
     */
    protected $_fields;

    /** 
     * the custom fields for this domain
     * 
     * @var array 
     * @access protected 
     */ 
    protected $_customFields;

    /**
     * The input params from the request
     *
     * @var array 
     * @access protected 
     */ 
    protected $_params;

    /** 
     * The group id that we are editing
     * 
     * @var int 
     */ 
    protected $_gid; 

    /** 
     * state wether to display serch form or not
     * 
     * @var int 
     */ 
    protected $_search; 
    
    /**
     * Should we display a map
     *
     * @var int
     */
    protected $_map;

    /**
     * Store profile ids if multiple profile ids are passed using comma separated.
     * Currently lets implement this functionality only for dialog mode
     */
    protected $_profileIds = array( );

    /**
     * extracts the parameters from the request and constructs information for
     * the selector object to do a query
     *
     * @return void 
     * @access public 
     * 
     */ 
    function preProcess( ) {
        
        $this->_search = true;
        
        $search = CRM_Utils_Request::retrieve( 'search', 'Boolean',
                                               $this, false, 0, 'GET' );
        if ( isset( $search ) && $search == 0) {
            $this->_search = false;
        }

        $this->_gid        = $this->get( 'gid' );
        $this->_profileIds = $this->get( 'profileIds' );

        $gids = explode( ',', CRM_Utils_Request::retrieve('gid', 'String', CRM_Core_DAO::$_nullObject, false, 0, 'GET') );
 
        if ( ( count( $gids ) > 1 ) && !$this->_profileIds && empty( $this->_profileIds ) ) {
            if ( !empty( $gids ) ) {
                foreach( $gids as $pfId  ) {
                   $this->_profileIds[ ] = CRM_Utils_Type::escape( $pfId, 'Positive' ); 
                }
            }
            
            // check if we are rendering mixed profiles
            require_once 'CRM/Core/BAO/UFGroup.php';
            if ( CRM_Core_BAO_UFGroup::checkForMixProfiles( $this->_profileIds ) ) {
                CRM_Core_Error::fatal( ts( 'You cannot combine profiles of multiple types.' ) );
            } 

            $this->_gid = $this->_profileIds[0];
            $this->set( 'profileIds', $this->_profileIds );
            $this->set( 'gid', $this->_gid );
        }
        
        if ( !$this->_gid ) {
           $this->_gid = CRM_Utils_Request::retrieve('gid', 'Positive', $this, false, 0, 'GET');
        } 
        
        require_once 'CRM/Core/BAO/UFGroup.php';
        if ( empty( $this->_profileIds ) ) {
            $gids = $this->_gid;
        } else {
            $gids = $this->_profileIds; 
        }

        $this->_fields =
            CRM_Core_BAO_UFGroup::getListingFields( CRM_Core_Action::UPDATE,
                                                    CRM_Core_BAO_UFGroup::PUBLIC_VISIBILITY | CRM_Core_BAO_UFGroup::LISTINGS_VISIBILITY,
                                                    false, $gids, false, 'Profile',
                                                    CRM_Core_Permission::SEARCH );

        $this->_customFields = CRM_Core_BAO_CustomField::getFieldsForImport( null );
        $this->_params   = array( );

        $resetArray = array( 'group', 'tag', 'preferred_communication_method', 'do_not_phone',
                             'do_not_email', 'do_not_mail', 'do_not_sms', 'do_not_trade', 'gender' );

        foreach ( $this->_fields as $name => $field ) {
            if ( (substr($name, 0, 6) == 'custom') && CRM_Utils_Array::value( 'is_search_range', $field ) ) {
                $from = CRM_Utils_Request::retrieve( $name.'_from', 'String',
                                                     $this, false, null, 'REQUEST' );
                $to = CRM_Utils_Request::retrieve( $name.'_to', 'String',
                                                   $this, false, null, 'REQUEST' );
                $value = array();
                if ( $from && $to ) {
                    $value['from'] = $from;
                    $value['to']   = $to;
                } else if ( $from ) {
                    $value['from'] = $from;
                } else if ( $to ) {
                    $value['to'] = $to;
                }
            } else if ( ( substr($name, 0, 7) == 'custom_' ) &&
                        ( CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_CustomField', 
                                                       substr($name, 7), 'html_type' ) == 'TextArea' ) ) {
                $value = trim( CRM_Utils_Request::retrieve( $name, 'String',
                                                            $this, false, null, 'REQUEST' ) );
                if ( ! empty($value) &&
                     ! ( ( substr( $value, 0, 1 )  == '%' ) &&
                         ( substr( $value, -1, 1 ) == '%' ) ) ) {
                    $value = '%' . $value . '%';
                }
                
            } else if ( CRM_Utils_Array::value( 'html_type', $field ) == 'Multi-Select State/Province' 
                        || CRM_Utils_Array::value( 'html_type', $field ) == 'Multi-Select Country') {
                $value = CRM_Utils_Request::retrieve( $name, 'String', $this, false, null, 'REQUEST' );
                if ( ! is_array($value) ) $value = explode(CRM_Core_BAO_CustomOption::VALUE_SEPERATOR, substr($value,1,-1));
            } else {
                $value = CRM_Utils_Request::retrieve( $name, 'String',
                                                      $this, false, null, 'REQUEST' );
            }
            
            if ( ( $name == 'group' || $name == 'tag' ) && ! empty( $value ) && ! is_array( $value ) ) {
                $v = explode( ',', $value );
                $value = array( );
                foreach ( $v as $item ) {
                    $value[$item] = 1;
                }
            }

            $customField = CRM_Utils_Array::value( $name, $this->_customFields );
          
            if ( ! empty( $_POST ) && ! CRM_Utils_Array::value( $name, $_POST ) ) {
                if ( $customField ) {
                    // reset checkbox/radio because a form does not send null checkbox values
                    if ( in_array( $customField['html_type'], 
                                   array( 'Multi-Select', 'CheckBox', 'Multi-Select State/Province', 'Multi-Select Country', 'Radio' ) ) ) {
                        // only reset on a POST submission if we dont see any value
                        $value = null;
                        $this->set( $name, $value );
                    }
                } else if ( in_array( $name, $resetArray ) ) {
                    $value = null;  
                    $this->set( $name, $value );  
                }
            }

            if ( isset( $value ) && $value != null ) {
                if ( !is_array( $value) ) {
                    $value = trim( $value );
                }
                $this->_params[$name] = $this->_fields[$name]['value'] = $value;
            }
        }

        // set the prox params
        // need to ensure proximity searching is enabled
        $proximityVars = array( 'street_address', 'city', 'postal_code', 'state_province_id',
                                'country_id', 'distance', 'distance_unit' );
        foreach ( $proximityVars as $var ) {
            $value = CRM_Utils_Request::retrieve( "prox_{$var}",
                                                  'String',
                                                  $this, false, null, 'REQUEST' );
            if ( $value ) {
                $this->_params["prox_{$var}"] = $value;
            }
        }                                     
        
        
        // set the params in session
        $session = CRM_Core_Session::singleton();
        $session->set('profileParams', $this->_params);
   }

    /** 
     * run this page (figure out the action needed and perform it). 
     * 
     * @return void 
     */ 
    function run( ) {
        $this->preProcess( );
        
        $this->assign( 'recentlyViewed', false );
        
        if ( $this->_gid ) {
            $ufgroupDAO = new CRM_Core_DAO_UFGroup( );
            $ufgroupDAO->id = $this->_gid;
            if ( ! $ufgroupDAO->find( true ) ) {
                CRM_Core_Error::fatal( );
            }
        }

        if ( $this->_gid ) {
            // set the title of the page
            if ( $ufgroupDAO->title ) {
                CRM_Utils_System::setTitle( $ufgroupDAO->title );
            }
        }


        $this->assign( 'isReset', true );


        $formController = new CRM_Core_Controller_Simple( 'CRM_Profile_Form_Search',
                                                           ts('Search Profile'),
                                                           CRM_Core_Action::ADD );
        $formController->setEmbedded( true );
        $formController->set( 'gid', $this->_gid );
        $formController->process( ); 
        
        $searchError = false;
        // check if there is a POST
        if ( ! empty( $_POST ) ) {
            if ( $formController->validate( ) !== true ) {
                $searchError = true;
            }
        }

        // also get the search tpl name
        $this->assign( 'searchTPL', $formController->getTemplateFileName( ) );
        
        $this->assign( 'search', $this->_search );

        // search if search returned a form error?
        if ( ( ! CRM_Utils_Array::value( 'reset', $_GET ) ||
               CRM_Utils_Array::value( 'force', $_GET ) ) &&
             ! $searchError ) {
            $this->assign( 'isReset', false );

            $gidString = $this->_gid;    
            if ( empty( $this->_profileIds ) ) {
                $gids = $this->_gid;
            } else {
                $gids = $this->_profileIds;
                $gidString = implode( ',', $this->_profileIds );
            }

            $map      = 0;
            $linkToUF = 0;
            $editLink = false;
            if ( $this->_gid ) {
                $map      = $ufgroupDAO->is_map;
                $linkToUF = $ufgroupDAO->is_uf_link;
                $editLink = $ufgroupDAO->is_edit_link;
            }
            
            if ( $map ) {
                $this->assign( 'mapURL',
                               CRM_Utils_System::url( 'civicrm/profile/map',
                                                      "map=1&gid={$gidString}&reset=1" ) );
            }
            if ( CRM_Utils_Array::value( 'group', $this->_params ) ) {
                foreach( $this->_params['group'] as $key => $val ) {
                    if ( !$val ) {
                        unset( $this->_params['group'][$key] );
                    }
                }
            }
                
            // the selector will override this if the user does have
            // edit permissions as determined by the mask, CRM-4341
            // do not allow edit for anon users in joomla frontend, CRM-4668
            $config = CRM_Core_Config::singleton( );
            if ( ! CRM_Core_Permission::check( 'access CiviCRM' ) ||
                 $config->userFrameworkFrontend == 1 ) {
                $editLink = false;
            }

            $selector = new CRM_Profile_Selector_Listings( $this->_params, $this->_customFields, $gids,
                                                           $map, $editLink, $linkToUF );
                
            $controller = new CRM_Core_Selector_Controller($selector ,
                                                           $this->get( CRM_Utils_Pager::PAGE_ID ),
                                                           $this->get( CRM_Utils_Sort::SORT_ID  ),
                                                           CRM_Core_Action::VIEW,
                                                           $this,
                                                           CRM_Core_Selector_Controller::TEMPLATE );
            $controller->setEmbedded( true );
            $controller->run( );
        }
        
        //CRM-6862 -run form cotroller after
        //selector, since it erase $_POST         
        $formController->run( ); 
        
        return parent::run( );
    }

    /**
     * Function to get the list of contacts for a profile
     * 
     * @param $form object 
     *
     * @access public
     */
    function getProfileContact( $gid ) 
    {
        $session = CRM_Core_Session::singleton();
        $params = $session->get('profileParams');
        
        $details = array( );
        $ufGroupParam   = array('id' => $gid );
        require_once "CRM/Core/BAO/UFGroup.php";
        CRM_Core_BAO_UFGroup::retrieve($ufGroupParam, $details);

        // make sure this group can be mapped
        if ( ! $details['is_map'] ) {
            CRM_Core_Error::statusBounce( ts('This profile does not have the map feature turned on.') );
        }

        $groupId = CRM_Utils_Array::value('limit_listings_group_id', $details);
        
        // add group id to params if a uf group belong to a any group
        if ($groupId) {
            if ( CRM_Utils_Array::value('group', $params ) ) {
                $params['group'][$groupId] = 1;
            } else {
                $params['group'] = array($groupId => 1);
            }
        }
        
        $fields = CRM_Core_BAO_UFGroup::getListingFields( CRM_Core_Action::VIEW,
                                                          CRM_Core_BAO_UFGroup::PUBLIC_VISIBILITY |
                                                          CRM_Core_BAO_UFGroup::LISTINGS_VISIBILITY,
                                                          false, $gid );

        $returnProperties =& CRM_Contact_BAO_Contact::makeHierReturnProperties( $fields );
        $returnProperties['contact_type'] = 1;
        $returnProperties['sort_name'   ] = 1;

        $queryParams =& CRM_Contact_BAO_Query::convertFormValues( $params, 1 );
        $query   = new CRM_Contact_BAO_Query( $queryParams, $returnProperties, $fields );
        
        $ids = $query->searchQuery( 0, 0, null, 
                                    false, false, false, 
                                    true, false );                            

        $contactIds = explode( ',', $ids );
        
        return $contactIds;
    }

    function getTemplateFileName() {
        if ( $this->_gid ) {
            $templateFile = "CRM/Profile/Page/{$this->_gid}/Listings.tpl";
            $template     =& CRM_Core_Page::getTemplate( );
            if ( $template->template_exists( $templateFile ) ) {
                return $templateFile;
            }

            // lets see if we have customized by name
            $ufGroupName = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_UFGroup', $this->_gid, 'name' );
            if ( $ufGroupName ) {
                $templateFile = "CRM/Profile/Page/{$ufGroupName}/Listings.tpl";
                if ( $template->template_exists( $templateFile ) ) {
                    return $templateFile;
                }
            }
        }
        return parent::getTemplateFileName( );
    }

}


