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

class CRM_Contribute_BAO_Query 
{
    
    /**
     * static field for all the export/import contribution fields
     *
     * @var array
     * @static
     */
    static $_contributionFields = null;
    
    /**
     * Function get the import/export fields for contribution
     *
     * @return array self::$_contributionFields  associative array of contribution fields
     * @static
     */
    static function &getFields( ) 
    {
        if ( ! self::$_contributionFields ) {
            self::$_contributionFields = array( );
            
            require_once 'CRM/Contribute/BAO/Contribution.php';
            $fields =& CRM_Contribute_BAO_Contribution::exportableFields( );
            
            // add field to get recur_id
            $fields['contribution_recur_id'] = array('name'  => 'contribution_recur_id',
                                                     'title' => ts('Recurring Contributions ID'),
                                                     'where' => 'civicrm_contribution.contribution_recur_id'
                                                     );
            $fields['contribution_note']     = array('name'  => 'contribution_note',
                                                     'title' => ts('Contribution Note')
                                                     );

            unset( $fields['contribution_contact_id'] );

            self::$_contributionFields = $fields;
        }
        return self::$_contributionFields;
    }
        
    /** 
     * if contributions are involved, add the specific contribute fields
     * 
     * @return void  
     * @access public  
     */
    static function select( &$query ) 
    {
        // if contribute mode add contribution id
        if ( $query->_mode & CRM_Contact_BAO_Query::MODE_CONTRIBUTE ) {
            $query->_select['contribution_id'] = "civicrm_contribution.id as contribution_id";
            $query->_element['contribution_id'] = 1;
            $query->_tables['civicrm_contribution'] = 1;
            $query->_whereTables['civicrm_contribution'] = 1;
        }

        // get contribution_type
        if ( CRM_Utils_Array::value( 'contribution_type', $query->_returnProperties ) ) {
            $query->_select['contribution_type']  = "civicrm_contribution_type.name as contribution_type";
            $query->_element['contribution_type'] = 1;
            $query->_tables['civicrm_contribution'] = 1;
            $query->_tables['civicrm_contribution_type'] = 1;
            $query->_whereTables['civicrm_contribution'] = 1;
            $query->_whereTables['civicrm_contribution_type'] = 1;
        }
        
        if ( CRM_Utils_Array::value( 'contribution_note', $query->_returnProperties ) ) {
            $query->_select['contribution_note']  = "civicrm_note_contribution.note as contribution_note";
            $query->_element['contribution_note'] = 1;
            $query->_tables['contribution_note']  = 1;
        }

        // get contribution_status
        if ( CRM_Utils_Array::value( 'contribution_status_id', $query->_returnProperties ) ) {
            $query->_select['contribution_status_id']  = "contribution_status.name as contribution_status_id";
            $query->_element['contribution_status_id'] = 1;
            $query->_tables['civicrm_contribution'] = 1;
            $query->_tables['contribution_status'] = 1;
            $query->_whereTables['civicrm_contribution'] = 1;
            $query->_whereTables['contribution_status'] = 1;
        }
        
        // get payment instruments
        if ( CRM_Utils_Array::value( 'payment_instrument', $query->_returnProperties ) ) {
            $query->_select['contribution_payment_instrument']  = "payment_instrument.name as contribution_payment_instrument";
            $query->_element['contribution_payment_instrument'] = 1;
            $query->_tables['civicrm_contribution'] = 1;
            $query->_tables['contribution_payment_instrument'] = 1;
            $query->_whereTables['civicrm_contribution'] = 1;
            $query->_whereTables['contribution_payment_instrument'] = 1;
        }

        if ( CRM_Utils_Array::value( 'check_number', $query->_returnProperties ) ) {
            $query->_select['contribution_check_number']  = "civicrm_contribution.check_number as contribution_check_number";
            $query->_element['contribution_check_number'] = 1;
            $query->_tables['civicrm_contribution'] = 1;
            $query->_whereTables['civicrm_contribution'] = 1;
        }
    }

