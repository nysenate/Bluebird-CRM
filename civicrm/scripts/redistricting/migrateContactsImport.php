<?php

// Project: BluebirdCRM
// Authors: Stefan Crain, Graylin Kim, Ken Zalewski
// Organization: New York State Senate
// Date: 2012-10-26
// Revised: 2012-11-21
// Revised: 2023-09-29 - handle out-of-bounds prefix_id/suffix_id values

// ./migrateContactsImport.php -S skelos --filename=migrate --dryrun
error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

define('KEYWORD_PARENT_ID', 296);
define('MAX_PREFIX_ID', 82);
define('MAX_SUFFIX_ID', 20);


class CRM_migrateContactsImport {

  function run() {

    global $_SERVER;

    //set memory limit so we don't max out
    ini_set('memory_limit', '5G');

    require_once realpath(dirname(__FILE__)).'/../script_utils.php';

    // Parse the options
    $shortopts = "f:nl:";
    $longopts = ["filename=", "dryrun", "log="];
    $optlist = civicrm_script_init($shortopts, $longopts, TRUE);

    if ($optlist === NULL) {
        $stdusage = civicrm_script_usage();
        $usage = '--filename FILENAME  [--dryrun]  [--log LEVEL]';
        error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
        exit(1);
    }

    if (empty($optlist['filename'])) {
      bbscript_log(LL::FATAL, "No filename provided. You must provide a filename to import.");
      exit();
    }

    if (!empty($optlist['log'])) {
      set_bbscript_log_level($optlist['log']);
    }

    //get instance settings which represents the destination instance
    $bbcfg_dest = get_bluebird_instance_config($optlist['site']);
    //bbscript_log(LL::TRACE, '$bbcfg_dest', $bbcfg_dest);

    require_once 'CRM/Utils/System.php';

    $civicrm_root = $bbcfg_dest['drupal.rootdir'].'/sites/all/modules/civicrm';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    /*if (!CRM_Utils_System::loadBootstrap([], FALSE, FALSE, $civicrm_root)) {
      CRM_Core_Error::debug_log_message('Failed to bootstrap CMS from migrateContactsImport.');
      return FALSE;
    }*/

    $dest = [
      'name' => $optlist['site'],
      'num' => $bbcfg_dest['district'],
      'db' => $bbcfg_dest['db.civicrm.prefix'].$bbcfg_dest['db.basename'],
      'files' => $bbcfg_dest['data.rootdir'],
      'domain' => $optlist['site'].'.'.$bbcfg_dest['base.domain'],
    ];
    //bbscript_log(LL::TRACE, "$dest", $dest);

    //if dest unset/irretrievable, exit
    if (empty($dest['db'])) {
      bbscript_log(LL::FATAL, "Unable to retrieve configuration for destination instance.");
      exit();
    }

    // Initialize CiviCRM
    require_once 'CRM/Core/Config.php';
    $config = CRM_Core_Config::singleton();
    $session = CRM_Core_Session::singleton();

    //override geocode method
    $config->geocodeMethod = '';

    //retrieve/set other options
    $optDry = $optlist['dryrun'];

    //set import folder based on environment
    $fileDir = '/data/redistricting/bluebird_'.$bbcfg_dest['install_class'].'/migrate';
    if (!file_exists($fileDir)) {
      mkdir( $fileDir, 0775, TRUE );
    }

    //check for existence of file to import
    $importFile = $fileDir.'/'.$optlist['filename'];
    if (!file_exists($importFile) && !$optDry) {
      bbscript_log(LL::FATAL, "The import file you have specified does not exist. It must reside in {$fileDir}.");
      exit();
    }

    //call main import function
    self::importData($dest, $importFile, $optDry);

    //process greetings
    $greetingScript = 'php '.$bbcfg_dest['app.rootdir'].'/civicrm/scripts/updateAllGreetings.php -S '.$bbcfg_dest['shortname'].' --quiet';
    shell_exec($greetingScript);
  }//run

