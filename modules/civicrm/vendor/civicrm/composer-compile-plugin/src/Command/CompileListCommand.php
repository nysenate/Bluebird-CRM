<?php

namespace Civi\CompilePlugin\Command;

use Civi\CompilePlugin\TaskList;
use Civi\CompilePlugin\TaskRunner;
use Civi\CompilePlugin\Util\TaskUIHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CompileListCommand extends \Composer\Command\BaseCommand
{

    protected function configure()
    {
        parent::configure();

        $this
          ->setName('compile:list')
          ->setDescription('Print list of compilation tasks')
          ->addOption('json', null, InputOption::VALUE_NONE, 'Report tasks in JSON format')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $taskList = new TaskList($this->getComposer(), $this->getIO());
        $taskList->load();

        $taskRunner = new TaskRunner($this->getComposer(), $this->getIO());
        $tasks = $taskRunner->sortTasks($taskList->getAll());

        if ($input->getOption('json')) {
            $output->writeln(json_encode($tasks), OutputInterface::OUTPUT_RAW);
        } elseif ($output->isVerbose()) {
            // TODO: Can we get Symfony Dumper to make this pretty?
            $output->writeln(
                json_encode($tasks, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                OutputInterface::OUTPUT_RAW
            );
        } else {
            $output->write(TaskUIHelper::formatTaskTable($tasks, ['active', 'id', 'title', 'action']));
        }
        return 0;
    }
}
