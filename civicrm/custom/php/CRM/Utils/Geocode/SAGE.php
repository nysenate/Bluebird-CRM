<?php
/**
 * SAGE.php - Interface to the Senate Address Geocoding Engine (SAGE)
 *
 * Project: BluebirdCRM
 * Author: Ken Zalewski
 * Organization: New York State Senate
 * Date: 2011-01-31
 * Revised: 2011-02-01
 */


/**
 * Class that uses SAGE to retrieve the lat/long of an address
 */
class CRM_Utils_Geocode_SAGE {
    /**
     * server to retrieve the lat/long
     *
     * @var string
     * @static
     */
    static protected $_server = 'sage.nysenate.gov';

    /**
     * uri of service
     *
     * @var string
     * @static
     */
    static protected $_uri = '/api/csv/geocode/extended';

    /**
     * function that takes an address object and gets the latitude / longitude
     * for this address. Note that at a later stage, we could make this
     * function also clean up the address into a more valid format
     *
     * @return boolean true if we modified the address, false otherwise
     * @static
     */
    static function format(&$values, $stateName = false)
    {
        CRM_Utils_System::checkPHPVersion(5, true);

        // we need a valid country, else we ignore
        if (! CRM_Utils_Array::value('country', $values)) {
            return false;
        }

        $config = CRM_Core_Config::singleton();

        $arg = array();
        $arg[] = "key=" . urlencode($config->geoAPIKey);

        if (CRM_Utils_Array::value('street_address', $values)) {
            $arg[] = "street=" . urlencode($values['street_address']);
        }

        $city = CRM_Utils_Array::value('city', $values);
        if ($city) {
            $arg[] = "city=" . urlencode($city);
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
            if ($stateProvince != $city) {
                $arg[] = "state=" . urlencode($stateProvince);
            }
        }

        if (CRM_Utils_Array::value( 'country', $values)) { 
            $arg[] = "country=" . urlencode($values['country']);
        }

        if (CRM_Utils_Array::value('postal_code', $values)) { 
            $arg[] = "zip5=" . urlencode($values['postal_code']);
        }

        $args = implode('&', $arg);

        $query = 'http://' . self::$_server . self::$_uri . '?' . $args;

        require_once 'HTTP/Request.php';
        $request = new HTTP_Request($query);
        $request->sendRequest();
        $string = $request->getResponseBody();

        $ret = explode(",", $string);
        if ($ret[0] != "ERROR") {
            $values['geo_code_1'] = $ret[0];
            $values['geo_code_2'] = $ret[1];
            return true;
        }
        else {
            // reset the geo code values if we did not get any good values
            $values['geo_code_1'] = $values['geo_code_2'] = 'null';
            return false;
        }
    }
}
