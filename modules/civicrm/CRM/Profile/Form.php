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

/**
 * This class generates form components for custom data
 * 
 * It delegates the work to lower level subclasses and integrates the changes
 * back in. It also uses a lot of functionality with the CRM API's, so any change
 * made here could potentially affect the API etc. Be careful, be aware, use unit tests.
 *
 */
class CRM_Profile_Form extends CRM_Core_Form
{
    const
        MODE_REGISTER = 1,
        MODE_SEARCH   = 2,
        MODE_CREATE   = 4,
        MODE_EDIT     = 8;
    
    protected $_mode;
    
    protected $_skipPermission = false;
    
    /** 
     * The contact id that we are editing 
     * 
     * @var int 
     */ 
    protected $_id; 
    
    /** 
     * The group id that we are editing
     * 
     * @var int 
     */ 
    protected $_gid; 
    
    /** 
     * The group id that we are passing in url
     * 
     * @var int 
     */ 
    public $_grid;

    /** 
     * The title of the category we are editing 
     * 
     * @var string 
     */ 
    protected $_title; 
    
    /** 
     * the fields needed to build this form 
     * 
     * @var array 
     */ 
    public $_fields; 
    
    /** 
     * to store contact details
     * 
     * @var array 
     */ 
    protected $_contact; 
    
    /** 
     * to store group_id of the group which is to be assigned to the contact
     * 
     * @var int
     */ 
    protected $_addToGroupID;

    /**
     * Do we allow updates of the contact
     *
     * @var int
     */
    public $_isUpdateDupe = 0;

    public $_isAddCaptcha = false;
    
    protected $_isPermissionedChecksum = false;
    /**
     * THe context from which we came from, allows us to go there if redirect not set
     *
     * @var string
     */
    protected $_context;
    
    /**
     * THe contact type for registration case
     *
     * @var string
     */
    protected $_ctype = null;

    protected $_defaults = null;

    /**
     * Store profile ids if multiple profile ids are passed using comma separated.
     * Currently lets implement this functionality only for dialog mode
     */
    protected $_profileIds = array( );

