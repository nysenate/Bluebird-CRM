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
require_once 'CRM/Contact/BAO/Contact.php';
require_once 'CRM/Activity/BAO/Activity.php';
require_once 'CRM/Core/BAO/UFField.php';

/**
 * form to process actions on the field aspect of Custom
 */
class CRM_UF_Form_Field extends CRM_Core_Form 
{
    /**
     * the uf group id saved to the session for an update
     *
     * @var int
     * @access protected
     */
    protected $_gid;

    /**
     * The field id, used when editing the field
     *
     * @var int
     * @access protected
     */
    protected $_id;

    /**
     * The set of fields that we can view/edit in the user field framework
     *
     * @var array
     * @access protected
     */
    protected $_fields;

    /**
     * the title for field
     *
     * @var int
     * @access protected
     */
    protected $_title;

    /**
     * The set of fields sent to the select element
     *
     * @var array
     * @access protected
     */
    protected $_selectFields;

    /**
     * to store fields with if locationtype exits status 
     *
     * @var array
     * @access protected
     */
    protected $_hasLocationTypes;
    
    /**
     * is this profile has searchable field
     * or is any field having in selector true.
     *
     * @var boolean.
     * @access protected
     */
    protected $_hasSearchableORInSelector;
    
    /**
     * Function to set variables up before form is built
     *
     * @return void
     * @access public
     */
    public function preProcess()
    {
        $this->_gid = CRM_Utils_Request::retrieve( 'gid', 'Positive', $this );
        $this->_id  = CRM_Utils_Request::retrieve( 'id' , 'Positive', $this );
        if ( $this->_gid ) {
            $this->_title = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_UFGroup', $this->_gid, 'title' );
            CRM_Utils_System::setTitle( $this->_title .' - '.ts( 'CiviCRM Profile Fields' ) );
            
            $url = CRM_Utils_System::url( 'civicrm/admin/uf/group/field', 
                                          "reset=1&action=browse&gid={$this->_gid}" ); 
            
            $session = CRM_Core_Session::singleton( ); 
            $session->pushUserContext( $url );
        }

        $showBestResult = CRM_Utils_Request::retrieve( 'sbr', 'Positive', CRM_Core_DAO::$_nullArray );
        if ( $showBestResult ) {
            $this->assign( 'showBestResult', $showBestResult );
        }
        
        $this->_fields =& CRM_Contact_BAO_Contact::importableFields( 'All', true, true, true );
        $this->_fields = array_merge( CRM_Activity_BAO_Activity::exportableFields( 'Activity' ), $this->_fields );
        if ( CRM_Core_Permission::access( 'CiviContribute' ) ) {
            require_once "CRM/Contribute/BAO/Contribution.php";
            $this->_fields = array_merge ( CRM_Contribute_BAO_Contribution::getContributionFields(), $this->_fields );
        }

        if ( CRM_Core_Permission::access( 'CiviMember' ) ) {
            require_once 'CRM/Member/BAO/Membership.php';
            $this->_fields = array_merge ( CRM_Member_BAO_Membership::getMembershipFields(), $this->_fields ); 
        }

        if ( CRM_Core_Permission::access( 'CiviEvent' ) ) {
            require_once 'CRM/Event/BAO/Query.php';
            $this->_fields = array_merge ( CRM_Event_BAO_Query::getParticipantFields( true ), $this->_fields ); 
        }
        
        if ( CRM_Core_Permission::access( 'Quest' ) ) {
            require_once 'CRM/Quest/BAO/Student.php';
            $this->_fields = array_merge ( CRM_Quest_BAO_Student::exportableFields(), $this->_fields );
        }
        
        $this->_selectFields = array( );
        foreach ( $this->_fields as $name => $field ) {
            // lets skip note for now since we dont support it
            if ( $name == 'note' ) {
                continue;
            }
            $this->_selectFields    [$name] = $field['title'];
            $this->_hasLocationTypes[$name] = CRM_Utils_Array::value( 'hasLocationType', $field );
        }

        // lets add group and tag to this list
        $this->_selectFields['group'] = ts('Group(s)');
        $this->_selectFields['tag'  ] = ts('Tag(s)');
        
        //CRM-4363 check for in selector or searchable fields.
        $this->_hasSearchableORInSelector = CRM_Core_BAO_UFField::checkSearchableORInSelector( $this->_gid );

        $this->assign('fieldId', $this->_id);
        if ( $this->_id ){
            require_once 'CRM/Core/DAO.php';
            $fieldTitle = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_UFField', $this->_id, 'label' );
            $this->assign('fieldTitle', $fieldTitle);
        }
        
    }
    
