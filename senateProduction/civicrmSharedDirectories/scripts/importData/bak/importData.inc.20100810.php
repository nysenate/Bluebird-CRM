<?php

/*   NOTES:

1) birthdates are assumed to be in the 1900's since omis doesn't include the millenium


*/

error_reporting(E_ERROR && E_PARSE);
error_reporting(E_ALL && ~E_NOTICE);

//no limit
set_time_limit(0);

//require_once dirname(__FILE__)."/../commonLibs/config.php";
//require_once dirname(__FILE__).'/../commonLibs/lib.inc.php';

require_once "../commonLibs/config.php";
require_once "../commonLibs/lib.inc.php";

if (isset($argv[1])) $task = strtolower($argv[1]);
if (isset($argv[2])) define('CIVICRM_CONFDIR',RAYROOTDIR."sites/{$argv[2]}".RAYROOTDOMAIN);
if (isset($argv[3])) $importSet=$argv[3];

if (isset($argv[4])) $sourceDesc=$argv[4];
else $sourceDesc='omis';

$startID = (isset($argv[5])) ? $argv[5] : 0;

require_once RAYCIVIPATH.'civicrm.config.php';
require_once 'CRM/Core/Config.php';
require_once 'CRM/Contact/BAO/Contact.php';
require_once 'CRM/Core/BAO/CustomValueTable.php';
require_once 'CRM/Core/BAO/Tag.php';

$config =& CRM_Core_Config::singleton();

$session =& CRM_Core_Session::singleton();

//set the user this data will be imported as
$session->set( 'userID',1 );

//DEFINITIONS AND CONSTANTS
global $aSuffixMap;
global $aPrefixMap;
global $dbTable;

require('senate.constants.php');

//turn off key checks for speed. requires data to be accurate
CRM_Core_DAO::executeQuery( "SET FOREIGN_KEY_CHECKS=0;", CRM_Core_DAO::$_nullArray );

markTime();

