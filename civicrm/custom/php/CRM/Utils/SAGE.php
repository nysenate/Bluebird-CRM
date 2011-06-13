<?php

require_once 'CRM/Core/BAO/Preferences.php';
require_once 'CRM/Core/Error.php';
require_once 'HTTP/Request.php';
require_once 'CRM/Core/BAO/Address.php';

// QQQ: should we find a new, unified, place to put the sage key?
class CRM_Utils_SAGE {

	public static $base = "http://sage.nysenate.gov/api/";

	public static function checkAddress( &$values ) {
		$session = CRM_Core_Session::singleton();

		// I'm 95% sure this isn't actually necessary, although catching
		// an absence of SAGE required fields would be good (add that below)
        // QQQ: Do we need to do all these checks isset() checks?
        //      I would think all values would always be set to either "" or
        //      The corresponding form value...??
        // QQQ: Should we be checking this in the lookup function as well?
    	if ( !isset($values['street_address']) ||
		     $values['city'] == null ||
             (!isset($values['city']) &&
              !isset($values['state_province']) &&
              !isset($values['postal_code'])) ) {
            return false;
        }

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
            $msg = "SAGE Warning: Postal lookup for [$addr2] has failed.\n";
            $session->setStatus(ts($msg));
            return false;
        }

        self::storeAddress($values,$xml,$addr_field);
        return true;
	}

	public static function format( &$values, $stateName=false ) {
		$session = CRM_Core_Session::singleton();

		// QQQ: Why is this the only place we do the state lookup?
        $stateProvince = self::getStateProvince($values,$stateName);
        list($addr_field,$addr2) = self::getAddress($values);

        //Construct and send the API Request. Note the service=geocoder.
        //Without it SAGE will default to Yahoo as the geocoding provider.
        //geocoder is the Senate's own geocoding provider, which uses the
        //open source "geocoder" project.
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
		    //QQQ: Why do we set these values to 'null' instead of ''?
			$values['geo_code_1'] = $values['geo_code_2'] = 'null';
			$msg = "SAGE Warning: Geocoding for [$params] has failed.\n";
            $session->setStatus(ts($msg));
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
		    $msg = "SAGE Warning: Distassign for [$params] has failed.\n";
            $session->setStatus(ts($msg));
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
		    $msg = "SAGE Warning: Lookup for [$params] has failed.\n";
            $session->setStatus(ts());
            return false;
        }

        //SAGE will let us know if USPS address validation has failed by
        //sending us a "simple" address from the geocoding source instead
        //of the "extended" USPS address
        if($xml->address->simple) {
            $msg = "SAGE Warning: USPS could not validate address: [$addr2]";
            $session->setStatus(ts($msg));
        } else {
            //Don't change imported addresses, assume they are correct as given
            $url_components = explode( '/', CRM_Utils_System::currentPath() );
            if ( $url_components[1] != 'import' )
                self::storeAddress($values, $xml->address->extended, $addr_field);
        }

        self::storeGeocodes($values, $xml);
        self::storeDistricts($values, $xml, $overwrite_districts);
        return true;
	}

	private static function validateResponse($xml) {
		//Fail silently if the XML response from SAGE was invalid
		//XML and could not be parsed into a simplexml object
		if (!$xml)
            return false;

        //SAGE reports invalid requests with the message object so
        //we treat it like an error flag on the reponse tree.
        if (!empty($xml->message)) {
            $session->setStatus(ts('SAGE Warning: '.$xml->message));
            return false;
        }

        return true;
	}

	private static function getAddress( $values ) {
	    //Historically there have been several address to store the address
	    //We need to return the address and the source field to store the
	    //corrected address back into the correct form field.
	    // QQQ: is supplmental_address_2 now depreciated?
	    $addr2_fields = array('street_address','supplemental_address_1');
		foreach($addr2_fields as $addr_field)
            if(CRM_Utils_Array::value($addr_field,$values))
                return array($addr_field,$values[$addr_field]);
        return array('street_address',"");
	}

	// QQQ: What is getStateProvince() doing and why?
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
		//Forced type cast required to convert the simplexml objects to strings
        $values['city'] = ucwords(strtolower((string)$xml->city));
        $values['state_province'] = (string)$xml->state;
        $values['postal_code'] = (string)$xml->zip5;
        $values['postal_code_suffix'] = (string)$xml->zip4;
        $values[$addr_field] = self::normalizeAddr2((string)$xml->address2, $values[$addr_field]);

        #Since standardization could change the street address, fix the parts
        self::fixStreetAddressParts($values);
	}

    private static function storeGeocodes( &$values, $xml ) {
        //Forced type cast required to convert the simplexml objects to strings
        $values['geo_code_1'] = (string)$xml->lat;
       	$values['geo_code_2'] = (string)$xml->lon;
    }

	private static function storeDistricts(&$values, $xml, $overwrite) {
        //The form includes the address primary key in the field name so we
        //must detect the address pk to store addresses in the right slots.
        //Get the pk from the form input names using the following method,
        //borrowed from CRM_Core_BAO_CustomField::getKeyId. We use -1 as the
	    //default id for all new addresses.
	    $id = -1;
		foreach( array_keys($values) as $key)
			if (preg_match('/^custom_(\d+)_?(-?\d+)?$/', $key, $match))
				$id = CRM_Utils_Array::value(2,$match,-1);

        //Write the SAGE values in as necessary. There are several instances,
        //see the nyss_sage module, where district should not be overwritten.
        //It is always the case that they should be filled in where blank.
        //Forced type cast required to convert the simplexml objects to strings
	    if($overwrite || !$values["custom_46_$id"])
	        $values["custom_46_$id"] = (string)$xml->congressional->district;
	    if($overwrite || !$values["custom_47_$id"])
	        $values["custom_47_$id"] = (string)$xml->senate->district;
	    if($overwrite || !$values["custom_48_$id"])
	        $values["custom_48_$id"] = (string)$xml->assembly->district;
	    if($overwrite || !$values["custom_49_$id"])
	        $values["custom_49_$id"] = (string)$xml->election->district;
	}

	private static function normalizeAddr2( $addr2, $old_addr2 )
	{
		//USPS returns ALLCAPS which is a bit hard on the eyes
		$addr2 = ucwords(strtolower($addr2));

		//Fix the PO Box which doesn't follow ucwords rules
        if (substr($addr2, 0, 6) == "Po Box") {
            $addr2 = "P.O. Box".substr($addr2, 6);

        //Fix Alphanumberic mixed address numbers to have capital letters
        //Omits numeric suffixes like 1st,2nd, etc. Fixes 19A, 12DC, etc.
	    } else {
            $addr2_parts = explode(" ", $addr2);
            foreach( $addr2_parts as $part) {
                //Allowing initial zero is okay because we're already corrected
                if (!preg_match("/^[0-9]*1st|3rd|2nd|[04-9]th$/", $part)) {
                    //pass
                } elseif (preg_match("/^[1-9][0-9a-zA-Z]+/", $part)) {
                    $part = strtoupper($part);
                }
            }
            $addr2 = implode(' ', $addr2_parts);
        }

        //NYSS 3800 - Retain original street number if alphanumerics match
        //    http://senatedev.nysenate.gov/issues/show/3800
        $regex = '/^[A-Za-z0-9]+([\S]+)/';
        if ( preg_match( $regex, $old_addr2, $matches ) )
            $street_number_in = $matches[0];

        if ( preg_match( $regex, $addr2, $matches ) )
            $street_number_out = $matches[0];

        if ( str_replace( '-', '', $street_number_in ) == $street_number_out )
            $addr2 = preg_replace( $regex, $street_number_in, $addr2 );

        return $addr2;
	}

	// JIRA 8077 - http://issues.civicrm.org/jira/browse/CRM-8077
	// NYSS 3356 - Fix the address after validating with SAGE
	//     http://senatedev.nysenate.gov/issues/show/3356
	private static function fixStreetAddressParts( &$values ) {
	    $addr2 = $values['street_address'];

		//Don't bother if there is no address to fix
		if(!$addr2) return;

		// If enabled in the preferences, replace the input address parts with
		// new parts parsed from the USPS corrected street address from SAGE
        $options = CRM_Core_BAO_Preferences::valueOptions( 'address_options' );
		if ( CRM_Utils_Array::value('street_address_parsing', $options) ) {

            //parseStreetAddress might be missing keys for some parts so wipe
            //all the parts out of the input and copy onto a clean slate
            foreach(array('street_number',
                    	  'street_name',
                    	  'street_unit',
                   	   	  'street_number_suffix') as $part ) {
                $values[$part] = "";
   	   	    }

            $addr2_parts = CRM_Core_BAO_Address::parseStreetAddress( $addr2 );
            $values = array_merge( $values, $addr2_parts );
        }
    }
}