    /**
     * Function to actually build the form
     *
     * @return void
     * @access public
     */
    public function buildQuickForm()
    {
        if ( $this->_action & CRM_Core_Action::DELETE ) {
            $this->addButtons( array(
                                     array ( 'type'      => 'next',
                                             'name'      => ts('Delete Profile Field'),
                                             'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                                             'isDefault' => true   ),
                                     array ( 'type'      => 'cancel',
                                             'name'      => ts('Cancel') ),
                                     )
                               );
            return;

        }

        if ( isset( $this->_id ) ) {
            $params = array( 'id' => $this->_id );
            CRM_Core_BAO_UFField::retrieve( $params, $defaults );

            // set it to null if so (avoids crappy E_NOTICE errors below
            $defaults['location_type_id'] = CRM_Utils_Array::value( 'location_type_id', $defaults );

            $specialFields = array ('street_address',
                                    'supplemental_address_1',
                                    'supplemental_address_2',
                                    'city', 'postal_code', 'postal_code_suffix',
                                    'geo_code_1', 'geo_code_2',
                                    'state_province', 'country','county',
                                    'phone', 'email', 'im', 'address_name' );
            
            if ( ! $defaults['location_type_id'] &&
                 in_array($defaults['field_name'], $specialFields)  ) {
                $defaults['location_type_id'] = 0;
            }
            
            $defaults[ 'field_name' ] = array ( $defaults['field_type'],
                                                $defaults['field_name'],
                                                $defaults['location_type_id'],
                                                CRM_Utils_Array::value( 'phone_type_id', $defaults ) );
            $this->_gid = $defaults['uf_group_id'];
            
        } else {
            $defaults['is_active'] = 1;
        }

        if ( $this->_action & CRM_Core_Action::ADD ) {
            $fieldValues = array('uf_group_id' => $this->_gid);
            $defaults['weight'] = CRM_Utils_Weight::getDefaultWeight('CRM_Core_DAO_UFField', $fieldValues);
        }
        
        // lets trim all the whitespace
        $this->applyFilter('__ALL__', 'trim');

        //hidden field to catch the group id in profile
        $this->add('hidden', 'group_id', $this->_gid);
        
        //hidden field to catch the field id in profile
        $this->add('hidden', 'field_id', $this->_id);
         
        $fields = array();
        $fields['Individual'  ] =& CRM_Contact_BAO_Contact::importableFields('Individual', false, false, true);
        $fields['Household'   ] =& CRM_Contact_BAO_Contact::importableFields('Household', false, false, true);
        $fields['Organization'] =& CRM_Contact_BAO_Contact::importableFields('Organization', false, false, true);
        
        // add current employer for individuals
        $fields['Individual']['current_employer'] = array( 'name'  => 'organization_name',
                                                           'title' => ts('Current Employer') );
        
        require_once 'CRM/Core/BAO/Preferences.php';
        $addressOptions = CRM_Core_BAO_Preferences::valueOptions( 'address_options', true, null, true );
        
        if ( !$addressOptions['county'] ) {
            unset( $fields['Individual'  ]['county']);
            unset( $fields['Household'   ]['county']);
            unset( $fields['Organization']['county']);
        }
        
        //build the common contact fields array CRM-3037.
        foreach ( $fields['Individual'] as $key => $value ) {
            if ( CRM_Utils_Array::value( $key, $fields['Household'] ) && 
                 CRM_Utils_Array::value( $key, $fields['Organization'] ) ) {
                $fields['Contact'][$key] = $value;
                //as we move common fields to contacts. There fore these fields
                //are unset from resoective array's.
                unset( $fields['Individual'][$key] );
                unset( $fields['Household'][$key] );
                unset( $fields['Organization'][$key] );
            }
        }
        
        // add current employer for individuals
        $fields['Contact']['id'] = array( 'name'  => 'id',
                                          'title' => ts('Internal Contact ID') );
                                          
        unset( $fields['Contact']['contact_type'] );

        // since we need a hierarchical list to display contact types & subtypes, 
        // this is what we going to display in first selector
        $contactTypes = CRM_Contact_BAO_ContactType::getSelectElements( false, false );
        unset($contactTypes['']);

        // include Subtypes For Profile
        $subTypes = CRM_Contact_BAO_ContactType::subTypeInfo( );
        foreach ( $subTypes as $name => $val ) {
            //custom fields for sub type
            $subTypeFields = CRM_Core_BAO_CustomField::getFieldsForImport( $name );

            if ( array_key_exists($val['parent'], $fields) ) {
                $fields[$name] = $fields[$val['parent']] + $subTypeFields;
            } else {
                $fields[$name] = $subTypeFields;
            }
        }
        
        //group selected and unwanted fields list
        require_once 'CRM/Core/BAO/UFGroup.php';
        $groupFieldList = array_merge( CRM_Core_BAO_UFGroup::getFields( $this->_gid, false, null, null , null, true, null, true ), 
                                       array( 'note', 'email_greeting_custom', 'postal_greeting_custom', 'addressee_custom', 'id' ) 
                                     );
        //unset selected fields
        foreach( $groupFieldList as $key => $value ) {
            if ( is_integer( $key) ) {
                unset( $fields['Individual'][$value], $fields['Household'][$value], $fields['Organization'][$value] );
                continue;
            }
            if ( CRM_Utils_Array::value( 'field_name',$defaults ) 
                && $defaults['field_name']['0'] == $value['field_type'] 
                && $defaults['field_name']['1'] == $key ) {
                continue;
            } 
            unset( $fields[$value['field_type']][$key] );
        }
        unset( $subTypes );

        if ( CRM_Core_Permission::access( 'Quest' ) ) {
            require_once 'CRM/Quest/BAO/Student.php';
            $fields['Student'] =& CRM_Quest_BAO_Student::exportableFields();
        }

        if ( CRM_Core_Permission::access( 'CiviContribute' ) ) {
            $contribFields =& CRM_Contribute_BAO_Contribution::getContributionFields();
            if ( ! empty( $contribFields ) ) {
                unset( $contribFields['is_test'] );
                unset( $contribFields['is_pay_later'] );
                unset( $contribFields['contribution_id'] );
                $fields['Contribution'] =& $contribFields;
            }
        }

        if ( CRM_Core_Permission::access( 'CiviEvent' ) ) {
            require_once 'CRM/Event/BAO/Query.php';
            $participantFields =& CRM_Event_BAO_Query::getParticipantFields( true );
            if ( ! empty( $participantFields ) ) {
                unset($participantFields['external_identifier'] );
                unset($participantFields['event_id'] );
                unset($participantFields['participant_contact_id'] );
                unset($participantFields['participant_is_test'] );
                unset($participantFields['participant_fee_level'] );
                unset($participantFields['participant_id'] );
                unset($participantFields['participant_is_pay_later'] );
                $fields['Participant'] =& $participantFields;
            }
        }
        
        if ( CRM_Core_Permission::access( 'CiviMember' ) ) {
            require_once 'CRM/Member/BAO/Membership.php';
            $membershipFields =& CRM_Member_BAO_Membership::getMembershipFields(); 
            unset( $membershipFields['membership_id'] );
            unset( $membershipFields['join_date'] );
            unset( $membershipFields['membership_start_date'] );
            unset( $membershipFields['membership_type_id'] );
            unset( $membershipFields['membership_end_date'] );
            unset( $membershipFields['member_is_test'] );
            unset( $membershipFields['is_override'] );
            unset( $membershipFields['status_id'] );
            unset( $membershipFields['member_is_pay_later'] );
            $fields['Membership'] =& $membershipFields;
        }
        
        $activityFields = CRM_Activity_BAO_Activity::exportableFields( 'Activity' );
        if ( ! empty( $activityFields ) ) {
            unset( $activityFields['activity_id'] );
            unset( $activityFields['source_contact_id'] ); 
            unset( $activityFields['is_test'] );
            unset( $activityFields['activity_type_id'] );
            unset( $activityFields['is_current_revision'] );
            unset( $activityFields['is_deleted'] );
            $fields['Activity'] = $activityFields;
        }
        
        $noSearchable = array();
        $addressCustomFields = array_keys( CRM_Core_BAO_CustomField::getFieldsForImport( 'Address' ) );

        foreach ( $fields as $key => $value ) {
            foreach ( $value as $key1 => $value1 ) {
                //CRM-2676, replacing the conflict for same custom field name from different custom group.
                require_once 'CRM/Core/BAO/CustomField.php';
                if ( $customFieldId = CRM_Core_BAO_CustomField::getKeyID( $key1 ) ) {
                    $customGroupId   = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_CustomField', $customFieldId, 'custom_group_id' );
                    $customGroupName = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_CustomGroup', $customGroupId, 'title' );
                    $this->_mapperFields[$key][$key1] = $value1['title'] . ' :: ' . $customGroupName; 
                    if ( in_array( $key1, $addressCustomFields ) ) {
                        $noSearchable[] = $value1['title'] . ' :: ' . $customGroupName;
                    }
                }else {
                    $this->_mapperFields[$key][$key1] = $value1['title'];
                }
                $hasLocationTypes[$key][$key1] = CRM_Utils_Array::value( 'hasLocationType', $value1 );

                // hide the 'is searchable' field for 'File' custom data
                if ( isset( $value1['data_type'] ) && 
                     isset( $value1['html_type'] ) && 
                     ( ( $value1['data_type'] == 'File' && $value1['html_type'] == 'File' ) 
                       || ( $value1['data_type'] == 'Link' && $value1['html_type'] == 'Link' ) )
                     ) {
                    if ( ! in_array( $value1['title'], $noSearchable ) ) {
                        $noSearchable[] = $value1['title'];
                    }
                }
            }
        }
        $this->assign( 'noSearchable', $noSearchable );

        require_once 'CRM/Core/BAO/LocationType.php';
        $this->_location_types =& CRM_Core_PseudoConstant::locationType();        
        $defaultLocationType =& CRM_Core_BAO_LocationType::getDefault();
        
       /* FIXME: dirty hack to make the default option show up first.  This
        * avoids a mozilla browser bug with defaults on dynamically constructed
        * selector widgets. */
        
        if ( $defaultLocationType ) { 
            $defaultLocation = $this->_location_types[$defaultLocationType->id];
            unset( $this->_location_types[$defaultLocationType->id] );
            $this->_location_types = array( $defaultLocationType->id => $defaultLocation ) +  $this->_location_types;
        }
        
        $this->_location_types = array ('Primary') + $this->_location_types;

        $contactTypes = !empty($contactTypes) ? array( 'Contact' => 'Contacts' ) + $contactTypes : array( );
        $sel1 = array( '' => '- select -' ) + $contactTypes;

        if ( CRM_Core_Permission::access( 'Quest' ) ) {
            $sel1['Student'] = 'Students';
        }
        
        if ( ! empty( $activityFields ) ) {
            $sel1['Activity'] = 'Activity';
        }
        
        if ( CRM_Core_Permission::access( 'CiviEvent' ) ) {
            $sel1['Participant'] = 'Participants';
        }
        
        if ( ! empty( $contribFields ) ) {
            $sel1['Contribution'] = 'Contributions';
        }
        
        if ( ! empty($membershipFields) ) {
            $sel1['Membership'] = 'Membership';
        }
        
        foreach ( $sel1 as $key => $sel ) {
            if ( $key ) {
                $sel2[$key] = $this->_mapperFields[$key];
            }
        } 
        $sel3[''] = null;
        $phoneTypes = CRM_Core_PseudoConstant::phoneType();
        ksort( $phoneTypes );
             
        foreach ( $sel1 as $k => $sel ) {
            if ( $k ) {
                foreach ( $this->_location_types as $key => $value ) {                        
                    $sel4[$k]['phone'][$key] =& $phoneTypes;
                }
            }
        } 
        
        foreach ( $sel1 as $k => $sel ) {
            if ( $k ) {
                if ( is_array( $this->_mapperFields[$k] ) ) {
                    foreach ( $this->_mapperFields[$k] as $key => $value ) {
                        if ( $hasLocationTypes[$k][$key] ) {
                            $sel3[$k][$key] = $this->_location_types;
                        } else {
                            $sel3[$key] = null;
                        }
                    }
                }
            }
        }
       
        $this->_defaults = array();
        $js = "<script type='text/javascript'>\n";
        $formName = "document.{$this->_name}";
        
        $alreadyMixProfile = false;
        if ( CRM_Core_BAO_UFField::checkProfileType( $this->_gid ) ) {
            $alreadyMixProfile = true;
        }
        $this->assign( 'alreadyMixProfile', $alreadyMixProfile );
        
        $extra = array( 'onclick' => 'showLabel();mixProfile();', 
                        'onblur'  => 'showLabel();mixProfile();' );
        
        $sel =& $this->addElement( 'hierselect', 'field_name', ts('Field Name'), $extra );  

        $formValues = array();
        $formValues = $this->exportValues( );

        if ( empty( $formValues ) ) {
            for ( $k = 1; $k < 4; $k++ ) {
                if ( !$defaults['field_name'][$k] ) {
                    $js .= "{$formName}['field_name[$k]'].style.display = 'none';\n"; 
                }
            }
        } else {
            if ( !empty( $formValues['field_name'] ) ) {
                foreach ( $formValues['field_name'] as $value ) {
                    for ( $k = 1; $k < 4; $k++ ) {
                        if (! isset( $formValues['field_name'][$k] ) || ! $formValues['field_name'][$k] ) {
                            $js .= "{$formName}['field_name[$k]'].style.display = 'none';\n"; 
                        } else {
                            $js .= "{$formName}['field_name[$k]'].style.display = '';\n"; 
                        }
                    }
                }
            } else {
                for ( $k = 1; $k < 4; $k++ ) {
                    if ( ! isset($defaults['field_name'][$k] ) ) {
                         $js .= "{$formName}['field_name[$k]'].style.display = 'none';\n"; 
                     }
                }
            } 
        }
        
        foreach ( $sel2 as $k => $v ) {
            if ( is_array( $sel2[$k] ) ) {
             asort( $sel2[$k] );              
            } 
        }

        $sel->setOptions( array( $sel1, $sel2, $sel3, $sel4 ) );
        
        $js .= "</script>\n";
        $this->assign( 'initHideBoxes', $js );
        
        $this->add( 'select', 
                    'visibility', 
                    ts('Visibility'),
                    CRM_Core_SelectValues::ufVisibility( ), 
                    true,
                    array( 'onChange' => "showHideSeletorSearch(this.value);" ) );
        
        //CRM-4363
        $js = array( 'onclick' => "mixProfile();" );
        // should the field appear in selectors (as a column)?
        $this->add( 'checkbox', 'in_selector', ts('Results Column?'), null, null, $js );
        $this->add( 'checkbox', 'is_searchable', ts( 'Searchable?'), null, null, $js );
         
        $attributes = CRM_Core_DAO::getAttribute( 'CRM_Core_DAO_UFField' );

        // weight
        $this->add( 'text', 'weight', ts('Order'), $attributes['weight'], true );
        $this->addRule( 'weight', ts('is a numeric field'), 'numeric' );
        
        $this->add( 'textarea', 'help_post', ts('Field Help'), $attributes['help_post'] );
       
        $this->add( 'checkbox', 'is_required', ts( 'Required?') );
        $this->add( 'checkbox', 'is_active', ts( 'Active?' ) );
        $this->add( 'checkbox', 'is_view', ts( 'View Only?' ) );
        // $this->add( 'checkbox', 'is_registration', ts( 'Display in Registration Form?' ) );
        //$this->add( 'checkbox', 'is_match'       , ts( 'Key to Match Contacts?'        ) );

        $this->add( 'text', 'label', ts('Field Label'), $attributes['label'] );
        
        $js = null;
        if ( $this->_hasSearchableORInSelector ) {
            $js = array( 'onclick' => "return verify( );" );    
        }
        
        // add buttons
        $this->addButtons( array(
                                 array ( 'type'      => 'next',
                                         'name'      => ts('Save'),
                                         'isDefault' => true,
                                         'js'        => $js ),
                                 array ( 'type'      => 'next',
                                         'name'      => ts('Save and New'),
                                         'subName'   => 'new', 
                                         'js'        => $js ),
                                 array ( 'type'      => 'cancel',
                                         'name'      => ts('Cancel') ),
                                 )
                           );
        
        $this->addFormRule( array( 'CRM_UF_Form_Field', 'formRule' ), $this );

        // if view mode pls freeze it with the done button.
        if ( $this->_action & CRM_Core_Action::VIEW ) {
            $this->freeze();
            $this->addElement( 'button', 'done', ts('Done'), 
                               array('onclick' => "location.href='civicrm/admin/uf/group/field?reset=1&action=browse&gid=" . $this->_gid . "'"));
        }

        if ( CRM_Utils_Array::value( 1, $defaults['field_name'] ) == 'url-1' ) {
            $defaults['field_name'][1] = 'url' ; 
        }
        
        $this->setDefaults( $defaults );
    }

    /**
     * Process the form
     *
     * @return void
     * @access public
     */
    public function postProcess()
    {
        $ids = array( 'uf_group' => $this->_gid );
        if ( $this->_action & CRM_Core_Action::DELETE ) {
            $fieldValues = array( 'uf_group_id' => $this->_gid );
            $wt = CRM_Utils_Weight::delWeight( 'CRM_Core_DAO_UFField', $this->_id, $fieldValues );
            $deleted = CRM_Core_BAO_UFField::del( $this->_id );
            
            //update group_type every time. CRM-3608 
            if ( $this->_gid && $deleted ) { 
                //get the profile type.
                $groupType = 'null';
                $fieldsType = CRM_Core_BAO_UFGroup::calculateGroupType( $this->_gid );
                if ( !empty( $fieldsType ) ) {
                    $groupType = implode( ',', $fieldsType );
                }
                //set group type
                CRM_Core_DAO::setFieldValue( 'CRM_Core_DAO_UFGroup', $this->_gid, 'group_type', $groupType );
            }
            
            CRM_Core_Session::setStatus(ts('Selected Profile Field has been deleted.'));
            return;
        }
        
        // store the submitted values in an array
        $params = $this->controller->exportValues( 'Field' );
        if ( $params['visibility'] == 'User and User Admin Only' ) {
            $params['is_searchable'] = $params['in_selector'] = 0; 
        }
                
        if ( $this->_action & CRM_Core_Action::UPDATE ) {
            $ids['uf_field'] = $this->_id;
        }
        
        // temporary hack to for website
        if ( $params['field_name'][1] == 'url' ) {
            $params['field_name'][1] = 'url-1';
        }
        
        //check for duplicate fields
        if ( CRM_Core_BAO_UFField::duplicateField( $params, $ids ) ) {
            CRM_Core_Session::setStatus( ts('The selected field was not added. It already exists in this profile.') );
            return;
        } else {
            $ufField = CRM_Core_BAO_UFField::add( $params, $ids );
            $name = $this->_selectFields[$ufField->field_name];
             
            //reset other field is searchable and in selector settings, CRM-4363
            if ( $this->_hasSearchableORInSelector && 
                 in_array( $ufField->field_type, array( 'Participant', 'Contribution', 'Membership', 'Activity' ) ) ) {
                CRM_Core_BAO_UFField::resetInSelectorANDSearchable( $this->_gid );
            }
            
            $config = CRM_Core_Config::singleton( );
            $showBestResult = false;
            if ( in_array( $ufField->field_name, array( 'country', 'state_province' ) ) && count( $config->countryLimit ) > 1 ) {
                // get state or country field weight if exists
                $field = 'state_province';
                if ( $ufField->field_name == 'state_province' ) {
                    $field = 'country';
                }
                $ufFieldDAO = new CRM_Core_DAO_UFField();
                $ufFieldDAO->field_name = $field;
                $ufFieldDAO->location_type_id = $ufField->location_type_id;
                $ufFieldDAO->uf_group_id = $ufField->uf_group_id;
                    
                if ( $ufFieldDAO->find( true ) ) {
                    if ( $field == 'country' && $ufFieldDAO->weight > $ufField->weight ) {
                        $showBestResult = true;
                    } elseif ( $field == 'state_province' && $ufFieldDAO->weight < $ufField->weight ) {
                        $showBestResult = true;
                    }
                } 
            }
            
            //update group_type every time. CRM-3608 
            if ( $this->_gid && is_a( $ufField, 'CRM_Core_DAO_UFField' ) ) {
                //get the profile type.
                $groupType = 'null';
                $fieldsType = CRM_Core_BAO_UFGroup::calculateGroupType( $this->_gid );
                if ( !empty( $fieldsType ) ) {
                    $groupType = implode( ',', $fieldsType );
                }
                //set group type
                CRM_Core_DAO::setFieldValue( 'CRM_Core_DAO_UFGroup', $this->_gid, 'group_type', $groupType );
            }
            CRM_Core_Session::setStatus( ts( 'Your CiviCRM Profile Field \'%1\' has been saved to \'%2\'.', 
                                             array( 1 => $name, 2 => $this->_title ) ) );
        }
        $buttonName = $this->controller->getButtonName( );
        
        $session = CRM_Core_Session::singleton( );
        if ( $buttonName == $this->getButtonName( 'next', 'new' ) ) {
            CRM_Core_Session::setStatus( ts(' You can add another profile field.') );
            $session->replaceUserContext( CRM_Utils_System::url( 'civicrm/admin/uf/group/field/add', 
                                                                 "reset=1&action=add&gid={$this->_gid}&sbr={$showBestResult}" ) );
        } else {
            $session->replaceUserContext( CRM_Utils_System::url( 'civicrm/admin/uf/group/field', 
                                                                 "reset=1&action=browse&gid={$this->_gid}" ) );
            $session->set( 'showBestResult', $showBestResult );
        }
    }

    /**
     * validation rule for subtype. 
     *
     * @param array $groupType contains all groupTypes.
     *
     * @param  string  $fieldType type of field.
     *
     * @param array $errors 
     *
     * @return array list of errors to be posted back to the form
     * @static
     * @access public
     */
    static function formRuleSubType( $fieldType, $groupType, $errors )
    { 
        if ( in_array( $fieldType, array( 'Participant', 'Contribution', 'Membership', 'Activity' ) ) ) {
            $individualSubTypes  = CRM_Contact_BAO_ContactType::subTypes( 'Individual' );
            foreach( $groupType as $value ) {
                if ( !in_array( $value, $individualSubTypes ) && 
                     !in_array( $value, array( 'Participant', 'Contribution', 'Membership',
                                               'Individual', 'Contact', 'Activity' ) ) ) {
                    $errors['field_name'] = 
                        ts( 'Cannot add or update profile field "%1" with combination of Household or Organization or any subtypes of Household or Organisation.', array( 1 => $fieldType ) );
                    break;
                }
            }  
        } else {
            $basicType = CRM_Contact_BAO_ContactType::getBasicType( $groupType );
            if( $basicType ) {
                if ( !is_array( $basicType ) ) {
                    $basicType = array(  $basicType );
                }
                if( !in_array( $fieldType, $basicType ) ) { 
                    $errors['field_name'] = 
                        ts( 'Cannot add or update profile field type "%1" with combination of subtype other than "%1".', 
                            array( 1 => $fieldType ) );
                }
            }
        }  
    }
    
    /**
     * global validation rules for the form
     *
     * @param array $fields posted values of the form
     *
     * @return array list of errors to be posted back to the form
     * @static
     * @access public
     */
    static function formRule( $fields, $files, $self ) 
    {
        $is_required     = CRM_Utils_Array::value( 'is_required'    , $fields, false );
        $is_registration = CRM_Utils_Array::value( 'is_registration', $fields, false );
        $is_view         = CRM_Utils_Array::value( 'is_view'        , $fields, false );
        $in_selector     = CRM_Utils_Array::value( 'in_selector'    , $fields, false );
        $is_searchable   = CRM_Utils_Array::value( 'is_searchable'  , $fields, false );
        $visibility      = CRM_Utils_Array::value( 'visibility'     , $fields, false );
        $is_active       = CRM_Utils_Array::value( 'is_active'      , $fields, false );
       
        $errors = array( );
        if ( $is_view && $is_registration ) {
            $errors['is_registration'] = ts( 'View Only cannot be selected if this field is to be included on the registration form' );
        }
        if ( $is_view && $is_required ) {
            $errors['is_view'] = ts( 'A View Only field cannot be required' );
        }
  
        $fieldName = $fields['field_name'][0];
        if (!$fieldName) {
            $errors['field_name'] = ts( 'Please select a field name' );
        }
        
        if ( $in_selector && in_array( $fieldName, array('Contribution', 'Participant', 'Membership', 'Activity' ) ) ) {
            $errors['in_selector'] = ts( "'In Selector' cannot be checked for %1 fields.", array( 1 => $fieldName ) );
        }
        
        if (! empty( $fields['field_id'] ) ) {
            //get custom field id 
            $customFieldId = explode( '_', $fieldName );
            if ( $customFieldId[0] == 'custom' ) {
                $customField = new CRM_Core_DAO_CustomField();
                $customField->id = $customFieldId[1];
                $customField->find(true);
                
                if ( !$customField->is_active && $is_active ) {
                    $errors['field_name'] = ts( 'Cannot set this field "Active" since the selected custom field is disabled.' );
                }
            }
         }

        //check profile is configured for double option process
        //adding group field, email field should be present in the group 
        //fixed for  issue CRM-2861 & CRM-4153
        $config = CRM_Core_Config::singleton( );
        if ( $config->profileDoubleOptIn ) { 
            if ( $fields['field_name'][1] == 'group' ) {
                require_once 'CRM/Core/BAO/UFField.php';
                $dao = new CRM_Core_BAO_UFField();
                $dao->uf_group_id = $fields['group_id'];
                $dao->find( );
                $emailField = false ;
                while ( $dao->fetch( ) ) {
                    //check email field is present in the group
                    if ( $dao->field_name == 'email' ) {
                        $emailField = true;
                    }
                } 
                if ( ! $emailField ) {
                    $disableSetting = "define( 'CIVICRM_PROFILE_DOUBLE_OPTIN' , 0 );"; 
                    $errors['field_name'] = ts( 'Your site is currently configured to require double-opt in when users join (subscribe) to Group(s) via a Profile form. In this mode, you need to include an Email field in a Profile BEFORE you can add the Group(s) field. This ensures that an opt-in confirmation email can be sent. Your site administrator can disable double opt-in by adding this line to the CiviCRM settings file: <em>%1</em>', array( 1 => $disableSetting ) );
                }
            }
        }
        
        //fix for CRM-3037
        $fieldType = $fields['field_name'][0];
        
        //get the group type. 
        $groupType = CRM_Core_BAO_UFGroup::calculateGroupType( $self->_gid, CRM_Utils_Array::value( 'field_id', $fields ) );
        
        switch ( $fieldType ) {
        
        case 'Contact' :
            if ( in_array( 'Activity', $groupType ) ) {
                $errors['field_name'] = 
                    ts( 'Cannot add or update profile field type Contact with combination of Activity' ); 
            } else {
                self::formRuleSubType( $fieldType, $groupType, $errors );
            }
            break;    
        case 'Individual' :
            if ( in_array( 'Activity', $groupType ) || in_array( 'Household', $groupType ) || in_array( 'Organization', $groupType ) ) {
                $errors['field_name'] = 
                    ts( 'Cannot add or update profile field type Individual with combination of Household or Organization or Activity' ); 
            } else {
                self::formRuleSubType( $fieldType, $groupType, $errors );
            }
            break;
        case 'Household' :
            if ( in_array( 'Activity', $groupType ) || in_array( 'Individual', $groupType ) || in_array( 'Organization', $groupType ) ) {
                $errors['field_name'] = 
                    ts( 'Cannot add or update profile field type Household with combination of Individual or Organization or Activity' ); 
            } else {
                self::formRuleSubType( $fieldType, $groupType, $errors );
            }
            break;
        case 'Organization' :
            if ( in_array( 'Activity', $groupType ) || in_array( 'Household', $groupType ) || in_array( 'Individual', $groupType ) ) {
                $errors['field_name'] = 
                    ts( 'Cannot add or update profile field type Organization with combination of Household or Individual or Activity' ); 
            } else {
                self::formRuleSubType( $fieldType, $groupType, $errors );
            }
            break;
        case 'Activity' :
                if ( in_array( 'Individual', $groupType ) || in_array( 'Membership', $groupType ) || in_array( 'Contribution', $groupType )
                 || in_array( 'Organization', $groupType ) || in_array( 'Household', $groupType ) || in_array( 'Participant', $groupType )) {
                $errors['field_name'] = 
                    ts( 'Cannot add or update profile field type Activity with combination Participant or Membership or Contribution or Household or Organization or Individual' ); 
            } else {
                self::formRuleSubType( $fieldType, $groupType, $errors );
            }
            break;   
        case 'Participant' :
            if ( in_array( 'Membership', $groupType ) || in_array( 'Contribution', $groupType )
                 || in_array( 'Organization', $groupType ) || in_array( 'Household', $groupType ) || in_array( 'Activity', $groupType ) ) {
                $errors['field_name'] = 
                    ts( 'Cannot add or update profile field type Participant with combination of Activity or Membership or Contribution or Household or Organization or Activity' ); 
            } else {
                self::formRuleSubType( $fieldType, $groupType, $errors );
            }
            break;
        case 'Contribution' :
            if ( in_array( 'Participant', $groupType ) || in_array( 'Membership', $groupType ) 
                 || in_array( 'Organization', $groupType ) || in_array( 'Household', $groupType ) || in_array( 'Activity', $groupType ) ) {
                $errors['field_name'] = 
                    ts( 'Cannot add or update profile field type Contribution with combination of Activity or Membership or Participant or Household or Organization' ); 
            }  else { 
                self::formRuleSubType( $fieldType, $groupType, $errors );
            }
            break;
        case 'Membership' :
            if ( in_array( 'Participant', $groupType ) || in_array( 'Contribution', $groupType )
                 || in_array( 'Organization', $groupType ) || in_array( 'Household', $groupType ) || in_array( 'Activity', $groupType ) ) {
                $errors['field_name'] = 
                    ts( 'Cannot add or update profile field type Membership with combination of Activity or Participant or Contribution or Household or Organization' ); 
            } else {
                self::formRuleSubType( $fieldType, $groupType ,$errors );
            } 
            break;
        default:
            $profileType = CRM_Core_BAO_UFField::getProfileType( $fields['group_id'], true, false, true );
            if ( CRM_Contact_BAO_ContactType::isaSubType( $fieldType ) ) {
                if ( CRM_Contact_BAO_ContactType::isaSubType( $profileType ) ) {
                    if ( $fieldType != $profileType ) {
                        $errors['field_name'] = 
                            ts('Cannot add or update profile field type "%1" with combination of "%2".', array(1 => $fieldType, 2 => $profileType));
                    }
                } else {
                    $basicType = CRM_Contact_BAO_ContactType::getBasicType( $fieldType );
                    if ( $profileType && 
                         $profileType != $basicType  &&
                         $profileType != 'Contact'   ) {
                        $errors['field_name'] = 
                            ts('Cannot add or update profile field type "%1" with combination of "%2".', array(1 => $fieldType, 2 => $profileType));
                    }
                }        
            } elseif( $fields['field_name'][1] == 'contact_sub_type' && 
                      !in_array( $profileType, array('Individual', 'Household', 'Organization') ) &&
                      !in_array( $profileType, CRM_Contact_BAO_ContactType::subTypes() )  ) { 
                
                $errors['field_name'] = 
                    ts( 'Cannot add or update profile field Contact Subtype as profile type is not one of Individual, Household or Organization.' );
            }
        }
        
        return empty($errors) ? true : $errors;
    }
    
}


