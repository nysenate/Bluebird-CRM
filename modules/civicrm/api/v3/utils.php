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
 * File for CiviCRM APIv3 utilitity functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_utils
 * 
 * @copyright CiviCRM LLC (c) 2004-2011
 * @version $Id: utils.php 30879 2010-11-22 15:45:55Z shot $
 *
 */

/**
 * Initialize CiviCRM - should be run at the start of each API function
 *
 * $useException boolean raise exception if set
 */
function _civicrm_api3_initialize($useException = true ) 
{
    require_once 'CRM/Core/Config.php';
    $config = CRM_Core_Config::singleton( );
    if ($useException) {
        CRM_Core_Error::setRaiseException();
    }
}

/*
 * Wrapper Function for civicrm_verify_mandatory to make it simple to pass either / or fields for checking
 * 
 * @param array $params array of fields to check
 * @param array $daoName string DAO to check for required fields (create functions only)
 * @param array $keys list of required fields options. One of the options is required
 * @return null or throws error if there the required fields not present
 
 * @
 *
 */
function civicrm_api3_verify_one_mandatory ($params, $daoName = null, $keyoptions = array() ) {
    foreach ($keyoptions as $key){
        $keys[0][] = $key;
    }
    civicrm_api3_verify_mandatory ($params, $daoName, $keys  );
}

/*
 * Load the DAO of the entity
 */
function _civicrm_api3_load_DAO($entity){
    $dao = _civicrm_api3_get_DAO ($entity);
    if (empty($dao)) {
        return false;
    }
    $file = str_replace ('_','/',$dao).".php";
    require_once ($file); 
    $d = new $dao();
    return $d;
}
/*
 * Function to return the DAO of the function or Entity
 * @param  $name is either a function of the api (civicrm_{entity}_create or the entity name 
 * return the DAO name to manipulate this function
 * eg. "civicrm_api3_contact_create" or "Contact" will return "CRM_Contact_BAO_Contact"
 */

function _civicrm_api3_get_DAO ($name) {
    static $dao = null; 
    if (!$dao) {
        require ('CRM/Core/DAO/.listAll.php');
    }
   
    if (strpos($name, 'civicrm_api3') !== false) {
        $last = strrpos ($name, '_') ;
        $name = substr ($name, 13, $last -13);// len ('civicrm_api3_') == 13
        if($name =='pledge_payment'){
            //for some reason pledge_payment doesn't follow normal conventions of BAO being the same as table name
            $name = 'Payment';
        }
    }  
    if(strtolower($name) =='individual' || strtolower($name) =='household' ||strtolower($name) =='organization'){
        $name = 'Contact';
    }
    return $dao[civicrm_api_get_camel_name($name,3)];
}

/*
 * Function to return the DAO of the function or Entity
 * @param  $name is either a function of the api (civicrm_{entity}_create or the entity name 
 * return the DAO name to manipulate this function
 * eg. "civicrm_contact_create" or "Contact" will return "CRM_Contact_BAO_Contact"
 */

function _civicrm_api3_get_BAO ($name) {
    $dao = _civicrm_api3_get_DAO($name);
    $dao = str_replace("DAO","BAO", $dao);
    return $dao;
}



/*
 * Function to check mandatory fields are included
 * 
 * @param array $params array of fields to check
 * @param array $daoName string DAO to check for required fields (create functions only)
 * @param array $keys list of required fields. A value can be an array denoting that either this or that is required.
 * @param bool $verifyDAO
 * @return null or throws error if there the required fields not present
 */

function civicrm_api3_verify_mandatory ($params, $daoName = null, $keys = array(), $verifyDAO = TRUE ) {
  // moving this to civicrm_api - remove the check for array pending testing
   if ( ! is_array( $params ) ) {
        throw new Exception ('Input variable `params` is not an array');
    }

    if ($daoName != null && $verifyDAO && !CRM_Utils_Array::value('id',$params)) {
        if(!is_array($unmatched =_civicrm_api3_check_required_fields( $params, $daoName, true))){
            $unmatched = array();
        }
    }
    require_once 'CRM/Utils/Array.php';
    if(CRM_Utils_Array::value('id',$params)){
        $keys = array('version');
    }else{
        $keys[] = 'version';//required from v3 onwards
    } 
    foreach ($keys as $key) {
        if(is_array($key)){
            $match = 0;
            $optionset = array();
            foreach($key as $subkey){
                if ( !array_key_exists ($subkey, $params)|| empty($params[$subkey])) {
                    $optionset[] = $subkey;
                }else{
                    $match = 1;//as long as there is one match then we don't need to rtn anything
                }
            }
            if (empty($match) &&!empty($optionset)){
                $unmatched[] = "one of (". implode(", ",$optionset) . ")";
            }
        }else{
            if ( !array_key_exists ($key, $params) || empty($params[$key]))
                $unmatched[] = $key;
        }

    }
    if(!empty($unmatched)){
        throw new Exception("Mandatory key(s) missing from params array: " . implode(", ",$unmatched));
    }
}

/*
 * Verify if the params are of the right type
 * @param array $params array of params to check
 * @param string $type ('numeric' only for now)
 * @param string/object dao to test the param againsts (test the standard fields based on the schema definition)
 * @param array $extra contains arrays of (fieldname, type)
 * @return null or throws error if some of the params are not of the right type
 */
function civicrm_api3_verify_type ($params, $dao, $extra) {
    throw new Exception("TODO: To be implemented");
    $notProperType = array();
    if ( !is_array( $keys ) ) {
        $keys = array ($keys) ;
    }
    foreach ($keys as $key) {
        if ( array_key_exists ($key, $params)) {
            switch ($type) {
            case 'numeric': 
                if (!is_numeric($params[$key]))
                    $notProperType[] = $key;
                break;
            default:
                throw new Exception("Type $type not known. Can't verify_type");
            }
        }
    }
    if(!empty($notProperType)){
        throw new Exception("Not of type $type " . implode(", ",$notProperType));
    }
}