  function importData($dest, $importFile, $optDryParam) {
    global $optDry;
    global $exportData;
    global $mergedContacts;
    global $selfMerged;

    bbscript_log(LL::INFO, __METHOD__);

    //set global to value passed to function
    $optDry = $optDryParam;

    //bbscript_log(LL::TRACE, "importData dest", $dest);
    bbscript_log(LL::INFO, "importing data using... $importFile");

    //retrieve data from file and set to variable as array
    $exportData = json_decode(file_get_contents($importFile), TRUE);
    //bbscript_log(LL::TRACE, 'importData $exportData', $exportData);

    //parse the import file source/dest, compare with params and return a warning message if values do not match
    if (!$optDry && $exportData['dest']['name'] != $dest['name']) {
      bbscript_log(LL::FATAL, 'The destination defined in the import file does not match the parameters passed to the script. Exiting the script as a mismatched destination could create significant data problems. Please investigate and then rerun the script.');
      exit();
    }

    //add app.dir so we can use it later
    $bbconfig = get_bluebird_instance_config($dest['name']);
    $exportData['dest']['app'] = $bbconfig['app.rootdir'];

    $source = $exportData['source'];

    //get bluebird administrator id to set as source
    $sql = "
      SELECT id
      FROM civicrm_contact
      WHERE display_name = 'Bluebird Administrator'
    ";
    $bbAdmin = CRM_Core_DAO::singleValueQuery($sql);
    $bbAdmin = ($bbAdmin) ?: 1;

    $statsTemp = $selfMerged = [];

    //process the import
    self::importAttachments($exportData);
    self::importContacts($exportData, $statsTemp, $bbAdmin);
    self::importActivities($exportData, $bbAdmin);
    self::importCases($exportData, $bbAdmin);
    self::importTags($exportData);
    self::importEmployment($exportData);
    self::importHouseholdRels($exportData);
    self::importDistrictInfo($exportData);

    //create group and add migrated contacts
    self::addToGroup($exportData);

    $source = $exportData['source'];

    bbscript_log(LL::INFO, "Completed contact migration import from district {$source['num']} ({$source['name']}) to district {$dest['num']} ({$dest['name']}) using {$importFile}.");

    //bbscript_log(LL::TRACE, 'importData $mergedContacts', $mergedContacts);

    //generate report stats
    $caseList = [];
    if (isset($exportData['cases'])) {
      foreach ($exportData['cases'] as $extID => $cases) {
        foreach ($cases as $case) {
          $caseList[] = $case;
        }
      }
    }

    $statsTempALC = $statsTemp['address_location_conflicts'] ?? [];
    $exportDataTags = $exportData['tags'] ?? [];

    $stats = [
      'total contacts' => count($exportData['import']),
      'individuals' => $statsTemp['Individual'],
      'organizations' => $statsTemp['Organization'],
      'households' => $statsTemp['Household'],
      'addresses with location conflicts (skipped)' => count($statsTempALC['skip'] ?? []),
      'addresses with location conflicts (new location assigned)' => count($statsTempALC['newloc'] ?? []),
      'employee/employer relationships' => count($exportData['employment'] ?? []),
      'total contacts merged with existing records' => $mergedContacts['All'],
      'individuals merged with existing records' => $mergedContacts['Individual'],
      'organizations merged with existing records' => $mergedContacts['Organization'],
      'households merged with existing records' => $mergedContacts['Household'],
      'contacts self-merged with other imported records (count)' => count($selfMerged ?? []),
      'activities' => count($exportData['activities'] ?? []),
      'cases' => count($caseList ?? []),
      'keywords' => count($exportDataTags['keywords'] ?? []),
      'first level issue codes' => count($exportDataTags['issuecodes'] ?? []),
      'positions' => count($exportDataTags['positions'] ?? []),
      'web bills' => count($exportDataTags['webBills'] ?? []),
      'web committees' => count($exportDataTags['webCommittees'] ?? []),
      'web issues' => count($exportDataTags['webIssues'] ?? []),
      'web petitions' => count($exportDataTags['webPetitions'] ?? []),
      'attachments' => count($exportData['attachments'] ?? []),
      'expanded details for various stats' => [
        'contacts self-merged with other imported records (current contact -> existing contact)' => $selfMerged ?? NULL,
        'addresses with location conflicts (skipped)' => $statsTempALC['skip'] ?? NULL,
        'addresses with location conflicts (new location assigned)' => $statsTempALC['newloc'] ?? NULL,
      ],
    ];
    bbscript_log(LL::INFO, "Migration statistics:", $stats);

    //log to file
    if (!$optDry) {
      //set import folder based on environment
      $fileDir = '/data/redistricting/bluebird_'.$bbconfig['install_class'].'/MigrationReports';
      if (!file_exists($fileDir)) {
        mkdir( $fileDir, 0775, TRUE );
      }

      $reportFile = $fileDir.'/'.$source['name'].'_'.$dest['name'].'.txt';
      $fileResource = fopen($reportFile, 'w');

      $content = [
        'options' => $exportData['options'] ?? NULL,
        'stats' => $stats,
      ];

      $content = print_r($content, TRUE);
      fwrite($fileResource, $content);
    }

    //now run cleanup scripts
    $dryParam = ($optDry) ? "--dryrun" : '';
    $scriptPath = $bbconfig['app.rootdir'].'/civicrm/scripts';
    $logLevel = get_bbscript_log_level();
    $cleanAddress = "php {$scriptPath}/dedupeAddresses.php -S {$dest['name']} --log={$logLevel}";
    $cleanRecords = "php {$scriptPath}/dedupeSubRecords.php -S {$dest['name']} --log={$logLevel} {$dryParam}";

    if (!$optDry) {
      system($cleanAddress);
      system($cleanRecords);
    }

    //cleanup log records
    self::_cleanLogRecords();
  }//importData

  /*
   * handles the creation of the file records in the db
   * this function must precede activities, case activities and attachment custom fields
   * so the new file record can be referenced when the entity record is created
   */
  function importAttachments($exportData) {
    global $optDry;
    global $attachmentIDs;

    bbscript_log(LL::INFO, __METHOD__);

    if (!isset($exportData['attachments'])) {
      return;
    }

    $attachmentIDs = [];
    $filePath = $exportData['dest']['files'].'/'.$exportData['dest']['domain'].'/civicrm/custom/';

    foreach ($exportData['attachments'] as $attachExtID => $details) {
      $sourceFilePath = $details['source_file_path'];

      $details['source_file_path'] = $filePath.$details['uri'];
      $file = self::_importAPI('file', 'create', $details);
      bbscript_log(LL::TRACE, 'importAttachments $file', $file);

      //construct source->dest IDs array
      $attachmentIDs[$attachExtID] = $file['id'];

      //copy the file to the destination folder
      self::_copyAttachment($filePath, $sourceFilePath, $details['source_file_path']);
    }
  }//importAttachments

