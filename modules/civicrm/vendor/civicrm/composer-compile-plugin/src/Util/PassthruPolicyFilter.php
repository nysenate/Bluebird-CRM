<?php

namespace Civi\CompilePlugin\Util;

use Composer\IO\IOInterface;

/**
 * Class PassthruPolicyFilter
 * @package Civi\CompilePlugin\Util
 *
 * This provides a variant of IOInterface in which output abides by the "passthru"
 * policy, i.e.
 *    - 'always': Print all output
 *    - 'error': Only print output if there's an error. Requires buffering.
 *    - 'never': Do not print anything
 */
class PassthruPolicyFilter extends IOFilter
{

    /**
     * @var string
     *   One of: 'always', 'error', 'never'
     */
    private $mode;

    /**
     * @var string[]
     */
    private $buffer;

    /**
     * @var callable
     */
    private $checkMessage;

    /**
     * PassthruPolicyFilter constructor.
     * @param IOInterface $delegate ;
     * @param string $mode
     *   One of: 'always', 'error', 'never'
     * @param callable $checkMessage
     *   Check if a message represents an error. This is used to switch from
     *   buffer-mode to output mode.
     *   Signature: `function(string $msg, string $method): bool`
     */
    public function __construct($delegate, $mode, $checkMessage)
    {
        parent::__construct($delegate);
        $this->mode = $mode;
        $this->checkMessage = $checkMessage;
        switch ($mode) {
            case 'always':
            case 'never':
                $this->buffer = null;
                break;
            case 'error':
                $this->buffer = [];
                break;
            default:
                throw new \InvalidArgumentException("Unrecognized passthru mode: $mode");
        }
    }

    private function onBufferMessage($method, $args)
    {
        $this->buffer[] = [$method, $args];

        if ($this->mode === 'flushing') {
            return;
        }

        $isError = false;
        $messages = (array)$args[0];
        foreach ($messages as $message) {
            if (call_user_func($this->checkMessage, $message, $method)) {
                $isError = true;
                break;
            }
        }

        if ($isError) {
            $this->mode = 'flushing';
            for ($i = 0; $i < count($this->buffer); $i++) {
                call_user_func_array([$this->delegate, $this->buffer[$i][0]], $this->buffer[$i][1]);
            }
            $this->mode = 'always';
            $this->buffer = null;
        }
    }

    public function writeRaw(
        $messages,
        $newline = true,
        $verbosity = self::NORMAL
    ) {
        switch ($this->mode) {
            case 'always':
                return $this->delegate->writeRaw(
                    $messages,
                    $newline,
                    $verbosity
                );
            case 'error':
            case 'flushing':
                return $this->onBufferMessage(
                    __FUNCTION__,
                    [$messages, $newline, $verbosity]
                );
        }
    }

    public function writeErrorRaw(
        $messages,
        $newline = true,
        $verbosity = self::NORMAL
    ) {
        switch ($this->mode) {
            case 'always':
                return $this->delegate->writeErrorRaw(
                    $messages,
                    $newline,
                    $verbosity
                );
            case 'error':
            case 'flushing':
                return $this->onBufferMessage(
                    __FUNCTION__,
                    [$messages, $newline, $verbosity]
                );
        }
    }

    public function write($messages, $newline = true, $verbosity = self::NORMAL)
    {
        switch ($this->mode) {
            case 'always':
                return $this->delegate->write($messages, $newline, $verbosity);
            case 'error':
            case 'flushing':
                return $this->onBufferMessage(
                    __FUNCTION__,
                    [$messages, $newline, $verbosity]
                );
        }
    }

    public function writeError(
        $messages,
        $newline = true,
        $verbosity = self::NORMAL
    ) {
        switch ($this->mode) {
            case 'always':
                return $this->delegate->writeError(
                    $messages,
                    $newline,
                    $verbosity
                );
            case 'error':
            case 'flushing':
                return $this->onBufferMessage(
                    __FUNCTION__,
                    [$messages, $newline, $verbosity]
                );
        }
    }

    public function overwrite(
        $messages,
        $newline = true,
        $size = null,
        $verbosity = self::NORMAL
    ) {
        switch ($this->mode) {
            case 'always':
                return $this->delegate->overwrite(
                    $messages,
                    $newline,
                    $size,
                    $verbosity
                );
            case 'error':
            case 'flushing':
                return $this->onBufferMessage(
                    __FUNCTION__,
                    [$messages, $newline, $size, $verbosity]
                );
        }
    }

    public function overwriteError(
        $messages,
        $newline = true,
        $size = null,
        $verbosity = self::NORMAL
    ) {
        switch ($this->mode) {
            case 'always':
                return $this->delegate->overwriteError(
                    $messages,
                    $newline,
                    $size,
                    $verbosity
                );
            case 'error':
            case 'flushing':
                return $this->onBufferMessage(
                    __FUNCTION__,
                    [$messages, $newline, $size, $verbosity]
                );
        }
    }
}
