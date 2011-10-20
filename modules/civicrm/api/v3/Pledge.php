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
 * @subpackage API_Pledge
 *
 * @copyright CiviCRM LLC (c) 2004-2011
 * @version $Id: Pledge.php
 *
 */

/**
 * Include utility functions
 */
require_once 'api/v3/utils.php';
require_once 'CRM/Utils/Rule.php';

/**
 * Add or update a plege
 *
 * @param  array   $params           (reference ) input parameters. Fields from interogate function should all work
 *
 * @return array (reference )        array representing created pledge
 * @static void
 * @access public
 */
function civicrm_api3_pledge_create( $params ) {


    if ($params['pledge_amount']){
      //acceptable in unique format or DB format but change to unique format here
      $params['amount'] = $params['pledge_amount'];
    }
     $required =  array('contact_id', 'amount', array('pledge_contribution_type_id','contribution_type_id') , 'installments','start_date');
    
   civicrm_api3_verify_mandatory ($params,null,$required);
     
    $values  = array( );
    require_once 'CRM/Pledge/BAO/Pledge.php';
    //check that fields are in appropriate format. Dates will be formatted (within reason) by this function
    $error = _civicrm_api3_pledge_format_params( $params, $values,TRUE ); 
    if ( civicrm_api3_error( $error ) ) {
        return $error;
    } 
    //format the custom fields
    _civicrm_api3_custom_format_params( $params, $values, 'Pledge' );
   return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $values);
    


}

/**
 * Delete a pledge
 *
 * @param  array   $params           array included 'pledge_id' of pledge to delete
 *
 * @return boolean        true if success, else false
 * @static void
 * @access public
 */
function civicrm_api3_pledge_delete( $params ) {


    civicrm_api3_verify_one_mandatory ($params,null,array('id', 'pledge_id'));
    if (!empty($params['id'])){
      //handle field name or unique db name
      $params['pledge_id'] = $params['id'];
    }

    $pledgeID = CRM_Utils_Array::value( 'pledge_id', $params );
    if ( ! $pledgeID ) {
      return civicrm_api3_create_error(  'Could not find pledge_id in input parameters' );
    }

    require_once 'CRM/Pledge/BAO/Pledge.php';
    if ( CRM_Pledge_BAO_Pledge::deletePledge( $pledgeID ) ) {
      return civicrm_api3_create_success(array($pledgeID =>$pledgeID),$params,'pledge','delete' );
    } else {
      return civicrm_api3_create_error(  'Could not delete pledge'  );
    }

}

/**
 * Retrieve a set of pledges, given a set of input params
 *
 * @param  array   $params           (reference ) input parameters. Use interogate for possible fields
 *
 * @return array (reference )        array of pledges, if error an array with an error id and error message
 * @static void
 * @access public
 */
function civicrm_api3_pledge_get( $params ) {
    civicrm_api3_verify_mandatory ($params);
    if(!empty($params['id'])  && empty($params['pledge_id'])){
      //if you pass in 'id' it will be treated by the query as contact_id
      $params['pledge_id'] = $params['id'];
      unset ($params['id']);
    }
    $options = _civicrm_api3_get_options_from_params($params);

    require_once 'CRM/Pledge/BAO/Query.php';
    require_once 'CRM/Contact/BAO/Query.php';
    if ( empty($options['return'] ) ) {
      $options['return'] = CRM_Pledge_BAO_Query::defaultReturnProperties( CRM_Contact_BAO_Query::MODE_PLEDGE );
    }else{
      $options['return']['pledge_id']=1;
    }
    $newParams = CRM_Contact_BAO_Query::convertFormValues( $options['input_params'] );

    $query = new CRM_Contact_BAO_Query( $newParams, $options['return'], null,
                                        false, false, CRM_Contact_BAO_Query::MODE_PLEDGE );
    list( $select, $from, $where ) = $query->query( );
    $sql 
    = "$select $from $where";

    if ( ! empty( $options['sort'] ) ) {
      $sql .= " ORDER BY ". $options['sort'];
    }
    $sql .= " LIMIT " . $options['offset'] . " , " . $options['limit'];
    $dao = CRM_Core_DAO::executeQuery( $sql );
    $pledge = array( );
    while ( $dao->fetch( ) ) {
        $pledge[$dao->pledge_id] = $query->store( $dao );
    }

    return civicrm_api3_create_success($pledge,$params, 'pledge','get',$dao);

}

/*
 * Set default to not return test params
 */
function _civicrm_api3_pledge_get_defaults(){
  return array('pledge_test' => 0);
}
/**
 * take the input parameter list as specified in the data model and
 * convert it into the same format that we use in QF and BAO object
 *
 * @param array  $params       Associative array of property name/value
 *                             pairs to insert in new contact.
 * @param array  $values       The reformatted properties that we can use internally
 *                            '
 * @return array|CRM_Error
 * @access public
 */
