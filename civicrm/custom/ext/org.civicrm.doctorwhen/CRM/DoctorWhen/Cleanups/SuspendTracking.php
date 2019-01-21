<?php

class CRM_DoctorWhen_Cleanups_SuspendTracking extends CRM_DoctorWhen_Cleanups_Base {

  public function getTitle() {
    return ts('Suspend modification tracking during upgrade (CRM-20958) [required]');
  }

  /**
   * Fill the queue with upgrade tasks.
   *
   * @param \CRM_Queue_Queue $queue
   * @param array $options
   */
  public function enqueue(CRM_Queue_Queue $queue, $options) {
    $queue->createItem($this->createTask($this->getTitle(), 'suspendModificationTracking'));
  }

  /**
   * (Queue Task Callback)
   *
   * @param CRM_Queue_TaskContext $ctx
   * @return bool
   */
  public static function suspendModificationTracking($ctx) {
    CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_case CHANGE modified_date modified_date TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');
    CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_activity CHANGE modified_date modified_date TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');
    return TRUE;
  }

}
