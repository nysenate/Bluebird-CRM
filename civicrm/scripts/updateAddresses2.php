<?php

/**
* A PHP cron script to format all the addresses in the database.
*/

require_once 'script_utils.php';
define('DEFAULT_ADDRESS_BATCH', 5);
define('DEFAULT_SLOW_REQUEST_THRESHOLD', 15.0);

function main()
{
  $prog = basename(__FILE__);
  $shortopts = 's:e:b:h:vgptdufy';
  $longopts = array('start=', 'end=', 'batch=', 'threshold=', 'validate', 'geocode', 'parse', 'throttle', 'distassign', 'usecoords', 'force', 'dryrun');
  $stdusage = civicrm_script_usage();
  $usage = "[--start|-s START_ID]  [--end|-e END_ID]  [--batch|-b COUNT]  [--threshold|-h SECS] [--validate|-v]"
           ."[--geocode|-g] [--parse|-p]  [--throttle|-t]  [--distassign|-d]  [--usecoords|-u] [--force|-f] [--dryrun|-y]";

  $optlist = civicrm_script_init($shortopts, $longopts);
  if ($optlist === null) {
    error_log("Usage: $prog  $stdusage  $usage");
    exit(1);
  }

  if (!is_cli_script()) {
    echo "<pre>\n";
  }

  // Log the execution of script.
  require_once 'CRM/Core/Error.php';
  CRM_Core_Error::debug_log_message('updateAddresses.php');

  // Check if street address should be parsed.
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
    // User might want to override.
    if ($optlist['parse'] == false) {
      $parseStreetAddress = false;
    }
  }

  $force = ($optlist['force'] ? "update" : "fill");
  if($optlist['geocode'] && $optlist['distassign']) {
    echo ts( "Geocoding and district assigning using $force strategy.\n" );
  }
  else if($optlist['geocode']) {
    echo ts( "Geocoding using $force strategy.\n" );
  }
  else if($optlist['distassign']) {
    echo ts( "District assigning using $force strategy.\n" );
  }

  // Don't process if no operations are specified
  if (!$parseStreetAddress && !$optlist['geocode'] && !$optlist['distassign']) {
    echo ts("Error: Geocode mapping, district assignment and Street Address Parsing are disabled. At least one option must be enabled to use this script.\n");
    exit(1);
  }

  $batch = ($optlist['batch']) ? $optlist['batch'] : DEFAULT_ADDRESS_BATCH;
  $threshold = ($optlist['threshold']) ? $optlist['threshold'] : DEFAULT_SLOW_REQUEST_THRESHOLD;

  echo ts( "Using batches of $batch addresses.\n");  
  processContacts($parseStreetAddress, $batch, $threshold, $optlist);
}