switch ($task) {

        case "parseonly":
		parseData($importSet, $startID, $sourceDesc);
                break;
        case "loaddbonly":
		loadDB($importSet);
		break;
        case "import":
		if (parseData($importSet, $startID, $sourceDesc)) loadDB($importSet);
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
		update($task, $importSet, $sourceDesc);
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

function loadDB($importSet) {

	global $dbTable;

        cLog(0,'info',"importing contacts to database");

	//set some keychecks off for speed.
	$dao = &CRM_Core_DAO::executeQuery( "SET foreign_key_checks = 0;", CRM_Core_DAO::$_nullArray );

	$opts = "FIELDS TERMINATED BY '\\t' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '\\n' IGNORE 1 LINES";

	$columns=implode(',',$dbTable['contact']);
        $dao = &CRM_Core_DAO::executeQuery( "LOAD DATA LOCAL INFILE '/tmp/{$importSet}-ct.csv' REPLACE INTO TABLE civicrm_contact $opts ({$columns});", CRM_Core_DAO::$_nullArray );
        cLog(0,'info',"importing addresses to database");
        $columns=implode(',',$dbTable['address']);
        $dao = &CRM_Core_DAO::executeQuery( "LOAD DATA LOCAL INFILE '/tmp/{$importSet}-ad.csv' REPLACE INTO TABLE civicrm_address $opts ({$columns});", CRM_Core_DAO::$_nullArray );

        cLog(0,'info',"importing phones to database");
        $columns=implode(',',$dbTable['phone']);
        $dao = &CRM_Core_DAO::executeQuery( "LOAD DATA LOCAL INFILE '/tmp/{$importSet}-ph.csv' REPLACE INTO TABLE civicrm_phone $opts ({$columns});", CRM_Core_DAO::$_nullArray );

        cLog(0,'info',"importing custom fields to database");
        $columns=implode(',',$dbTable['district_information']);
        $dao = &CRM_Core_DAO::executeQuery( "LOAD DATA LOCAL INFILE '/tmp/{$importSet}-cu.csv' REPLACE INTO TABLE civicrm_value_district_information_7 $opts ({$columns});", CRM_Core_DAO::$_nullArray );

        cLog(0,'info',"importing tags to database");
        $columns=implode(',',$dbTable['tag']);
        $dao = &CRM_Core_DAO::executeQuery( "LOAD DATA LOCAL INFILE '/tmp/{$importSet}-ta.csv' REPLACE INTO TABLE civicrm_entity_tag $opts ({$columns});", CRM_Core_DAO::$_nullArray );

        cLog(0,'info',"importing notes to database");
        $columns=implode(',',$dbTable['note']);
        $dao = &CRM_Core_DAO::executeQuery( "LOAD DATA LOCAL INFILE '/tmp/{$importSet}-no.csv' REPLACE INTO TABLE civicrm_note $opts ({$columns});", CRM_Core_DAO::$_nullArray );

        cLog(0,'info',"importing emails to database");
        $columns=implode(',',$dbTable['email']);
        $dao = &CRM_Core_DAO::executeQuery( "LOAD DATA LOCAL INFILE '/tmp/{$importSet}-em.csv' REPLACE INTO TABLE civicrm_email $opts ({$columns});", CRM_Core_DAO::$_nullArray );

        cLog(0,'info',"importing relationships to database");
        $columns=implode(',',$dbTable['relationship']);
        $dao = &CRM_Core_DAO::executeQuery( "LOAD DATA LOCAL INFILE '/tmp/{$importSet}-re.csv' REPLACE INTO TABLE civicrm_relationship $opts ({$columns});", CRM_Core_DAO::$_nullArray );

        cLog(0,'info',"importing cases as activities to database");
        $columns=implode(',',$dbTable['activity']);
        $dao = &CRM_Core_DAO::executeQuery( "LOAD DATA LOCAL INFILE '/tmp/{$importSet}-ac.csv' REPLACE INTO TABLE civicrm_activity $opts ({$columns});", CRM_Core_DAO::$_nullArray );

        cLog(0,'info',"importing cases as activity targets to database");
        $columns=implode(',',$dbTable['activity_target']);
        $dao = &CRM_Core_DAO::executeQuery( "LOAD DATA LOCAL INFILE '/tmp/{$importSet}-actarget.csv' REPLACE INTO TABLE civicrm_activity_target $opts ({$columns});", CRM_Core_DAO::$_nullArray );

        cLog(0,'info',"importing custom activity values to database");
        $columns=implode(',',$dbTable['activity_custom']);
        $dao = &CRM_Core_DAO::executeQuery( "LOAD DATA LOCAL INFILE '/tmp/{$importSet}-accust.csv' REPLACE INTO TABLE civicrm_value_activity_details_6 $opts ({$columns});", CRM_Core_DAO::$_nullArray );

}

function parseData($importSet, $startID, $sourceDesc) {

	//get Genderlist
	//get tag array
	//country id
	//province ids
	//prefixes
	//prefix omis conversion map

	global $aPrefixMap; 
	global $aSuffixMap;
	global $aRelLookup;
	global $aOmisCols;

	//civi prefixes
	$aPrefix = getOptions('individual_prefix');

        $session =& CRM_Core_Session::singleton();

	$fContacts = RAYIMPORTDIR.$importSet."/".$importSet."MST.TXT";
        $fCases = RAYIMPORTDIR.$importSet."/".$importSet."CAS.TXT";
        $fNotes = RAYIMPORTDIR.$importSet."/".$importSet."HIS.TXT";
        $fIssues = RAYIMPORTDIR.$importSet."/".$importSet."ISSCONV.TXT";

	unlink(RAYTMP.$importSet.'-ct.csv');
        unlink(RAYTMP.$importSet.'-ad.csv');
        unlink(RAYTMP.$importSet.'-ph.csv');
        unlink(RAYTMP.$importSet.'-cu.csv');
        unlink(RAYTMP.$importSet.'-ta.csv');
        unlink(RAYTMP.$importSet.'-no.csv');
        unlink(RAYTMP.$importSet.'-em.csv');
        unlink(RAYTMP.$importSet.'-re.csv');
        unlink(RAYTMP.$importSet.'-ac.csv');
        unlink(RAYTMP.$importSet.'-actarget.csv');
        unlink(RAYTMP.$importSet.'-accust.csv');

	$fOutContacts = fopen(RAYTMP.$importSet.'-ct.csv', 'w');
        $fOutAddress = fopen(RAYTMP.$importSet.'-ad.csv', 'w');
        $fOutPhone = fopen(RAYTMP.$importSet.'-ph.csv', 'w');
        $fOutCustom = fopen(RAYTMP.$importSet.'-cu.csv', 'w');
        $fOutTags = fopen(RAYTMP.$importSet.'-ta.csv', 'w');
	$fOutNotes = fopen(RAYTMP.$importSet.'-no.csv', 'w');
        $fOutEmail = fopen(RAYTMP.$importSet.'-em.csv', 'w');
        $fOutRelationship = fopen(RAYTMP.$importSet.'-re.csv', 'w');
        $fOutActivity = fopen(RAYTMP.$importSet.'-ac.csv', 'w');
        $fOutActivityTarget = fopen(RAYTMP.$importSet.'-actarget.csv', 'w');
        $fOutActivityCustom = fopen(RAYTMP.$importSet.'-accust.csv', 'w');

	//initialize the arrays, first line is header so throwaway 
	$fileError=false;
        if (!$ctRow = getLineAsArray($fContacts, '~')) $fileError = true;
        //while (!is_numeric($ntRow[0])) $ntRow = getLineAsArray($fNotes, '~');
        //while (!is_numeric($csRow[0])) $csRow = getLineAsArray($fCases, '~');
        //while (!is_numeric($isRow[0])) $isRow = getLineAsArray($fIssues, '~');
        //while (!is_numeric($ctRow[0])) $ctRow = getLineAsArray($fContacts, '~');

        if ($fileError) {

                cLog(0,'INFO',"error opening files!");
                return false;
        }

	//get the first entry
	$skipped=0;
	while (intval($ctRow[0])<$startID) {
		$ctRow = getLineAsArray($fContacts, '~');
		++$skipped;
		if (!$ctRow) break;
	}
        while (intval($ntRow[0])<$startID) {
		$ntRow = getLineAsArray($fNotes, '~');
		if (!$ntRow) break;
	}
        while (intval($csRow[0])<$startID) {
		$csRow = getLineAsArray($fCases, '~');
		if (!$csRow) break;
	}
        while (intval($isRow[0])<$startID) {
                $isRow = getLineAsArray($fIssues, '~');
                if (!$isRow) break;
        }

	//fix the id for omis
        $ctRow[0] = intval($ctRow[0]);
	$ntRow[0] = intval($ntRow[0]);
        $csRow[0] = intval($csRow[0]);
 	$isRow[0] = intval($isRow[0]);
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

        $dao = &CRM_Core_DAO::executeQuery( "SELECT max(id) as maxid from civicrm_activity;", CRM_Core_DAO::$_nullArray );
        $dao->fetch();
        $activityID = $dao->maxid;
        cLog(0,'info',"starting activityID will be ".($activityID+1));

	$cCounter=0;

	$aRels = array();
	$aIDMap = array();
	$aOrgKey = array();

	while ($ctRow) {

		++$contactID;
                ++$addressID;
		++$cCounter;

		if (RAYDEBUG) markTime('getLine');

		//set the contacts unique importID
		$importID=intval($ctRow[0]);

		//map civi id to external id for relationships
		//in case relationship parent does not precede current row.
		
			$aIDMap[$importID] = $contactID;

			//remember the relationship
			if (intval($ctRow[13])>0) {
				$n = count($aRels);
				$aRels[$n]['contactID'] = $contactID;
	      	                $aRels[$n]['parentImportID'] = intval($ctRow[13]);
	                        $aRels[$n]['type'] = $ctRow[16];
			}

		//if this is an org, create an organization for this contact if necessary, then create a contact linked to the org

		//initialize the org relationship for contact later
		$orgID = null;

		if ($ctRow[14]==7 || $ctRow[14]==6) {

			//generate the key
			//based on: name and full address
			$orgKey = $ctRow[39].$ctRow[5].$ctRow[6].$ctRow[8];
//print_r($aOrgKey);
			//if we already have this business, use the existing one
			if (isset($aOrgKey[$orgKey])) {

				$orgID = $aOrgKey[$orgKey];

			//otherwise create a new one
			} else {

				//remember this org as a new one by key
				$aOrgKey[$orgKey] = $contactID;

				//remember for assocation later:
				$orgID = $contactID;

		                $params = array();
		                $params['id'] = $contactID;
			        $params['contact_type'] = 'Organization';
		                $params['external_identifier'] = $importID;
		                $params['first_name'] = 'NULL';
		                $params['middle_name'] = 'NULL';
		                $params['last_name'] = 'NULL';
		                $params['sort_name'] = $ctRow[39];
		                $params['display_name'] = $ctRow[39];
				$params['gender_id'] = 'NULL';
		                $params['source'] = $sourceDesc;
		                $params['birth_date'] = 'NULL';
		                $params['addressee_id'] = 'NULL';
		                $params['addressee_custom'] = 'NULL';
		                $params['addressee_display'] = 'NULL';
		                $params['postal_greeting_id'] = 'NULL';
		                $params['postal_greeting_custom'] = 'NULL';
		                $params['postal_greeting_display'] = 'NULL';
		                $params['organization_name'] = $ctRow[39];
		                $params['job_title'] = 'NULL';
		                $params['prefix_id'] = 'NULL';
		                $params['suffix_id'] = 'NULL';
		                $params['do_not_mail'] = 0;
                                $params['employer_id'] = null;
				$params['nick_name'] = $ctRow[36] . ' ' . $ctRow[37];

				//write out the contact
		                if (!writeToFile($fOutContacts, $params)) break;
	
		                //work address
		                $params = array ( 'id'         => $addressID,
       	                           'contact_id'                => $contactID,
       	                           'location_type_id'          => 2,
       	                           'is_primary'                => 1,
       	                           'street_number'             => $ctRow[5],
       	                           'street_unit'             => 'NULL',
       	                           'street_name'             => $ctRow[6],
       	                           'street_address'            => $ctRow[5].' '.$ctRow[6],
       	                           'supplemental_address_1'    => $ctRow[7],
       	                           'supplemental_address_2'    => $ctRow[34],
       	                           'city'                      => $ctRow[8],
       	                           'postal_code'               => $ctRow[10],
       	                           'postal_code_suffix'        => $ctRow[11],
       	                           'country_id'                => 1228,
       	                           'state_province_id'         => 1031);
	
		                if (!writeToFile($fOutAddress, $params)) break;
	
				++$contactID;
				++$addressID;
			}
		}

		$params = array();
                $params['id'] = $contactID;
                $params['contact_type'] = 'Individual';
		$params['external_identifier'] = ($orgID!=null) ? $importID.'-1' : $importID; //make sure contacts related to orgs have different IDs since they have to be unique.
		$params['first_name'] = $ctRow[2]; 
                $params['middle_name'] = $ctRow[3]; 
                $params['last_name'] = $ctRow[1]; 
                $params['sort_name'] = $ctRow[1].', '.$ctRow[2]; 
                $params['display_name'] = $ctRow[2].' '.$ctRow[1]; 
		switch ($ctRow[17]) {
			case 'M':
				$params['gender_id'] = 2;
				break;
			case 'F':
				$params['gender_id'] = 1;
				break;
			default:
				$params['gender_id'] = 'NULL';
				break;
		}
		$params['source'] = $sourceDesc;
		//assume birthday was in the 1900s
		//ASSUMPTION!!
		$bday = $ctRow[27].$ctRow[28].$ctRow[29];
                $params['birth_date'] =  formatDate($bday,'19');
		$params['addressee_id'] = 4;
                $params['addressee_custom'] = $aPrefixMap[intval($ctRow[25])].' '.$ctRow[1];
                $params['addressee_display'] = $params['addressee_custom'];
                $params['postal_greeting_id'] = 4;
                $params['postal_greeting_custom'] = 'Dear '. $params['addressee_custom'];
                $params['postal_greeting_display'] = $params['postal_greeting_custom'];
                $params['organization_name'] = strpos($ctRow[39],'@')>0 ? '' : $ctRow[39]; //make sure the email address doesn't go into company field

		$params['job_title'] = 'NULL';
                if (strlen(trim($ctRow[38]))>0) $params['job_title'] = $ctRow[38];
		if (trim($ctRow[45])!="|") $params['job_title'] = $ctRow[45]; //non omis field uses a different column for title

//echo $ctRow[25]." ".$aPrefixMap[intval($ctRow[25])]." ".$aPrefix[$aPrefixMap[intval($ctRow[25])]]."\n";

                $params['prefix_id'] = isset($aPrefix[$aPrefixMap[intval($ctRow[25])]]) ? $aPrefix[$aPrefixMap[intval($ctRow[25])]] : 'NULL';

		$params['suffix_id'] = isset($aSuffixMap[$ctRow[4]]) ? $aSuffixMap[$ctRow[4]] : 'NULL';
		$params['do_not_mail'] = ($ctRow[15]=='U') ? 1 : 0;

                //set the relationship if its got an org
                $params['employer_id'] = ($orgID!=null) ? $orgID : 'NULL';
                $params['nick_name'] = $ctRow[36] . ' ' . $ctRow[37];

                if (!writeToFile($fOutContacts, $params)) break;

		if (count($ctRow)>46) {

			//concatenate custom fields into a note
			$nonOmis='';
			if (strlen($ctRow[57])>0) $nonOmis.='Type: '.$ctRow[57].'\n';
                	if (strlen($ctRow[58])>0) $nonOmis.='Spouse: '.$ctRow[58].'\n';
                	if (strlen($ctRow[59])>0) $nonOmis.='Children: '.$ctRow[59].'\n';
                	if ($ctRow[60]=='T') $nonOmis.='Loves Liz: '.$ctRow[60].'\n';
                	if ($ctRow[61]=='T') $nonOmis.='Groups: '.$ctRow[61].'\n';
                	if (strlen($ctRow[62])>0) $nonOmis.='Website: '.$ctRow[62].'\n';
                	if ($ctRow[63]=='T') $nonOmis.='Seniors: '.$ctRow[63].'\n';
                	if ($ctRow[64]=='T') $nonOmis.='Non-District: '.$ctRow[64].'\n';

                	$params = array();
                	$params['contact_id'] = $session->get( 'userID' ); //who inserted
                	$params['entity_table'] = 'civicrm_contact';
                	$params['subject'] = 'EXTERNAL DATA';
                	$params['modified_date'] = '';
                	$params['entity_id'] = $contactID;
                	$params['note'] = $nonOmis;

                	if (!writeToFile($fOutNotes, $params)) break;
		}

		//home address
		$params = array ( 'id'			      => $addressID,
				  'contact_id'		      => $contactID,
				  'location_type_id'          => 1,
                                  'is_primary'                => 1,
                                  'street_number'             => $ctRow[5],
                                  'street_unit'             => 'NULL',
                                  'street_name'             => $ctRow[6],
                                  'street_address'            => $ctRow[5].' '.$ctRow[6],
                                  'supplemental_address_1'    => $ctRow[7],
                                  'supplemental_address_2'    => $ctRow[34],
                                  'city'                      => $ctRow[8],
                                  'postal_code'               => $ctRow[10],
                                  'postal_code_suffix'        => $ctRow[11],
                                  'country_id'                => 1228,
                                  'state_province_id'         => 1031);

                if (!writeToFile($fOutAddress, $params)) break;

                $params = array();
                $params['entityID'] = $addressID;
                $params['congressional_district_46'] = cleanData($ctRow[22]);
                $params['election_district_49'] =  cleanData($ctRow[23]);
                //$params['school_district_54'] = $ctRow[21];
                $params['ny_assembly_district_48'] =  cleanData($ctRow[24]);
                $params['ny_senate_district_47'] =  cleanData($ctRow[21]);
		$params['ward_53'] =  cleanData($ctRow[18]);
                $params['town_52'] =  cleanData($ctRow[19]);
                $params['county_53'] =  cleanData($ctRow[20]);

                if (!writeToFile($fOutCustom, $params)) break;

		//non omis work address
                if (count($ctRow)>46) {

		   ++$addressID;
                   $params = array ( 'id'                     => $addressID,
                                  'contact_id'                => $contactID,
                                  'location_type_id'          => 2,
                                  'is_primary'                => 0,
                                  'street_number'             => 'NULL',
                                  'street_unit'             => 'NULL',
                                  'street_name'             => 'NULL',
                                  'street_address'            => $ctRow[46],
                                  'supplemental_address_1'    => $ctRow[47],
                                  'supplemental_address_2'    => 'NULL',
                                  'city'                      => $ctRow[48],
                                  'postal_code'               => $ctRow[50],
                                  'postal_code_suffix'        => 'NULL',
                                  'country_id'                => 1228,
                                  'state_province_id'         => 1031);

                   if (!writeToFile($fOutAddress, $params)) break;
		}

		//home
		if (cleanData($ctRow[30])<>'NULL') {
	        $params = array(    'contact_id'                => $contactID,
                                    'location_type_id'          => 1,
                                    'is_primary'                => 1,
                                    'phone_type_id' 		=> 246,
                                    'phone'         		=> $ctRow[30],
                                    );

                if (!writeToFile($fOutPhone, $params)) break;
		}

		//work phone
		if (isset($ctRow[51]) && strlen($ctRow[51])>0) {
	                $params = array(    'contact_id'                => $contactID,
	                                    'location_type_id'          => 2,
	                                    'is_primary'                => 0,
	                                    'phone_type_id'             => 246,
	                                    'phone'                     => $ctRow[51].' '.$ctRow[52],
       	                             );
			
                	if (!writeToFile($fOutPhone, $params)) break;
		}

                //mobile phone
                if (isset($ctRow[53]) && strlen($ctRow[53])>0) {
                        $params = array(    'contact_id'                => $contactID,
                                            'location_type_id'          => 1,
                                            'is_primary'                => 0,
                                            'phone_type_id'             => 247,
                                            'phone'                     => $ctRow[53],
                                     );

                        if (!writeToFile($fOutPhone, $params)) break;
                }

                //fax home
                if (isset($ctRow[54]) && strlen($ctRow[54])>0) {
                        $params = array(    'contact_id'                => $contactID,
                                            'location_type_id'          => 1,
                                            'is_primary'                => 0,
                                            'phone_type_id'             => 248,
                                            'phone'                     => $ctRow[54],
                                     );

                        if (!writeToFile($fOutPhone, $params)) break;
                }

                //fax work 
                if (isset($ctRow[55]) && strlen($ctRow[55])>0) {
                        $params = array(    'contact_id'                => $contactID,
                                            'location_type_id'          => 2,
                                            'is_primary'                => 0,
                                            'phone_type_id'             => 248,
                                            'phone'                     => $ctRow[55],
                                     );

                        if (!writeToFile($fOutPhone, $params)) break;
                }

                //email
                if (strpos($ctRow[39],'@')>0) {
                        $params = array(    'contact_id'                => $contactID,
                                            'location_type_id'          => 1,
                                            'email'                     => $ctRow[39],
                                            'is_primary'                => 1,
                                     );

                        if (!writeToFile($fOutEmail, $params)) break;
                }

		//email from non-omis data
                if (isset($ctRow[56]) && strpos($ctRow[56],'@')>0) {
                        $params = array(    'contact_id'                => $contactID,
                                            'location_type_id'          => 1,
                                            'email'                	=> $ctRow[56],
                                            'is_primary'             	=> 1,
                                     );

                        if (!writeToFile($fOutEmail, $params)) break;
		}

		//create a single note of all the omis data
		$omisData = "";
		foreach ($ctRow as $k=>$d) $omisData .= $aOmisCols[$k] . ": ".$d.'\n';
                $params = array();
                $params['contact_id'] = $session->get( 'userID' ); //who inserted
                $params['entity_table'] = 'civicrm_contact';
                $params['subject'] = 'OMIS DATA';
                $params['modified_date'] = 'NULL';
                $params['entity_id'] = $contactID;
                $params['note'] = $omisData;
                if (!writeToFile($fOutNotes, $params)) break;
                unset($params);

		if (intval($cCounter/1000)==$cCounter/1000.0) {
			$elapsed = getElapsed();
			$str = ($numContacts-$cCounter)." left. ".
				number_format($cCounter/$elapsed,2)."/sec - ".
				prettyFromSeconds(intval(($numContacts-$cCounter)/($cCounter/$elapsed))).
				" - mem:".memory_get_usage()/1000;
        	        cLog(0,'info',"converted {$cCounter}/{$numContacts} contacts. last uniqueID:{$importID} civicrmID:{$contactID} - {$str}");
		}

		//create notes 
		while ($ntRow && $ntRow[0]==$importID) {
			//set note params
			$params = array();
                	$params['contact_id'] = $session->get( 'userID' ); //who inserted
                        $params['entity_table'] = 'civicrm_contact';
                        $params['subject'] = 'OMIS NOTE';
                        $params['modified_date'] = 'NULL';
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
			if (!$ntRow) break;
                        $ntRow[0] = intval($ntRow[0]);
		}


                //create activities from cases
                while ($csRow && $csRow[0]==$importID) {

/*
                	//set note params
                        $params = array();
                        $params['contact_id'] = $session->get( 'userID' );; //who inserted
                        $params['entity_table'] = 'civicrm_contact';
                        $params['subject'] = "OMIS CASE NOTE: " . $csRow[2] ." - ". $csRow[11];
                        $params['modified_date'] = $csRow[20];
                        $params['entity_id'] = $contactID;
                        $params['note'] = "";
			if (strlen($csRow[8])>0) $params['note'] .= 'workphone: '.$csRow[8].'\n';
			if (strlen($csRow[8])>0) $params['note'] .= 'fax: '. $csRow[9].'\n';
			$params['note'] .= $csRow[18] . '\n' . $csRow[19].'\n' . $csRow[20];
*/
                        //set params
                        $params = array();
			$params['id'] = $activityID;
                        $params['source_contact_id'] = $session->get( 'userID' );; //who inserted
                        $params['subject'] = "OMIS CASE ACTIVITY ".intval($csRow[1]).": " . $csRow[2];

			//swap around the dates so it matches contact date format
			$actDate = $csRow[5];
			if (strlen($actDate)==5) $actDate = '0'.$actDate; 
			$actDate = substr($actDate,2,2).substr($actDate,4,2).substr($actDate,0,2);

                        $actCloseDate = $csRow[6];
                        if (strlen($actCloseDate)==5) $actCloseDate = '0'.$actCloseDate;
                        $actCloseDate = substr($actCloseDate,2,2).substr($actCloseDate,4,2).substr($actCloseDate,0,2);

			//format date for db and add time
                        $params['activity_date_time'] = formatDate($actDate).' '.$csRow[4];
			
			//if there's a close date, mark as closed
			if (strlen(trim($csRow[6]))>0) {

				$params['status_id'] = 2;

			//otherwise, if the open date was prior to 2009 mark it as closed
			} elseif (date('Y',strtotime(formatDate($actDate)))<'2009') {

                               	$params['status_id'] = 2;
                        } else {

     				$params['status_id'] = 1;
			}
			
			$params['details'] = '';
                        if (strlen($csRow[6])>0) $params['details'] .= '\nCASE CLOSED ON '.formatDate($csRow[6]);
			if (strlen($csRow[18])>0) $params['details'] .= '\nNote 1: '.$csRow[18];
                        if (strlen($csRow[19])>0) $params['details'] .= '\nNote 2: '.$csRow[19];
                        if (strlen($csRow[20])>0) $params['details'] .= '\nNote 3: '.$csRow[20];
                        if (strlen($csRow[7])>0) $params['details'] .= '\nHome Phone: '.$csRow[7];
                        if (strlen($csRow[8])>0) $params['details'] .= '\nWork Phone: '.$csRow[8];
                        if (strlen($csRow[9])>0) $params['details'] .= '\nFax: '. $csRow[9].'\n';
                        if (strlen($csRow[3])>0) $params['details'] .= '\nStaff: '. $csRow[3].'\n';
                        if (strlen($csRow[10])>0) $params['details'] .= '\nCSNUM: '. $csRow[10].'\n';
                        if (strlen($csRow[11])>0) $params['details'] .= '\nCLAB1: '. $csRow[11].'\n';
                        if (strlen($csRow[12])>0) $params['details'] .= '\nCID1: '. $csRow[12].'\n';
                        if (strlen($csRow[13])>0) $params['details'] .= '\nCLAB2: '. $csRow[13].'\n';
                        if (strlen($csRow[14])>0) $params['details'] .= '\nCID2: '. $csRow[14].'\n';
                        if (strlen($csRow[15])>0) $params['details'] .= '\nIssue: '. $csRow[15].'\n';
                        if (trim($csRow[22])!="|") $params['details'] .= '\nLegislation: '.$csRow[22];
                       
			//activity type
                        switch ($csRow[16]) {

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
                                 case 'E':
                                        $params['activity_type_id'] = 39; //email received
                                        break;
                                 case 'W':
                                        $params['activity_type_id'] = 43; //website mapped to other
                                        break;
				 default:
					$params['activity_type_id'] = 43; //other
  			}

			//set contact target
			$targetParams=array();
			$targetParams['activity_id'] = $activityID;
                        $targetParams['contact_id'] = $contactID;

			//following needs to be set in custom fields
			$custParams=array();
			$custParams['entity_id']=$activityID;
			switch ($csRow[17]) {

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

                        if (!writeToFile($fOutActivity, $params)) break;
                        if (!writeToFile($fOutActivityTarget, $targetParams)) break;
                        if (!writeToFile($fOutActivityCustom, $custParams)) break;
                        unset($params);
                        unset($custParams);

                        //get another case
                        $csRow = getLineAsArray($fCases, '~');
			if (!$csRow) break;
                        $csRow[0] = intval($csRow[0]);
			++$activityID;
                 }

                //create notes from issues 
		//cumulate issues into one note
		$bIssue=false;
		$tstamp = null;
		$note='';

                while ($isRow && $isRow[0]==$importID) {

			$bIssue=true;

			//pass these params to the tag writer since it has to recursively select the tags
			writeRecursiveTags($fOutTags,$contactID,$isRow[4]);

			//if 'Y' then add the tag as a freeform tag
			if (trim($isRow[5])=='Y') writeFreeformTag($fOutTags,$contactID,$isRow[4]);

			//get the most recent date
			$dt = formatDate($isRow[2]);
			if ($tstamp==null || strtotime($dt)>strtotime($tstamp)) $tstamp = $dt;

                        $note .= "Issue Code: ".$isRow[1]." Description: ".$isRow[3].'\n';

                        //get another issues 
                        $isRow = getLineAsArray($fIssues, '~');
                        if (!$isRow) break;
                        $isRow[0] = intval($isRow[0]);
                }

		//set the note params:
                $params = array();
                $params['contact_id'] = $session->get( 'userID' );; //who inserted
                $params['entity_table'] = 'civicrm_contact';
                $params['subject'] = "OMIS ISSUES CODES";
                $params['modified_date'] = $tstamp;
                $params['entity_id'] = $contactID;
		$params['note'] = $note;

		//now write cumulated codes as one note
		if ($bIssue) if (!writeToFile($fOutNotes, $params)) break;
                unset($params);

		//get the next contact
                $ctRow = getLineAsArray($fContacts, '~');
	}

	//write out all relationships

	$aDone = $params;

	foreach ($aRels as $aRel) {
	
		$params=array();
		$params['contact_id_a']=$aRel['contactID'];
                $params['contact_id_b']=$aIDMap[$aRel['parentImportID']];
                $params['relationship_type_id']=$aRelLookup[$aRel['type']];

		//remember for lookups
		$aDone[] = $params;

                //do a check to make sure we haven't done the relationship in reverse
		$skip=false;
		foreach ($aDone as $p) {
			if ($p['contact_id_b']==$params['contact_id_a'] && $p['relationship_type_id']==$params['relationship_type_id']) $skip=true;
		}
		if ($skip) continue;

                writeToFile($fOutRelationship, $params);
	}

        cLog(0,'info',"done converting {$cCounter} contacts.");

	return true;
}

function update($task, $importSet, $sourceDesc) {

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

        $ctRow = getLineAsArray($fContacts, '~');
        $ntRow = getLineAsArray($fNotes, '~');
        $csRow = getLineAsArray($fCases, '~');
        $isRow = getLineAsArray($fIssues, '~');

        //fix the id for omis
        $ctRow[0] = intval($ctRow[0]);
        $ntRow[0] = intval($ntRow[0]);
        $csRow[0] = intval($csRow[0]);
        $isRow[0] = intval($isRow[0]);
        //count number of lines in the file
        $numContacts = countFileLines($fContacts)-$skipped;

        $cCounter=0;

	global $aPrefixMap;
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
                $importID=intval($ctRow[0]);

		switch (strtolower($task)) {

			case 'updatecontactprefixid':
				$prefix_id = isset($aPrefix[$aPrefixMap[intval($ctRow[25])]]) ? $aPrefix[$aPrefixMap[intval($ctRow[25])]] : 'NULL';
        			$dao = &CRM_Core_DAO::executeQuery( "update civicrm_contact set prefix_id={$prefix_id} where source='{$sourceDesc}' AND user_unique_id = {$ctRow[0]};" , CRM_Core_DAO::$_nullArray );
				break;
		}
	
	        $ctRow = getLineAsArray($fContacts, '~');
	}
}

function writeFreeformTag($f, $id, $tag) {

	global $aFreeformTags;
	global $aTags;

        //get master tag list, loads into global var
        if (!isset($aFreeformTags)) getFreeformTags();	

	//create the tag if necessary - can't exist anywhere so using the big Tag category
        if (!isset($aFreeformTags[$tag]) && !isset($aTags[$tag])) {
echo 'doesnt exist $tag';	
        	$session =& CRM_Core_Session::singleton();

		 $params = array(
                                    'entity_id'     => $contact->id,
                                    'name'  => $tag,
				    'description' => $tag,
                                    'parent_id'          => 296, //parent_id of free form tags
                                    'is_selectable'       => 1,
                                    'is_reserverd'    => 0,
				    'used_for' => civicrm_contact,
                                    );
                $oTag = CRM_Core_BAO_Tag::add($params, CRM_Core_DAO::$_nullArray);

		//remember the new tag for reuse
		$aFreeformTags[$tag] = $oTag->id;

		print_r($oTag);	
	}

        $params = array();
        $params['entity_table'] = 'civicrm_contact';
        $params['entity_id'] = $id;
        $params['tag_id'] = $aFreeformTags[$tag]['id'];

        writeToFile($f, $params);	
}

function writeRecursiveTags($f, $id, $tag) {

	global $aTags;
	global $aTagsByID;

	//get master tag list, loads into global var
	if (!isset($aTags)) getTags();

	//check the tag exists
	if (!isset($aTags[$tag])) {
//		cLog(0,'ERROR', "TAG NOT FOUND: ".$params['tag']);
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
	
		writeRecursiveTags($f,$id,$aTagsByID[$aTags[$tag]['parent_id']]);
	}
}

function getTags() {

	global $aTags;
	global $aTagsByID;

	$session =& CRM_Core_Session::singleton();

        $dao = &CRM_Core_DAO::executeQuery( "SELECT name, id, parent_id from civicrm_tag where parent_id=291 or id=291;", CRM_Core_DAO::$_nullArray );

        $aTag = array();

        while ($dao->fetch()) {

                $aTags[$dao->name]['id'] = $dao->id;
                $aTags[$dao->name]['parent_id'] = $dao->parent_id;
		
                $aTagsByID[$dao->id]['name'] = $dao->name;
                $aTagsByID[$dao->id]['parent_id'] = $dao->parent_id;		
        }
}


function getFreeFormTags() {

        global $aFreeformTags;

        $session =& CRM_Core_Session::singleton();

        $dao = &CRM_Core_DAO::executeQuery( "SELECT name, id from civicrm_tag where parent_id=296 or id=296;", CRM_Core_DAO::$_nullArray );

        $aTag = array();

        while ($dao->fetch()) {

                $aFreeformTags[$dao->name] = $dao->id;
        }
}

function importIssueCodes($importSet) {

	echo $importSet;
}

function getOptions($strGroup) {

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
}

