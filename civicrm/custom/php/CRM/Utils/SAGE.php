<?php

require_once 'CRM/Core/BAO/Setting.php';
require_once 'CRM/Core/Error.php';
require_once 'HTTP/Request.php';
require_once 'CRM/Core/BAO/Address.php';
require_once 'CRM/Core/PseudoConstant.php';

define('MAX_STATUS_LEN', 200); //threshold length for status message

/**
* Utility class to handle SAGE requests and responses.
*/
class CRM_Utils_SAGE
{
  /**
  * Produces a SAGE warning.
  * @param string $message  Error message. 
  */
  private static function warn($message)
  {
    $session = CRM_Core_Session::singleton();
    $config = CRM_Core_Config::singleton();

    // Limit the length of the status message.
    //NYSS 7340
    //TODO setStatus doesn't trigger the js warning message as expected, when triggered via inline;
    //disable for now, but investigate ways to handle that better
    if ( CRM_Utils_Array::value('class_name', $_REQUEST, '') != 'CRM_Contact_Form_Inline_Address' &&
      strlen($session->getStatus()) < MAX_STATUS_LEN
    ) {
      // NYSS 5798 - Only show details in debug mode
      if ($config->debug) {
        $session->setStatus(ts("SAGE Warning: $message<br/>"));
      }
      else {
        $session->setStatus(ts("SAGE Warning: Address lookup failed.<br/>"));
      }
    }
  } // warn()


  /**
  * Performs USPS validation. If the address was validated, it will be stored in {$values}.
  *
  * @param array &$values  Array representing address/geocode/district values.
  * @return true if response validated successfully. 
  */
  public static function checkAddress(&$values)
  {
    list($addr_field, $addr) = self::getAddress($values);

    if (!$addr) {
      self::warn('Not enough address info.');
      return false;
    }

    // Construct and send the API Request
    $url = '/address/validate?format=xml&provider=usps&';
    $params = http_build_query( array(
      'addr1' => str_replace(',', '', $addr),
      'city' => CRM_Utils_Array::value('city', $values, ""),
      'zip5' => CRM_Utils_Array::value('postal_code', $values, ""),
      'state' => CRM_Utils_Array::value('state_province', $values, ""),
      'key' => SAGE_API_KEY,
      ),'', '&');

    $url = SAGE_API_BASE . $url . $params;
    $request = new HTTP_Request($url);
    $request->sendRequest();
    $xml = simplexml_load_string($request->getResponseBody());

    if (self::validateResponse($xml) && $xml->validated == 'true') {
      self::storeAddress($values, $xml, $addr_field);
      return true;
    }
    else {
      self::warn("Postal lookup for [$addr] has failed.\n");
      return false;
    }
  } // checkAddress()
 
 
  /**
  * Performs batch usps validation. Validated addresses are overwritten in {$rows}.
  *
  * @param array &$rows  An array of rows that each contain address columns. 
  * @return 
  */
  public static function batchCheckAddress(&$rows)
  {
    $addresses = self::getAddressesFromRows($rows);

    $url = '/address/validate/batch?format=xml&provider=usps&';
    $params = http_build_query(array(
      'key' => SAGE_API_KEY
      ), '', '&');
    $url = SAGE_API_BASE . $url . $params;
    $request = new HTTP_Request($url);
    $request->addRawPostData(json_encode($addresses));
    $request->sendRequest();
    $batchXml = simplexml_load_string($request->getResponseBody());

    if ($batchXml && $batchXml->total == count($addresses)) {
      for ($i = 0; $i < $batchXml->results->results->count(); $i++) {
        $xml = $batchXml->results->results[$i];
        if (self::validateResponse($xml) && $xml->validated == 'true') {
          list($addr_field, $addr) = self::getAddress($rows[$i]);
          self::storeAddress($rows[$i], $xml, $addr_field);
        }
      }
      return true;
    }
    return false;
  } // batchCheckAddress()


