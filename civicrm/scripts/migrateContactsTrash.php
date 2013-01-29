<?php

// Project: BluebirdCRM
// Authors: Brian Shaughnessy
// Organization: New York State Senate
// Date: 2013-01-15

// ./migrateContactsTrash.php -S skelos --dryrun
error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

define('DEFAULT_LOG_LEVEL', 'TRACE');

class CRM_migrateContactsTrash {

  function run() {

    global $_SERVER;

    require_once 'script_utils.php';

    // Parse the options
    $shortopts = "d:t:en";
    $longopts = array("dest=", "trash=", "employers", "dryrun");
    $optlist = civicrm_script_init($shortopts, $longopts);

    if ($optlist === null) {
        $stdusage = civicrm_script_usage();
        $usage = '[--dest ID|DISTNAME] [--trash OPTION] [--employers] [--dryrun]';
        error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
        exit(1);
    }

    if ( empty($optlist['dest']) || empty($optlist['trash']) ) {
      bbscript_log("fatal", "The destination and trash options must be defined.");
      exit();
    }

    //get instance settings for source and destination
    $bbcfg_source = get_bluebird_instance_config($optlist['site']);
    //bbscript_log("trace", "bbcfg_source", $bbcfg_source);

    $civicrm_root = $bbcfg_source['drupal.rootdir'].'/sites/all/modules/civicrm';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    if (!CRM_Utils_System::loadBootstrap(array(), FALSE, FALSE, $civicrm_root)) {
      CRM_Core_Error::debug_log_message('Failed to bootstrap CMS from migrateContactsTrash.');
      return FALSE;
    }

    $source = array(
      'name' => $optlist['site'],
      'num' => $bbcfg_source['district'],
      'db' => $bbcfg_source['db.civicrm.prefix'].$bbcfg_source['db.basename'],
      'files' => $bbcfg_source['data.rootdir'],
      'domain' => $optlist['site'].'.'.$bbcfg_source['base.domain'],
    );

    //destination may be passed as the instance name OR district ID
    if ( is_numeric($optlist['dest']) ) {
      $dest['num'] = $optlist['dest'];

      //retrieve the instance config using the district ID
      $bbFullConfig = get_bluebird_config();
      //bbscript_log("trace", "bbFullConfig", $bbFullConfig);
      foreach ( $bbFullConfig as $group => $details ) {
        if ( strpos($group, 'instance:') !== false ) {
          if ( $details['district'] == $optlist['dest'] ) {
            $dest['name'] = substr($group, 9);
            $bbcfg_dest = get_bluebird_instance_config($dest['name']);
            $dest['db'] = $bbcfg_dest['db.civicrm.prefix'].$bbcfg_dest['db.basename'];
            $dest['files'] = $bbcfg_dest['data.rootdir'];
            $dest['domain'] = $dest['name'].'.'.$bbcfg_dest['base.domain'];
            break;
          }
        }
      }
    }
    else {
      $bbcfg_dest = get_bluebird_instance_config($optlist['dest']);
      $dest = array(
        'name' => $optlist['dest'],
        'num' => $bbcfg_dest['district'],
        'db' => $bbcfg_dest['db.civicrm.prefix'].$bbcfg_dest['db.basename'],
        'files' => $bbcfg_dest['data.rootdir'],
        'domain' => $optlist['dest'].'.'.$bbcfg_dest['base.domain'],
      );
    }
    //bbscript_log("trace", "$source", $source);
    //bbscript_log("trace", "$dest", $dest);

    //if either dest or source unset, exit
    if ( empty($dest['db']) || empty($source['db']) ) {
      bbscript_log("fatal", "Unable to retrieve configuration for either source or destination instance.");
      exit();
    }

    // Initialize CiviCRM
    require_once 'CRM/Core/Config.php';
    $config = CRM_Core_Config::singleton();
    $session = CRM_Core_Session::singleton();

    self::trashContacts($source, $dest, $optlist['trash'], $optlist['employers'], $optlist['dryrun']);
  }//run

