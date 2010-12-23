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
require_once 'CRM/Contribute/PseudoConstant.php';

class CRM_Report_Form_Pledge_Pbnp extends CRM_Report_Form {
    protected $_charts = array( ''         => 'Tabular',
                                'barChart' => 'Bar Chart',
                                'pieChart' => 'Pie Chart'
                                );
    
    protected $_customGroupExtends = array( 'Pledge' );

    function __construct( ) {
        $this->_columns = 
            array( 'civicrm_contact'  =>
                   array( 'dao'       => 'CRM_Contact_DAO_Contact',
                          'fields'    =>
                          array( 'display_name'      => 
                                 array( 'title'      => ts( 'Constituent Name' ),
                                        'required'   => true,
                                        'no_repeat' => true ),
                                 'id' =>
                                 array( 'no_display' => true,
                                        'required'   => true, ),
                                 ), 
                          'grouping' => 'contact-fields',
                          ),
                   
                   'civicrm_pledge' =>
                   array( 'dao'     => 'CRM_Pledge_DAO_Pledge',
                          'fields'  =>
                          array( 'pledge_create_date' => 
                                 array( 'title'    => ts( 'Pledge Made' ),
                                        'required' => true,
                                        ),
                                 'contribution_type_id' =>
                                 array( 'title'    => ts('Contribution Type'),
                                        'requried'  => true,
                                        ),
                                'amount'    =>
                                 array( 'title'    => ts('Amount'),
                                        'required' => true,
                                        'type'      => CRM_Utils_Type::T_MONEY,
                                        ),
                                 'status_id' =>
                                 array( 'title'    => ts('Status'),
                                        ),
                                 ),
                          'filters'  => 
                          array( 'pledge_create_date' =>
                                 array('title'    =>  'Pledge Made', 
                                       'operatorType' => CRM_Report_Form::OP_DATE ),
                                 'contribution_type_id' =>
                                 array( 'title'        =>  ts('Contribution Type'),
                                        'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                        'options'      => CRM_Contribute_PseudoConstant::contributionType(),
                                        ),
                                 ),
                          'grouping' => 'pledge-fields',
                          ),
                   
                   'civicrm_pledge_payment'  =>
                   array( 'dao'       => 'CRM_Pledge_DAO_Payment',
                          'fields'    =>
                          array( 'scheduled_date' =>
                                 array( 'title'    => ts( 'Next Payment Due' ),
                                        'type'     => CRM_Utils_Type::T_DATE,
                                        'required' => true,),
                                 ), 
                          'grouping'  => 'pledge-fields',
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
                   
                   'civicrm_email' => 
                   array( 'dao'    => 'CRM_Core_DAO_Email',
                          'fields' =>
                          array( 'email' => null),
                          'grouping'=> 'contact-fields',
                          ),
                   
                   'civicrm_group' => 
                   array( 'dao'    => 'CRM_Contact_DAO_Group',
                          'alias'  => 'cgroup',
                          'filters' =>             
                          array( 'gid' => 
                                 array( 'name'    => 'group_id',
                                        'title'   => ts( 'Group' ),
                                        'group'   => true,
                                        'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                        'options' => CRM_Core_PseudoConstant::staticGroup( ) ), ), ),

                   );

        $this->_tagFilter = true;
        parent::__construct( );
    }
    
    function preProcess( ) {
        $this->assign( 'reportTitle', ts('Pledge But Not Paid Report' ) );
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
                        // to include optional columns address and email, only if checked
                        if ( $tableName == 'civicrm_address' ) {
                            $this->_addressField = true;
                            $this->_emailField = true; 
                        } else if ( $tableName == 'civicrm_email' ) { 
                            $this->_emailField = true;  
                        }
                        $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
                        $this->_columnHeaders["{$tableName}_{$fieldName}"]['type'] = CRM_Utils_Array::value( 'type', $field );
                        $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = CRM_Utils_Array::value( 'title', $field );
                    }
                }
            }
        }
        $this->_select = "SELECT " . implode( ', ', $select ) . " ";
    }
    
