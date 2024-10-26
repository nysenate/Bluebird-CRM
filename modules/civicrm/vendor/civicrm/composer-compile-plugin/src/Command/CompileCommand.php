<?php

namespace Civi\CompilePlugin\Command;

use Civi\CompilePlugin\TaskList;
use Civi\CompilePlugin\TaskRunner;
use Civi\CompilePlugin\Util\EnvHelper;
use Composer\Script\ScriptEvents;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CompileCommand extends \Composer\Command\BaseCommand
{

    protected function configure()
    {
        parent::configure();

        $this
          ->setName('compile')
          ->setDescription('Run compilation tasks')
          ->addOption('all', null, InputOption::VALUE_NONE, 'Run all tasks, regardless of configuration')
          ->addOption('dry-run', 'N', InputOption::VALUE_NONE, 'Dry-run: Print a list of steps to be run')
          ->addOption('soft-options', null, InputOption::VALUE_OPTIONAL, '(Internal)')
          ->addArgument('filterExpr', InputArgument::IS_ARRAY, 'Optional filter to match. Ex: \'vendor/package\' or \'vendor/package:id\'')
          ->setHelp(
              "Run compilation steps in all packages\n" .
              "\n" .
              "If no filterExpr is given, then it will execute based on the current\n" .
              "configuration (per composer.json and environment-variables)."
          )
        ;
    }

    protected function initialize(
        InputInterface $input,
        OutputInterface $output
    ) {
        $so = $input->getOption('soft-options');
        if ($so) {
            $json = json_decode(base64_decode($so), 1);
            foreach ($json['o'] ?? [] as $key => $value) {
                if ($input->hasOption($key)) {
                    $input->setOption($key, $value);
                }
            }
        }

        parent::initialize($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($output->isVerbose()) {
            EnvHelper::set('COMPOSER_COMPILE_PASSTHRU', 'always');
        }

        $taskList = new TaskList($this->getComposer(), $this->getIO());
        $taskList->load()->validateAll();

        $taskRunner = new TaskRunner($this->getComposer(), $this->getIO());
        $filters = $input->getArgument('filterExpr');
        if ($input->getOption('all') && !empty($filters)) {
            throw new \InvalidArgumentException("The --all option does not accept filters.");
        } elseif ($input->getOption('all')) {
            $taskRunner->run($taskList->getAll(), $input->getOption('dry-run'));
        } elseif (!empty($filters)) {
            $tasks = $taskList->getByFilters($filters);
            $taskRunner->run(
                $tasks,
                $input->getOption('dry-run')
            );
        } else {
            $taskRunner->runDefault($taskList, $input->getOption('dry-run'));
        }
        return 0;
    }
}