    static function where( &$query ) 
    {
        $isTest   = false;
        $grouping = null;
        foreach ( array_keys( $query->_params ) as $id ) {
            if ( substr( $query->_params[$id][0], 0, 13 ) == 'contribution_' ) {
                if ( $query->_mode == CRM_Contact_BAO_QUERY::MODE_CONTACTS ) {
                    $query->_useDistinct = true;
                }
                if ( $query->_params[$id][0] == 'contribution_test' ) {
                    $isTest = true;
                }
                $grouping = $query->_params[$id][3];
                self::whereClauseSingle( $query->_params[$id], $query );
            }
        }

        if ( $grouping !== null &&
             ! $isTest ) {
            $values = array( 'contribution_test', '=', 0, $grouping, 0 );
            self::whereClauseSingle( $values, $query );
        }
    }

    static function whereClauseSingle( &$values, &$query ) 
    {
        list( $name, $op, $value, $grouping, $wildcard ) = $values;

        $fields = array( );
        $fields = self::getFields();
        if ( !empty ( $value ) ) {
            $quoteValue = "\"$value\"";
        }

        $strtolower = function_exists('mb_strtolower') ? 'mb_strtolower' : 'strtolower';

        switch ( $name ) {
       
        case 'contribution_date':
        case 'contribution_date_low':
        case 'contribution_date_high':
            // process to / from date
            $query->dateQueryBuilder( $values,
                                      'civicrm_contribution', 'contribution_date', 'receive_date', 'Contribution Date', false );
            return;

        case 'contribution_amount':
        case 'contribution_amount_low':
        case 'contribution_amount_high':
            // process min/max amount
            $query->numberRangeBuilder( $values,
                                        'civicrm_contribution', 'contribution_amount', 'total_amount', 'Contribution Amount' );
            return;

        case 'contribution_total_amount':
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( "civicrm_contribution.total_amount", 
                                                                              $op, $value, "Money" ) ;
            $query->_qill[$grouping ][] = ts( 'Contribution Total Amount %1 %2', array( 1 => $op, 2 => $value ) );
            $query->_tables['civicrm_contribution'] = $query->_whereTables['civicrm_contribution'] = 1;
            return;
            
        case 'contribution_thankyou_date_isnull':
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( "civicrm_contribution.thankyou_date", "IS NULL" );
            $query->_qill[$grouping ][] = ts( 'Contribution Thank-you date is null' );
            $query->_tables['civicrm_contribution'] = $query->_whereTables['civicrm_contribution'] = 1;
            return;

        case 'contribution_receipt_date_isnull':
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( "civicrm_contribution.receipt_date", "IS NULL" );
            $query->_qill[$grouping ][] = ts( 'Contribution Receipt date is null' );
            $query->_tables['civicrm_contribution'] = $query->_whereTables['civicrm_contribution'] = 1;
            return;

        case 'contribution_type_id':
        case 'contribution_type':
            require_once 'CRM/Contribute/PseudoConstant.php';
            $cType = $value;
            $types = CRM_Contribute_PseudoConstant::contributionType( );
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( "civicrm_contribution.contribution_type_id", 
                                                                              $op, $value, "Integer" ) ;
            $query->_qill[$grouping ][] = ts( 'Contribution Type - %1', array( 1 => $types[$cType] ) );
            $query->_tables['civicrm_contribution'] = $query->_whereTables['civicrm_contribution'] = 1;
            return;
            
        case 'contribution_page_id':
            require_once 'CRM/Contribute/PseudoConstant.php';
            $cPage = $value;
            $pages = CRM_Contribute_PseudoConstant::contributionPage( );
            $query->_where[$grouping][] = "civicrm_contribution.contribution_page_id = $cPage";
            $query->_qill[$grouping ][] = ts( 'Contribution Page - %1', array( 1 => $pages[$cPage] ) );
            $query->_tables['civicrm_contribution'] = $query->_whereTables['civicrm_contribution'] = 1;
            return;

        case 'contribution_pcp_made_through_id':
            require_once 'CRM/Contribute/PseudoConstant.php';
            $pcPage = $value;
            $pcpages = CRM_Contribute_PseudoConstant::pcPage( );
            $query->_where[$grouping][] = "civicrm_contribution_soft.pcp_id = $pcPage";
            $query->_qill[$grouping ][] = ts( 'Personal Campaign Page - %1', array( 1 => $pcpages[$pcPage] ) );
            $query->_tables['civicrm_contribution_soft'] = $query->_whereTables['civicrm_contribution_soft'] = 1;
            return;
            
        case 'contribution_payment_instrument_id':
        case 'contribution_payment_instrument':
            require_once 'CRM/Contribute/PseudoConstant.php';
            $pi  = $value;
            $pis = CRM_Contribute_PseudoConstant::paymentInstrument( );
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( "civicrm_contribution.payment_instrument_id", 
                                                                              $op, $value, "Integer" ) ;

            $query->_qill[$grouping ][] = ts( 'Paid By - %1', array( 1 => $pis[$pi] ) );
            $query->_tables['civicrm_contribution'] = $query->_whereTables['civicrm_contribution'] = 1;
            return;

        case 'contribution_in_honor_of':
            $name    = trim( $value ); 
            $newName = str_replace(',' , " " ,$name );
            $pieces  =  explode( ' ', $newName ); 
            foreach ( $pieces as $piece ) { 
                $value = $strtolower(CRM_Core_DAO::escapeString(trim($piece)));
                $value = "'%$value%'";
                $sub[] = " ( contact_b.sort_name LIKE $value )";
            }
            
            $query->_where[$grouping][] = ' ( ' . implode( '  OR ', $sub ) . ' ) '; 
            $query->_qill[$grouping][]  = ts( 'Honor name like - \'%1\'', array( 1 => $name ) );
            $query->_tables['civicrm_contact_b'] = $query->_whereTables['civicrm_contact_b'] = 1;
            $query->_tables['civicrm_contribution'] = $query->_whereTables['civicrm_contribution'] = 1;
            return;

        case 'contribution_status_id':
            if ( is_array( $value ) ) {
                foreach ($value as $k => $v) {
                    if ( $v ) {
                        $val[$k] = $k;
                    }
                } 
            
                $status = implode (',' ,$val);
                
                if ( count($val) > 1 ) {
                    $op = 'IN';
                    $status = "({$status})";
                }     
            } else {
                $op = '=';
                $status = $value;
            }

            require_once "CRM/Core/OptionGroup.php";
            $statusValues = CRM_Core_OptionGroup::values("contribution_status");
            
            $names = array( );
            if ( is_array( $val ) ) {
                foreach ( $val as $id => $dontCare ) {
                    $names[] = $statusValues[ $id ];
                }
            } else {
                $names[] = $statusValues[ $value ];
            }

            $query->_qill[$grouping][] = ts('Contribution Status %1', array( 1 => $op ) ) . ' ' . implode( ' ' . ts('or') . ' ', $names );
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( "civicrm_contribution.contribution_status_id", 
                                                                              $op,
                                                                              $status,
                                                                              "Integer" );
            $query->_tables['civicrm_contribution'] = $query->_whereTables['civicrm_contribution'] = 1;
            return;

        case 'contribution_source':
            $value = $strtolower( CRM_Core_DAO::escapeString( $value ) );
            if ( $wildcard ) {
                $value = "%$value%";
                $op    = 'LIKE';
            }
            $wc = ( $op != 'LIKE' ) ? "LOWER(civicrm_contribution.source)" : "civicrm_contribution.source";
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( $wc, $op, $value, "String" ) ;
            $query->_qill[$grouping][]  = ts( 'Contribution Source %1 %2', array( 1 => $op, 2 => $quoteValue ) );
            $query->_tables['civicrm_contribution'] = $query->_whereTables['civicrm_contribution'] = 1;
            return;
        
        case 'contribution_trxn_id':
        case 'contribution_transaction_id':
            $wc = ( $op != 'LIKE' ) ? "LOWER(civicrm_contribution.trxn_id)" : "civicrm_contribution.trxn_id";
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( $wc, $op, $value, "String" ) ;
            $query->_qill[$grouping][]  = ts( 'Transaction ID %1 %2', array( 1 => $op, 2 => $quoteValue ) );
            $query->_tables['civicrm_contribution'] = $query->_whereTables['civicrm_contribution'] = 1;
            return;
            
        case 'contribution_check_number':
            $wc = ( $op != 'LIKE' ) ? "LOWER(civicrm_contribution.check_number)" : "civicrm_contribution.check_number";
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( $wc, $op, $value, "String" ) ;
            $query->_qill[$grouping][]  = ts( 'Check Number %1 %2', array( 1 => $op, 2 => $quoteValue ) );
            $query->_tables['civicrm_contribution'] = $query->_whereTables['civicrm_contribution'] = 1;
            return;
            
        case 'contribution_is_test':
        case 'contribution_test':
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( "civicrm_contribution.is_test", $op, $value, "Boolean" ) ;
            if ( $value ) {
                $query->_qill[$grouping][] = ts( "Find Test Contributions" );
            }
            $query->_tables['civicrm_contribution'] = $query->_whereTables['civicrm_contribution'] = 1;
            return;
            
        case 'contribution_is_pay_later':
        case 'contribution_pay_later':
            if ( $value ) {
                $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( "civicrm_contribution.is_pay_later", $op, $value, "Boolean" ) ;
                $query->_qill[$grouping][] = ts( "Find Pay Later Contributions" );
                $query->_tables['civicrm_contribution'] = $query->_whereTables['civicrm_contribution'] = 1;
            }
            return;
        
        case 'contribution_recurring':
            if ( $value ) {
                $query->_where[$grouping][] = "civicrm_contribution.contribution_recur_id IS NOT NULL";
                $query->_qill[$grouping][]  = ts( "Displaying Recurring Contributions" );
                $query->_tables['civicrm_contribution_recur'] = $query->_whereTables['civicrm_contribution_recur'] = 1;
            }
            return;

        case 'contribution_recur_id':
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( "civicrm_contribution.contribution_recur_id", 
                                                                              $op, $value, "Integer" ) ;
            $query->_tables['civicrm_contribution'] = $query->_whereTables['civicrm_contribution'] = 1;
            return;

        case 'contribution_id':
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( "civicrm_contribution.id", $op, $value, "Integer" ) ;
            $query->_qill[$grouping][]  = ts( 'Contribution ID %1 %2', array( 1 => $op, 2 => $quoteValue ) );
            $query->_tables['civicrm_contribution'] = $query->_whereTables['civicrm_contribution'] = 1;
            return;

        case 'contribution_note':
            $value = $strtolower( CRM_Core_DAO::escapeString( $value ) );
            if ( $wildcard ) {
                $value = "%$value%"; 
                $op    = 'LIKE';
            }
            $wc = ( $op != 'LIKE' ) ? "LOWER(civicrm_note.note)" : "civicrm_note.note";
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( $wc, $op, $value, "String" ) ;
            $query->_qill[$grouping][]  = ts( 'Contribution Note %1 %2', array( 1 => $op, 2 => $quoteValue ) );
            $query->_tables['civicrm_contribution'] = $query->_whereTables['civicrm_contribution'] = $query->_whereTables['contribution_note'] = 1;
            return;
            
        case 'contribution_membership_id':
            $query->_where[$grouping][] = " civicrm_membership.id $op $value";
            $query->_tables['contribution_membership'] = $query->_whereTables['contribution_membership'] = 1;
            
            return;

        case 'contribution_participant_id':
            $query->_where[$grouping][] = " civicrm_participant.id $op $value";
            $query->_tables['contribution_participant'] = $query->_whereTables['contribution_participant'] = 1;
            return;

        case 'contribution_pcp_display_in_roll':
            $query->_where[$grouping][] = " civicrm_contribution_soft.pcp_display_in_roll $op '$value'";
            if ( $value ) {
                $query->_qill[$grouping][] = ts( "Display in Roll" );
            }
            $query->_tables['civicrm_contribution_soft'] = $query->_whereTables['civicrm_contribution_soft'] = 1;
            return;

            //supporting search for currency type -- CRM-4711
        case 'contribution_currency_type':
            $currencySymbol = CRM_Core_PseudoConstant::currencySymbols( 'name' );
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( "civicrm_contribution.currency",
                                                                               $op, $currencySymbol[$value], "String"); 
            $query->_qill[$grouping][]  = ts( 'Currency Type - %1', array( 1 => $currencySymbol[$value] ) );
            $query->_tables['civicrm_contribution'] = $query->_whereTables['civicrm_contribution'] = 1;
            return;

        default: 
            //all other elements are handle in this case
            $fldName    = substr($name, 13 );
            $whereTable = $fields[$fldName];
            $value      = trim($value);
            
            //contribution fields (decimal fields) which don't require a quote in where clause.
            $moneyFields = array('non_deductible_amount', 'fee_amount', 'net_amount');
            //date fields
            $dateFields  = array ( 'receive_date', 'cancel_date', 'receipt_date', 'thankyou_date', 'fulfilled_date' ) ;
        
            if ( in_array($fldName, $dateFields) ) {
                $dataType = "Date";
            } elseif ( in_array($fldName, $moneyFields) ) {
                $dataType = "Money";
            } else {
                $dataType = "String";
            }
            
            $wc = ( $op != 'LIKE' && $dataType != 'Date' ) ? "LOWER($whereTable[where])" : "$whereTable[where]";
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( $wc, $op, $value, $dataType) ;
            $query->_qill[$grouping][]  = "$whereTable[title] $op $quoteValue";            
            list( $tableName, $fieldName ) = explode( '.', $whereTable['where'], 2 );  
            $query->_tables[$tableName] = $query->_whereTables[$tableName] = 1;
            if ($tableName == 'civicrm_contribution_product') {
                $query->_tables['civicrm_product']      = $query->_whereTables['civicrm_product'     ] = 1;
                $query->_tables['civicrm_contribution'] = $query->_whereTables['civicrm_contribution'] = 1;
            } else {
                $query->_tables['civicrm_contribution'] = $query->_whereTables['civicrm_contribution'] = 1;
            }
        }
    }

