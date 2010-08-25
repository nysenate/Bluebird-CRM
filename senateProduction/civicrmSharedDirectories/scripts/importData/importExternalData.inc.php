<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2009                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007.                                       |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

error_reporting(E_ERROR | E_PARSE);

//no limit
set_time_limit(0);

require_once dirname(__FILE__)."/../commonLibs/config.php";
require_once dirname(__FILE__).'/../commonLibs/lib.inc.php';

if (isset($argv[1])) $task = strtolower($argv[1]);
if (isset($argv[2])) define('CIVICRM_CONFDIR',RAYROOTDIR."sites/{$argv[2]}".RAYROOTDOMAIN);
if (isset($argv[3])) $importSet=$argv[3];
$startID = (isset($argv[4])) ? $argv[4] : 0;

require_once RAYCIVIPATH.'civicrm.config.php';
require_once 'CRM/Core/Config.php';
require_once 'CRM/Contact/BAO/Contact.php';
require_once 'CRM/Core/BAO/CustomValueTable.php';

$config =& CRM_Core_Config::singleton();

$session =& CRM_Core_Session::singleton();

//set the user this data will be imported as
$session->set( 'userID',1 );

//importFields
global $fieldsContact,  $fieldsAddress,  $fieldsPhone,  $fieldsDistrictInformation,  $fieldsTag,  $fieldsNote;

$fieldsContact = array('id',
	'contact_type',
	'user_unique_id',
	'first_name',
	'middle_name',
	'last_name',
	'sort_name',
	'display_name',
	'nick_name',
	'gender_id',
	'source');

$fieldsAddress = array('id',
	'contact_id',
	'location_type_id',
	'is_primary',
	'street_number',
	'street_address',
	'supplemental_address_1',
	'supplemental_address_2',
	'city',
	'postal_code',
	'postal_code_suffix',
	'country_id',
	'state_province_id');

$fieldsPhone = array('contact_id',
	'location_type_id',
	'is_primary',
	'phone_type_id',
	'phone');

$fieldsDistrictInformation = array(
	'entity_id',
	'congressional_district_46',
	'election_district_49',
	'ny_assembly_district_48',
	'ny_senate_district_47');

$fieldsTag = array('entity_table',
	'entity_id',
	'tag_id');

$fieldsNote = array('contact_id',
	'entity_table',
	'subject',
	'modified_date',
	'entity_id,note');

//turn off key checks for speed. requires data to be accurate
CRM_Core_DAO::executeQuery( "SET FOREIGN_KEY_CHECKS=0;", CRM_Core_DAO::$_nullArray );

markTime();

switch ($task) {

        case "import":
                importData($importSet, $startID);
                break;
	case "showfields":
		showExportableFields();
		break;
        case "importissuelist":
		if (!confirmCheck("importissuelist", "CAREFUL, ONLY ADVISABLE ON A BLANK DATABASE!")) exit;
                importIssueCodes($importSet);
                break;
}

cLog(0,'info',"DONE IN ".prettyFromSeconds(getElapsed()));

exit;


//--------------------------------------------
//functions

function showExportableFields() {

	$f = CRM_Contact_BAO_Contact::exportableFields('Individual');

	print_r($f);

	echo "\ncustom fields: \n\n";
	foreach ($f as $key=>$val)  if (stristr($key,'custom')) echo $key." => ".$val['title']."\n";
}

function importData($importSet, $startID) {

	
	///parses import into database load files
	if (!parseData($importSet, $startID)) return false;

	loadDB($importSet);

	return true;

        //clean up some tmp files
	//WRONG PATH!! needs importset
	//unlink(RAYTMP.'ct.csv');
        //unlink(RAYTMP.'ad.csv');
        //unlink(RAYTMP.'ph.csv');
        //unlink(RAYTMP.'cu.csv');
        //unlink(RAYTMP.'ta.csv');
        //unlink(RAYTMP.'no.csv');
}