  function importContacts($exportData, &$stats, $bbAdmin) {
    global $optDry;
    global $extInt;
    global $mergedContacts;

    bbscript_log(LL::INFO, __METHOD__);

    //make sure the $extInt IDs array is reset during importContacts
    //['external_identifier' => 'target contact id']
    $extInt = [];
    $relatedTypes = [
      'email', 'phone', 'website', 'im', 'address', 'note',
      'Additional_Constituent_Information', 'Attachments', 'Contact_Details', 'Organization_Constituent_Information',
      'Website_Profile',
    ];
    //records which use entity_id rather than contact_id as foreign key
    $fkEId = [
      'note', 'Additional_Constituent_Information', 'Attachments',
      'Contact_Details', 'Organization_Constituent_Information',
      'Website_Profile',
    ];

    //initialize stats arrays
    $mergedContacts = $stats = [
      'Individual' => 0,
      'Organization' => 0,
      'Household' => 0,
      'All' => 0,
    ];

    //increase external_identifier field length to varchar(64)
    if (!$optDry) {
      $sql = "
        ALTER TABLE civicrm_contact
        MODIFY external_identifier varchar(64);
      ";
      CRM_Core_DAO::executeQuery($sql);
    }

    foreach ($exportData['import'] as $extID => $details) {
      //bbscript_log(LL::TRACE, 'importContacts $details', $details);
      bbscript_log(LL::DEBUG, "importContacts() extID = {$extID}");
      $stats[$details['contact']['contact_type']] ++;

      //check greeting fields
      self::_checkGreeting($details['contact']);

      //look for existing contact record in target db and add to params array
      $matchedContact = self::_contactLookup($details, $exportData['dest']);
      if ($matchedContact) {
        //count merged
        $mergedContacts[$details['contact']['contact_type']] ++;
        $mergedContacts['All'] ++;

        //if updating existing contact, fill only
        self::_fillContact($matchedContact, $details);

        //set id
        $details['contact']['id'] = $matchedContact;
      }

      //clean the contact array
      $details['contact'] = self::_cleanArray($details['contact']);

      //make sure required fields exist
      switch ($details['contact']['contact_type']) {
        case 'Individual':
          if (empty($details['contact']['first_name']) &&
            empty($details['contact']['last_name']) &&
            !$matchedContact
          ) {
            $details['contact']['first_name'] = 'Contact';
            $details['contact']['last_name'] = $details['contact']['external_identifier'];
          }
          break;
        case 'Organization':
          if (empty($details['contact']['organization_name']) &&
            !$matchedContact
          ) {
            $details['contact']['organization_name'] = 'Organization '.$details['contact']['external_identifier'];
          }
          break;
        case 'Household':
          if (empty($details['contact']['household_name']) &&
            !$matchedContact
          ) {
            $details['contact']['household_name'] = 'Household '.$details['contact']['external_identifier'];
          }
          break;
        default:
          $details['contact']['display_name'] = 'Unknown Contact';
      }

      //import the contact via api
      $contact = self::_importAPI('contact', 'create', $details['contact'], TRUE);
      bbscript_log(LL::TRACE, "importContacts _importAPI contact", $contact);

      //set the contact ID for use in related records; also build mapping array
      if ($optDry && $matchedContact) {
        $contactID = $matchedContact;
      }
      else {
        $contactID = $contact['id'] ?? NULL;
      }
      $extInt[$extID] = $contactID;

      //cycle through each set of related records
      foreach ($relatedTypes as $type) {
        $fk = (in_array($type, $fkEId)) ? 'entity_id' : 'contact_id';
        if (isset($details[$type])) {
          bbscript_log(LL::DEBUG, "importContacts() processing related {$type} records");
          foreach ($details[$type] as $record) {
            switch ($type) {
              case 'Attachments':
                //bbscript_log(LL::TRACE, "importContacts attachments record", $record);
                //handle attachments via sql rather than API
                $attachSqlEle = [];

                //get new attachment IDs
                foreach ($record as $attF => $attV) {
                  if (!empty($attV)) {
                    $attachSqlEle[$attF] = self::_importEntityAttachments($contactID, $attV, 'civicrm_value_attachments_5');
                  }
                }
                if (!empty($attachSqlEle)) {
                  $attachSql = "
                    INSERT IGNORE INTO civicrm_value_attachments_5
                    (entity_id, ".implode(', ', array_keys($attachSqlEle)).")
                    VALUES
                    ({$contactID}, ".implode(', ', $attachSqlEle).")
                  ";
                  //bbscript_log(LL::TRACE, 'importContacts $attachSql', $attachSql);

                  if ($optDry) {
                    bbscript_log(LL::DEBUG, "importing attachments for contact", $record);
                  }
                  else {
                    CRM_Core_DAO::executeQuery($attachSql);
                  }
                }
                break;

              case 'address':
                //if location type is missing, set it to home and if needed, it will be corrected below
                if (empty($record['location_type_id'])) {
                  $record['location_type_id'] = 1;
                }

                //need to fix location types so we don't overwrite
                $existingAddresses = CRM_Core_BAO_Address::allAddress( $contactID );
                if (!empty($existingAddresses)) {
                  if (array_key_exists($record['location_type_id'], $existingAddresses)) {
                    //bbscript_log(LL::TRACE, 'importContacts $record', $record);

                    //we have a location conflict -- either skip importing this address, or assign new loc type
                    $action = self::_compareAddresses($record['location_type_id'], $existingAddresses, $record);

                    if ($action == 'skip') {
                      $stats['address_location_conflicts']['skip'][] = "CID{$contactID}_LOC{$record['location_type_id']}";
                      break;
                    }
                    elseif ($action == 'newloc') {
                      $stats['address_location_conflicts']['newloc'][] = "CID{$contactID}_LOC{$record['location_type_id']}";
                      //attempt to assign to other, other2, main, main2
                      foreach ([4,11,3,12] as $newLocType) {
                        if (!array_key_exists($newLocType, $existingAddresses)) {
                          $record['location_type_id'] = $newLocType;
                          break;
                        }
                      }
                    }
                  }
                }

                $record[$fk] = $contactID;
                self::_importAPI($type, 'create', $record);
                break;

              case 'note':
                if (empty($record['modified_date'])) {
                  $record['modified_date'] = '2009-09-30';
                }
                $record[$fk] = $contactID;
                $record['contact_id'] = $bbAdmin;
                self::_importAPI($type, 'create', $record);
                break;

              default:
                $record[$fk] = $contactID;
                self::_importAPI($type, 'create', $record);
            }
          }
        }
      }
    }
  }//importContacts

  function importActivities($exportData, $bbAdmin) {
    global $optDry;
    global $extInt;

    bbscript_log(LL::INFO, __METHOD__);

    if (!isset($exportData['activities'])) {
      return;
    }

    foreach ($exportData['activities'] as $actID => $details) {
      $params = $details['activity'];
      $params['source_contact_id'] = $bbAdmin;
      unset($params['activity_id']);
      unset($params['source_record_id']);
      unset($params['parent_id']);
      unset($params['original_id']);
      unset($params['entity_id']);

      //prevent error if subject is missing
      if (empty($params['subject'])) {
        $params['subject'] = '(none)';
      }

      $targets = [];
      foreach ($details['targets'] as $tExtID) {
        $targets[] = $extInt[$tExtID];
      }
      $params['target_contact_id'] = $targets;

      if (isset($details['custom'])) {
        $params['custom_43'] = $details['custom']['place_of_inquiry_43'];
        $params['custom_44'] = $details['custom']['activity_category_44'];
      }

      //make sure priority is set
      if (empty($params['priority_id'])) {
        $params['priority_id'] = 2;
      }

      //clean params array
      $params = self::_cleanArray($params);

      $newActivity = self::_importAPI('activity', 'create', $params);
      //bbscript_log(LL::TRACE, 'importActivities newActivity', $newActivity);

      //handle attachments
      if (isset($details['attachments'])) {
        foreach ($details['attachments'] as $attID) {
          self::_importEntityAttachments($newActivity['id'], $attID, 'civicrm_activity');
        }
      }
    }
  }//importActivities

