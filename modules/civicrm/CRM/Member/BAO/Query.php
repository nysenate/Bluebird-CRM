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

class CRM_Member_BAO_Query 
{
    
    static function &getFields( ) 
    {
        require_once 'CRM/Member/BAO/Membership.php';
        $fields =& CRM_Member_BAO_Membership::exportableFields( );
        return $fields;
    }
    

    /** 
     * if membership are involved, add the specific membership fields
     * 
     * @return void  
     * @access public  
     */
    static function select( &$query ) 
    {
        // if membership mode add membership id
        if ( $query->_mode & CRM_Contact_BAO_Query::MODE_MEMBER ||
             CRM_Utils_Array::value( 'membership_id', $query->_returnProperties ) ) {

            $query->_select['membership_id'] = "civicrm_membership.id as membership_id";
            $query->_element['membership_id'] = 1;
            $query->_tables['civicrm_membership'] = 1;
            $query->_whereTables['civicrm_membership'] = 1;
           
            //add membership type
            if ( CRM_Utils_Array::value( 'membership_type', $query->_returnProperties ) ) {
                $query->_select['membership_type']  = "civicrm_membership_type.name as membership_type";
                $query->_element['membership_type'] = 1;
                $query->_tables['civicrm_membership_type'] = 1;
                $query->_whereTables['civicrm_membership_type'] = 1;
            }
            
            //add join date
            if ( CRM_Utils_Array::value( 'join_date', $query->_returnProperties ) ) {
                $query->_select['join_date']  = "civicrm_membership.join_date as join_date";
                $query->_element['join_date'] = 1;
            }
            
            //add source
            if ( CRM_Utils_Array::value( 'membership_source', $query->_returnProperties ) ) {
                $query->_select['membership_source']  = "civicrm_membership.source as membership_source";
                $query->_element['membership_source'] = 1;
            }

            //add status
            if ( CRM_Utils_Array::value( 'membership_status', $query->_returnProperties ) ) {
                $query->_select['membership_status']  = "civicrm_membership_status.label as membership_status";
                $query->_element['membership_status'] = 1;
                $query->_tables['civicrm_membership_status'] = 1;
                $query->_whereTables['civicrm_membership_status'] = 1;
            }

            if ( CRM_Utils_Array::value( 'status_id', $query->_returnProperties ) ) {
                $query->_select['status_id']  = "civicrm_membership_status.id as status_id";
                $query->_element['status_id'] = 1;
                $query->_tables['civicrm_membership_status'] = 1;
                $query->_whereTables['civicrm_membership_status'] = 1;
            }
            
            //add start date / end date
            if ( CRM_Utils_Array::value( 'membership_start_date', $query->_returnProperties ) ) {
                $query->_select['membership_start_date']  = "civicrm_membership.start_date as membership_start_date";
                $query->_element['membership_start_date'] = 1;
            }

            if ( CRM_Utils_Array::value( 'membership_end_date', $query->_returnProperties ) ) {
                $query->_select['membership_end_date']  = "civicrm_membership.end_date as  membership_end_date";
                $query->_element['membership_end_date'] = 1;
            }

            //add owner_membership_id
            if ( CRM_Utils_Array::value( 'owner_membership_id', $query->_returnProperties ) ) {
                $query->_select['owner_membership_id']  = "civicrm_membership.owner_membership_id as owner_membership_id";
                $query->_element['owner_membership_id'] = 1;
            }
            //add recur id w/o taking contribution table in join.
            if ( CRM_Utils_Array::value( 'membership_recur_id', $query->_returnProperties ) ) {
                $query->_select['membership_recur_id']  = "civicrm_membership.contribution_recur_id as membership_recur_id";
                $query->_element['membership_recur_id'] = 1;
            }
        }
    }

