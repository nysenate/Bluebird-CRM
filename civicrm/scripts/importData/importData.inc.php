<?php

error_reporting(E_ALL & ~E_NOTICE);

//no limit
set_time_limit(0);

require_once 'senate.constants.php';
require_once 'lib.inc.php';

$prog = $argv[0];
$task = "import";
$instance = $importSet = $importDir = "";
$sourceDesc = "omis";
$startID = 0;

if (count($argv) <= 1) {
  die("Usage: $prog [options] instanceName importSet\nwhere [options] are:\n  -t task\n  -d importDir\n  -s sourceDesc\n  -i startID\n");
}

for ($i = 1; $i < count($argv); $i++) {
  $arg = $argv[$i];
  if ($arg == '-d') {
    $importDir = $argv[++$i];
  }
  else if ($arg == '-t') {
    $task = strtolower($argv[++$i]);
  }
  else if ($arg == '-s') {
    $sourceDesc = $argv[++$i];
  }
  else if ($arg == '-i') {
    $startID = $argv[++$i];
  }
  else if (substr($arg, 0, 1) == '-') {
    die("$prog: $arg: Invalid option\n");
  }
  else if (empty($instance)) {
    $instance = strtolower($arg);
  }
  else {
    $importSet = $arg;
  }
}

if (empty($instance) || empty($importSet)) {
  die("$prog: Must specify an instance and an importSet.\n");
}

if (empty($importDir)) {
  $importDir = RAYIMPORTDIR.$importSet;
}

if (!file_exists($importDir)) {
  die("$prog: $importDir: Directory not found\nMust specify a valid import directory.\n");
}

define('CIVICRM_CONFDIR', RAYROOTDIR.'sites/default');
if (putenv("INSTANCE_NAME=$instance") == false) {
  die("Unable to set INSTANCE_NAME in environment.\n");
}

require_once RAYCIVIPATH.'civicrm.config.php';
require_once 'CRM/Core/Config.php';
require_once 'CRM/Contact/BAO/Contact.php';
require_once 'CRM/Core/BAO/CustomValueTable.php';
require_once 'CRM/Core/BAO/Tag.php';

$config =& CRM_Core_Config::singleton();
$session =& CRM_Core_Session::singleton();

//set the user this data will be imported as
$session->set('userID', 1);

//turn off key checks for speed. requires data to be accurate
CRM_Core_DAO::executeQuery("SET FOREIGN_KEY_CHECKS=0;", CRM_Core_DAO::$_nullArray);

markTime();

global $aStates;
getStates();

switch ($task) {
  case "parseonly":
    parseData($importSet, $importDir, $startID, $sourceDesc);
    break;
  case "updatestates":
    include('updateStates.php');
    updateStates();
    break;
  case "loaddbonly":
    loadDB($importSet, $importDir);
    break;
  case "import":
    if (parseData($importSet, $importDir, $startID, $sourceDesc)) {
      loadDB($importSet, $importDir);
    }
    break;
  case "showfields":
    showExportableFields();
    break;
  case "importissuelist":
    if (!confirmCheck("importissuelist", "CAREFUL, ONLY ADVISABLE ON A BLANK DATABASE!")) exit;
    importIssueCodes($importSet);
    break;
  //as a default, call the update which takes different params
  default:
    update($task, $importSet, $importDir, $sourceDesc);
    break;
}

cLog(0, 'info', "DONE IN ".prettyFromSeconds(getElapsed()));
exit;


//--------------------------------------------
//functions

function showExportableFields()
{
  $f = CRM_Contact_BAO_Contact::exportableFields('Individual');
  print_r($f);
  echo "\ncustom fields: \n\n";
  foreach ($f as $key => $val) {
    if (stristr($key, 'custom')) {
      echo $key." => ".$val['title']."\n";
    }
  }
} // showExportableFields()



function loadDB($importSet, $importDir)
{
  global $bluebird_db_info;

  //set some keychecks off for speed.
  $dao = &CRM_Core_DAO::executeQuery("SET foreign_key_checks = 0;", CRM_Core_DAO::$_nullArray);

  //$opts = "FIELDS TERMINATED BY '\\t' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '\\n' IGNORE 1 LINES";
  $opts = "FIELDS TERMINATED BY '\\t' LINES TERMINATED BY '\\n'";

  foreach ($bluebird_db_info as $name => $db_info) {
    $abbrev = $db_info['abbrev'];
    $table = $db_info['table'];
    $colstr = implode(',', $db_info['cols']);
    $fname = $importDir.'/'.$importSet.'-'.$abbrev.'.tsv';
    if (file_exists($fname) == false) {
      cLog(0, 'error', "Unable to import data into table '$table'; file '$fname' not found");
    }
    else {
      cLog(0, 'info', "importing $name records from '$fname' into database table '$table'");
      cLog(0,'info', "LOAD DATA LOCAL INFILE '$fname' REPLACE INTO TABLE '$table' $opts ({$colstr});");
      $dao = &CRM_Core_DAO::executeQuery("LOAD DATA LOCAL INFILE '$fname' REPLACE INTO TABLE $table $opts ({$colstr});", CRM_Core_DAO::$_nullArray);
    }
  }
} // loadDB()



