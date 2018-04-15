<?php

require_once 'export.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function export_civicrm_config(&$config) {
  _export_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function export_civicrm_xmlMenu(&$files) {
  _export_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function export_civicrm_install() {
  _export_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function export_civicrm_uninstall() {
  _export_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function export_civicrm_enable() {
  _export_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function export_civicrm_disable() {
  _export_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function export_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _export_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function export_civicrm_managed(&$entities) {
  _export_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function export_civicrm_caseTypes(&$caseTypes) {
  _export_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function export_civicrm_angularModules(&$angularModules) {
_export_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function export_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _export_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

function export_civicrm_buildForm( $formName, &$form ) {
  if ($formName == 'CRM_Export_Form_Select') {
    $form->addElement('checkbox', 'street_long', ts('Street Address Long Form'), NULL);

    CRM_Core_Region::instance('form-body')->add(array(
      'template' => 'CRM/NYSS/ExportSelect.tpl',
    ));
    CRM_Core_Resources::singleton()->addScriptFile('gov.nysenate.export', 'js/ExportSelect.js');
  }

  if ($formName == 'CRM_Export_Form_Map') {
    //NYSS 4426 set as hidden field so we have it in the $_POST array
    $form->addElement('hidden', 'street_long', $form->_streetLong);

    CRM_Core_Resources::singleton()->addScriptFile('gov.nysenate.export', 'js/ExportMap.js');
  }

  //TODO insert html/js

} //end buildForm

function export_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  //6248
  if ($formName == 'CRM_Export_Form_Map') {
    if ($form->_streetLong) {
      $streetAddressFound = FALSE;
      foreach ($fields['mapper'][1] as $f) {
        if ($f[1] == 'street_address') {
          $streetAddressFound = TRUE;
        }
      }
      if (!$streetAddressFound) {
        $errors['street_long'] = 'You chose to export street addresses in long form, but have not included the street address field in your export mapping. Please add that field or return to the first step and deselect the long address option.';
      }
    }
  }
}

function export_civicrm_export($exportTempTable, $headerRows, $sqlColumns, $exportMode) {
  //CRM_Core_Error::debug_var('POST', $_POST);
  //CRM_Core_Error::debug_var('headerRows', $headerRows);
  //CRM_Core_Error::debug_var('exportMode', $exportMode);
  //CRM_Core_Error::debug_var('sqlColumns', $sqlColumns);
  //CRM_Core_Error::debug_var('$exportTempTable', $exportTempTable);

  //field exclusions; only implement for primary export option
  if ($_POST['exportOption'] == 1) {
    $headerRemove = array(
      'IM Service Provider',
      'Group(s)',
      'Tag(s)',
      'Note(s)',
    );
    foreach ($headerRows as $key => $headerRow) {
      if (in_array( $headerRow, $headerRemove) ) {
        unset( $headerRows[$key] );
      }
    }

    $sqlRemove = array(
      'provider_id',
      'groups',
      'tags',
      'notes',
      'case_activity_subject',//TODO should really just address in headers
    );
    foreach ($sqlRemove as $sqlField) {
      unset($sqlColumns[$sqlField]);
    }
  } //end primary export exclusions

  //3665 mailing exclusions option
  if ($_POST['postal_mailing_export']['postal_mailing_export'] == 1 &&
    in_array('Last Name', $headerRows) &&
    in_array('Contact Type', $headerRows)
  ) {
    // exclude contacts where last_name is empty
    $query = "
      DELETE FROM $exportTempTable
      WHERE contact_type = 'Individual'
        AND (last_name = '' OR last_name IS NULL)
    ";
    CRM_Core_DAO::executeQuery($query);
  }

  //4403 exclude Mailing Exclusions group
  if ($_POST['postal_mailing_export']['postal_mailing_export'] == 1) {
    $gid = CRM_Core_DAO::singleValueQuery("
      SELECT id
      FROM civicrm_group
      WHERE name LIKE 'Mailing_Exclusions';
    ");

    if ($gid) { //continue if group found
      $query = "
        DELETE exp
        FROM $exportTempTable AS exp
        LEFT JOIN civicrm_group_contact gc 
          ON gc.contact_id = exp.civicrm_primary_id 
          AND gc.status = 'Added'
        WHERE gc.group_id = $gid;
      ";
      CRM_Core_DAO::executeQuery( $query );
    }
  }

  //4766 exclude do not mail/do not trade
  if ($_POST['postal_mailing_export']['postal_mailing_export'] == 1) {
    $query = "
      DELETE exp
      FROM $exportTempTable AS exp
      JOIN civicrm_contact c 
        ON c.id = exp.civicrm_primary_id
      WHERE c.do_not_trade = 1
        OR c.do_not_mail = 1;
    ";
    $delDN = CRM_Core_DAO::executeQuery( $query );
    //CRM_Core_Error::debug('delDN',$delDN);
  }

  //4426 change street suffix to long form
  if ($_POST['street_long'] == 1) {
    //construct pattern array from address_abbreviations
    $patReplace = array();

    $sql = "
      SELECT DISTINCT normalized, long_form
      FROM address_abbreviations;
    ";
    $dao = CRM_Core_DAO::executeQuery($sql);

    while($dao->fetch()) {
      $patReplace[$dao->normalized] = $dao->long_form;
    }
    $dao->free();
    //CRM_Core_Error::debug('patReplace', $patReplace);exit();

    //get street_address for all records from the temp table
    $sql = "
      SELECT civicrm_primary_id, street_address
      FROM $exportTempTable
      WHERE street_address IS NOT NULL
        AND street_address != '';
    ";
    $dao = CRM_Core_DAO::executeQuery($sql);

    while($dao->fetch()) {
      //split street_address into array of elements in reverse
      $streetSplit = array_reverse(explode(' ', $dao->street_address));

      //we have to handle the cycles separately so that we do a complete search for long form before looking for abbrev
      foreach ($streetSplit as $chunk) {
        $chunk = _cleanStr($chunk);
        //if the long form is present continue with next record
        if (in_array($chunk, $patReplace)) {
          continue;
        }
      }

      foreach ($streetSplit as $chunk) {
        $chunk = _cleanStr($chunk);
        //see if the short form is present and conduct the replacement
        if (array_key_exists($chunk, $patReplace)) {
          $longForm = ucfirst($patReplace[$chunk]);
          $chunk = "(\b)$chunk(\b)";
          $newAddr = addslashes(preg_replace("~(?i)$chunk(?!.*?$chunk)~", "$1{$longForm}$2", $dao->street_address));
          //CRM_Core_Error::debug('newAddr',$newAddr);exit();

          $sql = "
            UPDATE $exportTempTable
            SET street_address = '$newAddr'
            WHERE civicrm_primary_id = $dao->civicrm_primary_id;
          ";
          $upd = CRM_Core_DAO::executeQuery($sql);
          //CRM_Core_Error::debug('sqlupd',$sqlupd);exit();

          //after handling the update, advance to next record
          continue 2;
        }
      }
    }
    $dao->free();

  } //end street_long

  //CRM_Core_Error::debug('exportTempTable',$exportTempTable);exit();

  //if no records in the table, return to search
  //ideally we return to the existing search, but we don't seem to have the qfKey at this point
  $sql = "SELECT count(*) FROM $exportTempTable;";
  if (CRM_Core_DAO::singleValueQuery($sql) == 0) {
    $status = "There were no records to export. Please run your search and export again.";
    $session = CRM_Core_Session::singleton();
    $session->setStatus($status);

    $currentPath = CRM_Utils_System::currentPath();
    $urlParams = NULL;
    $qfKey = CRM_Utils_Request::retrieve('qfKey', 'String');
    if (CRM_Utils_Rule::qfKey($qfKey)) $urlParams = "&qfKey=$qfKey";

    CRM_Utils_System::redirect(CRM_Utils_System::url($currentPath, $urlParams));
  }

  //3665 code copied from CRM_Export_BAO_Export::writeCSVFromTable, just to modify the order clause
  $writeHeader = true;
  $offset = 0;
  $limit = 100;

  //only apply special sort if using primary export, as we know the necessary fields will exist
  if ($_POST['exportOption'] == 1) {
    $query = "
      SELECT *
      FROM $exportTempTable
      ORDER BY
        CASE WHEN $exportTempTable.contact_type = 'Individual' THEN 1
             WHEN $exportTempTable.contact_type = 'Household' THEN 2 ELSE 3 END,
        CASE WHEN $exportTempTable.gender_id = 'Male' THEN 1
             WHEN $exportTempTable.gender_id = 'Female' THEN 2
             WHEN $exportTempTable.gender_id = 'Other' THEN 3 ELSE 999 END,
        IFNULL($exportTempTable.birth_date, '9999-01-01')
    ";
  }
  else {
    $query = "
      SELECT *
      FROM $exportTempTable
    ";
  }

  //5703 remove some activity fields
  if ($exportMode == CRM_Export_Form_Select::ACTIVITY_EXPORT &&
    $_POST['exportOption'] == 1
  ) {
    $rm = array(
      'source_record_id',
      'activity_is_test',
      'activity_campaign_id',
      'activity_campaign',
      'activity_engagement_level',
      'Test',
      'Campaign ID',
      'Campaign Title',
      'Engagement Index',
    );
    foreach ($headerRows as $key => $headerRow) {
      if (in_array( $headerRow, $rm)) {
        unset($headerRows[$key]);
      }
    }
    foreach ($rm as $rmf) {
      unset($sqlColumns[$rmf]);
    }
  }//end activity mods

  while (1) {
    $limitQuery = $query . "
      LIMIT $offset, $limit
    ";
    $dao = CRM_Core_DAO::executeQuery($limitQuery);

    if ($dao->N <= 0) {
      break;
    }

    $componentDetails = array( );
    while ($dao->fetch()) {
      $row = array();

      foreach ($sqlColumns as $column => $dontCare) {
        //9018 apply activity details cleanup
        if (in_array($column, array('activity_details', 'case_activity_details'))) {
          $row[$column] = _cleanHTML($dao->$column);
        }
        else {
          $row[$column] = $dao->$column;
        }
      }

      $componentDetails[] = $row;
    }
    CRM_Core_Report_Excel::writeCSVFile(
      CRM_Export_BAO_Export::getExportFileName('csv', $exportMode),
      $headerRows, $componentDetails, null, $writeHeader );

    $writeHeader = false;
    $offset += $limit;
  }

  CRM_Utils_System::civiExit();

  //debug
  /*CRM_Core_Error::debug('tt', $exportTempTable);
  CRM_Core_Error::debug('hr', $headerRows);
  CRM_Core_Error::debug('sc', $sqlColumns);
  CRM_Core_Error::debug('em', $exportMode);
  exit();*/

}

//helper to strip spaces and punctuation so we normalize comparison
function _cleanStr($string) {
  $string = preg_replace('/[\W]+/', '', $string);
  $string = strtolower($string);
  return $string;
}//_cleanStr

/*
 * convert html to text
 * using native function but placing in wrapper as we may/may not have additional
 * processing we want to perform
 */
function _cleanHTML($str) {
  //CRM_Core_Error::debug_var('_cleanHTML $str (before)', $str);

  $str = CRM_Utils_String::htmlToText($str);

  //CRM_Core_Error::debug_var('_cleanHTML $str (after)', $str);
  return $str;
}//_cleanStr

