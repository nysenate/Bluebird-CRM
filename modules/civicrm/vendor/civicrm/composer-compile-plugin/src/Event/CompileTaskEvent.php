<?php

namespace Civi\CompilePlugin\Event;

use Civi\CompilePlugin\Task;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;

class CompileTaskEvent extends \Composer\EventDispatcher\Event
{

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var PackageInterface
     */
    private $package;

    /**
     * @var Task
     */
    private $task;

    /**
     * @var bool
     */
    private $dryRun;

    /**
     * CompileEvent constructor.
     * @param string $eventName
     * @param \Composer\Composer $composer
     * @param \Composer\IO\IOInterface $io
     * @param \Composer\Package\PackageInterface $package
     * @param \Civi\CompilePlugin\Task $task
     * @param bool $dryRun
     */
    public function __construct(
        $eventName,
        \Composer\Composer $composer,
        \Composer\IO\IOInterface $io,
        \Composer\Package\PackageInterface $package,
        Task $task,
        $dryRun
    ) {
        parent::__construct($eventName);
        $this->io = $io;
        $this->composer = $composer;
        $this->package = $package;
        $this->task = $task;
        $this->dryRun = $dryRun;
    }

    /**
     * @return \Composer\IO\IOInterface
     */
    public function getIO()
    {
        return $this->io;
    }

    /**
     * @return \Composer\Composer
     */
    public function getComposer()
    {
        return $this->composer;
    }

    /**
     * @return \Composer\Package\PackageInterface
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @return \Civi\CompilePlugin\Task
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * @return boolean
     */
    public function isDryRun()
    {
        return $this->dryRun;
    }
}