function parseData($importSet, $importDir, $startID, $sourceDesc)
{
  global $aSuffixLookup;
  global $aRelLookup;
  global $omis_ct_fields;
  global $omis_nt_fields;
  global $omis_cs_fields;
  global $omis_is_fields;
  global $omis_ext_fields;
  global $bluebird_db_info;
  global $aStates;
  
  //civi prefixes
  $aPrefix = getOptions('individual_prefix');

  //civi suffixes
  $aSuffix = getOptions('individual_suffix');

  $session =& CRM_Core_Session::singleton();

  $infiles = get_import_files($importDir, $importSet);
  if (!$infiles) {
    echo "Unable to find all necessary import files.\n";
    return null;
  }

  foreach ($bluebird_db_info as $name => $db_info) {
    $abbrev = $db_info['abbrev'];
    $fname = $importDir.'/'.$importSet.'-'.$abbrev.'.tsv';
    $fp = fopen($fname, 'w');
    if ($fp === false) {
      cLog(0, 'error', "Unable to open '$fname' for writing");
    }
    else {
      $fout[$name] = $fp;
    }
  }

  //initialize the arrays, skipping header lines
  $skipped = 0;
  $done = false;
  do {
    $ctRow = getLineAsAssocArray($infiles['contacts'], DELIM, $omis_ct_fields);
    if ($ctRow && is_numeric($ctRow['KEY'])) {
      $ctRow['KEY'] = intval($ctRow['KEY']);
      if ($ctRow['KEY'] >= $startID) {
        $done = true;
      }
      else {
        $skipped++;
      }
    }
  } while ($ctRow && !$done);

  $done = false;
  do {
    $ntRow = getLineAsAssocArray($infiles['notes'], DELIM, $omis_nt_fields);
    if ($ntRow && is_numeric($ntRow['KEY'])) {
      $ntRow['KEY'] = intval($ntRow['KEY']);
      if ($ntRow['KEY'] >= $startID) {
        $done = true;
      }
    }
  } while ($ntRow && !$done);

  $done = false;
  do {
    $csRow = getLineAsAssocArray($infiles['cases'], DELIM, $omis_cs_fields);
    if ($csRow && is_numeric($csRow['KEY'])) {
      $csRow['KEY'] = intval($csRow['KEY']);
      if ($csRow['KEY'] >= $startID) {
        $done = true;
      }
    }
  } while ($csRow && !$done);

  $done = false;
  do {
    $isRow = getLineAsAssocArray($infiles['issues'], DELIM, $omis_is_fields);
    if ($isRow && is_numeric($isRow['KEY'])) {
      $isRow['KEY'] = intval($isRow['KEY']);
      if ($isRow['KEY'] >= $startID) {
        $done = true;
      }
    }
  } while ($isRow && !$done);

  if (!$ctRow) {
    cLog(0, 'INFO', "Error opening files!");
    return false;
  }

  //count number of lines in the file
  $numContacts = countFileLines($infiles['contacts']) - $skipped; 

  cLog(0, 'info', "importing {$numContacts} lines starting with $startID, skipped $skipped");
  cLog(0, 'info', "starting OMIS IDs: ct=".$ctRow['KEY'].",nt=".$ntRow['KEY'].",cs=".$csRow['KEY'].",is=".$isRow['KEY']);

  //get the max contactID from civi
  $dao = &CRM_Core_DAO::executeQuery("SELECT max(id) as maxid from civicrm_contact;", CRM_Core_DAO::$_nullArray);
  $dao->fetch();
  $contactID = $dao->maxid;
  cLog(0, 'info', "starting contactID will be ".($contactID+1));

  $dao = &CRM_Core_DAO::executeQuery("SELECT max(id) as maxid from civicrm_address;", CRM_Core_DAO::$_nullArray);
  $dao->fetch();
  $addressID = $dao->maxid;
  cLog(0, 'info', "starting addressID will be ".($addressID+1));

  $dao = &CRM_Core_DAO::executeQuery("SELECT max(id) as maxid from civicrm_activity;", CRM_Core_DAO::$_nullArray);
  $dao->fetch();
  $activityID = $dao->maxid;
  cLog(0, 'info', "starting activityID will be ".($activityID+1));

  // Array that maps tag name to tagID.
  $aTagsByName = array();
  // Array that maps tagID to its parent tagID.
  $aTagsByID = array();
  // Array that stores hierarchy tags that could not be mapped.
  $aUnsavedTags = array();

  // load all tags, and get max tag ID
  $tagID = getAllTags($aTagsByName, $aTagsByID);
  cLog(0, 'info', "starting tagID will be ".($tagID+1));

  $cCounter = 0;

  $aRels = array();
  $aOrgKey = array();

  while ($ctRow) {
    // check for an OMIS extended record
    $omis_ext = (count($ctRow) > 45) ? true : false;
    $ctRow['KEY'] = intval($ctRow['KEY']);
    $ctRow['SKEY'] = intval($ctRow['SKEY']);

    ++$contactID;
    ++$cCounter;

    if (RAYDEBUG) markTime('getLine');

    // Set the unique importID for this contact.
    $importID = $ctRow['KEY'];

    // If this is an org, create an organization for this contact if necessary,
    // then create a contact linked to the org.

    //initialize the org relationship for contact later
    $orgID = null;

    // Assume we are going to need to create a contact.
    // An organization-only record may make contact creation unnecessary.
    $createContact = true;

    if ($ctRow['RT'] == 7 || $ctRow['RT'] == 6) {
      //generate the key, based on: name and full address
      $orgKey = $ctRow['OCOMPANY'].$ctRow['HOUSE'].$ctRow['STREET'].$ctRow['CITY'];
      //if we already have this business, use the existing one
      //otherwise create a new one
      if (isset($aOrgKey[$orgKey])) {
        $orgID = $aOrgKey[$orgKey];
      }
      else {
        //remember this org as a new one by key
        $aOrgKey[$orgKey] = $contactID;

        //remember for association later
        $orgID = $contactID;
        //Append "-1" to end of OMIS ID to differentiate from the contact.
        $extID = $sourceDesc.$importID.'-1';

        $params = create_civi_organization($orgID,$sourceDesc,$importID,$ctRow);

        //write out the contact
        if (!writeToFile($fout['contact'], $params)) {
          exit("Error: I/O failure: contact");
        }
  
        //work address (address record + district info record)
        write_full_address($fout, ++$addressID, $contactID, $ctRow, LOC_TYPE_WORK);

        //increase contactID for individual if we're going to need to create a new one
        if (!empty($ctRow['FIRST']) || !empty($ctRow['LAST'])) {
          ++$contactID;
          $createContact = true;
        }
        else {
          $createContact = false;
        }
      }

      if ($createContact) {
        /*
        ** Write out the relationship.
        ** The individual contact for the relationship is going to happen
        ** further down, but we can already create the relationship since
        ** contactID was increased above.
        */
      	$params = array(
      	  'contact_id_a' => $contactID,
          'contact_id_b' => $orgID,
          'relationship_type_id' => $aRelLookup['employeeOf']
        );
      	if (!writeToFile($fout['relationship'], $params)) {
          exit("Error: I/O failure: relationship");
      	}
      } //createContact
    } //has org

    // Handle relationship.
    // This is safe since contactID was increased to match individual.

    /* Only one of the TC2 codes in a spousal relationship will be set.
    ** The TC2 that is set identifies the Head of Household.
    ** If neither is set, or both are set, then use oldest person as HoH.
    */
    $spouse_key = $ctRow['SKEY'];
    if ($spouse_key > 0) {
      //if the relationship target exists, just add the info
      if (isset($aRels[$spouse_key])) {
        $relKey = $spouse_key;
        $aRels[$relKey]['contactIDb'] = $contactID;
        // consistency check at the second record in a spousal relationship
        if ($aRels[$relKey]['omisKEYb'] != $importID ||
            $aRels[$relKey]['omisKEYa'] != $spouse_key) {
          cLog(0, 'info', "Warning: Spousal relationship inconsistency at contactID=$contactID, KEY=$importID, SKEY=$spouse_key");
        }
        /* If neither A nor B had TC2 set, or if both A and B had TC2 set,
        ** then pick the HoH using birth date.
        */
        if (($ctRow['TC2'] > 0 && $aRels[$relKey]['hoh']) ||
            (empty($ctRow['TC2']) && !$aRels[$relKey]['hoh'])) {
          // use DOB to pick HoH
          cLog(0, 'info', "Warning: No definitive Head-of-Household found at contactID=$contactID, KEY=$importID, SKEY=$spouse_key");
          $bdateA = convert_birth_date($aRels[$relKey]['ctRow']);
          $bdateB = convert_birth_date($ctRow);
          if ($bdateB > $bdateA) {
            $aRels[$relKey]['hoh'] = 'b';
            $aRels[$relKey]['ctRow'] = $ctRow;
          }
          else {
            // ctRow was also set, so only need to set the hoh
            $aRels[$relKey]['hoh'] = 'a';
          }
        }
        else if ($ctRow['TC2'] > 0) {
          $aRels[$relKey]['hoh'] = 'b';
          $aRels[$relKey]['ctRow'] = $ctRow;
        }
      }
      else {
        $relKey = $importID;
        //otherwise create a new relationship record
        $aRels[$relKey]['omisKEYa'] = $importID;
        $aRels[$relKey]['omisKEYb'] = $spouse_key;
        $aRels[$relKey]['contactIDa'] = $contactID;
        $aRels[$relKey]['ctRow'] = $ctRow;   // save the entire record
        if ($ctRow['TC2'] > 0) {
          $aRels[$relKey]['hoh'] = 'a';  // most likely, this is the HoH
        } else {
          $aRels[$relKey]['hoh'] = null;  // most likely, the spouse is HoH
        }
      }
    }

    // Create the individual contact record

    if ($createContact) {
      $params = array();
      $params['id'] = $contactID;
      $params['contact_type'] = 'Individual';
      $params['external_identifier'] = $sourceDesc.$importID;
      $params['first_name'] = $ctRow['FIRST']; 

      $mi = trim($ctRow['MI']);
      // if MI is one character, then append a period, otherwise leave as is
      if (strlen($mi) == 1) {
        $mi .= '.';
      }

      $params['middle_name'] = $mi; 
      $params['last_name'] = $ctRow['LAST']; 
      $params['sort_name'] = $ctRow['LAST'].', '.$ctRow['FIRST'].' '.$mi; 
      $params['display_name'] = $ctRow['FIRST'].' '.$mi.' '.$ctRow['LAST']; 
      switch ($ctRow['SEX']) {
        case 'F': $params['gender_id'] = GENDER_FEMALE; break;
        case 'M': $params['gender_id'] = GENDER_MALE; break;
        default:  $params['gender_id'] = DBNULL; break;
      }
      $params['source'] = $sourceDesc;
      $params['birth_date'] = convert_birth_date($ctRow);

      // Set Prefix and Suffix IDs.
      $prefix_id = $suffix_id = DBNULL;

      /*
      ** It's important to note that OMIS stores two prefixes for each
      ** record: One for the addressee (INSIDE1) and one for the greeting
      ** (SALUTE1).  Bluebird only stores one prefix for each contact.
      ** The INSIDE1 value is used for generating the Bluebird prefix.
      ** If the INSIDE1 and SALUTE1 are identical, then the Bluebird prefix
      ** can be used for both the addressee and the greetings.  Otherwise,
      ** custom greetings will be generated.
      */
      if (isset($aPrefix[$ctRow['INSIDE1']])) {
        $prefix_id = $aPrefix[$ctRow['INSIDE1']];
      }
      else if ($ctRow['SEX'] == 'M') {
        $prefix_id = $aPrefix['Mr.'];
      }
      else if ($ctRow['SEX'] == 'F') {
        $prefix_id = $aPrefix['Ms.'];
      }

      // normalize the OMIS suffix into one of the accepted Bluebird suffixes
      $suffix = $aSuffixLookup[$ctRow['SUFFIX']]['suffix'];
      if ($suffix) {
        // convert normalized suffix text into a Bluebird suffix ID
        $suffix_id = $aSuffix[$suffix];
      }
      else {
        $suffix = '';
      }

      $params['prefix_id'] = $prefix_id;
      $params['suffix_id'] = $suffix_id;
 
      //lookup extra suffix and remember for note
      $otherSuffix = $aSuffixLookup[$ctRow['SUFFIX']]['otherSuffix'];
  
      /********************************************************************
      ** Set the Addressee to either a template or a custom value.
      */
      if ($ctRow['TC1'] == '100') {
        $addressee_id = ADDRESSEE_CUSTOM;
        $addressee_custom = "The ".$ctRow['LAST']." Family";
        $addressee_display = $addressee_custom;
      }
      else {
        $addressee_display = $ctRow['INSIDE1'].' '.$ctRow['FIRST'].' '.$mi.' '.$ctRow['LAST'].' '.$suffix;
        // If the prefix_id was set, use the standard addressee template.
        if ($params['prefix_id'] != DBNULL) {
          $addressee_id = ADDRESSEE_FULL;
          $addressee_custom = DBNULL;
        }
        else {
          $addressee_id = ADDRESSEE_CUSTOM;
          $addressee_custom = $addressee_display;
        }
      }

      $params['addressee_id'] = $addressee_id;
      $params['addressee_custom'] = $addressee_custom;
      $params['addressee_display'] = $addressee_display;

      /**********************************************************************
      ** Set Postal/E-mail Greetings to either a template or a custom value.
      */
      if ($ctRow['FAM1']) {
        $greeting_id = GREETING_NICK;
        $greeting_custom = DBNULL;
        $greeting_display = "Dear ".$ctRow['FAM1'];
      }
      else if ($ctRow['SALUTE1']) {
        $greeting_display = "Dear ".$ctRow['SALUTE1'].' '.$ctRow['LAST'];
        if ($ctRow['INSIDE1'] == $ctRow['SALUTE1'] && $prefix_id != DBNULL) {
          $greeting_id = GREETING_PREFIX_LN;
          $greeting_custom = DBNULL;
        }
        else {
          $greeting_id = GREETING_CUSTOM;
          $greeting_custom = $greeting_display;
        }
      }
      else {
        $greeting_id = GREETING_FRIEND;
        $greeting_custom = DBNULL;
        $greeting_display = "Dear Friend";
      }

      $params['postal_greeting_id'] = $greeting_id;
      $params['postal_greeting_custom'] = $greeting_custom;
      $params['postal_greeting_display'] = $greeting_display;
      $params['email_greeting_id'] = $greeting_id;
      $params['email_greeting_custom'] = $greeting_custom;
      $params['email_greeting_display'] = $greeting_display;

      //make sure the email address doesn't go into company field
      $params['organization_name'] = strpos($ctRow['OCOMPANY'],'@')>0 ? '' : $ctRow['OCOMPANY'];
      $params['job_title'] = DBNULL;
      if (strlen(trim($ctRow['OTITLE']))>0) {
        $params['job_title'] = $ctRow['OTITLE'];
      }
  
      $params['do_not_mail'] = ($ctRow['MS'] == 'U') ? 1 : 0;
  
      //set the relationship if it has an org
      $params['employer_id'] = $orgID>0 ? $orgID : DBNULL;
      $params['nick_name'] = $ctRow['FAM1'];
      $params['household_name'] = DBNULL;
  
      if (!writeToFile($fout['contact'], $params)) {
        exit("Error: I/O failure: contact");
      }

      //home address (address record + district info record)
      write_full_address($fout, ++$addressID, $contactID, $ctRow,LOC_TYPE_HOME);
    } //createContact

    if ($omis_ext) {
      //concatenate custom fields into a note
      $nonOmis = '';
      foreach ($omis_ext_fields as $fld => $is_bool) {
        if ($is_bool && $ctRow[$fld] == 'T') {
          $nonOmis .= $fld.': '.$ctRow[$fld].'\n';
        }
        else if (!$is_bool && $ctRow[$fld]) {
          $nonOmis .= $fld.': '.$ctRow[$fld].'\n';
        }
      }

      //add the other suffix
      if ($otherSuffix) {
        $nonOmis .= 'Other Suffix: '.$otherSuffix;
      }

      $params = array(
        'entity_table' => 'civicrm_contact',
        'entity_id' => $contactID,
        'note' => $nonOmis,
        'contact_id' => $session->get('userID'), //who inserted
        'modified_date' => DBNULL,
        'subject' => 'EXTERNAL DATA'
      );

      if (!writeToFile($fout['note'], $params)) {
        exit("Error: I/O failure: note");
      }
    }

    //add the constituent information
    $params = array();
    $params['entity_id'] = $contactID;
    $params['record_type_61'] = $ctRow['RT'];
    if (!writeToFile($fout['constituentinformation'], $params)) {
      exit("Error: I/O failure: constituentinformation");
    }

    //non omis work address
    if ($omis_ext) {
      $params = array(
        'id'                 => ++$addressID,
        'contact_id'         => $contactID,
        'location_type_id'   => LOC_TYPE_WORK,
        'is_primary'         => 0,
        'street_number'      => DBNULL,
        'street_unit'        => DBNULL,
        'street_name'        => DBNULL,
        'street_address'     => $ctRow['ADDR_WORK_STREET1'],
        'supplemental_address_1' => $ctRow['ADDR_WORK_STREET2'],
        'supplemental_address_2' => DBNULL,
        'city'               => $ctRow['ADDR_WORK_CITY'],
        'postal_code'        => $ctRow['ADDR_WORK_ZIP'],
        'postal_code_suffix' => DBNULL,
        'country_id'         => COUNTY_CODE_USA,
        'state_province_id'  => $aStates[$ctRow['ADDR_WORK_STATE']]);

      if (!writeToFile($fout['address'], $params)) {
        exit("Error: I/O failure: address");
      }
    }

    //home
    if (cleanData($ctRow['PHONE']) <> DBNULL) {
      $params = array(
        'contact_id'       => $contactID,
        'location_type_id' => LOC_TYPE_HOME,
        'is_primary'       => 1,
        'phone_type_id'    => PHONE_TYPE_PHONE,
        'phone'            => $ctRow['PHONE'],
      );

      if (!writeToFile($fout['phone'], $params)) {
        exit("Error: I/O failure: phone");
      }
    }

    //work phone
    if (strlen($ctRow['PHONE_WORK']) > 0) {
      $wphone = $ctRow['PHONE_WORK'];
      if ($ctRow['PHONE_WORK_EXT']) {
        $wphone .= ' '.$ctRow['PHONE_WORK_EXT'];
      }
      $params = array(
        'contact_id'        => $contactID,
        'location_type_id'  => LOC_TYPE_WORK,
        'is_primary'        => 0,
        'phone_type_id'     => PHONE_TYPE_PHONE,
        'phone'             => $wphone
      );
      
      if (!writeToFile($fout['phone'], $params)) {
        exit("Error: I/O failure: phone");
      }
    }

    //mobile phone
    if (strlen($ctRow['PHONE_MOBILE']) > 0) {
      $params = array(
        'contact_id'       => $contactID,
        'location_type_id' => LOC_TYPE_HOME,
        'is_primary'       => 0,
        'phone_type_id'    => PHONE_TYPE_MOBILE,
        'phone'            => $ctRow['PHONE_MOBILE'],
      );

      if (!writeToFile($fout['phone'], $params)) {
        exit("Error: I/O failure: phone");
      }
    }

    //fax home
    if (strlen($ctRow['FAX_HOME']) > 0) {
      $params = array(
        'contact_id'       => $contactID,
        'location_type_id' => LOC_TYPE_HOME,
        'is_primary'       => 0,
        'phone_type_id'    => PHONE_TYPE_FAX,
        'phone'            => $ctRow['FAX_HOME'],
      );

      if (!writeToFile($fout['phone'], $params)) {
        exit("Error: I/O failure: phone");
      }
    }

    //fax work 
    if (strlen($ctRow['FAX_WORK']) > 0) {
      $params = array(
        'contact_id'       => $contactID,
        'location_type_id' => LOC_TYPE_WORK,
        'is_primary'       => 0,
        'phone_type_id'    => PHONE_TYPE_FAX,
        'phone'            => $ctRow['FAX_WORK'],
      );

      if (!writeToFile($fout['phone'], $params)) {
        break;
      }
    }

    //email
    if (strpos($ctRow['OCOMPANY'],'@') > 0) {
      $params = array(
        'contact_id'       => $contactID,
        'location_type_id' => LOC_TYPE_HOME,
        'email'            => strtolower($ctRow['OCOMPANY']),
        'is_primary'       => 1,
      );

      if (!writeToFile($fout['email'], $params)) {
        exit("Error: I/O failure: email");
      }
    }

    //email from non-omis data
    if (strpos($ctRow['EMAIL'],'@') > 0) {
      $params = array(
        'contact_id'       => $contactID,
        'location_type_id' => LOC_TYPE_HOME,
        'email'            => strtolower($ctRow['EMAIL']),
        'is_primary'       => 1,
      );

      if (!writeToFile($fout['email'], $params)) {
        exit("Error: I/O failure: email");
      }
    }

    //create a single note of all the omis data
    $omisData = "";
    foreach ($ctRow as $fldname => $fldval) {
      $omisData .= $fldname.": ".$fldval.'\n';
    }

    $params = array(
      'entity_table' => 'civicrm_contact',
      'entity_id' => $contactID,
      'note' => $omisData,
      'contact_id' => $session->get('userID'), //who inserted
      'modified_date' => DBNULL,
      'subject' => 'OMIS DATA'
    );
    if (!writeToFile($fout['note'], $params)) {
      exit("Error: I/O failure: note");
    }
    unset($params);

    if (intval($cCounter/1000) == $cCounter/1000.0) {
      $elapsed = getElapsed();
      $str = ($numContacts-$cCounter)." left. ".
        number_format($cCounter/$elapsed,2)."/sec - ".
        prettyFromSeconds(intval(($numContacts-$cCounter)/($cCounter/$elapsed))).
        " - mem:".memory_get_usage()/1000;
      cLog(0,'info',"converted {$cCounter}/{$numContacts} contacts. last uniqueID:{$importID} civicrmID:{$contactID} - {$str}");
    }


    /*************************************************************************
    **  Notes (HIS file)
    *************************************************************************/
    while ($ntRow && $ntRow['KEY'] < $importID) {
      cLog(0, 'info', "Warning: Skipping note id=".$ntRow['KEY']);
      print_r($ntRow);
      $ntRow = getLineAsAssocArray($infiles['notes'], DELIM, $omis_nt_fields);
    }
    while ($ntRow && $ntRow['KEY'] == $importID) {
      // fields 4 thru 18 (HL1 to HL15) are the text lines
      $notetext = "Case #: ".$ntRow['HNUM'].'\n'.
                  "Page #: ".$ntRow['HPAG'].'\n'.
                  "Text: ".'\n';
      for ($i = 1; $i <= 15; $i++) {
        $idx = "HL".$i;
        $notetext .= $ntRow[$idx].'\n';
      }

      $params = array(
        'entity_table' => 'civicrm_contact',
        'entity_id' => $contactID,
        'note' => $notetext,
        'contact_id' => $session->get('userID'), //who inserted
        'modified_date' => DBNULL,
        'subject' => 'OMIS NOTE'
      );

      if (!writeToFile($fout['note'], $params)) {
        exit("Error: I/O failure: note");
      }
      unset($params);

      //get another note
      $ntRow = getLineAsAssocArray($infiles['notes'], DELIM, $omis_nt_fields);
      if (!$ntRow) break;
      $ntRow['KEY'] = intval($ntRow['KEY']);
    }


    /*************************************************************************
    **  Cases (CAS file)
    *************************************************************************/

    //create activities from cases
    while ($csRow && $csRow['KEY'] < $importID) {
      cLog(0, 'info', "Warning: Skipping case id=".$csRow['KEY']);
      print_r($csRow);
      $csRow = getLineAsAssocArray($infiles['cases'], DELIM, $omis_cs_fields);
    }
    while ($csRow && $csRow['KEY'] == $importID) {
      //set params
      $params = array();
      $params['id'] = ++$activityID;
      $params['source_contact_id'] = $session->get('userID');; //who inserted
      $params['subject'] = "OMIS CASE ACTIVITY ".intval($csRow['CASENUM']).": ".$csRow['CSUBJECT'];

      // COPENDATE and CCLOSEDATE are in YYMMDD format, but other OMIS dates
      // are in MMDDYY format.  So re-format it for use with formatDate().
      $actDate = $csRow['COPENDATE'];
      if (strlen($actDate) == 5) $actDate = '0'.$actDate; 
      $actDate = substr($actDate,2,2).substr($actDate,4,2).substr($actDate,0,2);
      $actDate = formatDate($actDate);
      $actTime = formatTime($csRow['COPENTIME']);

      //format date for db and add time
      $params['activity_date_time'] = $actDate.' '.$actTime;
      //if there's a close date, mark as closed
      if (strlen(trim($csRow['CCLOSEDATE']))>0) {
        $params['status_id'] = 2;
        //otherwise, if the open date was prior to 2009 mark it as closed
      } elseif (substr($actDate, 0, 4) < '2009') {
        $params['status_id'] = 2;
      } else {
        $params['status_id'] = 1;
      }
      
      $actdetails = '';
      if (strlen($csRow['CCLOSEDATE']) > 0)
        $actdetails .= '\nCASE CLOSED ON '.formatDate($csRow['CCLOSEDATE']);
      if (strlen($csRow['CNOTE1']) > 0)
        $actdetails .= '\nNote 1: '.$csRow['CNOTE1'];
      if (strlen($csRow['CNOTE2']) > 0)
        $actdetails .= '\nNote 2: '.$csRow['CNOTE2'];
      if (strlen($csRow['CNOTE3']) > 0)
        $actdetails .= '\nNote 3: '.$csRow['CNOTE3'];
      if (strlen($csRow['CHOMEPH']) > 0)
        $actdetails .= '\nHome Phone: '.$csRow['CHOMEPH'];
      if (strlen($csRow['CWORKPH']) > 0)
        $actdetails .= '\nWork Phone: '.$csRow['CWORKPH'];
      if (strlen($csRow['CFAXPH']) > 0)
        $actdetails .= '\nFax: '.$csRow['CFAXPH'];
      if (strlen($csRow['CSTAFF']) > 0)
        $actdetails .= '\nStaff: '.$csRow['CSTAFF'];
      if (strlen($csRow['CSNUM']) > 0)
        $actdetails .= '\nSSN: '.$csRow['CSNUM'];
      if (strlen($csRow['CLAB1']) > 0)
        $actdetails .= '\nCLAB1: '.$csRow['CLAB1'];
      if (strlen($csRow['CID1']) > 0)
        $actdetails .= '\nCID1: '.$csRow['CID1'];
      if (strlen($csRow['CLAB2']) > 0)
        $actdetails .= '\nCLAB2: '.$csRow['CLAB2'];
      if (strlen($csRow['CID2']) > 0)
        $actdetails .= '\nCID2: '.$csRow['CID2'];
      if (strlen($csRow['CISSUE']) > 0)
        $actdetails .= '\nIssue: '.$csRow['CISSUE'];
      if (trim($csRow['LEGISLATION']) != "|")
        $actdetails .= '\nLegislation: '.$csRow['LEGISLATION'];

      $params['details'] = $actdetails;

      //activity type
      switch ($csRow['CFORM']) {
        case 'E':
          $acttypeid = 39; //email received
          break;
        case 'F':
          $acttypeid = 41; //fax received
          break;
        case 'I':
          $acttypeid = 42; //in person
          break;
        case 'L':
          $acttypeid = 37; //letter received
          break;
        case 'M':
          $acttypeid = 1; //meeting
          break;
        case 'O':
          $acttypeid = 43; //other
          break;
        case 'P':
          $acttypeid = 35; //phone received
          break;
        case 'W':
          $acttypeid = 43; //website mapped to other
          break;
        default:
          $acttypeid = 43; //other
      }

      $params['activity_type_id'] = $acttypeid;

      //set contact target
      $targParams = array(
          'activity_id' => $activityID,
          'contact_id' => $contactID
      );

      //following needs to be set in custom fields
      switch ($csRow['CPLACE']) {
        case 'AO':
          $poi = 'albany_office';
          break;
        case 'DO':
          $poi = 'district_office';
          break;
        case 'OT':
          $poi = 'other';
          break;
      }

      $custParams = array(
          'entity_id' => $activityID,
          'place_of_inquiry_43' => $poi
      );

      if (!writeToFile($fout['activity'], $params))
        exit("Error: I/O failure: activity");
      if (!writeToFile($fout['activitytarget'], $targParams))
        exit("Error: I/O failure: activitytarget");
      if (!writeToFile($fout['activitycustom'], $custParams))
        exit("Error: I/O failure: activitycustom");
      unset($params);
      unset($custParams);
      unset($targParams);

      //get another case
      $csRow = getLineAsAssocArray($infiles['cases'], DELIM, $omis_cs_fields);
      if (!$csRow) break;
      $csRow['KEY'] = intval($csRow['KEY']);
    }


    /*************************************************************************
    **  Issue Codes (ISS file)
    *************************************************************************/

    //create notes from issues 
    //cumulate issues into one note
    $tstamp = null;
    $note = "";

    while ($isRow && $isRow['KEY'] < $importID) {
      cLog(0, 'info', "Warning: Skipping issue code id=".$isRow['KEY']);
      print_r($isRow);
      $isRow = getLineAsAssocArray($infiles['issues'], DELIM, $omis_is_fields);
    }
    while ($isRow && $isRow['KEY'] == $importID) {
      $issCode = $isRow['ISSUECODE'];
      $issDesc = trim($isRow['ISSUEDESCRIPTION']);
      $issCat = $isRow['CATEGORY'];

      $lastCat = strrchr($issCat, ">");
      if ($lastCat !== false) {
        $issCat = substr($lastCat, 1);
      }

      // Remember each tagID associated with the current contact.
      $stored_tag_ids = array();
      $issCatLower = strtolower($issCat);
      $issDescLower = strtolower($issDesc);

      /* If the category is in the tags table and is part of the issue code
         hierarchy (parent is not Freeform or Positions), then link to it
         as well as any parent tags.
      */
      if ($issCat != "*NOMATCH*") {
        if (isset($aTagsByName[$issCatLower])) {
          $tag_id = $aTagsByName[$issCatLower];
          $parent_id = $aTagsByID[$tag_id];
          if ($parent_id == POSITION_TAG_PARENT_ID ||
              $parent_id == FREEFORM_TAG_PARENT_ID) {
            cLog(0, 'info', "Warning: Category [$issCat] exists as a tag, but is not part of the hierarchy (it is either freeform or position).");
          }
          else {
            writeEntityTags($fout['entitytag'], $contactID, $tag_id, $aTagsByID,
                            $stored_tag_ids);
          }
        }
        else {
          //cLog(0,'info',"Warning: Categ. [$issCat] not found in tag table.");
          if (isset($aUnsavedTags[$issCat])) {
            $aUnsavedTags[$issCat]++;
          }
          else {
            $aUnsavedTags[$issCat] = 1;
          }
        }
      }

      // If IS_TAG is set, then the issue description becomes a freeform tag.
      if ($isRow['IS_TAG'] == 'Y') {
        // If the issDesc is not yet a tag, then create it.
        if (isset($aTagsByName[$issDescLower])) {
          $tag_id = $aTagsByName[$issDescLower];
        }
        else {
          $tag_id = ++$tagID;
          $aTagsByName[$issDescLower] = $tag_id;
          $aTagsByID[$tagID] = FREEFORM_TAG_PARENT_ID;
          writeFreeformTag($fout['tag'], $tag_id, $issDesc);
        }
        // Now link the current contact to the freeform tag.
        writeEntityTags($fout['entitytag'], $contactID, $tag_id, $aTagsByID,
                        $stored_tag_ids);
      }

      //get the most recent date, which is already in YYYY-MM-DD format
      $dt = $isRow['UPDATED'];
      if ($tstamp == null || strtotime($dt) > strtotime($tstamp)) {
        $tstamp = $dt;
      }

      $note .= "Issue Code $issCode: $issDesc".'\n';

      //get another issue
      $isRow = getLineAsAssocArray($infiles['issues'], DELIM, $omis_is_fields);
      if (!$isRow) break;
      $isRow['KEY'] = intval($isRow['KEY']);
    }

    //now write cumulated codes as one note
    if (!empty($note)) {
      //set the note params:
      $params = array(
        'entity_table' => 'civicrm_contact',
        'entity_id' => $contactID,
        'note' => $note,
        'contact_id' => $session->get('userID'),   //who inserted
        'modified_date' => $tstamp,
        'subject' => "OMIS ISSUE CODES"
      );
      if (!writeToFile($fout['note'], $params))
        break;
    }
    unset($params);

    //get the next contact
    $ctRow = getLineAsAssocArray($infiles['contacts'], DELIM, $omis_ct_fields);
  }


  /************************************************************************
  ** Relationships  -  Finished processing all contacts.
  ************************************************************************/

  //write out all relationships

  foreach ($aRels as $aRel) {
    $family_name = $aRel['ctRow']['LAST'].' Family';
    $nick_name = trim($aRel['ctRow']['FAM2']);

    //create the household record
    $params = array();
    $params['id'] = ++$contactID;
    $params['contact_type'] = 'Household';
    $params['external_identifier'] = $sourceDesc.$aRel['omisKEYa'].'+'.$aRel['omisKEYb'];
    $params['first_name'] = DBNULL;
    $params['middle_name'] = DBNULL;
    $params['last_name'] = DBNULL;
    $params['sort_name'] = $family_name;
    $params['display_name'] = $family_name;
    $params['gender_id'] = DBNULL;
    $params['source'] = $sourceDesc;
    $params['birth_date'] = DBNULL;
    $params['prefix_id'] = DBNULL;
    $params['suffix_id'] = DBNULL;

    // set the addressee using TC2 and INSIDE2

    if ($aRel['ctRow']['TC2'] == '100') {
      $addressee_id = ADDRESSEE_THE_HN;
      $addressee_custom = DBNULL;
      $addressee_display = 'The '.$family_name;
    }
    else {
      $addressee_id = ADDRESSEE_CUSTOM;
	  
      // if MI is one character, then append a period, otherwise leave as is
      $aRmi = trim($aRel['ctRow']['MI']);
      if (strlen($mi) == 1) { $aRmi .= '.'; }
	  
      $addressee_custom = $aRel['ctRow']['INSIDE2'].' '.$aRel['ctRow']['FIRST'].' '.$aRmi.' '.$aRel['ctRow']['LAST'].' '.$aRel['ctRow']['SUFFIX'];
      $addressee_display = $addressee_custom;
    }

    $params['addressee_id'] = $addressee_id;
    $params['addressee_custom'] = $addressee_custom;
    $params['addressee_display'] = $addressee_display;

    // set the postal and e-mail greetings using TC2, FAM2, and SALUTE2

    if ($aRel['ctRow']['TC2'] == "100") {
      $greeting_id = GREETING_HN;
      $greeting_custom = DBNULL;
      $greeting_display = 'Dear '.$family_name;
    }
    else if (strlen($nick_name) > 0) {
      $greeting_id = GREETING_NICK2;
      $greeting_custom = DBNULL;
      $greeting_display = 'Dear '.$nick_name;
    }
    else if ($aRel['ctRow']['SALUTE2']) {
      $greeting_id = GREETING_CUSTOM;
      $greeting_custom = 'Dear '.$aRel['ctRow']['SALUTE2'].' '.$aRel['ctRow']['LAST'];
      $greeting_display = $greeting_custom;
    }
    else {
      $greeting_id = GREETING_FRIENDS;
      $greeting_custom = DBNULL;
      $greeting_display = 'Dear Friends';
    }

    $params['postal_greeting_id'] = $greeting_id;
    $params['postal_greeting_custom'] = $greeting_custom;
    $params['postal_greeting_display'] = $greeting_display;
    $params['email_greeting_id'] = $greeting_id;
    $params['email_greeting_custom'] = $greeting_custom;
    $params['email_greeting_display'] = $greeting_display;

    $params['organization_name'] = DBNULL;
    $params['job_title'] = DBNULL;
    $params['do_not_mail'] = 0;
    $params['employer_id'] = DBNULL;
    $params['nick_name'] = $nick_name;
    $params['household_name'] = $family_name;

    if (!writeToFile($fout['contact'], $params)) break;
 
    // household address (address record + district info record)
    write_full_address($fout, ++$addressID, $contactID, $aRel['ctRow'], LOC_TYPE_HOME);

    $rcode = trim($aRel['ctRow']['RCD']);
    if (empty($rcode)) {
      $rcode = 'W';  // default to Wife
    }

    //create the spousal relationship
    $params = array(
      'contact_id_a' => $aRel['contactIDa'],
      'contact_id_b' => $aRel['contactIDb'],
      'relationship_type_id' => $aRelLookup[$rcode]
    );
    writeToFile($fout['relationship'], $params);

    // Assume Member of Household for both relationships.
    $relA = $relB = "MoH";
    if ($aRel['hoh'] == 'a') {
      $relA = "HoH";
    }
    else if ($aRel['hoh'] == 'b') {
      $relB = "HoH";
    }

    // Add the relationship between the second spouse and the household
    //contactID here is the household
    $params = array(
      'contact_id_a' => $aRel['contactIDb'],
      'contact_id_b' => $contactID,
      'relationship_type_id' => $aRelLookup[$relB]
    );
    writeToFile($fout['relationship'], $params);

    // Add the relationship between the first spouse and the household
    //contactID here is the household
    $params = array(
      'contact_id_a' => $aRel['contactIDa'],
      'contact_id_b' => $contactID,
      'relationship_type_id' => $aRelLookup[$relA]
    );
    writeToFile($fout['relationship'], $params);
  }

  cLog(0, 'info', "Unsaved tag info (tagName => occurrences):");
  print_r($aUnsavedTags);
  cLog(0, 'info', "done converting {$cCounter} contacts.");

  return true;
} // parseData()



