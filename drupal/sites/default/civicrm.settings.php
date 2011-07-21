<?php
# civicrm.settings.php - CiviCRM configuration file
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-10
# Revised: 2011-06-06
#
# This customized civicrm.settings.php file takes advantage of the strict
# CRM hostname naming scheme that we have developed.  Each CRM instance is
# of the form <instanceName>.crm.nysenate.gov.  The <instanceName> maps
# indirectly to the databases that are used for that instance via the
# Bluebird configuration file.
#


require_once dirname(__FILE__).'/../../../civicrm/scripts/bluebird_config.php';

$bbconfig = get_bluebird_instance_config();

if ($bbconfig == null) {
  die("Unable to properly bootstrap the CiviCRM module.\n");
}

define('CIVICRM_UF', 'Drupal');
define('CIVICRM_USE_MEMCACHE', $bbconfig['cache.memcache']);
define('CIVICRM_MEMCACHE_TIMEOUT', $bbconfig['cache.memcache.timeout']);
define('CIVICRM_MEMCACHE_PREFIX', $bbconfig['serverhost']);

define('CIVICRM_USE_ARRAYCACHE', $bbconfig['cache.arraycache']);
define('CIVICRM_DSN', $bbconfig['civicrm_db_url'].'?new_link=true');
define('CIVICRM_UF_DSN', $bbconfig['drupal_db_url'].'?new_link=true');

global $civicrm_root;

$civicrm_root = $bbconfig['drupal.rootdir'].'/sites/all/modules/civicrm';
define('CIVICRM_TEMPLATE_COMPILEDIR', $bbconfig['data.rootdir'].'/'.$bbconfig['data_dirname'].'/civicrm/templates_c');
define('CIVICRM_UF_BASEURL', 'http://'.$bbconfig['servername'].'/');
define('CIVICRM_SITE_KEY', '32425kj24h5kjh24542kjh524');

//define('CIVICRM_MULTISITE', null);
//define('CIVICRM_UNIQ_EMAIL_PER_SITE', null);
define('CIVICRM_DOMAIN_ID', 1);
define('CIVICRM_DOMAIN_GROUP_ID', null);
define('CIVICRM_DOMAIN_ORG_ID', null);
define('CIVICRM_EVENT_PRICE_SET_DOMAIN_ID', 0 );

define('CIVICRM_ACTIVITY_ASSIGNEE_MAIL' , 1 );
define('CIVICRM_CONTACT_AJAX_CHECK_SIMILAR' , 1 );
define('CIVICRM_PROFILE_DOUBLE_OPTIN', 1 );
define('CIVICRM_TRACK_CIVIMAIL_REPLIES', false);
// define( 'CIVICRM_MAIL_LOG', '%%templateCompileDir%%/mail.log' );
define('CIVICRM_TAG_UNCONFIRMED', 'Unconfirmed');
define('CIVICRM_PETITION_CONTACTS','Petition Contacts');
define('CIVICRM_CIVIMAIL_WORKFLOW', 1 );


/**
 *
 * Do not change anything below this line. Keep as is
 *
 */

$include_path = '.'.PATH_SEPARATOR.$civicrm_root.PATH_SEPARATOR.
                $civicrm_root.DIRECTORY_SEPARATOR.'packages'.PATH_SEPARATOR.
                get_include_path( );
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

