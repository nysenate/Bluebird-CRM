<?php

define('DELETE_BATCH', 1000);
define('TEST_COUNT', 0);

class CRM_NYSS_Contact_BAO {
  /**
   * Process all trashed contacts (permanently delete)
   */
  static function processTrashed($params = []) {
    ini_set('memory_limit', '8000M');
    ini_set('max_execution_time', 0);

    $sTime = microtime(TRUE);

    $modifiedDateSql = (!empty($params['modified_date'])) ?
      "AND modified_date <= '".date('Y-m-d H:i:s', strtotime($params['modified_date']))."'" : '';

    //get all trashed contact IDs
    $sql = "
      SELECT id
      FROM civicrm_contact
      WHERE is_deleted = 1
        {$modifiedDateSql}
    ";
    $trashed = CRM_Core_DAO::executeQuery($sql);

    if ($params['dryrun'] ?? FALSE) {
      return $trashed->N;
    }

    $lineBreak = (!empty($params['return'])) ? "\n" : '<br />';

    $contactIDs = $batchIDs = [];

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

        $batchIDs = [];
        $countStatus = count($contactIDs);

        $output = "deleting ".DELETE_BATCH." contacts. {$countStatus} total contacts deleted...{$lineBreak}";
        echo $output;

        unset($ids);

        //$mem = memory_get_usage(TRUE);
        //CRM_Core_Error::debug_var('mem', $mem);

        $transaction->commit();
        $transaction = new CRM_Core_Transaction();
      }

      if (!empty(TEST_COUNT) && count($contactIDs) > TEST_COUNT) {
        break;
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
    $sql = "
      DELETE FROM civicrm_contact
      WHERE is_deleted = 1
    ";
    CRM_Core_DAO::executeQuery($sql);

    $transaction->commit();

    $eTime = microtime(TRUE);
    $diffTime = ($eTime - $sTime)/60;
    //CRM_Core_Error::debug_var('diffTime', $diffTime);

    $contactCount = count($contactIDs);

    $batchFinalCount = count($batchIDs);
    echo "deleting {$batchFinalCount} contacts. {$contactCount} total contacts deleted...{$lineBreak}";

    //return output
    $output = "{$lineBreak}{$contactCount} trashed contact records were permanently deleted.";
    echo $output;

    if ($params['return'] ?? FALSE) {
      return $contactCount;
    }

    CRM_Utils_System::civiExit();
  }
}
