<?php

namespace Civi\CompilePlugin;

use Civi\CompilePlugin\Command\CompileCommand;
use Civi\CompilePlugin\Command\CompileListCommand;
use Civi\CompilePlugin\Command\CompileWatchCommand;

class CommandProvider implements \Composer\Plugin\Capability\CommandProvider
{

    public function getCommands()
    {
        return [
            new CompileCommand(),
            new CompileListCommand(),
            new CompileWatchCommand(),
        ];
    }
}