    static function where( &$query ) 
    {
        $isTest   = false;
        $grouping = null;
        foreach ( array_keys( $query->_params ) as $id ) {
            if ( substr( $query->_params[$id][0], 0, 7 ) == 'member_' ) {
                if ( $query->_mode == CRM_Contact_BAO_QUERY::MODE_CONTACTS ) {
                    $query->_useDistinct = true;
                }
                if ( $query->_params[$id][0] == 'member_test' ) {
                    $isTest = true;
                }
                $grouping = $query->_params[$id][3];
                self::whereClauseSingle( $query->_params[$id], $query );
            }
        }

        if ( $grouping !== null &&
             ! $isTest ) {
            $values = array( 'member_test', '=', 0, $grouping, 0 );
            self::whereClauseSingle( $values, $query );
        }
    }
  
    static function whereClauseSingle( &$values, &$query ) 
    {
        list( $name, $op, $value, $grouping, $wildcard ) = $values;
        switch( $name ) {

        case 'member_join_date_low':
        case 'member_join_date_high':
            $query->dateQueryBuilder( $values,
                                      'civicrm_membership', 'member_join_date', 'join_date',
                                      'Member Since' );
            return;
        case 'member_start_date_low':
        case 'member_start_date_high':
            $query->dateQueryBuilder( $values,
                                      'civicrm_membership', 'member_start_date', 'start_date',
                                      'Start Date' );
            return;

        case 'member_end_date_low':
        case 'member_end_date_high':
            $query->dateQueryBuilder( $values,
                                       'civicrm_membership', 'member_end_date', 'end_date',
                                      'End Date' );
            return;

        case 'member_join_date':
            $op = '>=';
            $date = CRM_Utils_Date::format( $value );
            if ( $date ) {
                $query->_where[$grouping][] = "civicrm_membership.join_date {$op} {$date}";
                $date = CRM_Utils_Date::customFormat( $value );
                $format  = CRM_Utils_Date::customFormat( CRM_Utils_Date::format( array_reverse($value), '-' ) );
                $query->_qill[$grouping ][] = ts( 'Member Since %2 %1', array( 1 => $format, 2 => $op) );
            }

            return;
            
        case 'member_source':
            $strtolower = function_exists('mb_strtolower') ? 'mb_strtolower' : 'strtolower';
            $value = $strtolower(CRM_Core_DAO::escapeString(trim($value)));

            $query->_where[$grouping][] = "civicrm_membership.source $op '{$value}'";
            $query->_qill[$grouping ][] = ts( 'Source %2 %1', array( 1 => $value, 2 => $op) );
            $query->_tables['civicrm_membership'] = $query->_whereTables['civicrm_membership'] = 1;
            return;

        case 'member_status_id':
            require_once 'CRM/Member/PseudoConstant.php';
            $status = implode (',' ,array_keys($value));
            
            if (count($value) > 1) {
                $op = 'IN';
                $status = "({$status})";
            }     
            
            $names = array( );
            $statusTypes = CRM_Member_PseudoConstant::membershipStatus(null, null, 'label');
            foreach ( $value as $id => $dontCare ) {
                $names[] = $statusTypes[$id];
            }
            $query->_qill[$grouping][]  = ts('Membership Status %1', array( 1 => $op ) ) . ' ' . implode( ' ' . ts('or') . ' ', $names );
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( "civicrm_membership.status_id", 
                                                                              $op,
                                                                              $status,
                                                                              "Integer" );
            $query->_tables['civicrm_membership'] = $query->_whereTables['civicrm_membership'] = 1;
            return;
            
        case 'member_test':
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( "civicrm_membership.is_test", 
                                                                              $op,
                                                                              $value,
                                                                              "Integer" );
            if ( $value ) {
                $query->_qill[$grouping][] = ts( "Find Test Memberships" );
            }
            $query->_tables['civicrm_membership'] = $query->_whereTables['civicrm_membership'] = 1;
            return;
            
        case 'member_auto_renew':
            $op = "!=";
            if ( $value ) {
                 $query->_where[$grouping][] = " civicrm_membership.contribution_recur_id IS NOT NULL";
                 $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( "ccr.contribution_status_id", 
                                                                                   $op,
                                                                                   CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_OptionValue', 'Cancelled', 'value', 'label'),
                                                                                   "Integer" );
                 $query->_qill[$grouping][] = ts( "Find Auto-renew Memberships" );
            }
            $query->_tables['civicrm_membership'] = $query->_whereTables['civicrm_membership'] = 1;
            return;
        case 'member_pay_later':
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( "civicrm_membership.is_pay_later", 
                                                                              $op,
                                                                              $value,
                                                                              "Integer" );
            if ( $value ) {
                $query->_qill[$grouping][] = ts( "Find Pay Later Memberships" );
            }
            $query->_tables['civicrm_membership'] = $query->_whereTables['civicrm_membership'] = 1;
            return;
            
        case 'member_membership_type_id':
            require_once 'CRM/Member/PseudoConstant.php';
            $mType = implode (',' , array_keys($value));
            if (count($value) > 1) {
                $op = 'IN';
                $mType = "({$mType})";
            }     

            $names = array( );
            $membershipTypes  = CRM_Member_PseudoConstant::membershipType( );
            foreach ( $value as $id => $dontCare ) {
                $names[] = $membershipTypes[$id];
            }
            $query->_qill[$grouping][]  = ts('Membership Type %1', array( 1 => $op ) ) . ' ' . implode( ' ' . ts('or') . ' ', $names );
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( "civicrm_membership.membership_type_id", 
                                                                              $op,
                                                                              $mType,
                                                                              "Integer" );
            $query->_tables['civicrm_membership'] = $query->_whereTables['civicrm_membership'] = 1;
            return;
            
        case 'member_id':
            $query->_where[$grouping][] = " civicrm_membership.id $op $value";
            $query->_tables['civicrm_membership'] = $query->_whereTables['civicrm_membership'] = 1;
            return;
            
        case 'member_is_primary':
            switch( $value ) {
                case 1:
                $query->_qill[$grouping][]  = ts( "Primary AND Related Members" );
                break;

                case 2:
                $query->_where[$grouping][] = " civicrm_membership.owner_membership_id IS NULL";
                $query->_qill[$grouping][]  = ts( "Primary Members Only" );
                break;

                case 3:
                $query->_where[$grouping][] = " civicrm_membership.owner_membership_id IS NOT NULL";
                $query->_qill[$grouping][]  = ts( "Related Members Only" );
                break;

            }
            $query->_tables['civicrm_membership'] = $query->_whereTables['civicrm_membership'] = 1;
            return;

        }
    }

