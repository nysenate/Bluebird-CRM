<?php

define('DELIM', '~');
define("DBNULL", null);
// ParentID of all top-level category tags in the issue code hierarchy.
define('CATEGORY_TAG_PARENT_ID', 291);
// ParentID of all freeform tags.
define('FREEFORM_TAG_PARENT_ID', 296);
define('COUNTRY_CODE_USA', 1228);

/*
** NOTES:
** 1. birthdates are assumed to be in the 1900's since OMIS doesn't
**    include the millenium
*/

error_reporting(E_ERROR && E_PARSE);
error_reporting(E_ALL && ~E_NOTICE);

//no limit
set_time_limit(0);

require_once "../commonLibs/config.php";
require_once "../commonLibs/lib.inc.php";

$prog = $argv[0];
$task = "import";
$instance = $importSet = $importDir = "";
$sourceDesc = "omis";
$startID = 0;

if (count($argv) <= 1) {
  die("Usage: $prog [options] instanceURL importSet\nwhere [options] are:\n  -t task\n  -d importDir\n  -s sourceDesc\n  -i startID\n");
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

if (strpos($instance, '.') === false) {
  $instance = $instance.".crm.nysenate.gov";
  echo "Warning: InstanceURL expanded to $instance\n";
}

if (empty($importDir)) {
  $importDir = RAYIMPORTDIR.$importSet;
}

if (!file_exists($importDir)) {
  die("$prog: $importDir: Directory not found\nMust specify a valid import directory.\n");
}

define('CIVICRM_CONFDIR', RAYROOTDIR.'sites/default');
if (putenv("SERVER_NAME=$instance") == false) {
  die("Unable to set SERVER_NAME in environment.\n");
}

require_once RAYCIVIPATH.'civicrm.config.php';
require_once 'CRM/Core/Config.php';
require_once 'CRM/Contact/BAO/Contact.php';
require_once 'CRM/Core/BAO/CustomValueTable.php';
require_once 'CRM/Core/BAO/Tag.php';
require_once 'senate.constants.php';

$config =& CRM_Core_Config::singleton();
$session =& CRM_Core_Session::singleton();

//set the user this data will be imported as
$session->set('userID', 1);

//turn off key checks for speed. requires data to be accurate
CRM_Core_DAO::executeQuery("SET FOREIGN_KEY_CHECKS=0;", CRM_Core_DAO::$_nullArray);

markTime();

switch ($task) {
  case "parseonly":
    parseData($importSet, $importDir, $startID, $sourceDesc);
    break;
  case "loaddbonly":
    loadDB($importSet);
    break;
  case "import":
    if (parseData($importSet, $importDir, $startID, $sourceDesc)) {
      loadDB($importSet);
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



function loadDB($importSet)
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
    $fname = RAYTMP.$importSet.'-'.$abbrev.'.tsv';
    cLog(0, 'info', "importing $name records from $fname into database table $table");

    cLog(0,'info',"LOAD DATA LOCAL INFILE '$fname' REPLACE INTO TABLE $table $opts ({$colstr});");

    $dao = &CRM_Core_DAO::executeQuery("LOAD DATA LOCAL INFILE '$fname' REPLACE INTO TABLE $table $opts ({$colstr});", CRM_Core_DAO::$_nullArray);
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
    $fname = RAYTMP.$importSet.'-'.$abbrev.'.tsv';
    unlink($fname);
    $fout[$name] = fopen($fname, 'w');
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
    cLog(0,'INFO',"error opening files!");
    return false;
  }

  //count number of lines in the file
  $numContacts = countFileLines($infiles['contacts']) - $skipped; 

  cLog(0,'info',"importing {$numContacts} lines starting with $startID, skipped $skipped");
  cLog(0,'info',"starting OMIS IDs: ct=".$ctRow['KEY'].",nt=".$ntRow['KEY'].",cs=".$csRow['KEY'].",is=".$isRow['KEY']."\n");

  //get the max contactID from civi
  $dao = &CRM_Core_DAO::executeQuery( "SELECT max(id) as maxid from civicrm_contact;", CRM_Core_DAO::$_nullArray );
  $dao->fetch();
  $contactID = $dao->maxid;
  cLog(0,'info',"starting contactID will be ".($contactID+1));

  $dao = &CRM_Core_DAO::executeQuery( "SELECT max(id) as maxid from civicrm_address;", CRM_Core_DAO::$_nullArray );
  $dao->fetch();
  $addressID = $dao->maxid;
  cLog(0,'info',"starting addressID will be ".($addressID+1));

  $dao = &CRM_Core_DAO::executeQuery( "SELECT max(id) as maxid from civicrm_activity;", CRM_Core_DAO::$_nullArray );
  $dao->fetch();
  $activityID = $dao->maxid;
  cLog(0,'info',"starting activityID will be ".($activityID+1));

  $cCounter = 0;

  $aRels = array();
  $aIDMap = array();
  $aOrgKey = array();

  while ($ctRow) {
    // check for an OMIS extended record
    $omis_ext = (count($ctRow) > 45) ? true : false;
    $ctRow['KEY'] = intval($ctRow['KEY']);
    $ctRow['SKEY'] = intval($ctRow['SKEY']);

    ++$contactID;
    ++$cCounter;

    if (RAYDEBUG) markTime('getLine');

    //set the contacts unique importID
    $importID = $ctRow['KEY'];

    //if this is an org, create an organization for this contact if necessary, then create a contact linked to the org

    //initialize the org relationship for contact later
    $orgID = null;

    if ($ctRow['RT'] == 7 || $ctRow['RT'] == 6) {

      //generate the key, based on: name and full address
      $orgKey = $ctRow['OCOMPANY'].$ctRow['HOUSE'].$ctRow['STREET'].$ctRow['CITY'];

      //if we already have this business, use the existing one
      //otherwise create a new one
      if (isset($aOrgKey[$orgKey])) {
        $orgID = $aOrgKey[$orgKey];
      } else {
        //remember this org as a new one by key
        $aOrgKey[$orgKey] = $contactID;

        //remember for assocation later:
        $orgID = $contactID;

        $params = array();
        $params['id'] = $contactID;
        $params['contact_type'] = 'Organization';
        //make sure contacts related to orgs have different IDs since they have to be unique.
        $params['external_identifier'] = $sourceDesc.$importID.'-1';
        $params['first_name'] = DBNULL;
        $params['middle_name'] = DBNULL;
        $params['last_name'] = DBNULL;
        $params['sort_name'] = $ctRow['OCOMPANY'];
        $params['display_name'] = $ctRow['OCOMPANY'];
        $params['gender_id'] = DBNULL;
        $params['source'] = $sourceDesc;
        $params['birth_date'] = DBNULL;
        $params['addressee_id'] = DBNULL;
        $params['addressee_custom'] = DBNULL;
        $params['addressee_display'] = DBNULL;
        $params['postal_greeting_id'] = DBNULL;
        $params['postal_greeting_custom'] = DBNULL;
        $params['postal_greeting_display'] = DBNULL;
        $params['organization_name'] = $ctRow['OCOMPANY'];
        $params['job_title'] = DBNULL;
        $params['prefix_id'] = DBNULL;
        $params['suffix_id'] = DBNULL;
        $params['do_not_mail'] = 0;
        $params['employer_id'] = DBNULL;
        $params['nick_name'] = $ctRow['FAM1'];
        $params['household_name'] = DBNULL;

        //write out the contact
        if (!writeToFile($fout['contact'], $params)) {
          exit("i/o fail: contact");
        }
  
        //work address
        $params = create_civi_address(++$addressID, $contactID, $ctRow, 2);

        if (!writeToFile($fout['address'], $params)) {
          exit("i/o fail: address");
        }

        //increase contactID for individual  
        ++$contactID;

        //write out the relationship
        $params = array();
        $params['contact_id_a'] = $contactID;
        $params['contact_id_b'] = $orgID;
        $params['relationship_type_id'] = $aRelLookup['employeeOf'];
        if (!writeToFile($fout['relationship'], $params)) {
          exit("i/o fail: relationship");        
        }
      }
    }

    //map civi id to external id for relationships
    //in case relationship parent does not precede current row.

    $aIDMap[$importID] = $contactID;

    //now do handle relationship. safe since contactID was increased to match individual
    if ($ctRow['SKEY'] > 0 ) {
      //if the relationship target exists, just add the info
      if (isset($aRels[$ctRow['SKEY']])) {
        $aRels[$ctRow['SKEY']]['relationshipCtRow'] = $ctRow;
        //make sure that the type is stored if the first entry was missing it
        if (!isset($aRels[$ctRow['SKEY']]['type'])) {
          if (strlen(trim($ctRow['RCD'])) > 0) {
            $aRels[$ctRow['SKEY']]['type'] = $ctRow['RCD'];
          }
        }
        //preset a relationship if there is none defined
        if (!isset($aRels[$ctRow['SKEY']]['type'])) {
          $aRels[$ctRow['SKEY']]['type'] = 'W';
        }

        //otherwise remember a rel record
      } else {
        $aRels[$importID]['contactID'] = $contactID;
        $aRels[$importID]['relationshipImportID'] = $ctRow['SKEY'];
        if (strlen(trim($ctRow['RCD'])) > 0) {
          $aRels[$importID]['type'] = $ctRow['RCD'];
        }

        //remember the row so we can pull out other data
        $aRels[$importID]['ctRow'] = $ctRow;
      }
    }

    //if this was just an org entry and there is not contact associated, skip the rest
    if ($orgID!=null && empty($ctRow['FIRST']) && empty($ctRow['LAST'])) {
      //get the next contact
      $ctRow = getLineAsAssocArray($infiles['contacts'], DELIM, $omis_ct_fields);
      //continue the loop
      continue;
    }

    $params = array();
    $params['id'] = $contactID;
    $params['contact_type'] = 'Individual';
    $params['external_identifier'] = $sourceDesc.$importID;
    $params['first_name'] = $ctRow['FIRST']; 

    $mi = trim($ctRow['MI']);
    $mi = (strlen($mi)>0 && substr($mi,-1) != '.') ? $mi.='.' : $mi;

    $params['middle_name'] = $mi; 
    $params['last_name'] = $ctRow['LAST']; 
    $params['sort_name'] = $ctRow['LAST'].', '.$ctRow['FIRST'] .' ' . $mi; 
    $params['display_name'] = $ctRow['FIRST'].' '.$mi.' '.$ctRow['LAST']; 
    switch ($ctRow['SEX']) {
      case 'M': $params['gender_id'] = 2; break;
      case 'F': $params['gender_id'] = 1; break;
      default:  $params['gender_id'] = DBNULL; break;
    }
    $params['source'] = $sourceDesc;
    //assume birthday was in the 1900s
    //ASSUMPTION!!
    $bday = $ctRow['BMM'].$ctRow['BDD'].$ctRow['BYY'];
    $params['birth_date'] =  formatDate($bday,'19');
    $params['addressee_id'] = ($ctRow['TC1'] == 100) ? 4 : 1;
    $params['addressee_custom'] = ($ctRow['TC1'] == 100) ? "The ".$ctRow['LAST']." Family" : DBNULL;

    $suffixWord = $aSuffixLookup[$ctRow['SUFFIX']]['suffix'];
    if ($suffixWord == null) {
      $suffixWord = '';
    }

    $params['addressee_display'] = ($ctRow['TC1'] == 100) ? "The ".$ctRow['LAST']." Family" : $ctRow['INSIDE1'].' '.$ctRow['FIRST'].' '.$mi.' '.$ctRow['LAST'] . ' ' . cleanData($suffixWord); //LCD
    $params['postal_greeting_id'] = 4;
    if ($ctRow['FAM1']) {
      $params['postal_greeting_custom'] = "Dear ".$ctRow['FAM1'];
    }
    else if ($ctRow['SALUTE1']) {
      $params['postal_greeting_custom'] = "Dear ".$ctRow['SALUTE1'].' '.$ctRow['LAST'];
    }
    else {
      $params['postal_greeting_custom'] = "Dear Friend";
    }
    $params['postal_greeting_display'] = $params['postal_greeting_custom'];
    //make sure the email address doesn't go into company field
    $params['organization_name'] = strpos($ctRow['OCOMPANY'],'@')>0 ? '' : $ctRow['OCOMPANY'];
    $params['job_title'] = DBNULL;
    if (strlen(trim($ctRow['OTITLE']))>0) {
      $params['job_title'] = $ctRow['OTITLE'];
    }

    $params['prefix_id'] = isset($aPrefix[$ctRow['INSIDE1']]) ? $aPrefix[$ctRow['INSIDE1']] : DBNULL;

    //lookup suffix and remember for note
    $suffix = $aSuffix[$aSuffixLookup[$ctRow['SUFFIX']]['suffix']];
    $otherSuffix = $aSuffix[$aSuffixLookup[$ctRow['SUFFIX']]['otherSuffix']];

    if (!$suffix || $suffix=='null') $params['suffix_id'] = DBNULL;
    else $params['suffix_id'] = $suffix;

    $params['do_not_mail'] = ($ctRow['MS']=='U') ? 1 : 0;

    //set the relationship if its got an org
    $params['employer_id'] = ($orgID!=null) ? $orgID : DBNULL;
    $params['nick_name'] = $ctRow['FAM1'];
    $params['household_name'] = DBNULL;

    if (!writeToFile($fout['contact'], $params)) {
      exit("i/o fail: contact");
    }

    if ($omis_ext) {
      //concatenate custom fields into a note
      $nonOmis='';
      foreach ($omis_ext_fields as $fld => $is_bool) {
        if ($is_bool && $ctRow[$fld] == 'T') {
          $nonOmis.=$fld.': '.$ctRow[$fld].'\n';
        }
        else if (!$is_bool && $ctRow[$fld]) {
          $nonOmis.=$fld.': '.$ctRow[$fld].'\n';
        }
      }

      //add the other suffix
      if ($otherSuffix!='null') {
        $nonOmis.='Other Suffix: '.$otherSuffix;
      }

      $params = array();
      $params['contact_id'] = $session->get('userID'); //who inserted
      $params['entity_table'] = 'civicrm_contact';
      $params['subject'] = 'EXTERNAL DATA';
      $params['modified_date'] = '';
      $params['entity_id'] = $contactID;
      $params['note'] = $nonOmis;

      if (!writeToFile($fout['note'], $params)) {
        exit("i/o fail: note");
      }
    }

    //add the constituent information
    $params = array();
    $params['entity_id'] = $importID;
    $params['record_type_61'] = $ictRow['RT'];
    if (!writeToFile($fout['constituentinformation'], $params)) {
      exit("i/o fail: constituent information");
    }

    //home address
    $params = create_civi_address(++$addressID, $contactID, $ctRow, 1);

    if (!writeToFile($fout['address'], $params)) {
      exit("i/o fail: address");
    }

    $params = array();
    $params['entityID'] = $addressID;
    $params['congressional_district_46'] = DBNULL;
    $params['ny_senate_district_47'] = cleanData($ctRow['SD']);
    $params['ny_assembly_district_48'] = cleanData($ctRow['AD']);
    $params['election_district_49'] = cleanData($ctRow['ED']);
    $params['county_50'] = cleanData($ctRow['CT']);
    $params['county_legislative_district_51'] = DBNULL;
    $params['town_52'] = cleanData($ctRow['TN']);
    $params['ward_53'] = cleanData($ctRow['WD']);
    $params['school_district_54'] = cleanData($ctRow['SCD']);
    $params['new_york_city_council_55'] = DBNULL;
    $params['neighborhood_56'] = DBNULL;
    $params['last_import_57'] = date("Y-m-d H:i:s T");

    if (!writeToFile($fout['district'], $params)) {
      exit("i/o fail: district");
    }

    //non omis work address
    if ($omis_ext) {
      $params = array(
        'id'                 => ++$addressID,
        'contact_id'         => $contactID,
        'location_type_id'   => 2,
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
        exit("i/o fail: address");
      }
    }

    //home
    if (cleanData($ctRow['PHONE']) <> DBNULL) {
      $params = array(
        'contact_id'       => $contactID,
        'location_type_id' => 1,
        'is_primary'       => 1,
        'phone_type_id'    => 246,
        'phone'            => $ctRow['PHONE'],
      );

      if (!writeToFile($fout['phone'], $params)) {
        exit("i/o fail: phone");
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
        'location_type_id'  => 2,
        'is_primary'        => 0,
        'phone_type_id'     => 246,
        'phone'             => $wphone
      );
      
      if (!writeToFile($fout['phone'], $params)) {
        exit("i/o fail: phone");
      }
    }

    //mobile phone
    if (strlen($ctRow['PHONE_MOBILE']) > 0) {
      $params = array(
        'contact_id'       => $contactID,
        'location_type_id' => 1,
        'is_primary'       => 0,
        'phone_type_id'    => 247,
        'phone'            => $ctRow['PHONE_MOBILE'],
      );

      if (!writeToFile($fout['phone'], $params)) {
        exit("i/o fail: phone");
      }
    }

    //fax home
    if (strlen($ctRow['FAX_HOME']) > 0) {
      $params = array(
        'contact_id'       => $contactID,
        'location_type_id' => 1,
        'is_primary'       => 0,
        'phone_type_id'    => 248,
        'phone'            => $ctRow['FAX_HOME'],
      );

      if (!writeToFile($fout['phone'], $params)) {
        exit("i/o fail: phone");
      }
    }

    //fax work 
    if (strlen($ctRow['FAX_WORK']) > 0) {
      $params = array(
        'contact_id'       => $contactID,
        'location_type_id' => 2,
        'is_primary'       => 0,
        'phone_type_id'    => 248,
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
        'location_type_id' => 1,
        'email'            => $ctRow['OCOMPANY'],
        'is_primary'       => 1,
      );

      if (!writeToFile($fout['email'], $params)) {
        exit("i/o fail: email");
      }
    }

    //email from non-omis data
    if (strpos($ctRow['EMAIL'],'@') > 0) {
      $params = array(
        'contact_id'       => $contactID,
        'location_type_id' => 1,
        'email'            => $ctRow['EMAIL'],
        'is_primary'       => 1,
      );

      if (!writeToFile($fout['email'], $params)) {
        exit("i/o fail: email");
      }
    }

    //create a single note of all the omis data
    $omisData = "";
    foreach ($ctRow as $fldname => $fldval) {
      $omisData .= $fldname.": ".$fldval.'\n';
    }

    $params = array();
    $params['contact_id'] = $session->get('userID'); //who inserted
    $params['entity_table'] = 'civicrm_contact';
    $params['subject'] = 'OMIS DATA';
    $params['modified_date'] = DBNULL;
    $params['entity_id'] = $contactID;
    $params['note'] = $omisData;
    if (!writeToFile($fout['note'], $params)) {
      exit("i/o fail: note");
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

    //create notes 
    while ($ntRow && $ntRow['KEY'] < $importID) {
      $ntRow = getLineAsAssocArray($infiles['notes'], DELIM, $omis_nt_fields);
    }
    while ($ntRow && $ntRow['KEY'] == $importID) {
      //set note params
      $params = array();
      $params['contact_id'] = $session->get('userID'); //who inserted
      $params['entity_table'] = 'civicrm_contact';
      $params['subject'] = 'OMIS NOTE';
      $params['modified_date'] = DBNULL;
      $params['entity_id'] = $contactID;

      // fields 4 thru 18 (HL1 to HL15) are the text lines
      $notetext = "Case #: ".$ntRow['HNUM'].'\n'.
                  "Page #: ".$ntRow['HPAG'].'\n'.
                  "Text: ".'\n';
      for ($i = 1; $i <= 15; $i++) {
        $idx = "HL".$i;
        $notetext .= $ntRow[$idx].'\n';
      }
      $params['note'] = $notetext;

      if (!writeToFile($fout['note'], $params)) {
        exit("i/o fail: note");
      }
      unset($params);

      //get another note
      $ntRow = getLineAsAssocArray($infiles['notes'], DELIM, $omis_nt_fields);
      if (!$ntRow) break;
      $ntRow['KEY'] = intval($ntRow['KEY']);
    }

    //create activities from cases
    while ($csRow && $csRow['KEY']<$importID) {
      $csRow = getLineAsAssocArray($infiles['cases'], DELIM, $omis_cs_fields);
    }
    while ($csRow && $csRow['KEY'] == $importID) {
      //set params
      $params = array();
      $params['id'] = $activityID;
      $params['source_contact_id'] = $session->get('userID');; //who inserted
      $params['subject'] = "OMIS CASE ACTIVITY ".intval($csRow['CASENUM']).": ".$csRow['CSUBJECT'];

      //swap around the dates so it matches contact date format
      $actDate = $csRow['COPENDATE'];
      if (strlen($actDate)==5) $actDate = '0'.$actDate; 
      $actDate = substr($actDate,2,2).substr($actDate,4,2).substr($actDate,0,2);
      $actCloseDate = $csRow['CCLOSEDATE'];
      if (strlen($actCloseDate)==5) $actCloseDate = '0'.$actCloseDate;
      $actCloseDate = substr($actCloseDate,2,2).substr($actCloseDate,4,2).substr($actCloseDate,0,2);

      //format date for db and add time
      $params['activity_date_time'] = formatDate($actDate).' '.$csRow['COPENTIME'];
      //if there's a close date, mark as closed
      if (strlen(trim($csRow['CCLOSEDATE']))>0) {
        $params['status_id'] = 2;
        //otherwise, if the open date was prior to 2009 mark it as closed
      } elseif (date('Y',strtotime(formatDate($actDate)))<'2009') {
        $params['status_id'] = 2;
      } else {
        $params['status_id'] = 1;
      }
      
      $params['details'] = '';
      if (strlen($csRow['CCLOSEDATE'])>0) $params['details'] .= '\nCASE CLOSED ON '.formatDate($csRow['CCLOSEDATE']);
      if (strlen($csRow['CNOTE1'])>0) $params['details'] .= '\nNote 1: '.$csRow['CNOTE1'];
      if (strlen($csRow['CNOTE2'])>0) $params['details'] .= '\nNote 2: '.$csRow['CNOTE2'];
      if (strlen($csRow['CNOTE3'])>0) $params['details'] .= '\nNote 3: '.$csRow['CNOTE3'];
      if (strlen($csRow['CHOMEPH'])>0) $params['details'] .= '\nHome Phone: '.$csRow['CHOMEPH'];
      if (strlen($csRow['CWORKPH'])>0) $params['details'] .= '\nWork Phone: '.$csRow['CWORKPH'];
      if (strlen($csRow['CFAXPH'])>0) $params['details'] .= '\nFax: '. $csRow['CFAXPH'].'\n';
      if (strlen($csRow['CSTAFF'])>0) $params['details'] .= '\nStaff: '. $csRow['CSTAFF'].'\n';
      if (strlen($csRow['CSNUM'])>0) $params['details'] .= '\nSSN: '. $csRow['CSNUM'].'\n';
      if (strlen($csRow['CLAB1'])>0) $params['details'] .= '\nCLAB1: '. $csRow['CLAB1'].'\n';
      if (strlen($csRow['CID1'])>0) $params['details'] .= '\nCID1: '. $csRow['CID1'].'\n';
      if (strlen($csRow['CLAB2'])>0) $params['details'] .= '\nCLAB2: '. $csRow['CLAB2'].'\n';
      if (strlen($csRow['CID2'])>0) $params['details'] .= '\nCID2: '. $csRow['CID2'].'\n';
      if (strlen($csRow['CISSUE'])>0) $params['details'] .= '\nIssue: '. $csRow['CISSUE'].'\n';
      if (trim($csRow['LEGISLATION'])!="|") $params['details'] .= '\nLegislation: '.$csRow['LEGISLATION'];

      //activity type
      switch ($csRow['CFORM']) {
        case 'E':
          $params['activity_type_id'] = 39; //email received
          break;
        case 'F':
          $params['activity_type_id'] = 41; //fax received
          break;
        case 'I':
          $params['activity_type_id'] = 42; //in person
          break;
        case 'L':
          $params['activity_type_id'] = 37; //letter received
          break;
        case 'M':
          $params['activity_type_id'] = 1; //meeting
          break;
        case 'O':
          $params['activity_type_id'] = 43; //other
          break;
        case 'P':
          $params['activity_type_id'] = 35; //phone received
          break;
        case 'W':
          $params['activity_type_id'] = 43; //website mapped to other
          break;
        default:
          $params['activity_type_id'] = 43; //other
        }

      //set contact target
      $targParams = array();
      $targParams['activity_id'] = $activityID;
      $targParams['contact_id'] = $contactID;

      //following needs to be set in custom fields
      $custParams = array();
      $custParams['entity_id'] = $activityID;
      switch ($csRow['CPLACE']) {
        case 'AO':
          $custParams['place_of_inquiry_43'] = 'albany_office';
          break;

        case 'DO':
          $custParams['place_of_inquiry_43'] = 'district_office';
          break;

        case 'OT':
          $custParams['place_of_inquiry_43'] = 'other';
          break;
      }

      if (!writeToFile($fout['activity'], $params))
        exit("i/o fail: activity");
      if (!writeToFile($fout['activitytarget'], $targParams))
        exit("i/o fail: activitytarget");
      if (!writeToFile($fout['activitycustom'], $custParams))
        exit("i/o fail: activitycustom");
      unset($params);
      unset($custParams);

      //get another case
      $csRow = getLineAsAssocArray($infiles['cases'], DELIM, $omis_cs_fields);
      if (!$csRow) break;
      $csRow['KEY'] = intval($csRow['KEY']);
      ++$activityID;
    }

    //create notes from issues 
    //cumulate issues into one note
    $bIssue = false;
    $tstamp = null;
    $note = '';

    while ($isRow && $isRow['KEY']<$importID) {
      $isRow = getLineAsAssocArray($infiles['issues'], DELIM, $omis_is_fields);
    }
    while ($isRow && $isRow['KEY'] == $importID) {
      $bIssue = true;
      //pass these params to tag writer since it has to recursively select tags
      writeRecursiveTags($fout['tag'], $contactID, $isRow['CATEGORY']);

      //if 'Y' then add the tag as a freeform tag
      if (trim($isRow['IS_TAG']) == 'Y') {
        writeFreeformTag($fout['tag'], $contactID, $isRow['ISSUEDESCRIPTION']);
      }

      //get the most recent date
      $dt = formatDate($isRow['UPDATED']);
      if ($tstamp == null || strtotime($dt) > strtotime($tstamp)) {
        $tstamp = $dt;
      }

      $note .= "Issue Code ".$isRow['ISSUECODE'].": ".$isRow['ISSUEDESCRIPTION'].'\n';

      //get another issues 
      $isRow = getLineAsAssocArray($infiles['issues'], DELIM, $omis_is_fields);
      if (!$isRow) break;
      $isRow['KEY'] = intval($isRow['KEY']);
    }

    //set the note params:
    $params = array();
    $params['contact_id'] = $session->get('userID');; //who inserted
    $params['entity_table'] = 'civicrm_contact';
    $params['subject'] = "OMIS ISSUE CODES";
    $params['modified_date'] = $tstamp;
    $params['entity_id'] = $contactID;
    $params['note'] = $note;

    //now write cumulated codes as one note
    if ($bIssue) {
      if (!writeToFile($fout['note'], $params))
        break;
    }
    unset($params);

    //get the next contact
    $ctRow = getLineAsAssocArray($infiles['contacts'], DELIM, $omis_ct_fields);
  }

  //write out all relationships

  foreach ($aRels as $aRel) {
    //create the household record
    $params = array();
    $params['id'] = ++$contactID;
    $params['contact_type'] = 'Household';
    $params['external_identifier'] = $sourceDesc.$aRel['relationshipImportID'].'-H';
    $params['first_name'] = DBNULL;
    $params['middle_name'] = DBNULL;
    $params['last_name'] = DBNULL;
    $params['sort_name'] = $aRel['ctRow']['LAST'].' Family';
    $params['display_name'] = $aRel['ctRow']['LAST'].' Family';
    $params['gender_id'] = DBNULL;
    $params['source'] = $sourceDesc;
    $params['birth_date'] = DBNULL;
    $params['addressee_id'] = 5;
    $params['addressee_custom'] = DBNULL;
    $params['addressee_display'] = 'The '.$aRel['ctRow']['LAST'].' Family';
    $params['postal_greeting_id'] = 5;
    $params['postal_greeting_custom'] = DBNULL;
    $params['postal_greeting_display'] = 'Dear '.$aRel['ctRow']['LAST'].' Family';
    $params['organization_name'] = DBNULL;
    $params['job_title'] = DBNULL;
    $params['prefix_id'] = DBNULL;
    $params['suffix_id'] = DBNULL;
    $params['do_not_mail'] = 0;
    $params['employer_id'] = DBNULL;
    $params['nick_name'] = DBNULL;
    $params['household_name'] = $aRel['ctRow']['LAST'].' Family';
    if (!writeToFile($fout['contact'], $params)) break;
 
    //add the address
    $params = create_civi_address(++$addressID, $contactID, $aRel['ctRow'], 1);
    if (!writeToFile($fout['address'], $params)) break;

    //create the spousal relationship
    $params = array();
    $params['contact_id_a'] = $aRel['contactID'];
    $params['contact_id_b'] = $aIDMap[$aRel['relationshipImportID']];
    $params['relationship_type_id'] = $aRelLookup[$aRel['type']];
    writeToFile($fout['relationship'], $params);

    //find out who is HoH
    if ($aRel['ctRow']['TC2'] == 'HoH') {
      $first='HoH';
      $second='MoH';
    } elseif ($aRel['relationshipCtRow']['TC2']=='HoH') {
      $first='MoH';
      $second='HoH';
    //default
    } else {
      $first='MoH';
      $second='MoH';
    }

/*implement this later: **decided against
    } elseif ($aRel['ctRow']['GENDER']=='M') {
                        $first='HoH';
                        $second='MoH';
    } else {
                        $first='MoH';
                        $second='HoH';
    }
*/
    //add the head of household relationship to the household record
    //contactID here is the household
    $params = array();
    $params['contact_id_a'] = $aIDMap[$aRel['relationshipImportID']];
    $params['contact_id_b'] = $contactID; 
    $params['relationship_type_id'] = $aRelLookup[$first];
    writeToFile($fout['relationship'], $params);

    //add the member of household relationship to the household record
    //contactID here is the household
    $params = array();
    $params['contact_id_a'] = $aRel['contactID'];
    $params['contact_id_b'] = $contactID;
    $params['relationship_type_id'] = $aRelLookup[$second];
    writeToFile($fout['relationship'], $params);
  }

  cLog(0,'info',"done converting {$cCounter} contacts.");

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
              , CRM_Core_DAO::$_nullArray );
        break;
    }
  
    $ctRow = getLineAsArray($infiles['contacts'], DELIM);
  }
} // update()



