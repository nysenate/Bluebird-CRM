<?php

class CRM_DoctorWhen_Cleanups_ActivityModified extends CRM_DoctorWhen_Cleanups_Base {

  public function isActive() {
    return CRM_Core_DAO::checkFieldExists('civicrm_activity', 'modified_date');
  }

  public function getTitle() {
    return ts('"civicrm_activity.modified_date" - Fill in missing values using "civicrm_log" (CRM-20958)');
  }

  /**
   * Fill the queue with upgrade tasks.
   *
   * @param \CRM_Queue_Queue $queue
   * @param array $options
   */
  public function enqueue(CRM_Queue_Queue $queue, $options) {
    list($minId, $maxId) = CRM_Core_DAO::executeQuery(
      "SELECT coalesce(min(id),0), coalesce(max(id),0) FROM civicrm_activity"
    )->getDatabaseResult()->fetchRow();
    for ($startId = $minId; $startId <= $maxId; $startId += self::DEFAULT_BATCH_SIZE) {
      $endId = $startId + self::DEFAULT_BATCH_SIZE - 1;

      $title = sprintf('CRM-20958 - Fill in missing values of "civicrm_activity.modified_date" using civicrm_log (%d => %d)', $startId, $endId);

      $sql = 'UPDATE civicrm_activity
       SET modified_date = (SELECT MAX(l.modified_date) FROM civicrm_log l WHERE l.entity_table ="civicrm_activity" AND civicrm_activity.id = l.entity_id)
       WHERE (id BETWEEN %1 AND %2)
       AND modified_date IS NULL';
      $vars = array(1 => array($startId, 'Int'), 2 => array($endId, 'Int'));

      $queue->createItem($this->createTask($title, 'executeQuery', $sql, $vars));
    }
  }

}
