<?php

require_once 'merge.civix.php';
use CRM_NYSS_Merge_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function merge_civicrm_config(&$config) {
  _merge_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function merge_civicrm_xmlMenu(&$files) {
  _merge_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function merge_civicrm_install() {
  _merge_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function merge_civicrm_postInstall() {
  _merge_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function merge_civicrm_uninstall() {
  _merge_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function merge_civicrm_enable() {
  _merge_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function merge_civicrm_disable() {
  _merge_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function merge_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _merge_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function merge_civicrm_managed(&$entities) {
  _merge_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function merge_civicrm_caseTypes(&$caseTypes) {
  _merge_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function merge_civicrm_angularModules(&$angularModules) {
  _merge_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function merge_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _merge_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function merge_civicrm_entityTypes(&$entityTypes) {
  _merge_civix_civicrm_entityTypes($entityTypes);
}

define('MERGE_LOG_DEBUG', 1);

function merge_civicrm_merge($type, &$data, $mainId = NULL, $otherId = NULL, $tables = NULL) {
  //_merge_mD('merge_civicrm_merge $type', $type);
  //_merge_mD('merge_civicrm_merge $data', $data);
  //_merge_mD('merge_civicrm_merge $mainId', $mainId);
  //_merge_mD('merge_civicrm_merge $otherId', $otherId);
  //_merge_mD('merge_civicrm_merge $tables', $tables);

  switch ($type) {
    case 'batch':
      if (!empty($data['fields_in_conflict'])) {
        _merge_resolveConflicts($data, $mainId, $otherId);
      }
      _merge_mD('post-processed batch data', $data);
      break;

    case 'sqls':
      //log the merge against the retained record
      _merge_logMerge($mainId, $otherId);
      break;
      
    case 'form':
      if (!empty($data['migration_info']['move_custom_64'])) {
        if ($mergedPrivacyNote = _merge_fixPrivacyNote($mainId, $otherId)) {
          $data['migration_info']['move_custom_64'] = $mergedPrivacyNote;
        }
      }
      break;

    default:
      break;
  }
  return;
}

function merge_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Contact_Form_Merge') {
    CRM_Core_Resources::singleton()->addScriptFile(CRM_NYSS_Merge_ExtensionUtil::LONG_NAME, 'js/Merge.js');
  }
}

/**
 * @param $data
 * @param $mainId
 * @param $otherId
 *
 * helper to resolve some conflicts when in batch mode
 *
 * $data['fields_in_conflict'] = list of fields in conflict
 * $data['migration_info']['rows'] = list of all fields with main/other vals
 *
 * options for altering data:
 * 1. set $conflicts = NEW VALUE
 * 2. unset($conflicts[field]) = remove conflict (proceed without concern)
 * 3. set $rows[field]
 * 4. unset($conflicts[field]) && $data[migration_info][main_details] = NEW VAL
 *    - remove conflict and fix value
 */
function _merge_resolveConflicts(&$data, $mainId, $otherId) {
  _merge_mD('data', $data);

  $conflicts =& $data['fields_in_conflict'];
  $rows =& $data['migration_info']['rows'];

  //if org names sans space/punctation are equal, merge
  if (array_key_exists('move_organization_name', $conflicts)) {
    if (_merge_cleanVal($rows['move_organization_name']['main']) ==
      _merge_cleanVal($rows['move_organization_name']['other'])
    ) {
      //unset from the conflicts array
      //we don't know which value to retain, so we default to main
      unset($conflicts['move_organization_name']);
    }
  }

  //if job_title sans space/punctation are equal, merge
  if (array_key_exists('move_job_title', $conflicts)) {
    if (_merge_cleanVal($rows['move_job_title']['main']) ==
      _merge_cleanVal($rows['move_job_title']['other'])
    ) {
      //unset from the conflicts array
      //we don't know which value to retain, so we default to main
      unset($conflicts['move_job_title']);
    }
  }

  //if external ids in conflict, just keep main value
  if (array_key_exists('move_external_identifier', $conflicts)) {
    unset($conflicts['move_external_identifier']);
  }

  //if "do not...", uncheck value for retention and proceed with merge
  if (array_key_exists('move_do_not_mail', $conflicts)) {
    $conflicts['move_do_not_mail'] = 0;
  }
  if (array_key_exists('move_do_not_email', $conflicts)) {
    $conflicts['move_do_not_email'] = 0;
  }
  if (array_key_exists('move_do_not_trade', $conflicts)) {
    $conflicts['move_do_not_trade'] = 0;
  }
  if (array_key_exists('move_do_not_phone', $conflicts)) {
    $conflicts['move_do_not_phone'] = 0;
  }
  if (array_key_exists('move_do_not_sms', $conflicts)) {
    $conflicts['move_do_not_sms'] = 0;
  }

  //if opt-out, set to yes
  if (array_key_exists('move_is_opt_out', $conflicts)) {
    $conflicts['move_is_opt_out'] = 1;
  }

  //if greeting in conflict and one is custom, retain formula option
  if (array_key_exists('move_addressee', $conflicts)) {
    _merge_fixGreeting('move_addressee', $rows, $conflicts);
  }
  if (array_key_exists('move_addressee_id', $conflicts)) {
    _merge_fixGreeting('move_addressee_id', $rows, $conflicts);
  }
  if (array_key_exists('move_email_greeting', $conflicts)) {
    _merge_fixGreeting('move_email_greeting', $rows, $conflicts);
  }
  if (array_key_exists('move_email_greeting_id', $conflicts)) {
    _merge_fixGreeting('move_email_greeting_id', $rows, $conflicts);
  }
  if (array_key_exists('move_postal_greeting', $conflicts)) {
    _merge_fixGreeting('move_postal_greeting', $rows, $conflicts);
  }
  if (array_key_exists('move_postal_greeting_id', $conflicts)) {
    _merge_fixGreeting('move_postal_greeting_id', $rows, $conflicts);
  }
  if (array_key_exists('move_addressee_custom', $conflicts)) {
    _merge_fixGreeting('move_addressee_custom', $rows, $conflicts);
  }

  //if case insensitive fname/mname/lname match or value sans space matches,
  //try to find mixed case and retain
  if (array_key_exists('move_last_name', $conflicts)) {
    _merge_fixName('move_last_name', $rows, $conflicts);
  }
  if (array_key_exists('move_first_name', $conflicts)) {
    _merge_fixName('move_first_name', $rows, $conflicts);
  }
  if (array_key_exists('move_middle_name', $conflicts)) {
    _merge_fixName('move_middle_name', $rows, $conflicts);
  }

  //if one record type is BOE, retain above other values
  if (array_key_exists('move_custom_61', $conflicts)) {
    _merge_fixRT($data, $rows, $conflicts);
  }

  //11494 select later value for BOE date of registration
  if (array_key_exists('move_custom_24', $conflicts)) {
    if (strtotime($rows['move_custom_24']['other']) > strtotime($rows['move_custom_24']['main'])) {
      $conflicts['move_custom_24'] = $rows['move_custom_24']['other'];
    }
    else {
      $conflicts['move_custom_24'] = $rows['move_custom_24']['main'];
    }
  }

  //11494 if move_location_address_0 sans space/punctation are equal, merge
  if (array_key_exists('move_location_address_0', $conflicts)) {
    _merge_mD('move_location_address_0 rows', $rows);
    if (_merge_cleanVal($rows['move_location_address_0']['main']) ==
      _merge_cleanVal($rows['move_location_address_0']['other'])
    ) {
      unset($conflicts['move_location_address_0']);
    }
    else {
      //14495 postal +4
      //compare postal +4
      $mainAddr = &$data['migration_info']['main_details']['location_blocks']['address'][0];
      $otherAddr = $data['migration_info']['other_details']['location_blocks']['address'][0];
      if ((!empty($mainAddr['postal_code_suffix']) || !empty($otherAddr['postal_code_suffix'])) &&
        $mainAddr['postal_code_suffix'] != $otherAddr['postal_code_suffix']
      ) {
        //at this point we know that the postal code +4 is in conflict, but don't know if there are other
        //points of conflict with the address blocks; so let's recreate the address string and compare them sans +4
        $mainAddrStr = _merge_cleanVal($mainAddr['street_address'].$mainAddr['supplemental_address_1'].$mainAddr['city'].$mainAddr['state_province_id'].$mainAddr['postal_code']);
        $otherAddrStr = _merge_cleanVal($otherAddr['street_address'].$otherAddr['supplemental_address_1'].$otherAddr['city'].$otherAddr['state_province_id'].$otherAddr['postal_code']);

        //_merge_mD('move_location_address_0 $mainAddrStr', $mainAddrStr);
        //_merge_mD('move_location_address_0 $otherAddrStr', $otherAddrStr);

        if ($mainAddrStr == $otherAddrStr) {
          //we now know the +4 was the only point of conflict
          if (!empty($otherAddr['postal_code_suffix'])) {
            $mainAddr['postal_code_suffix'] = $otherAddr['postal_code_suffix'];
          }

          unset($conflicts['move_location_address_0']);
        }
      }
    }
  }

  //if contact source custom field conflicts & one or other is BOE, retain that
  if (array_key_exists('move_custom_60', $conflicts) &&
    ($rows['move_custom_60']['main'] == 'BOE' || $rows['move_custom_60']['other'] == 'BOE')
  ) {
    $rows['move_custom_60']['main'] = $rows['move_custom_60']['other'] =
    $conflicts['move_custom_60'] = 'BOE';
  }

  //phone blocks
  _merge_fixLocationBlocks($data, 'phone', $rows, $conflicts);

  //email blocks
  _merge_fixLocationBlocks($data, 'email', $rows, $conflicts);

  //TODO IM?

  //if web profile email and date both conflict, set to newer value
  if (array_key_exists('move_custom_72', $conflicts) && array_key_exists('move_custom_79', $conflicts)) {
    if (strtotime($rows['move_custom_79']['other']) > strtotime($rows['move_custom_79']['main'])) {
      $conflicts['move_custom_79'] = $rows['move_custom_79']['other'];
      $conflicts['move_custom_72'] = $rows['move_custom_72']['other'];
    }
    else {
      $conflicts['move_custom_79'] = $rows['move_custom_79']['main'];
      $conflicts['move_custom_72'] = $rows['move_custom_72']['other'];
    }
  }

  //14495 deceased
  if (array_key_exists('move_is_deceased', $conflicts)) {
    $conflicts['move_is_deceased'] = 1;
  }

  //12878 combine privacy note values
  if (array_key_exists('move_custom_64', $conflicts)) {
    $conflicts['move_custom_64'] = trim(trim($rows['move_custom_64']['main'].'; '.$rows['move_custom_64']['other']), ';');
  }

  //let's log any remaining conflicts for later review
  if (in_array(null, $conflicts, true)) {
    $conflictDetails = array();
    foreach ($conflicts as $fld => $value) {
      if ($value === null) {
        $conflictDetails[$fld] = array(
          'mainId' => $mainId,
          'mainId value' => $rows[$fld]['main'],
          'otherId' => $otherId,
          'otherId value' => $rows[$fld]['other'],
        );
      }
      else {
        $conflictDetails[$fld] = "Mergeable to: $value";
      }
    }
    _merge_mD('merge conflict details', $conflictDetails);
  }

  return;
} //_merge_resolveConflicts()


//helper to strip spaces and punctuation so we normalize comparison
function _merge_cleanVal($string) {
  //14495 - convert # to Apt
  $string = str_replace('#', 'apt', $string);

  return strtolower(preg_replace('/[\W]+/', '', $string));
}


//helper to work out greeting field custom vs formula
function _merge_fixGreeting($gType, &$rows, &$conflicts) {
  $gMain =& $rows[$gType]['main'];
  $gOther =& $rows[$gType]['other'];

  //check if casing/punctuation is only difference
  if (_merge_cleanVal($gMain) == _merge_cleanVal($gOther)) {
    //unset from the conflicts array
    unset($conflicts[$gType]);
    return;
  }

  //perform fixup if one is customized and the other is not
  if (_merge_merge_isCustom($gMain) && !_merge_merge_isCustom($gOther)) {
    $conflicts[$gType] = $gOther;
    return;
  }
  elseif (!_merge_merge_isCustom($gMain) && _merge_merge_isCustom($gOther)) {
    $conflicts[$gType] = $gMain;
    return;
  }

  //we know at this point that the values conflict and one is not a formula
  //the other value could be a Dear Friend(s) selection
  //we will retain the custom value as it's more likely to be a meaningful value
  if ($gMain == 'Customized') {
    $conflicts[$gType] = $gMain;
    $conflicts[$gType.'_custom'] = $rows[$gType.'_custom']['main'];
    return;
  }
  elseif ($gOther == 'Customized') {
    $conflicts[$gType] = $gOther;
    $conflicts[$gType.'_custom'] = $rows[$gType.'_custom']['other'];
    return;
  }

  //another scenario is where values are customized and variance is
  //due to other field values used to construct a cached value
  //consider middle initial:
  if (isset($rows['move_middle_name']) &&
    strpos($gType, '_custom') &&
    $rows['move_middle_name']['main'] != $rows['move_middle_name']['other']
  ) {

    if (strlen($rows[$gType]['main']) > strlen($rows[$gType]['other'])) {
      $conflicts[$gType] = $rows[$gType]['main'];
    }
    else {
      $conflicts[$gType] = $rows[$gType]['other'];
    }

    _merge_mD("custom greeting conflicted. retained: ", $rows[$gType]['main']);
    return;
  }

  //if neither are customized, conflict with formulas; use default
  if ($gMain != 'Customized' && $gOther != 'Customized') {
    $contactType = $rows['move_contact_type']['main'];
    $greetingType = str_replace('move_', '', str_replace('_id', '', $gType));
    $defaultGreetingId = CRM_Contact_BAO_Contact_Utils::defaultGreeting($contactType, $greetingType);
    $filter = [
      'contact_type' => $contactType,
      'greeting_type' => $greetingType,
    ];
    $allGreetings = CRM_Core_PseudoConstant::greeting($filter);
    $defaultGreetingString = $greetingString = CRM_Utils_Array::value($defaultGreetingId, $allGreetings);
    _merge_mD('setting default greeting string', $defaultGreetingString);

    $conflicts[$gType] = $defaultGreetingString;
    return;
  }

  return;
} //_merge_fixGreeting()


//helper to clean up and compare name fields
//our algorithms will give preference to the main value
function _merge_fixName($name, &$rows, &$conflicts) {
  $nMain = $rows[$name]['main'];
  $nOther = $rows[$name]['other'];
  $mWeight = $oWeight = 0;

  //first find if we have a cleaned match, else return
  if (_merge_cleanVal($nMain) != _merge_cleanVal($nOther)) {
    return;
  }

  //determine if mixed case +1
  if ($nMain != strtolower($nMain) && $nMain != strtoupper($nMain)) {
    $mWeight++;
    _merge_mD('nMain is mixed case', $nMain);
  }
  if ($nOther != strtolower($nOther) && $nOther != strtoupper($nOther)) {
    $oWeight++;
    _merge_mD('nOther is mixed case', $nOther);
  }

  //determine if value has spaces -1
  if (!preg_match("/\s/", $nMain)) {
    $mWeight++;
    _merge_mD('nMain has no spaces', $nMain);
  }
  if (!preg_match("/\s/", $nOther)) {
    $oWeight++;
    _merge_mD('nOther has no spaces', $nOther);
  }

  //determine if value has punctuation +1
  if (preg_match("/\p{P}/", $nMain)) {
    $mWeight++;
    _merge_mD('nMain has punctuation', $nMain);
  }
  if (preg_match("/\p{P}/", $nOther)) {
    $oWeight++;
    _merge_mD('nOther has punctuation', $nOther);
  }

  //take value with greater weight and set other to match
  if ($oWeight > $mWeight) {
    $nMain = $nOther;
  }

  //update element in conflict array
  _merge_mD("$name value retained:", $nMain);
  $conflicts[$name] = $nMain;

  return;
} //_merge_fixName()


//if either of the RT values is Board of Election, set and merge
function _merge_fixRT(&$data, &$rows, &$conflicts) {
  $rtMain = $rows['move_custom_61']['main'];
  $rtOther = $rows['move_custom_61']['other'];
  _merge_mD("rtOther", $rtOther);
  _merge_mD("rtMain", $rtMain);

  if ($rtMain == 'Board of Election' || $rtOther == 'Board of Election') {
    $conflicts['move_custom_61'] = 1; //value for BOE record type option
  }
  else {
    //compare modified date to determine which is more recent
    try {
      $mainModified = civicrm_api3('contact', 'getvalue', [
        'id' => $data['migration_info']['main_details']['contact_id'],
        'return' => 'modified_date',
      ]);
      $otherModified = civicrm_api3('contact', 'getvalue', [
        'id' => $data['migration_info']['other_details']['contact_id'],
        'return' => 'modified_date',
      ]);
      $recordTypeOpts = civicrm_api3('contact', 'getoptions',
        array('field' => 'custom_61'));
      _merge_mD("mainModified: $mainModified", strtotime($mainModified));
      _merge_mD("otherModified: $otherModified", strtotime($otherModified));
      _merge_mD("recordTypeOpts", $recordTypeOpts);

      if (strtotime($otherModified) > strtotime($mainModified)) {
        $conflicts['move_custom_61'] = array_search($rtOther, $recordTypeOpts['values']);
      }
      else {
        $conflicts['move_custom_61'] = array_search($rtMain, $recordTypeOpts['values']);
      }
    }
    catch (CiviCRM_API3_Exception $e) {}
  }
} //_merge_fixRT()


/**
 * @param $data
 * @param $type
 *
 * helper to resolve phone/email blocks
 */
function _merge_fixLocationBlocks(&$data, $type, &$rows, &$conflicts) {
  for ($blk_idx = 0; $blk_idx <= 3; $blk_idx++) {
    $block = "move_location_{$type}_{$blk_idx}"; // eg. move_location_phone_0
    if (array_key_exists($block, $conflicts)) {
      unset($conflicts[$block]);
      if (_merge_cleanVal($rows[$block]['main']) != _merge_cleanVal($rows[$block]['other'])) {
        //if not equal, append Other phone block to Main
        //add other block to main
        $data['migration_info']['main_details']['location_blocks'][$type][] =
          $data['migration_info']['other_details']['location_blocks'][$type][$blk_idx];
      }
    }
  }
} // _merge_fixLocationBlocks()

//determine if we have a custom greeting value
function _merge_merge_isCustom($value) {
  //if the value is 'Customized' or we don't have braces, then custom
  if ($value == 'Customized' ||
    !preg_match("/[\{\}]/", $value)) {
    return true;
  }
  else {
    return false;
  }
} // _merge_merge_isCustom()

function _merge_fixPrivacyNote($mainId, $otherId) {
  try {
    $pnA = civicrm_api3('contact', 'getvalue', [
      'id' => $mainId,
      'return' => 'custom_64',
    ]);
    
    $pnB = civicrm_api3('contact', 'getvalue', [
      'id' => $otherId,
      'return' => 'custom_64',
    ]);
    
    if (!empty($pnA) && !empty($pnB)) {
      return "{$pnA}; {$pnB}";
    }
  }
  catch (CiviCRM_API3_Exception $e) {
    _merge_mD('error retrieving privacy note: $e', $e);
  }
  
  return NULL;
}

function _merge_logMerge($mainId, $otherId) {
  $session = CRM_Core_Session::singleton();
  $cid = $session->get('userID');
  $date = date('YmdHis');

  //handle with straight sql as it's faster than using the BAO
  $sql = "
    INSERT INTO civicrm_log (entity_table, entity_id, data, modified_id, modified_date) 
    VALUES
    ('civicrm_contact', $mainId, 'Contact $otherId was merged into this contact ($mainId).', $cid, $date)
  ";
  CRM_Core_DAO::executeQuery($sql);

  return;
} //_merge_logMerge()


//allows us to condition all logging based on a constant
function _merge_mD($msg, $var, $level = 1) {
  if ($level <= MERGE_LOG_DEBUG) {
    CRM_Core_Error::debug_var($msg, $var, true, true, 'merge');
  }
  return;
} //_merge_mD()
