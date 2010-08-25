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

class CRM_Event_BAO_Query 
{
    
    static function &getFields( ) 
    {
        $fields = array( );
        require_once 'CRM/Event/DAO/Event.php';
        require_once 'CRM/Core/DAO/Discount.php';
        $fields = array_merge( $fields, CRM_Event_DAO_Event::import( ) );
        $fields = array_merge( $fields, self::getParticipantFields( ) );
        $fields = array_merge( $fields, CRM_Core_DAO_Discount::export( ) );
               
        return $fields;
    }

    static function &getParticipantFields( $onlyParticipant = false ) 
    {
        require_once 'CRM/Event/BAO/Participant.php';
        $fields =& CRM_Event_BAO_Participant::importableFields( 'Individual', true, $onlyParticipant );
        return $fields;
    }
    

    /** 
     * build select for CiviEvent 
     * 
     * @return void  
     * @access public  
     */
    static function select( &$query ) 
    {
        if ( ( $query->_mode & CRM_Contact_BAO_Query::MODE_EVENT ) ||
             CRM_Utils_Array::value( 'participant_id', $query->_returnProperties ) ) {
            
            $query->_select['participant_id'] = "civicrm_participant.id as participant_id";
            $query->_element['participant_id'         ] = 1;
            $query->_tables['civicrm_participant'] = $query->_whereTables['civicrm_participant'] = 1;

            //add fee level
            if ( CRM_Utils_Array::value( 'participant_fee_level', $query->_returnProperties ) ) {
                $query->_select['participant_fee_level' ]  = "civicrm_participant.fee_level as participant_fee_level";
                $query->_element['participant_fee_level']  = 1;
            }
            
            //add fee amount
            if ( CRM_Utils_Array::value( 'participant_fee_amount', $query->_returnProperties ) ) {
                $query->_select['participant_fee_amount']  = "civicrm_participant.fee_amount as participant_fee_amount";
                $query->_element['participant_fee_amount']  = 1;
            }
            
            //add fee currency
            if ( CRM_Utils_Array::value( 'participant_fee_currency', $query->_returnProperties ) ) {
                $query->_select['participant_fee_currency' ]  = "civicrm_participant.fee_currency as participant_fee_currency";
                $query->_element['participant_fee_currency']  = 1;
            }
            
            //add event title also if event id is select
            if ( CRM_Utils_Array::value( 'event_id'   , $query->_returnProperties ) ||
                 CRM_Utils_Array::value( 'event_title', $query->_returnProperties ) ) {
                $query->_select['event_id'] = "civicrm_event.id as event_id";
                $query->_select['event_title'] = "civicrm_event.title as event_title";
                $query->_element['event_id'] = 1;
                $query->_element['event_title'] = 1;
                $query->_tables['civicrm_event'] = 1;
                $query->_whereTables['civicrm_event'] = 1;
            }
        
            //add start date / end date
            if ( CRM_Utils_Array::value( 'event_start_date', $query->_returnProperties ) ) {
                $query->_select['event_start_date']  = "civicrm_event.start_date as event_start_date";
                $query->_element['event_start_date'] = 1;
            }
            
            if ( CRM_Utils_Array::value( 'event_end_date', $query->_returnProperties ) ) {
                $query->_select['event_end_date']  = "civicrm_event.end_date as event_end_date";
                $query->_element['event_end_date'] = 1;
            }
        
            //event type
            if ( CRM_Utils_Array::value( 'event_type_id', $query->_returnProperties ) ) {
                $query->_select['event_type']  = "event_type.label as event_type_id";
                $query->_element['event_type_id']     = 1;
                $query->_tables['event_type']         = 1;
                $query->_whereTables['event_type']    = 1;
            }

            //add status
            if ( CRM_Utils_Array::value( 'participant_status_id', $query->_returnProperties ) ) {
                $query->_select['participant_status']  = "participant_status.label as participant_status_id";
                $query->_element['participant_status_id'] = 1;
                $query->_tables['civicrm_participant'] = 1;
                $query->_tables['participant_status'] = 1;
                $query->_whereTables['civicrm_participant'] = 1;
                $query->_whereTables['participant_status'] = 1;
            }
            
            //add role
            if ( CRM_Utils_Array::value( 'participant_role_id', $query->_returnProperties ) ) {
                $query->_select['participant_role']  = "participant_role.label as participant_role_id";
                $query->_element['participant_role_id'] = 1;
                $query->_tables['civicrm_participant'] = 1;
                $query->_tables['participant_role'] = 1;
                $query->_whereTables['civicrm_participant'] = 1;
                $query->_whereTables['participant_role'] = 1;
            }
            
            //add register date
            if ( CRM_Utils_Array::value( 'participant_register_date', $query->_returnProperties ) ) {
                $query->_select['participant_register_date' ]  = "civicrm_participant.register_date as participant_register_date";
                $query->_element['participant_register_date']  = 1;
            }
            
            //add source
            if ( CRM_Utils_Array::value( 'participant_source', $query->_returnProperties ) ) {
                $query->_select['participant_source' ]  = "civicrm_participant.source as participant_source";
                $query->_element['participant_source']  = 1;
                $query->_tables['civicrm_participant'] = $query->_whereTables['civicrm_participant'] = 1;
            }
            
            //participant note
            if ( CRM_Utils_Array::value( 'participant_note', $query->_returnProperties ) ) {
                $query->_select['participant_note' ] = "civicrm_note.note as participant_note";
                $query->_element['participant_note'] = 1;
                $query->_tables['participant_note' ] = 1;
                $query->_whereTables['civicrm_note'] = 1;
            }

            if ( CRM_Utils_Array::value( 'participant_is_pay_later', $query->_returnProperties ) ) {
                $query->_select['participant_is_pay_later']  = "civicrm_participant.is_pay_later as participant_is_pay_later";
                $query->_element['participant_is_pay_later'] = 1;
            }

            if ( CRM_Utils_Array::value( 'participant_is_test', $query->_returnProperties ) ) {
                $query->_select['participant_is_test']  = "civicrm_participant.is_test as participant_is_test";
                $query->_element['participant_is_test'] = 1;
            }
 
            if ( CRM_Utils_Array::value( 'participant_registered_by_id', $query->_returnProperties ) ) {
                $query->_select['participant_registered_by_id']  = "civicrm_participant.registered_by_id as participant_registered_by_id";
                $query->_element['participant_registered_by_id'] = 1;
            }
 
            // get discount name
            if ( CRM_Utils_Array::value( 'participant_discount_name', $query->_returnProperties ) ) {
                $query->_select['participant_discount_name']      = "discount_name.label as participant_discount_name";
                $query->_element['participant_discount_name']     = 1;
                $query->_tables['civicrm_discount']               = 1;
                $query->_tables['participant_discount_name']      = 1;
                $query->_whereTables['civicrm_discount']          = 1;
                $query->_whereTables['participant_discount_name'] = 1;
            }
        }
    }

