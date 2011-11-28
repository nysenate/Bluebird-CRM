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

require_once 'CRM/Core/Form.php';
require_once 'CRM/Contact/Form/Location.php';
require_once 'CRM/Custom/Form/CustomData.php';
require_once 'CRM/Contact/BAO/ContactType.php';

/**
 * This class generates form components generic to all the contact types.
 * 
 * It delegates the work to lower level subclasses and integrates the changes
 * back in. It also uses a lot of functionality with the CRM API's, so any change
 * made here could potentially affect the API etc. Be careful, be aware, use unit tests.
 *
 */
class CRM_Contact_Form_Contact extends CRM_Core_Form
{
    /**
     * The contact type of the form
     *
     * @var string
     */
    public $_contactType;
    
    /**
     * The contact type of the form
     *
     * @var string
     */
    public $_contactSubType;
    
    /**
     * The contact id, used when editing the form
     *
     * @var int
     */
    public $_contactId;
    
    /**
     * the default group id passed in via the url
     *
     * @var int
     */
    public $_gid;
    
    /**
     * the default tag id passed in via the url
     *
     * @var int
     */
    public $_tid;
    
    /**
     * name of de-dupe button
     *
     * @var string
     * @access protected
     */
    protected $_dedupeButtonName;
    
    /**
     * name of optional save duplicate button
     *
     * @var string
     * @access protected
     */
    protected $_duplicateButtonName;
    
    protected $_editOptions = array( );
    
    public $_blocks;
    
    public $_values = array( );
    
    public $_action;
    /**
     * The array of greetings with option group and filed names
     *
     * @var array
     */
    public $_greetings;
    
    /**
     * Do we want to parse street address. 
     */
    private $_parseStreetAddress; 
     
    /**
     * check contact has a subtype or not 
     */
    public $_isContactSubType;
    
    /**
     * build all the data structures needed to build the form
     *
     * @return void
     * @access public
     */
    function preProcess( )
    {
        $this->_action  = CRM_Utils_Request::retrieve('action', 'String',$this, false, 'add' );
        
        $this->_dedupeButtonName    = $this->getButtonName( 'refresh', 'dedupe'    );
        $this->_duplicateButtonName = $this->getButtonName( 'upload',  'duplicate' );
        
        $session = & CRM_Core_Session::singleton( );
        if ( $this->_action == CRM_Core_Action::ADD ) {
            // check for add contacts permissions
            require_once 'CRM/Core/Permission.php';
            if ( ! CRM_Core_Permission::check( 'add contacts' ) ) {
                CRM_Utils_System::permissionDenied( );
                CRM_Utils_System::civiExit( );
            }
            $this->_contactType = CRM_Utils_Request::retrieve( 'ct', 'String',
                                                               $this, true, null, 'REQUEST' );
            if ( ! in_array( $this->_contactType,
                             array( 'Individual', 'Household', 'Organization' ) ) ) {
                CRM_Core_Error::statusBounce( ts('Could not get a contact id and/or contact type') );
            }
            
            $this->_isContactSubType = false;
            if( $this->_contactSubType = CRM_Utils_Request::retrieve( 'cst','String', $this ) ) {
                $this->_isContactSubType = true;
            }
            
            if ( $this->_contactSubType && !(CRM_Contact_BAO_ContactType::isExtendsContactType($this->_contactSubType, $this->_contactType, true)) ) { 
                CRM_Core_Error::statusBounce(ts("Could not get a valid contact subtype for contact type '%1'", array(1 => $this->_contactType)));
            }

            $this->_gid = CRM_Utils_Request::retrieve( 'gid', 'Integer',
                                                       CRM_Core_DAO::$_nullObject,
                                                       false, null, 'GET' );
            $this->_tid = CRM_Utils_Request::retrieve( 'tid', 'Integer',
                                                       CRM_Core_DAO::$_nullObject,
                                                       false, null, 'GET' );
            $typeLabel = 
                CRM_Contact_BAO_ContactType::contactTypePairs( true, $this->_contactSubType ? 
                                                               $this->_contactSubType : $this->_contactType );
            CRM_Utils_System::setTitle( ts( 'New %1', array( 1 => $typeLabel ) ) );
            $session->pushUserContext(CRM_Utils_System::url('civicrm/dashboard', 'reset=1'));
            $this->_contactId = null;
        } else {
            //update mode
            if ( ! $this->_contactId ) {
                $this->_contactId = CRM_Utils_Request::retrieve( 'cid', 'Positive', $this, true );
            }
            
            if ( $this->_contactId ) {
                require_once 'CRM/Contact/BAO/Contact.php';
                $contact = new CRM_Contact_DAO_Contact( );
                $contact->id = $this->_contactId;
                if ( ! $contact->find( true ) ) {
                    CRM_Core_Error::statusBounce( ts('contact does not exist: %1', array(1 => $this->_contactId)) );
                }
                $this->_contactType = $contact->contact_type;
                $this->_contactSubType = $contact->contact_sub_type;
                
                // check for permissions
                require_once 'CRM/Contact/BAO/Contact/Permission.php';
                $session =& CRM_Core_Session::singleton( );
                if ( $session->get( 'userID' ) != $this->_contactId &&
                     ! CRM_Contact_BAO_Contact_Permission::allow( $this->_contactId, CRM_Core_Permission::EDIT ) ) {
                    CRM_Core_Error::statusBounce( ts('You do not have the necessary permission to edit this contact.') );
                }
                
                list( $displayName, $contactImage ) = CRM_Contact_BAO_Contact::getDisplayAndImage( $this->_contactId );
                
                CRM_Utils_System::setTitle( $displayName, $contactImage . ' ' . $displayName );
                $context = CRM_Utils_Request::retrieve( 'context', 'String', $this );
                $qfKey = CRM_Utils_Request::retrieve( 'key', 'String', $this );
                require_once 'CRM/Utils/Rule.php';
                $urlParams = 'reset=1&cid='. $this->_contactId;
                if ( $context ) $urlParams .= "&context=$context"; 
                if ( CRM_Utils_Rule::qfKey( $qfKey ) ) $urlParams .= "&key=$qfKey"; 
                $session->pushUserContext(CRM_Utils_System::url('civicrm/contact/view', $urlParams ));
                
                $values = $this->get( 'values');
                // get contact values.
                if ( !empty( $values ) ) {
                    $this->_values = $values;
                } else {
                    $params = array( 'id'         => $this->_contactId,
                                     'contact_id' => $this->_contactId ) ;
                    $contact = CRM_Contact_BAO_Contact::retrieve( $params, $this->_values, true );
                    $this->set( 'values', $this->_values );
                }
            } else {
                CRM_Core_Error::statusBounce( ts('Could not get a contact_id and/or contact_type') );
            }
        }
        
        // parse street address, CRM-5450
        require_once 'CRM/Core/BAO/Preferences.php';
        $this->_parseStreetAddress = $this->get( 'parseStreetAddress' );
        if ( !isset( $this->_parseStreetAddress ) ) { 
            $addressOptions = CRM_Core_BAO_Preferences::valueOptions( 'address_options' );
            $this->_parseStreetAddress = false;
            if ( CRM_Utils_Array::value( 'street_address', $addressOptions ) &&
                 CRM_Utils_Array::value( 'street_address_parsing', $addressOptions ) ) {
                $this->_parseStreetAddress = true;
            }
            $this->set( 'parseStreetAddress', $this->_parseStreetAddress );
        }
        $this->assign( 'parseStreetAddress', $this->_parseStreetAddress );
        
        $this->_editOptions = $this->get( 'contactEditOptions' ); 
        if ( CRM_Utils_System::isNull( $this->_editOptions ) ) {
            $this->_editOptions  = CRM_Core_BAO_Preferences::valueOptions( 'contact_edit_options', true, null, 
                                                                           false, 'name', true, 'AND v.filter = 0' );
            $this->set( 'contactEditOptions', $this->_editOptions );
        }
        
        // build demographics only for Individual contact type
        if ( $this->_contactType != 'Individual' &&
             array_key_exists( 'Demographics', $this->_editOptions ) ) {
            unset( $this->_editOptions['Demographics'] );
        }
        
        // in update mode don't show notes
        if ( $this->_contactId && array_key_exists( 'Notes', $this->_editOptions ) ) {
            unset( $this->_editOptions['Notes'] );
        }
        
        $this->assign( 'editOptions',    $this->_editOptions );
        $this->assign( 'contactType',    $this->_contactType );
        $this->assign( 'contactSubType', $this->_contactSubType );

        //build contact subtype form element, CRM-6864
        $buildContactSubType = true;
        if ( $this->_contactSubType && ($this->_action & CRM_Core_Action::ADD) ) {
            $buildContactSubType = false;
        }
        $this->assign( 'buildContactSubType', $buildContactSubType );
        
        // get the location blocks.
        $this->_blocks = $this->get( 'blocks' );
        if ( CRM_Utils_System::isNull( $this->_blocks ) ) {
            $this->_blocks = CRM_Core_BAO_Preferences::valueOptions( 'contact_edit_options', true, null, 
                                                                     false, 'name', true, 'AND v.filter = 1' );
            $this->set( 'blocks', $this->_blocks );
        }
        $this->assign( 'blocks', $this->_blocks );
        
        // this is needed for custom data.
        $this->assign( 'entityID', $this->_contactId );
        
        // also keep the convention.
        $this->assign( 'contactId', $this->_contactId );
        
        // location blocks.
        CRM_Contact_Form_Location::preProcess( $this );

        // execute preProcess dynamically by js else execute normal preProcess
        if ( array_key_exists( 'CustomData', $this->_editOptions ) ) {
            if ( CRM_Utils_Request::retrieve( 'type', 'String', CRM_Core_DAO::$_nullObject ) ) {
                require_once 'CRM/Contact/Form/Edit/CustomData.php';
                CRM_Contact_Form_Edit_CustomData::preProcess( $this );
            } else {
                $contactSubType = $this->_contactSubType;
                // need contact sub type to build related grouptree array during post process
                if ( CRM_Utils_Array::value( 'contact_sub_type', $_POST ) ) {
                    $contactSubType = $_POST['contact_sub_type'];
                }
                //only custom data has preprocess hence directly call it
                CRM_Custom_Form_CustomData::preProcess( $this, null, $contactSubType, 
                                                        1, $this->_contactType, $this->_contactId );
            }
        }
        
    }
    