/**
 *
 * @param <type> $msg
 * @param <type> $data
 * @param object $dao DAO / BAO object to be freed here
 * @return <type>
 */
function &civicrm_api3_create_error( $msg, $data = null,&$dao = null ) 
{
    if(is_object ($dao)){
        $dao->free(); 
    }
    return CRM_Core_Error::createAPIError( $msg, $data );
}

/**
 * Format array in result output styple
 * @param array $params
 * @dao object DAO object to be freed here
 * @return array $result
 */
function civicrm_api3_create_success( $values = 1,$params=array(), $entity = null,$action = null,&$dao = null )
{
    $result = array();
    $result['is_error'] = 0;
    //lets set the ['id'] field if it's not set & we know what the entity is
    if(is_array($values) && !empty($entity)){
        foreach ($values as $key => $item){
            if(empty($item['id']) &&  !empty($item[$entity . "_id"])){
                $values[$key]['id'] = $item[$entity . "_id"];
            }
        } 
    }
    //if ( array_key_exists ('debug',$params) && is_object ($dao)) {
    if ( is_array($params) && array_key_exists ('debug',$params)) {
        if(!is_object ($dao)){
            $d = _civicrm_api3_get_DAO ($params['entity']);
            if (!empty($d)) {
                $file = str_replace ('_','/',$d).".php";
                require_once ($file);
                $dao = new $d(); 
            }
        }
        if(is_object ($dao)){
            $allFields = array_keys($dao->fields());
            $paramFields = array_keys($params);
            $undefined = array_diff ($paramFields, $allFields,array_keys($_COOKIE),array ('action','entity','debug','version','check_permissions','IDS_request_uri','IDS_user_agent','return','sequential','rowCount','option_offset','option_limit','option_sort'));
            if ($undefined) 
                $result['undefined_fields'] = array_merge ($undefined);
        }
    }
    if(is_object ($dao)){
        $dao->free(); 
    }

    $result['version'] =3;
    if (is_array( $values)) {
        $result['count'] = count( $values);

        // Convert value-separated strings to array
        _civicrm_api3_separate_values( $values );

        if ( $result['count'] == 1 ) {
            list($result['id']) = array_keys($values);
        } elseif ( ! empty($values['id'] ) ) {
            $result['id']= $values['id'];
        }
    } else {
        $result['count'] = ! empty( $values ) ? 1 : 0;
    }

    if ( is_array($values) && isset( $params['sequential'] ) && 
         $params['sequential'] ==1 ) {
        $result['values'] =  array_values($values);
    } else {
        $result['values'] =  $values;
    }

    return $result;
}

/**
 *  Recursive function to explode value-separated strings into arrays
 * 
 */
function _civicrm_api3_separate_values( &$values )
{
    $sp = CRM_Core_DAO::VALUE_SEPARATOR;
    foreach ($values as &$value) {
        if (is_array($value)) {
            _civicrm_api3_separate_values($value);
        }
        elseif (is_string($value)) {
            if (strpos($value, $sp) !== FALSE) {
                $value = explode($sp, trim($value, $sp));
            }
        }
    }
}

/**
 *  function to check if an error is actually a duplicate contact error
 *  
 *  @param array $error (array of) valid Error values
 *  
 *  @return true if error is duplicate contact error, false otherwise 
 *  
 *  @access public 
 */
function civicrm_api3_duplicate($error)
{  
    if ( is_array( $error )  && civicrm_api3_error( $error ) ) {
        $code = $error['error_message']['code'];
        if ($code == CRM_Core_Error::DUPLICATE_CONTACT ) {
            return true ;
        }
    }
    return false;
}

/**
 * Check if the given array is actually an error
 *
 * @param  array   $params           (reference ) input parameters
 *
 * @return boolean true if error, false otherwise
 * @static void
 * @access public
 */
function civicrm_api3_error( $params ) 
{
    if ( is_array( $params ) ) {
        return ( array_key_exists( 'is_error', $params ) &&
                 $params['is_error'] ) ? true : false;
    }
    return false;
}

/**
 *
 * @param array $fields
 * @param array $params
 * @param array $values
 * @return Bool $valueFound
 */
function _civicrm_api3_store_values( &$fields, $params, &$values ) 
{
    $valueFound = false;
    
    $keys = array_intersect_key($params,$fields);
    foreach($keys as $name => $value) {
        if( $name !== 'id' ) {
            $values[$name] = $value;
            $valueFound = true;
        }
    }
    return $valueFound;
}

/*
 * Function transfers the filters being passed into the DAO onto the params object
 */

function _civicrm_api3_dao_set_filter (&$dao,$params, $unique = TRUE ) {
    $entity = substr ($dao->__table , 8);

    $fields = _civicrm_api3_build_fields_array($dao,$unique);
    $fields = array_intersect(array_keys($fields),array_keys($params));
    if( isset($params[$entity. "_id"])){
        //if entity_id is set then treat it as ID (will be overridden by id if set)       
        $dao->id = $params[$entity. "_id"];
         
    }
    //apply options like sort
    _civicrm_api3_apply_options_to_dao($params, $dao );   
    
    //accept filters like filter.activity_date_time_high
    // std is now 'filters' => .. 
    if(strstr(implode(',', array_keys($params)), 'filter')){
        if(is_array($params['filters'])){
            foreach ($params['filters'] as $paramkey =>$paramvalue) {
                _civicrm_api3_apply_filters_to_dao($paramkey,$paramvalue, $dao );
            }
        }else{
            foreach ($params as $paramkey => $paramvalue) {
                if(strstr($paramkey, 'filter') ){
                    _civicrm_api3_apply_filters_to_dao(substr($paramkey,7),$paramvalue, $dao );
                }
            }
        }
    }  
    

    if (!$fields) 
        return;
    foreach ($fields as $field) {
        $dao->$field = $params [$field];
    }
    if(!empty($params['return']) && is_array($params['return'])){
      $dao->selectAdd( ); 
      foreach ($params['return'] as $returnValue ) {
        $dao->selectAdd( $returnValue); 
      }
      $dao->selectAdd( 'id');
    }
}

