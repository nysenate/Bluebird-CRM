#!/usr/bin/php
<?php

$this_dir = dirname(__FILE__);
$root_dir = realpath("$this_dir/../../..");
require_once realpath("$this_dir/../script_utils.php");
require_once realpath("$this_dir/../bluebird_config.php");
require_once 'utils.php';
// Bootstrap CiviCRM so we can use the SAGE module
$_SERVER["HTTP_HOST"] = $_SERVER['SERVER_NAME'] = 'sd99';
require_once "$root_dir/drupal/sites/default/civicrm.settings.php";
require_once "$root_dir/civicrm/custom/php/CRM/Utils/SAGE.php";

$config = get_bluebird_config();

# Some packages required for command line parsing
add_packages_to_include_path();
$optList = get_options();
$dbconn = get_connection($config['globals']);
geocodeAddresses($optList, $dbconn);
die("Completed geocoding process.\n");


function get_options()
{
  $prog = basename(__FILE__);

  $short_opts = 'hn';
  $long_opts = array('help', 'dryrun');
  $usage = "[--help|-h] [--dryrun|-n]";

  $optList = process_cli_args($short_opts, $long_opts);
  if (!$optList || $optList['help']) {
    die("$prog $usage\n");
  }

  return $optList;
} // get_options()


function geocodeAddresses($optList, $conn)
{
  echo "[NOTICE] About to geocode addresses in the person table\n";

  //Format the row as civicrm likes to see it.
  $sql = "SELECT id, address1 as street_address, address2 as street_address2,
                 city as city, state as state_province, zip as postal_code
          FROM person WHERE district IS NULL ORDER BY id ASC";

  if (!($result = mysql_query($sql, $conn))) {
    die(mysql_error($conn)."\n$sql\n");
  }

  $geocodeCount = 0;

  while ($row = mysql_fetch_assoc($result)) {
    //geocode, dist assign and format address
    echo "[DEBUG] Geocoding: {$row['street_address']}, {$row['city']}, {$row['state_province']} {$row['postal_code']}\n";

    if ($optList['dryrun']) {
      continue;
    }

    CRM_Utils_SAGE::lookup($row);

    //Supply zero as a default so we can find the bad ones later
    $district = 0;
    if (isset($row['custom_47_-1']) && $row['custom_47_-1']) {
      $district = $row['custom_47_-1'];
      echo "[DEBUG] Address geocoded to SD=$district\n";
    }
    else {
      echo "[NOTICE] Address could not be geocoded.\n";
    }

    $sql = "UPDATE person SET district=$district WHERE id={$row['id']}";

    if (!mysql_query($sql, $conn)) {
      echo "[ERROR] District update failed for id={$row['id']}, district=$district [".mysql_error($conn)."]\n";
    }
    else if ($district > 0) {
      $geocodeCount++;
    }
  }
  echo "[NOTICE] Geocoded $geocodeCount record(s).\n";
} // geocodeAddresses()

?>