    /**
     * This function sets the default values for the form. Note that in edit/view mode
     * the default values are retrieved from the database
     * 
     * @access public
     * @return None
     */
    function setDefaultValues( ) 
    {
        $defaults = $this->_values;
        $params   = array( );

        if ( $this->_action & CRM_Core_Action::ADD ) {
            if ( array_key_exists( 'TagsAndGroups', $this->_editOptions ) ) {
                // set group and tag defaults if any
                if ( $this->_gid ) {
                    $defaults['group'][$this->_gid] = 1;
                }
                if ( $this->_tid ) {
                    $defaults['tag'][$this->_tid] = 1;
                }
            }
            if ( $this->_contactSubType ) {
                $defaults['contact_sub_type'] = $this->_contactSubType;
            }
        } else {
            require_once 'CRM/Contact/BAO/Relationship.php';
            $currentEmployer = CRM_Contact_BAO_Relationship::getCurrentEmployer( array( $this->_contactId ) );
            $defaults['current_employer_id'] = CRM_Utils_Array::value( 'org_id', $currentEmployer[$this->_contactId] );
            
            foreach ( $defaults['email'] as $dontCare => &$val ) {
                if (isset( $val['signature_text'] ) ) {
                    $val['signature_text_hidden'] = $val['signature_text'] ;
                }
                if (isset( $val['signature_html'] ) ) {
                    $val['signature_html_hidden'] = $val['signature_html'] ;
                }
            }
            
        }
        $this->assign( 'currentEmployer', CRM_Utils_Array::value('current_employer_id', $defaults) );            

        // set defaults for blocks ( custom data, address, communication preference, notes, tags and groups )
        foreach( $this->_editOptions as $name => $label ) {                
            if ( !in_array( $name, array( 'Address', 'Notes' ) ) ) {
                require_once(str_replace('_', DIRECTORY_SEPARATOR, 'CRM_Contact_Form_Edit_' . $name ) . '.php');
                eval( 'CRM_Contact_Form_Edit_' . $name . '::setDefaultValues( $this, $defaults );' );
            }
        }
        
        $addressValues = array( );       
        if ( isset( $defaults['address'] ) && is_array( $defaults['address'] ) && 
             !CRM_Utils_system::isNull( $defaults['address'] ) ) {
             
             // start of contact shared adddress defaults
             $sharedAddresses = array( );
             $masterAddress = array( );

             // get contact name of shared contact names
             $shareAddressContactNames = CRM_Contact_BAO_Contact_Utils::getAddressShareContactNames( $defaults['address'] );

             foreach ( $defaults['address'] as $key => $addressValue ) {
                 if ( CRM_Utils_Array::value( 'master_id', $addressValue ) && !$shareAddressContactNames[ $addressValue['master_id']]['is_deleted'] ) {
                     $sharedAddresses[$key]['shared_address_display'] = array( 'address' => $addressValue['display'],
                                                                               'name'    => $shareAddressContactNames[ $addressValue['master_id'] ]['name'] ); 
                 } else {
                    $defaults['address'][$key]['use_shared_address'] = 0;
                 }

                 //check if any address is shared by any other contacts
                 $masterAddress[$key] = CRM_Core_BAO_Address::checkContactSharedAddress( $addressValue['id'] );
             }
             
             $this->assign( 'sharedAddresses', $sharedAddresses );
             $this->assign( 'masterAddress', $masterAddress );
             // end of shared address defaults
             
             // start of parse address functionality   
             // build street address, CRM-5450.  
             if ( $this->_parseStreetAddress ) {                 
                $parseFields = array( 'street_address', 'street_number', 'street_name', 'street_unit' );
                foreach ( $defaults['address'] as $cnt => &$address ) {
                    $streetAddress = null;
                    foreach ( array( 'street_number', 'street_number_suffix', 'street_name', 'street_unit' ) as $fld ) {
                        if ( in_array( $fld, array( 'street_name', 'street_unit' ) ) ) { 
                            $streetAddress .= ' ';
                        }
                        $streetAddress .= CRM_Utils_Array::value( $fld, $address );
                    }
                    $streetAddress = trim( $streetAddress );
                    if ( !empty( $streetAddress ) ) {
                        $address['street_address'] = $streetAddress;
                    }
                    $address['street_number'] .= CRM_Utils_Array::value( 'street_number_suffix', $address ); 
                
                    // build array for set default.
                    foreach ( $parseFields as $field ) {
                        $addressValues["{$field}_{$cnt}"] = CRM_Utils_Array::value( $field, $address ); 
                    }
                
                    // don't load fields, use js to populate.
                    foreach ( array( 'street_number', 'street_name', 'street_unit' ) as $f ) {
                        if ( isset( $address[$f] ) ) unset( $address[$f] );
                    }
                }
                $this->assign( 'allAddressFieldValues', json_encode( $addressValues ) );
                
                //hack to handle show/hide address fields.
                $parsedAddress = array( );
                if ( $this->_contactId &&
                     CRM_Utils_Array::value( 'address', $_POST ) 
                     && is_array( $_POST['address'] ) ) {
                    foreach ( $_POST['address'] as $cnt => $values ) {
                        $showField = 'streetAddress';
                        foreach ( array( 'street_number', 'street_name', 'street_unit' ) as $fld ) {
                            if ( CRM_Utils_Array::value( $fld, $values ) ) {
                                $showField = 'addressElements';
                                break;
                            }
                        }
                        $parsedAddress[$cnt] = $showField;
                    }
                }
                $this->assign( 'showHideAddressFields',     $parsedAddress );
                $this->assign( 'loadShowHideAddressFields', empty( $parsedAddress  ) ? false : true  );             
            }
            // end of parse address functionality 
        }
        
        if ( CRM_Utils_Array::value( 'image_URL', $defaults  ) ) {
            list( $imageWidth, $imageHeight ) = getimagesize( $defaults['image_URL'] );
            list( $imageThumbWidth, $imageThumbHeight ) = CRM_Contact_BAO_Contact::getThumbSize( $imageWidth, $imageHeight );
            $this->assign( 'imageWidth', $imageWidth );
            $this->assign( 'imageHeight', $imageHeight );
            $this->assign( 'imageThumbWidth', $imageThumbWidth );
            $this->assign( 'imageThumbHeight', $imageThumbHeight );
            $this->assign( 'imageURL', $defaults['image_URL'] );                                            
        }
        
        //set location type and country to default for each block
        $this->blockSetDefaults( $defaults );
        return $defaults;
    }
    
