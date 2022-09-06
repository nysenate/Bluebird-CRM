<?php

namespace Civi\CompilePlugin\Command;

use Civi\CompilePlugin\Task;
use Civi\CompilePlugin\TaskList;
use Civi\CompilePlugin\Util\EnvHelper;
use Civi\CompilePlugin\Util\ShellRunner;
use Composer\EventDispatcher\ScriptExecutionException;
use Lurker\ResourceWatcher;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CompileWatchCommand extends \Composer\Command\BaseCommand
{

    protected function configure()
    {
        parent::configure();

        $this
          ->setName('compile:watch')
          ->setDescription('Watch source tree for changes. Compile automatically')
          ->addOption('dry-run', 'N', InputOption::VALUE_NONE, 'Dry-run: Print a list of steps to be run')
          ->addOption('interval', null, InputOption::VALUE_REQUIRED, 'How frequently to check for changes (milliseconds)', 1000)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($output->isVerbose()) {
            EnvHelper::set('COMPOSER_COMPILE_PASSTHRU', 'always');
        }

        $intervalMicroseconds = 1000 * $input->getOption('interval');
        $watcher = $taskList = null;
        $stale = true;
        $firstRun = true;

        while (true) {
            if ($stale) {
                if ($firstRun) {
                    $output->writeln("<info>Load compilation tasks</info>");
                } else {
                    $output->writeln("<info>Reload compilation tasks</info>");
                    // Ensure any previous instances destruct first. (Ex: Cleanup inotify)
                    unset($watcher);
                    $this->resetComposer();
                }

                $oldTaskList = $taskList;
                $taskList = new TaskList($this->getComposer(), $this->getIO());
                $taskList->load()->validateAll();

                $output->writeln(sprintf("Found <comment>%d</comment> task(s)", count($taskList->getAll())));

                if ($oldTaskList === null) {
                    $output->writeln("<info>Perform initial build</info>");
                    $this->runCompile($input, $output);
                } else {
                    $changedTasks = $this->findChangedTasks($oldTaskList, $taskList);
                    if ($changedTasks) {
                        $output->writeln("<info>Run new or modified tasks</info>");
                        foreach ($changedTasks as $taskId => $task) {
                            $this->runCompile($input, $output, $taskId);
                        }
                    } else {
                        $output->writeln("<info>No changed tasks</info>");
                    }
                    $oldTaskList = null;
                }

                $output->writeln("<info>Watch for updates</info>");
                $watcher = new ResourceWatcher();
                $addWatch = function ($logicalId, $filename, $callback) use ($watcher, $output) {
                    if (strpos($filename, getcwd() . '/') === 0) {
                        $filename = substr($filename, strlen(getcwd()) + 1);
                    }
                    $trackingId = $logicalId . ':' . md5($filename);
                    $output->writeln("<comment>$logicalId</comment>: $filename", OutputInterface::VERBOSITY_VERY_VERBOSE);
                    $watcher->track($trackingId, $filename);
                    $watcher->addListener($trackingId, $callback);
                };
                $onTaskListChange = function () use (&$stale) {
                    $stale = true;
                };
                foreach ($taskList->getSourceFiles() as $sourceFile) {
                    if (file_exists($sourceFile)) {
                        $addWatch('taskList', $sourceFile, $onTaskListChange);
                    }
                }

                foreach ($taskList->getAll() as $task) {
                    /** @var Task $task */
                    $onChangeTask = function ($e) use ($input, $output, $task) {
                        $this->runCompile($input, $output, $task->id);
                    };
                    foreach ($task->watchFiles ?? [] as $watch) {
                        $addWatch($task->id, $task->pwd . '/' . $watch, $onChangeTask);
                    }
                }

                $stale = false;
                $firstRun = false;
            }
            // CONSIDER: Perhaps it would be better to restart a PHP subprocess everytime configuration changes?
            // This would be more robust if, eg, the downloaded PHP code changes?

            $output->writeln("Polling", OutputInterface::VERBOSITY_VERY_VERBOSE);
            $watcher->start($intervalMicroseconds, $intervalMicroseconds);
        }
        return 0;
    }

    /**
     * Execute a subprocess with the 'composer compile' command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string|null $filterExpr
     *   Optional filter expression to pass to the subcommand.
     *   Ex: 'vendor/package:123'
     */
    protected function runCompile(InputInterface $input, OutputInterface $output, $filterExpr = null)
    {
        // Note: It is important to run compilation tasks in a subprocess to
        // ensure that (eg) `callback`s run with the latest code.

        $start = microtime(1);
        $output->writeln(sprintf("<info>Started at <comment>%s</comment></info>", date('Y-m-d H:i:s', (int)$start)));

        $cmd = '@composer compile';
        if ($input->getOption('dry-run')) {
            $cmd .= ' --dry-run';
        }
        if ($input->getOption('ansi')) {
            $cmd .= ' --ansi';
        }
        if ($filterExpr) {
            $cmd .= ' ' . escapeshellarg($filterExpr);
        }
        try {
            $r = new ShellRunner($this->getComposer(), $this->getIO());
            $r->run($cmd);
        } catch (ScriptExecutionException $e) {
            $this->getIO()->writeError('<error>Compilation failed</error>');
        } finally {
            $end = microtime(1);
            $output->writeln(sprintf(
                "<info>Finished at <comment>%s</comment> (<comment>%.3f</comment> seconds)</info>",
                date('Y-m-d H:i:s', $start),
                $end - $start
            ));
        }
    }

    protected function findChangedTasks(TaskList $oldTaskList, TaskList $newTaskList)
    {
        $export = function (Task $task) {
            $d = $task->definition;
            ksort($d);
            return $d;
        };
        $tasks = [];
        $old = $oldTaskList->getAll();
        foreach ($newTaskList->getAll() as $id => $newTask) {
            if (!isset($old[$id]) || $export($old[$id]) != $export($newTask)) {
                $tasks[$id] = $newTask;
            }
        }
        return $tasks;
    }
}
