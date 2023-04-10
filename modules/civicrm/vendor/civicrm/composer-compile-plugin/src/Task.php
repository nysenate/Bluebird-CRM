<?php

namespace Civi\CompilePlugin;

class Task
{

    /**
     * List of supported types
     *
     * This is a quick-and-dirty hack.
     */
    public const HANDLERS = 'composer|export|php|php-eval|php-method|php-script|putenv|sh';

    /**
     * (Required) A unique ID for this task
     *
     * Ex: 'vendor/package:123'
     *
     * @var string
     */
    public $id;

    /**
     * (Optional) Printable title for this compilation task.
     *
     * @var string
     *   Ex: 'Compile <comment>foobar.txt</comment>'
     */
    public $title;

    /**
     * (Optional) The developer's chosen ordering key.
     *
     * This ordering takes precedence over all other orderings. For example,
     * if your ecosystem had a policy that all `XML=>PHP` compilations run
     * before all `SCSS=>CSS` compilations, then you would use different weights
     * for `XML=>PHP` (eg -5) and `SCSS=>CSS` (eg +5).
     *
     * NOTE: This option was added in early drafts as a pressure-relief valve
     * in case some control was needed over ordering. It's now hidden, though,
     * because I think it's better to wait for some feedback re:use-cases before
     * committing to this model.
     *
     * @var int
     */
    public $weight;

    /**
     * (System-Generated) The topological order of the package which defines this
     * task.
     *
     * @var int
     */
    public $packageWeight;

    /**
     * (System-Generated) Within a given package, the written ordering (from JSON)
     * determines natural weight.
     *
     * @var int
     */
    public $naturalWeight;

    /**
     * (System-Generated) The name of the package which defined this task.
     *
     * @var string
     */
    public $packageName;

    /**
     * (Required) Run commands
     *
     * List of commands to run. Each command should begin with an '@type'.
     *
     * Ex: '@php-method Foo::bar'
     * Ex: '@sh cp foo bar'
     *
     * @var string[]
     */
    public $run;

    /**
     * (Required) The folder in which to execute the task.
     *
     * @var string
     */
    public $pwd;

    /**
     * List of file-names and/or directory-names to watch.
     *
     * @see \Lurker\ResourceWatcher::track()
     * @var array
     */
    public $watchFiles = [];

    /**
     * (Optional) Whether the task should be executed.
     *
     * @var bool
     */
    public $active;

    /**
     * The file in which this task was originally defined.
     *
     * @var string
     */
    public $sourceFile;

    /**
     * (Required) The raw task definition
     *
     * @var array
     */
    public $definition;

    /**
     * Ensure that any required fields are defined.
     * @return static
     */
    public function validate()
    {
        $missing = [];
        foreach (['naturalWeight', 'packageWeight', 'packageName', 'pwd', 'definition', 'run'] as $requiredField) {
            if ($this->{$requiredField} === null || $this->{$requiredField} === '') {
                $missing[] = $requiredField;
            }
        }
        if ($missing) {
            throw new \RuntimeException("Compilation task is missing field(s): " . implode(",", $missing));
        }

        $handlers = explode('|', self::HANDLERS);
        foreach ($this->getParsedRun() as $run) {
            if (!in_array($run['type'], $handlers)) {
                throw new \RuntimeException("Compilation task has invalid run expression: " . json_encode($run['expr']));
            }
        }
        return $this;
    }

    /**
     * @param string $filter
     *   Ex: 'vendor/*'
     *   Ex: 'vendor/package'
     *   Ex: 'vendor/package:id'
     * @return bool
     */
    public function matchesFilter($filter)
    {
        if ($this->packageName === '__root__') {
            return $filter === '__root__';
        }

        list ($tgtVendorPackage, $tgtId) = explode(':', "{$filter}:");
        list ($tgtVendor, $tgtPackage) = explode('/', $tgtVendorPackage . '/');
        list ($actualVendor, $actualPackage) = explode('/', $this->packageName);

        if ($tgtVendor !== '*' && $tgtVendor !== $actualVendor) {
            return false;
        }
        if ($tgtPackage !== '*' && $tgtPackage !== $actualPackage) {
            return false;
        }
        if ($tgtId !== '' && $tgtId !== '*' && $tgtId != $this->naturalWeight) {
            return false;
        }

        return true;
    }

    /**
     * Get a list of 'run' values.
     *
     * @return array
     *   List of the 'run' values. Each is parsed.
     *
     *   Example: Suppose we have `run => ['@php foo']`
     *   The output would be: `[['type' => 'php', 'code' => 'foo']]`
     */
    public function getParsedRun()
    {
        $runs = [];
        foreach ($this->run as $runExpr) {
            $runs[] = $this->parseRunExpr($runExpr);
        }
        return $runs;
    }

    protected function parseRunExpr($runExpr)
    {
        if (preg_match(';^@([a-z0-9\-]+) (.*)$;', $runExpr, $m)) {
            return [
              'type' => $m[1],
              'code' => $m[2],
              'expr' => $runExpr,
            ];
        } else {
            throw new \InvalidArgumentException("Failed to parse run expression: $runExpr");
        }
    }
}
