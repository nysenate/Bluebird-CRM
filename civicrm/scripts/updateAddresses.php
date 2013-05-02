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
  $shortopts = 's:e:b:h:gpt:d:u:f';
  $longopts = array('start=', 'end=', 'batch=', 'threshold=', 'geocode', 'parse', 'throttle', 'distassign', 'usecoords', 'force');
  $stdusage = civicrm_script_usage();
  $usage = "[--start|-s startID]  [--end|-e endID]  [--batch|-b count]  [--threshold|-h secs]  [--geocode|-g]  [--parse|-p]  [--throttle|-t]  [--distassign|-d]  [--usecoords|-u]  [--force|-f]";

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

  // do check for parse street address.
  require_once 'CRM/Core/BAO/Preferences.php';
  $address_options = CRM_Core_BAO_Setting::valueOptions(CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME, 'address_options');
  $parseAddress = CRM_Utils_Array::value('street_address_parsing',$address_options, false);
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

  $force = ($optlist['force'] ? "update" : "fill");
  if($optlist['geocode'] && $optlist['distassign']) {
    echo ts( "Geocoding and district assigning using $force strategy.\n" );
  }
  else if($optlist['geocode']) {
    echo ts( "Geocoding  using $force strategy.\n" );
  }
  else if($optlist['distassign']) {
    echo ts( "District assigning using $force strategy.\n" );
  }

  // don't process.
  if (!$parseStreetAddress && !$optlist['geocode'] && !$optlist['distassign']) {
    echo ts("Error: Geocode mapping, district assignment and Street Address Parsing are disabled. At least one option must be enabled to use this script.\n");
    exit(1);
  }

  $batch = ($optlist['batch']) ? $optlist['batch'] : DEFAULT_ADDRESS_BATCH;
  $threshold = ($optlist['threshold']) ? $optlist['threshold'] : DEFAULT_SLOW_REQUEST_THRESHOLD;

  processContacts($parseStreetAddress, $batch, $threshold, $optlist);
}

