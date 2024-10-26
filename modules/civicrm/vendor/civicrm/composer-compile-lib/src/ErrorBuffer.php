<?php
namespace CCL;

class ErrorBuffer {

  /**
   * @return static
   */
  public static function create() {
    return new static();
  }

  /**
   * @var string[]
   */
  protected $lines = [];

  /**
   * @var bool
   */
  protected $fatal = FALSE;

  /**
   * Start directing errors to this buffer.
   *
   * @return static
   */
  public function start() {
    set_error_handler([$this, 'onError']);
    return $this;
  }

  /**
   * Stop directing errors to this buffer.
   *
   * @return static
   */
  public function stop() {
    restore_error_handler();
    return $this;
  }

  public function onError($errno, $errstr, $errfile, $errline) {
    switch ($errno) {
      case E_ERROR:
      case E_PARSE:
      case E_CORE_ERROR:
      case E_COMPILE_ERROR:
      case E_USER_ERROR:
      case E_RECOVERABLE_ERROR:
        $this->fatal = TRUE;
        $this->lines[] = sprintf("Error: \"%s\" in \"%s\" at line %d", $errstr, $errfile, $errline);
        return TRUE;

      case E_WARNING:
      case E_CORE_WARNING:
      case E_COMPILE_WARNING:
      case E_USER_WARNING:
      case E_NOTICE:
      case E_USER_NOTICE:
      case E_STRICT:
      case E_DEPRECATED:
      case E_USER_DEPRECATED:
        $this->lines[] = sprintf("Warning: \"%s\" in \"%s\" at line %d", $errstr, $errfile, $errline);
        return TRUE;

      default:
        return FALSE;
    }
  }

  /**
   * @return \string[]
   */
  public function getLines() {
    return $this->lines;
  }

  public function isFatal() {
    return $this->fatal;
  }

}
