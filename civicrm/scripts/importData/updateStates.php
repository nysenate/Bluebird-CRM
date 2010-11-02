function updateStates($importSet, $importDir, $startID, $sourceDesc)
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
  cLog(0,'info',"starting contactID will be ".($contactID+1));

  $dao = &CRM_Core_DAO::executeQuery("SELECT max(id) as maxid from civicrm_address;", CRM_Core_DAO::$_nullArray);
  $dao->fetch();
  $addressID = $dao->maxid;
  cLog(0,'info',"starting addressID will be ".($addressID+1));

  $dao = &CRM_Core_DAO::executeQuery("SELECT max(id) as maxid from civicrm_activity;", CRM_Core_DAO::$_nullArray);
  $dao->fetch();
  $activityID = $dao->maxid;
  cLog(0,'info',"starting activityID will be ".($activityID+1));

  // Array that maps tag name to tagID.
  $aTagsByName = array();
  // Array that maps tagID to its parent tagID.
  $aTagsByID = array();
  // Array that stores hierarchy tags that could not be mapped.
  $aUnsavedTags = array();

  // load all tags, and get max tag ID
  $tagID = getAllTags($aTagsByName, $aTagsByID);
  cLog(0,'info',"starting tagID will be ".($tagID+1));

  $cCounter = 0;

  $aRels = array();
  $aOrgKey = array();

  while ($ctRow) {

    if (RAYDEBUG) markTime('getLine');

    // check for an OMIS extended record
    $omis_ext = (count($ctRow) > 45) ? true : false;

    $ctRow['KEY'] = intval($ctRow['KEY']);
    $importID = $ctRow['KEY'];

	//since we might have org records, do the update for both external source IDs, the personal 'extid' addr and the 'extid-1' org addr.

    $dao = &CRM_Core_DAO::executeQuery('UPDATE civicrm_address set state_province_id = '. $aStates[$ctRow['ADDR_WORK_STATE']].' WHERE contact_id=(select id from civicrm_contact where external_identifier=\''.($sourceDesc.$importID).'\';', CRM_Core_DAO::$_nullArray);
    $dao = &CRM_Core_DAO::executeQuery('UPDATE civicrm_address set state_province_id = '. $aStates[$ctRow['ADDR_WORK_STATE']].' WHERE contact_id=(select id from civicrm_contact where external_identifier=\''.($sourceDesc.$importID.'-1').'\';', CRM_Core_DAO::$_nullArray);

    $ctRow = getLineAsAssocArray($infiles['contacts'], DELIM, $omis_ct_fields);
  }
}