function writeFreeformTag($f, $id, $tag)
{
  global $aFreeformTags;
  global $aTags;

  //get master tag list, loads into global var
  if (!isset($aFreeformTags)) {
    getFreeformTags();  
  }

  //create the tag if necessary - can't exist anywhere so using the big Tag category
  if (!isset($aFreeformTags[$tag]) && !isset($aTags[$tag])) {
    $session =& CRM_Core_Session::singleton();

    $params = array(
      'entity_id'     => null,
      'name'          => $tag,
      'description'   => $tag,
      'parent_id'     => FREEFORM_TAG_PARENT_ID,
      'is_selectable' => 1,
      'is_reserverd'  => 0,
      'used_for'      => 'civicrm_contact',
    );
    $oTag = CRM_Core_BAO_Tag::add($params, CRM_Core_DAO::$_nullArray);

    //remember the new tag for reuse
    $aFreeformTags[$tag] = $oTag->id;

    //print_r("added $tag {$oTag->id}\n");
    //print_r($oTag);
  }

  $params = array();
  $params['entity_table'] = 'civicrm_contact';
  $params['entity_id'] = $id;

  if (isset($aFreeformTags[$tag])) {
    $params['tag_id'] = $aFreeformTags[$tag]['id'];
    writeToFile($f, $params);
  }
} // writeFreeformTag()



