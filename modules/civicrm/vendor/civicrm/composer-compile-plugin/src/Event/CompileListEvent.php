<?php

namespace Civi\CompilePlugin\Event;

use Civi\CompilePlugin\Task;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;

class CompileListEvent extends \Composer\EventDispatcher\Event
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
     * @var array
     */
    private $tasksSpecs;

    /**
     * @var Task[]
     */
    private $tasks;

    /**
     * CompileEvent constructor.
     * @param string $eventName
     * @param \Composer\Composer $composer
     * @param \Composer\IO\IOInterface $io
     * @param \Composer\Package\PackageInterface $package
     * @param array $tasksSpecs
     * @param \Civi\CompilePlugin\Task[] $tasks
     */
    public function __construct(
        $eventName,
        \Composer\Composer $composer,
        \Composer\IO\IOInterface $io,
        \Composer\Package\PackageInterface $package,
        array $tasksSpecs,
        ?array $tasks = null
    ) {
        parent::__construct($eventName);
        $this->io = $io;
        $this->composer = $composer;
        $this->package = $package;
        $this->tasksSpecs = $tasksSpecs;
        $this->tasks = $tasks;
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
     * @return array
     */
    public function getTasksSpecs()
    {
        return $this->tasksSpecs;
    }

    /**
     * @param array $tasksSpecs
     */
    public function setTasksSpecs($tasksSpecs)
    {
        $this->tasksSpecs = $tasksSpecs;
    }

    /**
     * @return \Civi\CompilePlugin\Task[]
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * @param \Civi\CompilePlugin\Task[] $tasks
     */
    public function setTasks($tasks)
    {
        $this->tasks = $tasks;
    }
}
