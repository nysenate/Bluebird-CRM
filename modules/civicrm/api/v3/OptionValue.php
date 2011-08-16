<?php
require_once 'CRM/Core/BAO/OptionValue.php';
function civicrm_api3_option_value_get( $params ) {

     civicrm_api3_verify_mandatory($params);
     if (empty($params['option_group_id']) && !empty($params['option_group_name'])){
       $opt = array('version' =>3, 'name' => $params['option_group_name']);
       $optionGroup = civicrm_api('OptionGroup', 'Get', $opt);   
       if(empty($optionGroup['id'])){
         return civicrm_api3_create_error("option group name does not correlate to a single option group");    
       }
       $params['option_group_id'] = $optionGroup['id'];
      }    
      
      return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
      
}

/**
 *  Add a OptionValue. OptionValues are used to classify CRM entities (including Contacts, Groups and Actions).
 *
 * Allowed @params array keys are:
 * {@schema Core/OptionValue.xml}
 * {@example OptionValueCreate.php}
 * @return array of newly created option_value property values.
 * @access public
 */
function civicrm_api3_option_value_create( $params ) 
{

    civicrm_api3_verify_mandatory ($params);//need to check it's an array before the next part so it meets standards. better solution later
    $weight=0;
    if ( !array_key_exists ('is_active', $params)) {
      $params ['is_active'] = 1;
    }
    if ( !array_key_exists ('label', $params) && array_key_exists ('name', $params)) {
      $params ['label'] = $params ['name']; // no idea why that's a "mandatory" field
    }
    if ( !CRM_Utils_Array::value( 'value', $params ) && array_key_exists ('option_group_id', $params)) {
       require_once 'CRM/Utils/Weight.php';
       $fieldValues = array('option_group_id' =>  $params['option_group_id']);
       // use the next available value
       /* CONVERT(value, DECIMAL) is used to convert varchar
       field 'value' to decimal->integer                    */
      $params['value'] = (int) CRM_Utils_Weight::getDefaultWeight('CRM_Core_DAO_OptionValue',
                                                                 $fieldValues,
                                                           'CONVERT(value, DECIMAL)');
      $weight= $params['value'];
    }
    if ( !array_key_exists ('weight', $params)) {
      $params ['weight'] = $params['value']; // no idea why that's a "mandatory" field
    }
    civicrm_api3_verify_mandatory ($params,'CRM_Core_BAO_OptionValue');
    

    if (CRM_Utils_Array::value('id', $params)){
      $ids             = array( 'optionValue' => $params['id'] );
    }
    $optionValueBAO = CRM_Core_BAO_OptionValue::add( $params, $ids );

    $values = array( );
    _civicrm_api3_object_to_array($optionValueBAO, $values[$optionValueBAO->id]);
    return civicrm_api3_create_success($values,$params);

}


/**
 * Deletes an existing OptionValue
 *
 * @param  array  $params
 * 
 * {@example OptionValueDelete.php 0}
 * @return boolean | error  true if successfull, error otherwise
 * @access public
 */
function civicrm_api3_option_value_delete( $params ) 
{

    civicrm_api3_verify_mandatory ($params,null,array ('id'));
    $id = (int) $params["id"];

    require_once 'CRM/Core/BAO/OptionValue.php';
    return CRM_Core_BAO_OptionValue::del( $id ) ? civicrm_api3_create_success( ) : civicrm_api3_create_error(  'Could not delete OptionValue '. $id  );

}

?>