  /*
   * trash contacts in source database using options
   * we use the api to ensure all associated records are dealt with correctly
   */
  function trashContacts($source, $dest, $trashopt, $employers = FALSE, $optDry) {

    if ( $trashopt == 'none' ) {
      bbscript_log("info", "No records trashed (trash action = none).");
      exit();
    }

    $trashedIDs = array();
    $trashContactTypes = array( 'Individual' );

    switch ( $trashopt ) {
      case 'migrated':
        self::_trashMigratedIndiv($trashedIDs, $dest);
        break;
      case 'boeredist':
        self::_trashBOEIndiv($trashedIDs, $dest);
        break;
      default:
    }

    if ( $employers ) {
      self::_trashOrgs($trashedIDs, $source, $trashopt, $employers);
      $trashContactTypes[] = 'Organization';
    }

    foreach ( $trashContactTypes as $type ) {
      if ( $optDry && !empty($trashedIDs[$type]) ) {
        bbscript_log("debug", "The following {$type} contacts would be trashed:", $trashedIDs[$type]);
      }
      foreach ( $trashedIDs[$type] as $cid ) {
        $params = array(
          'version' => 3,
          'id' => $cid,
        );
        if ( !$optDry ) {
          civicrm_api('contact', 'delete', $params);
        }
      }
    }

    bbscript_log("info", "Completed contact migration trashing from district {$source['num']} ({$source['name']}) to district {$dest['num']} ({$dest['name']}).");

    //generate stats
    $stats = array(
      'trashing option' => $trashopt,
      'employers trashed?' => ($employers) ? 'yes' : 'no',
      'total individuals trashed' => count($trashedIDs['Individual']),
    );
    if ( $employers ) {
      $stats['total organizations trashed'] = count($trashedIDs['Organization']);
    }
    bbscript_log("info", "Trashing statistics:", $stats);
  }//trashContacts

  /*
   * helper function to retrieve migrated contacts from redistricting report contact cache table
   * we split into indivs and orgs (orgs will be processed according to employer param)
   */
  function _trashMigratedIndiv(&$trashedIDs, $dest) {
    //check for existence of redist contact cache table
    $redistTbl = "redist_report_contact_cache";
    $sql = "SHOW TABLES LIKE '{$redistTbl}'";
    if ( !CRM_Core_DAO::singleValueQuery($sql) ) {
      bbscript_log("fatal",
        "Redistricting contact cache table for this district does not exist. Exiting trashing process.");
      exit();
    }

    $sql = "
      SELECT contact_id, contact_type
      FROM {$redistTbl}
      WHERE district = {$dest['num']}
    ";
    $contacts = CRM_Core_DAO::executeQuery($sql);

    while ( $contacts->fetch() ) {
      $trashedIDs[$contacts->contact_type][] = $contacts->contact_id;
    }
    //bbscript_log("trace", '_trashOrgs $trashedIDs after _trashMigratedIndiv', $trashedIDs);
  }

  /*
   * helper function to retrieve boe indivs from db
   * we retrieve any contact with a BOE address in the destination district
   * we also retrieve employer IDs for future use
   */
  function _trashBOEIndiv(&$trashedIDs, $dest) {
    $sql = "
      SELECT c.id, c.employer_id
      FROM civicrm_address a
      JOIN civicrm_value_district_information_7 di
        ON a.id = di.entity_id
        AND di.ny_senate_district_47 = {$dest['num']}
        AND a.location_type_id = 6
      JOIN civicrm_contact c
        ON a.contact_id = c.id
        AND c.is_deleted = 0
        AND c.contact_type = 'Individual'
    ";
    $contacts = CRM_Core_DAO::executeQuery($sql);

    while ( $contacts->fetch() ) {
      $trashedIDs['Individual'][] = $contacts->id;
      if ( !empty($contacts->employer_id) ) {
        $trashedIDs['Organization'][] = $contacts->employer_id;
      }
    }
    //bbscript_log("trace", '_trashBOEIndiv $trashedIDs', $trashedIDs);
  }

