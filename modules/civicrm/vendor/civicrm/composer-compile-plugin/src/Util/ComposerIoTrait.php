<?php

namespace Civi\CompilePlugin\Util;

use Composer\Composer;
use Composer\IO\IOInterface;

/**
 * Class ComposerIoTrait
 * @package Civi\CompilePlugin\Util
 *
 * Small bit of sugar for the common-case where a class injects $composer + $io.
 */
trait ComposerIoTrait
{
    /**
     * @var Composer
     */
    protected $composer;

    /**
     * @var IOInterface
     */
    protected $io;

    /**
     * TaskList constructor.
     * @param \Composer\Composer $composer
     * @param \Composer\IO\IOInterface $io
     */
    public function __construct(
        \Composer\Composer $composer,
        \Composer\IO\IOInterface $io
    ) {
        $this->composer = $composer;
        $this->io = $io;
    }
}
