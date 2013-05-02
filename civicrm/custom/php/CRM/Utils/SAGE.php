<?php

require_once 'CRM/Core/BAO/Setting.php';
require_once 'CRM/Core/Error.php';
require_once 'HTTP/Request.php';
require_once 'CRM/Core/BAO/Address.php';
require_once 'CRM/Core/PseudoConstant.php';

define( 'MAX_STATUS_LEN', 200 ); //threshold length for status message

// QQQ: should we find a new, unified, place to put the sage key?
class CRM_Utils_SAGE
{

    private static function warn( $message ) {
        $session = CRM_Core_Session::singleton();
        $config = CRM_Core_Config::singleton();

        //for bulk actions, the status message is concatenated and could get quite long
        //so we will limit the length
        if (strlen($session->getStatus()) < MAX_STATUS_LEN) {

            // NYSS 5798 - Only show details in debug mode
            if ($config->debug) {
                $session->setStatus(ts("SAGE Warning: $message<br/>"));
            } else {
                $session->setStatus(ts("SAGE Warning: Address lookup failed.<br/>"));
            }
        }
    }

    public static function checkAddress( &$values )
    {
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

        //The address could be stored in a couple different places.
        //Get the address and remember where we found it for later
        list($addr_field, $addr) = self::getAddress($values);

        //SAGE throws back a cryptic warning if there is no address.
        //Check first and use our own more descriptive warning.
        if (!$addr) {
            self::warn('Not enough address info.');
            return false;
        }

        #Construct and send the API Request
        $url = '/xml/validate/extended?';
        $params = http_build_query( array(
            'addr2' => str_replace(',', '', $addr),
            'city' => CRM_Utils_Array::value('city', $values, ""),
            'zip5' => CRM_Utils_Array::value('postal_code', $values, ""),
            'state' => CRM_Utils_Array::value('state_province', $values, ""),
            'country' => CRM_Utils_Array::value('country', $values, ""),
            'key' => SAGE_API_KEY,
            ),'', '&');
        $request = new HTTP_Request(SAGE_API_BASE . $url . $params);
        $request->sendRequest();
        $xml = simplexml_load_string($request->getResponseBody());

        if (self::validateResponse($xml) === false) {
            self::warn("Postal lookup for [$addr] has failed.\n");
            return false;
        }

        self::storeAddress($values, $xml, $addr_field);
        return true;
    }

    public static function format( &$values, $stateName=false )
    {
        // QQQ: Why is this the only place we do the state lookup?
        $stateProvince = self::getStateProvince($values, $stateName);
        list($addr_field, $addr) = self::getAddress($values);

        //Construct and send the API Request. Note the service=geocoder.
        //Without it SAGE will default to Yahoo as the geocoding provider.
        //geocoder is the Senate's own geocoding provider, which uses the
        //open source "geocoder" project.
        $url = '/xml/geocode/extended?';
        $params = http_build_query(array(
                'service' => CRM_Utils_Array::value('service', $values, "rubygeocoder"),
                'addr2' => str_replace(',', '', $addr),
                'state' => $stateProvince,
                'city' => CRM_Utils_Array::value('city', $values, ""),
                'zip5' => CRM_Utils_Array::value('postal_code', $values, ""),
                'key' => SAGE_API_KEY,
            ), '', '&');
        $request = new HTTP_Request(SAGE_API_BASE . $url . $params);
        $request->sendRequest();
        $xml = simplexml_load_string($request->getResponseBody());

        if(!self::validateResponse($xml)) {
            //QQQ: Why do we set these values to 'null' instead of ''?
            $values['geo_code_1'] = $values['geo_code_2'] = 'null';
            self::warn("Geocoding for [$params] has failed.");
            return false;
        }

        self::storeGeocodes($values, $xml);
        return true;
    }



    public static function distassign( &$values, $overwrite_districts=true ) {
        //The address could be stored in a couple different places
        //get the address and remember where we found it for later
        list($addr_field, $addr) = self::getAddress($values);
        if (!$addr) {
            self::warn("Not enough address info.");
            return false;
        }

        #Construct and send the API Request
        $url = '/xml/districts/extended?nometa=1&';
        $params = http_build_query( array(
                'addr2' => str_replace(',', '', $addr),
                'city' => CRM_Utils_Array::value('city',$values,""),
                'zip5' => CRM_Utils_Array::value('postal_code',$values,""),
                'state' => CRM_Utils_Array::value('state_province',$values,""),
                'country' => CRM_Utils_Array::value('country', $values, ""),
                'key' => SAGE_API_KEY,
            ), '', '&');
        $request = new HTTP_Request(SAGE_API_BASE . $url . $params);
        $request->sendRequest();
        $xml = simplexml_load_string($request->getResponseBody());

        #Check the response for validity
        if(!self::validateResponse($xml)) {
            self::warn("Distassign for [$params] has failed.");
            return false;
        }

        self::storeDistricts($values,$xml,$overwrite_districts);
        return true;
    }

