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
 * A PHP cron script to format all the addresses in the database. Currently
 * it only does geocoding if the geocode values are not set. At a later
 * stage we will also handle USPS address cleanup and other formatting
 * issues
 *
 */

require_once 'script_utils.php';
define('DEFAULT_ADDRESS_BATCH', 200);
define('DEFAULT_SLOW_REQUEST_THRESHOLD', 1.0);


function run()
{
    $prog = basename(__FILE__);
    $shortopts = 's:e:b:h:gpt';
    $longopts = array('start=', 'end=', 'batch=', 'threshold=', 'geocode', 'parse', 'throttle');
    $stdusage = civicrm_script_usage();
    $usage = "[--start|-s startID]  [--end|-e endID]  [--batch|-b count]  [--threshold|-h secs]  [--geocode|-g]  [--parse|-p]  [--throttle|-t]";

    $optlist = civicrm_script_init($shortopts, $longopts);
    if ($optlist === null) {
      error_log("Usage: $prog  $stdusage  $usage");
      exit(1);
    }

    if (!is_cli_script()) {
        echo "<pre>\n";
    }

    //log the execution of script
    require_once 'CRM/Core/Error.php';
    CRM_Core_Error::debug_log_message('updateAddresses.php');

    require_once 'CRM/Core/Config.php';
    $config = CRM_Core_Config::singleton();
    $geocodeMethod = $config->geocodeMethod;
    echo "Geocode method is configured as: $geocodeMethod\n";

    // do check for geocoding.
    if (empty($geocodeMethod)) {
        if ($optlist['geocode'] == true) {
            echo ts('Error: You need to set a mapping provider under Global Settings.');
            exit(1); 
        }
    } else {
        // user might want to over-ride.
        if ($optlist['geocode'] == false) {
            $geocodeMethod = null;
            echo "Geocoding will NOT be done, based on user options.\n";
        }
    }

    // do check for parse street address.
    require_once 'CRM/Core/BAO/Preferences.php';
    $parseAddress = CRM_Utils_Array::value('street_address_parsing',
                                           CRM_Core_BAO_Preferences::valueOptions('address_options'), false);
    $parseStreetAddress = false;
    if (!$parseAddress) {
        if ($optlist['parse'] == true) {
            echo ts( 'Error: You need to enable Street Address Parsing under Global Settings >> Address Settings.' );
            exit(1);
        }
    } else {
        $parseStreetAddress = true;
        // user might want to over-ride.
        if ($optlist['parse'] == false) {
            $parseStreetAddress = false;
        }
    }
    
    // don't process.
    if (!$parseStreetAddress && !$geocodeMethod) {
        echo ts("Error: Both Geocode mapping as well as Street Address Parsing are disabled. You must configure one or both options to use this script.\n");
        exit(1);
    }
    
    $batch = ($optlist['batch']) ? $optlist['batch'] : DEFAULT_ADDRESS_BATCH;
    $threshold = ($optlist['threshold']) ? $optlist['threshold'] : DEFAULT_SLOW_REQUEST_THRESHOLD;

    processContacts($geocodeMethod, $parseStreetAddress, $optlist['start'],
                    $optlist['end'], $batch, $threshold ,$optlist['throttle']);
}


