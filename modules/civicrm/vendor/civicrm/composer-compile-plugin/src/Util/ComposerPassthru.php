<?php

namespace Civi\CompilePlugin\Util;

use Composer\Composer;
use Composer\EventDispatcher\EventDispatcher;
use Composer\IO\IOInterface;

/**
 * Class ComposerPassthru
 * @package Civi\CompilePlugin\Util
 *
 * Place a call to composer in a subprocess.
 *
 * Unlike EventDispatcher and ShellRunner, this variant is supposed to be
 * pass-thru console I/O.
 *
 * This is useful for protecting against quirky scenarios mid-upgrade.
 * @link https://github.com/civicrm/composer-compile-plugin/issues/7
 */
class ComposerPassthru
{
    use ComposerIoTrait {
        __construct as constructComposerIo;
    }

    /**
     * List of basic 'composer' options that will be passed
     * through to any/all subcommands.
     *
     * @var string[]
     */
    protected $options;

    public function __construct(Composer $composer, IOInterface $io)
    {
        $this->constructComposerIo($composer, $io);
        $this->options = [];
        if ($io->isDecorated()) {
            $this->options[] = '--ansi';
        }

        if (!$io->isInteractive()) {
            $this->options[] = '--no-interaction';
        }

        if ($io->isVeryVerbose()) {
            $this->options[] = ' -vv';
        } elseif ($io->isVerbose()) {
            $this->options[] .= '-v';
        }
    }

    /**
     * @param string $callable
     *   Ex: '@composer compile foo:bar'
     */
    public function run($callable)
    {
        $parts = [
          $this->getPhpExecCommand(),
          escapeshellarg(getenv('COMPOSER_BINARY')),
          implode(' ', $this->options),
          substr($callable, 9)
        ];

        $exec = implode(' ', $parts);

        passthru($exec, $exitCode);
        if ($exitCode !== 0) {
            $message = sprintf('Subcommand %s returned with error code %d', $callable, $exitCode);
            $this->io->writeError("<error>$message</error>", true, IOInterface::QUIET);
            throw new \RuntimeException($message);
        }
    }

    protected function getPhpExecCommand()
    {
        $d = new class ($this->composer, $this->io) extends EventDispatcher
        {
            public function exfiltratePhpExec()
            {
                return $this->getPhpExecCommand();
            }
        };
        return $d->exfiltratePhpExec();
    }
}
