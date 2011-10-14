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
 * File for the CiviCRM APIv3 membership contribution link functions
 *
 * @todo Probably needs renaming
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Membership
 *
 * @copyright CiviCRM LLC (c) 2004-2011
 * @version $Id: MembershipContributionLink.php 30171 2010-10-14 09:11:27Z mover $
 */

/**
 * Include utility functions
 */
require_once 'api/v3/utils.php';
require_once 'CRM/Member/DAO/MembershipPayment.php';
/**
 * Add or update a link between contribution and membership
 *
 * @param  array   $params           (reference ) input parameters
 *
 * @return array (reference )        membership_payment_id of created or updated record
 * @static void
 * @access public
 */
function civicrm_api3_membership_payment_create( $params ) {

    civicrm_api3_verify_mandatory($params,'CRM_Member_DAO_MembershipPayment',array('contribution_id','membership_id'));

    require_once 'CRM/Core/Transaction.php';
    $transaction = new CRM_Core_Transaction( );


    $mpDAO = new CRM_Member_DAO_MembershipPayment();
    $mpDAO->copyValues($params);
    $result = $mpDAO->save();

    if ( is_a( $result, 'CRM_Core_Error') ) {
      $transaction->rollback( );
      return civicrm_api3_create_error( $result->_errors[0]['message'] );
    }

    $transaction->commit( );

    _civicrm_api3_object_to_array($mpDAO, $mpArray[$mpDAO->id]);

    return civicrm_api3_create_success($mpArray,$params);

}

/**
 * Retrieve one / all contribution(s) / membership(s) linked to a
 * membership / contrbution.
 *
 * @param  array   $params           (reference ) input parameters
 * @todo missing delete function
 *
 * @return array (reference )        array of properties, if error an array with an error id and error message
 * @static void
 * @access public
 */
function &civicrm_api3_membership_payment_get( $params ) {

    civicrm_api3_verify_mandatory($params);
    return _civicrm_api3_basic_get('CRM_Member_DAO_MembershipPayment', $params);
}