function update($task, $importSet, $importDir, $sourceDesc)
{
  //get Genderlist
  //get tag array
  //country id
  //province ids
  //need to set location_type_id to the one that isn't editable

  $session =& CRM_Core_Session::singleton();
  $infiles = get_import_files($importDir, $importSet);

  $ctRow = getLineAsArray($infiles['contacts'], DELIM);
  $ntRow = getLineAsArray($infiles['notes'], DELIM);
  $csRow = getLineAsArray($infiles['cases'], DELIM);
  $isRow = getLineAsArray($infiles['issues'], DELIM);

  //fix the id for omis
  $ctRow[0] = intval($ctRow[0]);
  $ntRow[0] = intval($ntRow[0]);
  $csRow[0] = intval($csRow[0]);
  $isRow[0] = intval($isRow[0]);
  //count number of lines in the file
  $numContacts = countFileLines($infiles['contacts']) - $skipped;

  $cCounter = 0;

  $aPrefix = getOptions('individual_prefix');

  while ($ctRow) {
    ++$cCounter;
    if (intval($cCounter/1)==$cCounter/1.0) {
      $elapsed = getElapsed();
      $str = ($numContacts-$cCounter)." left. ".
              number_format($cCounter/$elapsed,2)."/sec - ".
              prettyFromSeconds(intval(($numContacts-$cCounter)/($cCounter/$elapsed))).
              " - mem:".memory_get_usage()/1000;
      cLog(0,'info',"processed {$cCounter}/{$numContacts} - {$str}");
    }

    //fix numeric ids
    $ctRow[0] = intval($ctRow[0]);
    $ntRow[0] = intval($ntRow[0]);
    $csRow[0] = intval($csRow[0]);
    $isRow[0] = intval($isRow[0]);
  
    if (RAYDEBUG) markTime('getLine');

    //set the contacts unique importID
    $importID = intval($ctRow[0]);

    switch (strtolower($task)) {
      case 'updatecontactprefixid':
        $prefix_id = isset($aPrefix[$ctRow[40]]) ? $aPrefix[$ctRow[40]] : DBNULL;
        $dao = &CRM_Core_DAO::executeQuery(
              "update civicrm_contact set prefix_id={$prefix_id} ".
              "where source='{$sourceDesc}' AND user_unique_id = {$ctRow[0]};"
              , CRM_Core_DAO::$_nullArray);
        break;
    }
  
    $ctRow = getLineAsArray($infiles['contacts'], DELIM);
  }
} // update()