    /*
     * do the set default related to location type id, 
     * primary location,  default country
     *
     */
    function blockSetDefaults( &$defaults ) 
    {
        $locationTypeKeys = array_filter(array_keys( CRM_Core_PseudoConstant::locationType() ), 'is_int' );
        sort( $locationTypeKeys );
        
        // get the default location type
        require_once 'CRM/Core/BAO/LocationType.php';
        
        $locationType = CRM_Core_BAO_LocationType::getDefault( );
        
        // unset primary location type
        $primaryLocationTypeIdKey = CRM_Utils_Array::key( $locationType->id, $locationTypeKeys );
        unset( $locationTypeKeys[ $primaryLocationTypeIdKey ] );
        
        // reset the array sequence
        $locationTypeKeys = array_values( $locationTypeKeys );
        
        // get default phone and im provider id.
        require_once 'CRM/Core/OptionGroup.php';
        $defPhoneTypeId  = key( CRM_Core_OptionGroup::values( 'phone_type', false, false, false, ' AND is_default = 1' ) );
        $defIMProviderId = key( CRM_Core_OptionGroup::values( 'instant_messenger_service', 
                                                              false, false, false, ' AND is_default = 1' ) );
        
        $allBlocks = $this->_blocks;
        if ( array_key_exists( 'Address', $this->_editOptions ) ) {
            $allBlocks['Address'] = $this->_editOptions['Address'];
        }
        
        $config = CRM_Core_Config::singleton( );
        foreach ( $allBlocks as $blockName => $label ) {
            $name = strtolower( $blockName );
            $hasPrimary = $updateMode = false;
            
            // user is in update mode. 
            if ( array_key_exists( $name, $defaults ) && 
                 !CRM_Utils_System::isNull( $defaults[$name] ) ) {
                $updateMode = true;
            }
            
            for ( $instance = 1; $instance <= $this->get( $blockName .'_Block_Count' ); $instance++ ) {
                
                // make we require one primary block, CRM-5505
                if ( $updateMode ) {
                    if ( !$hasPrimary ) {
                        $hasPrimary = CRM_Utils_Array::value( 'is_primary', $defaults[$name][$instance] );
                    }
                    continue;
                }
                
                //set location to primary for first one.
                if ( $instance == 1 ) {
                    $hasPrimary = true;
                    $defaults[$name][$instance]['is_primary']       = true;
                    $defaults[$name][$instance]['location_type_id'] = $locationType->id;
                } else {
                    $locTypeId = isset( $locationTypeKeys[$instance-1] )?$locationTypeKeys[$instance-1]:$locationType->id;
                    $defaults[$name][$instance]['location_type_id'] = $locTypeId; 
                }
                
                //set default country
                if ( $name == 'address' && $config->defaultContactCountry ) {
                    $defaults[$name][$instance]['country_id'] = $config->defaultContactCountry;
                }
                
                //set default phone type.
                if ( $name == 'phone' && $defPhoneTypeId ) {
                    $defaults[$name][$instance]['phone_type_id'] = $defPhoneTypeId;
                }
                
                //set default im provider.
                if ( $name == 'im' && $defIMProviderId ) {
                    $defaults[$name][$instance]['provider_id'] = $defIMProviderId;
                }
            }
            
            if ( !$hasPrimary ) {
                $defaults[$name][1]['is_primary'] = true;
            }
        }
        
        // set defaults for country-state widget
        if ( CRM_Utils_Array::value( 'address', $defaults ) && is_array( $defaults['address'] ) ) {
            require_once 'CRM/Contact/Form/Edit/Address.php';
            foreach ( $defaults['address'] as $blockId => $values ) {
                CRM_Contact_Form_Edit_Address::fixStateSelect( $this,
                                                               "address[$blockId][country_id]",
                                                               "address[$blockId][state_province_id]",
                                                               "address[$blockId][county_id]",
                                                               CRM_Utils_Array::value( 'country_id',
                                                                                       $values, $config->defaultContactCountry ),
                                                               CRM_Utils_Array::value( 'state_province_id', $values ) );
                
            }
        }
        
    }
    
