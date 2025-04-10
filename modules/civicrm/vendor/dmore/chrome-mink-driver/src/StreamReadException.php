<?php

namespace DMore\ChromeDriver;

/**
 *
 * @deprecated This exception is no longer used or thrown by this library.
 */
class StreamReadException extends \Exception
{
    /**
     * @var bool
     */
    private $eof;
    /**
     * @var bool
     */
    private $timed_out;
    /**
     * @var bool
     */
    private $blocked;

    public function __construct($message, $code, $state = [], \Exception $previous = null)
    {
        $this->message = $message;
        $this->eof = $state['eof'] ?? null;
        $this->timed_out = $state['timed_out'] ?? null;
        $this->blocked = $state['blocked'] ?? null;

        parent::__construct($message, 0, $previous);
    }

    /**
     * @return boolean
     */
    public function isEof()
    {
        return $this->eof;
    }

    /**
     * @return boolean
     */
    public function isTimedOut()
    {
        return $this->timed_out;
    }

    /**
     * @return boolean
     */
    public function isBlocked()
    {
        return $this->blocked;
    }
}
