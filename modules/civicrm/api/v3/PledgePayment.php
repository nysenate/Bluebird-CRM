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
 * File for the CiviCRM APIv3 Pledge functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Pledge_Payment
 *
 * @copyright CiviCRM LLC (c) 2004-2011
 * @version $Id: PledgePayment.php
 *
 */

/**
 * Include utility functions
 */
require_once 'api/v3/utils.php';
require_once 'CRM/Pledge/BAO/Payment.php';

/**
 * Add or update a plege payment. Pledge Payment API doesn't actually add a pledge 
 *  if the request is to 'create' and 'id' is not passed in
 * the oldest pledge with no associated contribution is updated
 *
 * @todo possibly add ability to add payment if there are less payments than pledge installments
 * @todo possibily add ability to recalc dates if the schedule is changed
 * 
 * @param  array   $params           (reference ) input parameters
 *
 * @return array (reference )        pledge_id of created or updated record
 * @static void
 * @access public
 */
function civicrm_api3_pledge_payment_create( $params ) {

    civicrm_api3_verify_mandatory($params,null,array('pledge_id','status_id'));

    $paymentParams =$params;
    if (empty($params['id']) && !CRM_Utils_Array::value('option.create_new',$params)){
      $paymentDetails = CRM_Pledge_BAO_Payment::getOldestPledgePayment($params['pledge_id']);
      if(empty($paymentDetails) ){
        return civicrm_api3_create_error("There are no unmatched payment on this pledge. Pass in the pledge_payment id to specify one or 'option.create_new' to create one");
      }elseif(is_array($paymentDetails)){
        $paymentParams = array_merge($params,$paymentDetails);
      }
    }

    $dao = CRM_Pledge_BAO_Payment::add( $paymentParams );
     _civicrm_api3_object_to_array($dao, $result[$dao->id]);
    
   
    //update pledge status
     CRM_Pledge_BAO_Payment::updatePledgePaymentStatus( $params['pledge_id']);
    
    return civicrm_api3_create_success( $result ,$params,'pledge_payment','create',$dao);
   
}

/**
 * Delete a pledge Payment - Note this deletes the contribution not just the link
 *
 * @param  array   $params           (reference ) input parameters
 *
 * @return boolean        true if success, else false
 * @static void
 * @access public
 */
function civicrm_api3_pledge_payment_delete( $params ) {

    civicrm_api3_verify_mandatory($params,null,array('id'));
    $id = CRM_Utils_Array::value( 'id', $params );
    require_once 'CRM/Pledge/BAO/Pledge.php';
    if ( CRM_Pledge_BAO_Payment::del( $id ) ) {
      return civicrm_api3_create_success( array('id' => $id),$params);
    } else {
      return civicrm_api3_create_error(  'Could not delete payment'  );
    }


}

/**
 * Retrieve a set of pledges, given a set of input params
 *
 * @param  array   $params           (reference ) input parameters
 * @param array    $returnProperties Which properties should be included in the
 *                                   returned pledge object. If NULL, the default
 *                                   set of properties will be included.
 *
 * @return array (reference )        array of pledges, if error an array with an error id and error message
 * @static void
 * @access public
 */
function civicrm_api3_pledge_payment_get( $params ) {

    civicrm_api3_verify_mandatory($params);
    return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);  

}


function updatePledgePayments( $pledgeId, $paymentStatusId, $paymentIds  ){
  _civicrm_api3_initialize(true );
  require_once 'CRM/Pledge/BAO/Pledge.php';
  $result = updatePledgePayments( $pledgeId, $paymentStatusId, $paymentIds = null );
  return $result;

}

/* 
 * Gets field for civicrm_pledge_payment functions
 * 
 * @return array fields valid for other functions
 */

function civicrm_api3_pledge_payment_getfields($action = 'get'){
    $fields = _civicrm_api_get_fields('payment');
    $fields['option.create_new'] = array('title' => "Create new field rather than update an unpaid payment");
    return civicrm_api3_create_success($fields);
}