function processContacts($parseStreetAddress, $batch,   $threshold, $optlist) {

  $start_time = microtime(true);

  require_once 'CRM/Utils/SAGE.php';

  //set defaults for recording metrics
  $totalGeocoded = $totalAddresses = $totalDistAssigned = 0;

  $query = getQuery($optlist);

  echo "Executing query: $query\n";

  $dao =& CRM_Core_DAO::executeQuery($query, CRM_Core_DAO::$_nullArray);

  echo "Query time = " . get_elapsed_time($start_time) . " secs\n";

  require_once 'CRM/Core/DAO/Address.php';
  require_once 'CRM/Core/BAO/Address.php';

  echo "Iterating over addresses...\n";

  $totalAddressParsed = 0;
  $unparseableContactAddress = array( );
  while ($dao->fetch()) {
    $totalAddresses++;
    $addr_start_time = microtime(true);
      
    $address = array(
      'street_address'    => $dao->street_address,
      'postal_code'       => $dao->postal_code,
      'city'              => $dao->city,
      'state_province'    => $dao->state,
      'country'           => $dao->country
    );

    if ($optlist['geocode']) {
      $address['geo_code_1']  = $dao->lat;
      $address['geo_code_2']  = $dao->lon;
      $missing_geo = $address['geo_code_1'] && $address['geocode_2'];
    } else {
      $missing_geo = false;
    }

    if($optlist['distassign']) {
      $address['custom_46_'.$dao->d_id] =empty($dao->cd) ? null : $dao->cd;
      $address['custom_47_'.$dao->d_id] = empty($dao->sd) ? null : $dao->sd;
      $address['custom_48_'.$dao->d_id] = empty($dao->ad) ? null : $dao->ad;
      $address['custom_49_'.$dao->d_id] = empty($dao->ed) ? null : $dao->ed;
      $missing_districts = $address['custom_46_'.$dao->d_id]
              && $address['custom_47_'.$dao->d_id]
              && $address['custom_48_'.$dao->d_id]
              && $address['custom_49_'.$dao->d_id];
    } else {
      $missing_dstricts = false;
    }

    $success = false;

    if($optlist['geocode'] && $optlist['distassign']) {

      if($overwrite || ($missing_geo && $missing_districts) || (!array_key_exists('usecoords', $optlist) && $missing_districts)) {
        //three rules for acceptance here
        //1. when overwriting all
        //2. when missing both sets of data
        //3. when we're not using coordinates and districts are missing
          
        $success = CRM_Utils_SAGE::lookup($address, $overwrite, $overwrite);
                if($success) {
                    $totalGeocoded++;
                }
      }
      else if (!$missing_geo && $missing_districts) {
        //record not missing lat+lon but districts are missing
          
        $success = CRM_Utils_SAGE::lookup_from_point($address, $overwrite);
      }
      else if ($missing_geo && !$missing_districts) {
        //record missing geo but not districts

        $success = CRM_Utils_SAGE::format($address, true);
                if($success) {
                    $totalGeocoded++;
                }
      }
    }
    else if($optlist['geocode']) {
      //data is representative of query which
      //takes force in t1o consideration, any record
      //appearing here must be processed

      $success = CRM_Utils_SAGE::format($address, true);
            if($success) {
                $totalGeocoded++;
            }
    }
    else if($optlist['distassign']) {
      //again data is represenative of query
      if($optlist['usecoords'] && !$missing_geo) {
        $success = CRM_Utils_SAGE::lookup_from_point($address, $overwrite);
      }
      else {
        $success = CRM_Utils_SAGE::lookup($address, $overwrite, $overwrite);
                if($success) {
                    $totalGeocoded++;
                }
      }
    }
    
    if(!$optlist['geocode'] && $optlist['distassign']) {
      if(array_key_exists('geo_code_1', $address)) {
        unset($address['geo_code_1']);
      }
      if(array_key_exists('geo_code_2', $address)) {
        unset($address['geo_code_2']);
      }
    }

    // parse street address
    if ($parseStreetAddress) {
      $parsedFields = CRM_Core_BAO_Address::parseStreetAddress($dao->street_address);
      $success = true;
      // consider address is automatically parseable,
      // when we should find street_number and street_name
      //NYSS 5918 - consider parsed if *either* name or number parsed
      if (!CRM_Utils_Array::value('street_name', $parsedFields) &&
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
      $address = array_merge($address, $parsedFields);
    }

    //save address information
    if($success || $parseStreetAddress) {
      if (!empty($address)) {
        $address_dao = new CRM_Core_DAO_Address();
        $address_dao->id = $dao->address_id;
        $address_dao->copyValues($address);
        $address_dao->save();
        $address_dao->free();
      }
    }

    //save custom district fields
    if($success && $optlist['distassign']) {
      $customFields = null;
      if ( ! $customFields ) {
        require_once 'CRM/Core/BAO/CustomField.php';
        require_once 'CRM/Core/BAO/CustomValueTable.php';
        $customFields = CRM_Core_BAO_CustomField::getFields( 'Address', false, true );
      }

      if ( ! empty( $customFields ) ) {
        $addressCustom = CRM_Core_BAO_CustomField::postProcess(
        $address,
        $customFields,
        $dao->d_id,
             'Address', 
        true );
      }

      if ( ! empty( $addressCustom ) ) {
        CRM_Core_BAO_CustomValueTable::store( $addressCustom, 'civicrm_address', $dao->d_id );
      }
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
  if ($optlist['geocode'] || $optlist['distassign']) {
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

//dynamically build query based on command line args
function getQuery($optlist) {
  $query = "";
  $querySelect = array(
    "c.id",
    "a.id as address_id",
    "a.street_address",
    "a.city",
    "a.postal_code",
    "s.name as state",
    "o.name as country",
  );

  $whereClause = '( c.id = a.contact_id )';

  $start = $optlist['start'];
  if ($start && is_numeric($start)) {
    $whereClause .= " AND ( c.id >= $start )";
  }

  $end = $optlist['end'];
  if ($end && is_numeric($end)) {
    $whereClause .= " AND ( c.id <= $end )";
  }

  if($optlist['geocode']) {
    $querySelect[] = "a.geo_code_1 as lat";
    $querySelect[] = "a.geo_code_2 as lon";

    if(!$optlist['force']) {
      $whereClause .= "
 AND (( a.geo_code_1 is null OR a.geo_code_1 = 0 ) AND
( a.geo_code_2 is null OR a.geo_code_2 = 0 ) AND
( a.country_id is not null ))";
    }
  }

  if($optlist['distassign']) {
    $querySelect[] = "d.congressional_district_46 as cd";
    $querySelect[] = "d.ny_senate_district_47 as sd";
    $querySelect[] = "d.ny_assembly_district_48 as ad";
    $querySelect[] = "d.election_district_49 as ed";
    $querySelect[] = "d.id as d_id";

    if($optlist['usecoords'] && !$optlist['geocode']) {
      $querySelect[] = "a.geo_code_1 as lat";
      $querySelect[] = "a.geo_code_2 as lon";
    }

    if(!$optlist['force']) {
      $whereClause .= "
 AND (( d.congressional_district_46 is null or d.congressional_district_46 = \"\" ) OR
( d.ny_senate_district_47 is null or d.ny_senate_district_47= \"\" ) OR
( d.ny_assembly_district_48 is null or d.ny_assembly_district_48 = \"\" ) OR
( d.election_district_49 is null or d.election_district_49 = \"\" ))";
    }
  }

  $query = "SELECT ".implode( ', ', $querySelect )."
FROM       civicrm_contact  c
INNER JOIN civicrm_address                a ON a.contact_id = c.id
LEFT  JOIN civicrm_country                o ON a.country_id = o.id
LEFT  JOIN civicrm_state_province         s ON a.state_province_id = s.id
LEFT  JOIN civicrm_value_district_information_7 d ON a.id = d.entity_id
WHERE      {$whereClause}
  ORDER BY a.id
";

  return $query;
}

function get_address_line(&$dao_p)
{
  return 'ID #'.$dao_p->address_id.': '.$dao_p->street_address.', '.$dao_p->city.', '.$dao_p->state.' '.$dao_p->postal_code;
} // get_address_line()

run();

