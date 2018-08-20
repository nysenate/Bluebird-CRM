<?php
# civicrm.settings.php - CiviCRM configuration file
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-10
# Revised: 2016-10-18 - added imageUpload{Dir,URL}; removed unused params
#
# This customized civicrm.settings.php file takes advantage of the strict
# CRM hostname naming scheme that we have developed.  Each CRM instance is
# of the form <instanceName>.crm.nysenate.gov.  The <instanceName> maps
# indirectly to the databases that are used for that instance via the
# Bluebird configuration file.
#

require_once dirname(__FILE__).'/../../../civicrm/scripts/bluebird_config.php';
global $civicrm_root;
global $civicrm_setting;

$bbconfig = get_bluebird_instance_config();

if ($bbconfig == null) {
  die("Unable to properly bootstrap the CiviCRM module.\n");
}

$servername = get_config_value($bbconfig, 'servername', null);
$shortname = get_config_value($bbconfig, 'shortname', null);
$approot = get_config_value($bbconfig, 'app.rootdir', null);
$drupalroot = get_config_value($bbconfig, 'drupal.rootdir', null);
$dataroot = get_config_value($bbconfig, 'data.rootdir', null);

if (!$servername || !$shortname || !$approot || !$drupalroot || !$dataroot) {
  die("Incorrect config; check these settings: servername, shortname, app.rootdir, drupal.rootdir, data.rootdir.\n");
}

$datadirname = get_config_value($bbconfig, 'data.dirname', $shortname);
$installclass = get_config_value($bbconfig, 'install_class', 'production');

if ($installclass == 'dev') {
  //define('CIVICRM_DEBUG_LOG_QUERY', true);
}

define('CIVICRM_UF', 'Drupal');
define('CIVICRM_DSN', $bbconfig['civicrm_db_url'].'?new_link=true');
define('CIVICRM_UF_DSN', $bbconfig['drupal_db_url'].'?new_link=true');
define('CIVICRM_LOGGING_DSN', $bbconfig['log_db_url'].'?new_link=true');

$civicrm_root = "$drupalroot/sites/all/modules/civicrm";
define('CIVICRM_TEMPLATE_COMPILEDIR', "$dataroot/$datadirname/civicrm/templates_c");
define('CIVICRM_UF_BASEURL', "http://$servername/");
define('CIVICRM_SITE_KEY', get_config_value($bbconfig, 'site.key', '32425kj24h5kjh24542kjh524'));

define('CIVICRM_DOMAIN_ID', 1);
// define('CIVICRM_MAIL_LOG', '%%templateCompileDir%%/mail.log');
define('CIVICRM_TAG_UNCONFIRMED', 'Unconfirmed');
define('CIVICRM_PETITION_CONTACTS','Petition Contacts');

// Cache-related constants
define('CIVICRM_DB_CACHE_CLASS', get_config_value($bbconfig, 'cache.db.class', null));
define('CIVICRM_MEMCACHE_TIMEOUT', get_config_value($bbconfig, 'cache.memcache.timeout', 600));
define('CIVICRM_MEMCACHE_PREFIX', $servername);

// SAGE API constants
define('SAGE_API_KEY', get_config_value($bbconfig, 'sage.api.key', 'NO_KEY'));
define('SAGE_API_BASE', get_config_value($bbconfig, 'sage.api.base', 'NO_API'));


//temporary debugging statements
//CRM_Core_Error::debug_var('bbconfig', $bbconfig);
//CRM_Core_Error::debug_var('civicrm_root', $civicrm_root);
//CRM_Core_Error::debug_var('civicrm_setting', $civicrm_setting);

$civicrm_setting['Mailing Preferences']['profile_double_optin'] = false;
$civicrm_setting['Mailing Preferences']['profile_add_to_group_double_optin'] = false;
$civicrm_setting['Mailing Preferences']['track_civimail_replies'] = false;
//$civicrm_setting['Mailing Preferences']['civimail_workflow'] = true; //TODO support with Mosaico
$civicrm_setting['Mailing Preferences']['civimail_server_wide_lock'] = true;
$civicrm_setting['Mailing Preferences']['civimail_multiple_bulk_emails'] = true;
$civicrm_setting['Mailing Preferences']['include_message_id'] = true;
$civicrm_setting['Mailing Preferences']['write_activity_record'] = false;
$civicrm_setting['Mailing Preferences']['disable_mandatory_tokens_check'] = true;
$civicrm_setting['Mailing Preferences']['hash_mailing_url'] = true;
$civicrm_setting['Mailing Preferences']['auto_recipient_rebuild'] = false;

$civicrm_setting['CiviCRM Preferences']['checksumTimeout'] = 7;
$civicrm_setting['CiviCRM Preferences']['checksum_timeout'] = 7;
$civicrm_setting['CiviCRM Preferences']['securityAlert'] = false;
$civicrm_setting['CiviCRM Preferences']['versionCheck'] = false;
$civicrm_setting['CiviCRM Preferences']['max_attachments'] = 5;
$civicrm_setting['CiviCRM Preferences']['maxFileSize'] = 12; //9842
$civicrm_setting['CiviCRM Preferences']['communityMessagesUrl'] = false;
$civicrm_setting['CiviCRM Preferences']['empoweredBy'] = false;
$civicrm_setting['CiviCRM Preferences']['syncCMSEmail'] = false;
$civicrm_setting['CiviCRM Preferences']['communityMessagesUrl'] = false;
$civicrm_setting['CiviCRM Preferences']['remote_profile_submissions'] = false;
$civicrm_setting['CiviCRM Preferences']['recentItemsMaxCount'] = 10;
$civicrm_setting['CiviCRM Preferences']['smart_group_cache_refresh_mode'] = 'deterministic';
$civicrm_setting['CiviCRM Preferences']['smartGroupCacheTimeout'] = 10;