    /**
     * This function is used to add the rules (mainly global rules) for form.
     * All local rules are added near the element
     *
     * @return None
     * @access public
     * @see valid_date
     */
    function addRules( )
    {
        // skip adding formRules when custom data is build
        if ( $this->_addBlockName || ($this->_action & CRM_Core_Action::DELETE) ) {
			return;
		}
        
        $this->addFormRule( array( 'CRM_Contact_Form_Edit_'. $this->_contactType,   'formRule' ), $this->_contactId );
        
        if ( array_key_exists('Address', $this->_editOptions) ) {
            $this->addFormRule( array( 'CRM_Contact_Form_Edit_Address',   'formRule' ) );
        }

        if ( array_key_exists('CommunicationPreferences', $this->_editOptions) ) {
            $this->addFormRule( array( 'CRM_Contact_Form_Edit_CommunicationPreferences','formRule' ), $this );
        }
    }
    
    /**
     * global validation rules for the form
     *
     * @param array $fields     posted values of the form
     * @param array $errors     list of errors to be posted back to the form
     * @param int   $contactId  contact id if doing update.
     *
     * @return $primaryID email/openId
     * @static
     * @access public
     */
    static function formRule( $fields, &$errors, $contactId = null )
    {
        $config = CRM_Core_Config::singleton( );
       
        // validations.
        //1. for each block only single value can be marked as is_primary = true.
        //2. location type id should be present if block data present.
        //3. check open id across db and other each block for duplicate.
        //4. at least one location should be primary.
        //5. also get primaryID from email or open id block.
        
        // take the location blocks.
        $blocks = CRM_Core_BAO_Preferences::valueOptions( 'contact_edit_options', true, null, 
                                                          false, 'name', true, 'AND v.filter = 1' );
                                                                  
        $otherEditOptions = CRM_Core_BAO_Preferences::valueOptions( 'contact_edit_options', true, null,
                                                                    false, 'name', true, 'AND v.filter = 0');
        //get address block inside.
        if ( array_key_exists( 'Address', $otherEditOptions ) ) {
            $blocks['Address'] = $otherEditOptions['Address'];
        }
        
        $openIds = array( );
        $primaryID = false;
        foreach ( $blocks as $name => $label ) {
            $hasData = $hasPrimary = array( );
            $name = strtolower( $name );
            if ( CRM_Utils_Array::value( $name, $fields ) && is_array( $fields[$name] ) ) {
                foreach ( $fields[$name] as $instance => $blockValues ) {
                    $dataExists = self::blockDataExists( $blockValues );
                    
                    if ( !$dataExists && $name == 'address' ) {
                        $dataExists = CRM_Utils_Array::value( 'use_shared_address', $fields['address'][$instance] );
                    }
                    
                    if ( $dataExists ) {
                        // skip remaining checks for website
                        if ( $name == 'website' ) {
                            continue;
                        }
                        
                        $hasData[] = $instance;
                        if ( CRM_Utils_Array::value( 'is_primary', $blockValues ) ) {
                            $hasPrimary[] = $instance;
                            if ( !$primaryID && 
                                 in_array( $name, array( 'email', 'openid' ) ) && 
                                 CRM_Utils_Array::value( $name, $blockValues ) ) {
                                $primaryID = $blockValues[$name];
                            }
                        }
                        
                        if ( !CRM_Utils_Array::value( 'location_type_id', $blockValues ) ) {
                            $errors["{$name}[$instance][location_type_id]"] = 
                                ts('The Location Type should be set if there is  %1 information.', array( 1=> $label ) );
                        }
                    }
                    
                    if ( $name == 'openid' && CRM_Utils_Array::value( $name, $blockValues ) ) {
                        require_once 'CRM/Core/DAO/OpenID.php';
                        $oid = new CRM_Core_DAO_OpenID( );
                        $oid->openid = $openIds[$instance] = CRM_Utils_Array::value( $name, $blockValues );
                        $cid = isset($contactId) ? $contactId : 0;
                        if ( $oid->find(true) && ($oid->contact_id != $cid) ) {
                            $errors["{$name}[$instance][openid]"] = ts('%1 already exist.', array( 1 => $blocks['OpenID'] ) );
                        }
                    }
                }
                
                if ( empty( $hasPrimary ) && !empty( $hasData ) ) {
                    $errors["{$name}[1][is_primary]"] = ts('One %1 should be marked as primary.', array( 1 => $label ) );
                }
                
                if ( count( $hasPrimary ) > 1 ) {
                    $errors["{$name}[".array_pop($hasPrimary)."][is_primary]"] = ts( 'Only one %1 can be marked as primary.', 
                                                                                     array( 1 => $label ) );  
                }
            }
        }
        
        //do validations for all opend ids they should be distinct.
        if ( !empty( $openIds ) && ( count( array_unique($openIds) ) != count($openIds) ) ) {
            foreach ( $openIds as $instance => $value ) {
                if ( !array_key_exists( $instance, array_unique($openIds) ) ) {
                    $errors["openid[$instance][openid]"] = ts('%1 already used.', array( 1 => $blocks['OpenID'] ) );
                }
            }
        }
        
        // street number should be digit + suffix, CRM-5450
        $parseStreetAddress = CRM_Utils_Array::value( 'street_address_parsing', 
                                                      CRM_Core_BAO_Preferences::valueOptions( 'address_options' ) );
        if ( $parseStreetAddress ) {
            if ( is_array( $fields['address'] ) ) {  
                $invalidStreetNumbers = array( );
                foreach ( $fields['address'] as $cnt => $address ) {
                    if ( $streetNumber = CRM_Utils_Array::value( 'street_number', $address ) ) {
                        $parsedAddress = CRM_Core_BAO_Address::parseStreetAddress( $address['street_number'] );
                        if ( !CRM_Utils_Array::value( 'street_number', $parsedAddress ) ) {
                            $invalidStreetNumbers[] = $cnt;
                        }
                    }
                }
                
                if ( !empty( $invalidStreetNumbers ) ) {
                    $first = $invalidStreetNumbers[0];
                    foreach ( $invalidStreetNumbers as &$num ) $num = CRM_Contact_Form_Contact::ordinalNumber( $num );
                    $errors["address[$first][street_number]"] = ts('The street number you entered for the %1 address block(s) is not in an expected format. Street numbers may include numeric digit(s) followed by other characters. You can still enter the complete street address (unparsed) by clicking "Edit Complete Street Address".', array(1 => implode(', ', $invalidStreetNumbers)));
                }
            }
        }
        
        return $primaryID;
    }
    
