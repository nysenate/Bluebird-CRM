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

require_once 'CRM/Report/Form.php';
require_once 'CRM/Event/PseudoConstant.php';
require_once 'CRM/Core/OptionGroup.php';
require_once 'CRM/Event/BAO/Participant.php';
require_once 'CRM/Contact/BAO/Contact.php';

class CRM_Report_Form_Event_ParticipantListing extends CRM_Report_Form {

    protected $_summary = null;

    protected $_customGroupExtends = array( 'Participant' );
    
    function __construct( ) {
        $this->_columns = 
            array( 
                  'civicrm_contact' =>
                  array( 'dao'     => 'CRM_Contact_DAO_Contact',
                         'fields'  =>
                         array( 'display_name' => 
                                array( 'title'     => ts( 'Participant Name' ),
                                       'required'  => true,
                                       'no_repeat' => true ),
                                'id'  => 
                                array( 'no_display' => true,
                                       'required'   => true, ),
                                'employer_id'       => 
                                array( 'title'     => ts( 'Organization' ), ),
                                ),
                         'grouping'  => 'contact-fields',
                         'filters' =>             
                         array('sort_name'     => 
                               array( 'title'      => ts( 'Participant Name' ),
                                      'operator'   => 'like' ), ),
                         ),

                  'civicrm_email'   =>
                  array( 'dao'     => 'CRM_Core_DAO_Email',
                         'fields'  =>
                         array( 'email' => 
                                array( 'title'     => ts( 'Email' ),
                                       'no_repeat' => true 
                                       ),
                                ),
                         'grouping'  => 'contact-fields',
                         'filters' =>
                         array( 'email' => 
                                array( 'title'    => ts( 'Participant E-mail' ),
                                       'operator' => 'like' ) ), 
                         ),
                
                  'civicrm_address'     =>
                  array( 'dao'          => 'CRM_Core_DAO_Address',
                         'fields'       =>
                         array( 'street_address'    => null, 
                                'city'              => null,
                                'postal_code'       => null,
                                'state_province_id' => 
                                array( 'title'      => ts( 'State/Province' ), ),
                                'country_id'        => 
                                array( 'title'      => ts( 'Country' ), ),
                                ),
                         'grouping'  => 'contact-fields',
                         ),                  
                  'civicrm_participant' =>
                  array( 'dao'     => 'CRM_Event_DAO_Participant',
                         'fields'  =>
                         array( 'participant_id'            => array( 'title' => 'Participant ID' ),

                                'event_id'                  => array( 'default' => true,
                                                                      'type'    =>  CRM_Utils_Type::T_STRING ),
                                'status_id'                 => array( 'title'   => ts('Status'),
                                                                      'default' => true ),
                                'role_id'                   => array( 'title'   => ts('Role'),
                                                                      'default' => true ),
                                'participant_fee_level'     => null,
                                
                                'participant_fee_amount'    => null,
                               
                                'participant_register_date' => array( 'title'   => ts('Registration Date') ),
                                ), 
                         'grouping' => 'event-fields',
                         'filters'  =>             
                         array( 'event_id'                  => array( 'name'         => 'event_id',
                                                                      'title'        => ts( 'Event' ),
                                                                      'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                                                      'options'      => CRM_Event_PseudoConstant::event( null, null, "is_template IS NULL OR is_template = 0" ), ),
                                
                                'sid'                       => array( 'name'         => 'status_id',
                                                                      'title'        => ts( 'Participant Status' ),
                                                                      'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                                                      'options'      => CRM_Event_PseudoConstant::participantStatus( ) ),
                                'rid'                       => array( 'name'         => 'role_id',
                                                                      'title'        => ts( 'Participant Role' ),
                                                                      'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                                                      'options'      => CRM_Event_PseudoConstant::participantRole( ) ),
                                'participant_register_date' => array( 'title'        => ' Registration Date',
                                                                      'operatorType' => CRM_Report_Form::OP_DATE ),
                                ),
                         
                         'group_bys' => 
                         array( 'event_id' => 
                                array( 'title' => ts( 'Event' ), ), ),            
                         ),
                  
                  'civicrm_event' =>
                  array( 'dao'        => 'CRM_Event_DAO_Event',
                         'fields'     =>
                         array( 
                               'event_type_id' => array( 'title' => ts('Event Type') ), 
                               ),
                         'grouping'  => 'event-fields', 
                         'filters'   =>             
                         array(                      
                               'eid' =>  array( 'name'         => 'event_type_id',
                                                'title'        => ts( 'Event Type' ),
                                                'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                                'options'      => CRM_Core_OptionGroup::values('event_type') ), 
                               ),
                         'group_bys' => 
                         array( 'event_type_id'      => 
                                array( 'title'      => ts( 'Event Type ' ), ), ),
                         ),                
  

                  );
        $this->_options = array( 'blank_column_begin' => array( 'title'   => ts('Blank column at the Begining'),
                                                                'type'    => 'checkbox' ),
                                 
                                 'blank_column_end'   => array( 'title'   => ts('Blank column at the End'),
                                                                'type'    => 'select',
                                                                'options' => array( '' => '-select-' , 1 => 'One', 
                                                                                    2 => 'Two', 3 => 'Three' ) ),
                                 );
        parent::__construct( );
    }
    
    function preProcess( ) {
        parent::preProcess( );
    }
    
    function select( ) {
        $select = array( );
        $this->_columnHeaders = array( );
        
        //add blank column at the Start
        if ( CRM_Utils_Array::value( 'blank_column_begin', $this->_params['options'] ) ) {
            $select[] = " '' as blankColumnBegin";
            $this->_columnHeaders['blankColumnBegin']['title'] = '_ _ _ _';
        }
        foreach ( $this->_columns as $tableName => $table ) {
            if ( array_key_exists('fields', $table) ) {
                foreach ( $table['fields'] as $fieldName => $field ) {
                    if ( CRM_Utils_Array::value( 'required', $field ) ||
                         CRM_Utils_Array::value( $fieldName, $this->_params['fields'] ) ) {
                        
                        $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
                        $this->_columnHeaders["{$tableName}_{$fieldName}"]['type']  = CRM_Utils_Array::value( 'type', $field );
                        $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = $field['title'];

                    }
                }
            }
        }
        //add blank column at the end
        if ( $blankcols = CRM_Utils_Array::value( 'blank_column_end', $this->_params ) ) {
            for ( $i= 1; $i <= $blankcols; $i++ ) {
                $select[] = " '' as blankColumnEnd_{$i}";
                $this->_columnHeaders["blank_{$i}"]['title'] = "_ _ _ _";
            }
        }

        $this->_select = "SELECT " . implode( ', ', $select ) . " ";
    }
    
    static function formRule( $fields, $files, $self ) {  
        $errors = $grouping = array( );
        return $errors;
    }
    
    function from( ) {
        $this->_from = "
        FROM civicrm_participant {$this->_aliases['civicrm_participant']}
             LEFT JOIN civicrm_event {$this->_aliases['civicrm_event']} 
                    ON ({$this->_aliases['civicrm_event']}.id = {$this->_aliases['civicrm_participant']}.event_id ) AND 
                       ({$this->_aliases['civicrm_event']}.is_template IS NULL OR  
                        {$this->_aliases['civicrm_event']}.is_template = 0)
             LEFT JOIN civicrm_contact {$this->_aliases['civicrm_contact']} 
                    ON ({$this->_aliases['civicrm_participant']}.contact_id  = {$this->_aliases['civicrm_contact']}.id  )
             {$this->_aclFrom}
             LEFT JOIN civicrm_address {$this->_aliases['civicrm_address']}
                    ON {$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_address']}.contact_id AND 
                       {$this->_aliases['civicrm_address']}.is_primary = 1 
             LEFT JOIN  civicrm_email {$this->_aliases['civicrm_email']} 
                    ON ({$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_email']}.contact_id AND
                       {$this->_aliases['civicrm_email']}.is_primary = 1) ";
    }

    function where( ) {
        $clauses = array( );
        foreach ( $this->_columns as $tableName => $table ) {
            if ( array_key_exists('filters', $table) ) { 
                foreach ( $table['filters'] as $fieldName => $field ) {
                    
                    $clause = null;
                    if ( CRM_Utils_Array::value( 'type', $field ) & CRM_Utils_Type::T_DATE ) {
                        $relative = CRM_Utils_Array::value( "{$fieldName}_relative", $this->_params );
                        $from     = CRM_Utils_Array::value( "{$fieldName}_from"    , $this->_params );
                        $to       = CRM_Utils_Array::value( "{$fieldName}_to"      , $this->_params );
                        
                        if ( $relative || $from || $to ) {
                            $clause = $this->dateClause( $field['name'], $relative, $from, $to, $field['type'] );
                        }
                    } else { 
                        $op = CRM_Utils_Array::value( "{$fieldName}_op", $this->_params );
                        
                        if ( $fieldName == 'rid' ) {
                            $value =  CRM_Utils_Array::value("{$fieldName}_value", $this->_params);
                            if ( !empty($value) ) {
                                $clause = "( {$field['dbAlias']} REGEXP '[[:<:]]" . implode( '[[:>:]]|[[:<:]]',  $value ) . "[[:>:]]' )";
                            }
                            $op = null;
                        }

                        if ( $op ) {
                            $clause = 
                                $this->whereClause( $field,
                                                    $op,
                                                    CRM_Utils_Array::value( "{$fieldName}_value", $this->_params ),
                                                    CRM_Utils_Array::value( "{$fieldName}_min", $this->_params ),
                                                    CRM_Utils_Array::value( "{$fieldName}_max", $this->_params ) );
                        }
                    }
                    
                    if ( ! empty( $clause ) ) {
                        $clauses[] = $clause;
                    }
                }
            }
        }
        
        if ( empty( $clauses ) ) {
            $this->_where = "WHERE {$this->_aliases['civicrm_participant']}.is_test = 0 ";
        } else {
            $this->_where = "WHERE {$this->_aliases['civicrm_participant']}.is_test = 0 AND " . implode( ' AND ', $clauses );
        }
        if ( $this->_aclWhere ) {
            $this->_where .= " AND {$this->_aclWhere} ";
        }
    }

    function groupBy( ) {
        $this->_groupBy = "";
        if ( CRM_Utils_Array::value( 'group_bys', $this->_params ) &&
             is_array($this->_params['group_bys']) &&
             !empty($this->_params['group_bys']) ) {
            foreach ( $this->_columns as $tableName => $table ) {
                if ( array_key_exists('group_bys', $table) ) {
                    foreach ( $table['group_bys'] as $fieldName => $field ) {
                        if ( CRM_Utils_Array::value( $fieldName, $this->_params['group_bys'] ) ) {
                            $this->_groupBy[] = $field['dbAlias'];
                        }
                    }
                }
            }
        } 
        
        if ( !empty( $this->_groupBy ) ) {
            $this->_groupBy = "ORDER BY " . implode( ', ', $this->_groupBy )  . ", {$this->_aliases['civicrm_contact']}.sort_name";
        } else {
            $this->_groupBy = "ORDER BY {$this->_aliases['civicrm_contact']}.sort_name";
        }
    }

    function postProcess( ) {

        // get ready with post process params
        $this->beginPostProcess( );

        // get the acl clauses built before we assemble the query
        $this->buildACLClause( $this->_aliases['civicrm_contact'] );
        // build query
        $sql = $this->buildQuery( true );

        // build array of result based on column headers. This method also allows 
        // modifying column headers before using it to build result set i.e $rows.
        $this->buildRows ( $sql, $rows );

        // format result set. 
        $this->formatDisplay( $rows );

        // assign variables to templates
        $this->doTemplateAssignment( $rows );

        // do print / pdf / instance stuff if needed
        $this->endPostProcess( $rows );

      
    }
    
    function alterDisplay( &$rows ) {
        // custom code to alter rows
        
        $entryFound = false;
        $eventType  = CRM_Core_OptionGroup::values('event_type');
        
        foreach ( $rows as $rowNum => $row ) {
            // make count columns point to detail report
            // convert display name to links
            if ( array_key_exists('civicrm_participant_event_id', $row) ) {
                if ( $value = $row['civicrm_participant_event_id'] ) {
                    $rows[$rowNum]['civicrm_participant_event_id'] = 
                        CRM_Event_PseudoConstant::event( $value, false );  
                    $url = CRM_Report_Utils_Report::getNextUrl( 'event/income', 
                                                  'reset=1&force=1&id_op=in&id_value='.$value,
                                                                $this->_absoluteUrl, $this->_id );
                    $rows[$rowNum]['civicrm_participant_event_id_link' ] = $url;
                    $rows[$rowNum]['civicrm_participant_event_id_hover'] = 
                        ts("View Event Income Details for this Event");
                }
                $entryFound = true;
            }
            
            // handle event type id
            if ( array_key_exists('civicrm_event_event_type_id', $row) ) {
                if ( $value = $row['civicrm_event_event_type_id'] ) {
                    $rows[$rowNum]['civicrm_event_event_type_id'] = $eventType[$value];
                }
                $entryFound = true;
            }
            
            // handle participant status id
            if ( array_key_exists('civicrm_participant_status_id', $row) ) {
                if ( $value = $row['civicrm_participant_status_id'] ) {
                    $rows[$rowNum]['civicrm_participant_status_id'] = 
                        CRM_Event_PseudoConstant::participantStatus( $value, false );
                }
                $entryFound = true;
            }
            
            // handle participant role id
            if ( array_key_exists('civicrm_participant_role_id', $row) ) {
                if ( $value = $row['civicrm_participant_role_id'] ) {
                    $roles = explode( CRM_Core_DAO::VALUE_SEPARATOR, $value ); 
                    $value = array( );
                    foreach( $roles as $role) {
                        $value[$role] = CRM_Event_PseudoConstant::participantRole( $role, false );
                    }
                    $rows[$rowNum]['civicrm_participant_role_id'] = implode( ', ', $value );
                }
                $entryFound = true;
            }
            
            // Handel value seperator in Fee Level 
            if ( array_key_exists('civicrm_participant_participant_fee_level', $row) ) {
                if ( $value = $row['civicrm_participant_participant_fee_level'] ) {
                    CRM_Event_BAO_Participant::fixEventLevel( $value );
                    $rows[$rowNum]['civicrm_participant_participant_fee_level'] = $value;
                }
                $entryFound = true;
            }

            // Convert display name to link 
            if ( array_key_exists( 'civicrm_contact_display_name', $row ) && 
                 $rows[$rowNum]['civicrm_contact_display_name'] && 
                 array_key_exists( 'civicrm_contact_id', $row ) ) {
                $url = CRM_Utils_System::url( "civicrm/contact/view"  , 
                                              'reset=1&cid=' . $row['civicrm_contact_id'],
                                              $this->_absoluteUrl );
                $rows[$rowNum]['civicrm_contact_display_name_link' ] = $url;
                $rows[$rowNum]['civicrm_contact_display_name_hover'] = 
                    ts("View Contact Summary for this Contact.");
                $entryFound = true;
            }
            
            // Handle country id
            if ( array_key_exists( 'civicrm_address_country_id', $row ) ) {
                if ( $value = $row['civicrm_address_country_id'] ) {
                    $rows[$rowNum]['civicrm_address_country_id'] = CRM_Core_PseudoConstant::country( $value, true );
                }
                $entryFound = true;
            }

            // Handle state/province id
            if ( array_key_exists( 'civicrm_address_state_province_id', $row ) ) {
                if ( $value = $row['civicrm_address_state_province_id'] ) {
                    $rows[$rowNum]['civicrm_address_state_province_id'] = 
                        CRM_Core_PseudoConstant::stateProvince( $value, true );
                }
                $entryFound = true;
            }
            
            // Handle employer id
            if ( array_key_exists( 'civicrm_contact_employer_id', $row ) ) {
                if ( $value = $row['civicrm_contact_employer_id'] ) {
                    $rows[$rowNum]['civicrm_contact_employer_id'] = CRM_Contact_BAO_Contact::displayName( $value );
                    $url = CRM_Utils_System::url( 'civicrm/contact/view',
                                                  'reset=1&cid=' . $value, $this->_absoluteUrl );
                    $rows[$rowNum]['civicrm_contact_employer_id_link']  = $url;
                    $rows[$rowNum]['civicrm_contact_employer_id_hover'] = 
                        ts('View Contact Summary for this Contact.');
                }
            }

            // skip looking further in rows, if first row itself doesn't 
            // have the column we need
            if ( !$entryFound ) {
                break;
            }
        }
    }
}