function loadDB($importSet) {

	global $fieldsContact,  $fieldsAddress,  $fieldsPhone,  $fieldsDistrictInformation,  $fieldsTag,  $fieldsNote;

        cLog(0,'info',"importing contacts to database");

	//set some keychecks off for speed.
	$dao = &CRM_Core_DAO::executeQuery( "SET foreign_key_checks = 0;", CRM_Core_DAO::$_nullArray );

	$columns=implode(',',$fieldsContact);
        $dao = &CRM_Core_DAO::executeQuery( "LOAD DATA LOCAL INFILE '/tmp/{$importSet}-ct.csv' REPLACE INTO TABLE civicrm_contact FIELDS TERMINATED BY '\\t' LINES TERMINATED BY '\\n' IGNORE 1 LINES ({$columns});", CRM_Core_DAO::$_nullArray );
	
        cLog(0,'info',"importing addresses to database");
        $columns=implode(',',$fieldsAddress);
        $dao = &CRM_Core_DAO::executeQuery( "LOAD DATA LOCAL INFILE '/tmp/{$importSet}-ad.csv' REPLACE INTO TABLE civicrm_address FIELDS TERMINATED BY '\\t' LINES TERMINATED BY '\\n' IGNORE 1 LINES ({$columns});", CRM_Core_DAO::$_nullArray );

        cLog(0,'info',"importing phones to database");
        $columns=implode(',',$fieldsPhone);
        $dao = &CRM_Core_DAO::executeQuery( "LOAD DATA LOCAL INFILE '/tmp/{$importSet}-ph.csv' REPLACE INTO TABLE civicrm_phone FIELDS TERMINATED BY '\\t' LINES TERMINATED BY '\\n' IGNORE 1 LINES ({$columns});", CRM_Core_DAO::$_nullArray );

        cLog(0,'info',"importing custom fields to database");
        $columns=implode(',',$fieldsDistrictInformation);
        $dao = &CRM_Core_DAO::executeQuery( "LOAD DATA LOCAL INFILE '/tmp/{$importSet}-cu.csv' REPLACE INTO TABLE civicrm_value_district_information_7 FIELDS TERMINATED BY '\\t' LINES TERMINATED BY '\\n' IGNORE 1 LINES ({$columns});", CRM_Core_DAO::$_nullArray );

        cLog(0,'info',"importing tags to database");
        $columns=implode(',',$fieldsTag);
        $dao = &CRM_Core_DAO::executeQuery( "LOAD DATA LOCAL INFILE '/tmp/{$importSet}-ta.csv' REPLACE INTO TABLE civicrm_entity_tag FIELDS TERMINATED BY '\\t' LINES TERMINATED BY '\\n' IGNORE 1 LINES ({$columns});", CRM_Core_DAO::$_nullArray );

        cLog(0,'info',"importing notes to database");
        $columns=implode(',',$fieldsNote);
        $dao = &CRM_Core_DAO::executeQuery( "LOAD DATA LOCAL INFILE '/tmp/{$importSet}-no.csv' REPLACE INTO TABLE civicrm_note FIELDS TERMINATED BY '\\t' LINES TERMINATED BY '\\n' IGNORE 1 LINES ({$columns});", CRM_Core_DAO::$_nullArray );

/* NOT USED, all imported to 
        cLog(0,'info',"importing cases to database");
        $columns='contact_id,entity_table,subject,modified_date,entity_id,note';
        $dao = &CRM_Core_DAO::executeQuery( "LOAD DATA LOCAL INFILE '/tmp/ca.csv' REPLACE INTO TABLE civicrm_notes FIELDS TERMINATED BY '\\t' LINES TERMINATED BY '\\n' IGNORE 1 LINES ({$columns});", CRM_Core_DAO::$_nullArray );
*/
}