function writeTag($f, $tag_id, $tag_name, $parent_id, $used_for)
{
  $params = array(
    'id'            => $tag_id,
    'name'          => $tag_name,
    'description'   => $tag_name,
    'parent_id'     => $parent_id,
    'is_selectable' => 1,
    'is_reserved'   => 0,
    'is_tagset'     => 0,
    'used_for'      => $used_for
  );
  return writeToFile($f, $params);
} // writeTag()



function writeFreeformTag($f, $tag_id, $tag_name)
{
  $parent_id = FREEFORM_TAG_PARENT_ID;
  $used_for = "civicrm_contact,civicrm_activity,civicrm_case";
  return writeTag($f, $tag_id, $tag_name, $parent_id, $used_for);
} // writeFreeformTag()



function writeEntityTags($f, $contact_id, $tag_id, $tags_by_id, &$stored_ids)
{
  // Attach all tags that have a parent tag.  Top-level tags are not attached.
  while ($tags_by_id[$tag_id]) {
    // Only attach tags that have not been stored for the current contact.
    if (!in_array($tag_id, $stored_ids, true)) {
      $params = array(
        'entity_table' => 'civicrm_contact',
        'entity_id' => $contact_id,
        'tag_id' => $tag_id
      );
      writeToFile($f, $params);
      $stored_ids[] = $tag_id;
    }
    $tag_id = $tags_by_id[$tag_id];   // get the parent tagID
  }
} // writeEntityTags()



