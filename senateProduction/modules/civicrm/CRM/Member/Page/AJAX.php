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

/**
 * This class contains all the function that are called using AJAX (dojo)
 */
class CRM_Member_Page_AJAX
{
    /**
     * Function to setDefaults according to membership type
     */
    function getMemberTypeDefaults( $config ) 
    {
        require_once 'CRM/Utils/Type.php';
        $memType  = CRM_Utils_Type::escape( $_POST['mtype'], 'Integer') ; 
        
        $contributionType = CRM_Core_DAO::getFieldValue( 'CRM_Member_DAO_MembershipType', 
                                                         $memType, 
                                                         'contribution_type_id' );
        
        $totalAmount = CRM_Core_DAO::getFieldValue( 'CRM_Member_DAO_MembershipType', 
                                                    $memType, 
                                                    'minimum_fee' );

        // fix the display of the monetary value, CRM-4038
        require_once 'CRM/Utils/Money.php';
        $totalAmount = CRM_Utils_Money::format( $totalAmount, null, '%a' );
        
        $details = array( 'contribution_type_id' => $contributionType,
                          'total_amount'         => $totalAmount );                                         
        
        echo json_encode( $details );
        CRM_Utils_System::civiExit( );
    }
    
}