  /**
  * Performs geocoding by address and stores the geocodes in {$values}
  *
  * @param array &$values  Array representing address/geocode/district values.
  * @return true if response validated successfully. 
  */
  public static function geocode(&$values)
  {
    list($addr_field, $addr) = self::getAddress($values);

    // Construct and send the geocode API Request. 
    $url = '/geo/geocode?format=xml&';
    $params = http_build_query(array(
        'addr1' => str_replace(',', '', $addr),
        'city' => CRM_Utils_Array::value('city', $values, ""),
        'state' => CRM_Utils_Array::value('state_province', $values, ""),
        'zip5' => CRM_Utils_Array::value('postal_code', $values, ""),
        'key' => SAGE_API_KEY,
      ), '', '&');

    $url = SAGE_API_BASE . $url . $params;
    $request = new HTTP_Request($url);
    $request->sendRequest();
    $xml = simplexml_load_string($request->getResponseBody());

    if (self::validateResponse($xml) && $xml->geocoded == 'true') {
      self::storeGeocodes($values, $xml); 
    }
    else {
      //QQQ: Why do we set these values to 'null' instead of ''?
      $values['geo_code_1'] = $values['geo_code_2'] = 'null';
      self::warn("Geocoding for [$params] has failed.");
      return false;
    }
    return true;
  } // geocode()


  /**
  * Performs batch geocoding of addresses and stores the geocodes in {$rows}.
  *
  * @param array   &$rows  An array of rows that each contain an array with address and geocode columms.
  * @param boolean $overwrite_point  If true, geocode will be written by default to {$rows}
  */
  public static function batchGeocode(&$rows, $overwrite_point=true) 
  {
    $addresses = self::getAddressesFromRows($rows);

    $url = '/geo/geocode/batch?format=xml&';
    $params = http_build_query(array(
      'key' => SAGE_API_KEY
      ), '', '&');
    $url = SAGE_API_BASE . $url . $params;
    $request = new HTTP_Request($url);
    $request->addRawPostData(json_encode($addresses));
    $request->sendRequest();
    $batchXml = simplexml_load_string($request->getResponseBody());

    if ($batchXml && $batchXml->total == count($addresses)) {
      for ($i = 0; $i < $batchXml->results->results->count(); $i++) {
        $xml = $batchXml->results->results[$i];
        if (self::validateResponse($xml) && $xml->geocoded == 'true') {
          self::storeGeocodes($rows[$i], $xml, $overwrite_point);
        }
      }
      return true;
    }
    return false;
  } // batchGeocode()


  /**
  * Performs district assign by address and sets the district information to {$values}.
  *
  * @param array &$values An array representing address and district values.
  * @param boolean $overwrite_districts  If true, districts will be written by default to {$values}.
  * @param boolean $overwrite_point      If true, geocode will be written by default to {$values}
  * @param boolean $streetfile_only      If true, only streetfile lookup will be used for district assign
  */
  public static function distAssign(&$values, $overwrite_districts=true, $overwrite_point=true, $streetfile_only=false)
  {
    list($addr_field, $addr) = self::getAddress($values);
    if (!$addr) {
      self::warn("Not enough address info.");
      return false;
    }

    // Construct and send the API Request
    $url = '/district/assign?format=xml&';
    $params = array(
      'addr1' => str_replace(',', '', $addr),
      'city' => CRM_Utils_Array::value('city',$values,""),
      'zip5' => CRM_Utils_Array::value('postal_code',$values,""),
      'state' => CRM_Utils_Array::value('state_province',$values,""),
      'key' => SAGE_API_KEY,
    );

    if ($streetfile_only) {
      $params['districtStrategy'] = 'streetOnly';
    }

    $params = http_build_query($params, '', '&');
    $url = SAGE_API_BASE . $url . $params;
    $request = new HTTP_Request($url);
    $request->sendRequest();
    $xml = simplexml_load_string($request->getResponseBody());

    // Check the response for validity
    if (self::validateResponse($xml)) {
      if ($xml->districtAssigned == 'true') {
        self::storeDistricts($values, $xml, $overwrite_districts);
      }
      if ($xml->geocoded == 'true') {
        self::storeGeocodes($values, $xml, $overwrite_point);
      }
      return true;
    }
    else {
      self::warn("Distassign for [$params] has failed.");
      return false;
    }
  } // distAssign()