function getAllTags(&$tags_by_name, &$tags_by_id)
{
  // This array maps a tag name (issue description) to a tagID.  Tag names
  // are stored as lower-case in order to detect and avoid naming conflicts.
  $tags_by_name = array();
  // This array maps a tagID to its parent tagID.
  $tags_by_id = array();
  $max_id = 0;

  $session =& CRM_Core_Session::singleton();

  $dao = &CRM_Core_DAO::executeQuery(
           "SELECT id, name, parent_id from civicrm_tag;",
           CRM_Core_DAO::$_nullArray);

  while ($dao->fetch()) {
    if ($dao->id > $max_id) {
      $max_id = $dao->id;
    }
    $tags_by_name[strtolower($dao->name)] = $dao->id;
    $tags_by_id[$dao->id] = $dao->parent_id;    
  }
  return $max_id;
} // getAllTags()



function getStates()
{
  global $aStates;

  $session =& CRM_Core_Session::singleton();
  $dao = &CRM_Core_DAO::executeQuery(
           "SELECT abbreviation, id from civicrm_state_province ".
           "where country_id=".COUNTRY_CODE_USA.";", CRM_Core_DAO::$_nullArray);

  while ($dao->fetch()) {
    $aStates[$dao->abbreviation] = $dao->id;
  }
} // getStates()



