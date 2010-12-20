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
require_once 'CRM/Member/PseudoConstant.php';

class CRM_Report_Form_Member_Detail extends CRM_Report_Form {

    protected $_addressField = false;
    
    protected $_emailField   = false;

    protected $_phoneField   = false;
    
    protected $_summary      = null;
    
    protected $_customGroupExtends = array( 'Membership' );
    protected $_customGroupGroupBy = false;
    function __construct( ) {
        $this->_columns = 
            array( 'civicrm_contact' =>
                   array( 'dao'     => 'CRM_Contact_DAO_Contact',
                          'fields'  =>
                          array( 'display_name' => 
                                 array( 'title'      => ts( 'Contact Name' ),
                                        'required'   => true,
                                        'no_repeat'  => true ),
                                 'id'           => 
                                 array( 'no_display' => true, 
                                        'required'   => true ), ),
                          
                          'filters'  =>
                          array('sort_name'     => 
                                array( 'title'    => ts( 'Contact Name' ),
                                       'operator' => 'like' ),
                                'id' => 
                                array( 'no_display'  => true ), ),

                          'grouping'=> 'contact-fields',
                          ),
                   
                   'civicrm_membership' =>
                   array( 'dao'       => 'CRM_Member_DAO_Membership',
                          'fields'    =>
                          array(                              
                                'membership_type_id'    => array( 'title'     => 'Membership Type', 
                                                                  'required'  => true,
                                                                  'no_repeat' => true ),
                                'membership_start_date' => array( 'title'     => ts('Start Date'),
                                                                  'default'   => true ),
                                'membership_end_date'   => array( 'title'     => ts('End Date'),
                                                                  'default'   => true ),
                                'join_date'             => null,
                                
                                'source'                => array( 'title' => 'Source'),
                                ), 
                          'filters' => array( 					      
                                             'join_date'    =>
                                             array( 'operatorType'  => CRM_Report_Form::OP_DATE),

                                             'owner_membership_id'  =>
                                             array( 'title'         => ts('Membership Owner ID'),
                                                    'operatorType'  => CRM_Report_Form::OP_INT,
                                                   ),
                                             'tid'          =>
                                             array( 'name'          =>  'membership_type_id',
                                                    'title'         =>  ts( 'Membership Types' ),
                                                    'type'          =>  CRM_Utils_Type::T_INT,
                                                    'operatorType'  =>  CRM_Report_Form::OP_MULTISELECT,
                                                    'options'       =>  CRM_Member_PseudoConstant::membershipType(),
                                                    ), ),
                          
                          'grouping'=> 'member-fields',
                          ),
                   
                   'civicrm_membership_status' =>
                   array( 'dao'      => 'CRM_Member_DAO_MembershipStatus',
                          'alias'    => 'mem_status',
                          'fields'   =>
                          array( 'name'  =>  array( 'title'   => ts('Status'),
                                                    'default' => true ),
                                 ),
                          
                          'filters'  => array( 'sid' => 
                                               array( 'name'         => 'id',
                                                      'title'        => ts( 'Status' ),
                                                      'type'         => CRM_Utils_Type::T_INT,
                                                      'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                                      'options'      => CRM_Member_PseudoConstant::membershipStatus( null, null, 'label') ), ),
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
                                 array( 'title'      => ts( 'Country' ), ), 
                                 ),
                          'grouping' => 'contact-fields',
                          ),
                   
                   'civicrm_email' => 
                   array( 'dao'    => 'CRM_Core_DAO_Email',
                          'fields' =>
                          array( 'email' => null),
                          'grouping'=> 'contact-fields',
                          ),
                   
                   'civicrm_phone' => 
                  array( 'dao'    => 'CRM_Core_DAO_Phone',
                         'fields' =>
                         array( 'phone' => null),
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
        $this->assign( 'reportTitle', ts('Membership Detail Report' ) );
        parent::preProcess( );
    }
    
    function select( ) {
        $select = $this->_columnHeaders = array( );
        
        foreach ( $this->_columns as $tableName => $table ) {
            if ( array_key_exists('fields', $table) ) {
                foreach ( $table['fields'] as $fieldName => $field ) {
                    if ( CRM_Utils_Array::value( 'required', $field ) ||
                         CRM_Utils_Array::value( $fieldName, $this->_params['fields'] ) ) {
                        if ( $tableName == 'civicrm_address' ) {
                            $this->_addressField = true;
                        } else if ( $tableName == 'civicrm_email' ) {
                            $this->_emailField = true;
                        } else if ( $tableName == 'civicrm_phone' ) {
                            $this->_phoneField = true;
                        }
                        $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
                        $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = $field['title'];
                        $this->_columnHeaders["{$tableName}_{$fieldName}"]['type']  = CRM_Utils_Array::value( 'type', $field );
                    }
                }
            }
        }
        
        $this->_select = "SELECT " . implode( ', ', $select ) . " ";
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
                             {$this->_aliases['civicrm_membership']}.status_id ";

        
        //used when address field is selected
        if ( $this->_addressField ) {
            $this->_from .= "
             LEFT JOIN civicrm_address {$this->_aliases['civicrm_address']} 
                       ON {$this->_aliases['civicrm_contact']}.id = 
                          {$this->_aliases['civicrm_address']}.contact_id AND 
                          {$this->_aliases['civicrm_address']}.is_primary = 1\n";
        }
        //used when email field is selected
        if ( $this->_emailField ) {
            $this->_from .= "
              LEFT JOIN civicrm_email {$this->_aliases['civicrm_email']} 
                        ON {$this->_aliases['civicrm_contact']}.id = 
                           {$this->_aliases['civicrm_email']}.contact_id AND 
                           {$this->_aliases['civicrm_email']}.is_primary = 1\n";
        }
        //used when phone field is selected
        if ( $this->_phoneField ) {
            $this->_from .= "
              LEFT JOIN civicrm_phone {$this->_aliases['civicrm_phone']} 
                        ON {$this->_aliases['civicrm_contact']}.id = 
                           {$this->_aliases['civicrm_phone']}.contact_id AND 
                           {$this->_aliases['civicrm_phone']}.is_primary = 1\n";
        }
    }
    
    function where( ) {
        $clauses = array( );
        foreach ( $this->_columns as $tableName => $table ) {
            if ( array_key_exists('filters', $table) ) {
                foreach ( $table['filters'] as $fieldName => $field ) {
                    $clause = null;
                    if ( CRM_Utils_Array::value( 'operatorType', $field ) & CRM_Utils_Type::T_DATE ) {
                        $relative = CRM_Utils_Array::value( "{$fieldName}_relative", $this->_params );
                        $from     = CRM_Utils_Array::value( "{$fieldName}_from"    , $this->_params );
                        $to       = CRM_Utils_Array::value( "{$fieldName}_to"      , $this->_params );
                        
                        $clause = $this->dateClause( $field['name'], $relative, $from, $to, $field['type'] );
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
                        $clauses[ ] = $clause;
                    }
                }
            }
        }
        
        if ( empty( $clauses ) ) {
            $this->_where = "WHERE ( 1 ) ";
        } else {
            $this->_where = "WHERE " . implode( ' AND ', $clauses );
        }

        if ( $this->_aclWhere ) {
            $this->_where .= " AND {$this->_aclWhere} ";
        }  
    }
    
    function groupBy( ) {
        $this->_groupBy = " GROUP BY {$this->_aliases['civicrm_contact']}.id, {$this->_aliases['civicrm_membership']}.membership_type_id";
    }
    
    function postProcess( ) {
        
        $this->beginPostProcess( );

        // get the acl clauses built before we assemble the query
        $this->buildACLClause( $this->_aliases['civicrm_contact'] );
        $sql  = $this->buildQuery( true );
        
        $rows = array( );
        $this-> buildRows( $sql, $rows );
        
        $this->formatDisplay( $rows );        
        $this->doTemplateAssignment( $rows );
        $this->endPostProcess( $rows);
        
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
            
            if ( array_key_exists('civicrm_membership_membership_type_id', $row) ) {
                if ( $value = $row['civicrm_membership_membership_type_id'] ) {
                    $rows[$rowNum]['civicrm_membership_membership_type_id'] = 
                        CRM_Member_PseudoConstant::membershipType( $value, false ); 
                }
                $entryFound = true;
            }
            
            if ( array_key_exists('civicrm_address_state_province_id', $row) ) {
                if ( $value = $row['civicrm_address_state_province_id'] ) {
                    $rows[$rowNum]['civicrm_address_state_province_id'] = 
                        CRM_Core_PseudoConstant::stateProvince( $value, false );
                }
                $entryFound = true;
            }
            
            if ( array_key_exists('civicrm_address_country_id', $row) ) {
                if ( $value = $row['civicrm_address_country_id'] ) {
                    $rows[$rowNum]['civicrm_address_country_id'] = 
                        CRM_Core_PseudoConstant::country( $value, false );
                }
                $entryFound = true;
            }
            
            if ( array_key_exists('civicrm_contact_display_name', $row) && 
                 $rows[$rowNum]['civicrm_contact_display_name'] && 
                 array_key_exists('civicrm_contact_id', $row) ) {
                $url = CRM_Utils_System::url( "civicrm/contact/view"  , 
                                              'reset=1&cid=' . $row['civicrm_contact_id'],
                                              $this->_absoluteUrl );
                $rows[$rowNum]['civicrm_contact_display_name_link'] = $url;
                $rows[$rowNum]['civicrm_contact_display_name_hover'] =
                    ts("View Contact Summary for this Contact.");
                $entryFound = true;
            }
            
            if ( !$entryFound ) {
                break;
            }
        }
    }
}