    static function from( $name, $mode, $side ) 
    {
        $from = null;
        switch ( $name ) {
        
        case 'civicrm_membership':
            $from  = " $side JOIN civicrm_membership ON civicrm_membership.contact_id = contact_a.id ";
            $from .= " $side JOIN civicrm_contribution_recur ccr ON ( civicrm_membership.contribution_recur_id = ccr.id )";
            break;
    
        case 'civicrm_membership_type':
            if ( $mode & CRM_Contact_BAO_Query::MODE_MEMBER ) {
                $from = " INNER JOIN civicrm_membership_type ON civicrm_membership.membership_type_id = civicrm_membership_type.id ";
            } else {
                $from = " $side JOIN civicrm_membership_type ON civicrm_membership.membership_type_id = civicrm_membership_type.id ";
            }
            break;

        case 'civicrm_membership_status':
            if ( $mode & CRM_Contact_BAO_Query::MODE_MEMBER ) {
                $from = " INNER JOIN civicrm_membership_status ON civicrm_membership.status_id = civicrm_membership_status.id ";
            } else {
                $from = " $side JOIN civicrm_membership_status ON civicrm_membership.status_id = civicrm_membership_status.id ";
            }
            break;
            
        case 'civicrm_membership_payment':
            $from = " $side JOIN civicrm_membership_payment ON civicrm_membership_payment.membership_id = civicrm_membership.id ";
            break;
        }
        return $from;
    }
    
