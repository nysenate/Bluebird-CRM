<?php

namespace Civi\CompilePlugin\Handler;

use Civi\CompilePlugin\Event\CompileTaskEvent;
use Civi\CompilePlugin\Util\ShellRunner;

class ComposerScriptHandler
{

    public function runTask(CompileTaskEvent $e, $runType, $runCode)
    {
        $r = new ShellRunner($e->getComposer(), $e->getIO());
        switch ($runType) {
            // This type is actually handled as a composer script, though they don't recognize the prefix.
            case 'sh':
                $r->run($runCode);
                break;

            // These prefixes are the same -- simple pass-through.
            case 'composer':
            case 'php':
            case 'putenv':
                $r->run('@' . $runType . ' ' . $runCode);
                break;

            default:
                throw new \InvalidArgumentException("Unrecognized run-type: $runType");
        }
    }
}