    static function where( &$query ) 
    {
        $isTest   = false;
        $grouping = null;
        foreach ( array_keys( $query->_params ) as $id ) {
            if ( substr( $query->_params[$id][0], 0, 6) == 'event_' ||
                 substr( $query->_params[$id][0], 0, 12) == 'participant_') {
                if ( $query->_mode == CRM_Contact_BAO_QUERY::MODE_CONTACTS ) {
                    $query->_useDistinct = true;
                }
                if ( $query->_params[$id][0] == 'participant_test' ) {
                    $isTest = true;
                }
                $grouping = $query->_params[$id][3];
                self::whereClauseSingle( $query->_params[$id], $query );
            }
        }

        if ( $grouping !== null &&
             ! $isTest ) {
            $values = array( 'participant_test', '=', 0, $grouping, 0 );
            self::whereClauseSingle( $values, $query );
        }
    }
    
  
    static function whereClauseSingle( &$values, &$query ) 
    {
        list( $name, $op, $value, $grouping, $wildcard ) = $values;
        switch( $name ) {
            
        case 'event_start_date_low':
        case 'event_start_date_high':
            $query->dateQueryBuilder( $values,
                                      'civicrm_event', 'event_start_date', 'start_date', 'Start Date' );
            return;

        case 'event_end_date_low':
        case 'event_end_date_high':
            $query->dateQueryBuilder( $values,
                                       'civicrm_event', 'event_end_date', 'end_date', 'End Date' );
            return;

        case 'event_id':
            $query->_where[$grouping][] = "civicrm_event.id $op {$value}";
            $eventTitle = CRM_Core_DAO::getFieldValue('CRM_Event_DAO_Event', $value, 'title');
            $query->_qill[$grouping ][] = ts( 'Event' ) . " $op {$eventTitle}";
            $query->_tables['civicrm_event'] = $query->_whereTables['civicrm_event'] = 1;
            return;

        case 'event_type_id':
            require_once 'CRM/Core/OptionGroup.php';
            require_once 'CRM/Utils/Array.php';

            $eventTypes  = CRM_Core_OptionGroup::values("event_type" );
            $query->_where[$grouping][] = "civicrm_participant.event_id = civicrm_event.id and civicrm_event.event_type_id = '{$value}'";
            $query->_qill[$grouping ][] = ts( 'Event Type - %1', array( 1 => $eventTypes[$value] ) );
            $query->_tables['civicrm_event'] = $query->_whereTables['civicrm_event'] = 1;
            return;
            
        case 'participant_test':
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( "civicrm_participant.is_test", 
                                                                              $op,
                                                                              $value,
                                                                              "Integer" );
            if ( $value ) {
                $query->_qill[$grouping][]  = ts("Find Test Participants");
            }
            $query->_tables['civicrm_participant'] = $query->_whereTables['civicrm_participant'] = 1;
            return;
            
        case 'participant_fee_id':
            $feeLabel = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionValue', $value, 'label');
            if ( $value ) {
                $query->_where[$grouping][] = "civicrm_participant.fee_level $op '$feeLabel'";
                $query->_qill[$grouping][]  = ts("Fee level" ) . " $op $feeLabel";
            }
            $query->_tables['civicrm_participant'] = $query->_whereTables['civicrm_participant'] = 1;
            return;
            
        case 'participant_fee_amount':
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( "civicrm_participant.fee_amount", 
                                                                              $op,
                                                                              $value,
                                                                              "Money" );
            if ( $value ) {
                $query->_qill[$grouping][] = ts("Fee Amount" ) . " $op $value";
            }
            $query->_tables['civicrm_participant'] = $query->_whereTables['civicrm_participant'] = 1;
            return;    
            
        case 'participant_fee_amount_high':
        case 'participant_fee_amount_low':
            $query->numberRangeBuilder( $values,
                                        'civicrm_participant', 'participant_fee_amount', 'fee_amount', 'Fee Amount' );
            return;

        case 'participant_pay_later':
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( "civicrm_participant.is_pay_later", 
                                                                              $op,
                                                                              $value,
                                                                              "Integer" );
            if ( $value ) {
                $query->_qill[$grouping][]  = ts("Find Pay Later Participants");
            }
            $query->_tables['civicrm_participant'] = $query->_whereTables['civicrm_participant'] = 1;
            return;

        case 'participant_status_id':
            $val = array( );
            if ( is_array( $value ) ) {
                foreach ($value as $k => $v) {
                    if ($v) {
                        $val[$k] = $k;
                    }
                } 
                $status = implode (',' ,$val);
            } else {
                $status = $value;
            }

            if (count($val) > 1) {
                $op = 'IN';
                $status = "({$status})";
            }     

            require_once 'CRM/Event/PseudoConstant.php';
            $statusTypes  = CRM_Event_PseudoConstant::participantStatus( );
            $names = array( );

            if ( !empty($val) ) {
                foreach ( $val as $id => $dontCare ) {
                    $names[] = $statusTypes[$id];
                }
            } else {
                $names[] = $statusTypes[$value];
            }

            $query->_qill[$grouping][]  = ts('Participant Status %1', array( 1 => $op ) ) . ' ' . implode( ' ' . ts('or') . ' ', $names );
            
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( "civicrm_participant.status_id", 
                                                                              $op,
                                                                              $status,
                                                                              "Integer" );
            $query->_tables['civicrm_participant'] = $query->_whereTables['civicrm_participant'] = 1;
            return;

        case 'participant_role_id':
            $val = array( );
            if ( is_array( $value ) ) {
                foreach ($value as $k => $v) {
                    if ($v) {
                        $val[$k] = $k;
                    }
                } 
                $role = implode (',' ,$val);
            } else {
                $role = $value;
            }

            if (count($val) > 1) {
                $op = 'IN';
                $role = "({$role})";
            }     

            require_once 'CRM/Event/PseudoConstant.php';
            $roleTypes = CRM_Event_PseudoConstant::participantRole( );

            $names = array( );
            if ( !empty($val) ) {
                foreach ( $val as $id => $dontCare ) {
                    $names[] = $roleTypes[$id];
                }
            } else {
                $names[] = $roleTypes[$value];
            }

            $query->_qill[$grouping][]  = ts('Participant Role %1', array( 1 => $op ) ) . ' ' . implode( ' ' . ts('or') . ' ', $names );
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( "civicrm_participant.role_id", 
                                                                              $op,
                                                                              $role,
                                                                              "Integer" );
            
            $query->_tables['civicrm_participant'] = $query->_whereTables['civicrm_participant'] = 1;
            return;

        case 'participant_source':
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( "civicrm_participant.source", 
                                                                              $op,
                                                                              $value,
                                                                              "String" );
            $query->_qill[$grouping][]  = ts("Participant Source" ) . " $op $value";
            $query->_tables['civicrm_participant'] = $query->_whereTables['civicrm_participant'] = 1;
            return;

        case 'participant_register_date':
            $query->dateQueryBuilder( $values,
                                       'civicrm_participant', 'participant_register_date', 'register_date', 'Register Date' );
            return;

        case 'participant_id':
            $query->_where[$grouping][] = "civicrm_participant.id $op $value";
            $query->_tables['civicrm_participant'] = $query->_whereTables['civicrm_participant'] = 1;
            return;

        case 'event_id':
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( "civicrm_event.id", 
                                                                              $op,
                                                                              $value,
                                                                              "Integer" );
            $query->_tables['civicrm_event'] = $query->_whereTables['civicrm_event'] = 1;
            $title = CRM_Core_DAO::getFieldValue('CRM_Event_DAO_Event', $value, "title");
            $query->_qill[$grouping ][] = ts( 'Event' ) . " $op $value";
            return;

        case 'participant_contact_id':
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( "civicrm_participant.contact_id", 
                                                                              $op,
                                                                              $value,
                                                                              "Integer" );
            $query->_tables['civicrm_participant'] = $query->_whereTables['civicrm_participant'] = 1;
            return;

        case 'event_is_public':
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( "civicrm_event.is_public", 
                                                                              $op,
                                                                              $value,
                                                                              "Integer" );
            $query->_tables['civicrm_event'] = $query->_whereTables['civicrm_event'] = 1;
            return;

        }
    }

