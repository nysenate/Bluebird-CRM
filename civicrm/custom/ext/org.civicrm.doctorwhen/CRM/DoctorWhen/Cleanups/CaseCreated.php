<?php

class CRM_DoctorWhen_Cleanups_CaseCreated extends CRM_DoctorWhen_Cleanups_Base {

  public function isActive() {
    return CRM_Core_DAO::checkFieldExists('civicrm_case', 'created_date');
  }

  public function getTitle() {
    return ts('"civicrm_case.created_date" - Fill in missing values using the activity log (CRM-20958)');
  }

  /**
   * Fill the queue with upgrade tasks.
   *
   * @param \CRM_Queue_Queue $queue
   * @param array $options
   */
  public function enqueue(CRM_Queue_Queue $queue, $options) {
    $openCaseTypeId = CRM_Core_DAO::singleValueQuery(
      'SELECT value FROM civicrm_option_value cov
      INNER JOIN civicrm_option_group cog ON cov.option_group_id = cog.id
      WHERE cov.name = "Open Case" and cog.name = "activity_type"'
    );

    list($minId, $maxId) = CRM_Core_DAO::executeQuery(
      "SELECT coalesce(min(id),0), coalesce(max(id),0) FROM civicrm_case"
    )->getDatabaseResult()->fetchRow();
    for ($startId = $minId; $startId <= $maxId; $startId += self::DEFAULT_BATCH_SIZE) {
      $endId = $startId + self::DEFAULT_BATCH_SIZE - 1;
      $vars = array(1 => array($startId, 'Int'), 2 => array($endId, 'Int'), 3 => array($openCaseTypeId, 'Int'));

      // CONSIDER: In my local system, the "Open Case" timestamps seem to be more synthetic (:00:00)
      //  $title = sprintf('CRM-20958 - Compute civicrm_case.created_date from "Open Case" (%d => %d)', $startId, $endId);
      //  $sql = 'UPDATE civicrm_case
      //   SET created_date = (
      //     SELECT MIN(a.activity_date_time)
      //     FROM civicrm_case_activity ca
      //     INNER JOIN civicrm_activity a ON (ca.activity_id = a.id)
      //     WHERE civicrm_case.id = ca.case_id
      //     AND a.activity_type_id = %3
      //   )
      //   WHERE (id BETWEEN %1 AND %2)
      //   AND created_date IS NULL
      //';
      //  $this->addTask($title, 'task_executeQuery', $sql, $vars);

      // In case... for some ungodly reason... the 'Open Case' activity was missing...
      $title = sprintf('CRM-20958 - Compute civicrm_case.created_date from the activity log (%d => %d)', $startId, $endId);
      $sql = 'UPDATE civicrm_case
       SET created_date = (
         SELECT MIN(l.modified_date)
         FROM civicrm_case_activity ca
         INNER JOIN civicrm_log l ON (l.entity_table = "civicrm_activity" AND ca.activity_id = l.entity_id)
         WHERE civicrm_case.id = ca.case_id
       )
       WHERE (id BETWEEN %1 AND %2)
       AND created_date IS NULL
    ';
      $queue->createItem($this->createTask($title, 'executeQuery', $sql, $vars));
    }
  }

}
