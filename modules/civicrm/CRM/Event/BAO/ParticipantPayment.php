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
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

require_once 'CRM/Event/DAO/ParticipantPayment.php';

class CRM_Event_BAO_ParticipantPayment extends CRM_Event_DAO_ParticipantPayment
{
  
    static function &create(&$params, &$ids) 
    { 
        $paymentParticipant = new CRM_Event_BAO_ParticipantPayment(); 
        $paymentParticipant->copyValues($params);
        if ( isset( $ids['id'] ) ) {
            $paymentParticipant->id = CRM_Utils_Array::value( 'id', $ids );
        } else {
            $paymentParticipant->find( true );
        }
        $paymentParticipant->save();

        return $paymentParticipant;
    }

    
    /**                          
     * Delete the record that are associated with this Participation Payment
     * 
     * @param  array  $params   array in the format of $field => $value. 
     * 
     * @return boolean  true if deleted false otherwise
     * @access public 
     */ 
    static function deleteParticipantPayment( $params ) 
    {
        require_once 'CRM/Event/DAO/ParticipantPayment.php';
        $participantPayment = new CRM_Event_DAO_ParticipantPayment( );

        $valid = false;
        foreach ( $params as $field => $value ) {
            if ( ! empty( $value ) ) {
                $participantPayment->$field  = $value;
                $valid = true;
            }
        }

        if ( ! $valid ) {
            CRM_Core_Error::fatal( );
        }

        if ( $participantPayment->find( true ) ) {
            require_once 'CRM/Contribute/BAO/Contribution.php';
            CRM_Contribute_BAO_Contribution::deleteContribution( $participantPayment->contribution_id );
            $participantPayment->delete( ); 
            return $participantPayment;
        }
        return false;
    }
}