    /** 
     * pre processing work done here. 
     * 
     * gets session variables for table name, id of entity in table, type of entity and stores them. 
     * 
     * @param  
     * @return void 
     * 
     * @access public 
     */ 
    function preProcess() 
    {
        require_once 'CRM/Core/BAO/UFGroup.php';
        require_once "CRM/Core/BAO/UFField.php";
        
        $this->_id         = $this->get( 'id'  );
        $this->_gid        = $this->get( 'gid' ); 
        $this->_profileIds = $this->get( 'profileIds' );
        $this->_grid       = CRM_Utils_Request::retrieve( 'grid', 'Integer', $this   );
        $this->_context    = CRM_Utils_Request::retrieve( 'context', 'String', $this );
        
        $this->_duplicateButtonName = $this->getButtonName( 'upload',  'duplicate' );
        
        $gids = explode( ',', CRM_Utils_Request::retrieve('gid', 'String', CRM_Core_DAO::$_nullObject, false, 0, 'GET') );
        
        if ( ( count( $gids ) > 1 )  && !$this->_profileIds && empty( $this->_profileIds ) ) {
            if ( !empty( $gids ) ) {
                foreach( $gids as $pfId  ) {
                   $this->_profileIds[ ] = CRM_Utils_Type::escape( $pfId, 'Positive' ); 
                }
            }
            
            // check if we are rendering mixed profiles
            if ( CRM_Core_BAO_UFGroup::checkForMixProfiles( $this->_profileIds ) ) {
                CRM_Core_Error::fatal( ts( 'You cannot combine profiles of multiple types.' ) );
            } 

            // for now consider 1'st profile as primary profile and validate it 
            // i.e check for profile type etc.
            // FIX ME: validations for other than primary
            $this->_gid = $this->_profileIds[0];
            $this->set( 'gid', $this->_gid );
            $this->set( 'profileIds', $this->_profileIds );
        }
        
        if ( !$this->_gid ) {
           $this->_gid = CRM_Utils_Request::retrieve('gid', 'Positive', $this, false, 0, 'GET');
        } 
        
        //get values for captch and dupe update.
        if ( $this->_gid ) {
            $dao = new CRM_Core_DAO_UFGroup();
            $dao->id = $this->_gid;
            if ( $dao->find( true ) ) {
                $this->_isUpdateDupe = $dao->is_update_dupe;
                $this->_isAddCaptcha = $dao->add_captcha;
            }
            $dao->free( );
        }

        if ( empty( $this->_profileIds ) ) {
            $gids = $this->_gid;
        } else {
            $gids = $this->_profileIds; 
        }
       
        // if we dont have a gid use the default, else just use that specific gid
        if ( ( $this->_mode == self::MODE_REGISTER || $this->_mode == self::MODE_CREATE ) && ! $this->_gid ) {
            $this->_ctype  = CRM_Utils_Request::retrieve( 'ctype', 'String', $this, false, 'Individual', 'REQUEST' );
            $this->_fields  = CRM_Core_BAO_UFGroup::getRegistrationFields( $this->_action, $this->_mode, $this->_ctype );
        } else if ( $this->_mode == self::MODE_SEARCH ) {
            $this->_fields  = CRM_Core_BAO_UFGroup::getListingFields( $this->_action,
                                                                      CRM_Core_BAO_UFGroup::PUBLIC_VISIBILITY | CRM_Core_BAO_UFGroup::LISTINGS_VISIBILITY,
                                                                      false,
                                                                      $gids,
                                                                      true, null,
                                                                      $this->_skipPermission,
                                                                      CRM_Core_Permission::SEARCH ); 
        } else { 
            $this->_fields  = CRM_Core_BAO_UFGroup::getFields( $gids, false, null,
                                                               null, null,
                                                               false, null,
                                                               $this->_skipPermission,
                                                               null,
                                                               ( $this->_action == CRM_Core_Action::ADD ) ? CRM_Core_Permission::CREATE : CRM_Core_Permission::EDIT );
            
            ///is profile double-opt process configurablem, key
            ///should be present in civicrm.settting.php file
            $config = CRM_Core_Config::singleton( );
            if ( $config->profileDoubleOptIn &&
                 CRM_Utils_Array::value( 'group', $this->_fields ) ) {
                $emailField = false;
                foreach ( $this->_fields as $name => $values ) {
                    if ( substr( $name, 0, 6 ) == 'email-' ) {
                        $emailField = true;
                    }
                }
                
                if ( !$emailField ) {
                    $session = CRM_Core_Session::singleton( );
                    $status = ts( "Email field should be included in profile if you want to use Group(s) when Profile double-opt in process is enabled." ); 
                    $session->setStatus( $status );
                }
            }
        }

        if ( !is_array( $this->_fields ) ) {
            $session = CRM_Core_Session::singleton( );
            CRM_Core_Session::setStatus(ts('This feature is not currently available.'));
            return CRM_Utils_System::redirect( CRM_Utils_System::url( 'civicrm', 'reset=1' ) );
        }
        
        if ( $this->_mode != self::MODE_SEARCH ) {
            CRM_Core_BAO_UFGroup::setRegisterDefaults(  $this->_fields, $defaults );
            $this->setDefaults( $defaults );    
        }
        
        $this->setDefaultsValues();
    }
    