  /*
   * process orgs list to determine what will be trashed
   * the action will depend on the combination of trashopt and employers opt
   * in every case, we do not trash if:
   * - other relationships exists which are not being trashed
   * - the org is in the source district
   * - the org has email, activity, note, or case records
   */
  function _trashOrgs(&$trashedIDs, $source, $trashopt, $employers) {
    //bbscript_log("trace", '_trashOrgs $trashedIDs initial', $trashedIDs['Organization']);

    //if employers = TRUE and trashopt = migrated, we need to retrieve employers and
    //add to existing list of orgs as they may not be there already
    $indivList = implode(', ', $trashedIDs['Individual']);
    if ( $employers && $trashopt == 'migrated' ) {
      $sql = "
        SELECT employer_id
        FROM civicrm_contact c
        WHERE employer_id IS NOT NULL
          AND c.id IN ({$indivList})
      ";
      $emp = CRM_Core_DAO::executeQuery($sql);
      while ( $emp->fetch() ) {
        if ( !in_array($emp->employer_id, $trashedIDs['Organization']) ) {
          $trashedIDs['Organization'][] = $emp->employer_id;
        }
      }
    }
    //bbscript_log("trace", '_trashOrgs $trashedIDs after employers', $trashedIDs['Organization']);

    //return immediately if empty
    if ( empty($trashedIDs['Organization']) )
      return;

    //now lets remove from our org ID list any orgs that meet the three exclusion criteria
    $orgList = implode(', ', $trashedIDs['Organization']);

    //query to retrieve orgs with an in-source-district address; then remove from orgs array
    $sql = "
      SELECT a.contact_id
      FROM civicrm_address a
      JOIN civicrm_value_district_information_7 di
        ON a.id = di.entity_id
        AND di.ny_senate_district_47 = {$source['num']}
      WHERE a.contact_id IN ({$orgList})
      GROUP BY a.contact_id
    ";
    $orgsInDistrict = CRM_Core_DAO::executeQuery($sql);
    while ( $orgsInDistrict->fetch() ) {
      if ( in_array($orgsInDistrict->contact_id, $trashedIDs['Organization']) ) {
        $key = array_search($orgsInDistrict->contact_id, $trashedIDs['Organization']);
        unset($trashedIDs['Organization'][$key]);
      }
    }
    //bbscript_log("trace", '_trashOrgs $trashedIDs after in district', $trashedIDs['Organization']);

    //remove orgs with meaningful data
    $valueAdded = array(
      'email' => 'contact_id',
      'activity_target' => 'target_contact_id',
      'note' => 'entity_id',
      'case_contact' => 'contact_id',
    );
    foreach ( $valueAdded as $tblSuffix => $fk ) {
      //return immediately if empty
      if ( empty($trashedIDs['Organization']) )
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
      while ( $valRecords->fetch() ) {
        if ( in_array($valRecords->contact_id, $trashedIDs['Organization']) ) {
          $key = array_search($valRecords->contact_id, $trashedIDs['Organization']);
          unset($trashedIDs['Organization'][$key]);
        }
      }
    }
    //bbscript_log("trace", '_trashOrgs $trashedIDs after value records', $trashedIDs['Organization']);

    //remove orgs with relationships
    $relPair = array(
      'contact_id_a' => 'contact_id_b',
      'contact_id_b' => 'contact_id_a',
    );
    foreach ( $relPair as $c1 => $c2 ) {
      //return immediately if empty
      if ( empty($trashedIDs['Organization']) )
        return;

      //rebuild orgList
      $orgList = implode(', ', $trashedIDs['Organization']);

      //get relationships with org where related record is not in indiv list
      $sql = "
        SELECT $c1
        FROM civicrm_relationship
        WHERE $c1 IN ({$orgList})
          AND $c2 NOT IN ({$indivList})
          AND is_active = 1
      ";
      $rels = CRM_Core_DAO::executeQuery($sql);
      while ( $rels->fetch() ) {
        if ( in_array($rels->$c1, $trashedIDs['Organization']) ) {
          $key = array_search($rels->$c1, $trashedIDs['Organization']);
          unset($trashedIDs['Organization'][$key]);
        }
      }
    }
    //bbscript_log("trace", '_trashOrgs $trashedIDs after relationships', $trashedIDs['Organization']);
  }//_trashOrgs

}//end class

//run the script if called directly
$trashData = new CRM_migrateContactsTrash();
$trashData->run();
