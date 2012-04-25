<?php

define('API_PREFIX', '/api/1.0/');
define('API_PREFIX_LEN', strlen(API_PREFIX));
define('SCRIPT_DIR', dirname(__FILE__).'/../../scripts');

$reqUri = $_SERVER['REQUEST_URI'];

// Only API 1.0 is current supported.
if (strncmp($reqUri, API_PREFIX, API_PREFIX_LEN) !== 0) {
  die("API call is not in correct format\n");
}

$apiCmd = substr($reqUri, API_PREFIX_LEN);

switch ($apiCmd) {
  case "getInstances":
    system(SCRIPT_DIR.'/iterateInstances.sh -q --live-fast');
    break;
  default:
    die("[$apiCmd]: Invalid API command\n");
}

?>
