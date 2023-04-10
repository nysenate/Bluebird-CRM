<?php

namespace Civi\CompilePlugin;

use Civi\CompilePlugin\Event\CompileEvents;
use Civi\CompilePlugin\Event\CompileListEvent;
use Civi\CompilePlugin\Util\ComposerIoTrait;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;

class TaskList
{
    use ComposerIoTrait;

    /**
     * @var Task[]
     */
    protected $tasks;

    /**
     * @var array
     */
    protected $sourceFiles;

    /**
     * @var array
     *   Ex: ['foo/upstream' => 1, 'foo/downstream' => 2]
     */
    protected $packageWeights;

    /**
     * Scan the composer data and build a list of compilation tasks.
     *
     * @return static
     */
    public function load()
    {
        $this->tasks = [];
        $this->sourceFiles = [];
        $allPackages = array_merge(
            $this->composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages(),
            [$this->composer->getPackage()]
        );
        $compilePackages = $this->filterByHavingCompileTasks($allPackages);
        $this->packageWeights = array_flip(PackageSorter::sortPackages(
            $compilePackages
        ));

        $rootPackage = $this->composer->getPackage();
        $localRepo = $this->composer->getRepositoryManager()->getLocalRepository();
        foreach ($this->packageWeights as $packageName => $packageWeight) {
            if ($packageName === $rootPackage->getName()) {
                $this->loadPackage($rootPackage, realpath('.'));
                // I'm not a huge fan of using 'realpath()' here, but other tasks (using `getInstallPath()`)
                // are effectively using `realpath()`, so we should be consistent.
            } else {
                $package = $localRepo->findPackage($packageName, '*');
                $this->loadPackage($package, $this->composer->getInstallationManager()->getInstallPath($package));
            }
        }

        return $this;
    }

    /**
     * @param \Composer\Package\PackageInterface[] $installedPackages
     * @return array
     * List of installed packages (PackageInterface) with compilation tasks
     */
    protected function filterByHavingCompileTasks($installedPackages)
    {
        $rootPackage = $this->composer->getPackage();
        $localRepo = $this->composer->getRepositoryManager()->getLocalRepository();
        $packagesWithCompileTasks = [];

        foreach ($installedPackages as $package) {
            $path = '';
            if ($package->getName() === $rootPackage->getName()) {
                $installPath = realpath('.');
                // I'm not a huge fan of using 'realpath()' here, but other tasks (using `getInstallPath()`)
                // are effectively using `realpath()`, so we should be consistent.
            } else {
                $package = $localRepo->findPackage($package->getName(), '*');
                $installPath = $this->composer->getInstallationManager()->getInstallPath($package);
            }
            if ($this->packageHasCompileTasks($package, $installPath)) {
                $packagesWithCompileTasks[] = $package;
            }
        }
        return $packagesWithCompileTasks;
    }

    /**
     * @param \Composer\Package\PackageInterface $package
     * @param string $installPath The package's location on disk.
     * @return True if compile tasks are defined for this package.
     */
    protected function packageHasCompileTasks(PackageInterface $package, $installPath)
    {
        $extra = null;

        // Replicate behaviour from loadPackage which prefers
        // composer.json on disk values over use of getExtra.

        if (file_exists("$installPath/composer.json")) {
            $json = json_decode(file_get_contents("$installPath/composer.json"), 1);
            $extra = $json['extra'] ?? null;
        }
        if ($extra === null) {
            $extra = $package->getExtra();
        }
        if (!empty($extra['compile'])) {
            return true;
        }

        if (empty($extra['compile-includes'])) {
            return false;
        }

        foreach ($extra['compile-includes'] as $includeFile) {
            $includePathFull = "$installPath/$includeFile";
            if (!file_exists($includePathFull) || !is_readable($includePathFull)) {
                $this->io->writeError("<warning>Failed to read $includePathFull</warning>");
                continue;
            }
            $inc = json_decode(file_get_contents($includePathFull), 1);
            if (!empty($inc['compile'])) {
                return true;
            }
        }
    }