  /**
  * Performs batch district assignment of addresses and stores the districts in {$rows}.
  *
  * @param array   &$rows  An array of rows that each contain an array with address and district columms.
  * @param boolean $overwrite_districts  If true, districts will be written by default to {$rows}
  * @param boolean $overwrite_point      If true, geocode will be written by default to {$rows}
  * @param boolean $streetfile_only      If true, only streetfile lookup will be used for district assign
  */
  public static function batchDistAssign(&$rows, $overwrite_districts=true, $overwrite_point=true, $streetfile_only=false)
  {
    $addresses = self::getAddressesFromRows($rows);

    $url = '/district/assign/batch?format=xml&';
    $params = array(
      'key' => SAGE_API_KEY,
      'districtStrategy' => ($streetfile_only) ? 'streetOnly' : 'streetFallback'
    );

    $params = http_build_query($params, '', '&');
    $url = SAGE_API_BASE . $url . $params;
    $request = new HTTP_Request($url);
    $request->addRawPostData(json_encode($addresses));
    $request->sendRequest();
    $batchXml = simplexml_load_string($request->getResponseBody());

    if ($batchXml && $batchXml->total == count($addresses)) {
      for ($i = 0; $i < $batchXml->results->results->count(); $i++) {
        $xml = $batchXml->results->results[$i];
        if (self::validateResponse($xml)) {
          if ($xml->districtAssigned == 'true') {
            self::storeDistricts($rows[$i], $xml, $overwrite_districts);
          }
          if ($xml->geocoded == 'true') {
            self::storeGeocodes($rows[$i], $xml, $overwrite_point);
          }
        }
      }
      return true;
    }
    return false;
  } // batchDistAssign()


  /**
  * Performs a bluebird lookup by point and assigns district information to {$values}
  *
  * @param array &$values Array representing address/geocode/district values.
  * @param boolean $overwrite_districts  If true, districts will be written by default to {$values}.
  * @return true if response validated successfully, false otherwise.
  */
  public static function lookupFromPoint(&$values, $overwrite_districts=true)
  {
    $url = '/district/bluebird?format=xml';

    $params = http_build_query(array(
        'key' => SAGE_API_KEY,
        'lat' => CRM_Utils_Array::value('geo_code_1', $values, ""),
        'lon' => CRM_Utils_Array::value('geo_code_2', $values, "")
      ), '', '&');

    $url = SAGE_API_BASE . $url . $params;
    $request = new HTTP_Request($url);
    $request->sendRequest();
    $xml = simplexml_load_string($request->getResponseBody());

    if (self::validateResponse($xml)) {
      self::storeDistricts($values, $xml, $overwrite_districts);
      return true;
    }
    else {
      self::warn("Lookup for [$params] has failed.");
      return false;
    }
  } // lookupFromPoint()


  /**
  * Performs a bluebird lookup by point and assigns district information to {$rows}.
  *
  * @param array   &$rows  An array of rows that each contain an array point columms.
  * @param boolean $overwrite_districts  If true, districts will be written by default to {$rows}.
  */
  public static function batchLookupFromPoint(&$rows, $overwrite_districts=true)
  {
    $points = self::getPointsFromRows($rows);

    $url = '/district/assign/batch?format=xml&';
    $params = http_build_query(array(
        'key' => SAGE_API_KEY
      ), '', '&');

    $url = SAGE_API_BASE . $url . $params;
    $request = new HTTP_Request($url);
    $request->addRawPostData(json_encode($points));
    $request->sendRequest();
    $batchXml = simplexml_load_string($request->getResponseBody());

    if ($batchXml && $batchXml->total == count($points)) {
      for ($i = 0; $i < $batchXml->results->results->count(); $i++) {
        $xml = $batchXml->results->results[$i];
        if (self::validateResponse($xml) && $xml->districtAssigned == 'true') {
          self::storeDistricts($rows[$i], $xml, $overwrite_districts);
        }
      }
      return true;
    }
    return false;
  } // batchLookupFromPoint()