    /** 
     * This function sets the default values for the form. Note that in edit/view mode 
     * the default values are retrieved from the database 
     *  
     * @access public 
     * @return void 
     */ 
    function setDefaultsValues( ) 
    {
        $this->_defaults = array( );   
        if ( $this->_id ) {
            CRM_Core_BAO_UFGroup::setProfileDefaults( $this->_id, $this->_fields, $this->_defaults, true );
        }
        
        //set custom field defaults
        require_once "CRM/Core/BAO/CustomField.php";
        foreach ( $this->_fields as $name => $field ) {
            if ( $customFieldID = CRM_Core_BAO_CustomField::getKeyID($name) ) {
                $htmlType = $field['html_type'];
                
                if ( !isset( $this->_defaults[$name] ) || $htmlType == 'File') {
                    CRM_Core_BAO_CustomField::setProfileDefaults( $customFieldID,
                                                                  $name,
                                                                  $this->_defaults,
                                                                  $this->_id,
                                                                  $this->_mode );
                }
                
                if ( $htmlType == 'File') {
                    $url = CRM_Core_BAO_CustomField::getFileURL( $this->_id, $customFieldID );
                    
                    if ( $url ) {
                        $customFiles[$field['name']]['displayURL'] = "Attached File : {$url['file_url']}";
                        
                        $deleteExtra = "Are you sure you want to delete attached file ?";
                        $fileId      = $url['file_id'];
                        $deleteURL   = CRM_Utils_System::url( 'civicrm/file',
                                                              "reset=1&id={$fileId}&eid=$this->_id&fid={$customFieldID}&action=delete" );
                        $customFiles[$field['name']]['deleteURL'] =
                            "<a href=\"{$deleteURL}\" onclick = \"if (confirm( ' $deleteExtra ' )) this.href+='&amp;confirmed=1'; else return false;\">Delete Attached File</a>";
                    }
                } 
            }
        }
        if ( isset( $customFiles ) ) {
            $this->assign( 'customFiles', $customFiles ); 
        }
        
        if ( CRM_Utils_Array::value( 'image_URL', $this->_defaults ) ) {
            list( $imageWidth, $imageHeight ) = getimagesize( $this->_defaults['image_URL'] );
            list( $imageThumbWidth, $imageThumbHeight ) = CRM_Contact_BAO_Contact::getThumbSize( $imageWidth, $imageHeight );
            $this->assign( "imageWidth", $imageWidth );
            $this->assign( "imageHeight", $imageHeight );
            $this->assign( "imageThumbWidth", $imageThumbWidth );
            $this->assign( "imageThumbHeight", $imageThumbHeight );
            $this->assign( "imageURL", $this->_defaults['image_URL'] ); 
        }
        
        $this->setDefaults( $this->_defaults );
    } 
    
