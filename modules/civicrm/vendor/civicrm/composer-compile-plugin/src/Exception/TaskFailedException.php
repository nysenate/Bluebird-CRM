<?php

namespace Civi\CompilePlugin\Exception;

use Civi\CompilePlugin\Task;

class TaskFailedException extends \Exception
{

    protected $task;

    /**
     * TaskFailedException constructor.
     * @param Task $task
     */
    public function __construct(Task $task)
    {
        parent::__construct('Failed task: ' . $task->title);
        $this->task = $task;
    }

    /**
     * @return \Civi\CompilePlugin\Task
     */
    public function getTask()
    {
        return $this->task;
    }
}
