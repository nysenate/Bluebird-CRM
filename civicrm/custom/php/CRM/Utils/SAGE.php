<?php

require_once 'CRM/Core/BAO/Preferences.php';
require_once 'CRM/Core/Error.php';
require_once 'HTTP/Request.php';
require_once 'CRM/Core/BAO/Address.php';

class CRM_Utils_SAGE {

	public static $base = "http://sage.nysenate.gov/api/";

	public static $address_components = array(
			'street_number', 'street_name',
			'street_unit',   'street_number_suffix'
		);

	//TODO: supplemental_address_2 is never used?
	public static $address_locations = array(
			'street_address','supplemental_address_1'
		);

	public static function checkAddress( &$values ) {
		$session = CRM_Core_Session::singleton();

		//The address could be stored in a couple different places
		//get the address and remember where we found it for later
        list($addr_field,$addr2) = self::getAddress($values);
        //Sage throws back a cryptic warning if there is no address
        //Check first and use our own more descriptive warning
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
                'country' => CRM_Utils_Array::value('country', $values, ""),
                'key' => CRM_Core_BAO_Preferences::value('address_standardization_userid'),
            ),'', '&');
        $request = new HTTP_Request(self::$base . $url . $params);
        $request->sendRequest();
        $xml = simplexml_load_string($request->getResponseBody());

        if(self::validateResponse($xml)===false) {
            $session->setStatus(ts("SAGE Warning: Postal lookup for [$addr2] has failed.\n"));
            return false;
        }

        self::storeAddress($values,$xml,$addr_field);
        return true;
	}

	public static function format( &$values, $stateName=false ) {
		$session = CRM_Core_Session::singleton();

		//TODO: Why is this the only place we do the state lookup?
        $stateProvince = self::getStateProvince($values,$stateName);
        list($addr_field,$addr2) = self::getAddress($values);


        /* Construct and send the API Request
         * Without service=geocoder, SAGE will default to Yahoo as the
         * geocoding provider. "geocoder" is the Senate's own geocoding
         * provider, which uses the open source "geocoder" project.
         */
        $url = 'xml/geocode/extended/extended?';
        $params = http_build_query(array(
                'service' => 'geocoder',
                'addr2' => str_replace(',', '', $addr2),
                'state' => $stateProvince,
                'city' => CRM_Utils_Array::value('city', $values, ""),
                'zip5' => CRM_Utils_Array::value('postal_code', $values, ""),
                'key' => CRM_Core_Config::singleton()->geoAPIKey,
            ), '', '&');
        $request = new HTTP_Request(self::$base . $url . $params);
        $request->sendRequest();
        $xml = simplexml_load_string($request->getResponseBody());

		if(!self::validateResponse($xml)) {
			$values['geo_code_1'] = $values['geo_code_2'] = 'null'; //TODO: Why do we do this?
            $session->setStatus(ts("SAGE Warning: Geocoding for [$params] has failed.\n"));
            return false;
        }

        self::storeGeocodes($values, $xml);
        return true;
	}

	public static function distassign( &$values, $overwrite_districts=true ) {
		$session = CRM_Core_Session::singleton();

		//The address could be stored in a couple different places
		//get the address and remember where we found it for later
		list($addr_field,$addr2) = self::getAddress($values);
		if (!$addr2) {
            $session->setStatus(ts('SAGE Warning: Not enough address info.'));
            return false;
        }

        #Construct and send the API Request
		$url = 'xml/districts/extended?nometa=1&';
        $params = http_build_query( array(
                'addr2' => str_replace(',', '', $addr2),
                'city' => CRM_Utils_Array::value('city',$values,""),
                'zip5' => CRM_Utils_Array::value('postal_code',$values,""),
                'state' => CRM_Utils_Array::value('state_province',$values,""),
                'country' => CRM_Utils_Array::value('country', $values, ""),
                'key' => CRM_Core_Config::singleton()->geoAPIKey,
            ), '', '&');
        $request = new HTTP_Request(self::$base . $url . $params);
        $request->sendRequest();
        $xml = simplexml_load_string($request->getResponseBody());

		#Check the response for validity
		if(!self::validateResponse($xml)) {
            $session->setStatus(ts("SAGE Warning: Distassign for [$params] has failed.\n"));
            return false;
        }

        self::storeDistricts($values,$xml,$overwrite_districts);
        return true;
	}

	public static function lookup( &$values, $overwrite_districts=true) {
		$session = CRM_Core_Session::singleton();

		//The address could be stored in a couple different places
		//get the address and remember where we found it for later
		list($addr_field,$addr2) = self::getAddress($values);
        if (!$addr2) {
            $session->setStatus(ts('SAGE Warning: Not enough address info.'));
            return false;
        }

        #Construct and send the API Request
		$url = 'xml/bluebirdDistricts/extended?';
        $params = http_build_query( array(
                'addr2' => str_replace(',', '', $addr2),
                'city' => CRM_Utils_Array::value('city',$values,""),
                'zip5' => CRM_Utils_Array::value('postal_code',$values,""),
                'state' => CRM_Utils_Array::value('state_province',$values,""),
                'country' => CRM_Utils_Array::value('country', $values, ""),
                'key' => CRM_Core_Config::singleton()->geoAPIKey,
            ), '', '&');
        $request = new HTTP_Request(self::$base . $url . $params);
        $request->sendRequest();
        $xml = simplexml_load_string($request->getResponseBody());

		if(!self::validateResponse($xml)) {
            $session->setStatus(ts("SAGE Warning: Lookup for [$params] has failed.\n"));
            return false;
        }

        //SAGE will let us know if USPS address validation has failed by sending us a
        //"simple" address from the geocoding source instead of the "extended" USPS one
        if($xml->address->simple) {
            $msg = "SAGE Warning: USPS could not validate the address [$addr2]";
            $session->setStatus(ts($msg));
        } else {
            //NYSS don't clean up the address if importing
            $url_components = explode( '/', CRM_Utils_System::currentPath() );
            if ( $url_components[1] != 'import' )
                self::storeAddress($values, $xml->address->extended, $addr_field);
        }

        self::storeGeocodes($values, $xml);
        self::storeDistricts($values, $xml, $overwrite_districts);
        return true;
	}

	private static function validateResponse($xml) {
		//If the XML response is invalid, $xml will be NULL
		if (!$xml)
            return false;

        //The message element will only exist on invalid SAGE request
        if (!empty($xml->message)) {
            $session->setStatus(ts('SAGE Warning: '.$xml->message));
            return false;
        }

        return true;
	}

	private static function getEntity( $values ) {
		//-1 appears to signify "new entity" on forms, so -1 is default
		$entity = -1;
		foreach( array_keys($values) as $key){
			//Get the entity from the form input names using the following
			//method, borrowed from CRM_Core_BAO_CustomField::getKeyId
			if (preg_match('/^custom_(\d+)_?(-?\d+)?$/', $key, $match)) {
				$entity = CRM_Utils_Array::value(2,$match,-1);
				break;
			}
		}
		return $entity;
	}

	private static function getAddress( $values ) {
		foreach(self::$address_locations as $addr_field)
            if(CRM_Utils_Array::value($addr_field,$values))
                return array($addr_field,$values[$addr_field]);
        return array('street_address',"");
	}

	private static function getStateProvince( $values, $stateName )
	{
        if (CRM_Utils_Array::value('state_province', $values)) {
            if (CRM_Utils_Array::value('state_province_id', $values)) {
                $stateProvince = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_StateProvince', $values['state_province_id']);

            } else {
                if (!$stateName) {
                    $stateProvince = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_StateProvince', $values['state_province'], 'name', 'abbreviation');
                } else {
                    $stateProvince = $values['state_province'];
                }
            }
        }

        // dont add state twice if replicated in city (happens in NZ and other countries, CRM-2632)
        if($stateProvince && $stateProvince != CRM_Utils_Array::value('city', $values)) {
            return $stateProvince;
        } else {
            return "";
        }
	}

	private static function storeAddress( &$values, $xml, $addr_field ) {
		#Normalize and store the results
        $values[$addr_field] = self::normalizeStreetAddress((string)$xml->address2);
        $values['city'] = ucwords(strtolower((string)$xml->city));
        $values['state_province'] = (string)$xml->state;
        $values['postal_code'] = (string)$xml->zip5;
        $values['postal_code_suffix'] = (string)$xml->zip4;

        #Reprocess the address components since standardization could have changed them
        self::fixStreetAddressParts($values);
	}

    private static function storeGeocodes( &$values, $xml ) {
        $values['geo_code_1'] = (string)$xml->lat;
       	$values['geo_code_2'] = (string)$xml->lon;
    }

	private static function storeDistricts(&$values, $xml, $overwrite_districts) {
        //The district information needs to be stored for the form to pick it
        //up. The form includes the address primary key in the field name so
        //we must detect the pk to store addresses in the right slots.
        $address_id = self::getEntity($values);

        //$overwrite_districts can be true, but we use it like an array
        if($overwrite_districts==true)
            $overwrite_districts = array(true,true,true,true);
        //Write the SAGE values in as necessary. There are several instances,
        //see the nyss_sage module, where district should not be overwritten.
        //It is always the case that they should be filled in where blank.
	    if($overwrite_districts[0] || !$values["custom_46_$address_id"])
	        $values["custom_46_$address_id"] = (string)$xml->congressional->district;
	    if($overwrite_districts[1] || !$values["custom_47_$address_id"])
	        $values["custom_47_$address_id"] = (string)$xml->senate->district;
	    if($overwrite_districts[2] || !$values["custom_48_$address_id"])
	        $values["custom_48_$address_id"] = (string)$xml->assembly->district;
	    if($overwrite_districts[3] || !$values["custom_49_$address_id"])
	        $values["custom_49_$address_id"] = (string)$xml->election->district;
	}

	private static function normalizeStreetAddress( $addr2 )
	{
		//Standardize the case to Mixed Caps
		$addr2 = ucwords(strtolower($addr2));

        if (substr($addr2, 0, 6) == "Po Box") {
            $addr2 = "P.O. Box".substr($addr2, 6);
        }
        else {
            $addr_elems = explode(" ", $addr2);
            for ($j = 0; $j < count($addr_elems); $j++) {

                //Don't do anything to the following suffixes
                if ((preg_match("/^[1-9]*[1](st)$/", $addr_elems[$j])) ||
                    (preg_match("/^[1-9]*[2](nd)$/", $addr_elems[$j])) ||
                    (preg_match("/^[1-9]*[3](rd)$/", $addr_elems[$j])) ||
                    (preg_match("/^[1-9]*[4-9,0](th)$/", $addr_elems[$j]))) { /* pass */ }

                //Fix units with letters like 19A
                elseif (preg_match("/^[1-9][0-9a-zA-Z]+/", $addr_elems[$j])) {
                    $addr_elems[$j] = strtoupper($addr_elems[$j]);
                }
            }
            $addr2 = implode(" ", $addr_elems);
        }
        return $addr2;
	}

	//NYSS 3356/Jira 8077 - Post validation actions
	private static function fixStreetAddressParts( &$values )
	{
		//Can't fix it if there is no address
		if(empty($values['street_address']))
			return;

		// do street parsing again if enabled in the preferences, since street address
		// might have changed during the standardization with the USPS
        $address_options = CRM_Core_BAO_Preferences::valueOptions( 'address_options' );
		if ( CRM_Utils_Array::value('street_address_parsing', $address_options) ) {

            //Remove the values, in case the USPS got rid of them.
            foreach ( self::$address_components as $fld )
                unset( $values[$fld] );

            // main parse string.
            $parseString  = CRM_Utils_Array::value( 'street_address', $values );
            $parsedFields = CRM_Core_BAO_Address::parseStreetAddress( $parseString );

            // merge parse address in to main address block.
            $values = array_merge( $values, $parsedFields );
        }
    }
}