/*
 * Apply filters (e.g. high, low) to DAO object (prior to find)
 * @param string $filterField field name of filter
 * @param string $filterValue field value of filter
 * @param object $dao DAO object
 */
function   _civicrm_api3_apply_filters_to_dao($filterField,$filterValue, &$dao ){
    if( strstr($filterField, 'high') ){
        $fieldName = substr($filterField ,0,-5);
        $dao->whereAdd( "($fieldName <= $filterValue )" );        
    }
    if(strstr($filterField, 'low')){
        $fieldName = substr($filterField ,0,-4);
        $dao->whereAdd( "($fieldName >= $filterValue )" );        
    }
}
/*
 * @param array $params params array as passed into civicrm_api
 * @return array $options options extracted from params
 */

function _civicrm_api3_get_options_from_params(&$params){
  
  $options = array();
  $inputParams      = array( );
  $returnProperties = array( );
  $otherVars = array( 'sort', 'offset', 'rowCount' );

  $sort     = null;
  $offset   = 0;
  $rowCount = 25;
  foreach ( $params as $n => $v ) {
      if ( substr( $n, 0, 7 ) == 'return.' ) {
        $returnProperties[ substr( $n, 7 ) ] = $v;
      } elseif ( in_array( $n, $otherVars ) ) {
        $$n = $v;
      } else {
        $inputParams[$n] = $v;
      }
    }
  $options['sort'] = $sort;
  $options['limit'] = $rowCount;
  $options['offset'] = $offset;
  $options['return'] = $returnProperties;
  $options['input_params'] = $inputParams;
  return $options;
  
}
/*
 * Apply options (e.g. sort, limit, order by) to DAO object (prior to find)
 * @param array $params params array as passed into civicrm_api
 * @param object $dao DAO object
 */
function   _civicrm_api3_apply_options_to_dao(&$params, &$dao, $defaults = array() ) {
    $sort = CRM_Utils_Array::value('option.sort', $params, 0);
    $sort = CRM_Utils_Array::value('option_sort', $params, $sort);  
    
    $offset = CRM_Utils_Array::value('option.offset', $params, 0);
    $offset = CRM_Utils_Array::value('option_offset', $params,$offset ); // dear PHP thought it would be a good idea to transform a.b into a_b in the get/post

    //XAV->eileen do you want it?     $offset = CRM_Utils_Array::value('offset', $params,  $offset);
    $limit = CRM_Utils_Array::value('option.limit', $params,25);
    $limit = CRM_Utils_Array::value('option_limit', $params,$limit);
    
    if ( isset( $params['options'] )&&
         is_array( $params['options'] ) ){
        $offset = CRM_Utils_Array::value('offset', $params['options'], $offset );
        $limit  = CRM_Utils_Array::value('limit' , $params['options'], $limit  );
        $sort   = CRM_Utils_Array::value('sort'  , $params['options'], $sort   );
    }
    
    $dao->limit( (int)$offset, (int)$limit);
    

    if(!empty($sort)){
        $dao->orderBy( $sort);
    }

}

/*
 * build fields array. This is the array of fields as it relates to the given DAO
 * returns unique fields as keys by default but if set but can return by DB fields
 */
function _civicrm_api3_build_fields_array(&$dao, $unique = TRUE){
    $fields = $dao->fields();
    if ($unique){
        return $fields;
    }
      
    foreach($fields as $field){
        $dbFields[$field['name']] = $field;
    }
    return $dbFields;
}
/**
 * Converts an DAO object to an array 
 *
 * @param  object   $dao           (reference )object to convert
 * @params array of arrays (key = id) of array of fields
 * @static void
 * @access public
 */
function _civicrm_api3_dao_to_array ($dao, $params = null,$uniqueFields = TRUE) {
    $result = array();
    if (empty($dao) || !$dao->find() ) {
        return array();
    }


    $fields = array_keys(_civicrm_api3_build_fields_array($dao, $uniqueFields));

    while ( $dao->fetch() ) {
        $tmp = array();
        foreach( $fields as $key ) {
            if (array_key_exists($key, $dao)) {
                // not sure on that one 
                if ($dao->$key !== null)
                    $tmp[$key] = $dao->$key;
            }
        }
        $result[$dao->id] = $tmp;
    }
    return $result;
}

/**
 * Converts an object to an array 
 *
 * @param  object   $dao           (reference )object to convert
 * @param  array    $dao           (reference )array
 * @param array  $uniqueFields
 * @return array
 * @static void
 * @access public
 */
function _civicrm_api3_object_to_array( &$dao, &$values,$uniqueFields = FALSE )
{

    $fields = _civicrm_api3_build_fields_array($dao,$uniqueFields);
    foreach( $fields as $key => $value ) {
        if (array_key_exists($key, $dao)) {
            $values[$key] = $dao->$key;
        }
    }
}

/*
 * Wrapper for _civicrm_object_to_array when api supports unique fields
 */
function _civicrm_api3_object_to_array_unique_fields( &$dao, &$values ) {
    return _civicrm_api3_object_to_array( $dao, $values, TRUE );
}

/*
 * Function to get existing values when an 'id' is passed into a Create api
 * 
 * @params array $params input params
 * @return array $valuse params with existing values from contact
 */
function  civicrm_api3_update_get_existing($params, $function){
    $function = str_replace ( 'create' , 'get', $function );
    $values = $params;
    if(!empty($params['id'])){
        $getparams = array('id' => $params['id'],'version' => 3);
        $result = $function($getparams);
        $values = array_merge($result['values'][$params['id']],$params); 

    }
   
    return $values;
}   

/**
 * This function adds the contact variable in $values to the
 * parameter list $params.  For most cases, $values should have length 1.  If
 * the variable being added is a child of Location, a location_type_id must
 * also be included.  If it is a child of phone, a phone_type must be included.
 *
 * @param array  $values    The variable(s) to be added
 * @param array  $params    The structured parameter list
 * 
 * @return bool|CRM_Utils_Error
 * @access public
 */
