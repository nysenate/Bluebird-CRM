<?php

/**
* Utility class to handle SAGE requests and responses.
*/
class CRM_Utils_SAGE
{
  /**
  * Produces a SAGE warning.
  * @param string $message  Error message. 
  */
  private static function warn(string $message) {
    $session = CRM_Core_Session::singleton();
    $config = CRM_Core_Config::singleton();

    // Limit the length of the status message.
    //NYSS 7340
    //TODO setStatus doesn't trigger the js warning message when triggered via inline;
    //disable for now, but investigate ways to handle that better
    if (CRM_Utils_Array::value('class_name', $_REQUEST, '') != 'CRM_Contact_Form_Inline_Address') {
      // NYSS 5798 - Only show details in debug mode
      if ($config->debug) {
        $session->setStatus(ts("SAGE Warning: $message<br/>"));
      }
      else {
        $session->setStatus(ts("SAGE Warning: Address lookup failed.<br/>"));
      }
    }
  }

  /**
   * Builds the full URL with query string for a SAGE call.  This adds the
   * 'format' and 'key' parameters, if they are not present.
   *
   * @param string $url The resource path to call.
   * @param array $params An array of query string parameters to include.
   *
   * @return string The full URL with query string
   */
  protected static function buildSAGEUrl($url, $params) {
    // Add format and key params, if they aren't there.
    $params += ['format' => 'xml', 'key' => SAGE_API_KEY];

    return SAGE_API_BASE . '/' . trim($url, '/') . '?' . http_build_query($params, '', '&');
  }

  /**
   * Calls SAGE using a GET request.  Returns the XML object described
   * in the response.  The response is also cached, and can be refreshed
   * with the second parameter.
   *
   * @param string $url The target URL, e.g., '/geo/geocode'
   * @param array $params Address parameters to use in the request's query string
   * @param false $refresh If a new call should be forced
   *
   * @return \SimpleXMLElement
   */
  public static function callSAGE(string $url, array $params, $refresh = FALSE): SimpleXMLElement {
    static $cache = [];

    // Build the URL, which will also be the cache key.
    $cache_key = self::buildSAGEUrl($url, $params);

    // If the address is not cached, or if refresh is true,
    // call SAGE and cache the result.
    if (!($cache[$cache_key] ?? FALSE) || $refresh) {
      if (CRM_Core_Config::singleton()->debug) {
        CRM_Core_Session::singleton()
          ->setStatus("SAGE Request: $cache_key");
      }
      $request = new \GuzzleHttp\Client();
      $cache[$cache_key] = simplexml_load_string($request->get($cache_key)->getBody());
    }

    return $cache[$cache_key];
  }

  /**
   * Calls SAGE using a POST request.  Returns the XML object described
   * in the response.
   *
   * This adds the 'format' and 'key' fields to params if they are
   * not present.
   *
   * @param string $url The url to call, e.g., '/geo/geocode/batch'
   * @param array $params The query string parameters to use
   * @param string $data The POST body
   *
   * @return \SimpleXMLElement
   */
  public static function callSAGEPost(string $url, array $params, $data = ''): SimpleXMLElement {
    $full_url = self::buildSAGEUrl($url, $params);

    // Make the call, using $data for the POST body.
    $client = new \GuzzleHttp\Client();
    try {
      $r = $client
        ->request('POST', $full_url, ['body' => $data])
        ->getBody();
    }
    catch (\GuzzleHttp\Exception\GuzzleException $e) {
      $r = '';
    }

    return simplexml_load_string($r);
  }

  /**
   * Performs USPS validation. If the address was validated, it will be stored in {$values}.
   *
   * @param array &$values Array representing address/geocode/district values.
   *
   * @return boolean if response validated successfully.
   */
  public static function checkAddress(array &$values): bool {
    [$addr_field, $addr] = self::getAddress($values);

    if (!$addr) {
      self::warn('Not enough address info.');
      return false;
    }

    // Build the params, and make the request.
    $params = [
      'provider' => 'usps',
      'addr1' => str_replace(',', '', $addr),
      'city' => CRM_Utils_Array::value('city', $values, ""),
      'zip5' => CRM_Utils_Array::value('postal_code', $values, ""),
      'state' => CRM_Utils_Array::value('state_province', $values, ""),
    ];
    $xml = self::callSAGE('/address/validate', $params);

    // Is XML good, and validated is true?
    $ret = self::validateResponse($xml) && $xml->validated == 'true';

    // Yes, store the result
    if ($ret) {
      self::storeAddress($values, $xml, $addr_field);
    }
    // No, post a warning
    else {
      self::warn("Postal lookup for [$addr] has failed.\n");
    }

    return $ret;
  }


