<?php

class CRM_Utils_SAGE {
	
	public $base = "http://sage.nysenate.gov/api/";
	
	public static function standardize( &$values ) {
		$session = CRM_Core_Session::singleton();
        
        foreach( array('street_address','supplemental_address_1','supplemental_address_2') as $addr_field) {
        	if(!empty(CRM_Utils_Array::value($addr_field,$values,""))) {
        		$addr2 = $values[$addr_field];
        		break;
        	}
        }
        
        if (!$addr2) {
            $session->setStatus(ts('SAGE Warning: Not enough address info.'));
            return false;
        }
        
        #Construct and send the API Request
        $url = 'xml/validate/extended?';
        $params = http_build_query( array(
        		'addr2' => str_replace(',', '', $addr2),
                'city' => CRM_Utils_Array::value('city',$values,""),
                'zip5' => CRM_Utils_Array::value('postal_code',$values,""),
                'state' => CRM_Utils_Array::value('state_province',$values,""),
        		'key' => CRM_Core_BAO_Preferences::value('address_standardization_userid'),
        	));
		require_once 'HTTP/Request.php';
        $request = new HTTP_Request(self::$base . $url . $params);
        $request->sendRequest();
        $xml = simplexml_load_string($request->getResponseBody());
        
        #Check the response for validity
	    if (is_null($xml) || is_null($xml->address2)) {
            $session->setStatus(ts("SAGE Warning: Postal lookup for [$addr2] has failed.\n"));
            return false;
        } 
        else if (!empty($xml->message)) {
            $session->setStatus(ts('SAGE Warning: '.$xml->message));
            return false;
        }
        
        #Normalize and store the results
        $addr2 = ucwords(strtolower((string)$xml->address2));
        if (substr($addr2, 0, 6) == "Po Box") {
            $addr2 = "P.O. Box".substr($addr2, 6);
        }
        else {
            $addr_elems = explode(" ", $addr2);
            for ($j = 0; $j < count($addr_elems); $j++) {
                if ((preg_match("/^[1-9]*[1](st)$/", $addr_elems[$j])) ||
                    (preg_match("/^[1-9]*[2](nd)$/", $addr_elems[$j])) ||
                    (preg_match("/^[1-9]*[3](rd)$/", $addr_elems[$j])) ||
                    (preg_match("/^[1-9]*[4-9,0](th)$/", $addr_elems[$j]))) {
                    //don't do anything
                }
                elseif (preg_match("/^[1-9][0-9a-zA-Z]+/", $addr_elems[$j])) {
                    $addr_elems[$j] = strtoupper($addr_elems[$j]);
                }
            }
            $addr2 = implode(" ", $addr_elems);
        }
 
        $values[$addr_field] = $addr2;
        $values['city'] = ucwords(strtolower($xml->city));
        $values['state_province'] = $xml->state;
        $values['postal_code'] = $xml->zip5;
        $values['postal_code_suffix'] = $xml->zip4;
        return true;
	}
	
	public static function geocode( $params, $stateName = False ) {
	    // we need a valid country, else we ignore
        if (! CRM_Utils_Array::value('country', $values)) {
            return false;
        }

        if (CRM_Utils_Array::value('state_province', $values)) { 
            if (CRM_Utils_Array::value('state_province_id', $values)) {
                $stateProvince = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_StateProvince', $values['state_province_id']);
            }
            else {
                if (!$stateName) {
                    $stateProvince = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_StateProvince', $values['state_province'], 'name', 'abbreviation');
                }
                else {
                    $stateProvince = $values['state_province'];
                }
            }
            // dont add state twice if replicated in city (happens in NZ and other countries, CRM-2632)
            if ($stateProvince != CRM_Utils_Array::value('city', $values)) {
                $arg[] = "state=" . urlencode($stateProvince);
            }
        }

        /* Without service=geocoder, SAGE will default to Yahoo as the
        ** geocoding provider. "geocoder" is the Senate's own geocoding
        *  provider, which uses the open source "geocoder" project. */
        $url = 'xml/geocode/extended/extended?';
        $params = http_build_query(array(
        		'street' => CRM_Utils_Array::value('street_address', $values, ""),
        		'city' => CRM_Utils_Array::value('city', $values, ""),
        		'country' => CRM_Utils_Array::value('country', $values, ""),
        		'zip5' => CRM_Utils_Array::value('postal_code', $values, ""),
        		'service' => 'geocoder',
        		'key' => CRM_Core_Config::singleton()->geoAPIKey,
        	));
        require_once 'HTTP/Request.php';
        $request = new HTTP_Request(self::$base . $url . $params);
        $request->sendRequest();
        $xml = simplexml_load_string($request->getResponseBody());

        if( !$xml || $xml->message ) {
        	$values['geo_code_1'] = $values['geo_code_2'] = 'null';
        	return False;
        }
        
       	$values['geo_code_1'] = $xml->lat;
       	$values['geo_code_2'] = $xml->lon;
       	return true;
	}
	
	public static function distassign( &$values ) {
		$session = CRM_Core_Session::singleton();
		
		foreach( array('street_address','supplemental_address_1','supplemental_address_2') as $addr_field) {
        	if(!empty(CRM_Utils_Array::value($addr_field,$values,""))) {
        		$addr2 = $values[$addr_field];
        		break;
        	}
        }
        
		if (!$addr2) {
            $session->setStatus(ts('SAGE Warning: Not enough address info.'));
            return false;
        }
        
        #Construct and send the API Request
		$url = 'xml/district/extended?';
        $params = http_build_query( array(
        		'addr2' => str_replace(',', '', $addr2),
                'city' => CRM_Utils_Array::value('city',$values,""),
                'zip5' => CRM_Utils_Array::value('postal_code',$values,""),
                'state' => CRM_Utils_Array::value('state_province',$values,""),
        		'service' => 'none',
        		'key' => CRM_Core_Config::singleton()->geoAPIKey,
        	));
		require_once 'HTTP/Request.php';
        $request = new HTTP_Request(self::$base . $url . $params);
        $request->sendRequest();
        $xml = simplexml_load_string($request->getResponseBody());
        
        $xml->
	}
	
	public static function lookup( $params ) {
		
	}
}