function _civicrm_api3_add_formatted_param(&$values, $params) 
{
    /* Crawl through the possible classes: 
     * Contact 
     *      Individual 
     *      Household
     *      Organization
     *          Location 
     *              Address 
     *              Email 
     *              Phone 
     *              IM 
     *      Note
     *      Custom 
     */

    /* Cache the various object fields */
    static $fields = null;
    
    if ($fields == null) {
        $fields = array();
    }
    
    //first add core contact values since for other Civi modules they are not added
    require_once 'CRM/Contact/BAO/Contact.php';
    $contactFields =& CRM_Contact_DAO_Contact::fields( );
    _civicrm_api3_store_values( $contactFields, $values, $params );
    
    if (isset($values['contact_type'])) {
        /* we're an individual/household/org property */
        
        $fields[$values['contact_type']] = CRM_Contact_DAO_Contact::fields();
        
        _civicrm_api3_store_values( $fields[$values['contact_type']], $values, $params );
        return true;
    }
    
    if ( isset($values['individual_prefix']) ) {
        if ( $params['prefix_id'] ) {
            $prefixes = array( );
            $prefixes = CRM_Core_PseudoConstant::individualPrefix( );
            $params['prefix'] = $prefixes[$params['prefix_id']];
        } else {
            $params['prefix'] = $values['individual_prefix'];
        }
        return true;
    }

    if (isset($values['individual_suffix'])) {
        if ( $params['suffix_id'] ) {
            $suffixes = array( );
            $suffixes = CRM_Core_PseudoConstant::individualSuffix( );
            $params['suffix'] = $suffixes[$params['suffix_id']];
        } else {
            $params['suffix'] = $values['individual_suffix'];
        }
        return true;
    }
    
    //CRM-4575
    if ( isset( $values['email_greeting'] ) ) {
        if ( $params['email_greeting_id'] ) {
            $emailGreetings = array( );
            $emailGreetingFilter = array( 'contact_type'  => CRM_Utils_Array::value('contact_type', $params),
                                          'greeting_type' => 'email_greeting' );
            $emailGreetings = CRM_Core_PseudoConstant::greeting( $emailGreetingFilter );
            $params['email_greeting'] = $emailGreetings[$params['email_greeting_id']];
        } else {
            $params['email_greeting'] = $values['email_greeting'];
        }
        
        return true;
    }
    
    if ( isset($values['postal_greeting'] ) ) {
        if ( $params['postal_greeting_id'] ) {
            $postalGreetings = array( );
            $postalGreetingFilter = array( 'contact_type'  => CRM_Utils_Array::value('contact_type', $params),
                                           'greeting_type' => 'postal_greeting' );
            $postalGreetings = CRM_Core_PseudoConstant::greeting( $postalGreetingFilter );
            $params['postal_greeting'] = $postalGreetings[$params['postal_greeting_id']];
        } else {
            $params['postal_greeting'] = $values['postal_greeting'];
        }
        return true;
    }
    
    if ( isset($values['addressee'] ) ) {
        if ( $params['addressee_id'] ) {
            $addressee = array( );
            $addresseeFilter = array( 'contact_type'  => CRM_Utils_Array::value('contact_type', $params),
                                      'greeting_type' => 'addressee' );
            $addressee = CRM_Core_PseudoConstant::addressee( $addresseeFilter );
            $params['addressee'] = $addressee[$params['addressee_id']];
        } else {
            $params['addressee'] = $values['addressee'];
        }
        return true;
    }
    
    if ( isset($values['gender']) ) {
        if ( $params['gender_id'] ) {
            $genders = array( );
            $genders = CRM_Core_PseudoConstant::gender( );
            $params['gender'] = $genders[$params['gender_id']];
        } else {
            $params['gender'] = $values['gender'];
        }
        return true;
    }
    
    if ( isset($values['preferred_communication_method']) ) {
        $comm = array( );
        $preffComm = array( );
        $pcm = array( );
        $pcm = array_change_key_case( array_flip( CRM_Core_PseudoConstant::pcm() ), CASE_LOWER);
                                    
        $preffComm = explode(',' , $values['preferred_communication_method']);
        foreach ($preffComm as $v) {
            $v = strtolower(trim($v));
            if ( array_key_exists ( $v, $pcm) ) {
                $comm[$pcm[$v]] = 1;
            }
        }
        
        $params['preferred_communication_method'] = $comm;
        return true;
    }
    
    //format the website params.
    if ( CRM_Utils_Array::value( 'url', $values ) ) {
        static $websiteFields;
        if ( !is_array( $websiteFields ) ) {
            require_once 'CRM/Core/DAO/Website.php';
            $websiteFields = CRM_Core_DAO_Website::fields( );
        }
        if ( !array_key_exists( 'website', $params ) || 
             !is_array( $params['website'] ) ) {
            $params['website'] = array( );
        }
        
        $websiteCount = count( $params['website'] );
        _civicrm_api3_store_values( $websiteFields, $values,
                                    $params['website'][++$websiteCount] );
        
        return true;
    }
    
    // get the formatted location blocks into params - w/ 3.0 format, CRM-4605
    if ( CRM_Utils_Array::value( 'location_type_id', $values ) ) {
        _civicrm_api3_add_formatted_location_blocks( $values, $params );
        return true;
    }
    
    if (isset($values['note'])) {
        /* add a note field */
        if (!isset($params['note'])) {
            $params['note'] = array();
        }
        $noteBlock = count($params['note']) + 1;
        
        $params['note'][$noteBlock] = array();
        if (!isset($fields['Note'])) {
            $fields['Note'] = CRM_Core_DAO_Note::fields();
        }
        
        // get the current logged in civicrm user
        $session          = CRM_Core_Session::singleton( );
        $userID           =  $session->get( 'userID' );

        if ( $userID ) {
            $values['contact_id'] = $userID;
        }

        _civicrm_api3_store_values($fields['Note'], $values, $params['note'][$noteBlock]);

        return true;
    }
    
    /* Check for custom field values */
    if ($fields['custom'] == null) {
        $fields['custom'] =& CRM_Core_BAO_CustomField::getFields( $values['contact_type'], false, false, null, null, false, false, false );
    }
    
    foreach ($values as $key => $value) {
        if ($customFieldID = CRM_Core_BAO_CustomField::getKeyID($key)) {
            /* check if it's a valid custom field id */
            if (!array_key_exists($customFieldID, $fields['custom'])) {
                return civicrm_api3_create_error('Invalid custom field ID');
            } else {
                $params[$key] = $value;
            }
        }
    }
}