  /**
   * Performs batch usps validation. Validated addresses are overwritten in {$rows}.
   *
   * @param array &$rows An array of rows that each contain address columns.
   *
   * @return boolean
   */
  public static function batchCheckAddress(array &$rows): bool {
    $addresses = self::getAddressesFromRows($rows);

    $url = '/address/validate/batch';
    $params = ['provider' => 'usps'];
    $batchXml = self::callSAGEPost($url, $params, json_encode($addresses));

    if (($batchXml instanceof SimpleXMLElement) && $batchXml->total == count($addresses)) {
      for ($i = 0; $i < $batchXml->results->results->count(); $i++) {
        $xml = $batchXml->results->results[$i];
        if (self::validateResponse($xml) && $xml->validated == 'true') {
          [$addr_field, $addr] = self::getAddress($rows[$i]);
          self::storeAddress($rows[$i], $xml, $addr_field);
        }
      }
      return true;
    }
    return false;
  }

  /**
  * Performs geocoding by address and stores the geocodes in {$values}
  *
  * @param array &$values  Array representing address/geocode/district values.
  * @return boolean if response validated successfully.
  */
  public static function geocode(&$values): bool {
    [$addr_field, $addr] = self::getAddress($values);

    // Generate a cache key from the address parameters.
    $params = [
      'addr1' => str_replace(',', '', $addr),
      'city' => CRM_Utils_Array::value('city', $values, ""),
      'state' => CRM_Utils_Array::value('state_province', $values, ""),
      'zip5' => CRM_Utils_Array::value('postal_code', $values, ""),
    ];

    $xml = self::callSAGE('/geo/geocode', $params);

    // Is XML good, and validated is true?
    $ret = self::validateResponse($xml) && $xml->geocoded == 'true';

    // Yes, store the result
    if ($ret) {
      self::storeGeocodes($values, $xml);
    }
    // No, post a warning
    else {
      //QQQ: Why do we set these values to 'null' instead of ''?
      $values['geo_code_1'] = $values['geo_code_2'] = 'null';
      self::warn("Geocoding for [$params] has failed.");
    }

    return $ret;
  }


  /**
   * Performs batch geocoding of addresses and stores the geocodes in {$rows}.
   *
   * @param array   &$rows An array of rows that each contain an array with
   *   address and geocode columms.
   * @param boolean $overwrite_point If true, geocode will be written by
   *   default to {$rows}
   *
   * @return bool
   */
  public static function batchGeocode(array &$rows, $overwrite_point=true): bool {
    $addresses = self::getAddressesFromRows($rows);

    $url = '/geo/geocode/batch';
    $batchXml = self::callSAGEPost($url, [], json_encode($addresses));

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
  }


  /**
   * Performs district assign by address and sets the district information to
   * {$values}.
   *
   * @param array &$values An array representing address and district values.
   * @param boolean $overwrite_districts If true, districts will be written by
   *   default to {$values}.
   * @param boolean $overwrite_point If true, geocode will be written by
   *   default to {$values}
   * @param boolean $streetfile_only If true, only streetfile lookup will be
   *   used for district assign
   *
   * @return bool
   */
  public static function distAssign(array &$values, $overwrite_districts = TRUE, $overwrite_point = TRUE, $streetfile_only = FALSE): bool {
    [$addr_field, $addr] = self::getAddress($values);
    if (!$addr) {
      self::warn("Not enough address info.");
      return false;
    }

    // Construct and send the API Request
    $url = '/district/assign';
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

    $xml = self::callSAGE($url, $params);
    $ret = self::validateResponse($xml);

    if ($ret) {
      if ($xml->districtAssigned == 'true') {
        self::storeDistricts($values, $xml, $overwrite_districts);
      }
      if ($xml->geocoded == 'true') {
        self::storeGeocodes($values, $xml, $overwrite_point);
      }
    }
    else {
      self::warn("Distassign for [$params] has failed.");
    }

    return $ret;

  }