function importIssueCodes($importSet)
{
  echo $importSet;
} // importIssueCodes()



function getOptions($strGroup)
{
  $dao = &CRM_Core_DAO::executeQuery("select name, label, value from civicrm_option_value where option_group_id=(select id from civicrm_option_group where name='$strGroup');", CRM_Core_DAO::$_nullArray);

  $options = array();

  while ($dao->fetch()) {
    $name = (strlen($dao->label) > 0) ? $dao->label : $dao->name;
    $options[$name] = $dao->value;
  }

  return $options;  
} // getOptions()



function create_civi_address($addrID, $ctID, $omis_flds, $loc_type_id = LOC_TYPE_HOME)
{
  global $aStates;

  $addr = array(
    'id'                     => $addrID,
    'contact_id'             => $ctID,
    'location_type_id'       => $loc_type_id,
    'is_primary'             => 1,
    'street_number'          => intval($omis_flds['HOUSE']),
    'street_unit'            => DBNULL,
    'street_name'            => $omis_flds['STREET'],
    'street_address'         => $omis_flds['HOUSE'].' '.$omis_flds['STREET'],
    'supplemental_address_1' => $omis_flds['MAIL'],
    'supplemental_address_2' => $omis_flds['OVERFLOW'],
    'city'                   => $omis_flds['CITY'],
    'postal_code'            => $omis_flds['ZIP5'],
    'postal_code_suffix'     => $omis_flds['ZIP4'],
    'country_id'             => COUNTRY_CODE_USA,
    'state_province_id'      => $aStates[$omis_flds['STATE']]
  );
  return $addr;
} // create_civi_address()