  /**
  * Performs a bluebird lookup by address and assigns geocode and district information to {$values}.
  *
  * @param array   &$values              An array representing address, geocode, and district values.
  * @param boolean $overwrite_districts  If true, districts will be written by default to {$values}.
  * @param boolean $overwrite_point      If true, geocode will be written by default to {$values}
  * @return true if response validated successfully, false otherwise.
  */
  public static function lookup(&$values, $overwrite_districts=true, $overwrite_point=true)
  {
    list($addr_field, $addr) = self::getAddress($values);
    if (!$addr) {
      self::warn("Not enough address info.");
      return false;
    }

    // If there is a state/province id, set the value of the state/province.
    if (isset($values['state_province_id'])) {
      $values['state_province'] = CRM_Core_PseudoConstant::stateProvinceAbbreviation($values['state_province_id']);
    }

    // Construct and send the Bluebird API request.
    $url = '/district/bluebird?format=xml&';
    $params = http_build_query(array(
        'addr1' => str_replace(',', '', $addr),
        'city' => CRM_Utils_Array::value('city', $values, ""),
        'state' => CRM_Utils_Array::value('state_province', $values, ""),
        'zip5' => CRM_Utils_Array::value('postal_code', $values, ""),
        'key' => SAGE_API_KEY,
      ), '', '&');

    $url = SAGE_API_BASE . $url . $params;

    //7414 wrap in ignoreException so we don't fatal if the lookup fails
    $errorScope = CRM_Core_TemporaryErrorScope::ignoreException();
    $request = new HTTP_Request($url);
    $request->sendRequest();
    $xml = simplexml_load_string($request->getResponseBody());
    unset($errorScope);

    if (!self::validateResponse($xml)) {
      self::warn("Lookup for [$params] has failed.");
      return false;
    }

    if ($xml->uspsValidated != 'true') {
      self::warn("USPS could not validate address: [$addr]");
    }
    else {
      // Don't change imported addresses, assume they are correct as given.
      $url_components = explode( '/', CRM_Utils_System::currentPath() );
      if (count($url_components) > 1 && $url_components[1] != 'import')
        self::storeAddress($values, $xml, $addr_field);
    }

    if ($xml->geocoded == 'true') {
      self::storeGeocodes($values, $xml, $overwrite_point);
    }
    if ($xml->districtAssigned == 'true') {
      self::storeDistricts($values, $xml, $overwrite_districts);
    }
    return true;
  } // lookup()


  /**
  * Performs a batch bluebird lookup by address and assigns district and geocode information to 
  * each entry in {$rows}.
  *
  * @param array &$rows  An array of rows that each contain an array with address, geocode, 
  *                      and district columms. Basically an array of {$values} used in the 
  *                      non-batch form of this function.
  * @param boolean $overwrite_districts  If true, districts written to each row by default.
  * @param boolean $overwrite_point      If true, geocodes written to each row by default.
  * @return true if response was valid, false otherwise.
  */
  public static function batchLookup(&$rows, $overwrite_districts=true, $overwrite_point=true)
  {
    $addresses = self::getAddressesFromRows($rows);
    $url = '/district/bluebird/batch?format=xml&';
    $params = http_build_query(array(
        'key' => SAGE_API_KEY
      ), '', '&');

    $url = SAGE_API_BASE . $url . $params;
    $request = new HTTP_Request($url);
    $request->addRawPostData(json_encode($addresses));
    $request->sendRequest();
    $batchXml = simplexml_load_string($request->getResponseBody());
 
    if ($batchXml && $batchXml->total == count($addresses)) {
      for ($i = 0; $i < $batchXml->results->results->count(); $i++) {
        $xml = $batchXml->results->results[$i];
        if (self::validateResponse($xml)) {
          if ($xml->uspsValidated == 'true') {
            list($addr_field, $addr) = self::getAddress($rows[$i]);
            self::storeAddress($rows[$i], $xml, $addr_field);
          }
          if ($xml->geocoded == 'true') {
            self::storeGeocodes($rows[$i], $xml, $overwrite_point);
          }
          if ($xml->districtAssigned == 'true') {
            self::storeDistricts($rows[$i], $xml, $overwrite_districts);
          }
        }
      }
      return true;
    }
    return false;
  } // batchLookup()


  /**
  * Returns an array of addresses using data in the supplied {$rows}.
  *
  * @param array &$rows  An array of rows that each contain an array with address columns.
  * @return array containing arrays of (addr1, city, state, zip5). 
  */
  protected static function getAddressesFromRows(&$rows)
  {
    $addresses = array();
    foreach ($rows as $row) {
      list($addr_field, $addr) = self::getAddress($row);
      if (isset($row['state_province_id'])) {
        $row['state_province'] = CRM_Core_PseudoConstant::stateProvinceAbbreviation($row['state_province_id']);
      }
 
      $address = array(
        'addr1' => str_replace(',', '', $addr),
        'city'  => CRM_Utils_Array::value('city', $row, ""),
        'state' => CRM_Utils_Array::value('state_province', $row, ""),
        'zip5'  => CRM_Utils_Array::value('postal_code', $row, "")
      );

      $addresses[] = $address;
    }
    return $addresses;
  } // getAddressesFromRows()


