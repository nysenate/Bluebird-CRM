<?php
# civicrm.settings.php - CiviCRM configuration file
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-10
# Revised: 2016-10-18 - add imageUpload{Dir,URL}; removed unused params
# Revised: 2019-02-28 - add more CiviCRM settings
# Revised: 2019-05-23 - add more settings and reorganize
#
# This customized civicrm.settings.php file takes advantage of the strict
# CRM hostname naming scheme that we have developed.  Each CRM instance is
# of the form <instanceName>.crm.nysenate.gov.  The <instanceName> maps
# indirectly to the databases that are used for that instance via the
# Bluebird configuration file.
#

require_once dirname(__FILE__).'/../../../civicrm/scripts/bluebird_config.php';

// Both of these globals must be here.
global $civicrm_setting;
global $civicrm_root;

$bbcfg = get_bluebird_instance_config();

if ($bbcfg == null) {
  die("Unable to properly bootstrap the CiviCRM module.\n");
}

$servername = get_config_value($bbcfg, 'servername', null);
$shortname = get_config_value($bbcfg, 'shortname', null);
$approot = get_config_value($bbcfg, 'app.rootdir', null);
$drupalroot = get_config_value($bbcfg, 'drupal.rootdir', null);
$dataroot = get_config_value($bbcfg, 'data.rootdir', null);

if (!$servername || !$shortname || !$approot || !$drupalroot || !$dataroot) {
  die("Incorrect config; check these settings: servername, shortname, app.rootdir, drupal.rootdir, data.rootdir\n");
}

$datadirname = get_config_value($bbcfg, 'data_dirname', $shortname);
$installclass = get_config_value($bbcfg, 'install_class', 'production');
$civicrm_root = "$drupalroot/sites/all/modules/civicrm";

if ($installclass == 'dev') {
  //define('CIVICRM_DEBUG_LOG_QUERY', true);
}

// Set the correct include path and memory limit.
nyss_bootstrap_settings($civicrm_root);

define('CIVICRM_UF', 'Drupal');
define('CIVICRM_DSN', $bbcfg['civicrm_db_url'].'?new_link=true');
define('CIVICRM_UF_DSN', $bbcfg['drupal_db_url'].'?new_link=true');
define('CIVICRM_LOGGING_DSN', $bbcfg['log_db_url'].'?new_link=true');

define('CIVICRM_TEMPLATE_COMPILEDIR', "$dataroot/$datadirname/civicrm/templates_c");
define('CIVICRM_UF_BASEURL', "http://$servername/");
define('CIVICRM_SITE_KEY', get_config_value($bbcfg, 'site.key', '32425kj24h5kjh24542kjh524'));

define('CIVICRM_DOMAIN_ID', 1);
// define('CIVICRM_MAIL_LOG', '%%templateCompileDir%%/mail.log');
define('CIVICRM_TAG_UNCONFIRMED', 'Unconfirmed');
define('CIVICRM_PETITION_CONTACTS', 'Petition Contacts');

// Cache-related constants
define('CIVICRM_DB_CACHE_CLASS', get_config_value($bbcfg, 'cache.db.class', null));
define('CIVICRM_MEMCACHE_TIMEOUT', get_config_value($bbcfg, 'cache.memcache.timeout', 600));
define('CIVICRM_MEMCACHE_PREFIX', $servername);

// SAGE API constants
define('SAGE_API_KEY', get_config_value($bbcfg, 'sage.api.key', 'NO_KEY'));
define('SAGE_API_BASE', get_config_value($bbcfg, 'sage.api.base', 'NO_API'));

//reference value separator explicitly as class constant not yet available
define('SEP', "");


//temporary debugging statements
//CRM_Core_Error::debug_var('bbcfg', $bbcfg);
//CRM_Core_Error::debug_var('civicrm_root', $civicrm_root);
//CRM_Core_Error::debug_var('civicrm_setting', $civicrm_setting);

