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
require_once 'CRM/Contribute/PseudoConstant.php';

class CRM_Report_Form_Contribute_Bookkeeping extends CRM_Report_Form {
    protected $_addressField = false;

    protected $_emailField   = false;

    protected $_summary      = null;

    protected $_customGroupExtends = array( 'Membership' );

    function __construct( ) {
        $this->_columns = 
            array( 'civicrm_contact'      =>
                   array( 'dao'     => 'CRM_Contact_DAO_Contact',
                          'fields'  =>
                          array( 'sort_name' => 
                                 array( 'title' => ts( 'Contact Name' ),
                                        'required'  => true,
                                        'no_repeat' => true ),
                                 'id'           => 
                                 array( 'no_display' => true,
                                        'required'  => true, ), ),
                          'filters' =>             
                          array('sort_name'    => 
                                array( 'title'      => ts( 'Contact Name' ),
                                       'operator'   => 'like' ),
                                'id'    => 
                                array( 'title'      => ts( 'Contact ID' ),
                                       'no_display' => true ), ),
                          'grouping'=> 'contact-fields',
                          ),
                          
                   'civicrm_membership' =>
                   array( 'dao' => 'CRM_Member_DAO_Membership',
                   		  'fields' =>
                   		array( 'id' =>
                   			array( 	'title' => ts( 'Membership #' ),
                   					'no_display' => true,
                   					'required' => true, 
                   		     	 ), 
                   		     ),
                        ),
 
                   'civicrm_contribution' =>
                   array( 'dao'     => 'CRM_Contribute_DAO_Contribution',
                          'fields'  =>
                          array(
                                 'receive_date'         	=> array( 'default' => true ),
                                 'total_amount'         	=> array( 'title'        => ts( 'Amount' ),
                                                                    'required'     => true,
                                                                    'statistics'   => 
                                                                          array('sum' => ts( 'Amount' )),
                                                                  ),
                          		 'contribution_type_id' 	=> array( 'title'   => ts('Contribution Type'),
                                                                  'default' => true,
                                                                ),
                                 'trxn_id'              	=> array( 'title' => ts('Trans #'),
                                                                  'default' => true,
                                                                ),
                                 'invoice_id'				=> array( 'title' => ts('Invoice ID'),
                                                                  'default' => true,
                                                                ),
                                 'check_number'				=> array( 'title' => ts('Cheque #'),
                                                                  'default' => true,
                                                                ),
                                 'payment_instrument_id' 	=> array( 'title' => ts('Payment Instrument'),
                                                                  'default' => true,
                                                                ),
                                 'contribution_status_id' 	=> array( 'title' => ts('Status'),
                                                                'default' => true,
                                                                ),
                                 'id'						=> array( 'title' => ts('Contribution #'),
                                                                  'default' => true,
                                                                ),
                                 ),
                          'filters' =>             
                          array( 'receive_date'           => 
                                    array( 'operatorType' => CRM_Report_Form::OP_DATE ),
                                 'contribution_type_id'   =>
                                    array( 'title'        => ts( 'Contribution Type' ), 
                                           'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                           'options'      => CRM_Contribute_PseudoConstant::contributionType( )
                                         ),
                                 'payment_instrument_id'   =>
                                    array( 'title'        => ts( 'Paid By' ), 
                                           'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                           'options'      => CRM_Contribute_PseudoConstant::paymentInstrument( )
                                         ),
                                'contribution_status_id' => 
                                    array( 'title'        => ts( 'Contribution Status' ), 
                                        'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                        'options'      => CRM_Contribute_PseudoConstant::contributionStatus( ),
                                        'default'      => array( 1 ),
                                        ),
                                 'total_amount'           => 
                                    array( 'title'        => ts( 'Contribution Amount' ) ), ),
                          'grouping'=> 'contri-fields',
                          ),
                   
                   );
        
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
        FROM  civicrm_contact      {$this->_aliases['civicrm_contact']} {$this->_aclFrom}
              INNER JOIN civicrm_contribution {$this->_aliases['civicrm_contribution']} 
                      ON {$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_contribution']}.contact_id AND {$this->_aliases['civicrm_contribution']}.is_test = 0
              LEFT JOIN civicrm_membership_payment payment
                        ON ( {$this->_aliases['civicrm_contribution']}.id = payment.contribution_id )
              LEFT JOIN civicrm_membership {$this->_aliases['civicrm_membership']}
              		  ON payment.membership_id = {$this->_aliases['civicrm_membership']}.id ";
    }


    function groupBy( ) {
        $this->_groupBy = "";
    }

    function orderBy( ) {
        $this->_orderBy = " ORDER BY {$this->_aliases['civicrm_contact']}.sort_name, {$this->_aliases['civicrm_contribution']}.id ";
    }

    function postProcess( ) {
        // get the acl clauses built before we assemble the query
        $this->buildACLClause( $this->_aliases['civicrm_contact'] );
        parent::postProcess( );
    }

    function statistics( &$rows ) {
        $statistics = parent::statistics( $rows );

        $select = "
        SELECT COUNT({$this->_aliases['civicrm_contribution']}.total_amount ) as count,
               SUM( {$this->_aliases['civicrm_contribution']}.total_amount ) as amount,
               ROUND(AVG({$this->_aliases['civicrm_contribution']}.total_amount), 2) as avg
        ";

        $sql = "{$select} {$this->_from} {$this->_where}";
        $dao = CRM_Core_DAO::executeQuery( $sql );

        if ( $dao->fetch( ) ) {
            $statistics['counts']['amount']    = array( 'value' => $dao->amount,
                                                        'title' => 'Total Amount',
                                                        'type'  => CRM_Utils_Type::T_MONEY );
            $statistics['counts']['avg']       = array( 'value' => $dao->avg,
                                                        'title' => 'Average',
                                                        'type'  => CRM_Utils_Type::T_MONEY );
        }

        return $statistics;
    }
    
    function alterDisplay( &$rows ) {
        // custom code to alter rows
        $checkList  = array();
        $entryFound = false;
        $display_flag = $prev_cid = $cid =  0;
        $contributionTypes = CRM_Contribute_PseudoConstant::contributionType( );
        $paymentInstruments = CRM_Contribute_PseudoConstant::paymentInstrument( );
        

        foreach ( $rows as $rowNum => $row ) {

        	// convert display name to links
        	if ( array_key_exists('civicrm_contact_sort_name', $row) && 
        	     CRM_Utils_Array::value( 'civicrm_contact_sort_name', $rows[$rowNum] ) && 
    	         array_key_exists('civicrm_contact_id', $row) ) {
	            $url = CRM_Utils_System::url( "civicrm/contact/view"  , 
                    	                      'reset=1&cid=' . $row['civicrm_contact_id'],
                	                          $this->_absoluteUrl );
            	$rows[$rowNum]['civicrm_contact_sort_name_link' ] = $url;
        	    $rows[$rowNum]['civicrm_contact_sort_name_hover'] =  
    	            ts("View Contact Summary for this Contact.");
	        }
        	
        	// handle contribution status id
            if ( array_key_exists('civicrm_contribution_contribution_status_id', $row) ) {
                if ( $value = $row['civicrm_contribution_contribution_status_id'] ) {
                    $rows[$rowNum]['civicrm_contribution_contribution_status_id'] = 
                        CRM_Contribute_PseudoConstant::contributionStatus( $value );
                }
                $entryFound = true;
            }
            
            // handle payment instrument id
            if ( array_key_exists('civicrm_contribution_payment_instrument_id', $row) ) {
                if ( $value = $row['civicrm_contribution_payment_instrument_id'] ) {
                    $rows[$rowNum]['civicrm_contribution_payment_instrument_id'] = 
                        $paymentInstruments[$value];
                }
                $entryFound = true;
            }
            
            if ( $value = CRM_Utils_Array::value( 'civicrm_contribution_contribution_type_id', $row ) ) {
                $rows[$rowNum]['civicrm_contribution_contribution_type_id'] = $contributionTypes[$value];
                $entryFound = true;
            }

            // skip looking further in rows, if first row itself doesn't 
            // have the column we need
            if ( !$entryFound ) {
                break;
            }
            $lastKey = $rowNum;
        }
    }

}
