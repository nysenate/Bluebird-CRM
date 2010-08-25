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

require_once 'CRM/Core/DAO/Email.php';

/**
 * This class contains functions for email handling
 */
class CRM_Core_BAO_Email extends CRM_Core_DAO_Email 
{

    /**
     * takes an associative array and adds email
     *
     * @param array  $params         (reference ) an assoc array of name/value pairs
     *
     * @return object       CRM_Core_BAO_Email object on success, null otherwise
     * @access public
     * @static
     */
    static function add( &$params ) 
    {
        $email = new CRM_Core_DAO_Email( );
        $email->copyValues($params);

        // lower case email field to optimize queries
        $strtolower = function_exists('mb_strtolower') ? 'mb_strtolower' : 'strtolower';
        $email->email = $strtolower( $email->email );

        // since we're setting bulkmail for 1 of this contact's emails, first reset all their emails to is_bulkmail false
        // (only 1 email address can have is_bulkmail = true)
        if ( $email->is_bulkmail != 'null' && $params['contact_id']) {
            $sql = "
UPDATE civicrm_email 
SET is_bulkmail = 0
WHERE 
contact_id = {$params['contact_id']}";
            CRM_Core_DAO::executeQuery( $sql );
        }

        // handle if email is on hold
        self::holdEmail( $email );

        return $email->save( );
    }

    /**
     * Given the list of params in the params array, fetch the object
     * and store the values in the values array
     *
     * @param array $entityBlock   input parameters to find object
     *
     * @return boolean
     * @access public
     * @static
     */
    static function &getValues( $entityBlock ) 
    {
        return CRM_Core_BAO_Block::getValues( 'email', $entityBlock );
    }

   
    /**
     * Get all the emails for a specified contact_id, with the primary email being first
     *
     * @param int $id the contact id
     *
     * @return array  the array of email id's
     * @access public
     * @static
     */
    static function allEmails( $id ) 
    {
        if ( ! $id ) {
            return null;
        }

        $query = "
SELECT email, civicrm_location_type.name as locationType, civicrm_email.is_primary as is_primary, civicrm_email.on_hold as on_hold,
civicrm_email.id as email_id, civicrm_email.location_type_id as locationTypeId
FROM      civicrm_contact
LEFT JOIN civicrm_email ON ( civicrm_email.contact_id = civicrm_contact.id )
LEFT JOIN civicrm_location_type ON ( civicrm_email.location_type_id = civicrm_location_type.id )
WHERE
  civicrm_contact.id = %1
ORDER BY
  civicrm_email.is_primary DESC, civicrm_email.location_type_id DESC, email_id ASC ";
        $params = array( 1 => array( $id, 'Integer' ) );

        $emails = array( );
        $dao =& CRM_Core_DAO::executeQuery( $query, $params );
        while ( $dao->fetch( ) ) {
            $emails[$dao->email_id] = array( 'locationType'   => $dao->locationType,
                                             'is_primary'     => $dao->is_primary,
                                             'on_hold'        => $dao->on_hold,
                                             'id'             => $dao->email_id,
                                             'email'          => $dao->email,
                                             'locationTypeId' => $dao->locationTypeId );
        }
        return $emails;
    }
    

     /**
     * Get all the emails for a specified location_block id, with the primary email being first
     *
     * @param array $entityElements the array containing entity_id and
     * entity_table name
     *
     * @return array  the array of email id's
     * @access public
     * @static
     */
    static function allEntityEmails( &$entityElements ) 
    {
        if ( empty($entityElements) ) {
            return null;
        }

        $entityId    = $entityElements['entity_id'];
        $entityTable = $entityElements['entity_table'];


        $sql = " SELECT email, ltype.name as locationType, e.is_primary as is_primary, e.on_hold as on_hold,e.id as email_id, e.location_type_id as locationTypeId 
FROM civicrm_loc_block loc, civicrm_email e, civicrm_location_type ltype, {$entityTable} ev
WHERE ev.id = %1
AND   loc.id = ev.loc_block_id
AND   e.id IN (loc.email_id, loc.email_2_id)
AND   ltype.id = e.location_type_id
ORDER BY e.is_primary DESC, email_id ASC ";
       
        $params = array( 1 => array( $entityId, 'Integer' ) );
            
        $emails = array( );
        $dao =& CRM_Core_DAO::executeQuery( $sql, $params );
        while ( $dao->fetch( ) ) {
            $emails[$dao->email_id] = array( 'locationType'   => $dao->locationType,
                                             'is_primary'     => $dao->is_primary,
                                             'on_hold'        => $dao->on_hold,
                                             'id'             => $dao->email_id,
                                             'email'          => $dao->email,
                                             'locationTypeId' => $dao->locationTypeId );
        }
        
        return $emails;
    }

    /**
     * Function to set / reset hold status for an email
     *
     * @param object $email  email object
     *
     * @return void
     * @static
     */
    static function holdEmail ( &$email ) 
    {
        //check for update mode
        if ( $email->id ) {
            //get hold date
            $holdDate = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_Email', $email->id, 'hold_date' );

            //get reset date
            $resetDate = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_Email', $email->id, 'reset_date' );

            //set hold date only if it is not set and e
            if ( ($email->on_hold != 'null') && !$holdDate && $email->on_hold) {
                $email->hold_date  = date( 'YmdHis' );
                $email->reset_date = '';
            } else if ( $holdDate && ( $email->on_hold == 'null' ) && !$resetDate ) {
                //set reset date only if it is not set and if hold date is set
                $email->on_hold     = false;
                $email->hold_date   = '';
                $email->reset_date  = date( 'YmdHis' );
            }
        } else {
            if ( ($email->on_hold != 'null') && $email->on_hold ) {
                $email->hold_date   = date( 'YmdHis' );
            }
        }
    }
}