  /**
  * Returns an array of latlng points using data in the supplied {$rows}.
  *
  * @param array $rows  An array of rows that each contain geocode columns.
  * @return array containing arrays of (lat, lon).
  */
  protected static function getPointsFromRows(&$rows)
  {
    $points = array();
    foreach($rows as $row) {
      $points[] = array(
        'lat' => CRM_Utils_Array::value('geo_code_1', $row, ""),
        'lon' => CRM_Utils_Array::value('geo_code_2', $row, "") 
      );
    }
    return $points;
  } // getPointsFromRows()


  /**
  * Fail silently if the XML response from SAGE was invalid and could not
  * be parsed into a simplexml object, or if it was parsed but returned
  * a non-zero statusCode.
  * 
  * @param $xml
  * @return true if validated, false otherwise.
  */
  protected static function validateResponse($xml)
  {
    if (!$xml || $xml->statusCode != 0) {
      return false;
    }
    else {
      return true;
    }
  } // validateResponse()


  /** 
  * Historically there have been several fields to store the address.
  * We need to return the address and the source field to store the
  * corrected address back into the correct form field.
  *
  * @param $values
  * @return array containing (column name, street address)
  */
  protected static function getAddress($values)
  {
    $addr_fields = array('street_address', 'supplemental_address_1');
    foreach ($addr_fields as $addr_field) {
      if (CRM_Utils_Array::value($addr_field, $values)) {
        return array($addr_field, $values[$addr_field]);
      }
    }
    return array('street_address', "");
  } // getAddress()


  /**
  * Sets the address column entries within the supplied {$values} array using the 
  * SAGE xml response {$xml}. In addition, the street parts are parsed and stored as well.
  *
  * @param array     &$values     An array representing address, geocode, and district values
  * @param simplexml $xml         SimpleXml object containing SAGE xml response.
  * @param string    $addr_field  Column name where original street address was obtained.
  */
  protected static function storeAddress(&$values, $xml, $addr_field)
  {
    //Forced type cast required to convert the simplexml objects to strings
    $values['city'] = ucwords(strtolower((string)$xml->address->city));
    $values['state_province'] = (string)$xml->address->state;
    $values['postal_code'] = (string)$xml->address->zip5;
    $values['postal_code_suffix'] = (string)$xml->address->zip4;
    $values[$addr_field] = self::normalizeAddr((string)$xml->address->addr1, $values[$addr_field]);

    // Since standardization could change the street address, fix the parts
    self::fixStreetAddressParts($values);
  } // storeAddress()


  /**
  * Sets geocode column entries within the supplied {$values} array using the 
  * SAGE xml response {$xml}.
  *
  * @param array     &$values     An array representing address, geocode, and district values
  * @param simplexml $xml         SimpleXml object containing SAGE xml response.
  * @param boolean   $overwrite   If true, geocode data is written by default.
  */
  protected static function storeGeocodes(&$values, $xml, $overwrite = false)
  {
    //Forced type cast required to convert the simplexml objects to strings
    if ($overwrite || empty($values["geo_code_1"]) || !$values["geo_code_1"]) {
     $values["geo_method"] = (string)$xml->geocode->method;
     $values["geo_code_1"] = (string)$xml->geocode->lat;
    }
    if ($overwrite || empty($values["geo_code_2"]) || !$values["geo_code_2"]) {
     $values["geo_code_2"] = (string)$xml->geocode->lon;
    }
  } // storeGeocodes()