/**
 * This function format location blocks w/ v3.0 format.
 *
 * @param array  $values    The variable(s) to be added
 * @param array  $params    The structured parameter list
 * 
 * @return bool
 * @access public
 */
function _civicrm_api3_add_formatted_location_blocks( &$values, $params ) 
{
    static $fields = null;
    if ( $fields == null ) {
        $fields = array();
    }
    
    foreach ( array( 'Phone', 'Email', 'IM', 'OpenID' ) as $block ) {
        $name = strtolower( $block );
        if ( !array_key_exists( $name, $values ) ) continue; 
        
        // block present in value array. 
        if ( !array_key_exists($name, $params) || !is_array($params[$name]) ) $params[$name] = array( ); 
        
        if ( !array_key_exists( $block, $fields ) ) {
            require_once( str_replace('_', DIRECTORY_SEPARATOR, "CRM_Core_DAO_" . $block ) . ".php");
            eval( '$fields[$block] =& CRM_Core_DAO_' . $block . '::fields( );' );
        }
        
        $blockCnt = count( $params[$name] );
        
        // copy value to dao field name.
        if ( $name == 'im' ) $values['name'] = $values[$name];
        
        _civicrm_api3_store_values( $fields[$block ], $values,
                                    $params[$name][++$blockCnt] );
        
        if ( !CRM_Utils_Array::value( 'id', $params ) && ( $blockCnt == 1 ) ) {
            $params[$name][$blockCnt]['is_primary'] = true;
        }
        
        // we only process single block at a time.
        return true;
    }
    
    // handle address fields.
    if ( !array_key_exists('address', $params) || !is_array($params['address']) ) $params['address'] = array( );
    
    $addressCnt = 1;
    foreach ( $params['address'] as $cnt => $addressBlock ) {
        if ( CRM_Utils_Array::value( 'location_type_id', $values ) ==
             CRM_Utils_Array::value( 'location_type_id', $addressBlock ) ) { 
            $addressCnt = $cnt;
            break;
        }
        $addressCnt++;
    }
    
    if ( !array_key_exists( 'Address', $fields ) ) {
        require_once 'CRM/Core/DAO/Address.php';
        $fields['Address'] =& CRM_Core_DAO_Address::fields( );
    }
    _civicrm_api3_store_values( $fields['Address'], $values, $params['address'][$addressCnt] );
    
    $addressFields = array(   'county', 'country', 'state_province', 
                              'supplemental_address_1', 'supplemental_address_2', 
                              'StateProvince.name' );
    
    foreach ( $addressFields as $field ) {
        if ( array_key_exists( $field, $values ) ) {
            if ( !array_key_exists( 'address', $params ) ) $params['address'] = array( ); 
            $params['address'][$addressCnt][$field] = $values[$field];
        }
    }
    
    if ( $addressCnt == 1 ) $params['address'][$addressCnt]['is_primary'] = true;
    
    return true;
}

/**
 * Check a formatted parameter list for required fields.  Note that this
 * function does no validation or dupe checking.
 *
 * @param array $params  Structured parameter list (as in crm_format_params)
 *
 * @return bool|CRM_core_Error  Parameter list has all required fields
 * @access public
 */
function _civicrm_api3_required_formatted_contact($params) 
{
    
    if (! isset($params['contact_type'])) {
        return civicrm_api3_create_error('No contact type specified');
    }
    
    switch ($params['contact_type']) {
    case 'Individual':
        if (isset($params['first_name']) && isset($params['last_name'])) {
            return civicrm_api3_create_success(true);
        }
            
        if ( array_key_exists( 'email', $params ) &&
             is_array( $params['email'] ) && 
             !CRM_Utils_System::isNull( $params['email'] ) ) { 
            return civicrm_api3_create_success(true);
        }
            
        break;
    case 'Household':
        if (isset($params['household_name'])) {
            return civicrm_api3_create_success(true);
        }
        break;
    case 'Organization':
        if (isset($params['organization_name'])) {
            return civicrm_api3_create_success(true);
        }
        break;
    default:
        return 
            civicrm_api3_create_error('Invalid Contact Type: ' . $params['contact_type'] );
    }

    return civicrm_api3_create_error('Missing required fields');
}

/**
 *
 * @param <type> $params
 * @return <type>
 */
function _civicrm_api3_duplicate_formatted_contact($params) 
{
    $id = CRM_Utils_Array::value( 'id', $params );
    $externalId = CRM_Utils_Array::value( 'external_identifier', $params );
    if ( $id || $externalId ) {
        $contact = new CRM_Contact_DAO_Contact( );
        
        $contact->id = $id;
        $contact->external_identifier = $externalId;
        
        if ( $contact->find( true ) ) {
            if ( $params['contact_type'] != $contact->contact_type ) {
                return civicrm_api3_create_error( "Mismatched contact IDs OR Mismatched contact Types" );
            }
            
            $error = CRM_Core_Error::createError( "Found matching contacts: $contact->id",
                                                  CRM_Core_Error::DUPLICATE_CONTACT, 
                                                  'Fatal', $contact->id );
            return civicrm_api3_create_error( $error->pop( ) );
        }
    } else {
        require_once 'CRM/Dedupe/Finder.php';
        $dedupeParams = CRM_Dedupe_Finder::formatParams($params, $params['contact_type']);
        $ids = CRM_Dedupe_Finder::dupesByParams($dedupeParams, $params['contact_type'], 'Strict');
            
        if ( !empty($ids) ) {
            $ids = implode( ',', $ids );
            $error = CRM_Core_Error::createError( "Found matching contacts: $ids",
                                                  CRM_Core_Error::DUPLICATE_CONTACT, 
                                                  'Fatal', $ids );
            return civicrm_api3_create_error( $error->pop( ) );
        } 
    }
    return civicrm_api3_create_success( true );
}

