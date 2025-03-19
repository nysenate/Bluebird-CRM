<?php

$prog = basename(__FILE__);

require_once '../civicrm/scripts/script_utils.php';
$optlist = civicrm_script_init("d", array('dryrun'), FALSE);



if ($optlist === null) {
  $stdusage = civicrm_script_usage();
  error_log("Usage: ".basename(__FILE__)."  -S <SITE> [--dryrun]\n");
  exit(1);
}

drupal_script_init();

$bbcfg = get_bluebird_instance_config();

require_once 'CRM/Core/Config.php';
$config = CRM_Core_Config::singleton();

// Define the path to your Drupal installation.
define('DRUPAL_ROOT', '/home/nate/workspace/Bluebird-CRM/drupal');

// Include the Drupal bootstrap file.
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

// load cli args
$dryrun = (!empty($optlist['dryrun'])) ? true: false;

// get instance settings
// build a PDO object
$bbcfg = get_bluebird_instance_config($optlist['site']);

// Get contacts with a birtdate greater than June 1, 2023 and also have
// a reasonable replacement in their Web Profile.
$contacts = \Civi\Api4\Contact::get(FALSE)
  ->addSelect('id', 'birth_date', 'Website_Profile.Birth_Date', 'first_name', 'last_name', 'Website_Profile.Email')
  ->addWhere('birth_date', '>', '2023-06-01')
  ->addWhere('Website_Profile.Birth_Date', '<', '2023-06-01')
  ->setLimit(1) 
  ->execute();

if ($dryrun) {
  echo "\nThis is a dry-run. No changes will be made.\n\n";
}

foreach ($contacts as $contact) {

  echo $contact['id'] . "\t" . str_pad(substr($contact['first_name'] . ' ' . $contact['last_name'],0, 20), 20, ' ') .
       "\t" . $contact['birth_date'] . "\tto\t" . $contact['Website_Profile.Birth_Date'] . "\t";

  if (! $dryrun) {
    try {
      $result = \Civi\Api4\Contact::update(FALSE)
        ->addValue('birth_date', $contact['Website_Profile.Birth_Date'])
        ->addWhere('id', '=', $contact['id'])
        ->execute();
      if ($result->count() == 1) {
        echo "UPDATED \n";
      } else {
        echo "NOT UPDATED: Unexpected result count " . $result->count() . "\n";
      }
    } catch (Exception $e) {
      echo "NOT UPDATED: Unexpected result count " . $e->getMessage() . "\n";
    }
  } else {
    echo "\n";
  }
}
