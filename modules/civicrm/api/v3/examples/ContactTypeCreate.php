<?php 

function contact_type_create_example(){
    $params = array(
    
                  'first_name' 		=> 'Anne',
                  'last_name' 		=> 'Grant',
                  'contact_type' 		=> 'Individual',
                  'contact_sub_type' 		=> 'sub_individual',
                  'version' 		=> '',
                  'custom' 		=> 'Array',
                  'preferred_language' 		=> 'en_US',
                  'is_deceased' 		=> '',
                  'contact_id' 		=> '1',
                  'website' 		=> '',

  );
  require_once 'api/api.php';
  $result = civicrm_api_legacy( 'civicrm_contact_type_create','ContactType',$params );

  return $result;


}



function contact_type_create_expectedresult(){

  $expectedResult = array(
                  'contact_id' 		=> '1',
                  'is_error' 		=> '0',

  );

  return $expectedResult  ;
}

