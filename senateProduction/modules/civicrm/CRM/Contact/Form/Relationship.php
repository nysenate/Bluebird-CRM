<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
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

require_once 'CRM/Core/SelectValues.php';
require_once 'CRM/Core/Form.php';
require_once 'CRM/Contact/Form/Edit/Notes.php';
require_once 'CRM/Custom/Form/CustomData.php';

/**
 * This class generates form components for relationship
 * 
 */
class CRM_Contact_Form_Relationship extends CRM_Core_Form
{
    /**
     * max number of contacts we will display for a relationship
     */
    const MAX_RELATIONSHIPS = 50;
          

    /**
     * The relationship id, used when editing the relationship
     *
     * @var int
     */
    protected $_relationshipId;
    
    /**
     * The contact id, used when add/edit relationship
     *
     * @var int
     */
    protected $_contactId;
    
    /**
     * This is a string which is either a_b or  b_a  used to determine the relationship between to contacts
     *
     */
    protected $_rtype;
    /**
     * This is a string which is used to determine the relationship between to contacts
     *
     */
    protected $_rtypeId;
    
    /**
     * Display name of contact a
     *
     */
    protected $_display_name_a;

     /**
     * Display name of contact b
     *
     */
    protected $_display_name_b;
    
    /**
     * The relationship type id
     *
     * @var int
     */
    protected $_relationshipTypeId;
    
    /**
     * an array of all relationship names
     *
     * @var array
     */
    protected $_allRelationshipNames;
    
    /**
     * The relationship values if Updating relationship
     */
    protected $_values;
    
    /**
     * casid if it called from case context 
     */
    protected $_caseId;
    
    function preProcess( ) 
    {
        //custom data related code
        $this->_cdType     = CRM_Utils_Array::value( 'type', $_GET );
        $this->assign('cdType', false);
        if ( $this->_cdType ) {
            $this->assign('cdType', true);
            return CRM_Custom_Form_CustomData::preProcess( $this );
        }

        $this->_contactId      = $this->get('contactId');
        
        $this->_relationshipId = $this->get('id');
        
        $this->_rtype          = CRM_Utils_Request::retrieve( 'rtype', 'String', $this );
        
        $this->_rtypeId        = CRM_Utils_Request::retrieve( 'relTypeId', 'String', $this );
        
        $this->_display_name_a = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact', $this->_contactId, 'display_name' );
        
        $this->assign('sort_name_a', $this->_display_name_a);
        
        $this->_caseId = CRM_Utils_Request::retrieve( 'caseID', 'Integer', $this );
        
        //get the relationship values.
        $this->_values = array( );
        if ( $this->_relationshipId ) {
            $params = array( 'id' => $this->_relationshipId ); 
            CRM_Core_DAO::commonRetrieve( 'CRM_Contact_DAO_Relationship', $params, $this->_values );
        }
        
        if ( ! $this->_rtypeId ) { 
            $params = $this->controller->exportValues( $this->_name );
            if ( isset($params['relationship_type_id']) ) {
                $this->_rtypeId = $params['relationship_type_id'];
            } else if ( !empty( $this->_values ) ) {
                $this->_rtypeId = $this->_values['relationship_type_id'] . '_' . $this->_rtype;
            }
        }
        
        //get the relationship type id 
        $this->_relationshipTypeId = str_replace( array('_a_b', '_b_a'), array('', ''), $this->_rtypeId );
        
        //get the relationship type 
        if ( !$this->_rtype ) {
            $this->_rtype = str_replace( $this->_relationshipTypeId . '_', '', $this->_rtypeId );
        }
        $this->assign( "rtype", $this->_rtype );
        
        require_once 'CRM/Core/PseudoConstant.php';
        
        //use name as it remain constant, CRM-3336
        $this->_allRelationshipNames = CRM_Core_PseudoConstant::relationshipType( 'name' );
        
        // when custom data is included in this page
        if ( CRM_Utils_Array::value( "hidden_custom", $_POST ) ) {
            CRM_Custom_Form_Customdata::preProcess( $this );
            CRM_Custom_Form_Customdata::buildQuickForm( $this );
            CRM_Custom_Form_Customdata::setDefaultValues( $this );
        }
    }
    