    /**
     * Function to actually build the form
     *
     * @return void
     * @access public
     */
    public function buildQuickForm()
    {   
        //lets have single status message, CRM-4363
        $return = false;
        $statusMessage = null;
        
        //we should not allow component and mix profiles in search mode
        if ( $this->_mode != self::MODE_REGISTER ) {
            //check for mix profile fields (eg:  individual + other contact type)
            if ( CRM_Core_BAO_UFField::checkProfileType( $this->_gid ) ) {
                $statusMessage = ts( 'Profile search, view and edit are not supported for Profiles which include fields for more than one record type.' );
            }
            
            $profileType = CRM_Core_BAO_UFField::getProfileType( $this->_gid );
           
            
            if ( $this->_id ) {
                list( $contactType, $contactSubType ) = 
                    CRM_Contact_BAO_Contact::getContactTypes( $this->_id );
                         
                $profileSubType = false;
                if ( CRM_Contact_BAO_ContactType::isaSubType( $profileType ) ) {
                    $profileSubType = $profileType;
                    $profileType    = CRM_Contact_BAO_ContactType::getBasicType( $profileType );
                }

                if ( ($profileType != 'Contact') && 
                     (($profileSubType && $contactSubType && ($profileSubType != $contactSubType)) ||
                      ($profileType    !=  $contactType)) ) {
                    $return = true;
                    if ( !$statusMessage ) {
                        $statusMessage =  ts("This profile is configured for contact type '%1'. It cannot be used to edit contacts of other types.", array( 1 => $profileSubType ? $profileSubType : $profileType ) );
                    }
                }
            }
            
            if ( in_array( $profileType, array( "Membership", "Participant", "Contribution" ) ) ) {
                $return = true;
                if ( !$statusMessage ) {
                    $statusMessage = ts('Profile is not configured for the selected action.');
                }
            }
        }
        
        //lets have sigle status message, 
        $this->assign( 'statusMessage', $statusMessage );
        if ( $return ) {
            return false;
        }
        
        $sBlocks = array( );
        $hBlocks = array( );
        $config  = CRM_Core_Config::singleton( );
        
        $this->assign( 'id'          , $this->_id       );
        $this->assign( 'mode'        , $this->_mode     );
        $this->assign( 'action'      , $this->_action   );
        $this->assign_by_ref( 'fields'      , $this->_fields   );
        $this->assign( 'fieldset'    , (isset($this->_fieldset)) ? $this->_fieldset : "" ); 
        
        // do we need inactive options ?
        if ($this->_action & CRM_Core_Action::VIEW ) {
            $inactiveNeeded = true;
        } else {
            $inactiveNeeded = false;
        }
        
        $session  = CRM_Core_Session::singleton( );
        
        // should we restrict what we display
        $admin = true;
        if ( $this->_mode == self::MODE_EDIT ) {
            $admin = false;
            // show all fields that are visibile: 
            // if we are a admin OR the same user OR acl-user with access to the profile
            // or we have checksum access to this contact (i.e. the user without a login) - CRM-5909
            require_once 'CRM/ACL/API.php';
            if ( CRM_Core_Permission::check( 'administer users' ) ||
                 $this->_id == $session->get( 'userID' )          ||
                 $this->_isPermissionedChecksum ||
                 in_array( $this->_gid, 
                           CRM_ACL_API::group( CRM_Core_Permission::EDIT, 
                                               null, 
                                               'civicrm_uf_group', 
                                               CRM_Core_PseudoConstant::ufGroup( ) ) ) ) {
                $admin = true;
            }
        }
        
        $userID = $session->get( 'userID' );
        $anonUser = false; // if false, user is not logged-in. 
        if ( ! $userID ) {
            require_once 'CRM/Core/BAO/LocationType.php';
            $defaultLocationType =& CRM_Core_BAO_LocationType::getDefault();
            $primaryLocationType = $defaultLocationType->id;
            $anonUser = true; 
            $this->assign( 'anonUser', true );
        }
        
        $addCaptcha   = array();
        $emailPresent = false;
        
        // cache the state country fields. based on the results, we could use our javascript solution
        // in create or register mode
        $stateCountryMap = array( );
        
        // add the form elements
        foreach ($this->_fields as $name => $field ) {
            // make sure that there is enough permission to expose this field
            if ( ! $admin && $field['visibility'] == 'User and User Admin Only' ) {
                unset( $this->_fields[$name] );
                continue;
            }
            
            // since the CMS manages the email field, suppress the email display if in
            // register mode which occur within the CMS form
            if ( $this->_mode == self::MODE_REGISTER &&
                 substr( $name, 0, 5 ) == 'email' ) {
                unset( $this->_fields[$name] );
                continue;
            }
            
            list( $prefixName, $index ) = CRM_Utils_System::explode( '-', $name, 2 );
            if ( $prefixName == 'state_province' || $prefixName == 'country' ) {
                if ( ! array_key_exists( $index, $stateCountryMap ) ) {
                    $stateCountryMap[$index] = array( );
                }
                $stateCountryMap[$index][$prefixName] = $name;
            }
            
            CRM_Core_BAO_UFGroup::buildProfile($this, $field, $this->_mode );
            
            if ($field['add_to_group_id']) {
                $addToGroupId = $field['add_to_group_id'];
            }
            
            //build array for captcha
            if ( $field['add_captcha'] ) {
                $addCaptcha[$field['group_id']] = $field['add_captcha'];
            }
            
            if ( ($name == 'email-Primary') || ($name == 'email-'. isset($primaryLocationType) ? $primaryLocationType : "") ) { 
                $emailPresent = true;
                $this->_mail = $name;
            }
        }
        
        // add captcha only for create mode.
        if ( $this->_mode == self::MODE_CREATE ) {
            if ( !$this->_isAddCaptcha && !empty( $addCaptcha ) ) {
                $this->_isAddCaptcha = true;
            } 
            if ($this->_gid ) {
                $dao = new CRM_Core_DAO_UFGroup();
                $dao->id = $this->_gid;
                $dao->addSelect( );
                $dao->addSelect( 'add_captcha', 'is_update_dupe' );
                if ( $dao->find( true ) ) {
                    if ( $dao->add_captcha ) {
                        $setCaptcha = true;
                    }
                    if ($dao->is_update_dupe) {
                        $this->_isUpdateDupe = $dao->is_update_dupe;
                    }
                }
            }
        } else {
            $this->_isAddCaptcha = false;
        }
        
        //finally add captcha to form.
        if ( $this->_isAddCaptcha ) {
            require_once 'CRM/Utils/ReCAPTCHA.php';
            $captcha =& CRM_Utils_ReCAPTCHA::singleton( );
            $captcha->add( $this );
        }
        $this->assign( "isCaptcha", $this->_isAddCaptcha );
        
        if ( $this->_mode != self::MODE_SEARCH ) {
            if ( isset($addToGroupId) ) {
                $this->add('hidden', "add_to_group", $addToGroupId );
                $this->_addToGroupID = $addToGroupId;
            }
        }
        
    	// also do state country js
    	require_once 'CRM/Core/BAO/Address.php';
    	CRM_Core_BAO_Address::addStateCountryMap( $stateCountryMap, $this->_defaults );
        
        $action = CRM_Utils_Request::retrieve('action', 'String',$this, false, null );
        if ( $this->_mode == self::MODE_CREATE  ) { 
            require_once 'CRM/Core/BAO/CMSUser.php';
            CRM_Core_BAO_CMSUser::buildForm( $this, $this->_gid , $emailPresent ,$action );
        } else {                                                         
            $this->assign( 'showCMS', false );
        }
        
        $this->assign( 'groupId', $this->_gid ); 
        
        // now fix all state country selectors
        require_once 'CRM/Core/BAO/Address.php';
        CRM_Core_BAO_Address::fixAllStateSelects( $this, $this->_defaults );
        
        // if view mode pls freeze it with the done button.
        if ($this->_action & CRM_Core_Action::VIEW) {
            $this->freeze();
        }
        
        if ( $this->_context == 'dialog' ) {
            $this->addElement( 'submit', 
                               $this->_duplicateButtonName,
                               ts( 'Save Matching Contact' ) );
        }
    }
    
