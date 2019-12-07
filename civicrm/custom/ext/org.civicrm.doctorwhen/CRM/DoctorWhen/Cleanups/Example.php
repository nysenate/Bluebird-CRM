<?php

/**
 * Class CRM_DoctorWhen_Cleanups_Example
 */
class CRM_DoctorWhen_Cleanups_Example extends CRM_DoctorWhen_Cleanups_Base {

  public function getTitle() {
    return ts('Example title');
  }

  /**
   * Fill the queue with upgrade tasks.
   *
   * @param \CRM_Queue_Queue $queue
   * @param array $options
   */
  public function enqueue(CRM_Queue_Queue $queue, $options) {
    // Example: Execute some basic SQL
    $title = 'Execute some basic SQL';
    $sql = 'UPDATE civicrm_foo SET foo = "bar" WHERE whiz LIKE %1 ';
    $vars = array(
      1 => array('some example data', 'String'),
    );
    $queue->createItem(
      $this->createTask($title, 'executeQuery', $sql, $vars));

    // Example: Call a function in this class
    $title = 'Call an example function in this class';
    $extraVar = 'Hello world';
    $queue->createItem(
      $this->createTask($title, 'myStaticFunction', $extraVar));

    // TIP: If the tasks may intensive, then try enqueuing multiple tasks.
    // You can call the same SQL query or PHP function multiple times.
  }

  /**
   * (Queue Task Callback)
   *
   * @param CRM_Queue_TaskContext $ctx
   * @return bool
   */
  public static function myStaticFunction($ctx, $extraVar) {
    // Do something.
    return TRUE;
  }

}