    static function from( $name, $mode, $side ) 
    {
        $from = null;
        switch ( $name ) {

        case 'civicrm_contribution':
            $from = " $side JOIN civicrm_contribution ON civicrm_contribution.contact_id = contact_a.id ";
            break;

        case 'civicrm_contribution_recur':
            $from = " $side JOIN civicrm_contribution_recur ON civicrm_contribution.contribution_recur_id = civicrm_contribution_recur.id ";
            break;

            
        case 'civicrm_contribution_type':
            if ( $mode & CRM_Contact_BAO_Query::MODE_CONTRIBUTE ) {
                $from = " INNER JOIN civicrm_contribution_type ON civicrm_contribution.contribution_type_id = civicrm_contribution_type.id ";
            } else {
                $from = " $side JOIN civicrm_contribution_type ON civicrm_contribution.contribution_type_id = civicrm_contribution_type.id ";
            }
      
            break;

        case 'civicrm_contribution_page':
            $from = " $side JOIN civicrm_contribution_page ON civicrm_contribution.contribution_page ON civicrm_contribution.contribution_page.id";
            break;

        case 'civicrm_product':
            $from = " $side  JOIN civicrm_contribution_product ON civicrm_contribution_product.contribution_id = civicrm_contribution.id";
            $from .= " $side  JOIN civicrm_product ON civicrm_contribution_product.product_id =civicrm_product.id ";
      
            break;
            
        case 'contribution_payment_instrument':
            $from = " $side JOIN civicrm_option_group option_group_payment_instrument ON ( option_group_payment_instrument.name = 'payment_instrument')";
            $from .= " $side JOIN civicrm_option_value payment_instrument ON (civicrm_contribution.payment_instrument_id = payment_instrument.value
                               AND option_group_payment_instrument.id = payment_instrument.option_group_id ) ";
            break;

        case 'civicrm_contact_b':
            $from .= " $side JOIN civicrm_contact contact_b ON (civicrm_contribution.honor_contact_id = contact_b.id )";
            
            break;

        case 'contribution_status':
            $from = " $side JOIN civicrm_option_group option_group_contribution_status ON (option_group_contribution_status.name = 'contribution_status')";
            $from .= " $side JOIN civicrm_option_value contribution_status ON (civicrm_contribution.contribution_status_id = contribution_status.value 
                               AND option_group_contribution_status.id = contribution_status.option_group_id ) ";
            break;
            
