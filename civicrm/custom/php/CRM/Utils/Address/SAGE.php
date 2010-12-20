<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
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
 * Address utilities
 */
class CRM_Utils_Address_SAGE
{
    static function checkAddress( &$values )
    {

        if ( ! isset($values['street_address'])     || 
               ( ! isset($values['city']           )   &&
                 ! isset($values['state_province'] )   &&
                 ! isset($values['postal_code']    )      ) ) {
            return false;
        } 
        
        /*
        ** The UserID will be used as the SAGE API key.
        ** The URL will be used as the SAGE URL.
        */
        require_once 'CRM/Core/BAO/Preferences.php';
        $userID = CRM_Core_BAO_Preferences::value( 'address_standardization_userid' );
        $url    = CRM_Core_BAO_Preferences::value( 'address_standardization_url'    );

        if ( empty( $userID ) || empty( $url ) ) {
            return false;
        }

        $api_key = $userID;
        $addr2 = str_replace( ',', '', $values['street_address'] );    
        $city  = $values['city'];
        $zip5  = $values['postal_code'];
        $state = $values['state_province'];
        $data = array('addr2' => $addr2, 'city' => $city, 'zip5' => $zip5, 'state' => $state, 'key' => $api_key);
        $urlstring = ''; 
        foreach ($data as $key => $value) {
           $urlstring .= urlencode($key).'='.urlencode($value).'&';
        } 
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 180);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $urlstring);
        $html = curl_exec($ch);
        curl_close($ch);

        $xml = simplexml_load_string($html);
    
        $session = CRM_Core_Session::singleton( );
        if (is_null($xml) || is_null($xml->address2) ) {
            $session->setStatus( ts( 'Your SAGE API lookup has failed.' ) );
            return false;
        } 
        if (!empty($xml->message)) {
            $session->setStatus( ts('Error:'.$xml->message ) );
            return false;
        }  
        else {
    	   $session->setStatus( ts( 'SAGE USPS API lookup has succeeded.' ) );
        }
 
 
        $values['street_address'] = ucwords(strtolower((string)$xml->address2));
        $address_element = explode(" ", $values['street_address']);
        for ($j=0; $j < count($address_element); $j++) {
            if ((preg_match( "/^[1-9]*[1](st)$/", $address_element[$j])) ||
                (preg_match( "/^[1-9]*[2](nd)$/", $address_element[$j])) ||
                (preg_match( "/^[1-9]*[3](rd)$/", $address_element[$j])) ||
                (preg_match( "/^[1-9]*[4-9,0](th)$/", $address_element[$j]))) {
                //don't do anything
            }
            elseif (preg_match( "/^[1-9][0-9a-zA-Z]+/", $address_element[$j], $matches)) {
                $address_element[$j] = strtoupper($address_element[$j]);
            }	
        }
 
        $values['street_address']     = (string)(implode(" ",$address_element));
        $values['city']               = (string)(ucwords(strtolower($xml->city)));
        $values['state_province']     = (string)$xml->state;
        $values['postal_code']        = (string)$xml->zip5;
        $values['postal_code_suffix'] = (string)$xml->zip4;
        return true;
    }
}

