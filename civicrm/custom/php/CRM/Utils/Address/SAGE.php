<?php
/*
 * SAGE.php - An address information provider using the Senate Address
 *            Geo-coding Engine (SAGE).
 *
 * Project: BluebirdCRM
 * Author: Ken Zalewski
 * Organization: New York State Senate
 * Date: 2010-12-19
 * Revised: 2011-02-07
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
        if (!isset($values['street_address']) || 
             (!isset($values['city']) &&
              !isset($values['state_province']) &&
              !isset($values['postal_code']))) {
            return false;
        } 
        
        /*
        ** The UserID will be used as the SAGE API key.
        ** The URL will be used as the SAGE URL.
        */
        require_once 'CRM/Core/BAO/Preferences.php';
        $userID = CRM_Core_BAO_Preferences::value('address_standardization_userid');
        $url = CRM_Core_BAO_Preferences::value('address_standardization_url');
        $session = CRM_Core_Session::singleton();

        if (empty($userID) || empty($url)) {
            return false;
        }

        $api_key = $userID;

        // Try a sequence of possible address fields to find a non-empty value.
        $addr_fields = array('street_address',
                             'supplemental_address_1',
                             'supplemental_address_2');
        $addr2 = null;
        foreach ($addr_fields as $addr_field) {
            if (!empty($values[$addr_field])) {
                $addr2 = $values[$addr_field];
                break;
            }
        }

        if (!$addr2) {
            $session->setStatus(ts('SAGE Warning: Not enough address info.'));
            return false;
        }

        $data = array('addr2' => str_replace(',', '', $addr2),
                      'city' => $values['city'],
                      'zip5' => $values['postal_code'],
                      'state' => $values['state_province'],
                      'key' => $api_key);
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
    
        if (is_null($xml) || is_null($xml->address2)) {
            $session->setStatus(ts("SAGE Warning: Postal lookup for [$addr2] has failed.\n"));
            return false;
        } 
        else if (!empty($xml->message)) {
            $session->setStatus(ts('SAGE Warning: '.$xml->message));
            return false;
        }
        else {
            /*** kz - We really only care if the lookup fails, and we need
             ***      to cut down on the number of messages on import.
            $session->setStatus(ts('SAGE Info: Postal lookup has succeeded.'));
            ***/
        }
 
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
}

