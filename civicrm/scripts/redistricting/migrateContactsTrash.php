<?php

// Project: BluebirdCRM
// Authors: Brian Shaughnessy
// Organization: New York State Senate
// Date: 2013-01-15
// Revised: 2023-09-05

// ./migrateContactsTrash.php -S skelos --dryrun
error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

define('KEYWORD_PARENT_ID', 296);


class CRM_migrateContactsTrash
{
  function run()
  {
    global $_SERVER;

    require_once realpath(dirname(__FILE__)).'/../script_utils.php';

    // Parse the options
    $shortopts = "d:t:y:enl:";
    $longopts = ["dest=", "trash=", "types=", "employers", "dryrun", "log="];
    $optlist = civicrm_script_init($shortopts, $longopts, TRUE);

    if ($optlist === null) {
        $stdusage = civicrm_script_usage();
        $usage = '--dest {distnum|instance}  [--trash {none|migrated|boeredist}]  [--types {I|H|O}]  [--employers]  [--dryrun]  [--log LEVEL]';
        error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
        exit(1);
    }

    if (empty($optlist['dest'])) {
      bbscript_log(LL::FATAL, "The destination option must be defined.");
      exit();
    }

    if (empty($optlist['trash'])) {
      $optlist['trash'] = 'migrated';
    }

    if (empty($optlist['types'])) {
      $optlist['types'] = 'IHO';
    }

    if (!empty($optlist['log'])) {
      set_bbscript_log_level($optlist['log']);
    }

    //get instance settings for source and destination
    $bbcfg_source = get_bluebird_instance_config($optlist['site']);
    //bbscript_log(LL::TRACE, "bbcfg_source", $bbcfg_source);

    $source = [
      'name' => $optlist['site'],
      'num' => $bbcfg_source['district'],
      'db' => $bbcfg_source['db.civicrm.prefix'].$bbcfg_source['db.basename'],
      'files' => $bbcfg_source['data.rootdir'],
      'domain' => $optlist['site'].'.'.$bbcfg_source['base.domain'],
      'install_class' => $bbcfg_source['install_class'],
    ];

    //destination may be passed as the instance name OR district ID
    if (is_numeric($optlist['dest'])) {
      $distnum = $optlist['dest'];
      //retrieve the instance config using the district ID
      $bbFullConfig = get_bluebird_config();
      //bbscript_log(LL::TRACE, "bbFullConfig", $bbFullConfig);

      foreach ($bbFullConfig as $group => $details) {
        if (strpos($group, 'instance:') !== false) {
          if ($details['district'] == $distnum) {
            $instance = substr($group, 9);
            break;
          }
        }
      }
    }
    else {
      $instance = $optlist['dest'];
    }

    $bbcfg_dest = get_bluebird_instance_config($instance);
    $dest = [
      'name' => $instance,
      'num' => $bbcfg_dest['district'],
      'db' => $bbcfg_dest['db.civicrm.prefix'].$bbcfg_dest['db.basename'],
      'files' => $bbcfg_dest['data.rootdir'],
      'domain' => $instance.'.'.$bbcfg_dest['base.domain'],
    ];

    //bbscript_log(LL::TRACE, "$source", $source);
    //bbscript_log(LL::TRACE, "$dest", $dest);

    //if either dest or source unset, exit
    if (empty($dest['db']) || empty($source['db'])) {
      bbscript_log(LL::FATAL, "Unable to retrieve configuration for either source or destination instance.");
      exit();
    }

    //determine if we need to restrict by contact type
    $cTypesInclude = [];
    $cTypes = [
      'I' => 'Individual',
      'H' => 'Household',
      'O' => 'Organization',
    ];
    $types = str_split($optlist['types']);
    foreach ($types as $type) {
      if (!in_array(strtoupper($type), array_keys($cTypes))) {
        bbscript_log(LL::FATAL, "You selected invalid options for the contact type parameter. Please enter any combination of IHO (individual, household, organization), with no spaces between the characters.");
        exit();
      }
      else {
        $cTypesInclude[] = $cTypes[$type];
        bbscript_log(LL::INFO, "{$cTypes[$type]} contacts will be trashed.");
      }
    }

    // Initialize CiviCRM
    require_once 'CRM/Core/Config.php';
    $config = CRM_Core_Config::singleton();
    $session = CRM_Core_Session::singleton();

    self::trashContacts($source, $dest, $optlist['trash'], $optlist['employers'], $optlist['dryrun'], $cTypesInclude);
  }//run


