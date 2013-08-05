#!/usr/bin/php
<?php

require_once realpath(dirname(__FILE__).'/../script_utils.php');
require_once realpath(dirname(__FILE__).'/../bluebird_config.php');
require_once 'nysenate_api/xmlrpc-api.inc';
require_once 'nysenate_api/xmlrpc-api-signups.inc';
require_once 'utils.php';

# Some packages required for command line parsing
add_packages_to_include_path();
$optList = get_options();
$config = get_bluebird_config();

$env = array(
  'domain' => $config['globals']['signups.api.domain'],
  'apikey' => $config['globals']['signups.api.key'],
  'conn' => get_connection($config['globals'])
);

if ($optList['senators'] || $optList['all']) {
  updateSenators($optList, $env);
}

if ($optList['committees'] || $optList['all']) {
  updateCommittees($optList, $env);
}

if ($optList['signups'] || $optList['all']) {
  updateSignups($optList, $env);
}

if ($optList['geocode'] || $optList['all']) {
  geocodeAddresses($optList, $env);
}


function get_options()
{
  $prog = basename(__FILE__);
  $script_dir = dirname(__FILE__);

  $short_opts = 'hascgub:f:n';
  $long_opts = array('help','all','senators','committees','geocode','signups','batch=','first=','dryrun');
  $usage = "[--help|-h] [--all|-a] [--senators|-s] [--committees|-c] [--geocode|-g] [--signups|-u] [--batch|-b BATCH] [--first|-f FIRST_PERSON_NID] [--dryrun|-n]";

  $optList = process_cli_args($short_opts, $long_opts);
  if (!$optList || $optList['help'] ||
      !($optList['all'] || $optList['signups'] || $optList['geocode']
        || $optList['senators'] || $optList['committees'])) {
    die("$prog $usage\n");
  }

  return $optList;
} // get_options()


function updateSenators($optList, $env)
{
  // Fetch all the senators from NYSenate.gov
  list($domain, $apikey, $conn) = array_values($env);
  $view_service = new viewsGet($domain, $apikey);
  $senators = $view_service->get(array('view_name'=>'senators'));

  // Retrieve all senator ids from the database
  $inactive_senators = array();
  $result = mysql_query("SELECT nid, title FROM senator WHERE active=1", $conn);
  while ($row = mysql_fetch_assoc($result)) {
    $inactive_senators[$row['nid']] = $row['title'];
  }

  foreach ($senators as $senator) {
    $node_service = new nodeGet($domain, $apikey);
    $senatorData = $node_service->get(array('nid'=>$senator['nid']));
    unset($inactive_senators[$senator['nid']]);

    //Clean basic information
    $nid = (int)$senatorData['nid'];
    $title = mysql_real_escape_string($senatorData['title'], $conn);

    //Get the district number
    $node_service = new nodeGet($domain, $apikey);
    $districtData = $node_service->get(array('nid'=>$senatorData['field_senators_district'][0]['nid']));
    $district = (int)$districtData['field_district_number'][0]['value'];

    //Get the list id
    $list_title = $senatorData['field_bronto_mailing_list'][0]['value'];
    if (!$list_title) {
      echo "Skipping senator: D$district\t$title [$nid]; no mailing list found.\n";
    }
    else if ($optList['dryrun']) {
      echo "[DRYRUN] Updating senator: D$district\t$title [$nid]; list: $list_title\n";
    }
    else {
      $list_id = get_or_create_list($list_title, $conn);

      //Insert/Update the senator
      echo "Updating senator: D$district\t$title [$nid]; list: $list_title\n";
      $sql = "INSERT INTO senator (nid, title, district, list_id, active) VALUES ($nid, '$title', $district, $list_id, 1) ON DUPLICATE KEY UPDATE title='$title', district=$district, list_id=$list_id, active=1";
      if (!mysql_query($sql,$conn)) {
        die(mysql_error($conn)."\n$sql\n");
      }
    }
  }

  if (count($inactive_senators) > 0) {
    if ($optList['dryrun']) {
      echo "[DRYRUN] Deactivating senators: ".implode(',', array_values($inactive_senators))."\n";

    }
    else {
      // Mark all senators not updated as inactive
      echo "Deactivating senators: ".implode(',', array_values($inactive_senators))."\n";
      mysql_query("UPDATE senator SET active=0 WHERE nid IN (".implode(array_keys($inactive_senators)).")", $conn);
    }
  }
} // updateSenators()