  function importCases($exportData, $bbAdmin) {
    global $optDry;
    global $extInt;

    bbscript_log(LL::INFO, __METHOD__);

    if (!isset($exportData['cases'])) {
      return;
    }

    //store old->new activity ID so we can set original id value
    $oldNewActID = [];

    //store activities where is_current_revision = 0 for post processing
    $nonCurrentActivity = [];

    //cycle through contacts
    foreach ($exportData['cases'] as $extID => $cases) {
      $contactID = $extInt[$extID];

      //cycle through cases
      foreach ($cases as $case) {
        $activities = $case['activities'];
        unset($case['activities']);

        $case['contact_id'] = $contactID;
        $case['creator_id'] = $bbAdmin;
        //$case['debug'] = 1;

        //prevent error if case subject is missing
        if (empty($case['subject'])) {
          $case['subject'] = '(none)';
        }

        $newCase = self::_importAPI('case', 'create', $case);
        //bbscript_log(LL::TRACE, "importCases newCase", $newCase);

        $caseID = $newCase['id'];

        //6313 remove newly created open case activity before we migrate activities
        $sql = "
          SELECT ca.id, ca.activity_id
          FROM civicrm_case_activity ca
          JOIN civicrm_activity a
            ON ca.activity_id = a.id
            AND a.activity_type_id = 13
          WHERE ca.case_id = {$caseID}
        ";
        if (!$optDry) {
          $openCase = CRM_Core_DAO::executeQuery($sql);
          while ($openCase->fetch()) {
            $sql = "
              DELETE FROM civicrm_activity
              WHERE id = {$openCase->activity_id}
            ";
            CRM_Core_DAO::executeQuery($sql);
            $sql = "
              DELETE FROM civicrm_case_activity
              WHERE id = {$openCase->id}
            ";
            CRM_Core_DAO::executeQuery($sql);
          }
        }

        foreach ($activities as $oldID => $activity) {
          $activity['source_contact_id'] = $bbAdmin;
          $activity['target_contact_id'] = $contactID;
          $activity['case_id'] = $caseID;

          //check for and reset original_id
          if (!empty($activity['original_id'])) {
            if (array_key_exists($activity['original_id'], $oldNewActID)) {
              $activity['original_id'] = $oldNewActID[$activity['original_id']];
            }
            elseif (!$optDry) {
              bbscript_log(LL::DEBUG, "Unable to set the original_id for case activity.", $activity);
              unset($activity['original_id']);
            }
          }

          //unset some values we don't need to migrate
          unset($activity['parent_id']);
          unset($activity['source_record_id']);

          //prevent error if subject is missing
          if (empty($activity['subject'])) {
            $activity['subject'] = '(none)';
          }

          $newActivity = self::_importAPI('activity', 'create', $activity);
          //bbscript_log(LL::TRACE, 'importCases newActivity', $newActivity);

          $oldNewActID[$oldID] = $newActivity['id'];

          //check is_current_revision
          if (isset($activity['is_current_revision']) && $activity['is_current_revision'] != 1) {
            $nonCurrentActivity[] = $newActivity['id'];
          }

          //handle attachments
          if (isset($activity['attachments'])) {
            foreach ($activity['attachments'] as $attID) {
              self::_importEntityAttachments($newActivity['id'], $attID, 'civicrm_activity');
            }
          }
        }
      }
    }

    //process non current activities
    if (!empty($nonCurrentActivity) && !$optDry) {
      $nonCurrentActivityList = implode(',', $nonCurrentActivity);
      $sql = "
        UPDATE civicrm_activity
        SET is_current_revision = 0
        WHERE id IN ({$nonCurrentActivityList})
      ";
      CRM_Core_DAO::executeQuery($sql);
    }
  }//importCases

  function importTags($exportData) {
    global $optDry;
    global $extInt;

    bbscript_log(LL::INFO, __METHOD__);

    $tagExtInt = [];

    if (!isset($exportData['tags'])) {
      return;
    }

    //process keywords
    foreach ($exportData['tags']['keywords'] as $keyID => $keyDetail) {
      $params = [
        'name' => $keyDetail['name'],
        'description' => $keyDetail['desc'],
        'parent_id' => KEYWORD_PARENT_ID, //keywords constant
        'sequential' => 1,
      ];

      //attempt a lookup first
      $keyword = self::_importAPI('tag', 'get', $params);
      if (empty($keyword['id'])) {
        $keyword = self::_importAPI('tag', 'create', $params);
      }
      //bbscript_log(LL::TRACE, 'importTags $keyword', $keyword);

      $tagExtInt[$keyID] = $keyword['id'];
    }

    //process positions
    foreach ($exportData['tags']['positions'] as $posID => $posDetail) {
      $sql = "
        SELECT id
        FROM civicrm_tag
        WHERE name = %1
          AND parent_id = 292
      ";
      $intPosID = CRM_Core_DAO::singleValueQuery($sql, [
        1 => [$posDetail['name'], 'String'],
      ]);
      if (!$intPosID) {
        $params = [
          'name' => $posDetail['name'],
          'description' => $posDetail['desc'],
          'parent_id' => 292, //positions constant
        ];
        $newPos = self::_importAPI('tag', 'create', $params);
        //bbscript_log(LL::TRACE, 'importTags newPos', $newPos);
        $intPosID = $newPos['id'];
      }
      $tagExtInt[$posID] = $intPosID;
    }

    //process issue codes
    //begin by constructing base level tag
    $params = [
      'name' => "Migrated from: {$exportData['source']['name']} (SD{$exportData['source']['num']})",
      'description' => 'Tags migrated from other district',
      'parent_id' => 291,
      'sequential' => 1,
    ];
    $icParent = self::_importAPI('tag', 'get', $params, TRUE);
    if (!empty($icParent['id'])) {
      $icParent = self::_importAPI('tag', 'create', $params, TRUE);
    }

    //level 1
    foreach ($exportData['tags']['issuecodes']  as $icID1 => $icD1) {
      $params = [
        'name' => $icD1['name'],
        'description' => $icD1['desc'],
        'parent_id' => $icParent['id'] ?? NULL,
        'sequential' => 1,
      ];

      $icP1 = self::_importAPI('tag', 'get', $params, TRUE);
      if (!empty($icP1['id'])) {
        $icP1 = self::_importAPI('tag', 'create', $params, TRUE);
      }
      $tagExtInt[$icID1] = $icP1['id'] ?? NULL;

      //level 2
      if (isset($icD1['children'])) {
        foreach ($icD1['children'] as $icID2 => $icD2) {
          $params = [
            'name' => $icD2['name'],
            'description' => $icD2['desc'],
            'parent_id' => $icP1['id'] ?? NULL,
            'sequential' => 1,
          ];

          $icP2 = self::_importAPI('tag', 'get', $params, TRUE);
          if (!empty($icP2['id'])) {
            $icP2 = self::_importAPI('tag', 'create', $params, TRUE);
          }
          $tagExtInt[$icID2] = $icP2['id'] ?? NULL;

          //level 3
          if (isset($icD2['children']) && !empty($icP2['id'])) {
            foreach ($icD2['children'] as $icID3 => $icD3) {
              $params = [
                'name' => $icD3['name'],
                'description' => $icD3['desc'],
                'parent_id' => $icP2['id'],
                'sequential' => 1,
              ];

              $icP3 = self::_importAPI('tag', 'get', $params, TRUE);
              if (!empty($icP3['id'])) {
                $icP3 = self::_importAPI('tag', 'create', $params, TRUE);
              }

              if (!empty($icP3['id'])) {
                $tagExtInt[$icID3] = $icP3['id'];

                //level 4
                if (isset($icD3['children'])) {
                  foreach ($icD3['children'] as $icID4 => $icD4) {
                    $params = [
                      'name' => $icD4['name'],
                      'description' => $icD4['desc'],
                      'parent_id' => $icP3['id'],
                      'sequential' => 1,
                    ];

                    $icP4 = self::_importAPI('tag', 'get', $params, TRUE);
                    if (!empty($icP4['id'])) {
                      $icP4 = self::_importAPI('tag', 'create', $params, TRUE);
                    }

                    if (!empty($icP4['id'])) {
                      $tagExtInt[$icID4] = $icP4['id'];

                      //level 5
                      if (isset($icD4['children'])) {
                        foreach ($icD4['children'] as $icID5 => $icD5) {
                          $params = [
                            'name' => $icD5['name'],
                            'description' => $icD5['desc'],
                            'parent_id' => $icP4['id'],
                            'sequential' => 1,
                          ];

                          $icP5 = self::_importAPI('tag', 'get', $params, TRUE);
                          if (!empty($icP5['id'])) {
                            $icP5 = self::_importAPI('tag', 'create', $params, TRUE);
                          }
                          $tagExtInt[$icID5] = $icP5['id'];
                        }
                      }//end level 5
                    }
                  }
                }//end level 4
              }
            }
          }//end level 3
        }
      }//end level 2
    }//end level 1
    //bbscript_log(LL::TRACE, '_importTags $tagExtInt', $tagExtInt);

    //construct tag entity records
    foreach ($exportData['tags']['entities'] as $extID => $extTags) {
      if (empty($extTags)) {
        continue;
      }

      $params = [
        'contact_id' => $extInt[$extID],
      ];
      foreach ($extTags as $tID) {
        if (!empty($tagExtInt[$tID])) {
          $params['tag_id'][] = $tagExtInt[$tID];
        }
      }
      bbscript_log(LL::TRACE, 'importTags entityTag $params', $params);

      //avoid error if no tag_id values present
      if (empty($params['tag_id'])) {
        continue;
      }

      self::_importAPI('entity_tag', 'create', $params);
    }
  }