  /*
   * trash contacts in source database using options
   * we use the api to ensure all associated records are dealt with correctly
   */
  function trashContacts($source, $dest, $trashopt, $employers = FALSE, $optDry, $trashContactTypes)
  {
    if ($trashopt == 'none') {
      bbscript_log(LL::INFO, "No records trashed (trash action = none).");
      exit();
    }

    bbscript_log(LL::INFO, "Starting trashing process.");
    bbscript_log(LL::INFO, "Removing district {$dest['num']} contacts from district {$source['num']} database.");

    $trashedIDs = [];
    //bbscript_log(LL::TRACE, '$trashContactTypes', $trashContactTypes);

    switch ($trashopt) {
      case 'migrated':
        self::_trashMigrated($trashedIDs, $dest['num'], $employers);
        break;
      case 'boeredist':
        self::_trashBOE($trashedIDs, $dest['num'], $employers);
        break;
      default:
    }

    //cleanup trashedIDs and remove duplicates
    foreach ($trashedIDs as $type => $trashedByType) {
      if (!in_array($type, $trashContactTypes) && $type != 'OrgsRetained') {
        unset($trashedIDs[$type]);
        continue;
      }
      $trashedIDs[$type] = array_unique($trashedByType);
    }

    //process orgs and remove from trash list if various criteria met
    self::_trashOrgs($trashedIDs, $source['num']);

    //6649 remove any contacts with a userID
    self::_excludeUsers($trashedIDs);

    //bbscript_log(LL::TRACE, '$trashedIDs', $trashedIDs);
    self::_tagContacts($trashedIDs, $dest['num'], $optDry);

    $totalCount = 0;
    foreach ($trashContactTypes as $type) {
      if (empty($trashedIDs[$type])) {
        continue;
      }

      if ($optDry) {
        bbscript_log(LL::DEBUG, "The following $type contacts would be trashed:", $trashedIDs[$type]);
      }
      else {
        bbscript_log(LL::INFO, "Trashing {$type} contacts...");
        $i = 0;
        foreach ($trashedIDs[$type] as $cid) {
          $params = [
            'version' => 3,
            'id' => $cid,
          ];
          if (!$optDry) {
            civicrm_api('contact', 'delete', $params);
          }

          $i++;
          $totalCount++;
          if ($i == 500) {
            bbscript_log(LL::INFO, "contacts trashed: {$totalCount}...");
            $i = 0;
          }
        }
      }
    }

    $msg = "Removed contacts in district {$dest['num']} ({$dest['name']}) from the district {$source['num']} ({$source['name']}) database";
    bbscript_log(LL::INFO, "Completed contact migration trashing");
    bbscript_log(LL::INFO, $msg);

    //generate stats
    $indCount = count($trashedIDs['Individual'] ?? []);
    $orgCount = count($trashedIDs['Organization'] ?? []);
    $hhCount = count($trashedIDs['Household'] ?? []);

    $stats = [
      'trashing option' => $trashopt,
      'employers trashed?' => ($employers) ? 'yes' : 'no',
      'total individuals trashed' => $indCount,
      'total organizations trashed' => $orgCount,
      'total households trashed' => $hhCount,
      'total contacts trashed' => $indCount + $orgCount + $hhCount,
      'organization records retained (count)' => count($trashedIDs['OrgsRetained'] ?? []),
      'organization records retained (list)' => $trashedIDs['OrgsRetained'] ?? [],
    ];
    bbscript_log(LL::INFO, "Trashing statistics:", $stats);

    //save log to file
    if (!$optDry) {
      //set import folder based on environment
      $fileDir = '/data/redistricting/bluebird_'.$source['install_class'].'/MigrationTrashReports';
      if (!file_exists($fileDir)) {
        mkdir($fileDir, 0775, TRUE);
      }

      $reportFile = $fileDir.'/'.$source['name'].'_'.$dest['name'].'.txt';
      $fileResource = fopen($reportFile, 'w');

      $content = [
        'migration' => $msg,
        'stats' => $stats,
      ];

      $content = print_r($content, TRUE);
      fwrite($fileResource, $content);
      fclose($fileResource);
    }
  }//trashContacts