function updateCommittees($optList, $env)
{
  // Fetch all the committees from NYSenate.gov
  list($domain, $apikey, $conn) = array_values($env);
  $view_service = new viewsGet($domain, $apikey);
  $committees = $view_service->get(array('view_name'=>'committees'));

  // Retrieve all committee ids from the database
  $inactive_committees = array();
  $result = mysql_query("SELECT nid, title FROM committee WHERE active=1", $conn);
  while ($row = mysql_fetch_assoc($result)) {
    $inactive_committees[$row['nid']] = $row['title'];
  }

  foreach ($committees as $committee) {
    $node_service = new nodeGet($domain, $apikey);
    $committeeData = $node_service->get(array('nid'=>$committee['nid']));
    unset($inactive_committees[$committee['nid']]);

    //Clean basic information
    $nid = (int)$committeeData['nid'];
    $chair_nid = (int)$committeeData['field_chairs'][0]['nid'];
    $title = mysql_real_escape_string($committeeData['title'], $conn);

    //Get chair information for log entries
    $node_service = new nodeGet($domain, $apikey);
    if ($chair_nid) {
      $chairData = $node_service->get(array('nid'=>$chair_nid));
      $chair_title = $chairData['title'];
    }
    else {
      $chair_title = "None";
      $chair_nid = "NULL";
    }

    //Get the list id
    $list_title = $committeeData['field_bronto_mailing_list'][0]['value'];
    if (!$list_title) {
      echo "Skipping committee: $title [$nid], chair: $chair_title, no mailing list found.\n";
    }
    else if ($optList['dryrun']) {
      echo "[DRYRUN] Updating committee: $title [$nid], chair: $chair_title [$chair_nid]\n";
    }
    else {
      $list_id = get_or_create_list($list_title, $conn);

      //Insert/Update the committee
      echo "Updating committee: $title [$nid], chair: $chair_title [$chair_nid]\n";
      $sql = "INSERT INTO committee (nid, title, chair_nid, list_id) VALUES ($nid, '$title', $chair_nid, $list_id) ON DUPLICATE KEY UPDATE title='$title', chair_nid=$chair_nid, list_id=$list_id";
      if (!mysql_query($sql, $conn)) {
        die(mysql_error($conn)."\n".$sql);
      }
    }
  }

  if (count($inactive_committees) > 0) {
    if ($optList['dryrun']) {
      echo "[DRYRUN] Deactivating committees: ".implode(',', array_values($inactive_committees))."\n";
    }
    else {
      // Mark all senators not updated as inactive
      echo "Deactivating committees: ".implode(',', array_values($inactive_committees))."\n";
      mysql_query("UPDATE committee SET active=0 WHERE nid IN (".implode(array_keys($inactive_committees)).")", $conn);
    }
  }
} // updateCommittees()


function updateSignups($optList, $env)
{
  $issue_ids = array();
  list($domain, $apikey, $conn) = array_values($env);
  $limit = $optList['batch'] ? $optList['batch'] : 500; //default to 500

  //Starting point can be user supplied or queried from the database for
  //new contacts only
  if ($optList['first'] !== NULL) {
    $start_id = (int)$optList['first'];
  }
  else {
    $start_id = get_start_id($conn)+1;
  }

  while (true) {
    $old_start_id = $start_id;
    echo "Fetching the new records starting from ".($start_id?$start_id:0).".\n";
    $signup_service = new SignupGet($domain, $apikey);
    $signupData = $signup_service->get(array(
                                        'start_date' => null,
                                        'end_date' => null,
                                        'start_sid' => $start_id,
                                        'end_sid' => null,
                                        'limit' => $limit
                                      ));

    if (isset($signupData['faultCode'])) {
      var_dump($signupData);
      exit();
    }

    $count = count($signupData['accounts']);
    if ($count == 0) {
      echo "No Records Found.\n";
      break;
    }

    if ($optList['dryrun']) {
     echo "[DRYRUN] Retrieved signups from website: ".print_r($signupData, true)."\n";
     return;
    }

    echo "Processing batch of $count senator/committee accounts...\n";
    foreach ($signupData['accounts'] as $account) {
      //Output a quick warning letting us know something weird is happening
      $num_lists = count($account['lists']);
      if ($num_lists > 1) {
        echo "account['name']={$account['name']} has {$num_lists} lists associated with it.\n";
      } elseif ($num_lists == 0) {
        //There were no lists on this account... This is BAD.
        echo "ERROR: Account with no lists found!!! ".print_r($account, TRUE)."\n";
      }

      $list_id = get_or_create_list($account['lists'][0]['name'], $conn);

      //Store all the contacts in the database; get_or_create_person()
      //makes new rows as needed. Associate the contact with the last
      //list associated with this account. I currently believe each
      //account only has one list associated with it.
      foreach ($account['contacts'] as $contact) {
        list($person_id, $person_nid) = get_or_create_person($contact, $conn);

        //Move up our starting point as necessary
        if ($person_nid >= $start_id) {
          $start_id = $person_nid + 1;
        }

        foreach ($contact['issues'] as $issue) {
          $issue = mysql_real_escape_string($issue, $conn);
          $issue_id = get_or_create_issue($issue, $conn);
          $sql = "INSERT IGNORE INTO subscription (person_id,issue_id) VALUES ($person_id, $issue_id)";
          if (!mysql_query($sql, $conn)) {
            die(mysql_error($conn)."\n".$sql);
          }
        }

        $sql = "INSERT IGNORE INTO signup (list_id,person_id) VALUES ($list_id, $person_id)";
        if (!mysql_query($sql, $conn)) {
          die(mysql_error($conn)."\n".$sql);
        }
      }
    }
    echo "Inserted $old_start_id to $start_id signup records.\n";
  }
} // updateSignups()