    function from( ) {
        $this->_from = null;

        $allStatus = CRM_Contribute_PseudoConstant::contributionStatus( null, 'name' );
        $pendingStatus = array_search( 'Pending', $allStatus);
        foreach ( array( 'Pending', 'In Progress', 'Overdue' ) as $statusKey ) {
            if ( $key = CRM_Utils_Array::key( $statusKey, $allStatus ) ) {
                $unpaidStatus[] = $key;
            }
        } 
        
        $statusIds = implode( ', ', $unpaidStatus );

        $this->_from = "
        FROM civicrm_contact {$this->_aliases['civicrm_contact']} {$this->_aclFrom}
             INNER JOIN civicrm_pledge  {$this->_aliases['civicrm_pledge']} 
                        ON ({$this->_aliases['civicrm_pledge']}.contact_id =
                            {$this->_aliases['civicrm_contact']}.id)  AND 
                            {$this->_aliases['civicrm_pledge']}.status_id IN ( {$statusIds} )
             LEFT  JOIN civicrm_pledge_payment {$this->_aliases['civicrm_pledge_payment']}
                        ON ({$this->_aliases['civicrm_pledge']}.id =
                            {$this->_aliases['civicrm_pledge_payment']}.pledge_id AND  {$this->_aliases['civicrm_pledge_payment']}.status_id = {$pendingStatus} ) ";
        
        // include address field if address column is to be included
        if ( $this->_addressField ) {  
            $this->_from .= "
             LEFT  JOIN civicrm_address {$this->_aliases['civicrm_address']} 
                        ON ({$this->_aliases['civicrm_contact']}.id = 
                            {$this->_aliases['civicrm_address']}.contact_id) AND
                            {$this->_aliases['civicrm_address']}.is_primary = 1\n";
        }
        
        // include email field if email column is to be included
        if ( $this->_emailField ) { 
            $this->_from .= "
            LEFT  JOIN civicrm_email {$this->_aliases['civicrm_email']} 
                       ON ({$this->_aliases['civicrm_contact']}.id = 
                           {$this->_aliases['civicrm_email']}.contact_id) AND 
                           {$this->_aliases['civicrm_email']}.is_primary = 1\n";     
        }
    }      
    
    function groupBy( ) {
        $this->_groupBy = "";
        $this->_groupBy = "
         GROUP BY {$this->_aliases['civicrm_pledge']}.contact_id, 
                  {$this->_aliases['civicrm_pledge']}.id";
    }
    
    function postProcess( ) {
        // get the acl clauses built before we assemble the query
        $this->buildACLClause( $this->_aliases['civicrm_contact'] );
        parent::PostProcess();
    }
    
    function alterDisplay( &$rows ) {
        // custom code to alter rows
        $entryFound = false;
        $checkList  = array();
        $display_flag = $prev_cid = $cid = 0;
        
        foreach ( $rows as $rowNum => $row ) {
            if ( !empty($this->_noRepeats) && $this->_outputMode != 'csv' ) {
                // don't repeat contact details if its same as the previous row
                if ( array_key_exists('civicrm_contact_id', $row ) ) {
                    if ( $cid =  $row['civicrm_contact_id'] ) {
                        if ( $rowNum == 0 ) {
                            $prev_cid = $cid;
                        } else {
                            if( $prev_cid == $cid ) {
                                $display_flag = 1;
                                $prev_cid = $cid;
                            } else {
                                $display_flag = 0;
                                $prev_cid = $cid;
                            }
                        }
                        
                        if ( $display_flag ) {
                            foreach ( $row as $colName => $colVal ) {
                                if ( in_array($colName, $this->_noRepeats) ) {
                                    unset($rows[$rowNum][$colName]);          
                                }
                            }
                        }
                        $entryFound = true;
                    }
                }
            }            
            
            //handle the Contribution Type Ids
            if ( array_key_exists('civicrm_pledge_contribution_type_id', $row) ) {
                if ( $value = $row['civicrm_pledge_contribution_type_id'] ) {
                    $rows[$rowNum]['civicrm_pledge_contribution_type_id'] = 
                        CRM_Contribute_PseudoConstant::contributionType( $value, false );
                }
                $entryFound = true;
            }             
            
            //handle the Status Ids
            if ( array_key_exists( 'civicrm_pledge_status_id', $row ) ) {
                if ( $value = $row['civicrm_pledge_status_id'] ) {
                    $rows[$rowNum]['civicrm_pledge_status_id'] = 
                        CRM_Core_OptionGroup::getLabel( 'contribution_status', $value );
                }
                $entryFound = true;
            } 
            
            // handle state province
            if ( array_key_exists('civicrm_address_state_province_id', $row) ) {
                if ( $value = $row['civicrm_address_state_province_id'] ) {
                    $rows[$rowNum]['civicrm_address_state_province_id'] = 
                        CRM_Core_PseudoConstant::stateProvinceAbbreviation( $value, false );
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
            if ( array_key_exists('civicrm_contact_display_name', $row) && 
                 array_key_exists('civicrm_contact_id', $row) ) {
                $url = CRM_Report_Utils_Report::getNextUrl( 'pledge/summary', 
                                                            'reset=1&force=1&id_op=eq&id_value=' .
                                                            $row['civicrm_contact_id'],
                                                            $this->_absoluteUrl, $this->_id );
                $rows[$rowNum]['civicrm_contact_display_name_link' ] = $url;
                $rows[$rowNum]['civicrm_contact_display_name_hover' ] = 
                    ts("View Pledge Details for this contact");
                $entryFound = true;
            }
            
            // skip looking further in rows, if first row itself doesn't 
            // have the column we need
            if ( !$entryFound ) {
                break;
            }
        }
    }
}
