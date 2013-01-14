<?php

// Project: BluebirdCRM
// Authors: Stefan Crain, Graylin Kim, Ken Zalewski
// Organization: New York State Senate
// Date: 2012-10-26
// Revised: 2012-11-21

// ./migrateContacts.php -S skelos --dest 45 --file --dryrun
error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

define('DRY_COUNT', 25);
define('DEFAULT_LOG_LEVEL', 'TRACE');
define('LOC_TYPE_BOE', 6);

class CRM_migrateContactsImport {

  function run() {

    global $_SERVER;
    global $optDry;

    require_once 'script_utils.php';

    // Parse the options
    $shortopts = "f:n";
    $longopts = array("filename=", "dryrun");
    $optlist = civicrm_script_init($shortopts, $longopts);

    if ($optlist === null) {
        $stdusage = civicrm_script_usage();
        $usage = '[--filename FILENAME] [--dryrun]';
        error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
        exit(1);
    }

    if ( empty($optlist['filename']) ) {
      bbscript_log("fatal", "No filename provided. You must provide a filename to import.");
      exit();
    }

    //get instance settings which represents the destination instance
    $bbcfg_dest = get_bluebird_instance_config($optlist['site']);
    //bbscript_log("trace", '$bbcfg_dest', $bbcfg_dest);

    $civicrm_root = $bbcfg_dest['drupal.rootdir'].'/sites/all/modules/civicrm';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    if (!CRM_Utils_System::loadBootstrap(array(), FALSE, FALSE, $civicrm_root)) {
      CRM_Core_Error::debug_log_message('Failed to bootstrap CMS from migrateContactsImport.');
      return FALSE;
    }

    $dest = array(
      'name' => $optlist['site'],
      'num' => $bbcfg_dest['district'],
      'db' => $bbcfg_dest['db.civicrm.prefix'].$bbcfg_dest['db.basename'],
      'files' => $bbcfg_dest['data.rootdir'],
      'domain' => $optlist['site'].'.'.$bbcfg_dest['base.domain'],
    );
    //bbscript_log("trace", "$dest", $dest);

    //if dest unset/irretrievable, exit
    if ( empty($dest['db']) ) {
      bbscript_log("fatal", "Unable to retrieve configuration for destination instance.");
      exit();
    }

    // Initialize CiviCRM
    require_once 'CRM/Core/Config.php';
    $config = CRM_Core_Config::singleton();
    $session = CRM_Core_Session::singleton();

    //retrieve/set other options
    $optDry = $optlist['dryrun'];

    //set import folder based on environment
    $fileDir = '/data/importData/migrate_'.$bbcfg_dest['install_class'];
    if ( !file_exists($fileDir) ) {
      mkdir( $fileDir, 0775, TRUE );
    }

    //check for existence of file to import
    $importFile = $fileDir.'/'.$optlist['filename'];
    if ( !file_exists($importFile) ) {
      bbscript_log("fatal", "The import file you have specified does not exist. It must reside in {$fileDir}.");
      exit();
    }

    //call main import function
    $exportData = self::importData($dest, $importFile);
    $source = $exportData['source'];

    bbscript_log("info", "Completed contact migration import from district {$source['num']} ({$source['name']}) to district {$dest['num']} ({$dest['name']}) using {$importFile}.");

  }//run

  function importData($dest, $importFile) {
    global $optDry;

    //retrieve data from file and set to variable as array
    $exportData = json_decode(file_get_contents($importFile), TRUE);
    //bbscript_log("trace", "importData $exportData", $exportData);

    //parse the import file source/dest, compare with params and return a warning message if values do not match
    if ( $exportData['dest']['name'] != $dest['name'] ) {
      bbscript_log('fatal', 'The destination defined in the import file does not match the parameters passed to the script. Exiting the script as a mismatched destination could create significant data problems. Please investigate and then rerun the script.');
      exit();
    }

    $source = $exportData['source'];

    //get bluebird administrator id to set as source
    $sql = "
      SELECT id
      FROM civicrm_contact
      WHERE display_name = 'Bluebird Administrator'
    ";
    $bbAdmin = CRM_Core_DAO::singleValueQuery($sql);
    $bbAdmin = ( $bbAdmin ) ? $bbAdmin : 1;

    //process the import
    self::importContacts($exportData);
    self::importActivities($exportData, $bbAdmin);
    self::importCases($exportData, $bbAdmin);
    self::importTags($exportData);
    self::importEmployment($exportData, $optDry);
    self::importDistrictInfo($exportData, $optDry);

    //create group and add migrated contacts
    self::addToGroup($exportData);

    return $exportData;
  }//importData