    /**
     * This function sets the default values for the form. Relationship that in edit/view mode
     * the default values are retrieved from the database
     * 
     * @access public
     * @return None
     */
    function setDefaultValues( ) 
    {
        if ( $this->_cdType ) {
            return CRM_Custom_Form_CustomData::setDefaultValues( $this );
        }
        
        $defaults = array( );

        if ( $this->_action & CRM_Core_Action::UPDATE ) {
            if ( !empty( $this->_values ) ) {
                $defaults['relationship_type_id'] = $this->_rtypeId;
                if ( CRM_Utils_Array::value( 'start_date', $this->_values ) ) {
                    list( $defaults['start_date'] ) = CRM_Utils_Date::setDateDefaults( $this->_values['start_date'] );
                }
                if ( CRM_Utils_Array::value( 'end_date', $this->_values ) ) {
                    list( $defaults['end_date'] ) = CRM_Utils_Date::setDateDefaults( $this->_values['end_date'] );
                }
                $defaults['description'         ] = CRM_Utils_Array::value( 'description', $this->_values );
                $defaults['is_active'           ] = CRM_Utils_Array::value( 'is_active', $this->_values );
                $defaults['is_permission_a_b'   ] = CRM_Utils_Array::value( 'is_permission_a_b', $this->_values );
                $defaults['is_permission_b_a'   ] = CRM_Utils_Array::value( 'is_permission_b_a', $this->_values );
                $contact = new CRM_Contact_DAO_Contact( );
                if ( $this->_rtype == 'a_b' && $this->_values['contact_id_a'] == $this->_contactId ) {
                    $contact->id = $this->_values['contact_id_b'];
                } else {
                    $contact->id = $this->_values['contact_id_a'];
                }
                if ($contact->find(true)) {
                    $this->_display_name_b = $contact->display_name;
                    $this->assign('sort_name_b', $this->_display_name_b);
                    
                    //is current employee/employer.
                    if ( $this->_allRelationshipNames[$this->_relationshipTypeId]["name_{$this->_rtype}"] == 'Employee of' &&
                         $contact->id == CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact', $this->_contactId, 'employer_id' ) ) {
                        $defaults['is_current_employer'] = 1;
                        $this->_values['current_employee_id'] = $this->_contactId;
                        $this->_values['current_employer_id'] = $contact->id;
                    } else if ( $this->_allRelationshipNames[$this->_relationshipTypeId]["name_{$this->_rtype}"] == 'Employer of' && 
                                $this->_contactId == $contact->employer_id ) {
                        $defaults['is_current_employer'] = 1;
                        $this->_values['current_employee_id'] = $contact->id;
                        $this->_values['current_employer_id'] = $this->_contactId;
                    }
                }
                
                $relationshipID = $this->_values['id'];
                $query = "SELECT id, note FROM civicrm_note where entity_table = 'civicrm_relationship' and entity_id = $relationshipID  order by modified_date desc";
                $dao = new CRM_Core_DAO();
                $dao->query($query);
                if ( $dao->fetch($query) ) {
                    $defaults['note'] = $dao->note;
                }
            }
        } else {
            $defaults['is_active'           ] = 1;
            $defaults['relationship_type_id'] = $this->_rtypeId;
        }

        return $defaults;
    }
    