     public static function lookup_from_point( &$values, $overwrite_districts=true) {
        $url = '/xml/bluebirdDistricts/latlon/';

     	$url = $url.
     		CRM_Utils_Array::value('geo_code_1',$values,"").
     		",".
     		CRM_Utils_Array::value('geo_code_2',$values,"")
     		."?";

		$params = http_build_query(
			array(
				'key' => SAGE_API_KEY,
			), '', '&');

		$request = new HTTP_Request(SAGE_API_BASE . $url . $params);
		$request->sendRequest();
		$xml = simplexml_load_string($request->getResponseBody());

		if(!self::validateResponse($xml)) {
            self::warn("Lookup for [$params] has failed.");
			return false;
		}

		self::storeDistricts($values, $xml, $overwrite_districts);
        return true;
     }

    public static function lookup( &$values, $overwrite_districts=true, $overwrite_point=true) {
        //The address could be stored in a couple different places
        //get the address and remember where we found it for later
        list($addr_field, $addr) = self::getAddress($values);

        //If there is a state province id, set the value of the state province in the query
        //for SAGE.
        if (isset($values['state_province_id'])) {
            $values['state_province'] = CRM_Core_PseudoConstant::stateProvinceAbbreviation($values['state_province_id']);
        }
        if (!$addr) {
            self::warn("Not enough address info.");
            return false;
        }

        #Construct and send the API Request
        $url = '/xml/bluebirdDistricts/extended?';
        $params = http_build_query( array(
                'addr2' => str_replace(',', '', $addr),
                'city' => CRM_Utils_Array::value('city',$values,""),
                'zip5' => CRM_Utils_Array::value('postal_code',$values,""),
                'state' => CRM_Utils_Array::value('state_province',$values,""),
                'country' => CRM_Utils_Array::value('country', $values, ""),
                'key' => SAGE_API_KEY,
            ), '', '&');
        $request = new HTTP_Request(SAGE_API_BASE . $url . $params);
        $request->sendRequest();
        $xml = simplexml_load_string($request->getResponseBody());

        if(!self::validateResponse($xml)) {
            self::warn("Lookup for [$params] has failed.");
            return false;
        }

        //SAGE will let us know if USPS address validation has failed by
        //sending us a "simple" address from the geocoding source instead
        //of the "extended" USPS address
        if($xml->validated != "true") {
            self::warn("USPS could not validate address: [$addr]");
        } else {
            //Don't change imported addresses, assume they are correct as given
            $url_components = explode( '/', CRM_Utils_System::currentPath() );
            if (count($url_components) > 1 && $url_components[1] != 'import' )
                self::storeAddress($values, $xml, $addr_field);
        }

        if ($xml->geocoded == "true") {
            self::storeGeocodes($values, $xml, $overwrite_point);
        }
        if ($xml->distassigned == "true") {
            self::storeDistricts($values, $xml, $overwrite_districts);
        }
        return true;
    }



    private static function validateResponse($xml)
    {

        //Fail silently if the XML response from SAGE was invalid
        //XML and could not be parsed into a simplexml object
        if (!$xml)
            return false;

        //SAGE reports invalid requests with the message object so
        //we treat it like an error flag on the reponse tree.
        if (!empty($xml->message)) {
            return false;
        }

        return true;
    }