/**
 * Validate a formatted contact parameter list.
 *
 * @param array $params  Structured parameter list (as in crm_format_params)
 *
 * @return bool|CRM_Core_Error
 * @access public
 */
function _civicrm_api3_validate_formatted_contact($params) 
{
    /* Look for offending email addresses */
    if ( array_key_exists( 'email', $params ) ) {
        foreach ( $params['email']  as $count => $values ) {
            if( !is_array( $values ) ) continue;
            if ( $email = CRM_Utils_Array::value( 'email', $values ) ) {
                //validate each email 
                if ( !CRM_Utils_Rule::email( $email ) ) {
                    return civicrm_api3_create_error( 'No valid email address');
                }
                
                //check for loc type id.
                if ( !CRM_Utils_Array::value( 'location_type_id', $values ) ) {
                    return civicrm_api3_create_error( 'Location Type Id missing.');
                }
            }
        }
    }
    
    /* Validate custom data fields */
    if ( array_key_exists( 'custom', $params ) && is_array($params['custom']) ) {
        foreach ($params['custom'] as $key => $custom) {
            if (is_array($custom)) {
                $valid = CRM_Core_BAO_CustomValue::typecheck(
                                                             $custom['type'], $custom['value']);
                if (! $valid) {
                    return civicrm_api3_create_error('Invalid value for custom field \'' .
                                                     $custom['name']. '\'');
                }
                if ( $custom['type'] == 'Date' ) {
                    $params['custom'][$key]['value'] = str_replace( '-', '', $params['custom'][$key]['value'] );
                }
            }
        }
    }

    return civicrm_api3_create_success( true );
}

/**
 *
 * @param array $params
 * @param array $values
 * @param string $extends entity that this custom field extends (e.g. contribution, event, contact)
 * @param string $entityId ID of entity per $extends
 */
function _civicrm_api3_custom_format_params( $params, &$values, $extends, $entityId = null )
{
    $values['custom'] = array();
        
    require_once 'CRM/Core/BAO/CustomField.php';
    foreach ($params as $key => $value) {
        list( $customFieldID, $customValueID ) = CRM_Core_BAO_CustomField::getKeyID($key, true );
        if ( $customFieldID ) {
            CRM_Core_BAO_CustomField::formatCustomField( $customFieldID, $values['custom'], 
                                                         $value, $extends, $customValueID, $entityId, false, false );
        }
    }
}



/**
 * This function ensures that we have the right input parameters
 *
 * We also need to make sure we run all the form rules on the params list
 * to ensure that the params are valid
 *
 * @param array  $params       Associative array of property name/value
 *                             pairs to insert in new history.
 * @daoName string DAO to check params agains
 * @return bool should the missing fields be returned as an array (core error created as default)
 *                           
 * @todo the check for required fields unsets the ID as that isn't required for create but potentially also unsets other ID fields, note also the DAO might be a bit 'harsh' in it's required fields as the BAO handles some
 * @return bool true if all fields present, depending on $result a core error is created of an array of missing fields is returned
 * @access public
 */
function _civicrm_api3_check_required_fields( $params, $daoName, $return = FALSE)
{
    if ( isset($params['extends'] ) ) {
        if ( ( $params['extends'] == 'Activity' || 
               $params['extends'] == 'Phonecall'  || 
               $params['extends'] == 'Meeting'    || 
               $params['extends'] == 'Group'      || 
               $params['extends'] == 'Contribution' 
               ) && 
             ( $params['style'] == 'Tab' ) ) {
            return civicrm_api3_create_error(ts("Can not create Custom Group in Tab for ". $params['extends']));
        }
    }

    require_once(str_replace('_', DIRECTORY_SEPARATOR, $daoName) . ".php");
  
    $dao = new $daoName();
    $fields = $dao->fields();
 
    $missing = array();
    foreach ($fields as $k => $v) {
        if ($v['name'] == 'id') {
            continue;
        }
        
        if ( CRM_Utils_Array::value( 'required', $v ) ) {
            if ( empty( $params[$k] ) && !( $params[$k] === 0 ) ) { // 0 is a valid input for numbers, CRM-8122
                $missing[] = $k;
            }
        }
    }

    if (!empty($missing)) {
        if (!empty($return)) {
            return $missing;
        }else{
            return civicrm_api3_create_error(ts("Required fields ". implode(',', $missing) . " for $daoName are not present"));
        }
    }

    return true;
}



/**
 *  Function to check duplicate contacts based on de-deupe parameters
 */
function civicrm_api3_check_contact_dedupe( $params ) {
    static $cIndieFields = null;
    static $defaultLocationId = null;
    
    $contactType = $params['contact_type'] ;
    if ( $cIndieFields == null ) {
        require_once 'CRM/Contact/BAO/Contact.php';
        $cTempIndieFields = CRM_Contact_BAO_Contact::importableFields( $contactType );
        $cIndieFields = $cTempIndieFields;

        require_once "CRM/Core/BAO/LocationType.php";
        $defaultLocation =& CRM_Core_BAO_LocationType::getDefault();

        //set the value to default location id else set to 1
        if ( !$defaultLocationId = (int)$defaultLocation->id ) $defaultLocationId = 1;
    }
    
    require_once 'CRM/Contact/BAO/Query.php';
    $locationFields = CRM_Contact_BAO_Query::$_locationSpecificFields;
    
    $contactFormatted = array( );
    foreach ( $params as $key => $field ) {
        if ($field == null || $field === '') {
            continue;
        }
        if (is_array($field)) {
            foreach ($field as $value) {
                $break = false;
                if ( is_array($value) ) {
                    foreach ($value as $name => $testForEmpty) {
                        if ($name !== 'phone_type' &&
                            ($testForEmpty === '' || $testForEmpty == null)) {
                            $break = true;
                            break;
                        }
                    }
                } else {
                    $break = true;
                }
                if ( !$break ) {    
                    _civicrm_api3_add_formatted_param($value, $contactFormatted );
                }
            }
            continue;
        }
        
        $value = array($key => $field);
        
        // check if location related field, then we need to add primary location type
        if ( in_array($key, $locationFields) ) {
            $value['location_type_id'] = $defaultLocationId;
        } else if (array_key_exists($key, $cIndieFields)) {
            $value['contact_type'] = $contactType;
        }

        _civicrm_api3_add_formatted_param( $value, $contactFormatted );
    }

    $contactFormatted['contact_type'] = $contactType;

    return _civicrm_api3_duplicate_formatted_contact( $contactFormatted );
} 