// Array shortcuts
$prefsCore = &$civicrm_setting['CiviCRM Preferences'];
$prefsCase = &$civicrm_setting['CiviCRM Preferences'];
$prefsDir = &$civicrm_setting['Directory Preferences'];
$prefsExt = &$civicrm_setting['Extension Preferences'];
$prefsMail = &$civicrm_setting['Mailing Preferences'];
$prefsMap = &$civicrm_setting['Map Preferences'];
$prefsSearch = &$civicrm_setting['Search Preferences'];
$prefsUrl = &$civicrm_setting['URL Preferences'];
$prefsReportError = &$civicrm_setting['reporterror'];

// Core settings, from Core.setting.php
// contact_view_options, contact_edit_options
// user_dashboard_options
// address_options, address_format
// mailing_format, display_name_format, sort_name_format
// editor_id
// contact_ajax_check_similar, ajaxPopupsEnabled
// activity_assignee_notification, activity_assignee_notification_ics
// contact_smart_group_display
// installed
// contact_undelete, allowPermDeleteFinancial
// doNotAttachPDFReceipt
// recordGeneratedLetters
// recaptchaOptions, recaptchaPublicKey, recaptchaPrivateKey
// blogUrl, gettingStartedUrl
// resCacheCode
// verifySSL, enableSSL
// wpBasePage, wpLoadPhp
// secondDegRelPermissions
// disable_core_css
// logging_no_trigger_permission
// logging, logging_uniqueid_date, logging_all_tables_uniquid
// userFrameworkUsersTableName
// secure_cache_timeout_minutes
// site_id
// systemStatusCheckResult
// recentItemsProviders
// dedupe_default_limit
// preserve_activity_tab_filter
// do_not_notify_assignees_for
$prefsCore['advanced_search_options'] = SEP.implode(SEP, [1,2,3,4,5,6,10,13,16,17,18,19]).SEP;
$prefsCore['checksum_timeout'] = 7;
$prefsCore['communityMessagesUrl'] = false;
$prefsCore['contact_autocomplete_options'] = SEP.implode(SEP, [1,2,3,4,5,8,9]).SEP;
$prefsCore['contact_reference_options'] = SEP.implode(SEP, [1,2,3,4,5,8,9]).SEP;
$prefsCore['empoweredBy'] = false;
$prefsCore['enable_components'] = [ 'CiviMail', 'CiviCase', 'CiviReport' ];
$prefsCore['max_attachments'] = 5;
$prefsCore['maxFileSize'] = 12;
$prefsCore['recentItemsMaxCount'] = 10;
$prefsCore['remote_profile_submissions'] = false;
$prefsCore['securityAlert'] = false;
$prefsCore['smart_group_cache_refresh_mode'] = 'deterministic';
$prefsCore['syncCMSEmail'] = false;
$prefsCore['wkhtmltopdfPath'] = get_config_value($bbcfg, 'wkhtmltopdf.path', '/usr/local/bin/wkhtmltopdf');
$prefsCore['versionCheck'] = false;
$prefsCore['checksumTimeout'] = 7;
$prefsCore['menubar_color'] = '#DADAD2';
$prefsCore['ajaxPopupsEnabled'] = 1;

// Address settings, from Address.setting.php
// address_standardization_provider
// address_standardization_userid
// address_standardization_url
// hideCountryMailingLabels

// Campaign settings, from Campaign.setting.php
// tag_unconfirmed
// petition_contacts

// Case settings, from Case.setting.php
// civicaseRedactActivityEmail
// civicaseAllowMultipleClients
// civicaseNaturalActivityTypeSort
$prefsCase['civicaseActivityRevisions'] = true;

// Contribute settings, from Contribute.setting.php
// cvv_backoffice_required
// contribution_invoice_settings
// invoicing
// acl_financial_type
// deferred_revenue_enabled
// default_invoice_page
// always_post_to_accounts_receivable
// update_contribution_on_membership_type_change

// Developer settings, from Developer.setting.php
// assetCache
// userFrameworkLogging
// debug_enabled
// backtrace
// environment
// fatalErrorHandler