function writeRecursiveTags($f, $id, $tag)
{
  global $aTags;
  global $aTagsByID;

  //get master tag list, loads into global var
  if (!isset($aTags)) getTags();
  //print_r($aTags);
  //print_r($aTagsByID);
  //check the tag exists
  if (!isset($aTags[$tag])) {
    // cLog(0,'ERROR', "TAG NOT FOUND: ".$params['tag']);
    return;
  }

  //since all tags are filed under the root tag "issue codes", they all have a parent and if the parent is 0 we can stop.
  if ($aTags[$tag]['parent_id'] == 0) return;

  $params = array();
  $params['entity_table'] = 'civicrm_contact';
  $params['entity_id'] = $id;
  $params['tag_id'] = $aTags[$tag]['id'];

  writeToFile($f, $params);

  //call this function again until we've gone up the chain.
  if ($aTags[$tag]['parent_id']>0) {
    writeRecursiveTags($f, $id, $aTagsByID[$aTags[$tag]['parent_id']]['name']);
  }
} // writeRecursiveTags()



function getTags()
{
  global $aTags;
  global $aTagsByID;

  $session =& CRM_Core_Session::singleton();

  $dao = &CRM_Core_DAO::executeQuery(
           "SELECT name, id, parent_id from civicrm_tag ".
           "where parent_id=".CATEGORY_TAG_PARENT_ID.
           " or id=".CATEGORY_TAG_PARENT_ID.";", CRM_Core_DAO::$_nullArray);

  $aTag = array();

  while ($dao->fetch()) {
    $aTags[$dao->name]['id'] = $dao->id;
    $aTags[$dao->name]['parent_id'] = $dao->parent_id;
    $aTagsByID[$dao->id]['name'] = $dao->name;
    $aTagsByID[$dao->id]['parent_id'] = $dao->parent_id;    
  }
} // getTags()



