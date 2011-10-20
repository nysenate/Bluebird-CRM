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

require_once 'CRM/Contact/BAO/Contact.php';

/**
 * This class gets the name of the file to upload
 */
class CRM_Export_Form_Select extends CRM_Core_Form 
{
   
    /**
     * various Contact types
     */
    const
        EXPORT_ALL      = 1,
        EXPORT_SELECTED = 2;

    /**
     * export modes
     */
    const
        CONTACT_EXPORT     = 1,
        CONTRIBUTE_EXPORT  = 2,
        MEMBER_EXPORT      = 3,
        EVENT_EXPORT       = 4,
        PLEDGE_EXPORT      = 5,
        CASE_EXPORT        = 6,
        GRANT_EXPORT       = 7,
        ACTIVITY_EXPORT    = 8;

    /**
     * current export mode
     *
     * @var int
     */
    public $_exportMode;
    
    public $_componentTable;
    
    /**
     * build all the data structures needed to build the form
     *
     * @param
     * @return void
     * @access public
     */
    function preProcess( ) 
    {
         //special case for custom search, directly give option to download csv file
        $customSearchID = $this->get( 'customSearchID' );
        if ( $customSearchID ) {
            require_once 'CRM/Export/BAO/Export.php';
            CRM_Export_BAO_Export::exportCustom( $this->get( 'customSearchClass' ),
                                                 $this->get( 'formValues' ),
                                                 $this->get( CRM_Utils_Sort::SORT_ORDER ) );
        }

        $this->_selectAll  = false;
        $this->_exportMode = self::CONTACT_EXPORT;
        $this->_componentIds = array( );
        $this->_componentClause = null;

        // get the submitted values based on search
        if ( $this->_action == CRM_Core_Action::ADVANCED ) { 
            $values = $this->controller->exportValues( 'Advanced' ); 
        } else if ( $this->_action == CRM_Core_Action::PROFILE ) { 
            $values = $this->controller->exportValues( 'Builder' ); 
        } else if ( $this->_action == CRM_Core_Action::COPY ) {
            $values = $this->controller->exportValues( 'Custom' ); 
        } else {
            // we need to determine component export
            $stateMachine  =& $this->controller->getStateMachine( );
            $formName      = CRM_Utils_System::getClassName($stateMachine);
            $componentName = explode( '_', $formName );
            $components    = array( 'Contribute', 'Member', 'Event', 'Pledge', 'Case', 'Grant', 'Activity' );
            
            if ( in_array( $componentName[1], $components ) ) {
                eval( '$this->_exportMode = self::' . strtoupper( $componentName[1] ) . '_EXPORT;');
                require_once "CRM/{$componentName[1]}/Form/Task.php";
                eval('CRM_' . $componentName[1] . '_Form_Task::preProcessCommon( $this, true );');
                $values = $this->controller->exportValues( 'Search' ); 
            } else {
                $values = $this->controller->exportValues( 'Basic' ); 
            }
        } 

        $count = 0;
        $this->_matchingContacts = false;
        if ( CRM_Utils_Array::value( 'radio_ts', $values ) == 'ts_sel' ) {
            foreach ( $values as $key => $value ) {
                if ( strstr( $key, 'mark_x' ) ) {
                    $count++;
                }
                if ( $count > 2 ) { 
                    $this->_matchingContacts = true;
                    break;
                }
            }
        } 

        $componentMode = $this->get( 'component_mode' );
        switch ( $componentMode ) {
        case 2:
            require_once 'CRM/Contribute/Form/Task.php';
            CRM_Contribute_Form_Task::preProcessCommon( $this, true );
            $this->_exportMode = self::CONTRIBUTE_EXPORT;
            $componentName = array( '', 'Contribute' );
            break;

        case 3:
            require_once 'CRM/Event/Form/Task.php';
            CRM_Event_Form_Task::preProcessCommon( $this, true );
            $this->_exportMode = self::EVENT_EXPORT;
            $componentName = array( '', 'Event' );
            break;

        case 4:
            require_once 'CRM/Activity/Form/Task.php';
            CRM_Activity_Form_Task::preProcessCommon( $this, true );
            $this->_exportMode = self::ACTIVITY_EXPORT;
            $componentName = array( '', 'Activity' );
            break;

        }

        require_once 'CRM/Contact/Task.php';
        $this->_task = $values['task']; 
        if ( $this->_exportMode == self::CONTACT_EXPORT ) {
            $contactTasks = CRM_Contact_Task::taskTitles(); 
            $taskName = $contactTasks[$this->_task]; 
            $component = false;
            require_once 'CRM/Contact/Form/Task.php';
            CRM_Contact_Form_Task::preProcessCommon( $this, true );
        } else {
            $this->assign( 'taskName', "Export $componentName[1]" ); 
            eval( '$componentTasks = CRM_'. $componentName[1] .'_Task::tasks();' );
            $taskName = $componentTasks[$this->_task];
            $component = true;
        }

        if ( $this->_componentTable ) {
            $query = "
SELECT count(*)
FROM   {$this->_componentTable}
";
            $totalSelectedRecords = CRM_Core_DAO::singleValueQuery( $query );
        } else {
            $totalSelectedRecords = count( $this->_componentIds );
        }
        $this->assign( 'totalSelectedRecords', $totalSelectedRecords );
        $this->assign('taskName', $taskName);
        $this->assign('component', $component);
        // all records actions = save a search 
        if (($values['radio_ts'] == 'ts_all') || ($this->_task == CRM_Contact_Task::SAVE_SEARCH)) { 
            $this->_selectAll = true;
            $rowCount = $this->get( 'rowCount' );
            if ( $rowCount > 2 ) {
                $this->_matchingContacts = true;
            }
            $this->assign( 'totalSelectedRecords', $rowCount );
        }
        
        $this->assign( 'matchingContacts', $this->_matchingContacts );
        $this->set( 'componentIds', $this->_componentIds );
        $this->set( 'selectAll' , $this->_selectAll  );
        $this->set( 'exportMode' , $this->_exportMode );
        $this->set( 'componentClause', $this->_componentClause );
        $this->set( 'componentTable', $this->_componentTable );
    }