  /**
   * Performs batch district assignment of addresses and stores the districts
   * in {$rows}.
   *
   * @param array   &$rows An array of rows that each contain an array with
   *   address and district columms.
   * @param boolean $overwrite_districts If true, districts will be written by
   *   default to {$rows}
   * @param boolean $overwrite_point If true, geocode will be written by
   *   default to {$rows}
   * @param boolean $streetfile_only If true, only streetfile lookup will be
   *   used for district assign
   *
   * @return bool
   */
  public static function batchDistAssign(array &$rows,
                                         $overwrite_districts=true,
                                         $overwrite_point=true,
                                         $streetfile_only=false): bool {
    $addresses = self::getAddressesFromRows($rows);

    $url = '/district/assign/batch';
    $params = [
      'districtStrategy' => ($streetfile_only) ? 'streetOnly' : 'streetFallback',
    ];

    $batchXml = self::callSAGEPost($url, $params, json_encode($addresses));

    $ret = ($batchXml instanceof SimpleXMLElement) && ($batchXml->total == count($addresses));
    if ($ret) {
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
    }
    return $ret;
  }


  /**
  * Performs a bluebird lookup by point and assigns district information to {$values}
  *
  * @param array &$values Array representing address/geocode/district values.
  * @param boolean $overwrite_districts  If true, districts will be written by default to {$values}.
  * @return boolean if response validated successfully, false otherwise.
  */
  public static function lookupFromPoint(&$values, $overwrite_districts=true): bool {
    $url = '/district/bluebird';
    $params = [
        'lat' => CRM_Utils_Array::value('geo_code_1', $values, ""),
        'lon' => CRM_Utils_Array::value('geo_code_2', $values, ""),
      ];
    $xml = self::callSAGE($url, $params);
    $ret = self::validateResponse($xml);
    CRM_Core_Error::backtrace(__FUNCTION__, TRUE);
    if ($ret) {
      self::storeDistricts($values, $xml, $overwrite_districts);
    }
    else {
      $params = json_encode($params);
      self::warn("Lookup for [$params] has failed.");
    }
    return $ret;
  }


  /**
   * Performs a bluebird lookup by point and assigns district information to
   * {$rows}.
   *
   * @param array   &$rows An array of rows that each contain an array point
   *   columms.
   * @param boolean $overwrite_districts If true, districts will be written by
   *   default to {$rows}.
   *
   * @return bool
   */
  public static function batchLookupFromPoint(array &$rows, $overwrite_districts=true): bool {
    $points = self::getPointsFromRows($rows);

    $url = '/district/assign/batch';
    $batchXml = self::callSAGEPost($url, [], json_encode($points));
    $ret = ($batchXml instanceof SimpleXMLElement) && ($batchXml->total == count($points));

    if ($ret) {
      for ($i = 0; $i < $batchXml->results->results->count(); $i++) {
        $xml = $batchXml->results->results[$i];
        if (self::validateResponse($xml) && $xml->districtAssigned == 'true') {
          self::storeDistricts($rows[$i], $xml, $overwrite_districts);
        }
      }
    }

    return $ret;
  }


