<?php

/**
 * Class CRM_DoctorWhen_Cleanups_ReconcileSchema
 */
class CRM_DoctorWhen_Cleanups_ConvertTimestamp extends CRM_DoctorWhen_Cleanups_Base {

  private $table, $column, $jira, $default, $comment;

  /**
   * CRM_DoctorWhen_Cleanups_ReconcileSchema constructor.
   * @param array $tgt
   *   Keys:
   *     - table: string
   *     - column: string
   *     - changed: (optional) the version at which field became a timestamp
   *     - default: (optional)
   *     - cocmment (optional) table comment
   *     - jira: (optional) string, ex: "CRM-12345".
   */
  public function __construct($tgt) {
    $this->table = CRM_Utils_Array::value('table', $tgt);
    $this->column = CRM_Utils_Array::value('column', $tgt);
    $this->jira = CRM_Utils_Array::value('jira', $tgt);
    $this->default = CRM_Utils_Array::value('default', $tgt, 'NULL');
    $this->comment = CRM_Utils_Array::value('comment', $tgt);
  }

  public function isActive() {
    return CRM_Utils_Check_Component_Timestamps::isFieldType($this->table, $this->column, 'datetime');
  }

  public function getTitle() {
    $title = sprintf('"%s" - Change data type from DATETIME to TIMESTAMP',
      $this->table . '.' . $this->column);
    if ($this->jira) {
      $title .= sprintf(' (%s)', $this->jira);
    }
    return $title;
  }

  /**
   * Fill the queue with upgrade tasks.
   *
   * @param \CRM_Queue_Queue $queue
   * @param array $options
   */
  public function enqueue(CRM_Queue_Queue $queue, $options) {
    $sql = "ALTER TABLE {$this->table} CHANGE {$this->column} {$this->column} TIMESTAMP NULL DEFAULT {$this->default} ";
    if (isset($this->comment)) {
      $sql .= " COMMENT '{$this->comment}'";
    }
    $queue->createItem($this->createTask($this->getTitle(), 'executeQuery', $sql));
  }

}
