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
 * File for the CiviCRM APIv3 participant functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Participant
 *
 * @copyright CiviCRM LLC (c) 2004-2011
 * @version $Id: Participant.php 30486 2010-11-02 16:12:09Z shot $
 *
 */

/**
 * Files required for this package
 */
require_once 'api/v3/utils.php';


/**
 * Create a Event Participant Payment
 *
 * This API is used for creating a Participant Payment of Event.
 * Required parameters : participant_id, contribution_id.
 *
 * @param   array  $params     an associative array of name/value property values of civicrm_participant_payment
 * @example ParticipantPaymentCreate.php
 * {@example ParticipantPaymentCreate.php 0}
 * @return array of newly created payment property values.
 * @access public
 */
function civicrm_api3_participant_payment_create($params)
{

    civicrm_api3_verify_mandatory($params,null,array('participant_id','contribution_id')) ;

    $ids= array();
    if( CRM_Utils_Array::value( 'id', $params ) ) {
      $ids['id'] = $params['id'];
    }
    require_once 'CRM/Event/BAO/ParticipantPayment.php';
    $participantPayment = CRM_Event_BAO_ParticipantPayment::create($params, $ids);

    $payment = array( );
    _civicrm_api3_object_to_array($participantPayment, $payment[$participantPayment->id]);

    return civicrm_api3_create_success($payment,$params);

}

/**
 * Deletes an existing Participant Payment
 *
 * This API is used for deleting a Participant Payment
 *
 * @param  Int  $participantPaymentID   Id of the Participant Payment to be deleted
 *
 * @return null if successfull, array with is_error=1 otherwise
 * @access public
 */
function civicrm_api3_participant_payment_delete( $params )
{

    civicrm_api3_verify_mandatory($params,null,array('id'));
    require_once 'CRM/Event/BAO/ParticipantPayment.php';
    $participant = new CRM_Event_BAO_ParticipantPayment();

    return $participant->deleteParticipantPayment( $params ) ? civicrm_api3_create_success( ) : civicrm_api3_create_error('Error while deleting participantPayment');

}