  function importEmployment(&$exportData) {
    global $optDry;

    bbscript_log(LL::INFO, __METHOD__);

    if (!isset($exportData['employment'])) {
      $exportData['employment'] = [];
      return;
    }

    require_once 'CRM/Contact/BAO/Contact/Utils.php';

    foreach ($exportData['employment'] as  $employeeID => $employerID) {
      if ($optDry) {
        bbscript_log(LL::DEBUG, "creating employment relationship between I-{$employeeID} and O-{$employerID}");
      }
      else {
        $employeeIntID = self::_getIntID($employeeID);
        $employerIntID = self::_getIntID($employerID);
        CRM_Contact_BAO_Contact_Utils::createCurrentEmployerRelationship($employeeIntID, $employerIntID);
      }
    }
  }//importEmployment

  function importHouseholdRels(&$exportData) {
    global $optDry;

    bbscript_log(LL::INFO, __METHOD__);

    if (!isset($exportData['houserels'])) {
      $exportData['houserels'] = [];
      return;
    }

    foreach ($exportData['houserels'] as $rel) {
      $rel['contact_id_a'] = self::_getIntID($rel['contact_id_a']);
      $rel['contact_id_b'] = self::_getIntID($rel['contact_id_b']);

      self::_importAPI('relationship', 'create', $rel);
    }
  }//importHouseholdRels

  function importDistrictInfo($exportData) {
    global $optDry;

    bbscript_log(LL::INFO, __METHOD__);

    if (!isset($exportData['districtinfo'])) {
      return;
    }

    //build array referencing address name field (external address ID value)
    $addrExtInt = [];
    $sql = "
      SELECT id, name
      FROM civicrm_address
      WHERE name IS NOT NULL
    ";
    $ids = CRM_Core_DAO::executeQuery($sql);
    while ($ids->fetch()) {
      $addrExtInt[$ids->name] = $ids->id;
    }

    //get fields and construct array
    $distFields = [];
    $distFieldsDetail = self::getCustomFields('District_Information');
    foreach ($distFieldsDetail as $field) {
      $distFields[$field['column_name']] = $field['id'];
    }

    foreach ($exportData['districtinfo'] as $addrExtID => $details) {
      $details['entity_id'] = $addrExtInt[$addrExtID] ?? NULL;

      //capture errors mapping address external ID
      if (empty($details['entity_id'])) {
        bbscript_log(LL::DEBUG, 'importDistrictInfo: unmatched addrExtID', $addrExtID);
      }

      //clean array: remove elements with no value
      $details = self::_cleanArray($details);

      $distInfo = self::_importAPI('District_Information', 'create', $details);
      //bbscript_log(LL::TRACE, 'importDistrictInfo $distInfo', $distInfo);
    }

    //cleanup address name field (temp ext address ID)
    if (!$optDry) {
      $sql = "
          UPDATE civicrm_address
          SET name = NULL
          WHERE name IS NOT NULL;
        ";
        CRM_Core_DAO::executeQuery($sql);
      }
  }//importDistrictInfo

  /*
   * wrapper for civicrm_api
   * allows us to determine action based on dryrun status and perform other formatting actions
   */
  function _importAPI($entity, $action, $params, $checkExists = FALSE) {
    global $optDry;
    global $customMap;

    //record types which are custom groups
    $customGroups = [
      'Additional_Constituent_Information',
      'Attachments',
      'Contact_Details',
      'Organization_Constituent_Information',
      'District_Information',
      'Website_Profile',
    ];
    $dateFields = [
      'last_import_57',
      'boe_date_of_registration_24',
      'last_modified_79',
      'birth_date_73',
    ];

    //prepare custom fields
    if (in_array($entity, $customGroups)) {
      //get fields and construct array if not already constructed
      if (!isset($customMap[$entity]) || empty($customMap[$entity])) {
        $customDetails = self::getCustomFields($entity);
        foreach ($customDetails as $field) {
          $customMap[$entity][$field['column_name']] = 'custom_'.$field['id'];
        }
      }
      //bbscript_log(LL::TRACE, '_importAPI $customMap', $customMap);

      //cycle through custom fields and convert column name to custom_## format
      foreach ($params as $col => $v) {
        //if a date type column, strip punctuation
        if (in_array($col, $dateFields)) {
          $v = str_replace(['-', ':', ' '], '', $v);
        }
        if (array_key_exists($col, $customMap[$entity])) {
          $params[$customMap[$entity][$col]] = $v;
          unset($params[$col]);
        }
      }

      //change entity value for api
      $entity = 'custom_value';
    }

    //clean the params array
    $params = self::_cleanArray($params);

    if ($optDry) {
      bbscript_log(LL::INFO, "_importAPI entity:{$entity} action:{$action} params:", $params);
    }

    if (!$optDry || $action == 'get') {
      $params['sequential'] = 1;
      bbscript_log(LL::TRACE, '_importAPI $params', $params);
      //bbscript_log(LL::TRACE, '_importAPI $action', $action);
      //bbscript_log(LL::TRACE, '_importAPI $checkExists', $checkExists);

      if ($checkExists && $action == 'create') {
        $getParams = $params;
        unset($getParams['description']);
        $exists = civicrm_api3($entity, 'get', $getParams);
        bbscript_log(LL::TRACE, '_importAPI $exists', $exists);
        bbscript_log(LL::TRACE, '_importAPI $getParams', $getParams);

        if (empty($exists['count']) && !empty($params['external_identifier'])) {
          $exists = civicrm_api3('Contact', 'get', [
            'external_identifier' => $params['external_identifier'],
            'sequential' => 1,
          ]);
          bbscript_log(LL::TRACE, '_importAPI $exists', $exists);
        }
      }

      $skipErrorLog = FALSE;
      if (empty($exists['count'])) {
        try {
          $api = civicrm_api3($entity, $action, $params);
        }
        catch (CRM_Core_Exception $e) {
          if (
            $e->getMessage() != 'DB Error: already exists' &&
            //triggered when entity_tag already exists for contact/tag_id
            $e->getMessage() != 'Unable to add tags' &&
            //need to know if contact fails for any reason
            !in_array($entity, ['contact', 'Contact'])
          ) {
            if ($checkExists) {
              bbscript_log(LL::DEBUG, '_importAPI $exists', $exists);
            }

            bbscript_log(LL::DEBUG, '_importAPI $e', $e);
          }
          else {
            $skipErrorLog = TRUE;
          }
        }
      }

      if ((!empty($api['is_error']) || (empty($api) && empty($exists['values']))) && !$skipErrorLog) {
        bbscript_log(LL::ERROR, "_importAPI error", $api ?? NULL);
        bbscript_log(LL::ERROR, "_importAPI entity: {$entity} // action: {$action}", $params);

        return FALSE;
      }

      return $api ?? ($exists['values'][0] ?? NULL);
    }
  }