// Directory settings, from Directory.setting.php
$prefsDir['uploadDir'] = 'upload/';
$prefsDir['imageUploadDir'] = "$dataroot/$datadirname/pubfiles";
$prefsDir['customFileUploadDir'] = 'custom/';
$prefsDir['customTemplateDir'] = "$approot/civicrm/custom/templates";
$prefsDir['customPHPPathDir'] = "$approot/civicrm/custom/php";
$prefsDir['extensionsDir'] = "$approot/civicrm/custom/ext";

// Event settings, from Event.setting.php
// enable_cart
// show_events

// Extension settings, from Extension.setting.php
$prefsExt['ext_repo_url'] = false;

// Localization settings, from Localization.setting.php
// customTranslateFunction
// monetaryThousandSeparator, monetaryDecimalPoint
// moneyformat, moneyvalueformat
// defaultCurrency
// defaultContactCountry, defaultContactStateProvince
// countryLimit, provinceLimit
// inheritLocale
// dateformatDatetime
// dateformatFull, dateformatPartial
// dateformatTime, dateformatYear
// dateformatFinancialBatch
// dateformatshortdate
// dateInputFormat
// fieldSeparator
// fiscalYearStart
// lanaguageLimit
// lcMessages
// legacyEncoding
// timeInputFormat
// weekBegins
// contact_default_language

// Mailing settings, from Mailing.setting.php
$prefsMail['profile_double_optin'] = false;
$prefsMail['track_civimail_replies'] = false;
$prefsMail['civimail_workflow'] = true;
$prefsMail['civimail_server_wide_lock'] = true;
// replyTo
$prefsMail['mailing_backend'] = [
  'outBound_option' => CRM_Mailing_Config::OUTBOUND_OPTION_SMTP,
  'sendmail_path' => '',
  'sendmail_args' => '',
  'smtpServer' => get_config_value($bbcfg, 'smtp.host', 'localhost'),
  'smtpPort' => get_config_value($bbcfg, 'smtp.port', 25),
  'smtpAuth' => get_config_value($bbcfg, 'smtp.auth', 0),
  'smtpUsername' => get_config_value($bbcfg, 'smtp.username', ''),
  'smtpPassword' => CRM_Utils_Crypt::encrypt(get_config_value($bbcfg, 'smtp.password', ''))
];
$prefsMail['profile_add_to_group_double_optin'] = false;
$prefsMail['disable_mandatory_tokens_check'] = true;
// dedupe_email_default
$prefsMail['hash_mailing_url'] = true;
$prefsMail['civimail_multiple_bulk_emails'] = true;
$prefsMail['include_message_id'] = true;
$prefsMail['mailerBatchLimit'] = get_config_value($bbcfg, 'mailer.batch_limit', 1000);
$prefsMail['mailerJobSize'] = get_config_value($bbcfg, 'mailer.job_size', 1000);
$prefsMail['mailerJobsMax'] = get_config_value($bbcfg, 'mailer.jobs_max', 10);
// mailThrottleTime
// verpSeparator
$prefsMail['write_activity_record'] = false;
// simple_mail_limit
$prefsMail['auto_recipient_rebuild'] = false;
// allow_mail_from_logged_in_contact

// Map settings, from Map.setting.php
$prefsMap['geoProvider'] = get_config_value($bbcfg, 'geo.provider', 'SAGE');
$prefsMap['geoAPIKey'] = get_config_value($bbcfg, 'geo.api.key', '');
$prefsMap['mapProvider'] = get_config_value($bbcfg, 'map.provider', 'Google');
$prefsMap['mapAPIKey'] = get_config_value($bbcfg, 'map.api.key', '');

// Member settings, from Member.setting.php
// default_renewal_contribution_page

// Multisite settings, from Multisite.setting.php
// is_enabled
// domain_group_id
// event_price_set_domain_id
// uniq_email_per_site

