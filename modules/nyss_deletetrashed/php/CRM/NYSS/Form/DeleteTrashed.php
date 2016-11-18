<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

define('DELETE_BATCH', 1000);
define('TEST_COUNT', 0);

/**
 * This class generates form components for DedupeRules
 *
 */
class CRM_NYSS_Form_DeleteTrashed extends CRM_Core_Form
{

  /**
   * Function to pre processing
   *
   * @return None
   * @access public
   */
  function preProcess() {
  }

  /**
   * Function to build the form
   *
   * @return None
   * @access public
   */
  public
  function buildQuickForm() {
    $sql = "
      SELECT count(id)
      FROM civicrm_contact
      WHERE is_deleted = 1;
    ";
    $count = CRM_Core_DAO::singleValueQuery($sql);

    $this->assign('trashCount', $count);
  }

  /*
   * Function to process all trashed contacts (permanently delete)
   */
  static
  function processTrashed() {
    ini_set('memory_limit', '8000M');
    ini_set('max_execution_time', 0);

    $sTime = microtime(TRUE);

    //get configs
    $bbcfg = get_bluebird_instance_config();
    $config = CRM_Core_Config::singleton();

    //get all trashed contact IDs
    $sql = "
      SELECT id
      FROM civicrm_contact
      WHERE is_deleted = 1;
    ";
    $trashed = CRM_Core_DAO::executeQuery($sql);

    $contactIDs = $batchIDs = array();

    // start a new transaction
    $transaction = new CRM_Core_Transaction();

    while ($trashed->fetch()) {
      $contactIDs[] = $trashed->id;
      $batchIDs[] = $trashed->id;

      // do activity cleanup, CRM-5604
      CRM_Activity_BAO_Activity::cleanupActivity($trashed->id);

      // delete all notes related to contact
      CRM_Core_BAO_Note::cleanContactNotes($trashed->id);

      // process batch contacts
      if (count($contactIDs) % DELETE_BATCH == 0) {
        $ids = implode(',', $batchIDs);

        //delete log records in bulk (batch)
        $sql = "DELETE civicrm_log
          FROM civicrm_log
          JOIN civicrm_contact
            ON civicrm_log.entity_id = civicrm_contact.id
            AND entity_table = 'civicrm_contact'
            AND civicrm_contact.is_deleted = 1
            AND civicrm_contact.id IN ({$ids})
        ";
        CRM_Core_DAO::executeQuery($sql);

        //now delete contact records (batch)
        $sql = "
          DELETE FROM civicrm_contact
          WHERE is_deleted = 1
            AND id IN ({$ids})
        ";
        CRM_Core_DAO::executeQuery($sql);

        $batchIDs = array();
        $countStatus = count($contactIDs);

        $output = "deleting ".DELETE_BATCH." contacts. {$countStatus} total contacts deleted...<br />";
        echo $output;

        unset($ids);

        //$mem = memory_get_usage(TRUE);
        //CRM_Core_Error::debug_var('mem', $mem);

        $transaction->commit();
        $transaction = new CRM_Core_Transaction();
      }

      if (!empty(TEST_COUNT) && count($contactIDs) > TEST_COUNT) {
        exit();
      }
    }

    //delete log records in bulk
    $sql = "
      DELETE civicrm_log
      FROM civicrm_log
      JOIN civicrm_contact
        ON civicrm_log.entity_id = civicrm_contact.id
        AND entity_table = 'civicrm_contact'
        AND civicrm_contact.is_deleted = 1
    ";
    CRM_Core_DAO::executeQuery($sql);

    //now delete contact records
    $sql = "DELETE FROM civicrm_contact
      WHERE is_deleted = 1
    ";
    CRM_Core_DAO::executeQuery($sql);

    $transaction->commit();

    $eTime = microtime(TRUE);
    $diffTime = ($eTime - $sTime)/60;
    //CRM_Core_Error::debug_var('diffTime', $diffTime);

    $contactCount = count($contactIDs);

    $batchFinalCount = count($batchIDs);
    echo "deleting {$batchFinalCount} contacts. {$contactCount} total contacts deleted...<br />";

    //return output
    $output = "<br />{$contactCount} trashed contact records were permanently deleted.";
    echo $output;

    CRM_Utils_System::civiExit();
  }
}