  /*
   * dedupe matching function
   * given the values to be imported, lookup using indiv strict default rule
   * return contact ID if found
   */
  function _contactLookup($contact, $dest) {
    global $extInt;
    global $selfMerged;

    $cid = $xid = NULL;

    require_once $dest['app'].'/modules/nyss_dedupe/nyss_dedupe.module';
    //bbscript_log(LL::TRACE, '_contactLookup $contact', $contact);
    //bbscript_log(LL::TRACE, '_contactLookup $dest', $dest);

    //set contact type
    $cType = $contact['contact']['contact_type'];

    //format params to pass to dedupe tool based on contact type
    $params = [];
    $ruleTitle = '';
    switch ($cType) {
      case 'Individual':
        $params['civicrm_contact']['first_name'] = CRM_Utils_Array::value('first_name', $contact['contact']);
        $params['civicrm_contact']['middle_name'] = CRM_Utils_Array::value('middle_name', $contact['contact']);
        $params['civicrm_contact']['last_name'] = CRM_Utils_Array::value('last_name', $contact['contact']);
        $params['civicrm_contact']['suffix_id'] = CRM_Utils_Array::value('suffix_id', $contact['contact']);
        $params['civicrm_contact']['birth_date'] = CRM_Utils_Array::value('birth_date', $contact['contact']);
        $params['civicrm_contact']['gender_id'] = CRM_Utils_Array::value('gender_id', $contact['contact']);
        $ruleTitle = 'Individual Strict (first + last + (street + zip | email))';
        break;

      case 'Organization':
        $params['civicrm_contact']['organization_name'] = CRM_Utils_Array::value('organization_name', $contact['contact']);
        $ruleTitle = 'Organization 1 (name + street + city + email)';
        break;

      case 'Household':
        $params['civicrm_contact']['household_name'] = CRM_Utils_Array::value('household_name', $contact['contact']);
        $ruleTitle = 'Household 1 (name + street + city + email)';
        break;

      default:
    }

    if (isset($contact['address'])) {
      foreach ($contact['address'] as $address) {
        if (!empty($address['street_address']) && $address['is_primary']) {
          $params['civicrm_address']['street_address'] = CRM_Utils_Array::value('street_address', $address);
          $params['civicrm_address']['postal_code'] = CRM_Utils_Array::value('postal_code', $address);
          $params['civicrm_address']['city'] = CRM_Utils_Array::value('city', $address);
        }
      }
    }

    if (isset($contact['email'])) {
      foreach ($contact['email'] as $email) {
        if (!empty($email['email']) && $email['is_primary']) {
          $params['civicrm_email']['email'] = CRM_Utils_Array::value('email', $email);
        }
      }
    }
    $params = CRM_Dedupe_Finder::formatParams($params, $cType);
    $params['check_permission'] = 0;
    //bbscript_log(LL::TRACE, '_contactLookup $params', $params);

    //use dupeQuery hook implementation to build sql
    $o = new stdClass();
    $o->title = $ruleTitle;
    $o->params = $params;
    $o->noRules = false;
    $tableQueries = [];
    nyss_dedupe_civicrm_dupeQuery($o, 'table', $tableQueries);
    //bbscript_log(LL::TRACE, '$tableQueries', $tableQueries);

    $sql = $tableQueries['civicrm.custom.5'];
    $sql = "
      SELECT contact.id, contact.external_identifier
      FROM civicrm_contact as contact
      JOIN ($sql) as dupes
      WHERE dupes.id1 = contact.id
        AND contact.is_deleted = 0
      LIMIT 1
    ";
    //bbscript_log(LL::TRACE, '_contactLookup $sql', $sql);
    $c = CRM_Core_DAO::executeQuery($sql);

    while ($c->fetch()) {
      $cid = $c->id;
      $xid = $c->external_identifier;
    }

    $extID = $contact['contact']['external_identifier'];

    //also try a lookup on external id (which should really only happen during testing)
    if (!$cid) {
      $sql = "
        SELECT id
        FROM civicrm_contact
        WHERE external_identifier = %1
      ";
      $cid = CRM_Core_DAO::singleValueQuery($sql, [
        1 => [$extID, 'String'],
      ]);
    }
    //bbscript_log(LL::TRACE, '_contactLookup $cid', $cid);

    //if a contact is found which we will merge to, check to see if that contact was in our import set
    if ($xid) {
      //see if the matched record external_id is already in our $extInt array
      if (array_key_exists($xid, $extInt)) {
        //current record's ext id => matched record's ext id
        $selfMerged[$extID] = $xid;
      }
    }

    return $cid;
  }//_contactLookup

  /*
   * given an external identifier, try to determine the internal id in the destination db
   */
  function _getIntID($extID) {
    global $extInt;
    global $selfMerged;

    //first look in ext->int mapping
    if (isset($extInt[$extID])) {
      return $extInt[$extID];
    }
    //see if the record self-merged
    elseif (in_array($extID, $selfMerged)) {
      $mergedExtID = array_search($extID, $selfMerged);
      if (isset($extInt[$mergedExtID])) {
        return $extInt[$mergedExtID];
      }
    }
    //try a db lookup
    else {
      $sql = "SELECT id FROM civicrm_contact WHERE external_identifier = '{$extID}';";
      $intID = CRM_Core_DAO::singleValueQuery($sql);
      if ($intID) {
        return $intID;
      }
    }

    return null;
  }//_getIntID