        case 'contribution_note':
            $from .= " $side JOIN civicrm_note civicrm_note_contribution ON ( civicrm_note_contribution.entity_table = 'civicrm_contribution' AND
                                                        civicrm_contribution.id = civicrm_note_contribution.entity_id )";
            break;

        case 'contribution_membership':
            $from  = " $side  JOIN civicrm_membership_payment ON civicrm_membership_payment.contribution_id = civicrm_contribution.id";
            $from .= " $side  JOIN civicrm_membership ON civicrm_membership_payment.membership_id = civicrm_membership.id ";
            break;

        case 'contribution_participant':
            $from  = " $side  JOIN civicrm_participant_payment ON civicrm_participant_payment.contribution_id = civicrm_contribution.id";
            $from .= " $side  JOIN civicrm_participant ON civicrm_participant_payment.participant_id = civicrm_participant.id ";
            break;

        case 'civicrm_contribution_soft':
            $from = " $side JOIN civicrm_contribution_soft ON civicrm_contribution_soft.contribution_id = civicrm_contribution.id";
            break;
        }
        return $from;
    }

    static function defaultReturnProperties( $mode ) 
    {
        $properties = null;
        if ( $mode & CRM_Contact_BAO_Query::MODE_CONTRIBUTE ) {
            $properties = array(  
                                'contact_type'            => 1, 
                                'contact_sub_type'        => 1, 
                                'sort_name'               => 1, 
                                'display_name'            => 1,
                                'contribution_type'       => 1,
                                'contribution_source'     => 1,
                                'receive_date'            => 1,
                                'thankyou_date'           => 1,
                                'cancel_date'             => 1,
                                'total_amount'            => 1,
                                'accounting_code'         => 1,
                                'payment_instrument'      => 1,
                                'check_number'            => 1,
                                'non_deductible_amount'   => 1,
                                'fee_amount'              => 1,
                                'net_amount'              => 1,
                                'trxn_id'                 => 1,
                                'invoice_id'              => 1,
                                'currency'                => 1,
                                'cancel_date'             => 1,
                                'cancel_reason'           => 1,
                                'receipt_date'            => 1,
                                'thankyou_date'           => 1,
                                'product_name'            => 1,
                                'sku'                     => 1,
                                'product_option'          => 1,
                                'fulfilled_date'          => 1,
                                'contribution_start_date' => 1,
                                'contribution_end_date'   => 1,
                                'is_test'                 => 1,
                                'is_pay_later'            => 1,
                                'contribution_status_id'  => 1,
                                'contribution_recur_id'   => 1, 
                                'amount_level'            => 1,
                                'contribution_note'       => 1
                                );

            // also get all the custom contribution properties
            require_once "CRM/Core/BAO/CustomField.php";
            $fields = CRM_Core_BAO_CustomField::getFieldsForImport('Contribution');
            if ( ! empty( $fields ) ) {
                foreach ( $fields as $name => $dontCare ) {
                    $properties[$name] = 1;
                }
            }
        }
        return $properties;
    }


    /**
     * add all the elements shared between contribute search and advnaced search
     *
     * @access public 
     * @return void
     * @static
     */ 
    static function buildSearchForm( &$form ) 
    {
        require_once 'CRM/Utils/Money.php';

        //added contribution source
        $form->addElement('text', 'contribution_source', ts('Contribution Source'), CRM_Core_DAO::getAttribute('CRM_Contribute_DAO_Contribution', 'source') );
        
        $form->addDate( 'contribution_date_low', ts('Contribution Dates - From'), false, array( 'formatType' => 'searchDate') );
        $form->addDate( 'contribution_date_high', ts('To'), false, array( 'formatType' => 'searchDate') );
 
        $form->add('text', 'contribution_amount_low', ts('From'), array( 'size' => 8, 'maxlength' => 8 ) ); 
        $form->addRule('contribution_amount_low', ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('9.99', ' '))), 'money');

        $form->add('text', 'contribution_amount_high', ts('To'), array( 'size' => 8, 'maxlength' => 8 ) ); 
        $form->addRule('contribution_amount_high', ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('99.99', ' '))), 'money');

        //adding select option for curreny type -- CRM-4711
        require_once 'CRM/Core/PseudoConstant.php';
        $form->add('select', 'contribution_currency_type',
                   ts( 'Currency Type' ),
                   array( '' => ts( '- select -' ) ) +
                   CRM_Core_PseudoConstant::currencySymbols( 'name' ) );

        require_once 'CRM/Contribute/PseudoConstant.php';
        $form->add('select', 'contribution_type_id', 
                   ts( 'Contribution Type' ),
                   array( '' => ts( '- select -' ) ) +
                   CRM_Contribute_PseudoConstant::contributionType( ) );

        $form->add('select', 'contribution_page_id', 
                   ts( 'Contribution Page' ),
                   array( '' => ts( '- select -' ) ) +
                   CRM_Contribute_PseudoConstant::contributionPage( ) );

        
        $form->add('select', 'contribution_payment_instrument_id', 
                   ts( 'Payment Instrument' ), 
                   array( '' => ts( '- select -' ) ) +
                   CRM_Contribute_PseudoConstant::paymentInstrument( ) );

        $form->add('select', 'contribution_pcp_made_through_id', 
                   ts( 'Personal Campaign Page' ),
                   array( '' => ts( '- select -' ) ) +
                   CRM_Contribute_PseudoConstant::pcPage( ) );
        
        $status = array( );
        
        require_once "CRM/Core/OptionGroup.php";
        $statusValues = CRM_Core_OptionGroup::values("contribution_status");
        // Remove status values that are only used for recurring contributions or pledges (In Progress, Overdue).
        unset( $statusValues['5']);
        unset( $statusValues['6']);

        foreach ( $statusValues as $key => $val ) {
            $status[] =  $form->createElement('advcheckbox',$key, null, $val );
        }
        
        $form->addGroup( $status, 'contribution_status_id', ts( 'Contribution Status' ) );
        
        // add null checkboxes for thank you and receipt
        $form->addElement( 'checkbox', 'contribution_thankyou_date_isnull', ts( 'Thank-you date not set?' ) );
        $form->addElement( 'checkbox', 'contribution_receipt_date_isnull' , ts( 'Receipt not sent?' ) );

        //add fields for honor search
        $form->addElement( 'text', 'contribution_in_honor_of', ts( "In Honor Of" ) );

        $form->addElement( 'checkbox', 'contribution_test' , ts( 'Find Test Contributions?' ) );
        $form->addElement( 'checkbox', 'contribution_pay_later' , ts( 'Find Pay Later Contributions?' ) );

        //add field for transaction ID search
        $form->addElement( 'text', 'contribution_transaction_id', ts( "Transaction ID" ) );

        $form->addElement( 'checkbox', 'contribution_recurring' , ts( 'Find Recurring Contributions?' ) );
        $form->addElement('text', 'contribution_check_number', ts('Check Number') );
        
        //add field for pcp display in roll search
        $form->addYesNo( 'contribution_pcp_display_in_roll', ts('Display In Roll ?') );
        
        // add all the custom  searchable fields
        require_once 'CRM/Core/BAO/CustomGroup.php';
        $contribution = array( 'Contribution' );
        $groupDetails = CRM_Core_BAO_CustomGroup::getGroupDetail( null, true, $contribution );
        if ( $groupDetails ) {
            require_once 'CRM/Core/BAO/CustomField.php';
            $form->assign('contributeGroupTree', $groupDetails);
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

        $form->assign( 'validCiviContribute', true );
    }

    static function addShowHide( &$showHide ) 
    {
        $showHide->addHide( 'contributeForm' );
        $showHide->addShow( 'contributeForm_show' );
    }

    static function searchAction( &$row, $id ) 
    {
    }

    static function tableNames( &$tables ) 
    {
        //add contribution table
        if ( CRM_Utils_Array::value( 'civicrm_product', $tables ) ) {
            $tables = array_merge( array( 'civicrm_contribution' => 1), $tables );
        }
    }
}

