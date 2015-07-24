<?php
/* This file is a helper library for import_integration_messages.php
   It should never be run on its own */

require_once dirname(__FILE__).'/../../../scripts/common_funcs.php';
require_once dirname(__FILE__).'/../../../civicrm/scripts/script_utils.php';
require_once dirname(__FILE__).'/../../../civicrm/scripts/bluebird_config.php';
$old_incpath = add_packages_to_include_path(false);

// library to parse command-line options
require_once 'Console/Getopt.php';

// constants for log levels
define('LOG_LEVEL_DEBUG',5);
define('LOG_LEVEL_INFO',4);
define('LOG_LEVEL_NOTICE',3);
define('LOG_LEVEL_WARN',2);
define('LOG_LEVEL_ERROR',1);
define('LOG_LEVEL_CRITICAL',0);

/* A class to manage configuration for integration scripts */
class IntegrationConfig {
  // command-line options recognized by this script
  // there are no short options atm
  public $cli_short_options = '';
  public $cli_long_options = array(
            'source-host=',
            'source-user=',
            'source-pass=',
            'source-port=',
            'source-db=',
            'local-host=',
            'local-user=',
            'local-pass=',
            'local-port=',
            'local-db=',
            'archive=',
            'log-level=',
            );

  // the config array
  public $config = array();

  // other config sources
  public $bb_config = NULL;
  public $cli_config = NULL;

  // set up global default values
  // these can be overridden from command line and bluebird.cfg
  protected $_default_config = array(
            'source_host'=> 'localhost',
            'source_user'=> 'user',
            'source_pass'=> 'pass',
            'source_port'=> 'port',
            'source_db'  => 'db',
            'local_host' => 'localhost',
            'local_user' => 'user',
            'local_pass' => 'pass',
            'local_port' => 'port',
            'local_db'   => 'db',
            'archive'    => true,
            'log_level'  => 2,
            );

  // for singleton pattern
  protected static $instance = NULL;

  protected function __construct() {
    $this->config = $this->resolve_options();
  }

  public static function getInstance() {
    if (!self::$instance) {
      self::$instance = new IntegrationConfig();
    }
    return self::$instance;
  }

  public function resolve_options($reload = true) {
    $cfg = $this->_default_config;
    // init the return
    $ret = array();
    // get command line options
    if (is_null($this->cli_config) || $reload) {
      $this->cli_config = $this->get_cli_options();
    }
    // read the config file
    if (is_null($this->bb_config) || $reload) {
      $this->bb_config = $this->get_bb_options();
    }
    // set each cascaded option (command line -> bluebird -> default)
    foreach ($cfg as $k=>$v) {
      switch (true) {
        case array_key_exists($k, $this->cli_config):
          $ret[$k] = $this->cli_config[$k];
          break;
        case array_key_exists($k, $this->bb_config):
          $ret[$k] = $this->bb_config[$k];
          break;
        default:
          $ret[$k] = $v;
          break;
      }
    }
    $ret = $this->translate_logging_value($ret);
    return $ret;
  }

  public function get_bb_options() {
    $bbcfg = get_bluebird_config();
    $ret = array();
    foreach ($bbcfg['globals'] as $key=>$val) {
      if (preg_match('/^integration\./',$key)) {
        $k = str_replace('.','_',trim(preg_replace('/^integration\./','',$key)));
        $ret[$k] = trim($val);
      }
    }
    return $ret;
  }

  public function get_cli_options() {
    // ready
    $getopt = new Console_Getopt();
    // set
    $myargs = $getopt->readPHPArgv();
    // go
    $parsed = $getopt->getopt($myargs, $this->cli_short_options, $this->cli_long_options);
    // sanity check
    if (PEAR::isError($parsed)) {
      error_log("ERROR: Could not read options: {$parsed->message}");
      error_log("!!! COMMAND LINE OPTIONS IGNORED !!!");
      $parsed = array(0=>array());
    }
    // reorganize the options for easier reference
    $ret = array();
    foreach ($parsed[0] as $k=>$v) {
      $kk = str_replace('-','_',trim(preg_replace('/^-{1,2}/','',$v[0])));
      $ret[$kk] = trim($v[1]);
    }
    return $ret;
  }

  public function translate_logging_value($r) {
    $l = array_value($r, 'log_level', $this->_default_config['log_level']);
    if ((string)((int)$l) != $l) {
      if (defined('LOG_LEVEL_'.(string)$l)) {
        $l = constant('LOG_LEVEL_'.(string)$l);
      } else {
        $l = $this->_default_config['log_level'];
      }
    }
    $r['log_level'] = $l;
    return $r;
  }
}

class IntegrationLogger {
  public static $log_labels = array(
                                LOG_LEVEL_DEBUG    => 'DEBUG',
                                LOG_LEVEL_INFO     => 'INFO',
                                LOG_LEVEL_NOTICE   => 'NOTICE',
                                LOG_LEVEL_WARN     => 'WARN',
                                LOG_LEVEL_ERROR    => 'ERROR',
                                LOG_LEVEL_CRITICAL => 'CRITICAL',
                              );
  public static $use_log_file = false;

  public static function log($msg, $lvl=LOG_LEVEL_INFO) {
    $cfg = IntegrationConfig::getInstance();
    $lvl = array_key_exists((int)$lvl, static::$log_labels) ? (int)$lvl : LOG_LEVEL_INFO;
    if ($lvl <= $cfg->config['log_level']) {
      $lt = array_value(static::$log_labels, $lvl, static::$log_labels[LOG_LEVEL_INFO]);
      if ($use_log_file) {
        error_log("$lt: $msg");
      } else {
        $date_str = date('YmdHis');
        echo "[$date_str] $lt: $msg\n";
      }
    }
  }
}
// set an alias for easier reference
class_alias('IntegrationLogger','IL');