  function importContacts($exportData) {
    global $optDry;
    global $extInt;

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

    foreach ( $exportData['import'] as $extID => $details ) {
      //bbscript_log("trace", 'importContacts importContacts $details', $details);

      //look for existing contact record in target db and add to params array
      $matchedContact = self::_contactLookup($details);
      if ( $matchedContact ) {
        $details['contact']['id'] = $matchedContact;
      }

      //import the contact via api
      $contact = self::_importAPI('contact', 'create', $details['contact']);
      //bbscript_log("trace", "importContacts _importAPI contact", $contact);

      //set the contact ID for use in related records; also build mapping array
      $contactID = $contact['id'];
      $extInt[$extID] = $contactID;

      //cycle through each set of related records
      foreach ( $relatedTypes as $type ) {
        $fk = ( in_array($type, $fkEId) ) ? 'entity_id' : 'contact_id';
        if ( isset($details[$type]) ) {
          foreach ( $details[$type] as $record ) {
            $record['version'] = 3;
            $record[$fk] = $contactID;
            self::_importAPI($type, 'create', $record);
          }
        }
      }
    }
  }//importContacts

  function importActivities($exportData, $bbAdmin) {
    global $optDry;
    global $extInt;

    if ( !isset($exportData['activities']) ) {
      return;
    }

    foreach ( $exportData['activities'] as $actID => $details ) {
      $params = $details['activity'];
      $params['source_contact_id'] = $bbAdmin;
      unset($params['activity_id']);
      unset($params['source_record_id']);
      unset($params['parent_id']);
      unset($params['original_id']);
      unset($params['entity_id']);

      $targets = array();
      foreach ( $details['targets'] as $tExtID ) {
        $targets[] = $extInt[$tExtID];
      }
      $params['target_contact_id'] = $targets;

      if ( isset($details['custom']) ) {
        $params['custom_43'] = $details['custom']['place_of_inquiry_43'];
        $params['custom_44'] = $details['custom']['activity_category_44'];
      }

      $newActivity = self::_importAPI('activity', 'create', $params);
      //bbscript_log("trace", 'importActivities newActivity', $newActivity);
    }
  }//importActivities

