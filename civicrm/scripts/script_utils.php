<?php
// Project: BluebirdCRM
// Author: Ken Zalewski
// Organization: New York State Senate
// Date: 2011-02-17
// Revised: 2012-12-10
//

define('SCRIPT_UTILS_CIVIROOT', realpath(dirname(__FILE__).'/../core'));


function is_cli_script()
{
  return php_sapi_name() == "cli";
} // is_cli_script()



function drupal_script_init()
{
  define('DRUPAL_ROOT', realpath(__DIR__.'/../../drupal'));
  $oldwd = getcwd();
  chdir(DRUPAL_ROOT);
  $_SERVER['REQUEST_METHOD'] = 'GET';
  $_SERVER['HTTP_USER_AGENT'] = 'Terminal';
  $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
  require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
  drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
  chdir($oldwd);
} // drupal_script_init()


function civicrm_script_init($shopts = "", $longopts = array(), $session = true)
{
  // Determine if script is running from command line, or from web server.

  $myopts = array();
  $force_auth = true;

  if (is_cli_script()) {
    // Script is being run from the command line.
    $force_auth = false;
    $myopts = civicrm_script_init_cli($shopts, $longopts, $session);
  }
  else {
    // Script is being run from the web server.
    $force_auth = true;
    $myopts = civicrm_script_init_http($longopts);
  }
  if ($myopts) {
    require_once SCRIPT_UTILS_CIVIROOT.'/civicrm.config.php';
    // If running from web server, or if a username was provided, then
    // authenticate the user.  This allows us to run anonymously from the CLI.
    if ($force_auth || $myopts['user']) {
      CRM_Utils_System::authenticateScript(true, $myopts['user'], $myopts['pass']);
    }
  }
  return $myopts;
} // civicrm_script_init()



function civicrm_script_init_cli($shopts, $longopts, $session = true)
{
  $old_incpath = add_packages_to_include_path($session);
  if ($old_incpath === false) {
    error_log("Unable to set the script include_path.");
    return null;
  }

  // When running from the CLI, SITE is required.
  $shopts .= "U:P:K:S:";
  $longopts = array_merge($longopts, array("user=", "pass=", "key=", "site="));
  $myopts = process_cli_args($shopts, $longopts);
  if ($myopts) {
    if (!$myopts['site']) {
      error_log("Must specify site (--site or -S) when running from the command line.");
      return null;
    }
  }
  else {
    error_log("Unable to process command line arguments.");
    return null;
  }

  // Set up execution environment to mimic being called from the web server.
  // Must set the HTTP_HOST before calling civicrm.config.php.
  $_SERVER['PHP_SELF'] = "/index.php";
  $_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'] = $myopts['site'];
  $_SERVER['SCRIPT_FILENAME'] = __FILE__;
  $_REQUEST['key'] = $myopts['key'];

  return $myopts;
} // civicrm_script_init_cli()



function civicrm_script_init_http($longopts)
{
  // In the HTTP request, look for the provided options, plus User and Pass.
  // Do not look for Site, since it is implied by the virtual host.
  $longopts = array_merge($longopts, array("user=", "pass="));
  return process_url_args($longopts);
} // civicrm_script_init_http()



function add_packages_to_include_path($session = true)
{
  $old_incpath = set_include_path(SCRIPT_UTILS_CIVIROOT."/packages".PATH_SEPARATOR.get_include_path());
  $old_incpath = set_include_path(SCRIPT_UTILS_CIVIROOT."/vendor".PATH_SEPARATOR.get_include_path());

  if ($session) {
    session_start();
  }

  return $old_incpath;
} // add_packages_to_include_path()



function add_scripts_to_include_path($session = true)
{
  $old_incpath = set_include_path(dirname(__FILE__).PATH_SEPARATOR.get_include_path());
  if ($session) {
    session_start();
  }
  return $old_incpath;
} // add_scripts_to_include_path()



function process_cli_args($shopts, $longopts, $keepnulls = true)
{
  $shoptlets = str_replace(':', '', $shopts);

  if (strlen($shoptlets) != count($longopts)) {
    error_log('Number of short options and long options must match.');
    return null;
  }

  //require_once 'Console/Getopt.php';
  require_once SCRIPT_UTILS_CIVIROOT.'/vendor/pear/pear-core-minimal/src/PEAR.php';
  require_once __DIR__.'/libs/Getopt.php';

  $getopt = new Console_Getopt();
  $args = $getopt->readPHPArgv();
  array_shift($args);
  $rc = $getopt->getopt2($args, $shopts, $longopts);

  if (PEAR::isError($rc)) {
    error_log($rc->message);
    return null;
  }

  list($opts, $nonopts) = $rc;

  $optlist = array();
  for ($i = 0; $i < count($longopts); $i++) {
    $longopt = $longopts[$i];
    $shortopt = $shoptlets[$i];
    $has_arg = false;
    if (substr($longopt, -1) == '=') {
      $has_arg = true;
      $longopt = rtrim($longopt, '=');
    }
    $optlist[$longopt] = null;
    foreach ($opts as $v) {
      if ($v[0] == '--'.$longopt || $v[0] == $shortopt) {
        $optlist[$longopt] = ($has_arg) ? $v[1] : true;
        break;
      }
    }
    if ($optlist[$longopt] === null && !$keepnulls) {
      unset($optlist[$longopt]);
    }
  }

  $optlist['nonopts'] = $nonopts;
  return $optlist;
} // process_cli_args()