  /*
   * helper function to retrieve migrated contacts from redistricting report
   * contact cache table we split into indivs and orgs (orgs will be processed
   * according to employer param)
   */
  function _trashMigrated(&$trashedIDs, $distnum, $employers)
  {
    //check for existence of redist contact cache table
    $redistTbl = "redist_report_contact_cache";
    $sql = "SHOW TABLES LIKE '{$redistTbl}'";
    if (!CRM_Core_DAO::singleValueQuery($sql)) {
      bbscript_log(LL::FATAL,
        "Redistricting contact cache table for this district does not exist. Exiting trashing process.");
      exit();
    }

    $sql = "
      SELECT r.contact_id, r.contact_type, c.employer_id
      FROM {$redistTbl} r
      JOIN civicrm_contact c
        ON r.contact_id = c.id
        AND c.is_deleted = 0
      WHERE district = $distnum
    ";
    $contacts = CRM_Core_DAO::executeQuery($sql);

    while ($contacts->fetch()) {
      $trashedIDs[$contacts->contact_type][] = $contacts->contact_id;

      //if employers option true, add employer ids
      if (!empty($contacts->employer_id) && $employers) {
        $trashedIDs['Organization'][] = $contacts->employer_id;
      }
    }
    //bbscript_log(LL::TRACE, '_trashMigratedIndiv $trashedIDs', $trashedIDs);
  }


  /*
   * helper function to retrieve boe indivs from db
   * we retrieve any contact with a BOE address in the destination district
   * we also retrieve employer IDs for future use
   */
  function _trashBOE(&$trashedIDs, $distnum, $employers)
  {
    $sql = "
      SELECT c.id, c.contact_type, c.employer_id
      FROM civicrm_address a
      JOIN civicrm_value_district_information_7 di
        ON a.id = di.entity_id
        AND di.ny_senate_district_47 = $distnum
        AND a.location_type_id = 6
      JOIN civicrm_contact c
        ON a.contact_id = c.id
        AND c.is_deleted = 0
    ";
    $contacts = CRM_Core_DAO::executeQuery($sql);

    while ($contacts->fetch()) {
      $trashedIDs[$contacts->contact_type][] = $contacts->id;

      //if employers option true, add employer ids
      if (!empty($contacts->employer_id) && $employers) {
        $trashedIDs['Organization'][] = $contacts->employer_id;
      }
    }
    //bbscript_log(LL::TRACE, '_trashBOEIndiv $trashedIDs', $trashedIDs);
  }