    /**
     * @param \Composer\Package\PackageInterface $package
     * @param string $installPath
     *   The package's location on disk.
     */
    protected function loadPackage(PackageInterface $package, $installPath)
    {
        // Typically, a package folder has its own copy of composer.json.  We prefer to read from that file in case one
        // is drafting or applying patches.  Relatedly, if another plugin wants to hook into our list of tasks, they
        // should not try to work with `getExtra()`.  Instead, listen to `PRE_COMPILE_LIST` or `POST_COMPILE_LIST`
        // and alter configuration there.

        $taskDefinitions = [];
        $addDefinitions = function ($newDefinitions, $sourceFile) use (&$taskDefinitions) {
            foreach ($newDefinitions as $defn) {
                $defn['source-file'] = $sourceFile;
                $taskDefinitions[] = $defn;
            }
            $this->sourceFiles[] = $sourceFile;
        };

        $extra = null;
        if ($extra === null && file_exists("$installPath/composer.json")) {
            $json = json_decode(file_get_contents("$installPath/composer.json"), 1);
            $extra = $json['extra'] ?? null;
        }
        if ($extra === null) {
            $extra = $package->getExtra();
        }
        $addDefinitions($extra['compile'] ?? [], "$installPath/composer.json");

        foreach ($extra['compile-includes'] ?? [] as $includeFile) {
            $includePathFull = "$installPath/$includeFile";
            if (!file_exists($includePathFull) || !is_readable($includePathFull)) {
                $this->io->writeError("<warning>Failed to read $includePathFull</warning>");
                continue;
            }
            $inc = json_decode(file_get_contents($includePathFull), 1);
            $addDefinitions($inc['compile'] ?? [], $includePathFull);
        }

        $event = new CompileListEvent(CompileEvents::PRE_COMPILE_LIST, $this->composer, $this->io, $package, $taskDefinitions);
        $this->composer->getEventDispatcher()->dispatch(CompileEvents::PRE_COMPILE_LIST, $event);
        $taskDefinitions = $event->getTasksSpecs();

        $naturalWeight = 1;
        $tasks = [];
        foreach ($taskDefinitions as $taskDefinition) {
            $defaults = [
                'active' => true,
                'title' => sprintf(
                    '<comment>%s</comment>:<comment>%s</comment>',
                    $package->getName(),
                    $naturalWeight
                ),
                'watch-files' => null,
            ];

            $taskDefinition = array_merge($defaults, $taskDefinition);
            $task = new Task();
            $task->id = $package->getName() . ':' . $naturalWeight;
            $task->sourceFile = $taskDefinition['source-file'];
            $task->definition = $taskDefinition;
            $task->packageName = $package->getName();
            $task->pwd = dirname($taskDefinition['source-file']);
            $task->weight = 0;
            $task->packageWeight = $this->packageWeights[$package->getName()];
            $task->naturalWeight = $naturalWeight;
            $task->active = $taskDefinition['active'];
            $task->watchFiles = $taskDefinition['watch-files'];
            $task->title = $taskDefinition['title'];
            $task->run = (array) $taskDefinition['run'];
            $tasks[$task->id] = $task;
            $naturalWeight++;
        }

        $event = new CompileListEvent(CompileEvents::POST_COMPILE_LIST, $this->composer, $this->io, $package, $taskDefinitions, $tasks);
        $this->composer->getEventDispatcher()->dispatch(CompileEvents::POST_COMPILE_LIST, $event);

        $this->tasks = array_merge($this->tasks, $event->getTasks());
    }

    /**
     * Disable a list of tasks.
     *
     * @param string|string[] $taskIds
     * @return int
     *   The number of tasks which were toggled.
     */
    public function disable($taskIds)
    {
        $taskIds = (array)$taskIds;
        $count = 0;
        foreach ($taskIds as $taskId) {
            if ($this->tasks[$taskId]->active) {
                $this->tasks[$taskId]->active = false;
                $count++;
            }
        }
        return $count;
    }

    /**
     * Get the list of input files which produced this task-list.
     *
     * @return \string[]
     */
    public function getSourceFiles()
    {
        return $this->sourceFiles;
    }

    /**
     * @return Task[]
     */
    public function getAll()
    {
        return $this->tasks;
    }

    /**
     * @param string $filter
     *   Ex: 'vendor/*'
     *   Ex: 'vendor/package'
     *   Ex: 'vendor/package:id'
     * @return Task[]
     */
    public function getByFilter($filter)
    {
        $tasks = [];
        foreach ($this->tasks as $task) {
            /** @var Task $task */
            if ($task->matchesFilter($filter)) {
                $tasks[$task->id] = $task;
            }
        }
        return $tasks;
    }

    /**
     * @param string[] $filters
     *   Ex: ['vendor1/*', 'vendor2/package2']
     * @return Task[]
     */
    public function getByFilters($filters)
    {
        $tasks = [];
        foreach ($filters as $filter) {
            $tasks = array_merge($tasks, $this->getByFilter($filter));
        }
        return $tasks;
    }

    /**
     * @return static
     */
    public function validateAll()
    {
        foreach ($this->tasks as $task) {
            $task->validate();
        }
        return $this;
    }
}