    /**
     * Function to actually build the form
     *
     * @return void
     * @access public
     */
    public function buildQuickForm( ) 
    {
        //export option
        $exportOptions = $mergeHousehold = $mergeAddress = $postalMailing = array();        
        $exportOptions[] = HTML_QuickForm::createElement('radio',
                                                         null, null,
                                                         ts('Export PRIMARY fields'),
                                                         self::EXPORT_ALL,
                                                         array( 'onClick' => 'showMappingOption( );' ));
        $exportOptions[] = HTML_QuickForm::createElement('radio',
                                                         null, null,
                                                         ts('Select fields for export'),
                                                         self::EXPORT_SELECTED,
                                                         array( 'onClick' => 'showMappingOption( );' ));

        $mergeAddress[] = HTML_QuickForm::createElement( 'advcheckbox', 
                                                         'merge_same_address', 
                                                         null, 
                                                         ts('Merge Contacts with the Same Address'),
                                                         array( 'onclick' => 'showGreetingOptions( );' ) );
        $mergeHousehold[] = HTML_QuickForm::createElement( 'advcheckbox', 
                                                           'merge_same_household', 
                                                           null, 
                                                           ts('Merge Household Members into their Households'));
        $postalMailing[]  = HTML_QuickForm::createElement( 'advcheckbox',
                                                           'postal_mailing_export', 
                                                           null, 
                                                           null);
        
        $this->addGroup( $exportOptions, 'exportOption', ts('Export Type'), '<br/>' );

        if ( $this->_matchingContacts ) {
            $this->_greetingOptions = self::getGreetingOptions( );
            
            foreach ( $this->_greetingOptions as $key => $value ) {
                $fieldLabel = ts( '%1 (merging > 2 contacts)', array( 1 => ucwords( str_replace( '_', ' ', $key ) ) ) );
                $this->addElement( 'select', $key, $fieldLabel,
                                   $value, array( 'onchange' => "showOther(this);" ) );
                $this->addElement( 'text', "{$key}_other", '' );
            }
        }

        if ( $this->_exportMode == self::CONTACT_EXPORT ) {
            $this->addGroup( $mergeAddress, 'merge_same_address', ts('Merge Same Address'), '<br/>');
            $this->addGroup( $mergeHousehold, 'merge_same_household', ts('Merge Same Household'), '<br/>');
            $this->addGroup( $postalMailing,  'postal_mailing_export', ts('Postal Mailing Export'), '<br/>');

            $this->addElement( 'select', 'additional_group', ts( 'Additional Group for Export' ), 
                               array( '' => ts( '- select group -' )) + CRM_Core_PseudoConstant::staticGroup( ) );
        }
        
        $this->buildMapping( );

        $this->setDefaults(array('exportOption' => self::EXPORT_ALL ));

        $this->addButtons( array(
                                 array ( 'type'      => 'next',
                                         'name'      => ts('Continue >>'),
                                         'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                                         'isDefault' => true   ),
                                 array ( 'type'      => 'cancel',
                                         'name'      => ts('Cancel') ),
                                 )
                           );

        $this->addFormRule( array( 'CRM_Export_Form_Select', 'formRule' ), $this );
    }

    /**
     * Function for validation
     *
     * @param array $params (ref.) an assoc array of name/value pairs
     *
     * @return mixed true or array of errors
     * @access public
     * @static
     */
    public function formRule( $params, $files, $self ) 
    {
        $errors = array( );
                
        if ( CRM_Utils_Array::value( 'merge_same_address', $params['merge_same_address'] ) && 
             $self->_matchingContacts ) {
            $greetings = array( 'postal_greeting' => 'postal_greeting_other',
                                'addressee'       => 'addressee_other' );

            foreach ( $greetings as $key => $value ) {
                $otherOption = CRM_Utils_Array::value( $key, $params );
                
                if ( ( CRM_Utils_Array::value( $otherOption, $self->_greetingOptions[$key] ) == 'Other' ) &&
                     !CRM_Utils_Array::value( $value, $params ) ) {
                    
                    $label = ucwords( str_replace( '_', ' ', $key ) );
                    $errors[$value] = ts( 'Please enter a value for %1 (merging > 2 contacts), or select a pre-configured option from the list.', array( 1 => $label ) );
                }
            }
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * Process the uploaded file
     *
     * @return void
     * @access public
     */
    public function postProcess( ) 
    {
        $exportOption = $this->controller->exportValue( $this->_name, 'exportOption' );
        $merge_same_address   = $this->controller->exportValue( $this->_name, 'merge_same_address' );
        $merge_same_household = $this->controller->exportValue( $this->_name, 'merge_same_household' );

        // instead of increasing the number of arguments to exportComponents function, we 
        // will send $exportParams as another argument, which is an array and suppose to contain 
        // all submitted options or any other argument
        $exportParams = $this->controller->exportValues( $this->_name );
        
        if ( !empty( $this->_greetingOptions ) ) {
            foreach ( $this->_greetingOptions as $key => $value ) {
                if ( $option = CRM_Utils_Array::value( $key, $exportParams ) ) {
                    if ( $this->_greetingOptions[$key][$option] == 'Other' ) {
                        $exportParams[$key] = '';
                    } else {
                        $exportParams[$key] = $this->_greetingOptions[$key][$option];
                    }
                }
            }
        }
        
        $mappingId = $this->controller->exportValue( $this->_name, 'mapping' ); 
        if ( $mappingId ) {
            $this->set('mappingId', $mappingId);
        } else {
            $this->set('mappingId', null);
        }
        
        $mergeSameAddress = $mergeSameHousehold = false;
        if ( $merge_same_address['merge_same_address'] == 1 ) {
            $mergeSameAddress = true;
        }
        $this->set('mergeSameAddress', $mergeSameAddress );
        
        if ( $merge_same_household['merge_same_household'] == 1 ) {
            $mergeSameHousehold = true;
        }
        $this->set('mergeSameHousehold', $mergeSameHousehold );

        if ( $exportOption == self::EXPORT_ALL ) {
            require_once 'CRM/Export/BAO/Export.php';
            CRM_Export_BAO_Export::exportComponents( $this->_selectAll,
                                                     $this->_componentIds,
                                                     $this->get( 'queryParams' ),
                                                     $this->get( CRM_Utils_Sort::SORT_ORDER ),
                                                     null,
                                                     $this->get( 'returnProperties' ),
                                                     $this->_exportMode,
                                                     $this->_componentClause,
                                                     $this->_componentTable,
                                                     $mergeSameAddress,
                                                     $mergeSameHousehold,
                                                     $exportParams,
                                                     $this->get( 'queryOperator' )
                                                     );
        }
        
        //reset map page
        $this->controller->resetPage( 'Map' );
    }

    /**
     * Return a descriptive name for the page, used in wizard header
     *
     * @return string
     * @access public
     */
    public function getTitle( ) {
        return ts('Export All or Selected Fields');
    }

    /**
     * Function to build mapping form element
     *
     */
    function buildMapping( ) 
    { 
        switch ( $this->_exportMode ) {
        case CRM_Export_Form_Select::CONTACT_EXPORT : 
            $exportType = 'Export Contact';
            break;
            
        case CRM_Export_Form_Select::CONTRIBUTE_EXPORT : 
            $exportType = 'Export Contribution';
            break;
            
        case CRM_Export_Form_Select::MEMBER_EXPORT : 
            $exportType = 'Export Membership';
            break;
            
        case CRM_Export_Form_Select::EVENT_EXPORT : 
            $exportType = 'Export Participant';
            break;
            
        case CRM_Export_Form_Select::PLEDGE_EXPORT : 
            $exportType = 'Export Pledge';
            break;
            
        case CRM_Export_Form_Select::CASE_EXPORT : 
            $exportType = 'Export Case';
            break;
            
        case CRM_Export_Form_Select::GRANT_EXPORT : 
            $exportType = 'Export Grant';
            break;
            
        case CRM_Export_Form_Select::ACTIVITY_EXPORT : 
            $exportType = 'Export Activity';
            break;
        }
        
        require_once 'CRM/Core/BAO/Mapping.php';
        $mappingTypeId = CRM_Core_OptionGroup::getValue( 'mapping_type', $exportType, 'name' );
        $this->set( 'mappingTypeId', $mappingTypeId );

        $mappings  = CRM_Core_BAO_Mapping::getMappings( $mappingTypeId );
        if ( !empty( $mappings ) ) {
            $this->add('select','mapping', ts('Use Saved Field Mapping'), array('' => '-select-') + $mappings );
        }
    }

    static function getGreetingOptions( )
    {
        $options   = array( );
        $greetings = array( 'postal_greeting' => 'postal_greeting_other',
                            'addressee'       => 'addressee_other' );
            
        foreach ( $greetings as $key => $value ) {
            $params        = array( );
            $optionGroupId = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_OptionGroup', $key, 'id', 'name' );
            
            CRM_Core_DAO::commonRetrieveAll( 'CRM_Core_DAO_OptionValue', 'option_group_id', $optionGroupId, 
                                             $params, array( 'label', 'filter' ) );
            
            $greetingCount = 1;
            $options[$key] = array( "$greetingCount" => ts( 'List of names' ) );
            
            foreach ( $params as $id => $field ) {
                if ( CRM_Utils_Array::value( 'filter', $field ) == 4 ) {
                    $options[$key][++$greetingCount] = ts( $field['label'] );
                }
            }
            
            $options[$key][++$greetingCount] = ts( 'Other' );
        }

        return $options;
    }

}


