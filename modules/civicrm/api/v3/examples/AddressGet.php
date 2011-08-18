<?php 

function address_get_example(){
    $params = array(
                  'contact_id' 			=> '1',
                  'location_type_id' 	=> '1',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'address','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function address_get_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '1',
           'id' 		=> '63',
           'values' 		=> array('63' =>  array(
						'id' 				=>	'63',
						'contact_id'		=>	'1',
						'location_type_id'	=>	'1',
						'is_primary'		=>	'1',
						'is_billing'		=>	'0',
						'street_address'	=>	'14 Example Way',
						'street_number'		=>	'14',
						'street_name'		=>	'Example Way',
						'city'				=>	'Apeldoorn',
						'postal_code'		=>	'7743 AA',
						'country_id'		=> 	'1152',
						'geo_code_1'		=>	'52.1045501',
						'geo_code_2'		=>	'6.1667454'
           ),           ),
      );

  return $expectedResult  ;
}