    static function defaultReturnProperties( $mode ) 
    {
        $properties = null;
        if ( $mode & CRM_Contact_BAO_Query::MODE_MEMBER ) {
            $properties = array(  
                                'contact_type'           => 1, 
                                'contact_sub_type'       => 1, 
                                'sort_name'              => 1, 
                                'display_name'           => 1,
                                'membership_type'        => 1,
                                'member_is_test'         => 1, 
                                'member_is_pay_later'    => 1, 
                                'join_date'              => 1,
                                'membership_start_date'  => 1,
                                'membership_end_date'    => 1,
                                'membership_source'      => 1,
                                'membership_status'      => 1,
                                'membership_id'          => 1,
                                'owner_membership_id'    => 1,
                                'membership_recur_id'    => 1
                                );

            // also get all the custom membership properties
            require_once "CRM/Core/BAO/CustomField.php";
            $fields = CRM_Core_BAO_CustomField::getFieldsForImport('Membership');
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
        require_once 'CRM/Member/PseudoConstant.php';
        foreach (CRM_Member_PseudoConstant::membershipType( ) as $id => $Name) {
            $form->_membershipType =& $form->addElement('checkbox', "member_membership_type_id[$id]", null,$Name);
        }

        // Option to include / exclude inherited memberships from search results (e.g. rows where owner_membership_id is NOT NULL)
        $primaryValues = array( 1 => ts('All Members'), 2 => ts('Primary Members Only'), 3 => ts('Related Members Only') );
        $form->addRadio( 'member_is_primary', '', $primaryValues );
        $form->setDefaults( array( 'member_is_primary' => 1 ) );
        
        foreach (CRM_Member_PseudoConstant::membershipStatus(null, null, 'label') as $sId => $sName) {
            $form->_membershipStatus =& $form->addElement('checkbox', "member_status_id[$sId]", null,$sName);
        }

        $form->addElement( 'text', 'member_source', ts( 'Source' ) );
 
        $form->addDate( 'member_join_date_low', ts('Member Since - From'), false, array( 'formatType' => 'searchDate') );
        $form->addDate( 'member_join_date_high', ts('To'), false, array( 'formatType' => 'searchDate') );

        $form->addDate( 'member_start_date_low', ts('Start Date - From'), false, array( 'formatType' => 'searchDate') );
        $form->addDate( 'member_start_date_high', ts('To'), false, array( 'formatType' => 'searchDate') );

        $form->addDate( 'member_end_date_low', ts('End Date - From'), false, array( 'formatType' => 'searchDate') );
        $form->addDate( 'member_end_date_high', ts('To'), false, array( 'formatType' => 'searchDate') );

        $form->addElement( 'checkbox', 'member_test' , ts( 'Find Test Memberships?' ) );
        $form->addElement( 'checkbox', 'member_pay_later', ts( 'Find Pay Later Memberships?' ) );
        $form->addElement( 'checkbox', 'member_auto_renew', ts( 'Find Auto-renew Memberships?' ) );

        // add all the custom  searchable fields
        require_once 'CRM/Custom/Form/CustomData.php';
        $extends      = array( 'Membership' );
        $groupDetails = CRM_Core_BAO_CustomGroup::getGroupDetail( null, true, $extends );
        if ( $groupDetails ) {
            require_once 'CRM/Core/BAO/CustomField.php';
            $form->assign('membershipGroupTree', $groupDetails);
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
        $form->assign( 'validCiviMember', true );
    }

    static function searchAction( &$row, $id ) 
    {
    }

    static function addShowHide( &$showHide ) 
    {
        $showHide->addHide( 'memberForm' );
        $showHide->addShow( 'memberForm_show' );
    }

    static function tableNames( &$tables ) 
    {
        //add membership table
        if ( CRM_Utils_Array::value( 'civicrm_membership_log', $tables ) || CRM_Utils_Array::value( 'civicrm_membership_status', $tables ) || CRM_Utils_Array::value( 'civicrm_membership_type', $tables ) ) {
            $tables = array_merge( array( 'civicrm_membership' => 1), $tables );
        }

    }


}


