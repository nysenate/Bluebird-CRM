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

require_once 'CRM/Report/Form.php';
require_once 'CRM/Member/PseudoConstant.php';

class CRM_Report_Form_Member_Lapse extends CRM_Report_Form {

    protected $_summary      = null;
    protected $_addressField = false;
    protected $_emailField   = false;
    protected $_phoneField   = false;
    protected $_charts       = array( '' => 'Tabular' );
    protected $_customGroupExtends = array( 'Membership' );

    function __construct( ) {
        // UI for selecting columns to appear in the report list
        // array conatining the columns, group_bys and filters build and provided to Form
        $this->_columns = 
            array( 'civicrm_contact'  =>
                   array( 'dao'       => 'CRM_Contact_DAO_Contact',
                          'fields'    =>
                          array( 'sort_name'  => 
                                 array( 'title'      => ts( 'Member Name' ),
                                        'no_repeat'  => true, 
                                        'required'   => true),
                                 'id' =>
                                 array( 'no_display' => true,
                                        'required'   => true, ),
                                'first_name'  => 
                                 array( 'title'      => ts( 'First Name' ),
                                        'no_repeat'  => true ),
                                 'id'           => 
                                 array( 'no_display' => true, 
                                        'required'   => true ), 
                                 
                                 'last_name' => 
                                 array( 'title'      => ts( 'Last Name' ),
                                        'no_repeat'  => true ),
                                 'id'           => 
                                 array( 'no_display' => true, 
                                        'required'   => true ),
                                 
                                 ), 
                          'grouping'  => 'contact-fields',
                          ),

                   'civicrm_membership_type' =>
                   array( 'dao'        => 'CRM_Member_DAO_MembershipType',
                          'grouping'   => 'member-fields',
                          'filters'    =>
                          array( 'tid' => 
                                 array( 'name'          =>  'id',
                                        'title'         =>  ts( 'Membership Types' ),
                                        'operatorType'  =>  CRM_Report_Form::OP_MULTISELECT,
                                        'options'       =>  CRM_Member_PseudoConstant::membershipType(),
                                        ),   
                                 ),
                          ),
                   
                   'civicrm_membership'  =>
                   array( 'dao'          => 'CRM_Member_DAO_Membership',
                          'grouping'     => 'member-fields',
                          'fields'       =>  
                          array( 'membership_type_id' => 
                                 array( 'title'       => 'Membership Type',
                                        'required'    => true,
                                        'type'        => CRM_Utils_Type::T_STRING
                                        ),  
                                 'membership_start_date' => array( 'title'    => ts('Current Cycle Start Date'), ),
                                 
                                 'membership_end_date'   => array( 'title'    => ts('Membership Lapse Date'),
                                                                   'required' => true, ),
                                 ), 
                          'filters'  => 
                          array( 'membership_end_date' =>
                                 array('title'        =>  'Lapsed Memberships', 
                                       'operatorType' =>   CRM_Report_Form::OP_DATE ),
                                 ),
                          ),
                   
                   'civicrm_membership_status' =>
                   array( 'dao'      => 'CRM_Member_DAO_MembershipStatus',
                          'alias'    => 'mem_status',
                          'fields'   =>
                          array( 
                                'name'      => array ('title' => ts('Current Status'),
                                                      'required'  => true),
                                 ),
                          'grouping' => 'member-fields',		
                          ),

                   'civicrm_address' =>
                   array( 'dao'      => 'CRM_Core_DAO_Address',
                          'fields'   =>
                          array( 'street_address'    => null,
                                 'city'              => null,
                                 'postal_code'       => null,
                                 'state_province_id' => 
                                 array( 'title'      => ts( 'State/Province' ), ),
                                 'country_id'        => 
                                 array( 'title'      => ts( 'Country' ),  
                                        'default'    => true ), 
                                 ),
                          'grouping'=> 'contact-fields',
                          ),
                   
                   'civicrm_phone'    =>
                   array( 'dao'       => 'CRM_Core_DAO_Phone',
                          'alias'	  => 'phone',
                          'fields'    =>
                          array( 'phone' => null ),
                          'grouping'     => 'contact-fields',
                          ),
                   'civicrm_email' => 
                   array( 'dao'    => 'CRM_Core_DAO_Email',
                          'fields' =>
                          array( 'email' => null),
                          'grouping'=> 'contact-fields',
                          ),
                   'civicrm_group' => 
                   array( 'dao'    => 'CRM_Contact_DAO_GroupContact',
                          'alias'  => 'cgroup',
                          'filters'=>             
                          array( 'gid' => 
                                 array( 'name'         => 'group_id',
                                        'title'        => ts( 'Group' ),
                                        'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                        'group'        => true,
                                        'options'      => CRM_Core_PseudoConstant::group( ) ), ), ),
                   
                   );

        $this->_tagFilter = true;
        parent::__construct( );
    }
    