    static function from( $name, $mode, $side ) 
    {
        $from = null;
        switch ( $name ) {
        
        case 'civicrm_participant':
            $from = " LEFT JOIN civicrm_participant ON civicrm_participant.contact_id = contact_a.id ";
            break;
    
        case 'civicrm_event':
            $from = " INNER JOIN civicrm_event ON civicrm_participant.event_id = civicrm_event.id ";
            break;
            
        case 'event_type':
            $from = " $side JOIN civicrm_option_group option_group_event_type ON (option_group_event_type.name = 'event_type')";
            $from .= " $side JOIN civicrm_option_value event_type ON (civicrm_event.event_type_id = event_type.value AND option_group_event_type.id = event_type.option_group_id ) ";
            break;

        case 'participant_note':
            $from .= " $side JOIN civicrm_note ON ( civicrm_note.entity_table = 'civicrm_participant' AND
                                                        civicrm_participant.id = civicrm_note.entity_id )";
            break;

        case 'participant_status':
            $from .= " $side JOIN civicrm_participant_status_type participant_status ON (civicrm_participant.status_id = participant_status.id) ";
            break;

        case 'participant_role':
            $from = " $side JOIN civicrm_option_group option_group_participant_role ON (option_group_participant_role.name = 'participant_role')";
            $from .= " $side JOIN civicrm_option_value participant_role ON (civicrm_participant.role_id = participant_role.value 
                               AND option_group_participant_role.id = participant_role.option_group_id ) ";
            break;