function parseData($importSet, $startID) {

	//get Genderlist
	//get tag array
	//country id
	//province ids
	//need to set location_type_id to the one that isn't editable

        $session =& CRM_Core_Session::singleton();

	$fContacts = RAYIMPORTDIR.$importSet."/".$importSet."MST.TXT";
        $fCases = RAYIMPORTDIR.$importSet."/".$importSet."CAS.TXT";
        $fNotes = RAYIMPORTDIR.$importSet."/".$importSet."HIS.TXT";
        $fIssues = RAYIMPORTDIR.$importSet."/".$importSet."ISS.TXT";

	unlink(RAYTMP.$importSet.'-ct.csv');
        unlink(RAYTMP.$importSet.'-ad.csv');
        unlink(RAYTMP.$importSet.'-ph.csv');
        unlink(RAYTMP.$importSet.'-cu.csv');
        unlink(RAYTMP.$importSet.'-ta.csv');
        unlink(RAYTMP.$importSet.'-no.csv');

	$fOutContacts = fopen(RAYTMP.$importSet.'-ct.csv', 'w');
        $fOutAddress = fopen(RAYTMP.$importSet.'-ad.csv', 'w');
        $fOutPhone = fopen(RAYTMP.$importSet.'-ph.csv', 'w');
        $fOutCustom = fopen(RAYTMP.$importSet.'-cu.csv', 'w');
        $fOutTags = fopen(RAYTMP.$importSet.'-ta.csv', 'w');
	$fOutNotes = fopen(RAYTMP.$importSet.'-no.csv', 'w');

	//initialize the arrays, first line is header so throwaway 
	$fileError=false;
        if (!$ctRow = getLineAsArray($fContacts, '~')) $fileError = true;
        $ntRow = getLineAsArray($fNotes, '~');
        $csRow = getLineAsArray($fCases, '~');
        $isRow = getLineAsArray($fIssues, '~');
        $ctRow = getLineAsArray($fContacts, '~');
        $ntRow = getLineAsArray($fNotes, '~');
        $csRow = getLineAsArray($fCases, '~');
        $isRow = getLineAsArray($fIssues, '~');

        if ($fileError) {

                cLog(0,'INFO',"error opening files!");
                return false;
        }

	//get the first entry
	$skipped=0;
	while (intval($ctRow[0])<$startID) {$ctRow = getLineAsArray($fContacts, '~'); ++$skipped;}
        while (intval($ntRow[0])<$startID) $ntRow = getLineAsArray($fContacts, '~');
        while (intval($csRow[0])<$startID) $csRow = getLineAsArray($fContacts, '~');

	//fix the id for omis
        $ctRow[0] = intval($ctRow[0]);
	$ntRow[0] = intval($ntRow[0]);
        $csRow[0] = intval($csRow[0]);

	//count number of lines in the file
	$numContacts = countFileLines($fContacts)-$skipped; 

        cLog(0,'info',"importing {$numContacts} lines starting with $startID, skipped $skipped");

	//get the max contactID from civi
	$dao = &CRM_Core_DAO::executeQuery( "SELECT max(id) as maxid from civicrm_contact;", CRM_Core_DAO::$_nullArray );
	$dao->fetch();
	$contactID = $dao->maxid;
        cLog(0,'info',"starting contactID will be ".($contactID+1));

        $dao = &CRM_Core_DAO::executeQuery( "SELECT max(id) as maxid from civicrm_address;", CRM_Core_DAO::$_nullArray );
        $dao->fetch();
        $addressID = $dao->maxid;
	cLog(0,'info',"starting addressID will be ".($addressID+1));

	$cCounter=0;

	while ($ctRow) {

		++$contactID;
                ++$addressID;
		++$cCounter;

		if (RAYDEBUG) markTime('getLine');

		//set the contacts unique importID
		$importID=intval($ctRow[0]);

		$params = array();
                $params['id'] = $contactID;
                $params['contact_type'] = 'Individual';
		$params['user_unique_id'] = $importID;
		$params['first_name'] = $ctRow[2]; 
                $params['middle_name'] = $ctRow[3]; 
                $params['last_name'] = $ctRow[1]; 
                $params['sort_name'] = $ctRow[1].', '.$ctRow[2]; 
                $params['display_name'] = $ctRow[1].', '.$ctRow[2]; 
                $params['nick_name'] = $ctRow[1]; //nickname
		$params['gender_id'] = ($ctRow[17]=='M') ? 29 : 28;
		$params['source'] = 'omis';

                if (!writeToFile($fOutContacts, $params)) break;

		$params = array ( 'id'			      => $addressID,
				  'contact_id'		      => $contactID,
				  'location_type_id'          => 1,
                                  'is_primary'                => 1,
                                  'street_number'             => $ctRow[5],
                                  'street_address'            => $ctRow[5].' '.$ctRow[6],
                                  'supplemental_address_1'    => $ctRow[7],
                                  'supplemental_address_2'    => null,
                                  'city'                      => $ctRow[8],
                                  'postal_code'               => $ctRow[10],
                                  'postal_code_suffix'        => $ctRow[11],
                                  'country_id'                => 1228,
                                  'state_province_id'         => 1031);

                if (!writeToFile($fOutAddress, $params)) break;

	        $params = array(    'contact_id'                => $contactID,
                                    'location_type_id'          => 1,
                                    'is_primary'                => 1,
                                    'phone_type_id' 		=> 1,
                                    'phone'         		=> $ctRow[30],
                                    );

                if (!writeToFile($fOutPhone, $params)) break;
		unset($params);

		if (intval($cCounter/1000)==$cCounter/1000.0) {
			$elapsed = getElapsed();
			$str = ($numContacts-$cCounter)." left. ".
				number_format($cCounter/$elapsed,2)."/sec - ".
				prettyFromSeconds(intval(($numContacts-$cCounter)/($cCounter/$elapsed))).
				" - mem:".memory_get_usage()/1000;
        	        cLog(0,'info',"converted {$cCounter}/{$numContacts} contacts. last uniqueID:{$importID} civicrmID:{$contactID} - {$str}");
		}

		$params = array();
		$params['entityID'] = $addressID;
		$params['congressional_district_46'] = $ctRow[22];
        	$params['election_district_49'] = $ctRow[23];
        	//$params['school_district_54'] = $ctRow[21];
        	$params['ny_assembly_district_48'] = $ctRow[24];
                $params['ny_senate__district_47'] = $ctRow[21];

                if (!writeToFile($fOutCustom, $params)) break;

		//create notes 

		while ($ntRow && $ntRow[0]==$importID) {

			//set note params
			$params = array();
                	$params['contact_id'] = $session->get( 'userID' ); //who inserted
                        $params['entity_table'] = 'civicrm_contact';
                        $params['subject'] = '';
                        $params['modified_date'] = '';
                        $params['entity_id'] = $contactID;
                	$params['note'] = $ntRow[3].'\n'.
				$ntRow[4].'\n'.
                                $ntRow[5].'\n'.
                                $ntRow[6].'\n'.
                                $ntRow[7].'\n'.
                                $ntRow[8].'\n'.
                                $ntRow[9].'\n'.
                                $ntRow[10].'\n'.
                                $ntRow[11].'\n'.
                                $ntRow[12].'\n'.
                                $ntRow[13].'\n'.
                                $ntRow[14].'\n'.
                                $ntRow[15].'\n'.
                                $ntRow[16].'\n'.
                                $ntRow[17].'\n'.
                                $ntRow[18].'\n';

	                if (!writeToFile($fOutNotes, $params)) break;
			unset($params);

			//get another note
			$ntRow = getLineAsArray($fNotes, '~');					
                        $ntRow[0] = intval($ntRow[0]);
		}


                //create notes from cases
                while ($csRow && $csRow[0]==$importID) {

                	//set note params
                        $params = array();
                        $params['contact_id'] = $session->get( 'userID' );; //who inserted
                        $params['entity_table'] = 'civicrm_contact';
                        $params['subject'] = $csRow[2] ." - ". $csRow[11];
                        $params['modified_date'] = $csRow[20];
                        $params['entity_id'] = $contactID;
                        $params['note'] = "workphone: ".$csRow[8].'\nfax: '.
                        	$csRow[9].'\n'.
				$csRow[18].'\n'.
                                $csRow[19].'\n'.
                                $csRow[20];

                        if (!writeToFile($fOutNotes, $params)) break;
                        unset($params);

                        //get another note
                        $csRow = getLineAsArray($fCases, '~');
                        $csRow[0] = intval($csRow[0]);
                 }

                //create notes from issues 
		//cumulate issues into one note
		$bIssue=false;
		$params = array();
                while ($isRow && $isRow[0]==$importID) {

			$bIssue=true;

			$issParams['entity_table'] = 'civicrm_contact';
                        $issParams['entity_id'] = $contactID;

			$issParams['tag'] = $isRow[5];

			//pass these params to the tag writer since it has to recursively sellect the tags
			writeRecursiveTags($fOutTags,$issParams);

			//also write tags to notes
                        //set note params
                        $params['contact_id'] = $session->get( 'userID' );; //who inserted
                        $params['entity_table'] = 'civicrm_contact';
                        $params['subject'] = "OMIS ISSUES CODES";
                        $params['modified_date'] = "\N";
                        $params['entity_id'] = $contactID;
                        $params['note'] .= "Issue Code: ".$isRow[1]." Description: ".$isRow[3].'\n';

                        //get another note
                        $isRow = getLineAsArray($fIssues, '~');
                        $isRow[0] = intval($isRow[0]);
                }

		//now write cumulated codes as one note
		if ($bIssue) if (!writeToFile($fOutNotes, $params)) break;
                unset($params);

                $ctRow = getLineAsArray($fContacts, '~');
	}

        cLog(0,'info',"done converting {$cCounter} contacts.");

	return true;
}

function writeRecursiveTags($f, $params) {

	global $aTags;
	global $aTagsByID;

	//get master tag list
	if (!isset($aTags)) loadTags();

	//check the tag exists
	if (!isset($aTags[$params['tag']])) {
//		cLog(0,'ERROR', "TAG NOT FOUND: ".$params['tag']);
		return;
	}

	//since all tags are filed under the root tag "issue codes", if the parent is 0 that means we hit issue codes and can stop.
	if ($aTags[$params['tag']]['parent_id'] == 0) return;

	$params['tag_id'] = $aTags[$params['tag']]['id'];

	writeToFile($f, $params);

	//call this function again until we've gone up the chain.
	if ($aTags[$params['tag']]['parent_id']>0) {
	
		//replace tag with parent tag
		$params['tag'] = $tagsByID[$aTags[$params['tag']['parent_id']]];
		writeRecursiveTags($f,$params);
	}
}

function loadTags() {

	global $aTags;
	global $aTagsByID;
	
}

function importIssueCodes($importSet) {

	echo $importSet;
}

