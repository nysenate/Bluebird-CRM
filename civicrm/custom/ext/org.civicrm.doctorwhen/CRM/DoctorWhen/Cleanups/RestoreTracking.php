<?php

class CRM_DoctorWhen_Cleanups_RestoreTracking extends CRM_DoctorWhen_Cleanups_Base {

  public function getTitle() {
    return ts('Restore modification tracking after upgrade (CRM-20958) [required]');
  }

  /**
   * Fill the queue with upgrade tasks.
   *
   * @param \CRM_Queue_Queue $queue
   * @param array $options
   */
  public function enqueue(CRM_Queue_Queue $queue, $options) {
    $queue->createItem($this->createTask($this->getTitle(), 'restoreModificationTracking'));
  }

  /**
   * (Queue Task Callback)
   *
   * @param CRM_Queue_TaskContext $ctx
   * @return bool
   */
  public static function restoreModificationTracking($ctx) {
    CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_case CHANGE modified_date modified_date TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_activity CHANGE modified_date modified_date TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    return TRUE;
  }

}
