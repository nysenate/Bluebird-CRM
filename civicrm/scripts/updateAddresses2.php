<?php

/**
* A PHP cron script to format all the addresses in the database.
*/

require_once 'script_utils.php';
define('DEFAULT_ADDRESS_BATCH', 50);
define('DEFAULT_SLOW_REQUEST_THRESHOLD', 15.0);

$geocode_stats = array();

function main()
{
  $prog = basename(__FILE__);
  $shortopts = 's:e:b:h:l:vgpdutfyzNGACTHELW';
  $longopts = array('start=', 'end=', 'batch=', 'threshold=', 'log=',
                    'validate', 'geocode', 'parse', 'distassign', 'usecoords',
                    'streetonly', 'force', 'dryrun', 'debug',
                    'senate', 'congress', 'assembly', 'county', 'town',
                    'school', 'election', 'cleg', 'ward');

  $stdusage = civicrm_script_usage();
  $usage = "
  [--start|-s START_ID]  [--end|-e END_ID]  [--batch|-b COUNT]
  [--threshold|-h SECS]
  [--log|-l [TRACE|DEBUG|INFO|WARN|ERROR|FATAL]]
  [--validate|-v]  [--geocode|-g]  [--distassign|-d]  [--parse|-p]
  [--usecoords|-u] [--streetonly|-t]
  [--force|-f]  [--dryrun|-y]  [--debug|-z]
  [--senate|-N]  [--congress|-G]  [--assembly|-A]  [--county|-C]
  [--town|-T]  [--school|-H]  [--election|-E]  [--cleg|-L]  [--ward|-W]\n";

  $optlist = civicrm_script_init($shortopts, $longopts);
  if ($optlist === null) {
    error_log("Usage: $prog  $stdusage  $usage");
    exit(1);
  }

  if (!is_cli_script()) {
    echo "<pre>";
  }

  // Log the execution of script.
  require_once 'CRM/Core/Error.php';
  CRM_Core_Error::debug_log_message('updateAddresses.php');

  // Check if street address should be parsed.
  require_once 'CRM/Core/BAO/Preferences.php';

  // Set the log level
  global $BB_LOG_LEVEL, $LOG_LEVELS;
  $BB_LOG_LEVEL = (!empty($optlist['log']) && isset($LOG_LEVELS[strtoupper($optlist['log'])]))
                  ? $LOG_LEVELS[strtoupper($optlist['log'])][0]
                  : $LOG_LEVELS['TRACE'][0];

  $address_options = CRM_Core_BAO_Setting::valueOptions(CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME, 'address_options');
  $parseAddress = CRM_Utils_Array::value('street_address_parsing',$address_options, false);
  $parseStreetAddress = false;
  if (!$parseAddress) {
    if ($optlist['parse'] == true) {
      bbscript_log('ERROR', ts('Error: You need to enable Street Address Parsing under Global Settings >> Address Settings.'));
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
  if ($optlist['geocode'] && $optlist['distassign']) {
    bbscript_log('INFO', ts("Geocoding and district assigning using $force strategy."));
  }
  else if ($optlist['geocode']) {
    bbscript_log('INFO', ts( "Geocoding using $force strategy." ));
  }
  else if ($optlist['distassign']) {
    bbscript_log('INFO', ts( "District assigning using $force strategy." ));
  }

  // Don't process if no operations are specified
  if (!$parseStreetAddress && !$optlist['geocode'] && !$optlist['distassign'] && !$optlist['validate']) {
    bbscript_log('ERROR', ts("Error:USPS correction, Geocode mapping, district assignment and Street Address Parsing are disabled. At least one option must be enabled to use this script."));
    exit(1);
  }

  $batch = ($optlist['batch']) ? $optlist['batch'] : DEFAULT_ADDRESS_BATCH;
  $threshold = ($optlist['threshold']) ? $optlist['threshold'] : DEFAULT_SLOW_REQUEST_THRESHOLD;

  bbscript_log('INFO', "Using batches of $batch addresses.");
  processContacts($parseStreetAddress, $batch, $threshold, $optlist);
} // main()


function processContacts($parseStreetAddress, $batchSize, $threshold, $optlist) {
  global $geocode_stats;

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

  bbscript_log('TRACE', "Executing query: $query\n");
  $dao = new CRM_Core_DAO();
  $db = $dao->getDatabaseConnection()->connection;
  $res = bb_mysql_query($query, $db, true);

  // Decided not to use the dao for the query because of row counting errors.
  // $dao =& CRM_Core_DAO::executeQuery($query, CRM_Core_DAO::$_nullArray);

  bbscript_log('INFO', "Address Retrieval Query time = " . get_elapsed_time($startTime) . " secs");

  $batchNum = $currentBatchSize = $totalAddressParsed = 0;
  $unparseableContactAddress = array();

  $DEBUG = ($optlist['debug']);
  $overwrite = ($optlist['force'] == 'update');
  $performUspsValidate = $optlist['validate'];
  $performGeocode = $optlist['geocode'];
  $performDistAssign = $optlist['distassign'];
  $useCoords = $optlist['usecoords'];
  $streetFileOnly = $optlist['streetonly'];
  $dryrun = $optlist['dryrun'];
  $totalRows = mysql_num_rows($res);

  $districtTypes = array('senate', 'congress', 'assembly', 'county', 'town', 'school', 'election', 'cleg', 'ward');
  $districtAssignTypes = array();
  foreach ($districtTypes as $dt) {
    if ($optlist[$dt]) {
      $districtAssignTypes[] = $dt;
    }
  }
  if (empty($districtAssignTypes)) {
    $districtAssignTypes = $districtTypes;
  }

  bbscript_log('INFO', "Iterating over {$totalRows} addresses...");

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
    if ($totalAddresses % $batchSize != 0 && $totalAddresses != $totalRows) {
      continue;
    }

    $batchNum++;

    if ($DEBUG) {
      print_r($addressBatch);
    }

    // Perform batch requests based on groups of operations requested.
    if ($performUspsValidate && $performGeocode && $performDistAssign) {
      bbscript_log('INFO', ts("Performing batch bluebird lookup #{$batchNum}..."));
      CRM_Utils_SAGE::batchLookup($addressBatch, $overwrite, $overwrite);
    }
    else if ($performGeocode && $performDistAssign) {
      bbscript_log('INFO', ts("Performing batch district assign #{$batchNum}..."));
      CRM_Utils_SAGE::batchDistAssign($addressBatch, $overwrite, $overwrite, $streetFileOnly);
    }
    else {
      if ($performUspsValidate) {
        bbscript_log('INFO', ts("Performing batch check address #{$batchNum}..."));
        CRM_Utils_SAGE::batchCheckAddress($addressBatch);
      }
      if ($performGeocode && !($performDistAssign && !$useCoords)) {
        bbscript_log('INFO', ts("Performing batch geocode #{$batchNum}..."));
        CRM_Utils_SAGE::batchGeocode($addressBatch, $overwrite);
      }
      if ($performDistAssign) {
        if ($useCoords) {
          bbscript_log('INFO', ts("Performing batch lookup using geocodes #{$batchNum}..."));
          CRM_Utils_SAGE::batchLookupFromPoint($addressBatch, $overwrite);
        }
        else if ($performGeocode) {
          bbscript_log('INFO', ts("Performing batch geocode/district assign #{$batchNum}..."));
          CRM_Utils_SAGE::batchDistAssign($addressBatch, $overwrite, $overwrite, $streetFileOnly);
        }
        else {
          bbscript_log('INFO', ts("Performing batch district assign without overwriting geocode #{$batchNum}..."));
          CRM_Utils_SAGE::batchDistAssign($addressBatch, $overwrite, false);
        }
      }
    }

    $sageProcessTime = get_elapsed_time($batchStartTime);
    bbscript_log('DEBUG', ts("SAGE processing time: " . $sageProcessTime . " s."));

    // Iterate through each address in the batch and save where applicable.
    for ($i = 0; $i < count($addressBatch); $i++) {
      $parseSuccess = false;
      // Parse street address
      if ($parseStreetAddress) {
        $parseSuccess = parseAddress($addressBatch[$i], $unparseableContactAddress);
        if ($parseSuccess) {
          $totalAddressParsed++;
        }
      }

      if (!$dryrun) {
        // Save address information
        if ($performGeocode || $parseStreetAddress || $parseSuccess) {
          updateAddress($addressBatch[$i]);
        }

        // Save custom district fields.
        if ($performDistAssign) {
          updateDistricts($addressBatch[$i], $db, $districtAssignTypes);
        }
      }
    }

    foreach($geocode_stats as $method => $count) {
      bbscript_log('INFO', "Geocode usage for $method : $count");
    }

    if ($DEBUG) {
      print_r($addressBatch);
    }

    unset($addressBatch);
    $addressBatch = array();

    $batchProcessTime = get_elapsed_time($batchStartTime);
    if ($batchProcessTime > $threshold) {
      bbscript_log('WARN', ts("Slow batch request: {$batchProcessTime} s"));
    }
    else {
      bbscript_log('DEBUG', ts("Batch processing time: {$batchProcessTime} s"));
    }

    $batchStartTime = microtime(true);
  }

  bbscript_log('INFO', "Total addresses evaluated: $totalAddresses");
  if ($parseStreetAddress) {
    bbscript_log('INFO', ts("Addresses parsed: $totalAddressParsed"));
    if (count($unparseableContactAddress) > 0) {
      bbscript_log('INFO', ts("Below is a list of all the unparsed contact addresses:"));
      foreach ($unparseableContactAddress as $upca) {
        echo $upca . "\n";
      }
    }
  }

  $elapsed_time = get_elapsed_time($startTime);
  bbscript_log('INFO', "Elapsed time = $elapsed_time secs");
  if ($totalAddresses > 0) {
    bbscript_log('INFO', "Average time per address = ".($elapsed_time/$totalAddresses)." secs");
  }

  if (!is_cli_script()) {
    echo "</pre>";
  }

  mysql_free_result($res);
  return;
} // processContacts()


function parseAddress(&$address, &$unparseableContactAddress)
{
  if (!empty($address['street_address'])) {
    $parsedFields = CRM_Core_BAO_Address::parseStreetAddress($address['street_address']);

    // NYSS 5918 - Consider parsed if *either* name or number parsed
    $success = (CRM_Utils_Array::value('street_name', $parsedFields) ||
                CRM_Utils_Array::value('street_number', $parsedFields));

    if (!$success) {
      // Build contact edit url, so that user can manually fill the street
      // address fields if the street address is not parsed, CRM-5886
      $url = CRM_Utils_System::url('civicrm/contact/add', "reset=1&action=update&cid={$address['contact_id']}");
      $unparseableContactAddress[] = " Contact ID: " . $address['contact_id'] . " <a href =\"$url\"> ". $address['street_address'] . " </a> ";

      // Reset element values.
      $parsedFields = array_fill_keys(array_keys($parsedFields), '');
    }

    $address = array_merge($address, $parsedFields);
    return $success;
  }
  return false;
} // parseAddress()


// Updates the address in the database using the DAO
function updateAddress($address)
{
  global $geocode_stats;

  if (!empty($address)) {
    $address_dao = new CRM_Core_DAO_Address();
    $address_dao->id = $address['address_id'];
    $address_dao->copyValues($address);
    $address_dao->save();
    $address_dao->free();
    bbscript_log('TRACE', "Saved civicrm_address table entry for address_id {$address['address_id']}");

    if (!empty($address['geo_method'])) {
      if (!isset($geocode_stats[$address['geo_method']])) {
        $geocode_stats[$address['geo_method']] = 0;
      }
      $geocode_stats[$address['geo_method']]++;
    }
  }
} // updateAddress()


// Updates the districts in the database using a direct SQL query
// May want to update this to use proper DAOs.
function updateDistricts($address, $db, $districtTypes)
{
  $districtColumnNames = array(
    46 => 'congressional_district_46',
    47 => 'ny_senate_district_47',
    48 => 'ny_assembly_district_48',
    49 => 'election_district_49',
    50 => 'county_50',
    51 => 'county_legislative_district_51',
    52 => 'town_52',
    53 => 'ward_53',
    54 => 'school_district_54'
  );

  $districtTypeNames = array(
    46 => 'congress',
    47 => 'senate',
    48 => 'assembly',
    49 => 'election',
    50 => 'county',
    51 => 'cleg',
    52 => 'town',
    53 => 'ward',
    54 => 'school'
  );

  $matches = array();
  $sqlUpdates = array();
  foreach ($address as $key => $value) {
    if (preg_match('/custom_(\d{2})_\d+/', $key, $matches)) {
      $districtId = $matches[1];
      $districtName = $districtTypeNames[$districtId];

      if (isset($districtId) && isset($districtColumnNames[$districtId]) && !empty($value) && in_array($districtName, $districtTypes)) {
        if ($districtId == 52) {
          $sqlUpdates[] = "{$districtColumnNames[$districtId]} = '{$value}'";
        }
        else {
          $sqlUpdates[] = "{$districtColumnNames[$districtId]} = {$value}";
        }
      }
    }
  }

  if (!empty($sqlUpdates)) {
    $query = "UPDATE civicrm_value_district_information_7 di
              SET " . implode(', ', $sqlUpdates) . "
              WHERE di.entity_id = {$address['address_id']}";
    bb_mysql_query($query, $db, false);
  }
} // updateDistricts()


// Dynamically build query based on command line args
function getQuery($optlist)
{
  $districtColumns = array(
    'congress' => 'congressional_district_46',
    'senate' => 'ny_senate_district_47',
    'assembly' => 'ny_assembly_district_48',
    'election' => 'election_district_49',
    'county' => 'county_50',
    'cleg' => 'county_legislative_district_51',
    'town' => 'town_52',
    'ward' => 'ward_53',
    'school' => 'school_district_54'
  );

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

  if ($optlist['geocode']) {
    $querySelect[] = "a.geo_code_1 as lat";
    $querySelect[] = "a.geo_code_2 as lon";

    if (!$optlist['force']) {
      $whereClause .= "
        AND (( a.geo_code_1 is null OR a.geo_code_1 = 0 OR
               a.geo_code_2 is null OR a.geo_code_2 = 0 ) AND
               a.country_id is not null)";
    }
  }

  if ($optlist['distassign']) {
    $distSelect = array();
    foreach(array_values($districtColumns) as $col) {
      $distSelect[] = "d.$col";
    }
    $querySelect[] = implode(', ', $distSelect);
    $querySelect[] = "d.id as d_id";

    if ($optlist['usecoords'] && !$optlist['geocode']) {
      $querySelect[] = "a.geo_code_1 as lat";
      $querySelect[] = "a.geo_code_2 as lon";
    }

    if (!$optlist['force']) {
      $whereDist = array();
      $assignTypes = array();
      foreach (array_keys($districtColumns) as $dt) {
        if ($optlist[$dt]) {
          $assignTypes[] = $dt;
        }
      }

      if (empty($assignTypes)) {
        $assignTypes = array('senate', 'congress', 'assembly', 'county', 'school', 'town');
      }

      foreach($assignTypes as $dt) {
        $whereDist[] = "d.{$districtColumns[$dt]} is null OR d.{$districtColumns[$dt]} = \"\"";
      }

      $whereClause .= " AND (" . implode(' OR ', $whereDist) . ")";
    }
  }

  $query = "SELECT " . implode( ', ', $querySelect ) . "
    FROM       civicrm_contact  c
    INNER JOIN civicrm_address                a ON a.contact_id = c.id
    LEFT  JOIN civicrm_country                o ON a.country_id = o.id
    LEFT  JOIN civicrm_state_province         s ON a.state_province_id = s.id
    LEFT  JOIN civicrm_value_district_information_7 d ON a.id = d.entity_id
    WHERE      {$whereClause}
    ORDER BY a.id
    ";

  return $query;
} // getQuery()


function get_address_line(&$dao_p)
{
  return 'ID #'.$dao_p->address_id.': '.$dao_p->street_address.', '.$dao_p->city.', '.$dao_p->state.' '.$dao_p->postal_code;
} // get_address_line()


main();
