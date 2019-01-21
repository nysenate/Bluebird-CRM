<?php
abstract class CRM_DoctorWhen_Cleanups_Base {

  const DEFAULT_BATCH_SIZE = 5000;

  public function isActive() {
    return TRUE;
  }

  public abstract function getTitle();

  public abstract function enqueue(CRM_Queue_Queue $queue, $options);

  /**
   * Create a new task for insertion in the queue.
   *
   * Example:
   *
   * @code
   * $task = $this->createTask('Update the foo', 'executeQuery', $sql, $vars);
   * $queue->createItem($task);
   * @endCode
   *
   * @param string $title
   * @param string $funcName
   *   Name of a static function in the current class.
   * @return \CRM_Queue_Task
   */
  public function createTask($title, $funcName) {
    $args = func_get_args();
    $title = array_shift($args);
    $funcName = array_shift($args);
    $task = new CRM_Queue_Task(
      array(get_class($this), $funcName),
      $args,
      $title
    );
    return $task;
  }

  /**
   * (Queue Task Callback)
   *
   * Execute a single SQL query.
   *
   * @param CRM_Queue_TaskContext $ctx
   * @param string $sql
   *   An SQL template. May include tokens `%1`, `%2`, etc.
   * @param array $vars
   *   List of SQL parameters, as used by executeQuery().
   *
   * @return bool
   *
   * @see CRM_Core_DAO::executeQuery
   */
  public static function executeQuery($ctx, $sql, $vars = array()) {
    CRM_Core_DAO::executeQuery($sql, $vars);
    return TRUE;
  }

}
