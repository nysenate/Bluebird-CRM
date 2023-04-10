<?php

namespace Civi\CompilePlugin\Util;

use Composer\IO\BaseIO;
use Composer\IO\IOInterface;

/**
 * Class IOFilter
 * @package Civi\CompilePlugin\Util
 *
 * This is a base-class for implementing a wrapper around IOInterface.
 */
class IOFilter extends BaseIO implements IOInterface
{
    /**
     * @var IOInterface
     */
    protected $delegate;

    /**
     * IOFilter constructor.
     * @param IOInterface $delegate
     */
    public function __construct($delegate)
    {
        $this->delegate = $delegate;
    }

    public function isInteractive()
    {
        return $this->delegate->isInteractive();
    }

    public function isVerbose()
    {
        return $this->delegate->isVerbose();
    }

    public function isVeryVerbose()
    {
        return $this->delegate->isVeryVerbose();
    }

    public function isDebug()
    {
        return $this->delegate->isDebug();
    }

    public function isDecorated()
    {
        return $this->delegate->isDecorated();
    }

    public function write($messages, $newline = true, $verbosity = self::NORMAL)
    {
        return $this->delegate->write($messages, $newline, $verbosity);
    }

    public function writeError(
        $messages,
        $newline = true,
        $verbosity = self::NORMAL
    ) {
        return $this->delegate->writeError($messages, $newline, $verbosity);
    }

    public function overwrite(
        $messages,
        $newline = true,
        $size = null,
        $verbosity = self::NORMAL
    ) {
        return $this->delegate->overwrite(
            $messages,
            $newline,
            $size,
            $verbosity
        );
    }

    public function overwriteError(
        $messages,
        $newline = true,
        $size = null,
        $verbosity = self::NORMAL
    ) {
        return $this->delegate->overwriteError(
            $messages,
            $newline,
            $size,
            $verbosity
        );
    }

    public function ask($question, $default = null)
    {
        return $this->delegate->ask($question, $default);
    }

    public function askConfirmation($question, $default = true)
    {
        return $this->delegate->askConfirmation($question, $default);
    }

    public function askAndValidate(
        $question,
        $validator,
        $attempts = null,
        $default = null
    ) {
        return $this->delegate->askAndValidate(
            $question,
            $validator,
            $attempts,
            $default
        );
    }

    public function askAndHideAnswer($question)
    {
        return $this->delegate->askAndHideAnswer($question);
    }

    public function select(
        $question,
        $choices,
        $default,
        $attempts = false,
        $errorMessage = 'Value "%s" is invalid',
        $multiselect = false
    ) {
        return $this->delegate->select(
            $question,
            $choices,
            $default,
            $attempts,
            $errorMessage,
            $multiselect
        );
    }
}
