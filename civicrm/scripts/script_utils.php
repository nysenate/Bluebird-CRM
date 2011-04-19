<?php
// Project: BluebirdCRM
// Author: Ken Zalewski
// Organization: New York State Senate
// Date: 201l-02-17
// Revised: 2011-02-18
//


function is_cli_script()
{
  return php_sapi_name() == "cli";
} // is_cli_script()



function civicrm_script_init($shopts = "", $longopts = array())
{
  // Determine if script is running from command line, or from web server.

  $myopts = array();
  $force_auth = true;

  if (is_cli_script()) {
    // Script is being run from the command line.
    $force_auth = false;
    $myopts = civicrm_script_init_cli($shopts, $longopts);
  }
  else {
    // Script is being run from the web server.
    $force_auth = true;
    $myopts = civicrm_script_init_http($longopts);
  }

  if ($myopts) {
    require_once dirname(__FILE__).'/../core/civicrm.config.php';

    // If running from web server, or if a username was provided, then
    // authenticate the user.  This allows us to run anonymously from the CLI.
    if ($force_auth || $myopts['user']) {
      require_once 'CRM/Utils/System.php';
      CRM_Utils_System::authenticateScript(true, $myopts['user'], $myopts['pass']);
    }
  }
  return $myopts;
} // civicrm_script_init()



function civicrm_script_init_cli($shopts, $longopts)
{
  $civiroot = add_packages_to_include_path();

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
  $_SERVER['HTTP_HOST'] = $myopts['site'];
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



function add_packages_to_include_path()
{
  $civiroot = realpath(dirname(__FILE__).'/..');
  set_include_path("$civiroot/packages".PATH_SEPARATOR.get_include_path());
  session_start();
  return $civiroot;
} // add_packages_to_include_path()



function process_cli_args($shopts, $longopts)
{
  $shoptlets = str_replace(":", "", $shopts);

  if (strlen($shoptlets) != count($longopts)) {
    error_log("Number of short options and long options must match.");
    return null;
  }

  require_once 'Console/Getopt.php';
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
    if (substr($longopt, -1) == "=") {
      $has_arg = true;
      $longopt = rtrim($longopt, "=");
    }
    $optlist[$longopt] = null;
    foreach ($opts as $v) {
      if ($v[0] == '--'.$longopt || $v[0] == $shortopt) {
        $optlist[$longopt] = ($has_arg) ? $v[1] : true;
        break;
      }
    }
  }

  $optlist['nonopts'] = $nonopts;
  return $optlist;
} // process_cli_args()



function process_url_args($longopts)
{
  $optlist = array();
  foreach ($longopts as $longopt) {
    $has_arg = false;
    if (substr($longopt, -1) == "=") {
      $has_arg = true;
      $longopt = rtrim($longopt, "=");
    }
    $optlist[$longopt] = null;
    if (isset($_REQUEST[$longopt])) {
      $optlist[$longopt] = ($has_arg) ? $_REQUEST[$longopt] : true;
    }
  }
  return $optlist;
} // process_url_args()



function civicrm_script_usage()
{
  $usage = "[--user|-U username]  [--pass|-P password]";
  if (is_cli_script()) {
    return "--site|-S site  [--key|-K key]  ".$usage;
  }
  else {
    return $usage;
  }
} // civicrm_script_usage()



function get_elapsed_time($start_time = 0)
{
  return microtime(true) - $start_time;
} // get_elapsed_time()
