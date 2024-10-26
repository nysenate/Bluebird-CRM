<?php

namespace Civi\CompilePlugin\Event;

class CompileEvents
{

    /**
     * The PRE_COMPILE_LIST event occurs before parsing the task-list. This allows
     * other plugins to inspect and modify the raw 'taskDefinitions'.
     *
     * @see CompileListEvent
     */
    public const PRE_COMPILE_LIST = 'pre-compile-list';

    /**
     * The POST_COMPILE_LIST event occurs after parsing the task-list. This allows
     * other plugins to inspect and modify the parsed 'tasks'.
     *
     * @see CompileListEvent
     */
    public const POST_COMPILE_LIST = 'post-compile-list';

    /**
     * The PRE_COMPILE_TASK event occurs before executing a specific task.
     */
    public const PRE_COMPILE_TASK = 'pre-compile-task';

    /**
     * The POST_COMPILE_TASK event occurs before executing a specific task.
     */
    public const POST_COMPILE_TASK = 'post-compile-task';
}