function processContacts($parseStreetAddress, $batchSize, $threshold, $optlist) {

  require_once 'CRM/Utils/SAGE.php';
  require_once 'CRM/Core/DAO/Address.php';
  require_once 'CRM/Core/BAO/Address.php';  
  require_once 'CRM/Core/BAO/CustomField.php';
  require_once 'CRM/Core/BAO/CustomValueTable.php';

  $startTime = $batchStartTime = microtime(true);

  // Set defaults for recording metrics
  $addressBatch = array();
  $totalGeocoded = $totalAddresses = $totalDistAssigned = 0;

  $query = getQuery($optlist);

  echo "Executing query: $query\n";
  $dao = new CRM_Core_DAO();
  $db = $dao->getDatabaseConnection()->connection;
  $res = bb_mysql_query($query, $db, true);
  
  // Decided not to use the dao for the query because of row counting errors.
  // $dao =& CRM_Core_DAO::executeQuery($query, CRM_Core_DAO::$_nullArray);
  
  echo "Query time = " . get_elapsed_time($startTime) . " secs\n";
  echo "Iterating over addresses...\n";

  $currentBatchSize = $totalAddressParsed = 0;
  $unparseableContactAddress = array();

  $overwrite = ($optlist['force'] == 'update');
  $performUspsValidate = $optlist['validate']; 
  $performGeocode = $optlist['geocode'];
  $performDistAssign = $optlist['distassign']; 
  $useCoords = $optlist['usecoords'];
  $dryrun = $optlist['dryrun'];
  $totalRows = mysql_num_rows($res);

  echo "Total rows: {$totalRows}.\n";
  
  while (($row = mysql_fetch_assoc($res)) != null) {
    $totalAddresses++;
          
    $address = array(
      'contact_id'        => $row['id'],
      'address_id'        => $row['address_id'],
      'street_address'    => $row['street_address'],
      'postal_code'       => $row['postal_code'],
      'city'              => $row['city'],
      'state_province'    => $row['state'],
      'country'           => $row['country']
    );

    if ($performGeocode) {
      $address['geo_code_1']  = $row['lat'];
      $address['geo_code_2']  = $row['lon'];
    } 

    if ($performDistAssign) {
      $address['district_id'] = $row['d_id'];
      $address['custom_46_'.$row['d_id']] = empty($row['cd']) ? null : $row['cd'];
      $address['custom_47_'.$row['d_id']] = empty($row['sd']) ? null : $row['sd'];
      $address['custom_48_'.$row['d_id']] = empty($row['ad']) ? null : $row['ad'];
      $address['custom_49_'.$row['d_id']] = empty($row['ed']) ? null : $row['ed'];
    } 

    $addressBatch[] = $address;
    
    // Fill up the batch of addresses.
    if (($totalAddresses % $batchSize != 0) && ($totalAddresses != $totalRows)) continue;
    
/**
DEBUG print_r
*/
//    print_r($addressBatch);

    // Perform batch requests based on groups of operations requested.
    if ($performUspsValidate && $performGeocode && $performDistAssign) {
      echo ts("Performing batch bluebird lookup...\n");
      CRM_Utils_SAGE::batchLookup($addressBatch, $overwrite, $overwrite);
    }
    else if ($performGeocode && $performDistAssign) {
      echo ts("Performing batch district assign...\n");
      CRM_Utils_SAGE::batchDistAssign($addressBatch, $overwrite, $overwrite);
    }
    else {    
      if ($performUspsValidate) {
        echo ts("Performing batch check address...\n");
        CRM_Utils_SAGE::batchCheckAddress($addressBatch);
      } 
      if ($performGeocode && !($performDistAssign && !$useCoords)) {
        echo ts("Performing batch geocode...\n");
        CRM_Utils_SAGE::batchGeocode($addressBatch, $overwrite);
      }
      if ($performDistAssign) {
        if ($useCoords) {
          echo ts("Performing batch lookup using geocodes...\n");
          CRM_Utils_SAGE::batchLookupFromPoint($addressBatch, $overwrite);
        }
        else if ($performGeocode) {
          echo ts("Performing batch geocode/district assign...\n");
          CRM_Utils_SAGE::batchDistAssign($addressBatch, $overwrite, $overwrite);
        }
        else {
          echo ts("Performing batch district assign without overwriting geocode...\n");
          CRM_Utils_SAGE::batchDistAssign($addressBatch, $overwrite, false);
        }
      }
    }

    $sageProcessTime = get_elapsed_time($batchStartTime);
    echo ts("SAGE processing time: " . $sageProcessTime . " s.\n");

    // Iterate through each address in the batch and save where applicable.
    for ($i = 0; $i < count($addressBatch); $i++) {    
      $parseSuccess = false;
      // Parse street address
      if ($parseStreetAddress) {
        $parseSuccess = parseAddress($addressBatch[$i]);  
      }

      if (!$dryrun) {
        // Save address information
        if ($parseStreetAddress || $parseSuccess) {
          if (!empty($addressBatch[$i])) {
            $address_dao = new CRM_Core_DAO_Address();
            $address_dao->id = $addressBatch[$i]['address_id'];
            $address_dao->copyValues($addressBatch[$i]);
            $address_dao->save();
            $address_dao->free();
          }
        }

        // Save custom district fields.
        if($optlist['distassign']) {
          if (isset($customFields)) {
            $customFields = CRM_Core_BAO_CustomField::getFields('Address', false, true);
          }
          if (!empty($customFields)) {
            $addressCustom = CRM_Core_BAO_CustomField::postProcess(
              $addressBatch[$i], $customFields, $addressBatch[$i]['district_id'], 'Address', true        
            );
          }
          if (!empty($addressCustom)) {
            CRM_Core_BAO_CustomValueTable::store($addressCustom, 'civicrm_address', $addressBatch[$i]['district_id']);
          }
        }  
      }        
    }
/**
DEBUG print_r
*/
//    print_r($addressBatch);    

    unset($addressBatch);
    $addressBatch = array();

    $batchProcessTime = get_elapsed_time($batchStartTime);
    echo ts("Batch processing time: " . $batchProcessTime . " s.\n");

    $batchStartTime = microtime(true);
  }
  
  echo ts("Total addresses evaluated: $totalAddresses\n");
  if ($parseStreetAddress) {
    echo ts("Addresses parsed: $totalAddressParsed\n");
    if ($unparseableContactAddress) {
      echo ts("<br />\nFollowing is the list of contacts whose address is not parsed :<br />\n");
      foreach ($unparseableContactAddress as $contactLink) {
        echo ts("%1<br />\n", array(1 => $contactLink));
      }
    }
  }

  $elapsed_time = get_elapsed_time($startTime);
  echo "Elapsed time = $elapsed_time secs\n";
  if ($totalAddresses > 0) {
    echo "Average time per address = ".($elapsed_time/$totalAddresses)." secs\n";
  }

  if (!is_cli_script()) {
    echo "</pre>\n";
  }

  mysql_free_result($res);
  return;
}

function parseAddress(&$address)
{
  if (!empty($address['street_address'])) {
    $parsedFields = CRM_Core_BAO_Address::parseStreetAddress($address['street_address']);
    
    // NYSS 5918 - Consider parsed if *either* name or number parsed
    $success = (CRM_Utils_Array::value('street_name', $parsedFields) ||
                CRM_Utils_Array::value('street_number', $parsedFields));

    if (!$success) {
      // Build contact edit url, so that user can manually fill the street address fields 
      // if the street address is not parsed, CRM-5886
      $url = CRM_Utils_System::url('civicrm/contact/add', "reset=1&action=update&cid={$address['contact_id']}");
      $unparseableContactAddress[] = " Contact ID: " . $address['contact_id'] . " <a href =\"$url\"> ". $address['street_address'] . " </a> ";
      
      // Reset element values.
      $parsedFields = array_fill_keys(array_keys($parsedFields), '');
    }
    
    $address = array_merge($address, $parsedFields);
    return $success;  
  }
  return false;
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

main();