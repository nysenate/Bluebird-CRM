<?php

namespace Civi\CompilePlugin\Util;

use Civi\CompilePlugin\Subscriber\OldTaskAdapter;
use Civi\CompilePlugin\Subscriber\ShellSubscriber;
use Civi\CompilePlugin\Task;

class TaskUIHelper
{

    /**
     * Make a bulleted list to summarize the tasks.
     *
     * @param Task[] $tasks
     * @return string
     */
    public static function formatTaskSummary($tasks)
    {
        $tallies = [];
        foreach ($tasks as $task) {
            $tallies[$task->packageName] = $tallies[$task->packageName] ?? 0;
            $tallies[$task->packageName]++;
        }
        $buf = '';
        foreach ($tallies as $package => $tally) {
            if ($tally === 1) {
                $buf .= sprintf(
                    " - <comment>%s</comment> has <comment>%d</comment> task\n",
                    $package,
                    $tally
                );
            } else {
                $buf .= sprintf(
                    " - <comment>%s</comment> has <comment>%d</comment> task(s)\n",
                    $package,
                    $tally
                );
            }
        }
        return $buf;
    }

    /**
     * Make a table displaying a list of tasks.
     *
     * @param Task[] $tasks
     * @param string[] $fields
     *   List of fields/columns to display.
     *   Some mix of: 'active', 'id', 'packageName', 'title', 'action'
     * @return string
     */
    public static function formatTaskTable($tasks, $fields)
    {
        $availableHeaders = ['active' => '', 'id' => 'ID', 'packageName' => 'Package', 'title' => 'Title', 'action' => 'Action'];

        $header = [];
        foreach ($fields as $field) {
            $header[] = $availableHeaders[$field];
        }

        $fmtRun = function ($run) {
            return sprintf('<info>@%s</info> %s', $run['type'], $run['code']);
        };

        $makeMainRow = function (Task $task, $runExpr) use ($fields) {
            $row = [];
            foreach ($fields as $field) {
                switch ($field) {
                    case 'active':
                        $row[] = $task->active ? '+' : '-';
                        break;

                    case 'action':
                        $row[] = $runExpr;
                        break;

                    default:
                        $row[] = $task->{$field};
                        break;
                }
            }
            return $row;
        };

        $makeExtraRow = function (Task $task, $runExpr) use ($fields) {
            $row = [];
            foreach ($fields as $field) {
                switch ($field) {
                    case 'action':
                        $row[] = $runExpr;
                        break;

                    default:
                        $row[] = '';
                        break;
                }
            }
            return $row;
        };

        $rows = [];
        foreach ($tasks as $task) {
            /** @var Task $task */
            if (in_array('action', $fields)) {
                foreach ($task->getParsedRun() as $n => $run) {
                    $maker = ($n === 0) ? $makeMainRow : $makeExtraRow;
                    $rows[] = $maker($task, $fmtRun($run));
                }
            } else {
                $rows[] = $makeMainRow($task, null);
            }
        }

        return TableHelper::formatTable($header, $rows);
    }
}