    private static function getAddress( $values )
    {
        //Historically there have been several fields to store the address.
        //We need to return the address and the source field to store the
        //corrected address back into the correct form field.
        $addr_fields = array('street_address', 'supplemental_address_1');
        foreach ($addr_fields as $addr_field) {
            if (CRM_Utils_Array::value($addr_field, $values)) {
                return array($addr_field, $values[$addr_field]);
            }
        }
        return array('street_address', "");
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



    private static function storeAddress( &$values, $xml, $addr_field )
    {
        //Forced type cast required to convert the simplexml objects to strings
        $values['city'] = ucwords(strtolower((string)$xml->city));
        $values['state_province'] = (string)$xml->state;
        $values['postal_code'] = (string)$xml->zip5;
        $values['postal_code_suffix'] = (string)$xml->zip4;
        $values[$addr_field] = self::normalizeAddr((string)$xml->address2, $values[$addr_field]);

        #Since standardization could change the street address, fix the parts
        self::fixStreetAddressParts($values);
    }



    private static function storeGeocodes( &$values, $xml, $overwrite = false)
    {
        //Forced type cast required to convert the simplexml objects to strings
        if($overwrite || !$values["geo_code_1"])
        	$values["geo_code_1"] = (string)$xml->lat;
        if($overwrite || !$values["geo_code_2"])
        	$values["geo_code_2"] = (string)$xml->lon;

    }



    private static function storeDistricts(&$values, $xml, $overwrite)
    {
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
        if($overwrite || !$values["custom_50_$id"])
            $values["custom_50_$id"] = (string)$xml->county->district;
        if($overwrite || !$values["custom_52_$id"])
            $values["custom_52_$id"] = (string)$xml->town->district;
        if($overwrite || !$values["custom_54_$id"])
            $values["custom_54_$id"] = (string)$xml->school->district;
    }



    private static function normalizeAddr($addr, $orig_addr)
    {
        //USPS returns ALLCAPS which is a bit hard on the eyes
        $addr = ucwords(strtolower($addr));

        //Fix the PO Box which doesn't follow ucwords rules
        if (substr($addr, 0, 6) == "Po Box") {
            $addr = "PO Box".substr($addr, 6);	// issue #4277
        }
        else {
            //Fix alphanumeric mixed address numbers to have capital letters.
            //Omits numeric suffixes like 1st, 2nd, etc. Fixes 19A, 12DC, etc.
            $addr_parts = explode(' ', $addr);
            foreach ($addr_parts as &$part) {
                //Allowing initial zero is ok because we're already corrected.
                if (preg_match('/^[0-9]+(st|nd|rd|th)$/', $part)) {
                    //pass
                }
                else if (preg_match('/^[1-9][0-9a-zA-Z]+/', $part)) {
                    $part = strtoupper($part);
                }
                else if (preg_match('/^Mc[a-z]/', $part)) {
                    // Capitalize the third letter in parts that begin with 'Mc'
                    $part = 'Mc'.ucfirst(substr($part, 2));  // issue #4276
                }
            }
            $addr = implode(' ', $addr_parts);
        }

        //NYSS 3800 - Retain original street number if alphanumerics match.
        //    http://senatedev.nysenate.gov/issues/show/3800
        $regex = '/^[\d][[:alnum:]]*\-?[[:alnum:]]+/';
        if (preg_match($regex, $orig_addr, $matches)) {
            $street_number_in = $matches[0];

            if (preg_match($regex, $addr, $matches))
                $street_number_out = $matches[0];

            if (str_replace('-', '', $street_number_in) == $street_number_out)
                $addr = preg_replace($regex, $street_number_in, $addr);
        }

        return $addr;
    }



    // JIRA 8077 - http://issues.civicrm.org/jira/browse/CRM-8077
    // NYSS 3356 - Fix the address after validating with SAGE
    //     http://senatedev.nysenate.gov/issues/show/3356
    private static function fixStreetAddressParts( &$values )
    {
        $addr = $values['street_address'];

        //Don't bother if there is no address to fix
        if(!$addr) return;

        // If enabled in the preferences, replace the input address parts with
        // new parts parsed from the USPS corrected street address from SAGE
        $options = CRM_Core_BAO_Setting::valueOptions(CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,'address_options');
        if ( CRM_Utils_Array::value('street_address_parsing', $options) ) {

            //parseStreetAddress might be missing keys for some parts so wipe
            //all the parts out of the input and copy onto a clean slate
            foreach(array('street_number',
                          'street_name',
                          'street_unit',
                          'street_number_suffix') as $part ) {
                $values[$part] = "";
            }

            $addr_parts = CRM_Core_BAO_Address::parseStreetAddress( $addr );
            $values = array_merge( $values, $addr_parts );
        }
    }
}

class CRM_Utils_Address_SAGE extends CRM_Utils_SAGE {};
class CRM_Utils_Geocode_SAGE extends CRM_Utils_SAGE {};