// Search settings, from Search.setting.php
$prefsSearch['search_autocomplete_count'] = 15;
$prefsSearch['enable_innodb_fts'] = true;
$prefsSearch['fts_query_mode'] = 'wildwords-suffix';
// includeOrderByClause
$prefsSearch['includeWildCardInName'] = get_config_value($bbcfg, 'search.include_wildcard_in_name', false);
$prefsSearch['includeEmailInName'] = get_config_value($bbcfg, 'search.include_email_in_name', true);
// includeNickNameInName
// includeAlphabeticalPager
$prefsSearch['smartGroupCacheTimeout'] = 10;
// defaultSearchProfileID
$prefsSearch['searchPrimaryDetailsOnly'] = false;
$prefsSearch['quicksearch_options'] = ['sort_name', 'first_name', 'last_name', 'email', 'phone_numeric', 'street_address', 'city', 'postal_code'];

// URL settings, from Url.setting.php
$prefsUrl['userFrameworkResourceURL'] = 'sites/all/modules/civicrm/';
$prefsUrl['imageUploadURL'] = "data/$datadirname/pubfiles";
// customCSSURL
$prefsUrl['extensionsURL'] = 'sites/all/ext';

// Report Error extension settings
$prefsReportError['reporterror_mailto'] = 'zalewski@nysenate.gov,dev+nyss@lcdservices.biz';
$prefsReportError['reporterror_fromemail'] = '"Bluebird Error" <bluebird-no-reply@nysenate.gov>';
$prefsReportError['reporterror_show_full_backtrace'] = TRUE;
$prefsReportError['reporterror_show_post_data'] = TRUE;
$prefsReportError['reporterror_smartgroups_autodisable'] = TRUE;
$prefsReportError['reporterror_sendreport_profile'] = TRUE;
$prefsReportError['reporterror_handle_profile'] = TRUE;

if (isset($bbcfg['xhprof.profile']) && $bbcfg['xhprof.profile']) {
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
  if (isset($bbcfg['xhprof.memory']) && $bbcfg['xhprof.memory']) {
    $flags += XHPROF_FLAGS_MEMORY;
  }
  if (isset($bbcfg['xhprof.cpu']) && $bbcfg['xhprof.cpu']) {
    $flags += XHPROF_FLAGS_CPU;
  }
  if (!isset($bbcfg['xhprof.builtins']) || !$bbcfg['xhprof.builtins']) {
    $flags += XHPROF_FLAGS_NO_BUILTINS;
  }

  // Build the ignore list based on configuration parameters
  $ignored_functions = array();
  if (isset($bbcfg['xhprof.ignore']) && $bbcfg['xhprof.ignore']) {
    $ignored_functions = $bbcfg['xhprof.ignore'];
  }

  xhprof_enable($flags, array('ignored_functions' => $ignored_functions));
  register_shutdown_function('xhprof_shutdown_func', "{$installclass}_{$shortname}", null);
}


/**
 *
 * Do not change anything below this line.
 *
 */

function nyss_bootstrap_settings($rootdir)
{
  $include_path = '.'.PATH_SEPARATOR.$rootdir.PATH_SEPARATOR.
                  $rootdir.DIRECTORY_SEPARATOR.'packages'.PATH_SEPARATOR.
                  get_include_path();
  set_include_path($include_path);

  if (function_exists('variable_get') && variable_get('clean_url','0') != '0') {
    define('CIVICRM_CLEANURL', 1);
  }
  else {
    define('CIVICRM_CLEANURL', 0);
  }

  // force PHP to auto-detect Mac line endings
  ini_set('auto_detect_line_endings', '1');

  // Get the current PHP memory limit.
  $memLimitString = trim(ini_get('memory_limit'));
  $memLimitUnit   = strtolower(substr($memLimitString, -1));
  $memLimit       = (int) $memLimitString;

  switch ($memLimitUnit) {
    case 'g': $memLimit *= 1024;
    case 'm': $memLimit *= 1024;
    case 'k': $memLimit *= 1024;
  }

  // If the PHP memory limit is less than 64MB, then set it to 512MB
  if ($memLimit >= 0 and $memLimit < 67108864) {
    ini_set('memory_limit', '512M');
  }

  require_once 'CRM/Core/ClassLoader.php';
  CRM_Core_ClassLoader::singleton()->register();
} // nyss_bootstrap_settings()
