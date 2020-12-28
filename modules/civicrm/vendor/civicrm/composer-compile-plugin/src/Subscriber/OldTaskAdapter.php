<?php

namespace Civi\CompilePlugin\Subscriber;

use Civi\CompilePlugin\Event\CompileEvents;
use Civi\CompilePlugin\Event\CompileListEvent;
use Civi\CompilePlugin\Handler\PhpMethodHandler;
use Composer\EventDispatcher\EventSubscriberInterface;

class OldTaskAdapter implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return [
          CompileEvents::PRE_COMPILE_LIST => 'mapRunner'
        ];
    }

    /**
     * In previous releases, the task could specify 'php-method' or 'shell'.
     * These have been consolidated as 'run'.
     *
     * This function maps the old notation to the new notation.
     *
     * @param \Civi\CompilePlugin\Event\CompileListEvent $e
     */
    public function mapRunner(CompileListEvent $e)
    {
        $defns = $e->getTasksSpecs();
        foreach ($defns as &$defn) {
            $defn['run'] = $defn['run'] ?? [];

            if (isset($defn['php-method'])) {
                $phpMethods = (array)$defn['php-method'];
                foreach ($phpMethods as $phpMethod) {
                    // TODO Maybe move the validation bit elsewhere
                    if (PhpMethodHandler::isWellFormedMethod($phpMethod)) {
                        $defn['run'][] = '@php-method ' . $phpMethod;
                    } else {
                        throw new \InvalidArgumentException("Malformed php-method: " . json_encode($phpMethod, JSON_UNESCAPED_SLASHES));
                    }
                }
            }

            if (isset($defn['shell'])) {
                $shellCmds = (array)$defn['shell'];
                foreach ($shellCmds as $shellCmd) {
                    $defn['run'][] = '@sh ' . $shellCmd;
                }
            }
        }

        $e->setTasksSpecs($defns);
    }
}