function create_district_info($addrID, $omis_flds)
{
  $distinfo = array(
    'entityID' => $addrID,
    'congressional_district_46' => DBNULL,
    'ny_senate_district_47' => cleanData($omis_flds['SD']),
    'ny_assembly_district_48' => cleanData($omis_flds['AD']),
    'election_district_49' => cleanData($omis_flds['ED']),
    'county_50' => cleanData($omis_flds['CT']),
    'county_legislative_district_51' => DBNULL,
    'town_52' => cleanData($omis_flds['TN']),
    'ward_53' => cleanData($omis_flds['WD']),
    'school_district_54' => cleanData($omis_flds['SCD']),
    'new_york_city_council_55' => DBNULL,
    'last_import_57' => date("Y-m-d H:i:s")
  );
  return $distinfo;
} // create_district_info()


function write_full_address($fout, $addrID, $ctID, $omis_flds, $loc_type_id)
{
  $params = create_civi_address($addrID, $ctID, $omis_flds, $loc_type_id);
  if (!writeToFile($fout['address'], $params)) {
    exit("Error: I/O failure: address");
  }

  $params = create_district_info($addrID, $omis_flds);
  if (!writeToFile($fout['district'], $params)) {
    exit("Error: I/O failure: district");
  }

  return true;
} // write_full_address()