/**
 * Check permissions for a given API call.
 *
 * @param $entity string API entity being accessed
 * @param $action string API action being performed
 * @param $params array  params of the API call
 * @param $throw bool    whether to throw exception instead of returning false
 *
 * @return bool whether the current API user has the permission to make the call
 */
function civicrm_api3_api_check_permission($entity, $action, &$params, $throw = true)
{
    // return early unless we’re told explicitly to do the permission check
    if (empty($params['check_permissions']) or $params['check_permissions'] == false) return true;

    require_once 'CRM/Core/Permission.php';

    require_once 'CRM/Core/DAO/.permissions.php';
    $permissions = _civicrm_api3_permissions($entity, $action, $params);

    // $params might’ve been reset by the alterAPIPermissions() hook
    if (isset($params['check_permissions']) and $params['check_permissions'] == false) return true;

    foreach ($permissions as $perm) {
        if (!CRM_Core_Permission::check($perm)) {
            if ($throw) {
                throw new Exception("API permission check failed for $entity/$action call; missing permission: $perm.");
            } else {
                return false;
            }
        }
    }
    return true;
}

/*
 * Function to do a 'standard' api get - when the api is only doing a $bao->find then use this
 * 
 * @param string $bao_name name of BAO
 * @param array $params params from api
 * @param bool $returnAsSuccess return in api success format
 */
function _civicrm_api3_basic_get($bao_name, &$params, $returnAsSuccess = TRUE){
    $bao = new $bao_name();
    _civicrm_api3_dao_set_filter ( $bao, $params, FALSE );
    if($returnAsSuccess){
        return civicrm_api3_create_success(_civicrm_api3_dao_to_array ($bao,$params, FALSE),$params,$bao);
    }else{
        return _civicrm_api3_dao_to_array ($bao,$params, FALSE);
    }
}

/*
 * Function to do a 'standard' api create - when the api is only doing a $bao::create then use this
 */
function _civicrm_api3_basic_create($bao_name, &$params){

    $args = array(&$params);
    if (method_exists($bao_name, 'create')) {
      $fct='create';
    } elseif (method_exists($bao_name, 'add')) {
      $fct='add';
    }
    if (!isset ($fct)) {
        return civicrm_api3_create_error( 'Entity not created, missing create or add method for '.$bao_name );
    }
    $bao = call_user_func_array(array($bao_name, $fct), $args);
    if ( is_null( $bao) ) {
        return civicrm_api3_create_error( 'Entity not created '.$bao_name.'::'.$fct );
    } else {
        $values = array();
        _civicrm_api3_object_to_array($bao, $values[ $bao->id]);
        return civicrm_api3_create_success($values,$params,$bao,'create' );
    }
}

/*
 * Function to do a 'standard' api del - when the api is only doing a $bao::del then use this
 */
function _civicrm_api3_basic_delete($bao_name, &$params){

    civicrm_api3_verify_mandatory($params,null,array('id'));
    $args = array(&$params['id']);
    $bao = call_user_func_array(array($bao_name, 'del'), $args);
    return civicrm_api3_create_success( true );
}

/*
 * Get custom data for the given entity & Add it to the returnArray as 'custom_123' = 'custom string' AND 'custom_123_1' = 'custom string'
 * Where 123 is field value & 1 is the id within the custom group data table (value ID)
 * 
 * @param array $returnArray - array to append custom data too - generally $result[4] where 4 is the entity id.
 * @param string $entity  e.g membership, event 
 * @param int $groupID - per CRM_Core_BAO_CustomGroup::getTree
 * @param int $subType e.g. membership_type_id where custom data doesn't apply to all membership types
 * @param string $subName - Subtype of entity 
 * 
 */
function _civicrm_api3_custom_data_get(&$returnArray,$entity,$entity_id ,$groupID = null,$subType = null, $subName = null){
    require_once 'CRM/Core/BAO/CustomGroup.php'; 
    require_once 'CRM/Core/BAO/CustomField.php'; 
    $groupTree =& CRM_Core_BAO_CustomGroup::getTree($entity, 
                                                    CRM_Core_DAO::$_nullObject, 
                                                    $entity_id , 
                                                    $groupID,
                                                    $subType,
                                                    $subName);
    $groupTree = CRM_Core_BAO_CustomGroup::formatGroupTree( $groupTree, 1, CRM_Core_DAO::$_nullObject );
    $customValues = array( );
    CRM_Core_BAO_CustomGroup::setDefaults( $groupTree, $customValues );
    if ( !empty( $customValues ) ) {
        foreach ( $customValues as $key => $val ) {
            if(strstr($key, '_id')){
              $idkey = substr($key,0,-3);
              $returnArray['custom_' . (CRM_Core_BAO_CustomField::getKeyID($idkey ) . "_id")] = $val;
              $returnArray[$key] = $val;
            }else{
            // per standard - return custom_fieldID
            $returnArray['custom_' . (CRM_Core_BAO_CustomField::getKeyID($key))] = $val;

            //not standard - but some api did this so guess we should keep - cheap as chips
            $returnArray[$key] = $val;
            }
        }
    }
}