function _civicrm_api3_pledge_format_params( $params, &$values, $create=false ) {
  // based on contribution apis - copy all the pledge fields - this function filters out non -valid fields but unfortunately
  // means we have to put them back where there are 2 names for the field (name in table & unique name)
  // since there is no clear std to use one or the other. Generally either works ? but not for create date
  // perhaps we should just copy $params across rather than run it through the 'filter'?
  // but at least the filter forces anomalies into the open. In several cases it turned out the unique names wouldn't work
  // even though they are 'generally' what is returned in the GET - implying they should
  $fields =& CRM_Pledge_DAO_Pledge::fields( );
  _civicrm_api3_store_values( $fields, $params, $values );
  $values['sequential'] =  CRM_Utils_Array::value('sequential', $params,0);


  //add back the fields we know of that got dropped by the previous function
  if (!empty($params['pledge_create_date'])){
    //pledge_create_date will not be formatted by the format params function so change back to create_date
    $values['create_date'] = $params['pledge_create_date'];
  }else{

    //create_date may have been dropped by the $fields function so retrieve it
    $values['create_date'] = CRM_Utils_Array::value('create_date',$params);
  }

    //field has been renamed - don't lose it! Note that this must be called
    // installment amount not pledge_installment_amount, pledge_original_installment_amount
    // or original_installment_amount to avoid error
    // Division by zero in CRM\Pledge\BAO\Payment.php:162
    // but we should accept the variant because they are all 'logical assumptions' based on the
    // 'standards'
    $values['installment_amount'] = CRM_Utils_Array::value('installment_amount',$params);

  
  if ( array_key_exists( 'original_installment_amount', $params ) ) {
    $values['installment_amount'] = $params['original_installment_amount'];
    //it seems it will only create correctly with BOTH installment amount AND pledge_installment_amount set
    //pledge installment amount required for pledge payments
    $values['pledge_original_installment_amount'] = $params['original_installment_amount'];
  }

  if ( array_key_exists( 'pledge_original_installment_amount', $params ) ) {
    $values['installment_amount'] = $params['pledge_original_installment_amount'];
  }
 
  if ( array_key_exists( 'status_id', $params ) ){
    $values['pledge_status_id'] = $params['status_id'];
  }
  if ( array_key_exists('contact_id',$params)){
    //this is validity checked further down to make sure the contact exists
    $values['pledge_contact_id'] = $params['contact_id'];
  }
  if ( array_key_exists( 'id', $params )  ){
    //retrieve the id key dropped from params. Note we can't use pledge_id because it
    //causes an error in CRM_Pledge_BAO_Payment - approx line 302
    $values['id'] = $params['id'];
  }
  if ( array_key_exists( 'pledge_id', $params )  ){
    //retrieve the id key dropped from params. Note we can't use pledge_id because it
    //causes an error in CRM_Pledge_BAO_Payment - approx line 302
    $values['id'] = $params['pledge_id'];
    unset($values['pledge_id']);
  }
  if ( array_key_exists( 'status_id', $params ) ){
    $values['pledge_status_id'] = $params['status_id'];
  }
  if ( empty( $values['status_id'] ) && !empty($values['pledge_status_id']) ){
    $values['status_id'] = $values['pledge_status_id'];
  }
  if (empty($values['id'])){
    //at this point both should be the same so unset both if not set - passing in empty
    //value causes crash rather creating new - do it before next section as null values ignored in 'switch'
    unset($values['id']);
    
    //if you have a single installment when creating & you don't set the pledge status (not a required field) then 
    //status id is left null for pledge payments in BAO
    // so we are hacking in the addition of the pledge_status_id to pending here
    if(empty($values['status_id']) && $params['installments'] ==1){
      require_once 'CRM/Contribute/PseudoConstant.php';
      $contributionStatus = CRM_Contribute_PseudoConstant::contributionStatus( null, 'name' );
      $values['status_id'] = array_search( 'Pending', $contributionStatus);
    }
  }
  if ( !empty( $params['scheduled_date']) ){
    //scheduled date is required to set next payment date - defaults to start date
    $values['scheduled_date'] = $params['scheduled_date'];
  }elseif (array_key_exists( 'start_date', $params )){
    $values['scheduled_date'] = $params['start_date'];
  }

  foreach ($values as $key => $value) {
    // ignore empty values or empty arrays etc
    if ( CRM_Utils_System::isNull( $value ) ) {
      continue;
    }
    switch ($key) {

      case 'pledge_contact_id':
        if (!CRM_Utils_Rule::integer($value)) {
          return civicrm_api3_create_error("contact_id not valid: $value");
        }
        $dao = new CRM_Core_DAO();
        $qParams = array();
        $svq = $dao->singleValueQuery("SELECT id FROM civicrm_contact WHERE id = $value",
        $qParams);
        if (!$svq) {
          return civicrm_api3_create_error("Invalid Contact ID: There is no contact record with contact_id = $value.");
        }

        $values['contact_id'] = $values['pledge_contact_id'];
        unset ($values['pledge_contact_id']);
        break;
      case 'pledge_id':
        if (!CRM_Utils_Rule::integer($value)) {
          return civicrm_api3_create_error("contact_id not valid: $value");
        }
        $dao = new CRM_Core_DAO();
        $qParams = array();
        $svq = $dao->singleValueQuery("SELECT id FROM civicrm_pledge WHERE id = $value",
        $qParams);
        if (!$svq) {
          return civicrm_api3_create_error("Invalid Contact ID: There is no contact record with contact_id = $value.");
        }

        break;


      case 'installment_amount':
      case 'amount':
        if (!CRM_Utils_Rule::money($value)) {
          return civicrm_api3_create_error("$key not a valid amount: $value");
        }
        break;
      case 'currency':
        if (!CRM_Utils_Rule::currencyCode($value)) {
          return civicrm_api3_create_error("currency not a valid code: $value");
        }
        break;
      default:
        break;
    }
  }

   return array();
}


