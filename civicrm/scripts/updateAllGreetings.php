<?php
/*
** Project: BluebirdCRM
** Author: Ken Zalewski
** Organization: New York State Senate
** Date: 2011-08-30
** Revised: 2011-09-19
**
** Using this script you can update Email Greetings, Postal Greetings,
** and Addressee for a specific contact type
**
** params for this script
** ct=Individual or ct=Household or ct=Organization (ct = contact type)
*/

  
require_once 'script_utils.php';

define('BATCHSIZE', 250);

error_reporting(E_ERROR | E_PARSE | E_WARNING);

function run()
{
  $prog = basename(__FILE__);
  $shortopts = 'c:nfq:t';
  $longopts = array('ct=', 'dry-run', 'force', 'quiet', 'idtbl=');
  $stdusage = civicrm_script_usage();
  $usage = "[--ct|-c {Individual|Household|Organization}] [--dry-run|-n] [--force|-f] [--quiet|-q] [--idtbl TABLENAME|-t]";
  $contactOpts = array(
    'i' => 'Individual',
    'h' => 'Household',
    'o' => 'Organization'
  );

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
  CRM_Core_Error::debug_log_message('updateAllGreetings.php');

  require_once 'CRM/Core/Config.php';
  $config = CRM_Core_Config::singleton();

  $contactType = null;
  if (!empty($optlist['ct'])) {
    $contactOptIdx = strtolower($optlist['ct'][0]);
    if (isset($contactOpts[$contactOptIdx])) {
      $contactType = $contactOpts[$contactOptIdx];
    }
    else {
      //CRM_Core_Error::fatal( ts('Invalid Contact Type.') );
      echo ts("$prog: {$optlist['ct']}: Invalid Contact Type.\n");
      exit(1);
    }
  }

  require_once 'CRM/Contact/BAO/Contact.php';
  $dao = new CRM_Contact_BAO_Contact();

  if ($contactType) {
    $dao->contact_type = $contactType;
  }

  if ($optlist['force'] == FALSE) {
    $dao->whereAdd("
      addressee_display IS NULL OR
      addressee_display='' OR
      email_greeting_display IS NULL OR
      email_greeting_display='' OR
      postal_greeting_display IS NULL OR
      postal_greeting_display=''
    ");
  }

  //7247 option to restrict by IDs in temp table
  if ( !empty($optlist['idtbl']) ) {
    //make sure table exists
    $idTbl = $optlist['idtbl'];
    if ( CRM_Core_DAO::singleValueQuery("SHOW TABLES LIKE '{$idTbl}'") ) {
      //limit to ids found in the import set
      $dao->whereAdd("id IN (SELECT id FROM {$idTbl})");
    }
  }

  $dao->find(false);
  echo "[{$optlist['site']}] Executed query; about to update greetings for ".$dao->count()." matching contacts...\n";
  $cnt = 0;

  if ($optlist['dry-run'] == true) {
    echo "(The dry-run option is enabled. No contacts will be updated.)\n";
  }
  else {
    require_once 'CRM/Core/Transaction.php';

    while ($dao->fetch()) {
      if ($cnt % BATCHSIZE == 0) {
        if (isset($transaction)) {
          $transaction->commit();
          unset($transaction);
        }
        $transaction = new CRM_Core_Transaction();
      }

      //make sure we have a contact type
      if ( empty($dao->contact_type) ) {
        echo "Contact ID {$dao->id} has no contact type set. We are unable to set the greeting values.\n";
        flush();
        ob_flush();
        continue;
      }

      if ($optlist['quiet'] == FALSE) {
        echo "Processing contact ID {$dao->id} (type={$dao->contact_type}) {$dao->display_name}\n";
        ob_end_flush();
        ob_flush();
        flush();
        ob_start();
      }
      CRM_Contact_BAO_Contact::processGreetings($dao, TRUE);
      $cnt++;
    }

    if (isset($transaction)) {
      $transaction->commit();
      unset($transaction);
    }
  }

  //7247 remove temp table
  if ( !empty($idTbl) ) {
    $sql = "DROP TABLE IF EXISTS {$idTbl};";
    CRM_Core_DAO::executeQuery($sql);
  }

  echo "[{$optlist['site']}] Finished processing greetings for $cnt contacts.\n";
}

run();