/*
 * Validate fields being passed into API. This function relies on the getFields function working accurately
 * for the given API. 
 * 
 * As of writing only date was implemented.
 * @param string $entity
 * @param string $action
 * @param array $params - 
 * all variables are the same as per civicrm_api
 */
function _civicrm_api3_validate_fields($entity, $action, &$params) {
  //skip any entities without working getfields functions
  $skippedEntities = array('entity', 'mailinggroup', 'customvalue', 'custom_value', 'mailing_group');
  if (in_array(strtolower($entity), $skippedEntities) || strtolower ( $action ) == 'getfields'){
			return;
	}
	$fields = civicrm_api ( $entity, 'getfields', array ('version' => 3 ) );
	$fields = $fields['values'];
	foreach ( $fields as $fieldname => $fieldInfo ) {
        switch (CRM_Utils_Array::value ( 'type', $fieldInfo )){
        case 4:
        case 12: 
            //field is of type date or datetime
            _civicrm_api3_validate_date($params,$fieldname,$fieldInfo);
            break;
        }

	
	}
}

/*
 * Validate date fields being passed into API. 
 * It currently converts both unique fields and DB field names to a mysql date.
 * It also checks against the RULE:date function. This is a centralisation of code that was scattered and 
 * may not be the best thing to do. There is no code level documentation on the existing functions to work off
 * 
 * @param array $params params from civicrm_api
 * @param string $fieldname uniquename of field being checked
 * @param array $fieldinfo array of fields from getfields function
 */
function _civicrm_api3_validate_date(&$params,&$fieldname,&$fieldInfo){
  	//should we check first to prevent it from being copied if they have passed in sql friendly format?
    if (CRM_Utils_Array::value ( $fieldInfo ['name'], $params )) {	
        //accept 'whatever strtotime accepts
        if (strtotime($params [$fieldInfo ['name']]) ==0) {
            throw new exception ($fieldInfo ['name']. " is not a valid date: " . $params [$fieldInfo ['name']]);
        }
        $params [$fieldInfo ['name']] = CRM_Utils_Date::processDate ( $params [$fieldInfo ['name']] );
    } 
    if ((CRM_Utils_Array::value ('name', $fieldInfo) != $fieldname ) && CRM_Utils_Array::value ( $fieldname , $params )) {
        //If the unique field name differs from the db name & is set handle it here
        if (strtotime($params [$fieldname]) ==0) {
            throw new exception ($fieldname. " is not a valid date: " . $params [$fieldname]);
        }
        $params [$fieldname] = CRM_Utils_Date::processDate ( $params [$fieldname] );
    }
  
}

/**
 * Generic implementation of the "replace" action.
 *
 * Replace the old set of entities (matching some given keys) with a new set of
 * entities (matching the same keys).
 *
 * Note: This will verify that 'values' is present, but it does not directly verify
 * any other parameters.
 *
 * @param string $entity entity name
 * @param array $params params from civicrm_api, including:
 *   - 'values': an array of records to save
 *   - all other items: keys which identify new/pre-existing records
 */
function _civicrm_api3_generic_replace($entity, $params) {

    require_once 'CRM/Core/Transaction.php';
    $tx = new CRM_Core_Transaction();
    try {
        if (!is_array($params['values'])) {
            throw new Exception("Mandatory key(s) missing from params array: values");                     
        }
        
        // Extract the keys -- somewhat scary, don't think too hard about it
        $baseParams = $params;
        unset($baseParams['values']);
        unset($baseParams['sequential']);
        
        // Lookup pre-existing records
        $preexisting = civicrm_api($entity, 'get', $baseParams, $params);
        if (civicrm_api3_error($preexisting)) {
            $tx->rollback();
            return $preexisting;
        }
        
        // Save the new/updated records
        $creates = array();
        foreach ($params['values'] as $replacement) {
            // Sugar: Don't force clients to duplicate the 'key' data
            $replacement = array_merge($baseParams, $replacement);
            $action = (isset($replacement['id']) || isset($replacement[$entity.'_id'])) ? 'update' : 'create';
            $create = civicrm_api($entity, $action, $replacement);
            if (civicrm_api3_error($create)) {
                $tx->rollback();
                return $create;
            }
            foreach ($create['values'] as $entity_id => $entity_value) {
                $creates[$entity_id] = $entity_value;
            }
        }
        
        // Remove stale records
        $staleIDs = array_diff(
                               array_keys($preexisting['values']),
                               array_keys($creates)
                               );
        foreach ($staleIDs as $staleID) {
            $delete = civicrm_api($entity, 'delete', array(
                                                           'version' => $params['version'],
                                                           'id' => $staleID
                                                           ));
            if (civicrm_api3_error($delete)) {
                $tx->rollback();
                return $delete;
            }
        }
        
        return civicrm_api3_create_success($creates, $params);
        
    } catch (PEAR_Exception $e) {
        $tx->rollback();
        return civicrm_api3_create_error( $e->getMessage() );
    } catch (Exception $e) {
        $tx->rollback();
        return civicrm_api3_create_error( $e->getMessage() );
    }
}

/*
 * returns fields allowable by api
 */
function _civicrm_api_get_fields($entity){
    $dao = _civicrm_api3_get_DAO ($entity);
    if (empty($dao)) {
        return civicrm_api3_create_error("API for $entity does not exist (join the API team and implement $function" );
    }
    $file = str_replace ('_','/',$dao).".php";
    require_once ($file); 
    $d = new $dao();
    $fields = $d->fields() + _civicrm_api_get_custom_fields($entity) ;
    return $fields;
}

/*
 * Return an array of fields for a given entity - this is the same as the BAO function but 
 * fields are prefixed with 'custom_' to represent api params
 */
function _civicrm_api_get_custom_fields($entity){
    require_once 'CRM/Core/BAO/CustomField.php';
    $customfields = array();
    $customfields = CRM_Core_BAO_CustomField::getFields($entity) ;
    foreach ($customfields as $key => $value) {
        $customfields['custom_' . $key] = $value;
        unset($customfields[$key]);
    }
    return $customfields;
}