    /**
     * This function is used to add the rules for form.
     *
     * @return None
     * @access public
     */
    function addRules( )
    {
        if ( $this->_cdType ) {
            return;
        }
        
        if ( !($this->_action & CRM_Core_Action::DELETE) ) {
            $this->addRule('relationship_type_id', ts('Please select a relationship type.'), 'required' );

            // add a form rule only when creating a new relationship
            // edit is severely limited, so add a simpleer form rule
            if ( $this->_action & CRM_Core_Action::ADD ) {
                $this->addFormRule( array( 'CRM_Contact_Form_Relationship', 'formRule' ), $this );
                $this->addFormRule( array( 'CRM_Contact_Form_Relationship', 'dateRule' ) );
            } else if ( $this->_action & CRM_Core_Action::UPDATE ) {
                $this->addFormRule( array( 'CRM_Contact_Form_Relationship', 'dateRule' ) );
            }
        }
    }


    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) 
    {
        if ( $this->_cdType ) {
            return CRM_Custom_Form_CustomData::buildQuickForm( $this );
        }
        
        $relTypeID = explode('_', $this->_rtypeId, 3);

        if ( $this->_action & CRM_Core_Action::DELETE ) {
            
            $this->addButtons( array(
                                     array ( 'type'      => 'next',
                                             'name'      => ts('Delete'),
                                             'isDefault' => true   ),
                                     array ( 'type'      => 'cancel',
                                             'name'      => ts('Cancel') ),
                                     )
                               );
            return;
        }
               
        $callAjax   = $this->get( 'callAjax' );
        
        $searchRows = null;
        if ( !$callAjax ) {
            $searchRows = $this->get( 'searchRows' );
        } else {

            $this->addElement( 'hidden', 'store_contacts', '' , array('id'=> 'store_contacts'));
            $sourceUrl  = 'snippet=4&relType='. $this->get('relType');
            $sourceUrl .= '&relContact='. $this->get('relContact');
            $sourceUrl .= '&cid='.$this->_contactId;

            $this->assign( 'searchCount', true );
            
            // To handle employee of and employer of
            if ( !empty($this->_relationshipTypeId) &&
                 !empty($this->_rtype) ) {
                $sourceUrl .= '&typeName='.$this->_allRelationshipNames[$this->_relationshipTypeId]["name_{$this->_rtype}"];
            }    
            $this->assign( 'sourceUrl', CRM_Utils_System::url( 'civicrm/ajax/relationshipcontacts',  $sourceUrl, false, null, false) );
        }

        $this->assign( 'callAjax', $callAjax);
        $this->_callAjax = $callAjax;

        $this->addElement('select',
                          'relationship_type_id',
                          ts('Relationship Type'),
                          array('' => ts('- select -')) +
                          CRM_Contact_BAO_Relationship::getContactRelationshipType( $this->_contactId,
                                                                                    $this->_rtype,
                                                                                    $this->_relationshipId,
                                                                                    null, false, 'label' )
                          );

        // add a ajax facility for searching contacts
		$dataUrl = CRM_Utils_System::url( "civicrm/ajax/search", "reset=1", true, null, false );
		$this->assign('dataUrl',$dataUrl );
        $this->add('text', 'rel_contact', ts('Find Target Contact') );
        $this->add('hidden', "rel_contact_id" );
        $this->addDate( 'start_date', ts('Start Date'), false, array( 'formatType' => 'searchDate' ) );
        $this->addDate( 'end_date'  , ts('End Date')  , false, array( 'formatType' => 'searchDate' ) );
        $this->addElement('advcheckbox', 'is_active', ts('Enabled?'), null, 'setChecked()');
        
        $this->addElement('checkbox', 'is_permission_a_b', ts( 'Permission for contact a to view and update information for contact b' ) , null);
        $this->addElement('checkbox', 'is_permission_b_a', ts( 'permission for contact b to view and update information for contact a' ) , null);

        $this->add('text', 'description', ts('Description'), CRM_Core_DAO::getAttribute( 'CRM_Contact_DAO_Relationship', 'description' ) );
        
        CRM_Contact_Form_Edit_Notes::buildQuickForm($this);
        
        $searchCount           = $this->get( 'searchCount'   );
        $duplicateRelationship = $this->get( 'duplicateRelationship' );
        $searchDone            = $this->get( 'searchDone' );
        
        $isEmployeeOf = $isEmployerOf = false; 
        if ( ! empty( $this->_relationshipTypeId ) &&
             ! empty( $this->_rtype ) ) {
            if ( $this->_allRelationshipNames[$this->_relationshipTypeId]["name_{$this->_rtype}"] == 'Employee of' ) { 
                $isEmployeeOf = true;
            } else if ( $this->_allRelationshipNames[$this->_relationshipTypeId]["name_{$this->_rtype}"] == 'Employer of'  ) {
                $isEmployerOf = true; 
            }
        }
        
        $employers = $checkBoxes = $employees = array( );
        if ( $searchRows ) {
            foreach ( $searchRows as $id => $row ) {
                $checkBoxes[$id] = $this->createElement('checkbox', $id, null, '' );
                if ( $isEmployeeOf ) {
                    $employers[$id] = $this->createElement('radio', null, $id, null, $id);
                } else if ( $isEmployerOf ) {
                    $employees[$id] = $this->createElement('checkbox', $id, null, '' );
                }
            }

            $this->addGroup($checkBoxes, 'contact_check');
            $this->assign('searchRows', $searchRows );
        }
       
        if ( $isEmployeeOf ) {
            $this->assign('isEmployeeOf', $isEmployeeOf );
            if ( !$callAjax ) { 
                $this->addGroup($employers, 'employee_of');
            } 
        } else if ( $isEmployerOf ) {
            $this->assign('isEmployerOf', $isEmployerOf );
            if ( !$callAjax ) { 
                $this->addGroup($employees, 'employer_of');
            }
        }
        
        if ( $callAjax && ( $isEmployeeOf || $isEmployerOf ) ) {
            $this->addElement( 'hidden', 'store_employers', '' , array('id'=> 'store_employers'));
        }

        if ( $this->_action & CRM_Core_Action::UPDATE ) {
            $this->addElement('checkbox', 'is_current_employer' );
        }
                
        $this->assign('duplicateRelationship', $duplicateRelationship);
        $this->assign('searchCount'          , $searchCount);
        $this->assign('searchDone'           , $searchDone);
        
        if ( $this->get('contact_type') ) {
            require_once 'CRM/Contact/BAO/ContactType.php';
            $typeLabel = CRM_Contact_BAO_ContactType::getLabel( $this->get('contact_type') );
            $this->assign('contact_type'         , $this->get('contact_type') );
            $this->assign('contact_type_display' , $typeLabel );
        }
        
        if ( $searchDone ) {
            $searchBtn = ts('Search Again');
        } else {
            $searchBtn = ts('Search');
        }
        $this->addElement( 'submit', $this->getButtonName('refresh'), $searchBtn, array( 'class' => 'form-submit', 'id' => 'search-button' ) );
        $this->addElement( 'submit', $this->getButtonName('refresh', 'save'), 'Quick Save', array( 'class' => 'form-submit' , 'id' => 'quick-save') );
        $this->addElement( 'submit', $this->getButtonName('cancel' ), ts('Cancel'), array( 'class' => 'form-submit' ) );

        $this->addElement( 'submit', $this->getButtonName('refresh', 'savedetails'), 'Save Relationship', array( 'class' => 'form-submit hiddenElement' , 'id' => 'details-save') );
        $this->addElement('checkbox', 'add_current_employer', ts('Current Employer'), null );
        $this->addElement('checkbox', 'add_current_employee', ts('Current Employee'), null );
       
        //need to assign custom data type and subtype to the template
        $this->assign('customDataType', 'Relationship');
        $this->assign('customDataSubType',  $this->_relationshipTypeId );
        $this->assign('entityID',  $this->_relationshipId );
       
        // make this form an upload since we dont know if the custom data injected dynamically
        // is of type file etc $uploadNames = $this->get(
        // 'uploadNames' );
        $buttonParams =  array ( 'type'      => 'upload',
                                 'name'      => ts('Save Relationship'),
                                 'isDefault' => true   );
        if ( $callAjax ) {
            $buttonParams['js'] = array( 'onclick' => ' submitAjaxData();');
        }
        
        $this->addButtons( array( $buttonParams
                                  ,
                                  array ( 'type'      => 'cancel',
                                          'name'      => ts('Cancel') ),
                                  )
                           );
        
    }

       
    /**
     *  This function is called when the form is submitted 
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {
        // store the submitted values in an array
        $params = $this->controller->exportValues( $this->_name );
        $quickSave = false;
        if ( CRM_Utils_Array::value( '_qf_Relationship_refresh_save', $_POST ) ||
             CRM_Utils_Array::value( '_qf_Relationship_refresh_savedetails', $_POST ) ) {
            $quickSave = true;
        }      
        $this->set( 'searchDone', 0 );
        $this->set( 'callAjax', false );  
        if ( CRM_Utils_Array::value( '_qf_Relationship_refresh', $_POST ) || $quickSave ) {
            if ( is_numeric( $params['rel_contact_id'] ) ) {
                if ( $quickSave ) {
                    $params['contact_check'] = array( $params['rel_contact_id'] => 1 );
                } else {
                    $this->search( $params );
                    $quickSave = false;
                }
            } else {
                $this->set( 'callAjax', true );  
                $this->set( 'relType', $params['relationship_type_id'] );
                $this->set( 'relContact', $params['rel_contact'] );
                $quickSave = false;
            }
            $this->set( 'searchDone', 1 );
            if ( !$quickSave ) {
                return;
            }
        }

        // action is taken depending upon the mode
        $ids = array( );
        $ids['contact'] = $this->_contactId;
        
        // modify params for ajax call
        $this->modifyParams( $params );
       
        if ($this->_action & CRM_Core_Action::DELETE ){
            CRM_Contact_BAO_Relationship::del($this->_relationshipId);
            return;
        }
        
        $relationshipTypeId = str_replace( array('_a_b', '_b_a'), array('', ''), $params['relationship_type_id'] );
        if ( $this->_action & CRM_Core_Action::UPDATE ) {
            $ids['relationship'] = $this->_relationshipId;
            $relation = CRM_Contact_BAO_Relationship::getContactIds( $this->_relationshipId );
            $ids['contactTarget'] = ( $relation->contact_id_a == $this->_contactId ) ?
                $relation->contact_id_b : $relation->contact_id_a;
            
            //if relationship type change and previously it was
            //employer / emplyee relationship with current employer
            //than clear the current employer. CRM-3235.
            if ( CRM_Utils_Array::value( 'current_employee_id', $this->_values ) &&
                 $relationshipTypeId != $this->_values['relationship_type_id'] ) {
                require_once 'CRM/Contact/BAO/Contact/Utils.php';
                CRM_Contact_BAO_Contact_Utils::clearCurrentEmployer( $this->_values['current_employee_id'] );
            }
        } elseif ( $quickSave ) {
            if ( $params['add_current_employee'] &&
                 $this->_allRelationshipNames[$relationshipTypeId]["name_a_b"] == 'Employee of' ) {
                $params['employee_of'] = $params['rel_contact_id'];
            } elseif ( $params['add_current_employer'] &&
                       $this->_allRelationshipNames[$relationshipTypeId]["name_b_a"] == 'Employer of' ) {
                $params['employer_of'] = array( $params['rel_contact_id'] => 1 );
            }
            if ( !$this->_rtype ) {
                $this->_rtype = str_replace( $relationshipTypeId. '_', '', $params['relationship_type_id'] );
            }
        }

        $params['start_date'] = CRM_Utils_Date::processDate( $params['start_date'], null, true );
        $params['end_date']   = CRM_Utils_Date::processDate( $params['end_date'], null, true );

        //special case to handle if all checkboxes are unchecked
        $customFields = CRM_Core_BAO_CustomField::getFields( 'Relationship', false, false, $relationshipTypeId );
        $params['custom'] = CRM_Core_BAO_CustomField::postProcess( $params,
                                                                   $customFields,
                                                                   $this->_relationshipId,
                                                                   'Relationship' );
        
        list( $valid, $invalid, $duplicate, $saved, $relationshipIds ) =
            CRM_Contact_BAO_Relationship::create( $params, $ids );
        
        // if this is called from case view, 
        //create an activity for case role removal.CRM-4480
        if ( $this->_caseId ) {
            require_once "CRM/Case/BAO/Case.php";
            CRM_Case_BAO_Case::createCaseRoleActivity( $this->_caseId, $relationshipIds , $params['contact_check'], $this->_contactId );
        }

        $status = '';
        if ( $valid ) {
            $status .= ' ' . ts('%count new relationship record created.', array('count' => $valid, 'plural' => '%count new relationship records created.'));
        }
        if ( $invalid ) {
            $status .= ' ' . ts('%count relationship record not created due to invalid target contact type.', array('count' => $invalid, 'plural' => '%count relationship records not created due to invalid target contact type.'));
        }
        if ( $duplicate ) {
            $status .= ' ' . ts('%count relationship record not created - duplicate of existing relationship.', array('count' => $duplicate, 'plural' => '%count relationship records not created - duplicate of existing relationship.'));
        }
        if ( $saved ) {
            $status .= ts('Relationship record has been updated.');
        }
        
        $note = new CRM_Core_DAO_Note( );
        $note->entity_id = $relationshipIds[0];
        $note->entity_table = 'civicrm_relationship';
        $noteIds = array();
        if ( $note->find(true) ) {
            $id            = $note->id;    
            $noteIds["id"] = $id;
        }
        
        $noteParams = array(
                            'entity_id'     => $relationshipIds[0],
                            'entity_table'  => 'civicrm_relationship',
                            'note'          => $params['note'],
                            'contact_id'    => $this->_contactId
                            );
        CRM_Core_BAO_Note::add( $noteParams , $noteIds );
        
        
        // Membership for related contacts CRM-1657
        if ( CRM_Core_Permission::access( 'CiviMember' ) ) {
            CRM_Contact_BAO_Relationship::relatedMemberships( $this->_contactId, 
                                                              $params, $ids, 
                                                              $this->_action );
        }
        
        //handle current employee/employer relationship, CRM-3532
        if ( $this->_allRelationshipNames[$relationshipTypeId]["name_{$this->_rtype}"] == 'Employee of' ) {
            $orgId = null;
            if ( CRM_Utils_Array::value( 'employee_of', $params ) ) { 
                $orgId = $params['employee_of'];
            } else if ( $this->_action & CRM_Core_Action::UPDATE ) {
                if ( CRM_Utils_Array::value( 'is_current_employer', $params ) ) {
                    if ( CRM_Utils_Array::value( 'contactTarget', $ids ) != 
                         CRM_Utils_Array::value( 'current_employer_id', $this->_values ) )  {
                        $orgId = CRM_Utils_Array::value( 'contactTarget', $ids );
                    }
                } else if ( CRM_Utils_Array::value( 'contactTarget', $ids ) == 
                            CRM_Utils_Array::value( 'current_employer_id', $this->_values ) ) { 
                    //clear current employer.
                    require_once 'CRM/Contact/BAO/Contact/Utils.php';
                    CRM_Contact_BAO_Contact_Utils::clearCurrentEmployer( $this->_contactId );
                }
            }
            
            //set current employer
            if ( $orgId ) {
                $currentEmpParams[$this->_contactId] = $orgId;
                require_once 'CRM/Contact/BAO/Contact/Utils.php';
                CRM_Contact_BAO_Contact_Utils::setCurrentEmployer( $currentEmpParams );
            }
            
        } else if ( $this->_allRelationshipNames[$relationshipTypeId]["name_{$this->_rtype}"] == 'Employer of' ) {
            $individualIds = array( );
            if ( CRM_Utils_Array::value( 'employer_of', $params ) ) { 
                $individualIds = array_keys( $params['employer_of'] );
            } else if ( $this->_action & CRM_Core_Action::UPDATE ) {
                if ( CRM_Utils_Array::value( 'is_current_employer', $params ) ) {
                    if ( CRM_Utils_Array::value( 'contactTarget', $ids ) != 
                         CRM_Utils_Array::value( 'current_employee_id', $this->_values ) ) {
                        $individualIds[] = CRM_Utils_Array::value( 'contactTarget', $ids ); 
                    }
                } else if ( CRM_Utils_Array::value( 'contactTarget', $ids ) == 
                            CRM_Utils_Array::value( 'current_employee_id', $this->_values ) )  {
                    // clear current employee
                    require_once 'CRM/Contact/BAO/Contact/Utils.php';
                    CRM_Contact_BAO_Contact_Utils::clearCurrentEmployer( $ids['contactTarget'] );
                }
            }
            
            //set current employee
            if ( !empty( $individualIds ) ) {
                
                //build the employee params.
                foreach ( $individualIds as $key => $Id ) {
                    $currentEmpParams[$Id] = $this->_contactId;
                }
                
                require_once 'CRM/Contact/BAO/Contact/Utils.php';
                CRM_Contact_BAO_Contact_Utils::setCurrentEmployer( $currentEmpParams );
            }
        }
        
        CRM_Core_Session::setStatus( $status );   
        if ( $quickSave ) {
            $session =& CRM_Core_Session::singleton( );
            CRM_Utils_System::redirect( $session->popUserContext() );
        }

    }//end of function
    

    /**
     * This function is to get the result of the search for contact in relationship form
     *
     * @param  array $params  This contains elements for search criteria
     *
     * @access public
     * @return None
     *
     */
    function search( &$params ) 
    {
        //max records that will be listed
        $searchValues = array();
        if ( CRM_Utils_Array::value( 'rel_contact', $params ) ) {
            if ( is_numeric( $params['rel_contact_id'] ) ) {
                $searchValues[] = array( 'contact_id', '=', $params['rel_contact_id'], 0, 1 );
            } else {
                $searchValues[] = array( 'sort_name', 'LIKE', $params['rel_contact'], 0, 1 );
            }
        }
        $contactTypeAdded = false;
        
        $excludedContactIds = array( $this->_contactId );

        if ( $params['relationship_type_id'] ) {
            $relationshipType = new CRM_Contact_DAO_RelationshipType( );
            list( $rid, $direction ) = explode( '_', $params['relationship_type_id'], 2 );
           
            $relationshipType->id = $rid;
            if ( $relationshipType->find( true ) ) {
                if ( $direction == 'a_b' ) {
                    $type    = $relationshipType->contact_type_b;
                    $subType = $relationshipType->contact_sub_type_b;
                } else {
                    $type    = $relationshipType->contact_type_a;
                    $subType = $relationshipType->contact_sub_type_a;
                }

                $this->set( 'contact_type', $type );
                $this->set( 'contact_sub_type', $subType );
                if ( $type == 'Individual' || $type == 'Organization' || $type == 'Household' ) {
                    $searchValues[] = array( 'contact_type', '=', $type, 0, 0 );
                    $contactTypeAdded = true;
                }

                if ( $subType ) {
                    $searchValues[] = array( 'contact_sub_type', '=', $subType, 0, 0 );
                }
            }
        }

        if ( ! $contactTypeAdded && CRM_Utils_Array::value( 'contact_type', $params ) ) {
            $searchValues[] = array( 'contact_type', '=', $params['contact_type'], 0, 0 );
        }

        // get the count of contact
        $contactBAO  = new CRM_Contact_BAO_Contact( );
        $query = new CRM_Contact_BAO_Query( $searchValues );
        $searchCount = $query->searchQuery(0, 0, null, true );
        $this->set( 'searchCount', $searchCount );
        if ( $searchCount <= self::MAX_RELATIONSHIPS ) {
            // get the result of the search
            $result = $query->searchQuery(0, 50, null);
            
            $config = CRM_Core_Config::singleton( );
            $searchRows = array( );

            //variable is set if only one record is foun and that record already has relationship with the contact
            $duplicateRelationship = 0;
            
            while($result->fetch()) {
                $contactID = $result->contact_id;
                if ( in_array( $contactID, $excludedContactIds ) ) {
                    $duplicateRelationship++;
                    continue;
                }

                $duplicateRelationship = 0;                

                $searchRows[$contactID]['id'] = $contactID;
                $searchRows[$contactID]['name'] = $result->sort_name;
                $searchRows[$contactID]['city'] = $result->city;
                $searchRows[$contactID]['state'] = $result->state_province;
                $searchRows[$contactID]['email'] = $result->email;
                $searchRows[$contactID]['phone'] = $result->phone;

                $contact_type = '<img src="' . $config->resourceBase . 'i/contact_';

                require_once( 'CRM/Contact/BAO/Contact/Utils.php' );
                $searchRows[$contactID]['type'] = 
                    CRM_Contact_BAO_Contact_Utils::getImage( $result->contact_sub_type ? 
                                                             $result->contact_sub_type : $result->contact_type );

            }

            $this->set( 'searchRows' , $searchRows );
            $this->set('duplicateRelationship', $duplicateRelationship);
        } else {
            // resetting the session variables if many records are found
            $this->set( 'searchRows' , null );
            $this->set('duplicateRelationship', null);
        }
    }
    

  /**
   * function for validation
   *
   * @param array $params (reference ) an assoc array of name/value pairs
   *
   * @return mixed true or array of errors
   * @access public
   * @static
   */
    static function formRule( $params, $files, $form ) {
        
        // hack, no error check for refresh
        if ( CRM_Utils_Array::value( '_qf_Relationship_refresh', $_POST ) ||
             CRM_Utils_Array::value( '_qf_Relationship_refresh_save', $_POST ) ||
             CRM_Utils_Array::value( '_qf_Relationship_refresh_savedetails', $_POST ) ) {
            return true;
        }
        
        $form->modifyParams( $params );
      
        $ids = array( );
        $session = CRM_Core_Session::singleton( );
        $ids['contact'     ] = $form->get( 'contactId'     );
        $ids['relationship'] = $form->get( 'relationshipId');
        
        $errors = array( );
        $employerId = null;
        if ( CRM_Utils_Array::value( 'contact_check', $params ) && is_array( $params['contact_check'] ) ) {
            foreach ( $params['contact_check'] as $cid => $dontCare ) {
                $message = CRM_Contact_BAO_Relationship::checkValidRelationship( $params, $ids, $cid);
                if ( $message ) {
                    $errors['relationship_type_id'] = $message;
                    break;
                }
                
                if ( $cid == CRM_Utils_Array::value('employee_of', $params )  ) {
                    $employerId = $cid;
                } 
            }
        } else {
            if ( $form->_callAjax ) {
                $errors['store_contacts'] = ts( 'Select select at least one contact from Target Contact(s).' );   
            } else {
                $errors['contact_check'] = ts( 'Please select at least one contact.' );
            }
        }
        
        if ( CRM_Utils_Array::value('employee_of', $params ) &&
             !$employerId ) {
            if ( $form->_callAjax ) {
                $errors['store_employer'] = ts( 'Current employer should be one of the selected contacts.' );
            } else {
                $errors['employee_of'] = ts( 'Current employer should be one of the selected contacts.' );
            }
        }

        if ( CRM_Utils_Array::value('employer_of', $params ) && 
             CRM_Utils_Array::value('contact_check', $params ) &&
             array_diff( array_keys( $params['employer_of'] ), array_keys( $params['contact_check'] ) ) ) {
            if ( $form->_callAjax ) {
                $errors['store_employer'] = ts( 'Current employee should be among the selected contacts.' );
            } else {
                $errors['employer_of'] = ts( 'Current employee should be among the selected contacts.' );
            }
        }
       
        return empty($errors) ? true : $errors;
    }
    
    /**
     * function for date validation
     *
     * @param array $params (reference ) an assoc array of name/value pairs
     *
     * @return mixed true or array of errors
     * @access public
     * @static
     */
    static function dateRule( $params ) {
        $errors = array( );

        // check start and end date
        if ( CRM_Utils_Array::value( 'start_date', $params ) &&
             CRM_Utils_Array::value( 'end_date'  , $params ) ) {
            $start_date = CRM_Utils_Date::format( CRM_Utils_Array::value( 'start_date', $params ) );
            $end_date   = CRM_Utils_Date::format( CRM_Utils_Array::value( 'end_date'  , $params ) );
            if ( $start_date && $end_date && (int ) $end_date < (int ) $start_date ) {
                $errors['end_date'] = ts( 'The relationship end date cannot be prior to the start date.' );
            }
        }

        return empty($errors) ? true : $errors;

    }
    
    function modifyParams( &$params ) {
        if ( !$this->_callAjax ) {
            return;
        }
        
        if ( CRM_Utils_Array::value('store_contacts', $params) ) {
            $storedContacts = array( );
            foreach ( explode( ',', $params['store_contacts'] ) as $value ) {
                if ($value) {
                    $storedContacts[$value] = 1;
                }
            }
            $params['contact_check'] = $storedContacts;
        }
        
        if ( CRM_Utils_Array::value('store_employers', $params) ) {
            $employeeContacts = array( );
            foreach ( explode( ',', $params['store_employers'] ) as $value ) {
                if ($value) {
                    $employeeContacts[$value] = $value;
                }
            }
            if ( $this->_allRelationshipNames[$this->_relationshipTypeId]["name_{$this->_rtype}"] == 'Employee of' ) {
                $params['employee_of'] = current($employeeContacts); 
            } else {
                $params['employer_of'] = $employeeContacts; 
            }    
        }
    } 
    
}


