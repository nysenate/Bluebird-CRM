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
class CRM_HRDCase_Page_AJAX
{

    /**
     * Function for Case Subject combo box
     */
    function caseSubject( &$config ) 
    {
        require_once 'CRM/Utils/Type.php';
        $whereclause = $caseIdClause = null; 
        if ( isset( $_GET['name'] ) ) {
            $name        = CRM_Utils_Type::escape( $_GET['name'], 'String'  ) ;
            $name        = str_replace( '*', '%', $name );
            $whereclause = "civicrm_case.subject LIKE '%$name'";
        }
        
        if ( isset( $_GET['id'] ) ) {
            $caseId = CRM_Utils_Type::escape( $_GET['id'], 'Integer' );
            $caseIdClause = " AND civicrm_case.id = {$caseId}";
        }
        
        $elements = array( );
        if ( $name || $caseIdClause ) {
            if ( is_numeric( $_GET['c'] ) ) {
                $contactID = CRM_Utils_Type::escape( $_GET['c'], 'Integer' );
                if ( $contactID ) {
                    $clause = "civicrm_case_contact.contact_id = $contactID";
                    $whereclause = $whereclause ? ($whereclause . " AND " . $clause) : $clause;
                }
            }
            $query = "
SELECT distinct(civicrm_case.subject) as subject, civicrm_case.id as id
FROM civicrm_case
LEFT JOIN civicrm_case_contact ON civicrm_case_contact.case_id = civicrm_case.id
WHERE {$whereclause} {$caseIdClause}
ORDER BY subject";

            $dao = CRM_Core_DAO::executeQuery( $query );
            
            while ( $dao->fetch( ) ) {
                $elements[] = array( 'name' => $dao->subject,
                                     'id'   => $dao->id
                                     );
            }
        }

        if ( empty( $elements ) ) {
            $name = str_replace( '%', '', $name );
            $elements[] = array( 'name' => $name,
                                 'id'=> $name);
        }


        require_once "CRM/Utils/JSON.php";
        echo CRM_Utils_JSON::encode( $elements );
    }

}
