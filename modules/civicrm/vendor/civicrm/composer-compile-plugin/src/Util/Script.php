<?php

namespace Civi\CompilePlugin\Util;

/**
 * Class Script
 * @package Civi\CompilePlugin\Util
 *
 * Small helper methods for use in task-scripts.
 *
 */
class Script
{
    /**
     * Assert that we are properly executing within the context of a compilation ask.
     */
    public static function assertTask()
    {
        if (empty($GLOBALS['COMPOSER_COMPILE_TASK']) || empty(getenv('COMPOSER_COMPILE_TASK'))) {
            fwrite(STDERR, "This script may only be invoked via \"composer compile\".\n");
            exit(1);
        }
    }

    /**
     * Get the task-definition.
     *
     * @return array
     *   The task definition, as per `composer.json` (possibly with some defaults/mappings filled in).
     */
    public static function getTask()
    {
        return $GLOBALS['COMPOSER_COMPILE_TASK'];
    }
}
