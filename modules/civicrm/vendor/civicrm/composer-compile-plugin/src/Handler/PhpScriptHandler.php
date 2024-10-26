<?php

namespace Civi\CompilePlugin\Handler;

use Civi\CompilePlugin\Event\CompileTaskEvent;
use Civi\CompilePlugin\Util\ShellRunner;

/**
 * Class PhpScriptHandler
 * @package Civi\CompilePlugin\Handler
 *
 * This implements support for run-steps based on `@php-script <filename> [<cli-args>]`.
 */
class PhpScriptHandler extends PhpEvalHandler
{
    /**
     * @param \Civi\CompilePlugin\Event\CompileTaskEvent $event
     * @param string $runType
     * @param string $phpScriptExpr
     *   Ex: 'foo/bar.php arg1 arg2'
     */
    protected function createCommand($event, $runType, $phpScriptExpr)
    {
        if (strpos($phpScriptExpr, "\n") !== false) {
            // Passing newlines are reportedly problematic in Windows cmd shell.
            throw new \RuntimeException("CompilePlugin: Multiline script call is not permitted");
        }

        if (strpos($phpScriptExpr, ' ') !== false) {
            list ($scriptFile, $scriptArgs) = explode(' ', $phpScriptExpr, 2);
        } else {
            $scriptFile = $phpScriptExpr;
            $scriptArgs = '';
        }

        if (!file_exists($scriptFile)) {
            // It's prettier if we report the error rather than letting the subprocess fail.
            throw new \RuntimeException(sprintf("CompilePlugin: Script %s does not exist in %s", $scriptFile, getcwd()));
        }

        return parent::createCommand($event, 'php-eval', sprintf(
            'require %s;',
            var_export($scriptFile, 1)
        )) . ' ' . $scriptArgs;
    }
}
