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

require_once 'lib.inc.php';

if (isset($argv[1])) define('CIVICRM_CONFDIR',"/data/www/nyss/sites/{$argv[1]}".RAYROOTDOMAIN);
if (isset($argv[2])) $task = strtolower($argv[2]);
if (isset($argv[3])) $importSet=$argv[3];
$startID = (isset($argv[4])) ? $argv[4] : 0;

require_once RAYCIVIPATH.'civicrm.config.php';
require_once 'CRM/Core/Config.php';
require_once 'CRM/Contact/BAO/Contact.php';
require_once 'CRM/Core/BAO/CustomValueTable.php';

$config =& CRM_Core_Config::singleton();

$session =& CRM_Core_Session::singleton();

$session->set( 'userID',1 );

CRM_Core_DAO::executeQuery( "SET FOREIGN_KEY_CHECKS=0;", CRM_Core_DAO::$_nullArray );

markTime();

switch ($task) {

        case "import":
                importData($importSet);
                break;
	case "showfields":
		showExportableFields();
		break;
        case "importissuelist":
		if (!confirmCheck("importissuelist", "CAREFUL, ONLY ADVISABLE ON A BLANK DATABASE!")) exit;
                importIssueCodes($importSet);
                break;
}



function showExportableFields() {

	$f = CRM_Contact_BAO_Contact::exportableFields('Individual');

	print_r($f);

	echo "\ncustom fields: \n\n";
	foreach ($f as $key=>$val)  if (stristr($key,'custom')) echo $key." => ".$val['title']."\n";
}