$civicrm_setting['Directory Preferences']['customTemplateDir'] = "$approot/civicrm/custom/templates";
$civicrm_setting['Directory Preferences']['customPHPPathDir'] = "$approot/civicrm/custom/php";
$civicrm_setting['Directory Preferences']['extensionsDir'] = "$approot/civicrm/custom/ext";
$civicrm_setting['Directory Preferences']['imageUploadDir'] = "$dataroot/$datadirname/pubfiles";

$civicrm_setting['URL Preferences']['imageUploadURL'] = "data/$datadirname/pubfiles";
$civicrm_setting['URL Preferences']['extensionsURL'] = "sites/all/ext";

//reference value separator explicitly as class constant not yet available
$sep = "";
$civicrm_setting['Search Preferences']['enable_innodb_fts'] = true;
$civicrm_setting['Search Preferences']['fts_query_mode'] = 'wildwords-suffix';
$civicrm_setting['Search Preferences']['includeEmailInName'] = true;
//11087 //TODO this should be false; messes up search return count
$civicrm_setting['Search Preferences']['searchPrimaryDetailsOnly'] = true;
$civicrm_setting['Search Preferences']['search_autocomplete_count'] = "15";
$civicrm_setting['Search Preferences']['contact_autocomplete_options'] =
  "{$sep}1{$sep}2{$sep}3{$sep}4{$sep}5{$sep}8{$sep}9{$sep}";
$civicrm_setting['Search Preferences']['contact_reference_options'] =
  "{$sep}1{$sep}2{$sep}3{$sep}4{$sep}5{$sep}8{$sep}9{$sep}";

//display preferences
$civicrm_setting['Display Preferences']['advanced_search_options'] =
  "{$sep}1{$sep}2{$sep}3{$sep}4{$sep}5{$sep}6{$sep}10{$sep}13{$sep}16{$sep}17{$sep}18{$sep}19{$sep}";

$civicrm_setting['Extension Preferences']['ext_repo_url'] = false;

if (isset($bbconfig['xhprof.profile']) && $bbconfig['xhprof.profile']) {
  function xhprof_shutdown_func($source, $run_id = null) {
    // Hopefully we don't throw an exception; there's no way to catch it now...
    $xhprof_data = xhprof_disable();

    // Check to see if the custom/civicrm/php path has been added to the path
    if (!stream_resolve_include_path("xhprof_lib/utils/xhprof_runs.php")) {
      return; // Can't do anything without this...
    }

    require_once "xhprof_lib/utils/xhprof_runs.php";

    // Save the run under a namespace "bluebird" with an autogenerated uid.
    // uid can also be supplied as a third optional parameter to save_run
    $xhprof_runs = new XHProfRuns_Default();

    // In case no run_id was passed in, set it now from the return value
    $run_id = $xhprof_runs->save_run($xhprof_data, $source, $run_id);

    //TODO: Make some sort of link to the profile output.
  }

  // Build the profiling flags based on configuration parameters
  $flags = 0;
  if (isset($bbconfig['xhprof.memory']) && $bbconfig['xhprof.memory']) {
    $flags += XHPROF_FLAGS_MEMORY;
  }
  if (isset($bbconfig['xhprof.cpu']) && $bbconfig['xhprof.cpu']) {
    $flags += XHPROF_FLAGS_CPU;
  }
  if (!isset($bbconfig['xhprof.builtins']) || !$bbconfig['xhprof.builtins']) {
    $flags += XHPROF_FLAGS_NO_BUILTINS;
  }

  // Build the ignore list based on configuration parameters
  $ignored_functions = array();
  if (isset($bbconfig['xhprof.ignore']) && $bbconfig['xhprof.ignore']) {
    $ignored_functions = $bbconfig['xhprof.ignore'];
  }

  xhprof_enable($flags, array('ignored_functions' => $ignored_functions));
  register_shutdown_function('xhprof_shutdown_func', "{$installclass}_{$shortname}", null);
}


/**
 *
 * Do not change anything below this line. Keep as is
 *
 */

$include_path = '.'.PATH_SEPARATOR.$civicrm_root.PATH_SEPARATOR.
                $civicrm_root.DIRECTORY_SEPARATOR.'packages'.PATH_SEPARATOR.
                get_include_path();
set_include_path($include_path);

if (function_exists('variable_get') && variable_get('clean_url', '0') != '0') {
    define('CIVICRM_CLEANURL', 1);
} else {
    define('CIVICRM_CLEANURL', 0);
}

// force PHP to auto-detect Mac line endings
ini_set('auto_detect_line_endings', '1');

// make sure the memory_limit is at least 64 MB
$memLimitString = trim(ini_get('memory_limit'));
$memLimitUnit   = strtolower(substr($memLimitString, -1));
$memLimit       = (int) $memLimitString;
switch ($memLimitUnit) {
    case 'g': $memLimit *= 1024;
    case 'm': $memLimit *= 1024;
    case 'k': $memLimit *= 1024;
}
if ($memLimit >= 0 and $memLimit < 67108864) {
    ini_set('memory_limit', '1000M');
}

require_once 'CRM/Core/ClassLoader.php';
CRM_Core_ClassLoader::singleton()->register();
