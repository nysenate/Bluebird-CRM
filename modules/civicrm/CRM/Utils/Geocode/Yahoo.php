<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

/**
 * Class that uses geocoder.us to retrieve the lat/long of an address
 */
class CRM_Utils_Geocode_Yahoo {
    /**
     * server to retrieve the lat/long
     *
     * @var string
     * @static
     */
    static protected $_server = 'api.local.yahoo.com';

    /**
     * uri of service
     *
     * @var string
     * @static
     */
    static protected $_uri = '/MapsService/V1/geocode';

    /**
     * function that takes an address object and gets the latitude / longitude for this
     * address. Note that at a later stage, we could make this function also clean up
     * the address into a more valid format
     *
     * @param object $address
     *
     * @return boolean true if we modified the address, false otherwise
     * @static
     */
    static function format( &$values, $stateName = false ) {
        CRM_Utils_System::checkPHPVersion( 5, true );

        // we need a valid country, else we ignore
        if ( ! CRM_Utils_Array::value( 'country'        , $values  ) ) {
            return false;
        }

        $config = CRM_Core_Config::singleton( );

        $arg = array( );
        $arg[] = "appid=" . urlencode( $config->mapAPIKey );

        if (  CRM_Utils_Array::value( 'street_address', $values ) ) {
            $arg[] = "street=" . urlencode( $values['street_address'] );
        }

        $city = CRM_Utils_Array::value( 'city', $values );
        if ( $city ) {
            $arg[] = "city=" . urlencode( $city );
        }

        if (  CRM_Utils_Array::value( 'state_province', $values ) ) { 
            if ( CRM_Utils_Array::value( 'state_province_id', $values ) ) {
                $stateProvince = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_StateProvince', $values['state_province_id'] );
            } else {
                if ( ! $stateName ) {
                    $stateProvince = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_StateProvince', $values['state_province'], 'name', 'abbreviation' );
                } else {
                    $stateProvince = $values['state_province'];
                }
            }
            // dont add state twice if replicated in city (happens in NZ and other countries, CRM-2632)
            if ( $stateProvince != $city ) {
                $arg[] = "state=" . urlencode( $stateProvince );
            }
        }

        if (  CRM_Utils_Array::value( 'country', $values ) ) { 
            $arg[] = "country=" . urlencode( $values['country'] );
        }

        if (  CRM_Utils_Array::value( 'postal_code', $values ) ) { 
            $arg[] = "zip=" . urlencode( $values['postal_code'] );
        }

        $args = implode( '&', $arg );

        $query = 'http://' . self::$_server . self::$_uri . '?' . $args;

        require_once 'HTTP/Request.php';
        $request = new HTTP_Request( $query );
        $request->sendRequest( );
        $string = $request->getResponseBody( );
        $xml = simplexml_load_string( $string );

        $ret = array( );
        $ret['precision'] = (string)$xml->Result['precision'];

        if ( is_a($xml->Result, 'SimpleXMLElement') ) {
            $result = array( ) ;
            $result = get_object_vars($xml->Result);

            foreach ( $result as $key => $val ) {
                if (strlen($val)) $ret[(string)$key] =  (string)$val;
            }

            $values['geo_code_1'] = $ret['Latitude' ];
            $values['geo_code_2'] = $ret['Longitude'];
            return true;
        }
        // reset the geo code values if we did not get any good values
        $values['geo_code_1'] = $values['geo_code_2'] = 'null';
        return false;
    }

}


