<?php

use CRM_Utils_Check_Component_Timestamps as Timestamps;

class CRM_DoctorWhen_Cleanups {

  protected $queueName;

  /**
   * @var array
   *   Array(string $id => CRM_DoctorWhen_Cleanups_Base).
   */
  protected $tasks;

  public function __construct($queueName = 'DoctorWhen') {
    $this->queueName = $queueName;

    // Note: The order of tasks *is* significant.
    $this->tasks = array();
    $this->tasks['SuspendTracking'] = new CRM_DoctorWhen_Cleanups_SuspendTracking();
    if (is_callable(array('CRM_Utils_Check_Component_Timestamps', 'getConvertedTimestamps'))) {
      foreach (Timestamps::getConvertedTimestamps() as $tgt) {
        $id = 'ReconcileSchema::' . $tgt['table'] . '::' . $tgt['column'];
        $this->tasks[$id] = new CRM_DoctorWhen_Cleanups_ConvertTimestamp($tgt);
      }
    }
    $this->tasks['ActivityCreated'] = new CRM_DoctorWhen_Cleanups_ActivityCreated();
    $this->tasks['ActivityModified'] = new CRM_DoctorWhen_Cleanups_ActivityModified();
    $this->tasks['CaseCreated'] = new CRM_DoctorWhen_Cleanups_CaseCreated();
    $this->tasks['CaseModified'] = new CRM_DoctorWhen_Cleanups_CaseModified();
    $this->tasks['RestoreTracking'] = new CRM_DoctorWhen_Cleanups_RestoreTracking();
  }

  /**
   *
   * @param array $options
   *   Array with keys:
   *     - tasks: array, list of enabled tasks
   *       Ex: array('ActivityCreated', 'ActivityModified').
   * @return CRM_Queue_Queue
   */
  public function buildQueue($options) {
    $queue = CRM_Queue_Service::singleton()->create(array(
      'type' => 'Sql',
      'name' => $this->queueName,
      'reset' => TRUE,
    ));

    $options['tasks'] = array_unique(array_merge($options['tasks'],
      array('SuspendTracking', 'RestoreTracking')));

    foreach ($this->getAllActive() as $id => $provider) {
      /** @var CRM_DoctorWhen_Cleanups_Base $provider */
      if (in_array($id, $options['tasks'])) {
        $provider->enqueue($queue, $options);
      }
    }

    return $queue;
  }

  /**
   * @return array
   *   Array(string $id => object $task).
   */
  public function getAll() {
    return $this->tasks;
  }

  /**
   * @return array
   *   Array(string $id => object $task).
   */
  public function getAllActive() {
    $tasks = array();
    foreach ($this->tasks as $id => $task) {
      if ($task->isActive()) {
        $tasks[$id] = $task;
      }
    }
    return $tasks;
  }

}
