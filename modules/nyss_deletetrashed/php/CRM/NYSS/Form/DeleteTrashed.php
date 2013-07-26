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

require_once 'CRM/Core/Form.php';

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

    $contactIDs = array();

    // start a new transaction
    $transaction = new CRM_Core_Transaction();

    while ( $trashed->fetch() ) {
      $contactIDs[] = $trashed->id;

      // do activity cleanup, CRM-5604
      CRM_Activity_BAO_Activity::cleanupActivity($trashed->id);

      // delete all notes related to contact
      CRM_Core_BAO_Note::cleanContactNotes($trashed->id);
    }

    $ids = implode(',', $contactIDs);

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
    $sql = "
      DELETE FROM civicrm_contact
      WHERE is_deleted = 1
    ";
    CRM_Core_DAO::executeQuery($sql);

    $transaction->commit();

    $eTime = microtime(TRUE);
    $diffTime = ($eTime - $sTime)/60;

    $contactCount = count($contactIDs);

    //return output
    $output = "{$contactCount} trashed contact records were permanently deleted.";
    echo $output;

    CRM_Utils_System::civiExit();
  }
}
