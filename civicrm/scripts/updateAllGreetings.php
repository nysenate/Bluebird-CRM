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
  $longopts = ['ct=', 'dry-run', 'force', 'quiet', 'idtbl='];
  $stdusage = civicrm_script_usage();
  $usage = "[--ct|-c {Individual|Household|Organization}] [--dry-run|-n] [--force|-f] [--quiet|-q] [--idtbl TABLENAME|-t]";
  $contactOpts = [
    'i' => 'Individual',
    'h' => 'Household',
    'o' => 'Organization'
  ];

  $optlist = civicrm_script_init($shortopts, $longopts);
  if ($optlist === null) {
    error_log("Usage: $prog  $stdusage  $usage");
    exit(1);
  }

  if (!is_cli_script()) {
    echo "<pre>\n";
  }

  //log the execution of script
  CRM_Core_Error::debug_log_message('updateAllGreetings.php');

  require_once 'CRM/Core/Config.php';
  CRM_Core_Config::singleton();

  //Civi::log()->debug(__FUNCTION__, ['optlist' => $optlist]);

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

  //get greeting defaults
  $greetings = [
    'Individual' => [
      'addressee' => CRM_Core_OptionGroup::values('addressee',
        NULL, NULL, NULL, 'AND v.filter = 1 AND is_default = 1'),
      'email' => CRM_Core_OptionGroup::values('email_greeting',
        NULL, NULL, NULL, 'AND v.filter = 1 AND is_default = 1'),
      'postal' => CRM_Core_OptionGroup::values('postal_greeting',
        NULL, NULL, NULL, 'AND v.filter = 1 AND is_default = 1'),
    ],
    'Household' => [
      'addressee' => CRM_Core_OptionGroup::values('addressee',
        NULL, NULL, NULL, 'AND v.filter = 2 AND is_default = 1'),
      'email' => CRM_Core_OptionGroup::values('email_greeting',
        NULL, NULL, NULL, 'AND v.filter = 2 AND is_default = 1'),
      'postal' => CRM_Core_OptionGroup::values('postal_greeting',
        NULL, NULL, NULL, 'AND v.filter = 2 AND is_default = 1'),
    ],
    'Organization' => [
      'addressee' => CRM_Core_OptionGroup::values('addressee',
        NULL, NULL, NULL, 'AND v.filter = 3 AND is_default = 1'),
      'email' => CRM_Core_OptionGroup::values('email_greeting',
        NULL, NULL, NULL, 'AND v.filter = 3 AND is_default = 1'),
      'postal' => CRM_Core_OptionGroup::values('postal_greeting',
        NULL, NULL, NULL, 'AND v.filter = 3 AND is_default = 1'),
    ],
  ];
  //CRM_Core_Error::debug_var('$greetings', $greetings);

  //get prefixes/suffixes
  $prefixes = \Civi\Api4\Contact::getFields()
    ->setLoadOptions(TRUE)
    ->addWhere('name', '=', 'prefix_id')
    ->addSelect('options')
    ->execute()
    ->single();
  $prefixes = $prefixes['options'];

  $suffixes = \Civi\Api4\Contact::getFields()
    ->setLoadOptions(TRUE)
    ->addWhere('name', '=', 'suffix_id')
    ->addSelect('options')
    ->execute()
    ->single();
  $suffixes = $suffixes['options'];

  //Civi::log()->debug(__FUNCTION__, ['$prefixes' => $prefixes, '$suffixes' => $suffixes]);

  $replacementStrings = [
    //'{contact.prefix_id:label}' => 'prefix_id',
    '{contact.first_name}' => 'first_name',
    '{contact.middle_name}' => 'middle_name',
    '{contact.last_name}' => 'last_name',
    //'{contact.suffix_id:label}' => 'suffix_id',
    '{contact.organization_name}' => 'organization_name',
    '{contact.household_name}' => 'household_name',
    '{' => '',
    '}' => '',
  ];

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
  if (!empty($optlist['idtbl'])) {
    //make sure table exists
    $idTbl = $optlist['idtbl'];
    if (CRM_Core_DAO::singleValueQuery("SHOW TABLES LIKE '{$idTbl}'")) {
      //limit to ids found in the import set
      $dao->whereAdd("id IN (SELECT id FROM {$idTbl})");
    }
  }

  $dao->find(FALSE);
  echo "[{$optlist['site']}] Executed query; about to update greetings for ".$dao->count()." matching contacts...\n";
  $cnt = 0;

  if ($optlist['dry-run'] == true) {
    echo "(The dry-run option is enabled. No contacts will be updated.)\n";
  }
  else {
    require_once 'CRM/Core/Transaction.php';

    while ($dao->fetch()) {
      //Civi::log()->debug(__FUNCTION__, ['$dao' => $dao]);

      if ($cnt % BATCHSIZE == 0) {
        if (isset($transaction)) {
          $transaction->commit();
          unset($transaction);
        }
        $transaction = new CRM_Core_Transaction();
      }

      //make sure we have a contact type
      if (empty($dao->contact_type)) {
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

      //set defaults
      $dao->addressee_id = (!empty($dao->addressee_id)) ? $dao->addressee_id : key($greetings[$dao->contact_type]['addressee']);
      $dao->email_greeting_id = (!empty($dao->email_greeting_id)) ? $dao->email_greeting_id : key($greetings[$dao->contact_type]['email']);
      $dao->postal_greeting_id = (!empty($dao->postal_greeting_id)) ? $dao->postal_greeting_id : key($greetings[$dao->contact_type]['postal']);

      $greetingTemplates = array_filter([
        'email_greeting_display' => CRM_Contact_BAO_Contact::getTemplateForGreeting('email_greeting', $dao),
        'postal_greeting_display' => CRM_Contact_BAO_Contact::getTemplateForGreeting('postal_greeting', $dao),
        'addressee_display' => CRM_Contact_BAO_Contact::getTemplateForGreeting('addressee', $dao),
      ]);
      //Civi::log()->debug(__FUNCTION__, ['greetingTemplate' => $greetingTemplates]);

      //handle replacements
      $sqlUpdates = [
        "addressee_id = {$dao->addressee_id}",
        "email_greeting_id = {$dao->email_greeting_id}",
        "postal_greeting_id = {$dao->postal_greeting_id}",
      ];
      $sqlParams = [
        1 => [$dao->id, 'Positive'],
      ];
      $paramsCounter = 2;

      foreach ($greetingTemplates as $field => $greetingDisplay) {
        $greetingDisplay = str_replace('{contact.prefix_id:label}', $prefixes[$dao->prefix_id], $greetingDisplay);
        $greetingDisplay = str_replace('{contact.suffix_id:label}', $suffixes[$dao->suffix_id], $greetingDisplay);

        foreach ($replacementStrings as $string => $replace) {
          $greetingDisplay = str_replace($string, $dao->$replace, $greetingDisplay);
          //Civi::log()->debug(__FUNCTION__, ['$greetingDisplay' => $greetingDisplay]);
        }

        $sqlUpdates[] = "{$field} = %{$paramsCounter}";
        $sqlParams[$paramsCounter] = [trim(str_replace('  ', ' ', $greetingDisplay)), 'String'];
        $paramsCounter++;
      }

      $sqlUpdateString = implode(', ', $sqlUpdates);
      /*Civi::log()->debug(__FUNCTION__, [
        '$sqlUpdates' => $sqlUpdates,
        '$sqlUpdateString' => $sqlUpdateString,
        '$sqlParams' => $sqlParams,
      ]);*/

      CRM_Core_DAO::executeQuery("
        UPDATE civicrm_contact
        SET {$sqlUpdateString}
        WHERE id = %1
      ", $sqlParams);

      //don't use the core function as it's too slow
      //CRM_Contact_BAO_Contact::processGreetings($dao);
      $cnt++;
    }

    if (isset($transaction)) {
      $transaction->commit();
      unset($transaction);
    }
  }

  //7247 remove temp table
  if (!empty($idTbl)) {
    $sql = "DROP TABLE IF EXISTS {$idTbl};";
    CRM_Core_DAO::executeQuery($sql);
  }

  echo "[{$optlist['site']}] Finished processing greetings for $cnt contacts.\n";
}

run();
