<?php

require_once 'bluebird_config.php';

$bbconfig = get_bluebird_config('bluebird.cfg');

define('CIVICRM_UF', 'Drupal');
define('CIVICRM_USE_MEMCACHE', 0);
define('CIVICRM_DSN', $bbconfig['civicrm_db_url'].'?new_link=true');
define('CIVICRM_UF_DSN', $bbconfig['drupal_db_url'].'?new_link=true');

global $civicrm_root;

$civicrm_root = $bbconfig['drupal_root'].'/sites/all/modules/civicrm';
define('CIVICRM_TEMPLATE_COMPILEDIR', $bbconfig['data_rootdir'].'/'.$bbconfig['servername'].'/civicrm/templates_c');
define('CIVICRM_UF_BASEURL', 'http://'.$bbconfig['servername'].'/');
define('CIVICRM_SITE_KEY', '32425kj24h5kjh24542kjh524');

//define('CIVICRM_MULTISITE', null);
//define('CIVICRM_UNIQ_EMAIL_PER_SITE', null);
define('CIVICRM_DOMAIN_ID', 1);
define('CIVICRM_DOMAIN_GROUP_ID', null);
define('CIVICRM_DOMAIN_ORG_ID', null);
define('CIVICRM_EVENT_PRICE_SET_DOMAIN_ID', 0 );

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