function getFreeFormTags()
{
  global $aFreeformTags;

  $session =& CRM_Core_Session::singleton();
  $dao = &CRM_Core_DAO::executeQuery(
           "SELECT name, id from civicrm_tag ".
           "where parent_id=".FREEFORM_TAG_PARENT_ID.
           " or id=".FREEFORM_TAG_PARENT_ID.";", CRM_Core_DAO::$_nullArray);

  $aTag = array();

  while ($dao->fetch()) {
    $aFreeformTags[$dao->name] = $dao->id;
  }
} // getFreeFormTags()



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
  $session =& CRM_Core_Session::singleton();
 
  $dao = &CRM_Core_DAO::executeQuery( "SELECT id from civicrm_option_group where name='".$strGroup."';", CRM_Core_DAO::$_nullArray );
  $dao->fetch();
  $optionGroupID = $dao->id;

  $dao = &CRM_Core_DAO::executeQuery( "SELECT name, label, value from civicrm_option_value where option_group_id=$optionGroupID;", CRM_Core_DAO::$_nullArray );

  $options = array();

  while ($dao->fetch()) {
    $name = $dao->name;
    if (strlen($dao->label)>0) $name = $dao->label;
    $options[$name] = $dao->value;
  }

  return $options;  
} // getOptions()



function create_civi_address($addrID, $ctID, $omis_flds, $loc_type_id = 1)
{
  global $aStates;

  $addr = array(
    'id'                     => $addrID,
    'contact_id'             => $ctID,
    'location_type_id'       => $loc_type_id,
    'is_primary'             => 1,
    'street_number'          => $omis_flds['HOUSE'],
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