    /**
     * global form rule
     *
     * @param array  $fields the input form values
     * @param array  $files  the uploaded files if any
     * @param object $form   the form object
     *
     * @return true if no errors, else array of errors
     * @access public
     * @static
     */
    static function formRule( $fields, $files, $form )
    {
        $errors = array( );
        // if no values, return
        if ( empty( $fields ) ) {
            return true;
        }
        
        $cid = $register = null; 
        
        // hack we use a -1 in options to indicate that its registration 
        if ( $form->_id ) {
            $cid = $form->_id;
            $form->_isUpdateDupe = 1;
        }
        
        if ( $form->_mode == CRM_Profile_Form::MODE_REGISTER ) {
            $register = true; 
        } 
        
        // dont check for duplicates during registration validation: CRM-375 
        if ( ! $register && ! CRM_Utils_Array::value( '_qf_Edit_upload_duplicate', $fields ) ) { 
            // fix for CRM-3240
            if ( CRM_Utils_Array::value( 'email-Primary', $fields ) ) {
                $fields['email'] = CRM_Utils_Array::value( 'email-Primary', $fields );
            }
            
            // fix for CRM-6141
            if ( CRM_Utils_Array::value( 'phone-Primary-1', $fields ) &&
                 ! CRM_Utils_Array::value( 'phone-Primary', $fields ) ) {
                $fields['phone-Primary'] = $fields['phone-Primary-1'];
            }

            $session = CRM_Core_Session::singleton();

            $ctype = CRM_Core_BAO_UFGroup::getContactType($form->_gid);
            // If all profile fields is of Contact Type then consider
            // profile is of Individual type(default).
            if (!$ctype) $ctype = 'Individual';
            require_once 'CRM/Dedupe/Finder.php';
            $dedupeParams = CRM_Dedupe_Finder::formatParams($fields, $ctype);
            if ( $form->_mode == CRM_Profile_Form::MODE_CREATE ) {
                // fix for CRM-2888
                $exceptions = array( );
            } else {
                // for edit mode we need to allow our own record to be a dupe match!
                $exceptions = array( $session->get( 'userID' ) );
            }

            // for dialog mode we should always use fuzzy rule.
            $ruleType = 'Strict';
            if ( $form->_context == 'dialog' ) {
                $ruleType = 'Fuzzy';
            }    

            $dedupeParams['check_permission'] = false;
            $ids = CRM_Dedupe_Finder::dupesByParams( $dedupeParams,
                                                     $ctype, 
                                                     $ruleType, 
                                                     $exceptions );
            if ( $ids ) {
                if ( $form->_isUpdateDupe == 2 ) {
                    CRM_Core_Session::setStatus( ts('Note: this contact may be a duplicate of an existing record.') );
                } elseif ( $form->_isUpdateDupe == 1 ) {
                    if ( ! $form->_id ) {
                        $form->_id = $ids[0];
                    }
                } else {
                    if ( $form->_context == 'dialog' ) {
                        require_once 'CRM/Contact/BAO/Contact/Utils.php';
                 
		                 $contactLinks = CRM_Contact_BAO_Contact_Utils::formatContactIDSToLinks( $ids, true, true );
		
		                 $duplicateContactsLinks = '<div class="matching-contacts-found">';
		                 $duplicateContactsLinks .= ts('One matching contact was found. ', array('count' => count($contactLinks['rows']), 'plural' => '%count matching contacts were found.<br />'));                 
                         if ( $contactLinks['msg'] == 'view') {
                             $duplicateContactsLinks .= ts('You can View the existing contact.', array('count' => count($contactLinks['rows']), 'plural' => 'You can View the existing contacts.'));
                         } else {
                             $duplicateContactsLinks .= ts('You can View or Edit the existing contact.', array('count' => count($contactLinks['rows']), 'plural' => 'You can View or Edit the existing contacts.'));
                         }
		                 $duplicateContactsLinks .= '</div>';
		                 $duplicateContactsLinks .= '<table class="matching-contacts-actions">';
                         $row = '';
		                 for ($i=0;$i<sizeof($contactLinks['rows']);$i++) {                 
		            	   $row .='  <tr>	 ';
		            	   $row .='  	<td class="matching-contacts-name"> ';
		            	   $row .=  		$contactLinks['rows'][$i]['display_name'];
		            	   $row .='  	</td>';
		            	   $row .='  	<td class="matching-contacts-email"> ';
		            	   $row .=  		$contactLinks['rows'][$i]['primary_email'];
		            	   $row .='  	</td>';            	   
		            	   $row .='  	<td class="action-items"> ';
		            	   $row .=  		$contactLinks['rows'][$i]['view'].' ';
		            	   $row .=  		$contactLinks['rows'][$i]['edit'];
		            	   $row .='  	</td>';
		            	   $row .='  </tr>	 ';
		                 }
		
		                 $duplicateContactsLinks .= $row.'</table>';
		                 $duplicateContactsLinks .= "If you're sure this record is not a duplicate, click the 'Save Matching Contact' button below.";
		                 
						 $errors['_qf_default'] = $duplicateContactsLinks;
                        
                        
                        // let smarty know that there are duplicates
                        $template = CRM_Core_Smarty::singleton( );
                        $template->assign( 'isDuplicate', 1 );
                    } else {
                        $errors['_qf_default'] = ts( 'A record already exists with the same information.' );
                    }
                }
            }
        }
        
        foreach ($fields as $key => $value) {
            list($fieldName, $locTypeId, $phoneTypeId) = CRM_Utils_System::explode( '-', $key, 3 );
            if ($fieldName == 'state_province' && $fields["country-{$locTypeId}"]) {
                // Validate Country - State list            
                $countryId = $fields["country-{$locTypeId}"];
                $stateProvinceId = $value;
                
                if ($stateProvinceId && $countryId) {
                    $stateProvinceDAO = new CRM_Core_DAO_StateProvince();
                    $stateProvinceDAO->id = $stateProvinceId;
                    $stateProvinceDAO->find(true);
                    
                    if ($stateProvinceDAO->country_id != $countryId) {
                        // country mismatch hence display error
                        $stateProvinces = CRM_Core_PseudoConstant::stateProvince();
                        $countries =& CRM_Core_PseudoConstant::country();
                        $errors[$key] = "State/Province " . $stateProvinces[$stateProvinceId] . " is not part of ". $countries[$countryId] . ". It belongs to " . $countries[$stateProvinceDAO->country_id] . "." ;
                    }
                }
            }
            
            if ($fieldName == 'county' && $fields["state_province-{$locTypeId}"]) {
                // Validate County - State list            
                $stateProvinceId = $fields["state_province-{$locTypeId}"];
                $countyId = $value;
                
                if ($countyId && $stateProvinceId) {
                    $countyDAO = new CRM_Core_DAO_County();
                    $countyDAO->id = $countyId;
                    $countyDAO->find(true);
                    
                    if ($countyDAO->state_province_id != $stateProvinceId) {
                        // state province mismatch hence display error
                        $stateProvinces = CRM_Core_PseudoConstant::stateProvince();
                        $counties =& CRM_Core_PseudoConstant::county();
                        $errors[$key] = "County " . $counties[$countyId] . " is not part of ". $stateProvinces[$stateProvinceId] . ". It belongs to " . $stateProvinces[$countyDAO->state_province_id] . "." ;
                    }
                }
            }
            
        }

        $elements = array( 'email_greeting'  => 'email_greeting_custom', 
                           'postal_greeting' => 'postal_greeting_custom',
                           'addressee'       => 'addressee_custom' ); 
        foreach ( $elements as $greeting => $customizedGreeting ) {
            if( $greetingType = CRM_Utils_Array::value($greeting, $fields) ) {
                $customizedValue = CRM_Core_OptionGroup::getValue( $greeting, 'Customized', 'name' ); 
                if( $customizedValue  == $greetingType && 
                    ! CRM_Utils_Array::value( $customizedGreeting, $fields ) ) {
                    $errors[$customizedGreeting] = ts( 'Custom  %1 is a required field if %1 is of type Customized.', 
                                                       array( 1 => ucwords(str_replace('_'," ", $greeting) ) ) );
                }
            }
        }
        
        return empty($errors) ? true : $errors;
    }
    
