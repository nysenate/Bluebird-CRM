<?php

namespace Civi\CompilePlugin;

use Civi\CompilePlugin\Util\EnvHelper;
use Composer\Util\Platform;

/**
 * Class TaskTransfer
 * @package Civi\CompilePlugin
 *
 * Facilitate the transfer of the `$task` definition from the main compilation
 * process to any subordinate PHP process.
 *
 * The transfer is performed through an environment variable COMPOSER_COMPILE_TASK.
 *
 * If the task-definition is reasonably small (eg a few lines of text), then it
 * will be put directly into `COMPOSER_COMPILE_TASK` (as base64(gzip(json))).
 *
 * If the task definition is larger, then it will be written to a JSON file.
 * The env-var will have a file reference ('@/tmp/foobar-1234.json`).
 *
 * Notes:
 *  - '@' is outside the range of characters for b64, so that's not ambiguous.
 *  - Subprocesses only live long enough to run a single task, so it's fair to
 *    have a global variable within that process.
 */
class TaskTransfer
{

    public const ENV_VAR = 'COMPOSER_COMPILE_TASK';
    public const GLOBAL_VAR = 'COMPOSER_COMPILE_TASK';

    /**
     * If the env-var would exceed this size, then divert to a file.
     *
     * Completely arbitrary number. In Windows, there's a cumulative limit
     * of 32k for all env-vars.
     */
    public const MAX_ENV_SIZE = 1500;

    /**
     * Put the $task definition into an environment variable.
     *
     * @param \Civi\CompilePlugin\Task $task
     */
    public static function export(Task $task)
    {
        $data = base64_encode(gzencode(json_encode($task->definition)));
        if (strlen($data) < self::MAX_ENV_SIZE) {
            EnvHelper::set(self::ENV_VAR, $data);
        } else {
            $tempFile = tempnam(sys_get_temp_dir(), 'composer-compile-');
            file_put_contents($tempFile, json_encode($task->definition));
            EnvHelper::set(self::ENV_VAR, '@' . $tempFile);
        }
    }

    /**
     * Import the task definition from an environment variable.
     */
    public static function import()
    {
        $raw = getenv(self::ENV_VAR);
        if ($raw === false || $raw === '') {
            fprintf(STDERR, "WARNING: Failed to read compilation-task from %s. Please use \"composer compile\".\n", self::ENV_VAR);
            $GLOBALS[self::GLOBAL_VAR] = [];
            return;
        }

        if ($raw[0] === '@') {
            $file = substr($raw, 1);
            $GLOBALS[self::GLOBAL_VAR] = json_decode(file_get_contents($file), 1);
        } else {
            $GLOBALS[self::GLOBAL_VAR] = json_decode(gzdecode(base64_decode($raw)), 1);
        }
    }

    /**
     * After executing a subtask, cleanup any variables/files that we created.
     */
    public static function cleanup()
    {
        $raw = getenv(self::ENV_VAR);
        if ($raw[0] === '@') {
            $file = substr($raw, 1);
            unlink($file);
        }
        EnvHelper::remove(self::ENV_VAR);
    }

    /**
     * @return string
     *   PHP code to setup a global variable with the active task.
     */
    public static function createImportStatement()
    {
        return sprintf('%s::import();', self::CLASS);
    }
}