    function preProcess( ) {
        parent::preProcess( );
    }
    
    function select( ) {
        $select = array( );
        $this->_columnHeaders = array( );
        foreach ( $this->_columns as $tableName => $table ) {
            if ( array_key_exists('fields', $table) ) {
                foreach ( $table['fields'] as $fieldName => $field ) {
                    if ( CRM_Utils_Array::value( 'required', $field ) ||
                         CRM_Utils_Array::value( $fieldName, $this->_params['fields'] ) ) {
                        // to include optional columns address ,email and phone only if checked
                        if ( $tableName == 'civicrm_address' ) {
                            $this->_addressField = true;
                        } else if ( $tableName == 'civicrm_email' ) { 
                            $this->_emailField = true;  
                        } else if ( $tableName == 'civicrm_phone' ) {
                            $this->_phoneField = true;
                        }
                        $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
                        $this->_columnHeaders["{$tableName}_{$fieldName}"]['type']  = CRM_Utils_Array::value( 'type', $field );
                        $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = $field['title'];
                    }
                }
            }
        }
        $this->_select = "SELECT " . implode( ', ', $select ) . " ";
    }
    
    static function formRule( $fields, $files, $self ) {  
        $errors = $grouping = array( );
        //check for searching combination of dispaly columns and
        //grouping criteria
        
        return $errors;
    }
    