    /**
     * Function to actually build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) 
    {
        //load form for child blocks
        if ( $this->_addBlockName ) {
            require_once( str_replace('_', DIRECTORY_SEPARATOR, 'CRM_Contact_Form_Edit_' . $this->_addBlockName ) . '.php');
            return eval( 'CRM_Contact_Form_Edit_' . $this->_addBlockName . '::buildQuickForm( $this );' );
        }
        
        if ( $this->_action == CRM_Core_Action::UPDATE ) {
            $deleteExtra = ts('Are you sure you want to delete contact image.');
            $deleteURL =
                array( CRM_Core_Action::DELETE  =>
                       array(
                             'name'  => ts('Delete Contact Image'),
                             'url'   => 'civicrm/contact/image',
                             'qs'    => 'reset=1&cid=%%id%%&action=delete',
                             'extra' =>
                             'onclick = "if (confirm( \''. $deleteExtra .'\' ) ) this.href+=\'&amp;confirmed=1\'; else return false;"'
                             )
                       );
            $deleteURL = CRM_Core_Action::formLink( $deleteURL,
                                                    CRM_Core_Action::DELETE,
                                                    array( 'id'  => $this->_contactId,
                                                           ) );
            $this->assign( 'deleteURL', $deleteURL ); 
        }
        
        //build contact type specific fields
        require_once(str_replace('_', DIRECTORY_SEPARATOR, 'CRM_Contact_Form_Edit_' . $this->_contactType) . '.php');
        eval( 'CRM_Contact_Form_Edit_' . $this->_contactType . '::buildQuickForm( $this, $this->_action );' );
        
        // build Custom data if Custom data present in edit option
        $buildCustomData = null ; 
        if ( array_key_exists( 'CustomData', $this->_editOptions ) ) {
            $buildCustomData = "removeDefaultCustomFields( ), buildCustomData('{$this->_contactType}',this.value), highlightTabs( );";
        }

        // subtype is a common field. lets keep it here
        $typeLabel = CRM_Contact_BAO_ContactType::getLabel( $this->_contactType );
        $subtypes  = CRM_Contact_BAO_ContactType::subTypePairs( $this->_contactType );
        $subtypeElem =& $this->addElement( 'select', 'contact_sub_type', 
                                           ts('Contact Type'), array( '' => $typeLabel ) + $subtypes,
                                           array('onchange' => $buildCustomData ) );
        
        $allowEditSubType = true;
        if ( $this->_contactId && $this->_contactSubType ) {
            $allowEditSubType = CRM_Contact_BAO_ContactType::isAllowEdit( $this->_contactId, $this->_contactSubType );
        }
        if ( !$allowEditSubType ) {
            $subtypeElem->freeze( );
        }
        
        // build edit blocks ( custom data, demographics, communication preference, notes, tags and groups )
        foreach( $this->_editOptions as $name => $label ) {                
            if ( $name == 'Address' ) {
                $this->_blocks['Address'] = $this->_editOptions['Address'];
                continue;
            }
            require_once(str_replace('_', DIRECTORY_SEPARATOR, 'CRM_Contact_Form_Edit_' . $name ) . '.php');
            eval( 'CRM_Contact_Form_Edit_' . $name . '::buildQuickForm( $this );' );
        }
        
        // build location blocks.
        CRM_Contact_Form_Location::buildQuickForm( $this );
        
        // add attachment
        $this->addElement( 'file', 'image_URL', ts('Browse/Upload Image'), 'size=30 maxlength=60' );
        $this->addUploadElement( 'image_URL' );
        
        // add the dedupe button
        $this->addElement('submit', 
                          $this->_dedupeButtonName,
                          ts( 'Check for Matching Contact(s)' ) );
        $this->addElement('submit', 
                          $this->_duplicateButtonName,
                          ts( 'Save Matching Contact' ) );
        $this->addElement('submit', 
                          $this->getButtonName( 'next', 'sharedHouseholdDuplicate' ),
                          ts( 'Save With Duplicate Household' ) );
        
        $this->addButtons( array(
                                 array ( 'type'      => 'upload',
                                         'name'      => ts('Save'),
                                         'subName'   => 'view',
                                         'isDefault' => true   ),
                                 array ( 'type'      => 'upload',
                                         'name'      => ts('Save and New'),
                                         'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                                         'subName'   => 'new' ),
                                 array ( 'type'       => 'cancel',
                                         'name'      => ts('Cancel') ) ) );
    }
    
    /**
     * Form submission of new/edit contact is processed.
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {
        // check if dedupe button, if so return.
        $buttonName = $this->controller->getButtonName( );
        if ( $buttonName == $this->_dedupeButtonName ) {
            return;
        }
        
        //get the submitted values in an array
        $params = $this->controller->exportValues( $this->_name );
        
        if ( CRM_Utils_Array::value( 'image_URL', $params  ) ){
            CRM_Contact_BAO_Contact::processImageParams( $params ) ;
        }
        
        if ( is_numeric( CRM_Utils_Array::value( 'current_employer_id', $params ) ) 
             && CRM_Utils_Array::value( 'current_employer', $params ) ) { 
			$params['current_employer'] = $params['current_employer_id'];
        }
        
        // don't carry current_employer_id field,
        // since we don't want to directly update DAO object without
        // handling related business logic ( eg related membership )
        if ( isset( $params['current_employer_id'] ) ) unset( $params['current_employer_id'] ); 
        
        $params['contact_type'] = $this->_contactType;
        if ( !CRM_Utils_Array::value( 'contact_sub_type', $params ) && $this->_isContactSubType ) {
            $params['contact_sub_type'] = $this->_contactSubType;
        }
        
        if ( $this->_contactId ) {
            $params['contact_id'] = $this->_contactId;
        }
        
        //make deceased date null when is_deceased = false
        if ( $this->_contactType == 'Individual' && 
             CRM_Utils_Array::value( 'Demographics',  $this->_editOptions ) &&
             !CRM_Utils_Array::value( 'is_deceased', $params ) ) {
            $params['is_deceased']        = false;
            $params['deceased_date'] = null;
        }

        if ( isset($params['contact_id']) ) {
            // process membership status for deceased contact
            $deceasedParams = array( 'contact_id'  => $params['contact_id'],
                                     'is_deceased'   => !empty($params['is_deceased']),
                                     'deceased_date' => empty($params['deceased_date']) ? NULL : $params['deceased_date'] );
            $updateMembershipMsg = $this->updateMembershipStatus( $deceasedParams );
        }
        
        // action is taken depending upon the mode
        require_once 'CRM/Utils/Hook.php';
        if ( $this->_action & CRM_Core_Action::UPDATE ) {
            CRM_Utils_Hook::pre( 'edit', $params['contact_type'], $params['contact_id'], $params );
        } else {
            CRM_Utils_Hook::pre( 'create', $params['contact_type'], null, $params );
        }
        
        require_once 'CRM/Core/BAO/CustomField.php';
        $customFields     = 
            CRM_Core_BAO_CustomField::getFields( $params['contact_type'], false, true );

        //CRM-5143
        //if subtype is set, send subtype as extend to validate subtype customfield 
        $customFieldExtends = (CRM_Utils_Array::value('contact_sub_type', $params)) ? $params['contact_sub_type'] : $params['contact_type'];  
            
        $params['custom'] = CRM_Core_BAO_CustomField::postProcess( $params, 
                                                                   $customFields, 
                                                                   $this->_contactId,
                                                                   $customFieldExtends, 
                                                                   true );
        
        if ( array_key_exists( 'CommunicationPreferences',  $this->_editOptions ) ) {
            // this is a chekbox, so mark false if we dont get a POST value
            $params['is_opt_out'] = CRM_Utils_Array::value( 'is_opt_out', $params, false );
        }
        
        // process shared contact address.
        require_once 'CRM/Contact/BAO/Contact/Utils.php';
        CRM_Contact_BAO_Contact_Utils::processSharedAddress( $params['address'] );
        
        if ( ! array_key_exists( 'TagsAndGroups', $this->_editOptions ) ) {
            unset($params['group']);
        }

        if ( CRM_Utils_Array::value( 'contact_id', $params ) && ( $this->_action & CRM_Core_Action::UPDATE ) ) {
            // figure out which all groups are intended to be removed
            if ( ! empty($params['group']) ) {
                $contactGroupList =& CRM_Contact_BAO_GroupContact::getContactGroup( $params['contact_id'], 'Added' );
                if ( is_array($contactGroupList) ) {
                    foreach ( $contactGroupList as $key ) {
                        if ( $params['group'][$key['group_id']] != 1 &&
                             !CRM_Utils_Array::value( 'is_hidden', $key ) ) {
                            $params['group'][$key['group_id']] = -1;
                        }
                    }
                }
            }
        }
        
        // parse street address, CRM-5450
        $parseStatusMsg = null;
        if ( $this->_parseStreetAddress ) {
            $parseResult    = $this->parseAddress( $params );
            $parseStatusMsg = $this->parseAddressStatusMsg( $parseResult );
        }
        
        // Allow un-setting of location info, CRM-5969
        $params['updateBlankLocInfo'] = true;

        require_once 'CRM/Contact/BAO/Contact.php';
        $contact =& CRM_Contact_BAO_Contact::create( $params, true, false, true );

        // set the contact ID
        $this->_contactId = $contact->id;
       
        if ( array_key_exists( 'TagsAndGroups', $this->_editOptions ) ) {
            //add contact to tags
            require_once 'CRM/Core/BAO/EntityTag.php';
            CRM_Core_BAO_EntityTag::create( $params['tag'],'civicrm_contact' ,
                                            $params['contact_id'] );
        
            //save free tags
            if ( isset( $params['contact_taglist'] ) && !empty( $params['contact_taglist'] ) ) {
                require_once 'CRM/Core/Form/Tag.php';
                CRM_Core_Form_Tag::postProcess( $params['contact_taglist'], $params['contact_id'], 'civicrm_contact', $this );
            }
        }
        
        $statusMsg = ts('Your %1 contact record has been saved.', array( 1 => $contact->contact_type_display ) );
        if ( !empty($parseStatusMsg) ) {
            $statusMsg =  "$statusMsg <br > $parseStatusMsg";
        }
        if ( !empty($uploadFailMsg)  ) {
            $statusMsg = "$statusMsg <br > $uploadFailMsg";
        }
        if ( !empty($updateMembershipMsg) ) {
            $statusMsg = "$statusMsg <br > $updateMembershipMsg";
        }

        $session = CRM_Core_Session::singleton( );
        CRM_Core_Session::setStatus( $statusMsg );

        require_once 'CRM/Utils/Recent.php';
        // add the recently viewed contact
        $displayName = CRM_Contact_BAO_Contact::displayName( $contact->id );
        
        require_once 'CRM/Contact/BAO/Contact/Permission.php';
        $recentOther = array( );

        if ( ( $session->get( 'userID' ) == $contact->id ) ||
             CRM_Contact_BAO_Contact_Permission::allow( $contact->id, CRM_Core_Permission::EDIT ) ) {
            $recentOther['editUrl'] = CRM_Utils_System::url( 'civicrm/contact/add', 'reset=1&action=update&cid=' . $contact->id );
        }

        if ( ( $session->get( 'userID' ) != $this->_contactId ) && CRM_Core_Permission::check('delete contacts') ) {
            $recentOther['deleteUrl'] = CRM_Utils_System::url( 'civicrm/contact/view/delete', 'reset=1&delete=1&cid=' . $contact->id );
        }

        CRM_Utils_Recent::add( $displayName,
                               CRM_Utils_System::url( 'civicrm/contact/view', 'reset=1&cid=' . $contact->id ),
                               $contact->id,
                               $this->_contactType,
                               $contact->id,
                               $displayName,
                               $recentOther );
        
        // here we replace the user context with the url to view this contact
        $buttonName = $this->controller->getButtonName( );
        if ( $buttonName == $this->getButtonName( 'upload', 'new' )  ) {
            $resetStr  = "reset=1&ct={$contact->contact_type}";
            $resetStr .= $this->_contactSubType ? "&cst={$this->_contactSubType}" : '';
            $session->replaceUserContext(CRM_Utils_System::url('civicrm/contact/add', $resetStr ) );
        } else {
            $context = CRM_Utils_Request::retrieve( 'context', 'String', $this );
            $qfKey = CRM_Utils_Request::retrieve( 'key', 'String', $this );
            //validate the qfKey
            require_once 'CRM/Utils/Rule.php';
            $urlParams = 'reset=1&cid='. $contact->id;
            if ( $context ) $urlParams .= "&context=$context";  
            if ( CRM_Utils_Rule::qfKey( $qfKey ) ) $urlParams .= "&key=$qfKey";
            
            $session->replaceUserContext(CRM_Utils_System::url( 'civicrm/contact/view', $urlParams ));
        }
        
        // now invoke the post hook
        if ($this->_action & CRM_Core_Action::UPDATE) {
            CRM_Utils_Hook::post( 'edit', $params['contact_type'], $contact->id, $contact );
        } else {
            CRM_Utils_Hook::post( 'create', $params['contact_type'], $contact->id, $contact );
        }
    }
    
    /**
     * is there any real significant data in the hierarchical location array
     *
     * @param array $fields the hierarchical value representation of this location
     *
     * @return boolean true if data exists, false otherwise
     * @static
     * @access public
     */
    static function blockDataExists( &$fields ) 
    {
        if ( !is_array( $fields ) ) return false;
        
        static $skipFields = array( 'location_type_id', 'is_primary', 'phone_type_id', 'provider_id', 'country_id', 'website_type_id' );
        foreach ( $fields as $name => $value ) {
            $skipField = false;
            foreach ( $skipFields as $skip ) {
                if ( strpos( "[$skip]", $name ) !== false ) {
                    if ($name == 'phone') continue;
                    $skipField = true;
                    break;
                }
            }
            if ( $skipField ) {
                continue;
            }
            if ( is_array( $value ) ) {
                if ( self::blockDataExists( $value ) ) {
                    return true;
                }
            } else {
                if ( ! empty( $value ) ) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Function to that checks for duplicate contacts
     *  
     *  @param array  $fields      fields array which are submitted
     *  @param array  $error       error message array
     *  @param int    $contactID   contact id
     *  @param string $contactType contact type  
     */
     static function checkDuplicateContacts( &$fields, &$errors, $contactID, $contactType )
     {
         // if this is a forced save, ignore find duplicate rule
         if ( ! CRM_Utils_Array::value( '_qf_Contact_upload_duplicate', $fields ) ) {
   
             require_once 'CRM/Dedupe/Finder.php';
             $dedupeParams = CRM_Dedupe_Finder::formatParams($fields, $contactType);
             $ids = CRM_Dedupe_Finder::dupesByParams($dedupeParams, $contactType, 'Fuzzy', array( $contactID ) );
             if ( $ids ) {
                 require_once 'CRM/Contact/BAO/Contact/Utils.php';
                 
                 $contactLinks = CRM_Contact_BAO_Contact_Utils::formatContactIDSToLinks( $ids, true, true, $contactID );

                 $duplicateContactsLinks = '<div class="matching-contacts-found">';
                 $duplicateContactsLinks .= ts('One matching contact was found. ', array('count' => count($contactLinks['rows']), 'plural' => '%count matching contacts were found.<br />'));
                 if ( $contactLinks['msg'] == 'view') {
                     $duplicateContactsLinks .= ts('You can View the existing contact', array('count' => count($contactLinks['rows']), 'plural' => 'You can View the existing contacts'));                 
                 } else {
                     $duplicateContactsLinks .= ts('You can View or Edit the existing contact', array('count' => count($contactLinks['rows']), 'plural' => 'You can View or Edit the existing contacts'));
                 }
                 if  ( $contactLinks['msg'] == 'merge' ) {
                     // We should also get a merge link if this is for an existing contact
                     $duplicateContactsLinks .= ts(', or Merge this contact with an existing contact');
                 }
                 $duplicateContactsLinks .= '.';
                 $duplicateContactsLinks .= '</div>';
                 $duplicateContactsLinks .= '<table class="matching-contacts-actions">';
                 $row = '';
                 for ($i=0; $i < count($contactLinks['rows']); $i++) {                 
            	   $row .='  <tr>	 ';
            	   $row .='  	<td class="matching-contacts-name"> ';
            	   $row .=  		$contactLinks['rows'][$i]['display_name'];
            	   $row .='  	</td>';
            	   $row .='  	<td class="matching-contacts-email"> ';
            	   $row .=  		$contactLinks['rows'][$i]['primary_email'];
            	   $row .='  	</td>';            	   
            	   $row .='  	<td class="action-items"> ';
            	   $row .=  		$contactLinks['rows'][$i]['view'];
            	   $row .=  		$contactLinks['rows'][$i]['edit'];
            	   $row .=  		CRM_Utils_Array::value( 'merge', $contactLinks['rows'][$i] );
            	   $row .='  	</td>';
            	   $row .='  </tr>	 ';
                 }

                 $duplicateContactsLinks .= $row.'</table>';
                 $duplicateContactsLinks .= ts("If you're sure this record is not a duplicate, click the 'Save Matching Contact' button below.");
                 
				 $errors['_qf_default'] = $duplicateContactsLinks;
                 
                 

                 // let smarty know that there are duplicates
                 $template = CRM_Core_Smarty::singleton( );
                 $template->assign( 'isDuplicate', 1 );
             } else if ( CRM_Utils_Array::value( '_qf_Contact_refresh_dedupe', $fields ) ) {
                 // add a session message for no matching contacts
                 CRM_Core_Session::setStatus(ts('No matching contact found.'));
             }
         }
     }   

    function getTemplateFileName() 
    {
        if ( $this->_contactSubType ) {
            $templateFile = "CRM/Contact/Form/Edit/SubType/{$this->_contactSubType}.tpl";
            $template     = CRM_Core_Form::getTemplate( );
            if ( $template->template_exists( $templateFile ) ) {
                return $templateFile;
            }
        }
        return parent::getTemplateFileName( );
    }
    
    /* Parse all address blocks present in given params
     * and return parse result for all address blocks,
     * This function either parse street address in to child 
     * elements or build street address from child elements.
     *
     * @params $params an array of key value consist of address  blocks.
     *
     * @return $parseSuccess as array of sucess/fails for every address block.
     */
    function parseAddress( &$params ) 
    {
        $parseSuccess = $parsedFields = array( );
        if ( !is_array( $params['address'] ) || 
             CRM_Utils_System::isNull( $params['address'] ) ) {
            return $parseSuccess;
        }
        
        require_once 'CRM/Core/BAO/Address.php';
        
        $buildStreetAddress = false;
        foreach ( $params['address'] as $instance => &$address ) {
            $parseFieldName = 'street_address';
            foreach ( array( 'street_number', 'street_name', 'street_unit' ) as $fld ) {
                if ( CRM_Utils_Array::value( $fld, $address )  ) {
                    $parseFieldName     = 'street_number';
                    $buildStreetAddress = true;
                    break;
                }
            }
            
            // main parse string.
            $parseString = CRM_Utils_Array::value( $parseFieldName, $address );
            
            // parse address field.
            $parsedFields = CRM_Core_BAO_Address::parseStreetAddress( $parseString );
            
            if ( $buildStreetAddress ) {
                //hack to ignore spaces between number and suffix.
                //here user gives input as street_number so it has to
                //be street_number and street_number_suffix, but
                //due to spaces though preg detect string as street_name
                //consider it as 'street_number_suffix'.
                $suffix = $parsedFields['street_number_suffix'];
                if ( !$suffix ) {
                    $suffix = $parsedFields['street_name'];
                }
                $address['street_number_suffix'] = $suffix;
                $address['street_number']        = $parsedFields['street_number'];
                
                $streetAddress = null;
                foreach ( array( 'street_number', 'street_number_suffix', 'street_name', 'street_unit' ) as $fld ) {
                    if ( in_array( $fld, array( 'street_name', 'street_unit') ) ) {
                        $streetAddress .= ' ';
                    }
                    $streetAddress .= CRM_Utils_Array::value( $fld, $address );
                }
                $address['street_address'] = trim( $streetAddress );
                $parseSuccess[$instance]   = true;
            } else {
                $success = true;
                // consider address is automatically parseable,
                // when we should found street_number and street_name
                if ( ! CRM_Utils_Array::value( 'street_name', $parsedFields ) ||
                     ! CRM_Utils_Array::value( 'street_number', $parsedFields ) ) {
                    $success = false;
                }
                
                // check for original street address string.
                if ( empty( $parseString ) ) {
                    $success = true;
                }
                
                $parseSuccess[$instance] = $success;
                
                // we do not reset element values, but keep what we've parsed
                // in case of partial matches: CRM-8378
                
                // merge parse address in to main address block.
                $address = array_merge( $address, $parsedFields );
            }
        }
        
        return $parseSuccess;
    }
    
    /* check parse result and if some address block fails then this
     * function return the status message for all address blocks.
     * 
     * @param  $parseResult an array of address blk instance and its status.
     *
     * @return $statusMsg   string status message for all address blocks. 
     */
    function parseAddressStatusMsg( $parseResult ) 
    {
        $statusMsg = null;
        if ( !is_array( $parseResult ) || empty( $parseResult ) ) {
            return $statusMsg;
        }
        
        $parseFails = array( );
        foreach ( $parseResult as $instance => $success ) {
            if ( !$success ) $parseFails[] = $this->ordinalNumber( $instance );
        }
        
        if ( !empty( $parseFails ) ) {
            $statusMsg = ts( "Complete street address(es) have been saved. However we were unable to split the address in the %1 address block(s) into address elements (street number, street name, street unit) due to an unrecognized address format. You can set the address elements manually by clicking 'Edit Address Elements' next to the Street Address field while in edit mode.",
                             array( 1 =>  implode( ', ', $parseFails ) ) );
        }
        
        return $statusMsg;
    }
    
    /* 
     * Convert normal number to ordinal number format.
     * like 1 => 1st, 2 => 2nd and so on...
     *
     * @param  $number int number to convert in to ordinal number.
     *
     * @return ordinal number for given number.
     */
    function ordinalNumber( $number ) 
    {
        if ( empty( $number )  ) {
            return null;
        }
        
        $str = 'th';
        switch( floor( $number/10 ) % 10 ) {
        case 1:            
        default:
            switch( $number % 10 ) {
            case 1: 
                $str = 'st';
                break;
            case 2:
                $str = 'nd';
                break;
            case 3: 
                $str = 'rd';
                break;
            }
        }
        
        return "$number$str";
    }
    
    /* Update membership status to deceased
     * function return the status message for updated membership.
     * 
     * @param  $deceasedParams array  having contact id and deceased value.
     *
     * @return $updateMembershipMsg string  status message for updated membership. 
     */
    function updateMembershipStatus( $deceasedParams ) 
    {
        $updateMembershipMsg = null;
        $contactId = CRM_Utils_Array::value( 'contact_id', $deceasedParams );
        $deceasedDate = CRM_Utils_Array::value( 'deceased_date', $deceasedParams );
        
        // process to set membership status to deceased for both active/inactive membership        
        if ( $contactId && 
             $this->_contactType == 'Individual' &&
             CRM_Utils_Array::value( 'is_deceased', $deceasedParams ) ) {
            
            $session = CRM_Core_Session::singleton( );
            $userId = $session->get( 'userID' );
            if ( !$userId ) {
                $userId = $contactId; 
            }
            
            require_once 'CRM/Member/BAO/MembershipLog.php';
            require_once 'CRM/Member/DAO/Membership.php';
            require_once 'CRM/Member/PseudoConstant.php';
            require_once 'CRM/Utils/Date.php';
            
            // get deceased status id            
            $allStatus        = CRM_Member_PseudoConstant::membershipStatus( );
            $deceasedStatusId = array_search( 'Deceased', $allStatus );
            if ( !$deceasedStatusId ) return $updateMembershipMsg; 
            
            $today = time( );
            if ( $deceasedDate  && strtotime( $deceasedDate ) > $today ) return $updateMembershipMsg;
            
            // get non deceased membership
            $dao = new CRM_Member_DAO_Membership( );
            $dao->contact_id = $contactId;
            $dao->whereAdd( "status_id != $deceasedStatusId" );
            $dao->find( );
            
            $memCount  = 0;
            while( $dao->fetch( ) ) {
                // update status to deceased (for both active/inactive membership )
                CRM_Core_DAO::setFieldValue( 'CRM_Member_DAO_Membership', $dao->id, 
                                             'status_id', $deceasedStatusId );
                
                // add membership log
                $membershipLog = array('membership_id'         => $dao->id,
                                       'status_id'             => $deceasedStatusId,
                                       'start_date'            => CRM_Utils_Date::isoToMysql( $dao->start_date ),
                                       'end_date'              => CRM_Utils_Date::isoToMysql( $dao->end_date ),
                                       'renewal_reminder_date' => CRM_Utils_Date::isoToMysql( $dao->reminder_date ), 
                                       'modified_id'           => $userId,
                                       'modified_date'         => date('Ymd'),
                					   'membership_type_id'	   => $dao->membership_type_id 
                                       );
                
                
                CRM_Member_BAO_MembershipLog::add( $membershipLog, CRM_Core_DAO::$_nullArray );
                
                $memCount ++; 
            }
            
            // set status msg
            if ( $memCount ) {
                $updateMembershipMsg =  ts("%1 Current membership(s) for this contact have been set to 'Deceased' status.", 
                                           array( 1 => $memCount ) );
            }
        }
        
        return $updateMembershipMsg;
    }

}