function importData($importSet, $startID) {

	//get Genderlist
	//get tag array
	//country id
	//province ids
	//need to set location_type_id to the one that isn't editable
	$ctFields = CRM_Contact_BAO_Contact::exportableFields('Individual');

        $session =& CRM_Core_Session::singleton();
	
	$ctLookupField = array();

	//create lookup for speed
	$ctLookupField = array();
        foreach ($ctFields as $key=>$val) if (stristr($key,'custom')) $ctLookupField[$val['title']] = $key;

	$fContacts = RAYIMPORTDIR.$importSet."/".$importSet."MST.TXT";
        $fCases = RAYIMPORTDIR.$importSet."/".$importSet."CAS.TXT";
        $fNotes = RAYIMPORTDIR.$importSet."/".$importSet."HIS.TXT";
        $fIssues = RAYIMPORTDIR.$importSet."/".$importSet."ISS.TXT";

	$fOutContacts = fopen(RAYTMP.'ct.csv', 'w');
        $fOutAddress = fopen(RAYTMP.'ad.csv', 'w');
        $fOutPhone = fopen(RAYTMP.'ph.csv', 'w');
        $fOutCustom = fopen(RAYTMP.'cu.csv', 'w');

	//initialize the arrays, first line is header so throwaway 
        $ctRow = getLineAsArray($fContacts, '~');
        $ntRow = getLineAsArray($fNotes, '~');
        $csRow = getLineAsArray($fCases, '~');
        $isRow = getLineAsArray($fIssues, '~');

	//get the first entry
	$skipped=0;
	while (intval($ctRow[0])<$startID) {$ctRow = getLineAsArray($fContacts, '~'); ++$skipped;}
        while (intval($ntRow[0])<$startID) $ntRow = getLineAsArray($fContacts, '~');
        while (intval($csRow[0])<$startID) $csRow = getLineAsArray($fContacts, '~');

        cLog(0,'info',"importing {$numContacts} lines starting with $startID, skipped $skipped");

	//fix the id for omis
        $ctRow[0] = intval($ctRow[0]);
	$ntRow[0] = intval($ntRow[0]);
        $csRow[0] = intval($csRow[0]);

	//count number of lines in the file
	$numContacts = countFileLines($fContacts)-$skipped; 

	cLog(0,'info',"importing {$numContacts} lines");

	$cCounter=0;

	while ($ctRow) {

		++$cCounter;

		if (RAYDEBUG) markTime('getLine');

		//unset($ctRow);
	        //$ctRow = getLineAsArray($fContacts, '~');

		$ctParams = array();
		$ctParams['user_unique_id'] = intval($ctRow[0]);
		$ctParams['first_name'] = $ctRow[2]; 
                $ctParams['middle_name'] = $ctRow[3]; 
                $ctParams['last_name'] = $ctRow[1]; 
                $ctParams['sort_name'] = $ctRow[1].', '.$ctRow[2]; 
                $ctParams['displany_name'] = $ctRow[1].', '.$ctRow[2]; 
                $ctParams['nick_name'] = $ctRow[1]; //nickname
		$ctParams['gender_id'] = ($ctRow[17]=='M') ? 29 : 28;

                //if (!writeToFile($fhout, $aOut, $processedLines, $totalNum)) break;

		$ctParams['address'][1] = array ( 'location_type_id'      => 1,
                                              'is_primary'                => 1,
                                              'street_number'             => $ctRow[5],
                                              'street_address'            => $ctRow[6],
                                              'supplemental_address_1'    => $ctRow[7],
                                              'supplemental_address_2'    => null,
                                              'city'                      => $ctRow[8],
                                              'postal_code'               => $ctRow[10].'-'.$ctRow[11],
                                              'country_id'                => 1228,
                                              'state_province_id'         => 1031);

	        $ctParams['phone'][1] = array(
                                    'location_type_id'          => 1,
                                    'is_primary'                => 1,
                                    'phone_type_id' 		=> 1,
                                    'phone'         		=> $ctRow[30],
                                    );

		//doesn't work yet
                $ctParams['custom'][$ctLookupField['Congressional District']] = $ctRow[22];
                $ctParams['custom'][$ctLookupField['Election District']] = $ctRow[23];
                $ctParams['custom'][$ctLookupField['NY Senate District']] = $ctRow[21];
                $ctParams['custom'][$ctLookupField['NY Assembly District']] = $ctRow[24];
                $ctParams['custom'][$ctLookupField['NY Assembly District']] = $ctRow[24];

		$ctParams['tag'] = array();

		//check for existing contact
		//$search = array('user_unique_id' => $ctParams['user_unique_id']);
		//$vals = array();
        	//$contact = CRM_Contact_BAO_Contact::retrieve( $search, $vals );
		//print_r($contact);
		//exit;

        	$dao =& CRM_Core_DAO::executeQuery( "select * from civicrm_contact where id=-100{$ctParams['user_unique_id']}", CRM_Core_DAO::$_nullArray );

	        //if ( $dao->fetch( ) ) {

		
		//update contact
        	//if ( $contact->id ) {

                //insert contact
		//} else {

			if (RAYDEBUG) markTime('createContact');
			$contactID = &CRM_Contact_BAO_Contact::createProfileContact( $ctParams, $ctFields, null, null, null, null, true );
			if (RAYDEBUG) cLog(0,'debug','createContact: '.getElapsed('createContact'));

			$elapsed = getElapsed();
			$str = ($numContacts-$cCounter)." left. ".number_format($cCounter/$elapsed,2)."/sec - ".prettyFromSeconds(intval(($numContacts-$cCounter)/($cCounter/$elapsed)))." - mem:".memory_get_usage()/1000;
                        cLog(0,'info',"saved contact: {$cCounter}/{$numContacts} uniqueID:{$ctParams['user_unique_id']} civicrmID:{$contactID} - {$str}");

                        unset($ctParams);

			//load the contact to get the address id
			//district info is address specific                  
			$params = array('id'=>$contactID);     

			if (RAYDEBUG) markTime('loadContact');
			$contact = &CRM_Contact_BAO_Contact::retrieve($params);
			if (RAYDEBUG) cLog(0,'debug','loadContact: '.getElapsed('loadContact'));

			$ctCust = array();
			$ctCust['entityID'] = $contact->address[1]['id'];
			$ctCust[$ctLookupField['Congressional District']] = $ctRow[22];
        	        $ctCust[$ctLookupField['Election District']] = $ctRow[23];
        	        $ctCust[$ctLookupField['NY Senate District']] = $ctRow[21];
        	        $ctCust[$ctLookupField['NY Assembly District']] = $ctRow[24];
        	        $ctCust[$ctLookupField['NY Assembly District']] = $ctRow[24];

			if (RAYDEBUG) markTime('saveCustomData');
        		CRM_Core_BAO_CustomValueTable::setValues( $ctCust );
			if (RAYDEBUG) cLog(0,'debug','saveCustomData: '.getElapsed('saveCustomData'));

			unset($ctCust);
			unset($contact);

			//test: $dao = CRM_Core_DAO::executeQuery( "insert into civicrm_contact(id,first_name) values(134134,'sacha')", null);

			//create issues

			//create notes 

			//ensure we read first note. first row is header
			//on subsequent contacts, the last read note will belong to the new contact
	        	if (!$ntRow) {
				$ntRow = getLineAsArray($fNotes, '~');
                                $ntRow = getLineAsArray($fNotes, '~');
	                        $ntRow[0] = intval($ntRow[0]);
			}

			while ($ntRow && $ntRow[0]==$contact->user_unique_id) {

                                //check for existing note

				//set note params
				$ntParams = array();
                		$ntParams['contact_id'] = $session->get( 'userID' ); //who inserted
                                $ntParams['entity_table'] = 'civicrm_contact';
                                $ntParams['entity_id'] = $ntRow[0]; //belongs to id
                		$ntParams['note'] = $ntRow[3]."\n".
					$ntRow[4]."\n".
                                        $ntRow[5]."\n".
                                        $ntRow[6]."\n".
                                        $ntRow[7]."\n".
                                        $ntRow[8]."\n".
                                        $ntRow[9]."\n".
                                        $ntRow[10]."\n".
                                        $ntRow[11]."\n".
                                        $ntRow[12]."\n".
                                        $ntRow[13]."\n".
                                        $ntRow[14]."\n".
                                        $ntRow[15]."\n".
                                        $ntRow[16]."\n".
                                        $ntRow[17]."\n".
                                        $ntRow[18]."\n";

				//insert note
				CRM_Core_BAO_Note::add( $ntParams, $ids );				

				unset($ntParams);
				unset($ntRow);

				//get another note
				$ntRow = getLineAsArray($fNotes, '~');					
                                $ntRow[0] = intval($ntRow[0]);
			}


                        //create notes from cases
                        while ($csRow && $csRow[0]==$contact->user_unique_id) {

                                //check for existing case

                                //set note params
                                $csParams = array();
                                $csParams['contact_id'] = $session->get( 'userID' );; //who inserted
                                $csParams['entity_table'] = 'civicrm_contact';
                                $csParams['entity_id'] = $csRow[0]; //belongs to id
                                $csParams['subject'] = $csRow[2] ." - ". $csRow[11];
                                $csParams['modified_date'] = $csRow[20];
                                $csParams['note'] = "workphone: ".$csRow[8]."\nfax: ".
                                        $csRow[9]."\n".
					$csRow[18]."\n".
                                        $csRow[19]."\n".
                                        $csRow[20]."";

                                //insert note
                                CRM_Core_BAO_Note::add( $csParams, null );
				unset($csParams);
				unset($csRow);
                                //get another note
                                $csRow = getLineAsArray($fCases, '~');
                                $csRow[0] = intval($csRow[0]);
                        }
		//}

		//exit;
	}
}

function importIssueCodes($importSet) {

	echo $importSet;
}