function geocodeAddresses($optList, $env) {
  // Bootstrap CiviCRM so we can use the SAGE module
  $conn = $env['conn'];
  $root = dirname(dirname(dirname(dirname(__FILE__))));
  $_SERVER["HTTP_HOST"] = $_SERVER['SERVER_NAME'] = 'sd99';
  require_once "$root/drupal/sites/default/civicrm.settings.php";
  require_once "$root/civicrm/custom/php/CRM/Utils/SAGE.php";

  //Format the row as civicrm likes to see it.
  $sql = "SELECT id,
          address1 as street_address,
          address2 as street_address2,
          city as city,
          state as state_province,
          zip as postal_code
      FROM person WHERE district IS NULL ORDER BY id ASC";

  if (!($result = mysql_query($sql, $conn))) {
    die(mysql_error($conn)."\n".$sql);
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
      echo "[DEBUG] Address geocoded to Senate District $district\n";
    }
    else {
      echo "[NOTICE] Address --^ could not be geocoded.\n";
    }

    $sql = "UPDATE person SET district=$district WHERE id={$row['id']}";

    if (!mysql_query($sql, $conn)) {
     echo "[ERROR] District update failed for id={$row['id']}, district=$district [".mysql_error($conn)."]\n";
    }
    else if ($district > 0) {
     $geocodeCount++;
    }
  }
  echo "[NOTICE] Geocoded $geocodeCount record(s).\n\n";
} // geocodeAddresses()


function get_start_id($conn) {
  if (!$result = mysql_query("SELECT max(nid) as max_id FROM person",$conn)) {
    die(mysql_error($conn)."\n".$sql);
  }

  $row = mysql_fetch_assoc($result);
  return $row['max_id'];
} // get_start_id()


function get_or_create_person($contact, $conn) {
  $nid = (int)$contact['id'];
  $first_name = mysql_real_escape_string($contact['firstName'],$conn);
  $last_name = mysql_real_escape_string($contact['lastName'],$conn);
  $address1 = mysql_real_escape_string($contact['address1'],$conn);
  $address2 = mysql_real_escape_string($contact['address2'],$conn);
  $city = mysql_real_escape_string($contact['city'],$conn);
  $state = mysql_real_escape_string($contact['state'],$conn);
  $zip = mysql_real_escape_string($contact['zip'],$conn);
  $phone = mysql_real_escape_string($contact['phoneMobile'],$conn);
  $email = mysql_real_escape_string($contact['email'],$conn);
  $status = mysql_real_escape_string($contact['status'],$conn);
  $created = date('Y-m-d H:i:s',(int)$contact['created']);
  $modified = date('Y-m-d H:i:s',(int)$contact['modified']);

  $sql = "SELECT id FROM person WHERE nid=$nid";
  if ($result = mysql_query($sql, $conn)) {
    //Existing Person
    if ($row = mysql_fetch_assoc($result)) {
      return array($row['id'], $nid);
    //New Person
    } else {
      $sql = "
        INSERT INTO person
          (nid, first_name, last_name, address1, address2, city, state, zip, phone, email, status, created, modified)
        VALUES
          ($nid,'$first_name','$last_name','$address1','$address2','$city','$state','$zip','$phone','$email','$status','$created','$modified')
      ";
      if ($result = mysql_query($sql, $conn)) {
        return array(mysql_insert_id($conn), $nid);
      }
    }
  }

  die(mysql_error($conn)."\n".$sql);
} // get_or_create_person()

?>