function processContacts($geoMethod, $parseStreetAddress, $start, $end,
                         $batch, $threshold, $throttle)
{
    $start_time = microtime(true);
    echo "Start time = $start_time secs\n";

    // build where clause.
    $clause = array( '( c.id = a.contact_id )' );
    if ($start && is_numeric($start)) {
        $clause[] = "( c.id >= $start )";
    }
    if ($end && is_numeric($end)) {
        $clause[] = "( c.id <= $end )";
    }
    if ($geoMethod) {
        $clause[] = '( a.geo_code_1 is null OR a.geo_code_1 = 0 )';
        $clause[] = '( a.geo_code_2 is null OR a.geo_code_2 = 0 )';
        $clause[] = '( a.country_id is not null )';
    }
    $whereClause = implode( ' AND ', $clause );
    
    $query = "
SELECT     c.id,
           a.id as address_id,
           a.street_address,
           a.city,
           a.postal_code,
           s.name as state,
           o.name as country
FROM       civicrm_contact  c
INNER JOIN civicrm_address        a ON a.contact_id = c.id
LEFT  JOIN civicrm_country        o ON a.country_id = o.id
LEFT  JOIN civicrm_state_province s ON a.state_province_id = s.id
WHERE      {$whereClause}
  ORDER BY a.id
";
   
    $totalAddresses = $totalGeocoded = $totalAddressParsed = 0;
    
    echo "Executing query: $query\n";

    $dao =& CRM_Core_DAO::executeQuery($query, CRM_Core_DAO::$_nullArray);
    
    echo "Query time = " . get_elapsed_time($start_time) . " secs\n";

    if ($geoMethod) {
        require_once(str_replace('_', DIRECTORY_SEPARATOR, $geoMethod).'.php');
    }
    
    require_once 'CRM/Core/DAO/Address.php';
    require_once 'CRM/Core/BAO/Address.php';
    
    echo "Iterating over addresses...\n";

    $unparseableContactAddress = array( );
    while ($dao->fetch()) {
        $totalAddresses++;
        $addr_start_time = microtime(true);

        $params = array('street_address'    => $dao->street_address,
                        'postal_code'       => $dao->postal_code,
                        'city'              => $dao->city,
                        'state_province'    => $dao->state,
                        'country'           => $dao->country );
        
        $addressParams = array();

        // process geocode.
        if ($geoMethod) {
            // loop through the address removing more information
            // so we can get some geocode for a partial address
            // i.e. city -> state -> country
            
            $maxTries = 5;
            do {
                if ($throttle) {
                    usleep(50000);
                }
                
                $rc = eval('return '.$geoMethod.'::format($params, true);');
                array_shift($params);
                $maxTries--;
            } while ($rc != true && $maxTries > 1);
            
            if ($rc) {
                $totalGeocoded++;
                $addressParams['geo_code_1'] = $params['geo_code_1'];
                $addressParams['geo_code_2'] = $params['geo_code_2'];
            }
            else {
                echo 'Unable to geocode address: '.get_address_line($dao)."\n";
            }
        }
        
        // parse street address
        if ($parseStreetAddress) {
            $parsedFields = CRM_Core_BAO_Address::parseStreetAddress($dao->street_address);
            $success = true;
            // consider address is automatically parseable,
            // when we should find street_number and street_name
            if (!CRM_Utils_Array::value('street_name', $parsedFields) ||
                !CRM_Utils_Array::value('street_number', $parsedFields)) {
                $success = false;
            }
            
            // do check for all elements.
            if ($success) {
                $totalAddressParsed++;
            }
            else if ($dao->street_address) { 
                //build contact edit url, 
                //so that user can manually fill the street address fields if the street address is not parsed, CRM-5886
                $url = CRM_Utils_System::url('civicrm/contact/add', "reset=1&action=update&cid={$dao->id}");
                $unparseableContactAddress[] = " Contact ID: " . $dao->id . " <a href =\"$url\"> ". $dao->street_address . " </a> ";
                // reset element values.
                $parsedFields = array_fill_keys(array_keys($parsedFields), '');
            }
            $addressParams = array_merge($addressParams, $parsedFields);
        }
        
        // finally update address object.
        if (!empty($addressParams)) {
            $address = new CRM_Core_DAO_Address();
            $address->id = $dao->address_id;
            $address->copyValues($addressParams);
            $address->save();
            $address->free();
        }

        $addr_elapsed_time = get_elapsed_time($addr_start_time);
        if ($addr_elapsed_time > $threshold) {
            echo "SLOW REQUEST (took $addr_elapsed_time secs): ".
                 get_address_line($dao)."\n";
        }

        if ($totalAddresses % $batch == 0) {
            echo "Processed $totalAddresses addresses; average time per address = ".(get_elapsed_time($start_time)/$totalAddresses)." secs\n";
        }
    }
    
    echo ts("Total addresses evaluated: $totalAddresses\n");
    if ($geoMethod) {
        echo ts("Addresses geocoded: $totalGeocoded\n");
    }
    if ($parseStreetAddress) {
        echo ts("Addresses parsed: $totalAddressParsed\n");
        if ($unparseableContactAddress) {
            echo ts("<br />\nFollowing is the list of contacts whose address is not parsed :<br />\n");
            foreach ($unparseableContactAddress as $contactLink) {
                echo ts("%1<br />\n", array(1 => $contactLink));
            }
        }
    }
    
    $elapsed_time = get_elapsed_time($start_time);
    echo "Elapsed time = $elapsed_time secs\n";
    if ($totalAddresses > 0) {
      echo "Average time per address = ".($elapsed_time/$totalAddresses)." secs\n";
    }

    if (!is_cli_script()) {
        echo "</pre>\n";
    }
    return;
}



function get_address_line(&$dao_p)
{
  return 'ID #'.$dao_p->address_id.': '.$dao_p->street_address.', '.$dao_p->city.', '.$dao_p->state.' '.$dao_p->postal_code;
} // get_address_line()

run();