function create_civi_organization($orgid, $src, $omisid, $omis_flds)
{
  $company = $omis_flds['OCOMPANY'];
  $nickname = $omis_flds['FAM1'];

  $org = array(
    'id' => $orgid,
    'contact_type' => 'Organization',
    'external_identifier' => $src.$omisid.'-1',
    'first_name' => DBNULL,
    'middle_name' => DBNULL,
    'last_name' => DBNULL,
    'sort_name' => $company,
    'display_name' => $company,
    'gender_id' => DBNULL,
    'source' => $src,
    'birth_date' => DBNULL,
    'prefix_id' => DBNULL,
    'suffix_id' => DBNULL,
    'addressee_id' => ADDRESSEE_ORG,
    'addressee_custom' => DBNULL,
    'addressee_display' => $company,
    'postal_greeting_id' => DBNULL,
    'postal_greeting_custom' => DBNULL,
    'postal_greeting_display' => DBNULL,
    'email_greeting_id' => DBNULL,
    'email_greeting_custom' => DBNULL,
    'email_greeting_display' => DBNULL,
    'organization_name' => $company,
    'job_title' => DBNULL,
    'do_not_mail' => 0,
    'employer_id' => DBNULL,
    'nick_name' => $nickname,
    'household_name' => DBNULL
  );
  return $org;
} // create_civi_organization()


function get_import_files($idir, $iset)
{
  $ifiles = array();
  $iset = strtoupper($iset);
  $ifiles['contacts'] = $idir."/".$iset."MST.TXT";
  $ifiles['cases'] = $idir."/".$iset."CAS.TXT";
  $ifiles['notes'] = $idir."/".$iset."HIS.TXT";
  $ifiles['issues'] = $idir."/".$iset."ISSCONV.TXT";

  foreach ($ifiles as $ifile) {
    if (!file_exists($ifile)) {
      echo "$ifile: Import file does not exist.\n";
      return null;
    }
  }
  return $ifiles;
} // get_import_files()


function convert_birth_date($omis_flds)
{
  //assume birthdate was in the 1900's since OMIS doesn't include the millenium
  //ASSUMPTION!!
  $bday = $omis_flds['BMM'].$omis_flds['BDD'].$omis_flds['BYY'];
  return formatDate($bday, '19');
} // convert_birth_date()