    /**
     * Process the user submitted custom data values.
     *
     * @access public
     * @return void
     */
    public function postProcess( ) 
    {
        $params = $this->controller->exportValues( $this->_name );        
        
        if ( CRM_Utils_Array::value( 'image_URL', $params  ) ) {
            CRM_Contact_BAO_Contact::processImageParams( $params ) ;
        }
        
        $greetingTypes = array( 'addressee'       => 'addressee_id', 
                                'email_greeting'  => 'email_greeting_id', 
                                'postal_greeting' => 'postal_greeting_id'
                                );
        if ( $this->_id ) {
            $contactDetails = CRM_Contact_BAO_Contact::getHierContactDetails( $this->_id ,
                                                                              $greetingTypes );
            $details = $contactDetails[0][$this->_id];
        }
        if( !(CRM_Utils_Array::value( 'addressee_id',$details ) || 
              CRM_Utils_Array::value( 'email_greeting_id',$details ) || 
              CRM_Utils_Array::value( 'postal_greeting_id',$details ) )) {
            
            $profileType = CRM_Core_BAO_UFField::getProfileType( $this->_gid );
            //Though Profile type is contact we need
            //Individual/Household/Organization for setting Greetings.
            if( $profileType == 'Contact' ) {
                $profileType = 'Individual';
                //if we editing Household/Organization.
                if( $this->_id ) {
                    $profileType = CRM_Contact_BAO_Contact::getContactType( $this->_id );
                }
            }
            if ( CRM_Contact_BAO_ContactType::isaSubType( $profileType ) ) {
                $profileType = CRM_Contact_BAO_ContactType::getBasicType( $profileType );
            }
            
            $contactTypeFilters = array( 1 => 'Individual', 2 => 'Household', 
                                         3 => 'Organization' );
            $filter = CRM_Utils_Array::key( $profileType, $contactTypeFilters );
            if( $filter ) {
                foreach( $greetingTypes  as $key => $value ) {
                    if( !array_key_exists( $key, $params ) ) {
                        $defaultGreetingTypeId = CRM_Core_OptionGroup::values( $key, null, 
                                                                               null, null, 
                                                                               "AND is_default =1
                                                                               AND (filter = 
                                                                               {$filter} OR 
                                                                               filter = 0 )",
                                                                               'value' 
                                                                               );
                        
                        $params[$key] = key( $defaultGreetingTypeId );
                    }
                }
            }
            if ( $profileType == 'Organization' ) {
                unset( $params['email_greeting'], $params['postal_greeting'] );
                
            }
        }  
        if ( $this->_mode == self::MODE_REGISTER ) {
            require_once 'CRM/Core/BAO/Address.php';
            CRM_Core_BAO_Address::setOverwrite( false );
        }
        
        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );
        
