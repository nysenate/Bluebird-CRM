<?php

namespace Civi\CompilePlugin\Handler;

use Civi\CompilePlugin\Event\CompileTaskEvent;
use Civi\CompilePlugin\Util\ComposerIoTrait;
use Civi\CompilePlugin\Util\EnvHelper;
use Civi\CompilePlugin\Util\ShellRunner;
use Composer\Composer;
use Composer\IO\IOInterface;

/**
 * Class ExportHandler
 * @package Civi\CompilePlugin\Handler
 *
 * This implements support for run-steps based on `@export VAR1={{expr1}} VAR2={{expr2}}`.
 *
 * The intent is to allow exporting information about the composer runtime, such
 * as the file-path to upstream packages.
 */
class ExportHandler
{

    /**
     * @var string
     */
    public $rootPackagePath;

    /**
     * ExportHandler constructor.
     */
    public function __construct()
    {
        $this->rootPackagePath = getcwd();
    }

    /**
     * @param \Civi\CompilePlugin\Event\CompileTaskEvent $event
     * @param string $runType
     * @param string $exportList
     *   Ex: 'BOOT_CSS={{pkg:twbs/bootstrap}}'
     *   Ex: 'BOOT_CSS={{pkg:twbs/bootstrap}} BOOT_SCSS={{pkg:twbs/bootstrap-sass}}'
     */
    public function runTask(CompileTaskEvent $event, $runType, $exportList)
    {
        /** @var Composer $composer */
        $composer = $event->getComposer();
        $io = $event->getIO();

        $exports = explode(' ', $exportList);
        foreach ($exports as $export) {
            $envExpr = preg_replace_callback(';\{\{([\w_\-: /]*)\}\};', function ($m) use ($composer, $io) {
                // Ex: 'pkgs:twbs/bootstrap'
                $expr = $m[1];

                if (preg_match('/^pkg:/', $expr)) {
                    $packageName = substr($expr, 4);
                    return $this->findPkgPath($composer, $io, $packageName);
                }
            }, trim($export));
            [$name, $value] = explode('=', $envExpr, 2);
            EnvHelper::set($name, $value);
        }
    }

    /**
     * @param Composer $composer
     * @param IOInterface $io
     * @param string $packageName
     * @return string
     */
    protected function findPkgPath($composer, $io, $packageName)
    {
        if (in_array($packageName, $composer->getPackage()->getNames())) {
            return $this->rootPackagePath;
        }

        $localRepo = $composer->getRepositoryManager()->getLocalRepository();
        $package = $localRepo->findPackage($packageName, '*');
        if ($package) {
            return $composer->getInstallationManager()->getInstallPath($package);
        } else {
            // This is only a warning -- e.g. they might be looking up a 'suggested' pkg.
            $io->write("<warning>The package $packageName does not exist.</warning>");
            return '';
        }
    }
}
