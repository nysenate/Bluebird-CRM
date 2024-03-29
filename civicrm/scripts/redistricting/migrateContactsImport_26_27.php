<?php

// Project: BluebirdCRM
// Authors: Stefan Crain, Graylin Kim, Ken Zalewski
// Organization: New York State Senate
// Date: 2012-10-26
// Revised: 2012-11-21

// ./migrateContactsImport.php -S skelos --filename=migrate --dryrun
error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

define('DEFAULT_LOG_LEVEL', 'TRACE');

class CRM_migrateContactsImport {

  function run() {

    global $_SERVER;

    //set memory limit so we don't max out
    ini_set('memory_limit', '3000M');

    require_once realpath(dirname(__FILE__)).'/../script_utils.php';

    // Parse the options
    $shortopts = "f:n";
    $longopts = array("filename=", "dryrun");
    $optlist = civicrm_script_init($shortopts, $longopts, TRUE);

    if ($optlist === null) {
        $stdusage = civicrm_script_usage();
        $usage = '[--filename FILENAME] [--dryrun]';
        error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
        exit(1);
    }

    if ( empty($optlist['filename']) ) {
      bbscript_log(LL::FATAL, "No filename provided. You must provide a filename to import.");
      exit();
    }

    //get instance settings which represents the destination instance
    $bbcfg_dest = get_bluebird_instance_config($optlist['site']);
    //bbscript_log(LL::TRACE, '$bbcfg_dest', $bbcfg_dest);

    require_once 'CRM/Utils/System.php';

    $civicrm_root = $bbcfg_dest['drupal.rootdir'].'/sites/all/modules/civicrm';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    /*if (!CRM_Utils_System::loadBootstrap(array(), FALSE, FALSE, $civicrm_root)) {
      CRM_Core_Error::debug_log_message('Failed to bootstrap CMS from migrateContactsImport.');
      return FALSE;
    }*/

    $dest = array(
      'name' => $optlist['site'],
      'num' => $bbcfg_dest['district'],
      'db' => $bbcfg_dest['db.civicrm.prefix'].$bbcfg_dest['db.basename'],
      'files' => $bbcfg_dest['data.rootdir'],
      'domain' => $optlist['site'].'.'.$bbcfg_dest['base.domain'],
    );
    //bbscript_log(LL::TRACE, "$dest", $dest);

    //if dest unset/irretrievable, exit
    if ( empty($dest['db']) ) {
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
    if ( !file_exists($fileDir) ) {
      mkdir( $fileDir, 0775, TRUE );
    }

    //check for existence of file to import
    $importFile = $fileDir.'/'.$optlist['filename'];
    if ( !file_exists($importFile) ) {
      bbscript_log(LL::FATAL, "The import file you have specified does not exist. It must reside in {$fileDir}.");
      exit();
    }

    //call main import function
    self::importData($dest, $importFile, $optDry);
  }//run

  function importData($dest, $importFile, $optDryParam) {
    global $optDry;
    global $exportData;
    global $mergedContacts;
    global $selfMerged;

    //set global to value passed to function
    $optDry = $optDryParam;

    //bbscript_log(LL::TRACE, "importData dest", $dest);
    bbscript_log(LL::INFO, "importing data using... $importFile");

    //retrieve data from file and set to variable as array
    $exportData = json_decode(file_get_contents($importFile), TRUE);
    //bbscript_log(LL::TRACE, 'importData $exportData', $exportData);

    //parse the import file source/dest, compare with params and return a warning message if values do not match
    if ( $exportData['dest']['name'] != $dest['name'] ) {
      bbscript_log(LL::FATAL, 'The destination defined in the import file does not match the parameters passed to the script. Exiting the script as a mismatched destination could create significant data problems. Please investigate and then rerun the script.');
      //exit();
      $exportData['dest']['db'] = $dest['db'];
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
    $bbAdmin = ( $bbAdmin ) ? $bbAdmin : 1;

    $statsTemp = $selfMerged = array();

    //process the import
    //self::importAttachments($exportData);
    self::importContacts($exportData, $statsTemp, $bbAdmin);

    //squadron-hoylman
    self::sdActivitiesCreate();
    //self::importActivities($exportData, $bbAdmin);
    //self::importCases($exportData, $bbAdmin);
    self::importTags($exportData);
    self::importEmployment($exportData);
    self::importHouseholdRels($exportData);
    //self::importDistrictInfo($exportData);

    //create group and add migrated contacts
    self::addToGroup($exportData);

    $source = $exportData['source'];

    bbscript_log(LL::INFO, "Completed contact migration import from district {$source['num']} ({$source['name']}) to district {$dest['num']} ({$dest['name']}) using {$importFile}.");

    //bbscript_log(LL::TRACE, 'importData $mergedContacts', $mergedContacts);

    //generate report stats
    $caseList = array();
    if ( isset($exportData['cases']) ) {
      foreach ( $exportData['cases'] as $extID => $cases ) {
        foreach ( $cases as $case ) {
          $caseList[] = $case;
        }
      }
    }
    $stats = array(
      'total contacts' => count($exportData['import']),
      'individuals' => $statsTemp['Individual'],
      'organizations' => $statsTemp['Organization'],
      'households' => $statsTemp['Household'],
      'addresses with location conflicts (skipped)' => count($statsTemp['address_location_conflicts']['skip']),
      'addresses with location conflicts (new location assigned)' => count($statsTemp['address_location_conflicts']['newloc']),
      'employee/employer relationships' => count($exportData['employment']),
      'total contacts merged with existing records' => $mergedContacts['All'],
      'individuals merged with existing records' => $mergedContacts['Individual'],
      'organizations merged with existing records' => $mergedContacts['Organization'],
      'households merged with existing records' => $mergedContacts['Household'],
      'contacts self-merged with other imported records (count)' => count($selfMerged),
      'activities' => count($exportData['activities']),
      'cases' => count($caseList),
      'keywords' => count($exportData['tags']['keywords']),
      'first level issue codes' => count($exportData['tags']['issuecodes']),
      'positions' => count($exportData['tags']['positions']),
      'attachments' => count($exportData['attachments']),
      'expanded details for various stats' => array(
        'contacts self-merged with other imported records (current contact -> existing contact)' => $selfMerged,
        'addresses with location conflicts (skipped)' => $statsTemp['address_location_conflicts']['skip'],
        'addresses with location conflicts (new location assigned)' => $statsTemp['address_location_conflicts']['newloc'],
      ),
    );
    bbscript_log(LL::INFO, "Migration statistics:", $stats);

    //log to file
    if ( !$optDry ) {
      //set import folder based on environment
      $fileDir = '/data/redistricting/bluebird_'.$bbconfig['install_class'].'/MigrationReports';
      if ( !file_exists($fileDir) ) {
        mkdir( $fileDir, 0775, TRUE );
      }

      $reportFile = $fileDir.'/'.$source['name'].'_'.$dest['name'].'.txt';
      $fileResource = fopen($reportFile, 'w');

      $content = array(
        'options' => $exportData['options'],
        'stats' => $stats,
      );

      $content = print_r($content, TRUE);
      fwrite($fileResource, $content);
    }

    //now run cleanup scripts
    $dryParam = ($optDry) ? "--dryrun" : '';
    $scriptPath = $bbconfig['app.rootdir'].'/civicrm/scripts';
    $cleanAddress = "php {$scriptPath}/dedupeAddresses.php -S {$dest['name']}";
    if ( !$optDry ) {
      system($cleanAddress);
    }
    $cleanRecords = "php {$scriptPath}/dedupeSubRecords.php -S {$dest['name']} {$dryParam}";
    system($cleanRecords);

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

    if ( !isset($exportData['attachments']) ) {
      return;
    }

    $attachmentIDs = array();
    $filePath = $exportData['dest']['files'].'/'.$exportData['dest']['domain'].'/civicrm/custom/';

    foreach ( $exportData['attachments'] as $attachExtID => $details ) {
      $sourceFilePath = $details['source_file_path'];

      $details['source_file_path'] = $filePath.$details['uri'];
      $file = self::_importAPI('file', 'create', $details);
      //bbscript_log(LL::TRACE, 'importAttachments $file', $file);

      //construct source->dest IDs array
      $attachmentIDs[$attachExtID] = $file['id'];

      //copy the file to the destination folder
      self::_copyAttachment($filePath, $sourceFilePath, $details['source_file_path']);
    }
  }//importAttachments

  function importContacts(&$exportData, &$stats, $bbAdmin) {
    global $optDry;
    global $extInt;
    global $mergedContacts;

    $sdRerun = self::sdContacts();

    //make sure the $extInt IDs array is reset during importContacts
    //array( 'external_identifier' => 'target contact id' )
    $extInt = array();
    $relatedTypes = array(
      'email', 'phone', 'website', 'im', 'address', 'note',
      'Additional_Constituent_Information', 'Attachments', 'Contact_Details', 'Organization_Constituent_Information'
    );
    //records which use entity_id rather than contact_id as foreign key
    $fkEId = array(
      'note', 'Additional_Constituent_Information', 'Attachments',
      'Contact_Details', 'Organization_Constituent_Information'
    );

    //initialize stats arrays
    $mergedContacts = $stats = array(
      'Individual' => 0,
      'Organization' => 0,
      'Household' => 0,
      'All' => 0,
    );

    //increase external_identifier field length to varchar(64)
    if ( !$optDry ) {
      $sql = "
        ALTER TABLE civicrm_contact
        MODIFY external_identifier varchar(64);
      ";
      CRM_Core_DAO::executeQuery($sql);
    }

    foreach ( $exportData['import'] as $extID => $details ) {
      //squadron-hoylman
      if ( !in_array($extID, $sdRerun) ) {
        unset($exportData['import'][$extID]);
        continue;
      }

      //bbscript_log(LL::TRACE, 'importContacts importContacts $details', $details);
      $stats[$details['contact']['contact_type']] ++;

      //check greeting fields
      self::_checkGreeting($details['contact']);

      //look for existing contact record in target db and add to params array
      $matchedContact = self::_contactLookup($details, $exportData['dest']);
      if ( $matchedContact ) {
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
          if ( empty($details['contact']['first_name']) && empty($details['contact']['last_name']) ) {
            $details['contact']['first_name'] = 'Contact';
            $details['contact']['last_name'] = $details['contact']['external_identifier'];
          }
          break;
        case 'Organization':
          if ( empty($details['contact']['organization_name']) ) {
            $details['contact']['organization_name'] = 'Organization '.$details['contact']['external_identifier'];
          }
          break;
        case 'Household':
          if ( empty($details['contact']['household_name']) ) {
            $details['contact']['household_name'] = 'Household '.$details['contact']['external_identifier'];
          }
          break;
        default:
          $details['contact']['display_name'] = 'Unknown Contact';
      }

      //import the contact via api
      $contact = self::_importAPI('contact', 'create', $details['contact']);
      //bbscript_log(LL::TRACE, "importContacts _importAPI contact", $contact);

      //set the contact ID for use in related records; also build mapping array
      if ( $optDry && $matchedContact ) {
        $contactID = $matchedContact;
      }
      else {
        $contactID = $contact['id'];
      }
      $extInt[$extID] = $contactID;

      //cycle through each set of related records
      foreach ( $relatedTypes as $type ) {
        //bbscript_log(LL::TRACE, "importContacts related type: {$type}");
        $fk = ( in_array($type, $fkEId) ) ? 'entity_id' : 'contact_id';
        if ( isset($details[$type]) ) {
          foreach ( $details[$type] as $record ) {
            switch( $type ) {
              case 'Attachments':
                //bbscript_log(LL::TRACE, "importContacts attachments record", $record);
                //handle attachments via sql rather than API
                $attachSqlEle = array();

                //get new attachment IDs
                foreach ( $record as $attF => $attV ) {
                  if ( !empty($attV) ) {
                    $attachSqlEle[$attF] = self::_importEntityAttachments($contactID, $attV, 'civicrm_value_attachments_5');
                  }
                }
                if ( !empty($attachSqlEle) ) {
                  $attachSql = "
                    INSERT IGNORE INTO civicrm_value_attachments_5
                    (entity_id, ".implode(', ', array_keys($attachSqlEle)).")
                    VALUES
                    ({$contactID}, ".implode(', ', $attachSqlEle).")
                  ";
                  //bbscript_log(LL::TRACE, 'importContacts $attachSql', $attachSql);

                  if ( $optDry ) {
                    bbscript_log(LL::DEBUG, "importing attachments for contact", $record);
                  }
                  else {
                    CRM_Core_DAO::executeQuery($attachSql);
                  }
                }
                break;

              case 'address':
                //if location type is missing, set it to home and if needed, it will be corrected below
                if ( empty($record['location_type_id']) ) {
                  $record['location_type_id'] = 1;
                }

                //need to fix location types so we don't overwrite
                $existingAddresses = CRM_Core_BAO_Address::allAddress( $contactID );
                if ( !empty($existingAddresses) ) {
                  if ( array_key_exists($record['location_type_id'], $existingAddresses) ) {
                    //bbscript_log(LL::TRACE, 'importContacts $record', $record);

                    //we have a location conflict -- either skip importing this address, or assign new loc type
                    $action = self::_compareAddresses($record['location_type_id'], $existingAddresses, $record);

                    if ( $action == 'skip' ) {
                      $stats['address_location_conflicts']['skip'][] = "CID{$contactID}_LOC{$record['location_type_id']}";
                      continue;
                    }
                    elseif ( $action == 'newloc' ) {
                      $stats['address_location_conflicts']['newloc'][] = "CID{$contactID}_LOC{$record['location_type_id']}";
                      //attempt to assign to other, other2, main, main2
                      foreach ( array(4,11,3,12) as $newLocType ) {
                        if ( !array_key_exists($newLocType, $existingAddresses) ) {
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
                if ( empty($record['modified_date']) ) {
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

    //squadron-hoylman
    $extInt = self::sdExtInt();
    //bbscript_log(LL::INFO, 'extInt', $extInt);exit();
  }//importContacts

  function importActivities($exportData, $bbAdmin) {
    global $optDry;
    global $extInt;

    $actCount = 0;

    if ( !isset($exportData['activities']) ) {
      return;
    }

    foreach ( $exportData['activities'] as $actID => $details ) {
      //squadron-hoylman
      if ( empty($params['parent_id']) ) {
        continue;
      }
      else {
        $actCount++;
      }

      $params = $details['activity'];
      $params['source_contact_id'] = $bbAdmin;
      unset($params['activity_id']);
      unset($params['source_record_id']);
      unset($params['parent_id']);
      unset($params['original_id']);
      unset($params['entity_id']);

      //prevent error if subject is missing
      if ( empty($params['subject']) ) {
        $params['subject'] = '(none)';
      }

      $targets = array();
      foreach ( $details['targets'] as $tExtID ) {
        $targets[] = $extInt[$tExtID];
      }
      $params['target_contact_id'] = $targets;

      if ( isset($details['custom']) ) {
        $params['custom_43'] = $details['custom']['place_of_inquiry_43'];
        $params['custom_44'] = $details['custom']['activity_category_44'];
      }

      //make sure priority is set
      if ( empty($params['priority_id']) ) {
        $params['priority_id'] = 2;
      }

      //clean params array
      $params = self::_cleanArray($params);

      $newActivity = self::_importAPI('activity', 'create', $params);
      //bbscript_log(LL::TRACE, 'importActivities newActivity', $newActivity);

      //handle attachments
      if ( isset($details['attachments']) ) {
        foreach ( $details['attachments'] as $attID ) {
          self::_importEntityAttachments($newActivity['id'], $attID, 'civicrm_activity');
        }
      }
    }

    bbscript_log(LL::INFO, 'importData $actCount', $actCount);
  }//importActivities

  function importCases($exportData, $bbAdmin) {
    global $optDry;
    global $extInt;

    if ( !isset($exportData['cases']) ) {
      return;
    }

    //store old->new activity ID so we can set original id value
    $oldNewActID = array();

    //store activities where is_current_revision = 0 for post processing
    $nonCurrentActivity = array();

    //cycle through contacts
    foreach ( $exportData['cases'] as $extID => $cases ) {
      $contactID = $extInt[$extID];

      //cycle through cases
      foreach ( $cases as $case ) {
        $activities = $case['activities'];
        unset($case['activities']);

        $case['contact_id'] = $contactID;
        $case['creator_id'] = $bbAdmin;
        //$case['debug'] = 1;

        //prevent error if case subject is missing
        if ( empty($case['subject']) ) {
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
        if ( !$optDry ) {
          $openCase = CRM_Core_DAO::executeQuery($sql);
          while ( $openCase->fetch() ) {
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

        foreach ( $activities as $oldID => $activity ) {
          //squadron-hoylman
          if ( empty($activity['parent_id']) ) {
            //bbscript_log(LL::TRACE, 'importCases $activity[parent_id]', $activity['parent_id']);
            continue;
          }
          else {
            $actCount++;
          }

          $activity['source_contact_id'] = $bbAdmin;
          $activity['target_contact_id'] = $contactID;
          $activity['case_id'] = $caseID;

          //check for and reset original_id
          if ( !empty($activity['original_id']) ) {
            if ( array_key_exists($activity['original_id'], $oldNewActID) ) {
              $activity['original_id'] = $oldNewActID[$activity['original_id']];
            }
            elseif ( !$optDry ) {
              bbscript_log(LL::DEBUG, "Unable to set the original_id for case activity.", $activity);
              unset($activity['original_id']);
            }
          }

          //unset some values we don't need to migrate
          unset($activity['parent_id']);
          unset($activity['source_record_id']);

          //prevent error if subject is missing
          if ( empty($activity['subject']) ) {
            $activity['subject'] = '(none)';
          }

          $newActivity = self::_importAPI('activity', 'create', $activity);
          //bbscript_log(LL::TRACE, 'importCases newActivity', $newActivity);

          $oldNewActID[$oldID] = $newActivity['id'];

          //check is_current_revision
          if ( isset($activity['is_current_revision']) && $activity['is_current_revision'] != 1 ) {
            $nonCurrentActivity[] = $newActivity['id'];
          }

          //handle attachments
          if ( isset($activity['attachments']) ) {
            foreach ( $activity['attachments'] as $attID ) {
              self::_importEntityAttachments($newActivity['id'], $attID, 'civicrm_activity');
            }
          }
        }
      }
    }

    //process non current activities
    if ( !empty($nonCurrentActivity) && !$optDry ) {
      $nonCurrentActivityList = implode(',', $nonCurrentActivity);
      $sql = "
        UPDATE civicrm_activity
        SET is_current_revision = 0
        WHERE id IN ({$nonCurrentActivityList})
      ";
      CRM_Core_DAO::executeQuery($sql);
    }

    bbscript_log(LL::INFO, 'importCases $actCount', $actCount);
  }//importCases

  function importTags($exportData) {
    global $optDry;
    global $extInt;

    $tagExtInt = array();

    if ( !isset($exportData['tags']) ) {
      return;
    }

    //when processing tags, increase field length to varchar(80)
    if ( !$optDry ) {
      $sql = "
        ALTER TABLE civicrm_tag
        MODIFY name varchar(80);
      ";
      CRM_Core_DAO::executeQuery($sql);
    }

    //bbscript_log(LL::TRACE, 'importTags tags', $exportData['tags']);

    //process keywords
    foreach ( $exportData['tags']['keywords'] as $keyID => $keyDetail ) {
      $params = array(
        'name' => $keyDetail['name'],
        'description' => $keyDetail['desc'],
        'parent_id' => 296, //keywords constant
      );
      $newKeyword = self::_importAPI('tag', 'create', $params);
      //bbscript_log(LL::TRACE, 'importTags newKeyword', $newKeyword);
      $tagExtInt[$keyID] = $newKeyword['id'];
    }

    //process positions
    foreach ( $exportData['tags']['positions'] as $posID => $posDetail ) {
      $pName = civicrm_mysql_real_escape_string($posDetail['name']);
      $sql = "
        SELECT id
        FROM civicrm_tag
        WHERE name = '{$pName}'
          AND parent_id = 292
      ";
      $intPosID = CRM_Core_DAO::singleValueQuery($sql);
      if ( !$intPosID ) {
        $params = array(
          'name' => $posDetail['name'],
          'description' => $posDetail['desc'],
          'parent_id' => 292, //positions constant
        );
        $newPos = self::_importAPI('tag', 'create', $params);
        //bbscript_log(LL::TRACE, 'importTags newPos', $newPos);
        $intPosID = $newPos['id'];
      }
      $tagExtInt[$posID] = $intPosID;
    }

    //process issue codes
    //begin by constructing base level tag
    $params = array(
      'name' => "Migrated from: {$exportData['source']['name']} (SD{$exportData['source']['num']})",
      'description' => 'Tags migrated from other district',
      'parent_id' => 291,
    );
    $icParent = self::_importAPI('tag', 'create', $params);

    //level 1
    foreach ( $exportData['tags']['issuecodes']  as $icID1 => $icD1 ) {
      $params = array(
        'name' => $icD1['name'],
        'description' => $icD1['desc'],
        'parent_id' => $icParent['id'],
      );
      $icP1 = self::_importAPI('tag', 'create', $params);
      $tagExtInt[$icID1] = $icP1['id'];

      //level 2
      if ( isset($icD1['children']) ) {
        foreach ( $icD1['children'] as $icID2 => $icD2 ) {
          $params = array(
            'name' => $icD2['name'],
            'description' => $icD2['desc'],
            'parent_id' => $icP1['id'],
          );
          $icP2 = self::_importAPI('tag', 'create', $params);
          $tagExtInt[$icID2] = $icP2['id'];

          //level 3
          if ( isset($icD2['children']) ) {
            foreach ( $icD2['children'] as $icID3 => $icD3 ) {
              $params = array(
                'name' => $icD3['name'],
                'description' => $icD3['desc'],
                'parent_id' => $icP2['id'],
              );
              $icP3 = self::_importAPI('tag', 'create', $params);
              $tagExtInt[$icID3] = $icP3['id'];

              //level 4
              if ( isset($icD3['children']) ) {
                foreach ( $icD3['children'] as $icID4 => $icD4 ) {
                  $params = array(
                    'name' => $icD4['name'],
                    'description' => $icD4['desc'],
                    'parent_id' => $icP3['id'],
                  );
                  $icP4 = self::_importAPI('tag', 'create', $params);
                  $tagExtInt[$icID4] = $icP4['id'];

                  //level 5
                  if ( isset($icD4['children']) ) {
                    foreach ( $icD4['children'] as $icID5 => $icD5 ) {
                      $params = array(
                        'name' => $icD5['name'],
                        'description' => $icD5['desc'],
                        'parent_id' => $icP4['id'],
                      );
                      $icP5 = self::_importAPI('tag', 'create', $params);
                      $tagExtInt[$icID5] = $icP5['id'];
                    }
                  }//end level 5
                }
              }//end level 4
            }
          }//end level 3
        }
      }//end level 2
    }//end level 1
    //bbscript_log(LL::TRACE, '_importTags $tagExtInt', $tagExtInt);

    //construct tag entity records
    //bbscript_log(LL::TRACE, '_importTags $extInt', $extInt);
    foreach ( $exportData['tags']['entities'] as $extID => $extTags ) {
      $params = array(
        'contact_id' => $extInt[$extID],
      );
      foreach ( $extTags as $tIndex => $tID ) {
        $params['tag_id.'.$tIndex] = $tagExtInt[$tID];
      }
      if ( empty($params['contact_id']) ) {
        bbscript_log(LL::TRACE, "_importTags extID (no contact_id): $extID");
      }
      else {
        //bbscript_log(LL::TRACE, '_importTags entityTag $params', $params);
        self::_importAPI('entity_tag', 'create', $params);
      }
    }
  }//importTags

  function importEmployment(&$exportData) {
    global $optDry;

    bbscript_log(LL::INFO, "importing employer/employee relationships...");

    if ( !isset($exportData['employment']) ) {
      $exportData['employment'] = array();
      return;
    }

    require_once 'CRM/Contact/BAO/Contact/Utils.php';

    foreach ( $exportData['employment'] as  $employeeID => $employerID ) {
      if ( $optDry ) {
        bbscript_log(LL::DEBUG, "creating employment relationship between I-{$employeeID} and O-{$employerID}");
      }
      else {
        $employeeIntID = self::_getIntID($employeeID);
        $employerIntID = self::_getIntID($employerID);
        if ( !empty($employeeIntID) && !empty($employerIntID) ) {
          CRM_Contact_BAO_Contact_Utils::createCurrentEmployerRelationship($employeeIntID, $employerIntID);
        }
      }
    }
  }//importEmployment

  function importHouseholdRels(&$exportData) {
    global $optDry;

    bbscript_log(LL::INFO, "importing household relationships...");

    if ( !isset($exportData['houserels']) ) {
      $exportData['houserels'] = array();
      return;
    }

    foreach ( $exportData['houserels'] as $rel ) {
      $rel['contact_id_a'] = self::_getIntID($rel['contact_id_a']);
      $rel['contact_id_b'] = self::_getIntID($rel['contact_id_b']);

      if ( !empty($rel['contact_id_a']) && !empty($rel['contact_id_b']) ) {
        self::_importAPI('relationship', 'create', $rel);
      }
    }
  }//importHouseholdRels

  function importDistrictInfo($exportData) {
    global $optDry;

    if ( !isset($exportData['districtinfo']) ) {
      return;
    }

    //build array referencing address name field (external address ID value)
    $addrExtInt = array();
    $sql = "
      SELECT id, name
      FROM civicrm_address
      WHERE name IS NOT NULL
    ";
    $ids = CRM_Core_DAO::executeQuery($sql);
    while ( $ids->fetch() ) {
      $addrExtInt[$ids->name] = $ids->id;
    }

    //get fields and construct array
    $distFields = array();
    $distFieldsDetail = self::getCustomFields('District_Information');
    foreach ( $distFieldsDetail as $field ) {
      $distFields[$field['column_name']] = $field['id'];
    }

    foreach ( $exportData['districtinfo'] as $addrExtID => $details ) {
      $details['entity_id'] = $addrExtInt[$addrExtID];

      //capture errors mapping address external ID
      if ( empty($details['entity_id']) ) {
        bbscript_log(LL::DEBUG, 'importDistrictInfo: unmatched addrExtID', $addrExtID);
      }

      //clean array: remove elements with no value
      $details = self::_cleanArray($details);

      $distInfo = self::_importAPI('District_Information', 'create', $details);
      //bbscript_log(LL::TRACE, 'importDistrictInfo $distInfo', $distInfo);
    }

    //cleanup address name field (temp ext address ID)
    if ( !$optDry ) {
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
  function _importAPI($entity, $action, $params) {
    global $optDry;
    global $customMap;

    //record types which are custom groups
    $customGroups = array(
      'Additional_Constituent_Information', 'Attachments',
      'Contact_Details', 'Organization_Constituent_Information',
      'District_Information'
    );
    $dateFields = array(
      'last_import_57', 'boe_date_of_registration_24'
    );

    //prepare custom fields
    if ( in_array($entity, $customGroups) ) {
      //get fields and construct array if not already constructed
      if ( !isset($customMap[$entity]) || empty($customMap[$entity]) ) {
        $customDetails = self::getCustomFields($entity);
        foreach ( $customDetails as $field ) {
          $customMap[$entity][$field['column_name']] = 'custom_'.$field['id'];
        }
      }
      //bbscript_log(LL::TRACE, '_importAPI $customMap', $customMap);

      //cycle through custom fields and convert column name to custom_## format
      foreach ( $params as $col => $v ) {
        //if a date type column, strip punctuation
        if ( in_array($col, $dateFields) ) {
          $v = str_replace(array('-', ':', ' '), '', $v);
        }
        if ( array_key_exists($col, $customMap[$entity]) ) {
          $params[$customMap[$entity][$col]] = $v;
          unset($params[$col]);
        }
      }

      //change entity value for api
      $entity = 'custom_value';
    }

    //clean the params array
    $params = self::_cleanArray($params);

    if ( $optDry ) {
      bbscript_log(LL::DEBUG, "_importAPI entity:{$entity} action:{$action} params:", $params);
    }

    if ( !$optDry || $action == 'get' ) {
      //add api version
      $params['version'] = 3;
      //$params['debug'] = 1;

      $api = civicrm_api($entity, $action, $params);

      if ( $api['is_error'] ) {
        bbscript_log(LL::DEBUG, "_importAPI error", $api);
        bbscript_log(LL::DEBUG, "_importAPI entity: {$entity} // action: {$action}", $params);
      }
      return $api;
    }
  }//_importAPI

  /*
   * dedupe matching function
   * given the values to be imported, lookup using indiv strict default rule
   * return contact ID if found
   */
  function _contactLookup($contact, $dest) {
    global $extInt;
    global $selfMerged;

    require_once 'CRM/Dedupe/Finder.php';
    require_once 'CRM/Import/DataSource/CSV.php';
    require_once $dest['app'].'/modules/nyss_dedupe/nyss_dedupe.module';
    //bbscript_log(LL::TRACE, '_contactLookup $contact', $contact);
    //bbscript_log(LL::TRACE, '_contactLookup $dest', $dest);

    //set contact type
    $cType = $contact['contact']['contact_type'];

    //format params to pass to dedupe tool based on contact type
    $params = array();
    $ruleName = '';
    switch($cType) {
      case 'Individual':
        $params['civicrm_contact']['first_name'] = CRM_Utils_Array::value('first_name', $contact['contact']);
        $params['civicrm_contact']['middle_name'] = CRM_Utils_Array::value('middle_name', $contact['contact']);
        $params['civicrm_contact']['last_name'] = CRM_Utils_Array::value('last_name', $contact['contact']);
        $params['civicrm_contact']['suffix_id'] = CRM_Utils_Array::value('suffix_id', $contact['contact']);
        $params['civicrm_contact']['birth_date'] = CRM_Utils_Array::value('birth_date', $contact['contact']);
        $params['civicrm_contact']['gender_id'] = CRM_Utils_Array::value('gender_id', $contact['contact']);
        $ruleName = 'Individual Strict (first + last + (street + zip | email))';
        break;

      case 'Organization':
        $params['civicrm_contact']['organization_name'] = CRM_Utils_Array::value('organization_name', $contact['contact']);
        $ruleName = 'Organization 1 (name + street + city + email)';
        break;

      case 'Household':
        $params['civicrm_contact']['household_name'] = CRM_Utils_Array::value('household_name', $contact['contact']);
        $ruleName = 'Household 1 (name + street + city + email)';
        break;

      default:
    }

    if ( isset($contact['address']) ) {
      foreach ( $contact['address'] as $address ) {
        if ( !empty($address['street_address']) && $address['is_primary'] ) {
          $params['civicrm_address']['street_address'] = CRM_Utils_Array::value('street_address', $address);
          $params['civicrm_address']['postal_code'] = CRM_Utils_Array::value('postal_code', $address);
          $params['civicrm_address']['city'] = CRM_Utils_Array::value('city', $address);
        }
      }
    }

    if ( isset($contact['email']) ) {
      foreach ( $contact['email'] as $email ) {
        if ( !empty($email['email']) && $email['is_primary'] ) {
          $params['civicrm_email']['email'] = CRM_Utils_Array::value('email', $email);
        }
      }
    }
    $params = CRM_Dedupe_Finder::formatParams($params, $cType);
    $params['check_permission'] = 0;
    //bbscript_log(LL::TRACE, '_contactLookup $params', $params);

    //use dupeQuery hook implementation to build sql
    $o = new stdClass();
    $o->name = $ruleName;
    $o->params = $params;
    $o->noRules = false;
    $tableQueries = array();
    nyss_dedupe_civicrm_dupeQuery($o, 'table', $tableQueries);
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

    while ( $c->fetch() ) {
      $cid = $c->id;
      $xid = $c->external_identifier;
    }

    $extID = civicrm_mysql_real_escape_string($contact['contact']['external_identifier']);

    //also try a lookup on external id (which should really only happen during testing)
    if ( !$cid ) {
      $sql = "
        SELECT id
        FROM civicrm_contact
        WHERE external_identifier = '{$extID}'
      ";
      $cid = CRM_Core_DAO::singleValueQuery($sql);
    }
    //bbscript_log(LL::TRACE, '_contactLookup $cid', $cid);

    //if a contact is found which we will merge to, check to see if that contact was in our import set
    if ( $xid ) {
      //see if the matched record external_id is already in our $extInt array
      if ( array_key_exists($xid, $extInt) ) {
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
    if ( isset($extInt[$extID]) ) {
      return $extInt[$extID];
    }
    //see if the record self-merged
    elseif ( in_array($extID, $selfMerged) ) {
      $mergedExtID = array_search($extID, $selfMerged);
      if ( isset($extInt[$mergedExtID]) ) {
        return $extInt[$mergedExtID];
      }
    }
    //try a db lookup
    else {
      $sql = "SELECT id FROM civicrm_contact WHERE external_identifier = '{$extID}';";
      $intID = CRM_Core_DAO::singleValueQuery($sql);
      if ( $intID ) {
        return $intID;
      }
    }

    return null;
  }//_getIntID

  /*
   * given contact params, ensure greetings are constructed
   */
  function _checkGreeting(&$contact) {
    $gTypes = array(
      'email_greeting',
      'postal_greeting',
      'addressee',
    );

    foreach ( $gTypes as $type ) {
      if ( $contact[$type.'_id'] == 4 ) {
        if ( empty($contact[$type.'_custom']) ) {
          $custVal = (!empty($contact[$type.'_display'])) ? $contact[$type.'_display'] : 'Dear Friend';
          $contact[$type.'_custom'] = $custVal;
        }
      }
      else {
        $contact[$type.'_custom'] = '';
      }
    }

    //random bad data fix
    if ( $contact['email_greeting_id'] == 9 ) {
      $contact['email_greeting_id'] = 6;
    }

    //trap errors and set to custom
    require_once 'api/v3/Contact.php';
    $error = _civicrm_api3_greeting_format_params( $contact );
    if ( civicrm_error( $error ) ) {
      //determine which type errored
      $type = '';
      if ( strpos($error['error_message'], 'email') !== FALSE ) {
        $type = 'email_greeting';
      }
      elseif ( strpos($error['error_message'], 'postal') !== FALSE ) {
        $type = 'postal_greeting';
      }
      elseif ( strpos($error['error_message'], 'addressee') !== FALSE ) {
        $type = 'addressee';
      }
      else {
        return;
      }

      $contact[$type.'_id'] = 4;
      if ( empty($contact[$type.'_custom']) ) {
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
    global $customMapID; // array('id' => 'col_name')
    global $optDry;

    $params = array(
      'version' => 3,
      'id' => $matchedID,
    );
    $contact = civicrm_api('contact', 'getsingle', $params);

    foreach ( $contact as $f => $v ) {
      //if existing record field has a value, remove from imported record array
      if ( (!empty($v) || $v == '0') &&
        isset($details['contact'][$f]) &&
        $f != 'source' &&
        $f != 'external_identifier' ) {
        //unset from imported contact array
        unset($details['contact'][$f]);
      }
    }

    //process custom field data
    $customSets = array(
      'Additional_Constituent_Information',
      'Attachments',
      'Contact_Details',
      'Organization_Constituent_Information',
    );
    foreach ( $customSets as $set ) {
      //get/set custom group ID
      if ( !isset($customGroupID[$set]) || empty($customGroupID[$set]) ) {
        $customGroupID[$set] = self::getCustomFields($set, 'groupid');
      }

      //get/set custom fields
      if ( !isset($customMapID[$set]) || empty($customMapID[$set]) ) {
        $customDetails = self::getCustomFields($set);
        foreach ( $customDetails as $field ) {
          $customMapID[$set][$field['id']] = $field['column_name'];
        }
      }

      if ( isset($details[$set]) ) {
        $params = array(
          'version' => 3,
          'entity_id' => $matchedID,
          'custom_group_id' => $customGroupID[$set],
        );
        $data = self::_importAPI($set, 'get', $params);
        //bbscript_log(LL::TRACE, "_fillContact data: $set", $data);

        //trap the error: if get failed, we need to insert a record in the custom data table
        if ( $data['is_error'] && !$optDry ) {
          bbscript_log(LL::DEBUG, "unable to retrieve {$set} custom data for ID {$matchedID}. inserting record and proceeding.");
          $tbl = self::getCustomFields($set, 'table');
          $sql = "
            INSERT IGNORE INTO {$tbl} (entity_id)
            VALUES ({$matchedID});
          ";
        }

        //cycle through existing custom data and unset from $details if value exists
        if ( !empty($data['values']) ) {
          foreach ( $data['values'] as $custFID => $existingData ) {
            //should probably handle attachments more intelligently
            if ( !empty($existingData['latest']) || $existingData['latest'] == '0' ) {
              $colName = $customMapID[$set][$custFID];
              unset($details[$set][$colName]);
            }
          }
        }
      }
    }
  }//_fillContact

  /*
   * compare imported conflicting address with existing and decide if they match
   * and we should skip import, or they are different and we should assign a new loc type
   */
  function _compareAddresses($locType, $existing, $record) {
    global $exportData;

    //get existing address
    $params = array(
      'id' => $existing[$locType],
    );
    $address = self::_importAPI('address', 'getsingle', $params);

    //bbscript_log(LL::TRACE, "_compareAddresses address", $address);
    //bbscript_log(LL::TRACE, "_compareAddresses record", $record);

    $dupe = TRUE;
    $afs = array('street_address', 'supplemental_address_1', 'city', 'postal_code');
    foreach ( $afs as $af ) {
      if ( $address[$af] != $record[$af] ) {
        $dupe = FALSE;
        break;
      }
    }

    if ( $dupe ) {
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
    if ( empty($entityID) || empty($attID) ) {
      return;
    }

    if ( $optDry ) {
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
    if ( CRM_Core_DAO::singleValueQuery($sql) ) {
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
    if ( !file_exists($filePath) ) {
      mkdir( $filePath, 0775, TRUE );
    }

    //now copy file and fix owner:group
    if ( $optDry ) {
      bbscript_log(LL::DEBUG, "_copyAttachment: {$sourceFile}");
    }
    else {
      //ensure source file exists
      if ( file_exists($sourceFile) ) {
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
  }//_cleanLogRecords

  /*
   * given an array, cycle through and unset any elements with no value
   */
  function _cleanArray($data) {
    foreach ( $data as $f => $v ) {
      if ( empty($v) && $v !== 0 ) {
        unset($data[$f]);
      }
      if ( is_string($v) ) {
        $data[$f] = stripslashes($v);
      }
    }
    return $data;
  }//_cleanArray

  /*
   * create group in destination database and add all contacts
   */
  function addToGroup($exportData) {
    global $optDry;
    global $extInt;

    $source = $exportData['source'];
    $dest = $exportData['dest'];
    $g = $exportData['group'];

    //contacts
    $contactsList = implode("','", array_keys($extInt));

    if ( $optDry ) {
      bbscript_log(LL::DEBUG, "Imported contacts to be added to group:", $g);
      bbscript_log(LL::DEBUG, "List of contacts (external ids) added:", $contactsList);
      return;
    }

    //create group in destination database
    $sql = "
      INSERT IGNORE INTO {$dest['db']}.civicrm_group
      ( name, title, description, is_active, visibility, is_hidden, is_reserved )
      VALUES
      ( '{$g['name']}', '{$g['title']}', '{$g['description']}', 1, 'User and User Admin Only', 0, 0 );
    ";
    CRM_Core_DAO::executeQuery($sql);

    //get newly created group
    $sql = "
      SELECT id FROM {$dest['db']}.civicrm_group WHERE name = '{$g['name']}';
    ";
    $groupID = CRM_Core_DAO::singleValueQuery($sql);

    //error handling
    if ( !$groupID ) {
      bbscript_log(LL::FATAL, "Unable to retrieve migration group ({$g['title']}) and add contacts to group.");
      return;
    }

    //add contacts to group
    $sqlInsert = "
      INSERT IGNORE INTO {$dest['db']}.civicrm_group_contact
      ( group_id, contact_id, status )
      SELECT {$groupID} group_id, id contact_id, 'Added' status
      FROM civicrm_contact
      WHERE external_identifier IN ('{$contactsList}');
    ";
    //bbscript_log(LL::TRACE, "Group insert:", $sqlInsert);
    CRM_Core_DAO::executeQuery($sqlInsert);

    $now = date('Y-m-d H:i', strtotime('+3 hours', strtotime(date('Y-m-d H:i'))));
    $sqlSubInsert = "
      INSERT IGNORE INTO {$dest['db']}.civicrm_subscription_history
      ( contact_id, group_id, date, method, status )
      SELECT id contact_id, {$groupID} group_id, '{$now}' date, 'Admin' method, 'Added' status
      FROM civicrm_contact
      WHERE external_identifier IN ('{$contactsList}');
    ";
    //bbscript_log(LL::TRACE, "Group insert:", $sqlInsert);
    CRM_Core_DAO::executeQuery($sqlSubInsert);

    bbscript_log(LL::INFO, "Imported contacts added to group: {$g['title']}");
  }//addToGroup

  /*
   * given a custom data group name, return array of fields
   */
  function getCustomFields($name, $return = 'fields') {
    $group = civicrm_api('custom_group', 'getsingle', array('version' => 3, 'name' => $name ));
    if ( $return == 'fields' ) {
      $fields = civicrm_api('custom_field', 'get', array('version' => 3, 'custom_group_id' => $group['id']));
      //bbscript_log(LL::TRACE, 'getCustomFields fields', $fields);
      return $fields['values'];
    }
    elseif ( $return == 'table' ) {
      return $group['table_name'];
    }
    elseif ( $return == 'groupid' ) {
      return $group['id'];
    }
  }//getCustomFields

  function getValue($string) {
    if ($string == FALSE) {
      return "null";
    }
    else {
      return $string;
    }
  }//getValue

  function sdContacts() {
    $rerun = array(
      'SD26_BB218025_EXTomis104615+104616',
      'SD26_BB218029_EXTomis114019+114020',
      'SD26_BB218030_EXTomis115993+115994',
      'SD26_BB218031_EXTomis120203+120204',
      'SD26_BB218051_EXTomis152471+152472',
      'SD26_BB218052_EXTomis167902+167904',
      'SD26_BB218053_EXTomis175953+175954',
      'SD26_BB218054_EXTomis176990+176991',
      'SD26_BB218056_EXTomis179555+179556',
      'SD26_BB218057_EXTomis187431+187432',
    );
    return $rerun;
  }

  function sdActivities() {
    $rerun = array(
      Array
      (
        'activity_type_id' => '14',
        'subject' => 'Call Migna NYCHA re: priority code',
        'activity_date_time' => '2011-01-31 14:45:00',
        'status_id' => '1',
        'priority_id' => '2',
        'parent_id' => '12201',
        'source_contact_id' => '1',
        'target_contact_id' => '293700',
        'case_id' => '6',
        'is_test' => '0',
        'is_auto' => '0',
        'is_current_revision' => '0',
        'is_deleted' => '0',
        'version' => '3'
      ),
      Array
      (
        'activity_type_id' => '14',
        'subject' => 'Call Migna NYCHA re: priority code',
        'activity_date_time' => '2011-01-31 14:45:00',
        'details' => '<p>
	2/311 Spoke to Migna who requested the priority need be changed for Mrs. Martinez using their new tracking system</p>
',
        'status_id' => '2',
        'priority_id' => '2',
        'parent_id' => '12201',
        'medium_id' => '2',
        'is_current_revision' => '1',
        'source_contact_id' => '1',
        'target_contact_id' => '293700',
        'case_id' => '6',
        'is_test' => '0',
        'is_auto' => '0',
        'is_deleted' => '0',
        'custom_43' => '',
        'custom_44' => '',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '2',
        'subject' => 'Ask Migna for outcome of inquiry for priority change',
        'activity_date_time' => '2011-02-14 11:31:00',
        'status_id' => '1',
        'priority_id' => '2',
        'parent_id' => '12332',
        'source_contact_id' => '1',
        'target_contact_id' => '293700',
        'case_id' => '6',
        'is_test' => '0',
        'is_auto' => '0',
        'is_current_revision' => '0',
        'is_deleted' => '0',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '2',
        'subject' => 'Call Migna again for responce',
        'activity_date_time' => '2011-03-31 13:36:00',
        'status_id' => '1',
        'priority_id' => '2',
        'parent_id' => '12874',
        'source_contact_id' => '1',
        'target_contact_id' => '293700',
        'case_id' => '6',
        'is_test' => '0',
        'is_auto' => '0',
        'is_current_revision' => '0',
        'is_deleted' => '0',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '2',
        'subject' => 'Ask Migna for outcome of inquiry for priority change',
        'activity_date_time' => '2011-02-14 11:31:00',
        'status_id' => '2',
        'priority_id' => '2',
        'parent_id' => '12332',
        'medium_id' => '2',
        'is_current_revision' => '1',
        'source_contact_id' => '1',
        'target_contact_id' => '293700',
        'case_id' => '6',
        'is_test' => '0',
        'is_auto' => '0',
        'is_deleted' => '0',
        'custom_43' => '',
        'custom_44' => '',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '2',
        'subject' => 'Call Migna again for responce',
        'activity_date_time' => '2011-03-31 13:36:00',
        'details' => '<div>
	<p>
		Ok. Let&rsquo;s see if the social security addressed document works</p>
</div>
<table cellpadding="0" cellspacing="0" width="300px">
	<tbody>
		<tr>
			<td nowrap="1">
				Migna Taveras</td>
		</tr>
		<tr>
			<td nowrap="1">
				Senior Policy Analyst</td>
		</tr>
		<tr>
			<td nowrap="1">
				State and City Legislative Affairs</td>
		</tr>
		<tr>
			<td nowrap="1">
				250 Broadway, New York, NY 10007</td>
		</tr>
		<tr>
			<td nowrap="1">
				ofc.(212)306-8119</td>
		</tr>
		<tr>
			<td nowrap="1">
				fax.(212)306-6485</td>
		</tr>
		<tr>
			<td nowrap="1">
				New York City Housing Authority | <a href="http://www.nyc.gov/nycha" target="" title="">www.nyc.gov/nycha</a></td>
		</tr>
	</tbody>
</table>
<p>
	&nbsp;</p>
<div>
	<p>
		<strong>From:</strong>rdiaz@nysenate.gov [mailto:rdiaz@nysenate.gov]<br />
		<strong>Sent:</strong> Tuesday, March 29, 2011 1:02 PM<br />
		<strong>To:</strong> Taveras, Migna<br />
		<strong>Subject:</strong> RE: test</p>
</div>
<p>
	&nbsp;</p>
<p>
	No, I don&#39;t have a lease or phone bill. The granddaughter took mother and son in last April after they lost their home. This is not their apt.<br />
	<br />
	Rosemarie Diaz<br />
	Community Liaison<br />
	NYS Senator Daniel Squadron<br />
	Tel: 212-298-5565<br />
	Fax: 212-431-7836<br />
	<br />
	-----&quot;Taveras, Migna&quot; <a href="mailto:Migna.Taveras@nycha.nyc.gov"><Migna.Taveras@nycha.nyc.gov></a> wrote: -----</p>
<p>
	To: <a href="mailto:%27rdiaz@nysenate.gov%27">&quot;&#39;rdiaz@nysenate.gov&#39;&quot;</a> <a href="mailto:rdiaz@nysenate.gov"><rdiaz@nysenate.gov></a><br />
	From: &quot;Taveras, Migna&quot; <a href="mailto:Migna.Taveras@nycha.nyc.gov"><Migna.Taveras@nycha.nyc.gov></a><br />
	Date: 03/29/2011 12:57PM<br />
	Subject: RE: test</p>
<div>
	<p>
		I will send this over and let you know what response I get. Thanks, Migna</p>
	<p>
		Do they have a phone bill or lease?</p>
</div>
<table border="0" cellpadding="0" cellspacing="0">
	<tbody>
		<tr>
			<td>
				<p>
					<strong>Migna Taveras</strong></p>
			</td>
		</tr>
		<tr>
			<td>
				<p>
					Senior Policy Analyst</p>
			</td>
		</tr>
		<tr>
			<td>
				<p>
					State and City Legislative Affairs</p>
			</td>
		</tr>
		<tr>
			<td>
				<p>
					250 Broadway, New York, NY 10007</p>
			</td>
		</tr>
		<tr>
			<td>
				<p>
					ofc.(212)306-8119</p>
			</td>
		</tr>
		<tr>
			<td>
				<p>
					fax.(212)306-6485</p>
			</td>
		</tr>
		<tr>
			<td>
				<p>
					New York City Housing Authority | <a href="http://www.nyc.gov/nycha" title="">www.nyc.gov/nycha</a></p>
			</td>
		</tr>
	</tbody>
</table>
<p>
	&nbsp;</p>
<div>
	<p>
		<strong>From:</strong><a href="mailto:rdiaz@nysenate.gov">rdiaz@nysenate.gov</a> [<a href="mailto:rdiaz@nysenate.gov">mailto:rdiaz@nysenate.gov</a>]<br />
		<strong>Sent:</strong> Tuesday, March 29, 2011 12:41 PM<br />
		<strong>To:</strong> Taveras, Migna<br />
		<strong>Subject:</strong> RE: test</p>
</div>
<p>
	&nbsp;</p>
<p>
	Hi Migna,<br />
	<br />
	the constituent&#39;s name is Rosa Martinez and her son is Edwin Morales. I am confused, I don&#39;t have a notarized letter in her file. I have the SSI letter and a bank statement. Both of them have 465 East 10th St. Apt. 9-A NY NY 10009.<br />
	<br />
	call me if you have any questions.<br />
	<br />
	Best,<br />
	<br />
	Rosemarie Diaz<br />
	Community Liaison<br />
	NYS Senator Daniel Squadron<br />
	Tel: 212-298-5565<br />
	Fax: 212-431-7836<br />
	<br />
	-----&quot;Taveras, Migna&quot; <a href="mailto:Migna.Taveras@nycha.nyc.gov"><Migna.Taveras@nycha.nyc.gov></a> wrote: -----</p>
<p>
	To: <a href="mailto:%27rdiaz@nysenate.gov%27">&quot;&#39;rdiaz@nysenate.gov&#39;&quot;</a> <a href="mailto:rdiaz@nysenate.gov"><rdiaz@nysenate.gov></a><br />
	From: &quot;Taveras, Migna&quot; <a href="mailto:Migna.Taveras@nycha.nyc.gov"><Migna.Taveras@nycha.nyc.gov></a><br />
	Date: 03/29/2011 11:07AM<br />
	Subject: RE: test</p>
<div>
	<p>
		&nbsp;</p>
	<p>
		Please email me again the updated information that you sent for Rosa Muniz.</p>
	<p>
		&nbsp;</p>
	<p>
		For Rosa Muniz This is the response I received. &ldquo;Unless she can verify that she actually resides in NYC the N8 remains. She needs to submit documentation that she is a resident. Notarized letters are not documentation especially if her social security is being received in another state or country.&rdquo;</p>
</div>
<table border="0" cellpadding="0" cellspacing="0">
	<tbody>
		<tr>
			<td>
				<p>
					<strong>Migna Taveras</strong></p>
			</td>
		</tr>
		<tr>
			<td>
				<p>
					Senior Policy Analyst</p>
			</td>
		</tr>
		<tr>
			<td>
				<p>
					State and City Legislative Affairs</p>
			</td>
		</tr>
		<tr>
			<td>
				<p>
					250 Broadway, New York, NY 10007</p>
			</td>
		</tr>
		<tr>
			<td>
				<p>
					ofc.(212)306-8119</p>
			</td>
		</tr>
		<tr>
			<td>
				<p>
					fax.(212)306-6485</p>
			</td>
		</tr>
		<tr>
			<td>
				<p>
					New York City Housing Authority | <a href="http://www.nyc.gov/nycha" title="">www.nyc.gov/nycha</a></p>
			</td>
		</tr>
	</tbody>
</table>
<p>
	&nbsp;</p>',
        'status_id' => '2',
        'priority_id' => '2',
        'parent_id' => '12874',
        'medium_id' => '2',
        'is_current_revision' => '1',
        'source_contact_id' => '1',
        'target_contact_id' => '293700',
        'case_id' => '6',
        'is_test' => '0',
        'is_auto' => '0',
        'is_deleted' => '0',
        'custom_43' => '',
        'custom_44' => '',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '38',
        'subject' => 'Find Homeless services for son',
        'activity_date_time' => '2011-04-03 13:36:00',
        'status_id' => '1',
        'priority_id' => '2',
        'parent_id' => '12896',
        'source_contact_id' => '1',
        'target_contact_id' => '293700',
        'case_id' => '6',
        'is_test' => '0',
        'is_auto' => '0',
        'is_current_revision' => '0',
        'is_deleted' => '0',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '38',
        'subject' => 'Find Homeless services for son',
        'activity_date_time' => '2011-04-03 13:36:00',
        'details' => '<p>
	Two possible choices, Oliviary Center 212-947-3211 Nadia, 257 West 30th between 7th and 8th. Or Main chance 120 East 32nd St. 212-833-0680 Ext 321 contact Caitlyn Smith.</p>
<p>
	&nbsp;</p>
<p>
	Spoke to Nadia and she will be looking out for Edwin if he arrives understanding his issue with crowds and resistance to shelter she will suggest a bed at a church.</p>
',
        'status_id' => '1',
        'priority_id' => '2',
        'parent_id' => '12896',
        'medium_id' => '2',
        'source_contact_id' => '1',
        'target_contact_id' => '293700',
        'case_id' => '6',
        'is_test' => '0',
        'is_auto' => '0',
        'is_current_revision' => '0',
        'is_deleted' => '0',
        'custom_43' => '',
        'custom_44' => '',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '38',
        'subject' => 'Find Homeless services for son',
        'activity_date_time' => '2011-04-03 13:36:00',
        'details' => '<p>
	Two possible choices, Oliviary Center 212-947-3211 Nadia, 257 West 30th between 7th and 8th. Or Main chance 120 East 32nd St. 212-833-0680 Ext 321 contact Caitlyn Smith.</p>
<p>
	&nbsp;</p>
<p>
	Spoke to Nadia and she will be looking out for Edwin if he arrives understanding his issue with crowds and resistance to shelter she will suggest a bed at a church.</p>
',
        'status_id' => '2',
        'priority_id' => '2',
        'parent_id' => '12896',
        'medium_id' => '2',
        'is_current_revision' => '1',
        'source_contact_id' => '1',
        'target_contact_id' => '293700',
        'case_id' => '6',
        'is_test' => '0',
        'is_auto' => '0',
        'is_deleted' => '0',
        'custom_43' => '',
        'custom_44' => '',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '2',
        'subject' => 'Call when lease is faxed',
        'activity_date_time' => '2011-02-21 13:50:00',
        'status_id' => '1',
        'priority_id' => '2',
        'parent_id' => '12903',
        'source_contact_id' => '1',
        'target_contact_id' => '266061',
        'case_id' => '14',
        'is_test' => '0',
        'is_auto' => '0',
        'is_current_revision' => '0',
        'is_deleted' => '0',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '2',
        'subject' => 'Call when lease is faxed',
        'activity_date_time' => '2011-03-31 13:50:00',
        'details' => '<p>
	Waiting for landlord to fax lease. If he does not need to call hiom again before calling Ms. Sherman. Morris Platt 917-299-4932 Owner.</p>
',
        'status_id' => '1',
        'priority_id' => '2',
        'parent_id' => '12903',
        'medium_id' => '2',
        'source_contact_id' => '1',
        'target_contact_id' => '266061',
        'case_id' => '14',
        'is_test' => '0',
        'is_auto' => '0',
        'is_current_revision' => '0',
        'is_deleted' => '0',
        'custom_43' => '',
        'custom_44' => '',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '2',
        'subject' => 'Call when lease is faxed',
        'activity_date_time' => '2011-03-31 09:50:00',
        'details' => '<p>
	Waiting for landlord to fax lease. If he does not need to call hiom again before calling Ms. Sherman. Morris Platt 917-299-4932 Owner. Lease was faxed by oener. Called Ms. Sherman but was not able to reach her.</p>
',
        'status_id' => '1',
        'priority_id' => '2',
        'parent_id' => '12903',
        'medium_id' => '2',
        'source_contact_id' => '1',
        'target_contact_id' => '266061',
        'case_id' => '14',
        'is_test' => '0',
        'is_auto' => '0',
        'is_current_revision' => '0',
        'is_deleted' => '0',
        'custom_43' => '',
        'custom_44' => '',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '2',
        'subject' => 'Call Ms. Sherman let her know we received lease',
        'activity_date_time' => '2011-04-01 09:50:00',
        'status_id' => '1',
        'priority_id' => '2',
        'parent_id' => '12979',
        'source_contact_id' => '1',
        'target_contact_id' => '266061',
        'case_id' => '14',
        'is_test' => '0',
        'is_auto' => '0',
        'is_current_revision' => '0',
        'is_deleted' => '0',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '2',
        'subject' => 'Call when lease is faxed',
        'activity_date_time' => '2011-03-31 09:50:00',
        'details' => '<p>
	Waiting for landlord to fax lease. If he does not need to call hiom again before calling Ms. Sherman. Morris Platt 917-299-4932 Owner. Lease was faxed by oener. Called Ms. Sherman but was not able to reach her.</p>
',
        'status_id' => '2',
        'priority_id' => '2',
        'parent_id' => '12903',
        'medium_id' => '2',
        'is_current_revision' => '1',
        'source_contact_id' => '1',
        'target_contact_id' => '266061',
        'case_id' => '14',
        'is_test' => '0',
        'is_auto' => '0',
        'is_deleted' => '0',
        'custom_43' => '',
        'custom_44' => '',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '2',
        'subject' => 'Call Ms. Sherman let her know we received lease',
        'activity_date_time' => '2011-04-01 12:50:00',
        'details' => '<p>
	3/31/11called Ms. Sherman, but she was unreachable, she does not have an answering machine.</p>
',
        'status_id' => '5',
        'priority_id' => '2',
        'parent_id' => '12979',
        'medium_id' => '2',
        'source_contact_id' => '1',
        'target_contact_id' => '266061',
        'case_id' => '14',
        'is_test' => '0',
        'is_auto' => '0',
        'is_current_revision' => '0',
        'is_deleted' => '0',
        'custom_43' => '',
        'custom_44' => '',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '2',
        'subject' => 'Call Ms. Sherman let her know we received lease',
        'activity_date_time' => '2011-04-01 12:50:00',
        'details' => '<p>
	3/31/11called Ms. Sherman, but she was unreachable, she does not have an answering machine.</p>
',
        'status_id' => '2',
        'priority_id' => '2',
        'parent_id' => '12979',
        'medium_id' => '2',
        'is_current_revision' => '1',
        'source_contact_id' => '1',
        'target_contact_id' => '266061',
        'case_id' => '14',
        'is_test' => '0',
        'is_auto' => '0',
        'is_deleted' => '0',
        'custom_43' => '',
        'custom_44' => '',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '38',
        'subject' => 'New meeting date',
        'activity_date_time' => '2011-03-04 18:49:00',
        'status_id' => '1',
        'priority_id' => '2',
        'parent_id' => '13022',
        'source_contact_id' => '1',
        'target_contact_id' => '304528',
        'case_id' => '17',
        'is_test' => '0',
        'is_auto' => '0',
        'is_current_revision' => '0',
        'is_deleted' => '0',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '38',
        'subject' => 'New meeting date',
        'activity_date_time' => '2011-03-04 18:49:00',
        'details' => '<p>
	email attached</p>
',
        'status_id' => '2',
        'priority_id' => '2',
        'parent_id' => '13022',
        'medium_id' => '2',
        'custom_43' => 'district_office',
        'attachments' => Array
        (
          '0' => '36'
        ),
        'source_contact_id' => '1',
        'target_contact_id' => '304528',
        'case_id' => '17',
        'is_test' => '0',
        'is_auto' => '0',
        'is_current_revision' => '0',
        'is_deleted' => '0',
        'custom_44' => '',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '38',
        'subject' => 'New meeting date',
        'activity_date_time' => '2011-03-31 18:49:00',
        'details' => '<p>
	email attached</p>
',
        'status_id' => '2',
        'priority_id' => '2',
        'parent_id' => '13022',
        'medium_id' => '2',
        'custom_43' => 'district_office',
        'attachments' => Array
        (
          '0' => '36'
        )
      ,
        'source_contact_id' => '1',
        'target_contact_id' => '304528',
        'case_id' => '17',
        'is_test' => '0',
        'is_auto' => '0',
        'is_current_revision' => '0',
        'is_deleted' => '0',
        'custom_44' => '',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '38',
        'subject' => 'New meeting date',
        'activity_date_time' => '2011-03-31 18:49:00',
        'details' => '<p>
	3/31/11 1:15pm: email attached</p>
<p>
	3/31/11 2:30PM Violetta called and stated that Mr. Martinez called her to set up a new meeting time (1PM 4/1/11) Ms Hernandez said she is unable to attend and is even more upset because Mr. Martinez said the principal will not be attending the meeting.</p>
<p>
	I have called and left a voice mail for Mr. Martinez to request an earlier meeting time for Ms. Hernandez</p>
',
        'status_id' => '2',
        'priority_id' => '2',
        'parent_id' => '13022',
        'medium_id' => '2',
        'is_current_revision' => '1',
        'custom_43' => 'district_office',
        'attachments' => Array
        (
          '0' => '36'
        )
      ,
        'source_contact_id' => '1',
        'target_contact_id' => '304528',
        'case_id' => '17',
        'is_test' => '0',
        'is_auto' => '0',
        'is_deleted' => '0',
        'custom_44' => '',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '14',
        'subject' => 'Follow up',
        'activity_date_time' => '2011-03-31 16:51:00',
        'status_id' => '1',
        'priority_id' => '2',
        'parent_id' => '12995',
        'source_contact_id' => '1',
        'target_contact_id' => '266553',
        'case_id' => '20',
        'is_test' => '0',
        'is_auto' => '0',
        'is_current_revision' => '0',
        'is_deleted' => '0',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '14',
        'subject' => 'Follow up',
        'activity_date_time' => '2011-03-31 10:00:00',
        'status_id' => '1',
        'priority_id' => '2',
        'parent_id' => '12995',
        'medium_id' => '2',
        'source_contact_id' => '1',
        'target_contact_id' => '266553',
        'case_id' => '20',
        'is_test' => '0',
        'is_auto' => '0',
        'is_current_revision' => '0',
        'is_deleted' => '0',
        'custom_43' => '',
        'custom_44' => '',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '14',
        'subject' => 'Follow up',
        'activity_date_time' => '2011-03-31 10:00:00',
        'details' => '<p>
	Unreachable.</p>
',
        'status_id' => '1',
        'priority_id' => '2',
        'parent_id' => '12995',
        'medium_id' => '2',
        'source_contact_id' => '1',
        'target_contact_id' => '266553',
        'case_id' => '20',
        'is_test' => '0',
        'is_auto' => '0',
        'is_current_revision' => '0',
        'is_deleted' => '0',
        'custom_43' => '',
        'custom_44' => '',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '14',
        'subject' => 'Follow up',
        'activity_date_time' => '2011-03-31 10:00:00',
        'details' => '<p>
	Unreachable.</p>
',
        'status_id' => '5',
        'priority_id' => '2',
        'parent_id' => '12995',
        'medium_id' => '2',
        'is_current_revision' => '1',
        'source_contact_id' => '1',
        'target_contact_id' => '266553',
        'case_id' => '20',
        'is_test' => '0',
        'is_auto' => '0',
        'is_deleted' => '0',
        'custom_43' => '',
        'custom_44' => '',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '14',
        'subject' => 'Call CDC if no call back',
        'activity_date_time' => '2011-03-31 15:11:00',
        'status_id' => '1',
        'priority_id' => '2',
        'parent_id' => '12884',
        'source_contact_id' => '1',
        'target_contact_id' => '306862',
        'case_id' => '23',
        'is_test' => '0',
        'is_auto' => '0',
        'is_current_revision' => '0',
        'is_deleted' => '0',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '14',
        'subject' => 'Call CDC if no call back',
        'activity_date_time' => '2011-03-31 15:11:00',
        'details' => '<p>
	Spoke to constituent, Lucretia from CDC management called constituent back and was upset constituent had done outreach to our office. Lucretia stated she would submit the transfer request to HUD and it was up to them to approve it.</p>
',
        'status_id' => '2',
        'priority_id' => '2',
        'parent_id' => '12884',
        'medium_id' => '2',
        'is_current_revision' => '1',
        'source_contact_id' => '1',
        'target_contact_id' => '306862',
        'case_id' => '23',
        'is_test' => '0',
        'is_auto' => '0',
        'is_deleted' => '0',
        'custom_43' => '',
        'custom_44' => '',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '2',
        'subject' => 'Check transfer status',
        'activity_date_time' => '2011-04-07 15:11:00',
        'status_id' => '1',
        'priority_id' => '2',
        'parent_id' => '12907',
        'source_contact_id' => '1',
        'target_contact_id' => '306862',
        'case_id' => '23',
        'is_test' => '0',
        'is_auto' => '0',
        'is_current_revision' => '0',
        'is_deleted' => '0',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '2',
        'subject' => 'Check transfer status',
        'activity_date_time' => '2011-04-07 15:11:00',
        'details' => '<p>
	Lucrecia Perez CDC Property Management returned our call. Constituent had informed Ms. Perez of the assult against her daughter last summer but at the time refused to move. The transfer request has been processed and under Violence Against Women&#39;s Act VAWA the manager submitted the paperwork to HUD for aproval of the transfer. Based on Ms. Perez experience HUD takes over a moth to process. Ms. Perez send a copy of the letter to constituent acknowledging transfer request.</p>
',
        'status_id' => '2',
        'priority_id' => '2',
        'parent_id' => '12907',
        'medium_id' => '2',
        'is_current_revision' => '1',
        'source_contact_id' => '1',
        'target_contact_id' => '306862',
        'case_id' => '23',
        'is_test' => '0',
        'is_auto' => '0',
        'is_deleted' => '0',
        'custom_43' => '',
        'custom_44' => '',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '2',
        'subject' => 'Call Lucratia Perez to follow up on emergency transfer and apt availability',
        'activity_date_time' => '2011-06-27 11:25:00',
        'status_id' => '1',
        'priority_id' => '2',
        'parent_id' => '14283',
        'is_current_revision' => '1',
        'source_contact_id' => '1',
        'target_contact_id' => '306862',
        'case_id' => '23',
        'is_test' => '0',
        'is_auto' => '0',
        'is_deleted' => '0',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '14',
        'subject' => 'Call DOT and find out about speed reducer survey',
        'activity_date_time' => '2011-06-07 14:26:00',
        'status_id' => '1',
        'priority_id' => '2',
        'parent_id' => '13809',
        'source_contact_id' => '1',
        'target_contact_id' => '267824',
        'case_id' => '24',
        'is_test' => '0',
        'is_auto' => '0',
        'is_current_revision' => '0',
        'is_deleted' => '0',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '14',
        'subject' => 'Call DOT and find out about speed reducer survey',
        'activity_date_time' => '2011-06-07 14:26:00',
        'details' => '<p>
	<font face="Default Sans Serif,Verdana,Arial,Helvetica,sans-serif" size="2"><span>Hi Colleen,<br />
	<br />
	Hope all is well. I wanted to follow up on the request made by Senator Squadron on April 11th for a speed reducer on 7th St. We wanted to know if the survey had taken place and what the findings were. Also there is a pot hole on the north side of the street closer to Ave C that is holding stagnant water and it smells. A complaint was made to 311 but I don&#39;t have the exact date. If you have any information please let us know.<br />
	<br />
	Best,<br />
	<br />
	Rosemarie Diaz<br />
	Community Liaison<br />
	NYS Senator Daniel Squadron<br />
	Tel: 212-298-5565<br />
	Fax: 212-431-7836</span></font></p>
',
        'status_id' => '2',
        'priority_id' => '2',
        'parent_id' => '13809',
        'medium_id' => '2',
        'is_current_revision' => '1',
        'source_contact_id' => '1',
        'target_contact_id' => '267824',
        'case_id' => '24',
        'is_test' => '0',
        'is_auto' => '0',
        'is_deleted' => '0',
        'custom_43' => '',
        'custom_44' => '',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '14',
        'subject' => 'Call DOT for update',
        'activity_date_time' => '2011-06-14 13:46:00',
        'status_id' => '1',
        'priority_id' => '2',
        'parent_id' => '13821',
        'source_contact_id' => '1',
        'target_contact_id' => '267824',
        'case_id' => '24',
        'is_test' => '0',
        'is_auto' => '0',
        'is_current_revision' => '0',
        'is_deleted' => '0',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '14',
        'subject' => 'Call DOT for update',
        'activity_date_time' => '2011-06-14 13:46:00',
        'status_id' => '2',
        'priority_id' => '2',
        'parent_id' => '13821',
        'medium_id' => '2',
        'is_current_revision' => '1',
        'source_contact_id' => '1',
        'target_contact_id' => '267824',
        'case_id' => '24',
        'is_test' => '0',
        'is_auto' => '0',
        'is_deleted' => '0',
        'custom_43' => '',
        'custom_44' => '',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '16',
        'subject' => "f/u Wendy: left mssg for lawyer, is there anything we can help you with? close case if not or if can't contact.",
        'activity_date_time' => '2011-12-21 10:19:00',
        'status_id' => '1',
        'priority_id' => '2',
        'parent_id' => '17042',
        'source_contact_id' => '1',
        'target_contact_id' => '307036',
        'case_id' => '32',
        'is_test' => '0',
        'is_auto' => '0',
        'is_current_revision' => '0',
        'is_deleted' => '0',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '16',
        'subject' => "f/u Wendy: left mssg for lawyer, is there anything we can help you with? close case if not or if can't contact.",
        'activity_date_time' => '2011-12-21 10:19:00',
        'status_id' => '2',
        'priority_id' => '2',
        'parent_id' => '17042',
        'medium_id' => '2',
        'is_current_revision' => '1',
        'source_contact_id' => '1',
        'target_contact_id' => '307036',
        'case_id' => '32',
        'is_test' => '0',
        'is_auto' => '0',
        'is_deleted' => '0',
        'custom_43' => '',
        'custom_44' => '',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '2',
        'subject' => 'try calling, close case',
        'activity_date_time' => '2011-12-30 12:11:00',
        'status_id' => '1',
        'priority_id' => '2',
        'parent_id' => '17021',
        'is_current_revision' => '1',
        'is_deleted' => '1',
        'source_contact_id' => '1',
        'target_contact_id' => '307054',
        'case_id' => '37',
        'is_test' => '0',
        'is_auto' => '0',
        'version' => '3',
      ),
      Array
      (
        'activity_type_id' => '2',
        'subject' => 'find more information',
        'activity_date_time' => '2012-01-05 12:15:00',
        'status_id' => '1',
        'priority_id' => '2',
        'parent_id' => '17078',
        'is_current_revision' => '1',
        'is_deleted' => '1',
        'source_contact_id' => '1',
        'target_contact_id' => '307062',
        'case_id' => '40',
        'is_test' => '0',
        'is_auto' => '0',
        'version' => '3',
      ),
    );
    return $rerun;
  }//sdActivities

  function sdActivitiesCreate() {
    $activities = self::sdActivities();

    foreach ( $activities as $params ) {
      $attachments = (!empty($params['attachments'])) ? $params['attachments'] : '';
      unset($params['parent_id']);
      unset($params['attachments']);

      //clean params array
      $params = self::_cleanArray($params);

      $newActivity = self::_importAPI('activity', 'create', $params);
      //bbscript_log(LL::TRACE, 'importActivities newActivity', $newActivity);

      //handle attachments
      if ( $attachments ) {
        foreach ( $attachments as $attID ) {
          //self::_importEntityAttachments($newActivity['id'], $attID, 'civicrm_activity');

        }
      }
    }
  }//sdActivitiesCreate

  function sdExtInt() {
    $sql = "
      SELECT external_identifier, id
      FROM civicrm_contact
      WHERE external_identifier LIKE 'SD26_BB%'
        AND is_deleted != 1
    ";
    $r = CRM_Core_DAO::executeQuery($sql);

    $extInt = array();
    while ( $r->fetch() ) {
      $extInt[$r->external_identifier] = $r->id;
    }

    return $extInt;
  }//sdExtInt
}

//run the script
$importData = new CRM_migrateContactsImport();
$importData->run();