  /**
  * Performs a bluebird lookup by address and assigns geocode and district information to {$values}.
  *
  * @param array   &$values              An array representing address, geocode, and district values.
  * @param boolean $overwrite_districts  If true, districts will be written by default to {$values}.
  * @param boolean $overwrite_point      If true, geocode will be written by default to {$values}
  * @return true if response validated successfully, false otherwise.
  */
  public static function lookup(&$values, $overwrite_districts=true, $overwrite_point=true) {
    [$addr_field, $addr] = self::getAddress($values);
    if (!$addr) {
      self::warn("Not enough address info.");
      return false;
    }

    // If there is a state/province id, set the value of the state/province.
    if (isset($values['state_province_id'])) {
      $values['state_province'] = CRM_Core_PseudoConstant::stateProvinceAbbreviation($values['state_province_id']);
    }

    // Construct and send the Bluebird API request.
    $url = '/district/bluebird';
    $params = [
        'addr1' => str_replace(',', '', $addr),
        'city' => CRM_Utils_Array::value('city', $values, ""),
        'state' => CRM_Utils_Array::value('state_province', $values, ""),
        'zip5' => CRM_Utils_Array::value('postal_code', $values, ""),
      ];

    $xml = self::callSAGE($url, $params);
    $ret = self::validateResponse($xml);

    if ($ret) {
      if ($xml->uspsValidated == 'true') {
        // Don't change imported addresses, assume they are correct as given.
        $url_components = explode( '/', CRM_Utils_System::currentPath() );
        if (count($url_components) > 1 && $url_components[1] != 'import')
          self::storeAddress($values, $xml, $addr_field);
      }
      else {
        self::warn("USPS could not validate address: [$addr]");
      }

      if ($xml->geocoded == 'true') {
        self::storeGeocodes($values, $xml, $overwrite_point);
      }

      if ($xml->districtAssigned == 'true') {
        self::storeDistricts($values, $xml, $overwrite_districts);
      }
    }
    else {
      $params = json_encode($params);
      self::warn("Lookup for [$params] has failed.");
    }

    return $ret;
  }


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
  public static function batchLookup(&$rows, $overwrite_districts=true, $overwrite_point=true) {
    $addresses = self::getAddressesFromRows($rows);
    $url = '/district/bluebird/batch';
    $batchXml = self::callSAGEPost($url, [], json_encode($addresses));

    $ret = ($batchXml instanceof SimpleXMLElement) && ($batchXml->total == count($addresses));
    if ($ret) {
      for ($i = 0; $i < $batchXml->results->results->count(); $i++) {
        $xml = $batchXml->results->results[$i];
        if (self::validateResponse($xml)) {
          if ($xml->uspsValidated == 'true') {
            [$addr_field, $addr] = self::getAddress($rows[$i]);
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
    }

    return $ret;
  }


  /**
   * Returns an array of addresses using data in the supplied {$rows}.
   *
   * @param array &$rows An array of rows that each contain an array with address columns.
   *
   * @return array containing arrays of (addr1, city, state, zip5).
   */
  protected static function getAddressesFromRows(array &$rows): array {
    $addresses = array();
    foreach ($rows as $row) {
      [$addr_field, $addr] = self::getAddress($row);
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
  }

  /**
   * Returns an array of lat/lng points using data in the supplied {$rows}.
   *
   * @param array $rows An array of rows that each contain geocode columns.
   *
   * @return array containing arrays of (lat, lon).
   */
  protected static function getPointsFromRows(array &$rows): array {
    $points = array();
    foreach($rows as $row) {
      $points[] = array(
        'lat' => CRM_Utils_Array::value('geo_code_1', $row, ""),
        'lon' => CRM_Utils_Array::value('geo_code_2', $row, "") 
      );
    }
    return $points;
  }

  /**
  * Fail silently if the XML response from SAGE was invalid and could not
  * be parsed into a simplexml object, or if it was parsed but returned
  * a non-zero statusCode.
  * 
  * @param $xml
  * @return boolean true if validated, false otherwise.
  */
  protected static function validateResponse($xml): bool {
    return ($xml instanceof SimpleXMLElement) && ($xml->statusCode == 0);
  }

  /** 
  * Historically there have been several fields to store the address.
  * We need to return the address and the source field to store the
  * corrected address back into the correct form field.
  *
  * @param array $values
  * @return array containing (column name, street address)
  */
  protected static function getAddress(array $values): array {
    $addr_fields = array('street_address', 'supplemental_address_1');
    foreach ($addr_fields as $addr_field) {
      if (CRM_Utils_Array::value($addr_field, $values)) {
        return array($addr_field, $values[$addr_field]);
      }
    }
    return array('street_address', "");
  }


  /**
   * Sets the address column entries within the supplied {$values} array using the
   * SAGE xml response {$xml}. In addition, the street parts are parsed and stored as well.
   *
   * @param array &$values An array representing address, geocode, and district values
   * @param SimpleXMLElement $xml SimpleXml object containing SAGE xml response.
   * @param string $addr_field Column name where original street address was obtained.
   */
  protected static function storeAddress(array &$values, SimpleXMLElement $xml, string $addr_field) {
    //Forced type cast required to convert the simplexml objects to strings
    $values['city'] = ucwords(strtolower((string)$xml->address->city));
    $values['state_province'] = (string)$xml->address->state;
    $values['postal_code'] = (string)$xml->address->zip5;
    $values['postal_code_suffix'] = (string)$xml->address->zip4;

    $address = (!empty($xml->address->addr2)) ? $xml->address->addr1.', '.$xml->address->addr2 : $xml->address->addr1;
    $values[$addr_field] = self::normalizeAddr((string)$address, $values[$addr_field]);

    // Since standardization could change the street address, fix the parts
    self::fixStreetAddressParts($values);
  }


  /**
  * Sets geocode column entries within the supplied {$values} array using the 
  * SAGE xml response {$xml}.
  *
   * @param array &$values An array representing address, geocode, and district values
   * @param \SimpleXMLElement $xml SimpleXml object containing SAGE xml response.
   * @param boolean $overwrite If true, geocode data is written by default.
   */
  protected static function storeGeocodes(&$values, $xml, $overwrite = false) {
    //Forced type cast required to convert the simplexml objects to strings
    if ($overwrite || empty($values["geo_code_1"]) || !$values["geo_code_1"]) {
     $values["geo_method"] = (string)$xml->geocode->method;
     $values["geo_code_1"] = (string)$xml->geocode->lat;
    }
    if ($overwrite || empty($values["geo_code_2"]) || !$values["geo_code_2"]) {
     $values["geo_code_2"] = (string)$xml->geocode->lon;
    }
  }


  /**
  * Sets district column entries within the supplied {$values} array using the 
  * SAGE xml response {$xml}.
  * 
  * @param array     &$values    An array representing address, geocode, and district values
  * @param \SimpleXMLElement $xml        SimpleXml object containing SAGE xml response.
  * @param boolean   $overwrite  If true, district data is written by default.
  */
  protected static function storeDistricts(&$values, $xml, $overwrite) {
    /*Civi::log()->debug(__METHOD__, [
      'values' => $values,
      'xml' => $xml,
      'overwrite' => $overwrite,
    ]);*/

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
    //County Legislative District
    if ($overwrite || empty($values["custom_51_$id"]) || !$values["custom_51_$id"]) {
      $values["custom_51_$id"] = (string)$xml->districts->cleg->district;
    }
    if ($overwrite || empty($values["custom_52_$id"]) || !$values["custom_52_$id"]) {
      $values["custom_52_$id"] = (string)$xml->districts->town->district;
    }
    //Ward
    if ($overwrite || empty($values["custom_53_$id"]) || !$values["custom_53_$id"]) {
      $values["custom_53_$id"] = (string)$xml->districts->ward->district;
    }
    if ($overwrite || empty($values["custom_54_$id"]) || !$values["custom_54_$id"]) {
      $values["custom_54_$id"] = (string)$xml->districts->school->district;
    }
    //TODO City Council District
    /*if ($overwrite || empty($values["custom_55_$id"]) || !$values["custom_55_$id"]) {
      $values["custom_55_$id"] = (string)$xml->districts->city_council->district;
    }*/
  }


  /**
  * Applies normalizations to address line 1.
  *
  * @param string $addr       String containing the validated address line 1 value.
  * @param string $orig_addr  String containing the original address line 1 value. 
  */
  private static function normalizeAddr($addr, $orig_addr) {
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
  }


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
  private static function fixStreetAddressParts(&$values) {
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
  }

  /**
   * wrapper function to retain compatibility with expected methods in core
   */
  public static function format(&$values, $stateName = false) {
    self::geocode($values);
  }

  /**
   * @param $id
   * @return CRM_Core_DAO_Address|null
   *
   * Retrieve the full address record associated with the given address ID.
   */
  static function retrieveAddress($id) {
    if ($id) {
      $address = new CRM_Core_DAO_Address();
      $address->id = $id;
      if ($address->find(TRUE)) {
        return $address;
      }
    }

    return NULL;
  }

  /**
   * @param $addr
   * @param $params
   * @return bool
   *
   * Return true if all components of the address are equal to the params.
   */
  static function compareAddressComponents($addr, $params) {
    if ($addr === NULL || !is_array($params)) {
      return FALSE;
    }

    $cmp_keys = [
      'street_address', 'city', 'postal_code', 'postal_code_suffix',
      'state_province_id', 'supplemental_address_1',
    ];
    foreach ($cmp_keys as $akey) {
      $val1 = $addr->$akey ?? NULL;
      $val2 = $params[$akey] ?? NULL;

      if ($val1 != $val2) {
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * @param $params
   * @param $id
   * @return mixed|null
   *
   * Search through parameters for a key that starts with "custom_NN_", where
   * "NN" is the custom key ID.  If found, return the value associated with
   * the partial key.  If not found, return null.
   */
  static function customValue($params, $id) {
    foreach ($params as $k => $v) {
      $key_prefix = "custom_{$id}_";
      $len = strlen($key_prefix);
      if (substr($k, 0, $len) === $key_prefix) {
        return $v;
      }
    }

    return NULL;
  }

  /**
   * @param $params
   * @return bool
   *
   * Return true if all 7 district info parameters are populated, false otherwise.
   * NYSS 5308
   */
  static function districtInfoPopulated($params) {
    foreach ([46,47,48,49,50,52,54] as $cidx) {
      if (empty(self::customValue($params, $cidx))) {
        return FALSE;
      }
    }

    return TRUE;
  }
}