function process_url_args($longopts, $keepnulls = true)
{
  $optlist = array();
  foreach ($longopts as $longopt) {
    $has_arg = false;
    if (substr($longopt, -1) == '=') {
      $has_arg = true;
      $longopt = rtrim($longopt, '=');
    }
    $optlist[$longopt] = null;
    if (isset($_REQUEST[$longopt])) {
      $optlist[$longopt] = ($has_arg) ? $_REQUEST[$longopt] : true;
    }
    if ($optlist[$longopt] === null && !$keepnulls) {
      unset($optlist[$longopt]);
    }
  }
  return $optlist;
} // process_url_args()



function civicrm_script_usage()
{
  $usage = '[--user|-U username]  [--pass|-P password]';
  if (is_cli_script()) {
    return '--site|-S site  [--key|-K key]  '.$usage;
  }
  else {
    return $usage;
  }
} // civicrm_script_usage()


function get_elapsed_time($start_time = 0)
{
  return microtime(true) - $start_time;
} // get_elapsed_time()


abstract class LogLevel
{
  const FATAL = 0;
  const ERROR = 1;
  const WARN = 2;
  const NOTICE = 3;
  const INFO = 4;
  const DEBUG = 5;
  const TRACE = 6;
}

class_alias('LogLevel', 'LL', false);


$GLOBALS['bbscript_log_level'] = LL::NOTICE;


function set_bbscript_log_level($lvl)
{
  global $bbscript_log_level;

  if (is_numeric($lvl)) {
    $bbscript_log_level = (int)$lvl;
  }
  else {
    $new_level = @constant('LL::'.$lvl);
    if ($new_level !== null) {
      $bbscript_log_level = $new_level;
    }
    else {
      bbscript_log(LL::ERROR, "Invalid log level [$lvl] specified");
    }
  }
} // set_bbscript_log_level()


function get_bbscript_log_level()
{
  global $bbscript_log_level;
  return $bbscript_log_level;
} // get_bbscript_log_level()


function bbscript_log($lvl, $msg, $var = null)
{
  global $bbscript_log_level;

  static $log_levels = [
    LL::FATAL  => ['FATAL', 35],  /* purple */
    LL::ERROR  => ['ERROR', 31],  /* red */
    LL::WARN   => ['WARN', 33],   /* yellow */
    LL::NOTICE => ['NOTICE', 34], /* blue */
    LL::INFO   => ['INFO', 32],   /* green */
    LL::DEBUG  => ['DEBUG', 36],  /* cyan */
    LL::TRACE  => ['TRACE', 30]   /* grey */
  ];

  if ($lvl <= $bbscript_log_level) {
    if (!isset($log_levels[$lvl])) {
      $lvl = LL::TRACE;
    }
    list($lvl_text, $color) = $log_levels[$lvl];
    $timestamp = date('YmdHis.').(int)(gettimeofday()['usec'] / 1000);
    $lvl_text = "[\33[1;{$color}m".$lvl_text."\33[0m]";
    // Extra large padding to account for color strings!
    echo sprintf("%s %-20s %s\n", $timestamp, $lvl_text, $msg);

    if ($var !== null) {
      if (is_array($var) || is_object($var)) {
        print_r($var);
      }
      else {
        echo "$var\n";
      }
    }
  }
} // bbscript_log()


function bb_mysql_query($query, $db, $exit_on_fail = false)
{
  $result = mysqli_query($db, $query);
  if ($result === false) {
    if ($exit_on_fail) {
      bbscript_log(LL::FATAL, "MySQL Fatal Error: ".mysqli_error($db));
      bbscript_log(LL::FATAL, "Caused by:\n$query");
      bbscript_log(LL::FATAL, "Exiting the script immediately");
      exit(1);
    }
    else {
      bbscript_log(LL::ERROR, "MySQL Error: ".mysqli_error($db));
      bbscript_log(LL::ERROR, "Caused by:\n$query");
    }
  }
  return $result;
} // bb_mysql_query()