    function from( ) {
        $this->_from = null;
        
        $this->_from = "
        FROM  civicrm_contact {$this->_aliases['civicrm_contact']} {$this->_aclFrom}
              INNER JOIN civicrm_membership {$this->_aliases['civicrm_membership']} 
                         ON {$this->_aliases['civicrm_contact']}.id = 
                            {$this->_aliases['civicrm_membership']}.contact_id AND {$this->_aliases['civicrm_membership']}.is_test = 0
              LEFT  JOIN civicrm_membership_status {$this->_aliases['civicrm_membership_status']}
                         ON {$this->_aliases['civicrm_membership_status']}.id = 
                            {$this->_aliases['civicrm_membership']}.status_id
              LEFT  JOIN civicrm_membership_type {$this->_aliases['civicrm_membership_type']} 
                         ON {$this->_aliases['civicrm_membership']}.membership_type_id =
                            {$this->_aliases['civicrm_membership_type']}.id";

        //  include address field if address column is to be included
        if ( $this->_addressField ) {  
            $this->_from .= "
            LEFT JOIN civicrm_address {$this->_aliases['civicrm_address']} 
                      ON {$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_address']}.contact_id AND {$this->_aliases['civicrm_address']}.is_primary = 1\n";
        }
        
        // include email field if email column is to be included
        if ( $this->_emailField ) { 
            $this->_from .= "
            LEFT JOIN civicrm_email {$this->_aliases['civicrm_email']} 
                      ON {$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_email']}.contact_id AND {$this->_aliases['civicrm_email']}.is_primary = 1\n";     
        }

        // include phone field if phone column is to be included
        if ( $this->_phoneField ) { 
            $this->_from .= "
            LEFT JOIN civicrm_phone {$this->_aliases['civicrm_phone']} 
                      ON {$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_phone']}.contact_id 
                     AND {$this->_aliases['civicrm_phone']}.is_primary = 1\n";
        }
    }      
    
    function where( ) {
        $clauses = array( );
        foreach ( $this->_columns as $tableName => $table ) {
            if ( array_key_exists('filters', $table) ) {
                foreach ( $table['filters'] as $fieldName => $field ) {
                    $clause = null;
             
                    if ( $field['operatorType'] & CRM_Utils_Type::T_DATE ) {
                        $relative = CRM_Utils_Array::value( "{$fieldName}_relative", $this->_params );
                        $from     = CRM_Utils_Array::value( "{$fieldName}_from"    , $this->_params );
                        $to       = CRM_Utils_Array::value( "{$fieldName}_to"      , $this->_params );
                        
                        if ( $relative || $from || $to ) {
                            $clause = $this->dateClause( $field['name'], $relative, $from, $to, $field['type'] );
                        }
                    } else {
                        $op = CRM_Utils_Array::value( "{$fieldName}_op", $this->_params );
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
                        $clauses[$fieldName] = $clause;
                    }
                }
            }
        }
        
        if ( empty( $clauses ) ) {
            $this->_where = "WHERE end_date < '" .date('Y-m-d'). "' AND {$this->_aliases['civicrm_membership_status']}.name = 'Expired'";
        } else {
            if ( !array_key_exists('end_date', $clauses) ) {
                $this->_where = "WHERE end_date < '".date('Y-m-d')."' AND " . implode( ' AND ', $clauses );
            } else {
                $this->_where = "WHERE " . implode( ' AND ', $clauses );
            }
        }
        
        if ( $this->_aclWhere ) {
            $this->_where .= " AND {$this->_aclWhere} ";
        }     
    }
    
    function orderBy( ) {
        $this->_orderBy = " ORDER BY {$this->_aliases['civicrm_contact']}.sort_name, {$this->_aliases['civicrm_contact']}.id, {$this->_aliases['civicrm_membership']}.membership_type_id";
    }

    function postProcess( ) {
        $this->beginPostProcess( );

        // get the acl clauses built before we assemble the query
        $this->buildACLClause( $this->_aliases['civicrm_contact'] );
        $sql = $this->buildQuery( true );
        
        $dao   = CRM_Core_DAO::executeQuery( $sql );
        $rows  = $graphRows = array();
        $count = 0;
        while ( $dao->fetch( ) ) {
            $row = array( );
            foreach ( $this->_columnHeaders as $key => $value ) {
                $row[$key] = $dao->$key;
            }
            
            $rows[] = $row;
        }
        $this->formatDisplay( $rows );
        
        // assign variables to templates
        $this->doTemplateAssignment( $rows );

        $this->endPostProcess( $rows );
    }
    
    function alterDisplay( &$rows ) {
        // custom code to alter rows
        $entryFound = false;
        $checkList  =  array();   
        
        foreach ( $rows as $rowNum => $row ) {
            
            if ( !empty($this->_noRepeats) && $this->_outputMode != 'csv' ) {
                // not repeat contact display names if it matches with the one 
                // in previous row
                
                $repeatFound = false;
                foreach ( $row as $colName => $colVal ) {
                    if ( CRM_Utils_Array::value( $colName, $checkList ) && 
                         is_array($checkList[$colName]) && 
                         in_array($colVal, $checkList[$colName]) ) {
                        $rows[$rowNum][$colName] = "";
                        $repeatFound = true; 
                    }
                    if ( in_array($colName, $this->_noRepeats) ) {
                        $checkList[$colName][] = $colVal;
                    }
                }
            }
            
            //handle the Membership Type Ids
            if ( array_key_exists('civicrm_membership_membership_type_id', $row) ) {
                if ( $value = $row['civicrm_membership_membership_type_id'] ) {
                    $rows[$rowNum]['civicrm_membership_membership_type_id'] = 
                        CRM_Member_PseudoConstant::membershipType( $value, false );
                }
                $entryFound = true;
            }        
            
            // handle state province
            if ( array_key_exists('civicrm_address_state_province_id', $row) ) {
                if ( $value = $row['civicrm_address_state_province_id'] ) {
                    $rows[$rowNum]['civicrm_address_state_province_id'] = 
                        CRM_Core_PseudoConstant::stateProvince( $value, false );
                }
                $entryFound = true;
            }
            
            // handle country
            if ( array_key_exists('civicrm_address_country_id', $row) ) {
                if ( $value = $row['civicrm_address_country_id'] ) {
                    $rows[$rowNum]['civicrm_address_country_id'] = 
                        CRM_Core_PseudoConstant::country( $value, false );
                }
                $entryFound = true;
            }
            
            // convert display name to links
            if ( array_key_exists('civicrm_contact_sort_name', $row) && 
                 array_key_exists('civicrm_contact_id', $row) ) {
                $url = CRM_Report_Utils_Report::getNextUrl( 'member/detail', 
                                              'reset=1&force=1&id_op=eq&id_value=' . $row['civicrm_contact_id'],
                                                            $this->_absoluteUrl, $this->_id);
                $rows[$rowNum]['civicrm_contact_sort_name_link' ] = $url;
                $rows[$rowNum]['civicrm_contact_sort_name_hover'] =
                    ts("View Membership Detail for this Contact.");
            }
            
            // skip looking further in rows, if first row itself doesn't 
            // have the column we need
            if ( !$entryFound ) {
                break;
            }
        }
    }
}
