<?php
/*
  Session class for BlueBird analytics
  Provides an easy reference object containing request parameters,
  database connections, configuration, etc.
*/

require_once 'NYSS/Utils.php';
require_once 'NYSS/Logger.php';
require_once 'NYSS/AJAX/Response.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/../civicrm/scripts/bluebird_config.php';

class NYSS_AJAX_Session {
  protected static $instance = NULL;

  public $bbconfig = NULL;
  public $request = NULL;
  public $response = NULL;
  public $logger = NULL;

  protected function __construct() {
    $this->loadBBConfig();
    $this->startLogger();
    $this->log("Logging started",NYSS_LOG_LEVEL_DEBUG);
    $this->_parseRequest();
    $this->response = new NYSS_AJAX_Response($this->req('req'));
  }

  public function configBBInstance($instance) {
    return NYSS_Utils::array_ifelse("instance:{$instance}", $this->bbconfig, array());
  }

  public function configBBInstanceCredentials($instance=NULL) {
    if (!$instance) { $instance = $this->req('instance_account'); }
    $ret = NYSS_Utils::array_ifelse("imap.accounts",$this->configBBInstance($instance));
    return $ret ? explode('|',$ret) : NULL;
  }

  public static function getInstance() {
    if (!(static::$instance)) {
      static::$instance = new static;
    }
    return static::$instance;
  }

  public function loadBBConfig($filename=NULL) {
    $this->bbconfig = get_bluebird_config($filename);
    if (!$this->bbconfig) {
      $this->response->sendFatal("Could not load config");
    }
  }

  /* wrapper around $this->log() for easier reference */
  public function log($msg, $lvl=NYSS_LOG_LEVEL_INFO) {
    $this->logger->log($msg, $lvl);
  }

  protected function _parseRequest() {
    $this->log("parseRequest full _REQUEST=\n".var_export($_REQUEST,1),NYSS_LOG_LEVEL_DEBUG);
    if (!is_array($this->request)) { $this->request = array(); }
    foreach ($_REQUEST as $k=>$v) {
      $this->request[$k] = NYSS_Utils::clean_string($v);
      $this->log("parseRequest set $k = ".var_export($this->request[$k],1),NYSS_LOG_LEVEL_DEBUG);
    }
  }

  public function req($key, $default=NULL) {
    return NYSS_Utils::array_ifelse($key, $this->request, $default);
  }

  public function startLogger() {
    $level = $file = $loc = NULL;
    if (array_key_exists('debug',$this->bbconfig)) {
      $c = $this->bbconfig['debug'];
      $level = (int) NYSS_Utils::array_ifelse('level', $c, $level);
      $file  =       NYSS_Utils::array_ifelse('file',  $c, $file );
      $loc   =       NYSS_Utils::array_ifelse('path',  $c, $loc  );
    }
    $this->logger = NYSS_Logger::getInstance($level, $file, $loc);
  }
}