  /*
   * given contact params, ensure greetings are constructed
   */
  function _checkGreeting(&$contact) {
    $gTypes = [
      'email_greeting',
      'postal_greeting',
      'addressee',
    ];

    foreach ($gTypes as $type) {
      if ($contact[$type.'_id'] == 4) {
        if (empty($contact[$type.'_custom'])) {
          $custVal = (!empty($contact[$type.'_display'])) ? $contact[$type.'_display'] : 'Dear Friend';
          $contact[$type.'_custom'] = $custVal;
        }
      }
      else {
        $contact[$type.'_custom'] = '';
      }
    }

    //random bad data fix
    if ($contact['email_greeting_id'] == 9) {
      $contact['email_greeting_id'] = 6;
    }

    //trap errors and set to custom
    require_once 'api/v3/Contact.php';
    $error = _civicrm_api3_greeting_format_params($contact);
    if (civicrm_error($error)) {
      //determine which type errored
      $type = '';
      if (str_contains($error['error_message'], 'email')) {
        $type = 'email_greeting';
      }
      elseif (str_contains($error['error_message'], 'postal')) {
        $type = 'postal_greeting';
      }
      elseif (str_contains($error['error_message'], 'addressee')) {
        $type = 'addressee';
      }
      else {
        return;
      }

      $contact[$type.'_id'] = 4;
      if (empty($contact[$type.'_custom'])) {
        $custVal = (!empty($contact[$type.'_display'])) ? $contact[$type.'_display'] : 'Dear Friend';
        $contact[$type.'_custom'] = $custVal;
      }
      //bbscript_log(LL::TRACE, "greeting format check", $error);
      //bbscript_log(LL::TRACE, "greeting format contact", $contact);

      bbscript_log(LL::INFO, "fixing {$type} for contact {$contact['external_identifier']}");

      //call this function again so we can iterate through each type in case of multiple errors
      self::_checkGreeting($contact);
    }
  }//_checkGreeting

  /*
   * if we are merging the contact with an existing record, we need to fill only
   * (not overwrite) during import
   */
  function _fillContact($matchedID, &$details) {
    global $customGroupID;
    global $customMapID; // ['id' => 'col_name']
    global $optDry;

    $params = [
      'version' => 3,
      'id' => $matchedID,
    ];
    $contact = civicrm_api('contact', 'getsingle', $params);

    $leaveField = ['source', 'external_identifier', 'contact_type'];

    foreach ($contact as $f => $v) {
      //if existing record field has a value, remove from imported record array
      if ((!empty($v) || $v == '0') &&
        isset($details['contact'][$f]) &&
        !in_array($f, $leaveField)
      ) {
        //unset from imported contact array
        unset($details['contact'][$f]);
      }
    }

    //process custom field data
    $customSets = [
      'Additional_Constituent_Information',
      'Attachments',
      'Contact_Details',
      'Organization_Constituent_Information',
      'Website_Profile',
    ];

    foreach ($customSets as $set) {
      //get/set custom group ID
      if (empty($customGroupID[$set])) {
        $customGroupID[$set] = self::getCustomFields($set, 'groupid');
      }
      bbscript_log(LL::TRACE, '_fillContact $customGroupID', $customGroupID);

      //get/set custom fields
      if (empty($customMapID[$set])) {
        $customDetails = self::getCustomFields($set);
        foreach ($customDetails as $field) {
          $customMapID[$set][$field['id']] = $field['column_name'];
        }
      }
      bbscript_log(LL::TRACE, '_fillContact $customMapID', $customMapID);

      if (isset($details[$set])) {
        $params = [
          'version' => 3,
          'entity_id' => $matchedID,
          'custom_group_id' => $customGroupID[$set],
        ];
        $data = self::_importAPI($set, 'get', $params);
        bbscript_log(LL::TRACE, "_fillContact data: $set", $data);

        //trap the error: if get failed, we need to insert a record in the custom data table
        if ($data['is_error'] && !$optDry) {
          bbscript_log(LL::DEBUG, "unable to retrieve {$set} custom data for ID {$matchedID}. inserting record and proceeding.");
          $tbl = self::getCustomFields($set, 'table');
          $sql = "
            INSERT IGNORE INTO {$tbl} (entity_id)
            VALUES ({$matchedID});
          ";
          //TODO should we be running this SQL?
        }

        //cycle through existing custom data and unset from $details if value exists
        if (!empty($data['values'])) {
          foreach ($data['values'] as $existingData) {
            //should probably handle attachments more intelligently
            if ((!empty($existingData['latest']) || $existingData['latest'] == '0') &&
              isset($customMapID[$set][$existingData['id']])
            ) {
              $colName = $customMapID[$set][$existingData['id']];
              unset($details[$set][$colName]);
            }
          }
        }

        bbscript_log(LL::TRACE, '_fillContact $details', $details);
      }
    }
  }

  /*
   * compare imported conflicting address with existing and decide if they match
   * and we should skip import, or they are different and we should assign a new loc type
   */
  function _compareAddresses($locType, $existing, $record) {
    global $exportData;

    //get existing address
    $params = [
      'id' => $existing[$locType],
    ];
    $address = self::_importAPI('address', 'getsingle', $params);

    //bbscript_log(LL::TRACE, "_compareAddresses address", $address);
    //bbscript_log(LL::TRACE, "_compareAddresses record", $record);

    $dupe = TRUE;
    $afs = ['street_address', 'supplemental_address_1', 'city', 'postal_code'];
    foreach ($afs as $af) {
      if ($address[$af] ?? NULL != $record[$af] ?? NULL) {
        $dupe = FALSE;
        break;
      }
    }

    if ($dupe) {
      unset($exportData['districtinfo'][$record['name']]);
      return 'skip';
    }
    else {
      return 'newloc';
    }
  }

  /*
   * helper function to build entity_file record
   * called during contact, activities, and case import
   * we don't have a nice API or BAO function to handle this, so using straight SQL
   * return attachment ID (file_id)
   */
  function _importEntityAttachments($entityID, $attID, $entityType = 'civicrm_activity') {
    global $optDry;
    global $attachmentIDs;

    //when cycling through custom field set, may be handed an array element with empty value
    if (empty($entityID) || empty($attID)) {
      return;
    }

    if ($optDry) {
      bbscript_log(LL::DEBUG, "_importEntityAttachments insert file for {$entityType}");
      return;
    }

    //first check for existence of record
    $sql = "
      SELECT id
      FROM civicrm_entity_file
      WHERE entity_table = '{$entityType}'
        AND entity_id = {$entityID}
        AND file_id = {$attachmentIDs[$attID]}
    ";
    //bbscript_log(LL::TRACE, "_importEntityAttachments attID", $attID);
    //bbscript_log(LL::TRACE, "_importEntityAttachments search", $sql);
    if (CRM_Core_DAO::singleValueQuery($sql)) {
      return;
    }

    //record doesn't exist, proceed with insert
    $sql = "
      INSERT INTO civicrm_entity_file
      ( entity_table, entity_id, file_id )
      VALUES
      ( '{$entityType}', {$entityID}, {$attachmentIDs[$attID]} )
    ";
    //bbscript_log(LL::TRACE, "_importEntityAttachments insert", $sql);
    CRM_Core_DAO::executeQuery($sql);

    //return file ID
    return $attachmentIDs[$attID];
  }//_importAttachments