        //used to send subcribe mail to the group which user want.
        //if the profile double option in is enabled
        $mailingType = array( );
        $config = CRM_Core_Config::singleton( );
        if ( $config->profileDoubleOptIn && CRM_Utils_Array::value( 'group', $params ) ) {
            $result = null;
            foreach ( $params as $name => $values ) {
                if ( substr( $name, 0, 6 ) == 'email-' ) {
                    $result['email'] = $values ;
                }
            }
            $groupSubscribed = array( );
            if ( CRM_Utils_Array::value( 'email' , $result ) ) {
                require_once 'CRM/Contact/DAO/Group.php';
                //array of group id, subscribed by contact
                $contactGroup = array( );
                if( $this->_id ) {
                    $contactGroups = new CRM_Contact_DAO_GroupContact();
                    $contactGroups->contact_id = $this->_id;
                    $contactGroups->status     = 'Added';
                    $contactGroups->find();
                    $contactGroup = array();
                    while( $contactGroups->fetch() ) { 
                        $contactGroup[] = $contactGroups->group_id;
                        $groupSubscribed[$contactGroups->group_id] = 1;
                    }
                }
                foreach ( $params['group'] as $key => $val ) {
                    if ( ! $val ) {
                        unset( $params['group'][$key] );
                        continue;
                    }
                    $groupTypes = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Group',
                                                               $key, 'group_type', 'id' );
                    $groupType = explode( CRM_Core_BAO_CustomOption::VALUE_SEPERATOR, 
                                          substr( $groupTypes, 1, -1 ) );
                    //filter group of mailing type and unset it from params
                    if ( in_array( 2, $groupType ) ) {
                        //if group is already subscribed , ignore it 
                        $groupExist = CRM_Utils_Array::key( $key, $contactGroup );
                        if ( ! isset( $groupExist ) ) {
                            $mailingType[] = $key ;
                            unset( $params['group'][$key] );
                        }
                    }
                }
            }
        }
        
        if ( CRM_Utils_Array::value( 'add_to_group', $params ) ) {
            $addToGroupId = $params['add_to_group'];

            // since we are directly adding contact to group lets unset it from mailing
            if ( $key = array_search( $addToGroupId, $mailingType ) ) {
                unset( $mailingType[$key] );
            }            
        }
        
        if ( $this->_grid ){
            $params['group'] = $groupSubscribed;
        }
        
        // commenting below code, since we potentially
        // triggered maximum name field formatting cases during CRM-4430.
        // CRM-4343
        // $params['preserveDBName'] = true;

        $this->_id = CRM_Contact_BAO_Contact::createProfileContact($params, $this->_fields,
                                                                   $this->_id, $this->_addToGroupID,
                                                                   $this->_gid, $this->_ctype,
                                                                   true );
        //mailing type group
        if ( ! empty ( $mailingType ) ) {
            require_once 'CRM/Mailing/Event/BAO/Subscribe.php';
            CRM_Mailing_Event_BAO_Subscribe::commonSubscribe( $mailingType, $result );
        }

        require_once 'CRM/Core/BAO/UFGroup.php'; 
        $ufGroups = array( );
        if ( $this->_gid ) {
            $ufGroups[$this->_gid] =  1;
        } else if ( $this->_mode == self::MODE_REGISTER ) {
            $ufGroups = & CRM_Core_BAO_UFGroup::getModuleUFGroup('User Registration');
        }
        
        foreach( $ufGroups as $gId => $val ) {
            if ( $notify = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_UFGroup', $gId, 'notify' ) ) {
                $values = CRM_Core_BAO_UFGroup::checkFieldsEmptyValues( $gId, $this->_id, null );
                CRM_Core_BAO_UFGroup::commonSendMail(  $this->_id, $values );
            }
        }
        
        //create CMS user (if CMS user option is selected in profile)
        if ( CRM_Utils_Array::value( 'cms_create_account', $params ) &&
             $this->_mode == self::MODE_CREATE ) {
            $params['contactID'] = $this->_id;
            require_once "CRM/Core/BAO/CMSUser.php";
            if ( ! CRM_Core_BAO_CMSUser::create( $params, $this->_mail ) ) {
                CRM_Core_Session::setStatus( ts('Your profile is not saved and Account is not created.') );
                $transaction->rollback( );
                return CRM_Utils_System::redirect( CRM_Utils_System::url('civicrm/profile/create',
                                                                         'reset=1&gid=' . $this->_gid) );
            }
        }
        
        $transaction->commit( );
        
    }
    
    function getTemplateFileName() {
        if ( $this->_gid ) {
            $templateFile = "CRM/Profile/Form/{$this->_gid}/{$this->_name}.tpl";
            $template =& CRM_Core_Form::getTemplate( );
            if ( $template->template_exists( $templateFile ) ) {
                return $templateFile;
            }

            // lets see if we have customized by name
            $ufGroupName = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_UFGroup', $this->_gid, 'name' );
            if ( $ufGroupName ) {
                $templateFile = "CRM/Profile/Form/{$ufGroupName}/{$this->_name}.tpl";
                if ( $template->template_exists( $templateFile ) ) {
                    return $templateFile;
                }
            }
        }
        return parent::getTemplateFileName( );
    }
    
}