  /**
  * Sets district column entries within the supplied {$values} array using the 
  * SAGE xml response {$xml}.
  * 
  * @param array     &$values    An array representing address, geocode, and district values
  * @param simplexml $xml        SimpleXml object containing SAGE xml response.
  * @param boolean   $overwrite  If true, district data is written by default.
  */
  protected static function storeDistricts(&$values, $xml, $overwrite)
  {
    // The form includes the address primary key in the field name so we
    // must detect the address pk to store addresses in the right slots.
    // Get the pk from the form input names using the following method,
    // borrowed from CRM_Core_BAO_CustomField::getKeyId. We use -1 as the
    // default id for all new addresses.
    $id = -1;
    foreach (array_keys($values) as $key) {
      if (preg_match('/^custom_(\d+)_?(-?\d+)?$/', $key, $match)) {
        $id = CRM_Utils_Array::value(2, $match, -1);
      }
    }

    // Write the SAGE values in as necessary. There are several instances,
    // see the nyss_sage module, where district should not be overwritten.
    // It is always the case that they should be filled in where blank.
    // Forced type cast required to convert the simplexml objects to strings
    if ($overwrite || empty($values["custom_46_$id"]) || !$values["custom_46_$id"]) {
      $values["custom_46_$id"] = (string)$xml->districts->congressional->district;
    }
    if ($overwrite || empty($values["custom_47_$id"]) || !$values["custom_47_$id"]) {
      $values["custom_47_$id"] = (string)$xml->districts->senate->district;
    }
    if ($overwrite || empty($values["custom_48_$id"]) || !$values["custom_48_$id"]) {
      $values["custom_48_$id"] = (string)$xml->districts->assembly->district;
    }
    if ($overwrite || empty($values["custom_49_$id"]) || !$values["custom_49_$id"]) {
      $values["custom_49_$id"] = (string)$xml->districts->election->district;
    }
    if ($overwrite || empty($values["custom_50_$id"]) || !$values["custom_50_$id"]) {
      $values["custom_50_$id"] = (string)$xml->districts->county->district;
    }
    if ($overwrite || empty($values["custom_52_$id"]) || !$values["custom_52_$id"]) {
      $values["custom_52_$id"] = (string)$xml->districts->town->district;
    }
    if ($overwrite || empty($values["custom_54_$id"]) || !$values["custom_54_$id"]) {
      $values["custom_54_$id"] = (string)$xml->districts->school->district;
    }
  } // storeDistricts()


  /**
  * Applies normalizations to address line 1.
  *
  * @param string $addr       String containing the validated address line 1 value.
  * @param string $orig_addr  String containing the original address line 1 value. 
  */
  private static function normalizeAddr($addr, $orig_addr)
  {
    //Fix the PO Box which doesn't follow ucwords() rules
    if (substr($addr, 0, 6) == "Po Box") {
      $addr = "PO Box".substr($addr, 6); // issue #4277
    }
    else {
      // Fix alphanumeric mixed address numbers to have capital letters.
      // Omits numeric suffixes like 1st, 2nd, etc. Fixes 19A, 12DC, etc.
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
          $part = 'Mc'.ucfirst(substr($part, 2)); // issue #4276
        }
      }
      $addr = implode(' ', $addr_parts);
    }

    // NYSS 3800 - Retain original street number if alphanumerics match.
    // http://senatedev.nysenate.gov/issues/show/3800
    $regex = '/^[\d][[:alnum:]]*\-?[[:alnum:]]+/';
    if (preg_match($regex, $orig_addr, $matches)) {
      $street_number_in = $matches[0];

      if (preg_match($regex, $addr, $matches))
        $street_number_out = $matches[0];

      if (str_replace('-', '', $street_number_in) == $street_number_out)
        $addr = preg_replace($regex, $street_number_in, $addr);
    }

    return $addr;
  } // normalizeAddr()


  /**
  * If enabled in the preferences, replace the input address parts with
  * new parts parsed from the USPS corrected street address from SAGE
  *
  * JIRA 8077 - http://issues.civicrm.org/jira/browse/CRM-8077
  * NYSS 3356 - Fix the address after validating with SAGE
  * http://senatedev.nysenate.gov/issues/show/3356
  *
  * @param &$values - An array representing address, geocode, and district values
  */
  private static function fixStreetAddressParts(&$values)
  {
    $addr = $values['street_address'];

    // Don't bother if there is no address to fix
    if (!$addr) {
      return;
    }

    $options = CRM_Core_BAO_Setting::valueOptions(CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME, 'address_options');
    if (CRM_Utils_Array::value('street_address_parsing', $options)) {
      //parseStreetAddress might be missing keys for some parts so wipe
      //all the parts out of the input and copy onto a clean slate
      foreach (array('street_number', 'street_name',
                     'street_unit', 'street_number_suffix') as $part) {
        $values[$part] = "";
      }

      $addr_parts = CRM_Core_BAO_Address::parseStreetAddress($addr);
      $values = array_merge($values, $addr_parts);
    }
  } // fixStreetAddressParts()


  /**
   * wrapper function to retain compatibility with expected methods in core
   */
  public static function format(&$values, $stateName = false)
  {
    self::geocode($values);
  } // format()
}
