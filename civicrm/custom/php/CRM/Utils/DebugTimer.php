<?php

class CRM_Utils_DebugTimer {
  protected static $_timers = array();
  protected static $_name = 'default';
  public $start_time;
  public $end_time;
  public $marks = array();
  
  private function __construct($name='default') {
    $this->_name=self::_resolveName($name);
  }
  
  public static function &create($name='default', $start=true, $reset=true) {
    $timer = &self::_get($name,true);
    if ($reset) {
      $timer->reset();
    }
    if ($start) {
      $timer->start();
    }
    return $timer;
  }
  
  protected static function &_get($name='default', $create=false) {
    $name=self::_resolveName($name);
    if (array_key_exists($name,self::$_timers)) {
      $timer =& self::$_timers[$name];
      if ($create) { $timer->reset(); }
    } else {
      $timer =& new CRM_Utils_DebugTimer($name);
    }
    self::$_timers[$name] =& $timer;
    return $timer;
  }
  
  private static function _resolveName($name='default') {
    if (!(string)$name) { $name = self::$_name; }
    return $name;
  }
  
  public function clearMarks() {
    $this->marks = array();
  }

  public function interval($mark=NULL) {
    $ret = false;
    if ($this->start_time) {
      if (count($this->marks)>1) {
        $tmark = ($mark && array_key_exists($this->marks[$mark])) ? $this->marks[$mark] : end($this->marks);
      } else {
        $tmark = round(microtime(true),4);
      }
      $ret = round($tmark - $this->start_time, 4);
    }
    return $ret;
  }

  public function log($msg = NULL, $mark=NULL) {
    $timername = "CRM_Utils_DebugTimer {$this->_name}";
    $timermark = $mark ? ", Mark '$mark'" : '';
    $timertime = $this->interval($mark);
    if (is_null($msg)) { $msg = "Time Elapsed"; }
    $msg = "{$timername}{$timermark}, {$msg}: " .
            (($timertime!==false) ? $timertime : "TIMER_NOT_STARTED");
    error_log($msg);
  }
  
  public function logAll($msg = NULL) {
    if (is_null($msg)) { $msg = "Logging all for timer '{$this->_name}'"; }
    foreach ($this->marks as $k=>$v) {
      $this->log($msg, $k);
    }
  }
  
  public function mark($name=NULL, $time = NULL) {
    if (is_null($time)) { $time = microtime(true); }
    if (is_null($name)) { $name = count($this->marks); }
    $this->marks[$name] = round($time,4);
    return $this->marks[$name];
  }
  
  public function reset() {
    $this->end_time = NULL;
    $this->start_time = NULL;
    $this->clearMarks();
  }
  
  public function start() {
    $this->start_time = $this->mark();
    $this->end_time = NULL;
    return $this->start_time;
  }
  
  public function stop($reset=false) {
    $time = $this->mark();
    $this->end_time = $time;
    if ($reset) { $this->reset(); }
    return $time;
  }
}