        case 'participant_discount_name':
            $from = " $side JOIN civicrm_discount discount ON ( civicrm_participant.discount_id = discount.id )";
            $from .= " $side JOIN civicrm_option_group discount_name ON ( discount_name.id = discount.option_group_id ) ";
            break;


        }
        return $from;
    }

    /**
     * getter for the qill object
     *
     * @return string
     * @access public
     */
    function qill( ) {
        return (isset($this->_qill)) ? $this->_qill : "";
    }
   
    static function defaultReturnProperties( $mode ) 
    {
        $properties = null;
        if ( $mode & CRM_Contact_BAO_Query::MODE_EVENT ) {
            $properties = array(  
                                'contact_type'              => 1, 
                                'contact_sub_type'          => 1, 
                                'sort_name'                 => 1, 
                                'display_name'              => 1,
                                'event_id'                  => 1,
                                'event_title'               => 1,
                                'event_start_date'          => 1,
                                'event_end_date'            => 1,
                                'event_type_id'             => 1,
                                'participant_id'            => 1,
                                'participant_status_id'     => 1,
                                'participant_role_id'       => 1,
                                'participant_register_date' => 1,
                                'participant_source'        => 1,
                                'participant_fee_level'     => 1,
                                'participant_is_test'       => 1,
                                'participant_is_pay_later'  => 1,
                                'participant_fee_amount'    => 1,
                                'participant_discount_name' => 1,
                                'participant_fee_currency'  => 1,
                                'participant_registered_by_id' => 1
                                );
       
            // also get all the custom participant properties
            require_once "CRM/Core/BAO/CustomField.php";
            $fields = CRM_Core_BAO_CustomField::getFieldsForImport('Participant');
            if ( ! empty( $fields ) ) {
                foreach ( $fields as $name => $dontCare ) {
                    $properties[$name] = 1;
                }
            }
        }

        return $properties;
    }

    static function buildSearchForm( &$form ) 
    {
        $dataURLEvent     = CRM_Utils_System::url( 'civicrm/ajax/event',
                                                   "reset=1",
                                                   false, null, false);
        $dataURLEventType = CRM_Utils_System::url( 'civicrm/ajax/eventType',
                                                   "reset=1",
                                                   false, null, false);
        $dataURLEventFee  = CRM_Utils_System::url( 'civicrm/ajax/eventFee',
                                                   "reset=1",
                                                   false, null, false);
        
        $form->assign( 'dataURLEvent'     , $dataURLEvent    );
        $form->assign( 'dataURLEventType' , $dataURLEventType);
        $form->assign( 'dataURLEventFee'  , $dataURLEventFee );
        
        $eventId         =& $form->add('text', 'event_name'           , ts('Event Name') );
        $eventType       =& $form->add('text', 'event_type'           , ts('Event Type') );
        $participantFee  =& $form->add('text', 'participant_fee_level', ts('Fee Level')  );

        //elements for assigning value operation
        $eventNameId      =& $form->add( 'hidden', 'event_id'          , '', array( 'id' => 'event_id'      ) );
        $eventTypeId      =& $form->add( 'hidden', 'event_type_id'     , '', array( 'id' => 'event_type_id'      ) );
        $participantFeeId =& $form->add( 'hidden', 'participant_fee_id', '', array( 'id' => 'participant_fee_id' ) );

        $form->addDate( 'event_start_date_low', ts('Event Dates - From'), false, array( 'formatType' => 'searchDate') );
        $form->addDate( 'event_end_date_high', ts('To'), false, array( 'formatType' => 'searchDate') );

        require_once 'CRM/Event/PseudoConstant.php';
        $status = CRM_Event_PseudoConstant::participantStatus( null, null, 'label' );
        asort( $status );
        foreach ( $status as $id => $Name) {
            $form->_participantStatus =& $form->addElement('checkbox', "participant_status_id[$id]", null,$Name);
        }
        
        foreach (CRM_Event_PseudoConstant::participantRole( ) as $rId => $rName) {
            $form->_participantRole =& $form->addElement('checkbox', "participant_role_id[$rId]", null,$rName);
        }
        
        $form->addElement( 'checkbox', 'participant_test' , ts( 'Find Test Participants?' ) );
        $form->addElement( 'checkbox', 'participant_pay_later' , ts( 'Find Pay Later Participants?' ) );
        $form->addElement( 'text', 'participant_fee_amount_low', ts( 'From' ), array( 'size' => 8, 'maxlength' => 8 ) );
        $form->addElement( 'text', 'participant_fee_amount_high' , ts( 'To' ), array( 'size' => 8, 'maxlength' => 8 ) );

        $form->addRule( 'participant_fee_amount_low', ts( 'Please enter a valid money value.' ), 'money' );
        $form->addRule( 'participant_fee_amount_high', ts( 'Please enter a valid money value.' ), 'money' );
        // add all the custom  searchable fields
        require_once 'CRM/Core/BAO/CustomGroup.php';
        $extends      = array( 'Participant' );
        $groupDetails = CRM_Core_BAO_CustomGroup::getGroupDetail( null, true, $extends );
        if ( $groupDetails ) {
            require_once 'CRM/Core/BAO/CustomField.php';
            $form->assign('participantGroupTree', $groupDetails);
            foreach ($groupDetails as $group) {
                foreach ($group['fields'] as $field) {
                    $fieldId = $field['id'];
                    $elementName = 'custom_' . $fieldId;
                    CRM_Core_BAO_CustomField::addQuickFormElement( $form,
                                                                   $elementName,
                                                                   $fieldId,
                                                                   false, false, true );
                }
            }
        }

        $form->assign( 'validCiviEvent', true );
    }
    
    static function searchAction( &$row, $id ) 
    {
    }

    static function tableNames( &$tables ) 
    {
        //add participant table 
        if ( CRM_Utils_Array::value( 'civicrm_event', $tables ) ) {
            $tables = array_merge( array( 'civicrm_participant' => 1), $tables );
        }
    }
  
}