  /*
   * helper function to copy files from the source directory to destination
   * we copy instead of move because we are timid...
   */
  function _copyAttachment($filePath, $sourceFile, $destFile) {
    global $optDry;

    //make sure destination directory exists
    if (!file_exists($filePath)) {
      mkdir( $filePath, 0775, TRUE );
    }

    //now copy file and fix owner:group
    if ($optDry) {
      bbscript_log(LL::DEBUG, "_copyAttachment: {$sourceFile}");
    }
    else {
      //ensure source file exists
      if (file_exists($sourceFile)) {
        copy($sourceFile, $destFile);
        chown($destFile, 'apache');
        chgrp($destFile, 'bluebird');
      }
      else {
        //file couldn't be found and moved
        bbscript_log(LL::DEBUG, "file could not be located and copied: {$sourceFile}");
      }
    }
  }//_moveAttachment

  /*
   * a log record is created by virtue of using the notes api, which is not desired.
   * rather than mess with core, we will just run a cleanup to remove these log records
   * the records are unique in that the entity_id matches the modified_id (because there is no user session)
   * so we retrieve records like that created within the last hour and delete them
   */
  function _cleanLogRecords() {
    $dateTime = date('Y-m-d H:i:s');
    $sql = "
      DELETE FROM civicrm_log
      WHERE id IN (
        SELECT *
        FROM (
          SELECT id
          FROM civicrm_log
          WHERE modified_date >= DATE_SUB('{$dateTime}', INTERVAL 1 HOUR)
            AND entity_table = 'civicrm_contact'
            AND entity_id = modified_id
        ) migrationLog
      )
    ";
    CRM_Core_DAO::executeQuery($sql);
    bbscript_log(LL::INFO, "cleaning up log table records...");
  }

  /*
   * given an array, cycle through and unset any elements with no value
   */
  function _cleanArray($data) {
    foreach ($data as $f => $v) {
      if (empty($v) && $v !== 0) {
        unset($data[$f]);
      }

      if (is_string($v)) {
        $data[$f] = stripslashes($v);
      }

      //these should never be 0; throws error on import if they are
      if ($v == 0 && in_array($f, ['email_greeting_id', 'postal_greeting_id', 'addressee_id', 'prefix_id', 'suffix_id', 'gender_id'])) {
        unset($data[$f]);
      }

      //ignore custom prefix/suffix values
      if (($f == 'prefix_id' && $v > MAX_PREFIX_ID) || ($f == 'suffix_id' && $v > MAX_SUFFIX_ID)) {
        unset($data[$f]);
      }
    }
    return $data;
  }

  /*
   * create group in destination database and add all contacts
   */
  function addToGroup($exportData) {
    global $optDry;

    $source = $exportData['source'];
    $dest = $exportData['dest'];
    $g = $exportData['group'];

    //contacts
    $contactsList = implode("','", array_keys($exportData['import']));

    if ($optDry) {
      bbscript_log(LL::DEBUG, "Imported contacts to be added to group:", $g);
      bbscript_log(LL::DEBUG, "List of contacts (external ids) added:", $contactsList);
      return;
    }

    //create group in destination database
    $sql = "
      INSERT IGNORE INTO {$dest['db']}.civicrm_group
      (name, title, description, is_active, visibility, is_hidden, is_reserved)
      VALUES
      ('{$g['name']}', '{$g['title']}', '{$g['description']}', 1, 'User and User Admin Only', 0, 0);
    ";
    CRM_Core_DAO::executeQuery($sql);

    //get newly created group
    $sql = "
      SELECT id FROM {$dest['db']}.civicrm_group WHERE name = '{$g['name']}';
    ";
    $groupID = CRM_Core_DAO::singleValueQuery($sql);

    //error handling
    if (!$groupID) {
      bbscript_log(LL::FATAL, "Unable to retrieve migration group ({$g['title']}) and add contacts to group.");
      return;
    }

    //create intermediate temp table to deal with trigger issue
    CRM_Core_DAO::executeQuery("
      CREATE TABLE tmp_migrate_import_addtogroup
      SELECT {$groupID} group_id, id contact_id, 'Added' status
      FROM civicrm_contact
      WHERE external_identifier IN ('{$contactsList}')
    ");

    //add contacts to group
    $sqlInsert = "
      INSERT IGNORE INTO {$dest['db']}.civicrm_group_contact
      (group_id, contact_id, status)
      SELECT *
      FROM tmp_migrate_import_addtogroup
    ";
    bbscript_log(LL::TRACE, "Group insert:", $sqlInsert);
    CRM_Core_DAO::executeQuery($sqlInsert);

    //drop intermediate temp table
    CRM_Core_DAO::executeQuery("DROP TABLE IF EXISTS tmp_migrate_import_addtogroup");

    $now = date('Y-m-d H:i', strtotime('+3 hours', strtotime(date('Y-m-d H:i'))));
    $sqlSubInsert = "
      INSERT IGNORE INTO {$dest['db']}.civicrm_subscription_history
      (contact_id, group_id, date, method, status)
      SELECT id contact_id, {$groupID} group_id, '{$now}' date, 'Admin' method, 'Added' status
      FROM civicrm_contact
      WHERE external_identifier IN ('{$contactsList}');
    ";
    bbscript_log(LL::TRACE, "Group insert:", $sqlInsert);
    CRM_Core_DAO::executeQuery($sqlSubInsert);

    bbscript_log(LL::INFO, "Imported contacts added to group: {$g['title']}");
  }//addToGroup

  /*
   * given a custom data group name, return array of fields
   */
  function getCustomFields($name, $return = 'fields') {
    $group = civicrm_api3('custom_group', 'getsingle', ['name' => $name]);
    if ($return == 'fields') {
      $fields = civicrm_api3('custom_field', 'get', ['custom_group_id' => $group['id']]);
      //bbscript_log(LL::TRACE, 'getCustomFields fields', $fields);
      return $fields['values'];
    }
    elseif ($return == 'table') {
      return $group['table_name'];
    }
    elseif ($return == 'groupid') {
      return $group['id'];
    }
  }

  function getValue($string) {
    if (!$string) {
      return "null";
    }
    else {
      return $string;
    }
  }
}

//run the script
$importData = new CRM_migrateContactsImport();
$importData->run();