  /*
   * process orgs list to determine what will be trashed
   * the action will depend on the combination of trashopt and employers opt
   * in every case, we do not trash if:
   * - other relationships exists which are not being trashed
   * - the org is in the source district
   * - the org has email, activity, note, or case records
   */
  function _trashOrgs(&$trashedIDs, $distnum)
  {
    //bbscript_log(LL::TRACE, '_trashOrgs $trashedIDs initial', $trashedIDs['Organization']);

    //return immediately if empty
    if (empty($trashedIDs['Organization'])) {
      return;
    }

    //remove from orgID list any orgs that meet the three exclusion criteria
    $orgList = implode(', ', $trashedIDs['Organization']);

    //query to retrieve orgs with an in-source-district address; then remove from orgs array
    $sql = "
      SELECT a.contact_id
      FROM civicrm_address a
      JOIN civicrm_value_district_information_7 di
        ON a.id = di.entity_id
        AND di.ny_senate_district_47 = $distnum
      WHERE a.contact_id IN ({$orgList})
      GROUP BY a.contact_id
    ";
    $orgsInDistrict = CRM_Core_DAO::executeQuery($sql);
    while ($orgsInDistrict->fetch()) {
      if (in_array($orgsInDistrict->contact_id, $trashedIDs['Organization'])) {
        $key = array_search($orgsInDistrict->contact_id, $trashedIDs['Organization']);
        $trashedIDs['OrgsRetained'][$key] = $orgsInDistrict->contact_id;
        unset($trashedIDs['Organization'][$key]);
      }
    }
    //bbscript_log(LL::TRACE, '_trashOrgs $trashedIDs after in district', $trashedIDs['Organization']);

    //remove orgs with meaningful data
    /************************ THIS BLOCK HAS BEEN REMOVED ******************
    $valueAdded = [
      'email' => 'contact_id',
      'activity_target' => 'target_contact_id',
      'note' => 'entity_id',
      'case_contact' => 'contact_id',
    ];
    foreach ($valueAdded as $tblSuffix => $fk) {
      //return immediately if empty
      if (empty($trashedIDs['Organization']))
        return;

      //rebuild orgList each time to reflect mods
      $orgList = implode(', ', $trashedIDs['Organization']);

      $additionalWhere = ($tblSuffix == 'note') ? ' AND entity_table = "civicrm_contact" ' : '';
      $sql = "
        SELECT {$fk}
        FROM civicrm_{$tblSuffix}
        WHERE {$fk} IN ({$orgList})
        {$additionalWhere}
      ";
      $valRecords = CRM_Core_DAO::executeQuery($sql);
      while ($valRecords->fetch()) {
        if (in_array($valRecords->contact_id, $trashedIDs['Organization'])) {
          $key = array_search($valRecords->contact_id, $trashedIDs['Organization']);
          $trashedIDs['OrgsRetained'][$key] = $orgsInDistrict->contact_id;
          unset($trashedIDs['Organization'][$key]);
        }
      }
    }
    ************************** END OF REMOVED BLOCK ************************/
    //bbscript_log(LL::TRACE, '_trashOrgs $trashedIDs after value records', $trashedIDs['Organization']);

    //remove orgs with relationships
    $relPair = [
      'contact_id_a' => 'contact_id_b',
      'contact_id_b' => 'contact_id_a',
    ];
    foreach ($relPair as $c1 => $c2) {
      //return immediately if empty
      if (empty($trashedIDs['Organization'])) {
        return;
      }

      //get relationships with org where related record is not in indiv list
      if (!empty($trashedIDs['Organization']) && !empty($trashedIDs['Individual'])) {
        //rebuild orgList and indivList
        $orgList = implode(', ', $trashedIDs['Organization']);
        $indivList = implode(', ', $trashedIDs['Individual']);

        $sql = "
          SELECT r.{$c1}
          FROM civicrm_relationship r
          JOIN civicrm_contact c
            ON r.{$c2} = c.id
            AND c.is_deleted != 1
          WHERE $c1 IN ({$orgList})
            AND $c2 NOT IN ({$indivList})
            AND is_active = 1
        ";
        $rels = CRM_Core_DAO::executeQuery($sql);
        while ($rels->fetch()) {
          if (in_array($rels->$c1, $trashedIDs['Organization'])) {
            $key = array_search($rels->$c1, $trashedIDs['Organization']);
            $trashedIDs['OrgsRetained'][$key] = $rels->$c1;
            unset($trashedIDs['Organization'][$key]);
          }
        }
      }
    }
    //bbscript_log(LL::TRACE, '_trashOrgs $trashedIDs after relationships', $trashedIDs['Organization']);
  }//_trashOrgs