  function importCases($exportData, $bbAdmin) {
    global $optDry;
    global $extInt;

    if ( !isset($exportData['cases']) ) {
      return;
    }

    //cycle through contacts
    foreach ( $exportData['cases'] as $extID => $cases ) {
      $contactID = $extInt[$extID];

      //cycle through cases
      foreach ( $cases as $case ) {
        $activities = $case['activities'];
        unset($case['activities']);

        $case['version'] = 3;
        $case['contact_id'] = $contactID;
        $case['creator_id'] = $bbAdmin;
        //$case['debug'] = 1;
        $newCase = self::_importAPI('case', 'create', $case);
        //bbscript_log("trace", "importCases newCase", $newCase);

        $caseID = $newCase['id'];

        foreach ( $activities as $activity ) {
          $activity['source_contact_id'] = $bbAdmin;
          $activity['target_contact_id'] = $contactID;
          $activity['case_id'] = $caseID;
          $activity['version'] = 3;
          $newActivity = self::_importAPI('activity', 'create', $activity);
          //bbscript_log("trace", 'importCases newActivity', $newActivity);
        }
      }
    }
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

    //bbscript_log("trace", 'importTags tags', $exportData['tags']);

    //process keywords
    foreach ( $exportData['tags']['keywords'] as $keyID => $keyDetail ) {
      $params = array(
        'version' => 3,
        'name' => $keyDetail['name'],
        'description' => $keyDetail['desc'],
        'parent_id' => 296, //keywords constant
      );
      $newKeyword = self::_importAPI('tag', 'create', $params);
      //bbscript_log("trace", 'importTags newKeyword', $newKeyword);
      $tagExtInt[$keyID] = $newKeyword['id'];
    }

    //process positions
    foreach ( $exportData['tags']['positions'] as $posID => $posDetail ) {
      $sql = "
        SELECT id
        FROM civicrm_tag
        WHERE name = '{$posDetail['name']}'
          AND parent_id = 292
      ";
      $intPosID = CRM_Core_DAO::singleValueQuery($sql);
      if ( !$intPosID ) {
        $params = array(
          'version' => 3,
          'name' => $posDetail['name'],
          'description' => $posDetail['desc'],
          'parent_id' => 292, //positions constant
        );
        $newPos = self::_importAPI('tag', 'create', $params);
        //bbscript_log("trace", 'importTags newPos', $newPos);
        $intPosID = $newPos['id'];
      }
      $tagExtInt[$posID] = $intPosID;
    }

    //process issue codes
    //begin by constructing base level tag
    $params = array(
      'version' => 3,
      'name' => "Migrated from: {$exportData['source']['name']} ({$exportData['source']['num']})",
      'description' => 'Tags migrated from other district',
      'parent_id' => 291,
    );
    $icParent = self::_importAPI('tag', 'create', $params);

    //level 1
    foreach ( $exportData['tags']['issuecodes']  as $icID1 => $icD1 ) {
      $params = array(
        'version' => 3,
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
            'version' => 3,
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
                'version' => 3,
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
                    'version' => 3,
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
                        'version' => 3,
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
    //bbscript_log("trace", '_importTags $tagExtInt', $tagExtInt);

    //construct tag entity records
    foreach ( $exportData['tags']['entities'] as $extID => $extTags ) {
      $params = array(
        'version' => 3,
        'contact_id' => $extInt[$extID],
      );
      foreach ( $extTags as $tIndex => $tID ) {
        $params['tag_id.'.$tIndex] = $tagExtInt[$tID];
      }
      //bbscript_log("trace", '_importTags entityTag $params', $params);
      self::_importAPI('entity_tag', 'create', $params);
    }


  }//importTags

  function importEmployment($exportData, $optDry) {
    global $optDry;
    global $extInt;

    if ( !isset($exportData['employment']) ) {
      return;
    }

  }//importEmployment

  function importDistrictInfo($exportData, $optDry) {
    global $exportData;
    global $importDryrun;

  }//importDistrictInfo

  /*
   * wrapper for civicrm_api
   * allows us to determine action based on dryrun status
   */
  function _importAPI($entity, $action, $params) {
    global $optDry;

    if ( $optDry ) {
      bbscript_log("debug", "_importAPI entity:{$entity} action:{$action} params:", $params);
    }
    else {
      //prepend api version
      $params['version'] = 3;
      $api = civicrm_api($entity, $action, $params);
      return $api;
    }
  }//_importAPI

  /*
   * dedupe matching function
   * given the values to be imported, lookup using indiv strict default rule
   * return contact ID if found
   */
  function _contactLookup($contact) {
    require_once 'CRM/Dedupe/Finder.php';
    require_once '/opt/bluebird_dev/modules/nyss_dedupe/nyss_dedupe.module';
    //bbscript_log("trace", '_contactLookup $contact', $contact);

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
        $ruleName = 'Individual Strict (first + last + (street + zip | email))';
        break;

      case 'Organization':
        $params['civicrm_contact']['organization_name'] = CRM_Utils_Array::value('organization_name', $contact['contact']);
        $ruleName = 'Organization 1 (name + street + city + email)';
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
    //bbscript_log("trace", '_contactLookup $params', $params);

    //use dupeQuery hook implementation to build sql
    $o = new stdClass();
    $o->name = $ruleName;
    $o->params = $params;
    $o->noRules = false;
    $tableQueries = array();
    nyss_dedupe_civicrm_dupeQuery($o, 'table', $tableQueries);
    $sql = $tableQueries['civicrm.custom.5'];
    $sql = "
      SELECT contact.id
      FROM civicrm_contact as contact
      JOIN ($sql) as dupes
      WHERE dupes.id1 = contact.id
        AND contact.is_deleted = 0
      LIMIT 1
    ";
    //bbscript_log("trace", '_contactLookup $sql', $sql);
    $cid = CRM_Core_DAO::singleValueQuery($sql);

    //also try a lookup on external id (which should really only happen during testing)
    if ( !$cid ) {
      $sql = "
        SELECT id
        FROM civicrm_contact
        WHERE external_identifier = '{$contact['contact']['external_identifier']}'
      ";
      $cid = CRM_Core_DAO::singleValueQuery($sql);
    }

    return $cid;
  }//_contactLookup

  /*
   * helper function to build entity_file record and pass back to originating function
   * called during contact, activities, and case import
   */
  function _importAttachments() {
    global $optDry;

  }//_importAttachments

  /*
   * helper function to copy files from the source directory to destination
   * we copy instead of move because we are timid...
   */
  function _moveAttachment() {
    global $optDry;

  }//_moveAttachment

  /*
   * create group in destination database and add all contacts
   */
  function addToGroup($exportData) {
    global $optDry;

    $source = $exportData['source'];
    $dest = $exportData['dest'];
    $g = $exportData['group'];

    if ( $optDry ) {
      bbscript_log("debug", "Imported contacts added to group:", $g);
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
      bbscript_log("fatal", "Unable to retrieve migration group ({$g['title']}) and add contacts to group.");
      return;
    }

    //contacts
    $contactsList = implode("','", array_keys($exportData['import']));

    //add contacts to group
    $sqlInsert = "
      INSERT IGNORE INTO {$dest['db']}.civicrm_group_contact
      ( group_id, contact_id, status )
      SELECT {$groupID} group_id, id contact_id, 'Added' status
      FROM civicrm_contact
      WHERE external_identifier IN ('{$contactsList}');
    ";
    //bbscript_log("trace", "Group insert:", $sqlInsert);
    CRM_Core_DAO::executeQuery($sqlInsert);

    bbscript_log("info", "Imported contacts added to group: {$g['title']}");
  }//addToGroup

  /*
   * given a custom data group name, return array of fields
   */
  function getCustomFields($name, $flds = TRUE) {
    $group = civicrm_api('custom_group', 'getsingle', array('version' => 3, 'name' => $name ));
    if ( $flds ) {
      $fields = civicrm_api('custom_field', 'get', array('version' => 3, 'custom_group_id' => $group['id']));
      //bbscript_log("trace", 'getCustomFields fields', $fields);
      return $fields['values'];
    }
    else {
      return $group['table_name'];
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

}

//run the script
$importData = new CRM_migrateContactsImport();
$importData->run();
