<?php 

function address_create_example(){
	$params = array(
		'version' 			=>  '3',
		'contact_id'		=>	'1',
		'location_type_id'	=>	'2',
		'street_name'		=>	'Ambachtstraat',
		'street_number'		=>	'23',
		'street_address'	=>	'Ambachtstraat 23',
		'postal_code'		=>	'6971 BN',
		'country_id'		=>	'1152',
		'city'				=>	'Brummen'
  );
  require_once 'api/api.php';
  $result = civicrm_api( 'address','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function address_create_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 			=> '13',
           'id' 			=> '61',
           'values' 		=> array(           
                                'id' 				=>	'61',
                                'contact_id'		=>	'1',
                                'location_type_id'	=>	'2',
                                'is_primary'		=>	'0',
                                'is_billing'		=>	'0',
                                'street_address'	=>	'Ambachtstraat 23',
                                'street_number'		=>	'23',
                                'street_name'		=>	'Ambachtstraat',
                                'city'				=>	'Brummen',
                                'postal_code'		=>	'6971 BN',
                                'country_id'		=>	'1152',
                                'geo_code_1'		=>	'52.0876090',
                                'geo_code_2'		=>	'1551508'
								),
      );

  return $expectedResult  ;
}


