<?php

namespace Civi\CompilePlugin\Handler;

use Civi\CompilePlugin\Event\CompileTaskEvent;
use Civi\CompilePlugin\TaskTransfer;
use Civi\CompilePlugin\Util\ShellRunner;

class PhpMethodHandler
{
    public function runTask(CompileTaskEvent $event, $runType, $phpMethod)
    {
        // Surely there's a smarter way to get this?
        $vendorPath = $event->getComposer()->getConfig()->get('vendor-dir');
        $autoload =  $vendorPath . '/autoload.php';
        if (!file_exists($autoload)) {
            throw new \RuntimeException("CompilePlugin: Failed to locate autoload.php");
        }

        $cmd = '@php -r ' . escapeshellarg(sprintf(
            'require_once %s; %s %s($GLOBALS[\Civi\CompilePlugin\TaskTransfer::GLOBAL_VAR]);',
            var_export($autoload, 1),
            TaskTransfer::createImportStatement(),
            $phpMethod
        ));

        $r = new ShellRunner($event->getComposer(), $event->getIO());
        $r->run($cmd);
    }

    /**
     * @param string $phpMethod
     * @return bool
     */
    public static function isWellFormedMethod($phpMethod)
    {
        if (!is_string($phpMethod)) {
            return false;
        }
        $parts = explode('::', $phpMethod);
        if (count($parts) > 2) {
            return false;
        }
        return preg_match(';^[a-zA-Z0-9_\\\:]+$;', $phpMethod);
    }
}