  /*
   * cycle through and remove any contacts that have a user account
   */
  function _excludeUsers(&$trashedIDs)
  {
    //get contacts with user accounts from uf_match
    $cids = [];
    $sql = "
      SELECT contact_id
      FROM civicrm_uf_match
    ";
    $dao = CRM_Core_DAO::executeQuery($sql);
    while ($dao->fetch()) {
      $cids[] = $dao->contact_id;
    }

    foreach ($trashedIDs as $type => $ids) {
      $trashedIDs[$type] = array_diff($ids, $cids);
    }
  }//_excludeUsers


  //tag all contacts to be trashed
  function _tagContacts($trashedIDs, $distnum, $optDry)
  {
    //create tag
    $params = [
      'version' => 3,
      'name' => 'Redist2023 Trashed SD'.$distnum,
      'description' => "Out-of-district contact trashed from redistricting to SD$distnum",
      'parent_id' => KEYWORD_PARENT_ID, //keywords
    ];
    if ($optDry) {
      bbscript_log(LL::DEBUG, "Tag to be created: {$params['name']}");
    }
    else {
      $tag = civicrm_api('tag', 'create', $params);
      //bbscript_log(LL::TRACE, '_tagContacts $tag', $tag);

      //if error, may be because tag already exists
      if ($tag['is_error']) {
        unset($params['description']);
        $tag = civicrm_api('tag', 'get', $params);
        //bbscript_log(LL::TRACE, '_tagContacts $tag', $tag);
      }
    }

    foreach ($trashedIDs as $type => $contacts) {
      if ($type == 'OrgsRetained') {
        continue;
      }

      $params = [
        'version' => 3,
        'entity_table' => 'civicrm_contact',
        'tag_id' => $tag['id'],
      ];
      foreach ($contacts as $k => $contactID) {
        $params['contact_id.'.$k] = $contactID;
      }
      //bbscript_log(LL::TRACE, '_tagContacts $params', $params);

      if ($optDry) {
        bbscript_log(LL::DEBUG, "$type contacts would be tagged...");
      }
      else {
        $entityTags = civicrm_api('entity_tag', 'create', $params);
        //bbscript_log(LL::TRACE, '_tagContacts $entityTags', $entityTags);
      }
    }

    //validation: we had some occurrences of contacts not getting tagged, so do a check
    $unTagged = [];
    $reprocessTags = FALSE;
    foreach ($trashedIDs as $type => $contacts) {
      if ($type == 'OrgsRetained') {
        continue;
      }

      $contactList = implode(',', $contacts);
      $sql = "
        SELECT c.id
        FROM civicrm_contact c
        LEFT JOIN civicrm_entity_tag et
          ON c.id = et.entity_id
          AND et.entity_table = 'civicrm_contact'
        WHERE c.id IN ($contactList)
          AND et.id IS NULL
      ";
      $noTag = CRM_Core_DAO::executeQuery($sql);
      while ($noTag->fetch()) {
        if (!$optDry) {
          bbscript_log(LL::DEBUG, "Contact ID{$noTag->id} was not tagged successfully. Queued for reprocessing...");
        }
        $unTagged[$type][] = $noTag->id;
      }

      if (!empty($unTagged[$type])) {
        $reprocessTags = TRUE;
      }
    }
    if ($reprocessTags && !$optDry) {
      bbscript_log(LL::INFO, "Reprocessing untagged contacts...");
      self::_tagContacts($unTagged, $distnum, $optDry);
    }
  }//_tagContacts

}//end class


//run the script if called directly
$trashData = new CRM_migrateContactsTrash();
$trashData->run();

