<?php
# settings.php - Drupal configuration file
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-10
# Revised: 2011-06-11
#
# This customized settings.php file takes advantage of the strict CRM
# hostname naming scheme that we have developed.  Each CRM instance is
# of the form <instanceName>.crm.nysenate.gov.  The <instanceName> maps
# indirectly to the databases that are used for that instance via the
# Bluebird configuration file.
#

require_once dirname(__FILE__).'/../../../civicrm/scripts/bluebird_config.php';

# Use Bluebird custom maintenance pages within our own custom theme.
$conf['maintenance_theme'] = 'Garland';

$bbconfig = get_bluebird_instance_config();
//echo '<pre>';print_r($bbconfig);echo '</pre>';

if ($bbconfig == null) {
  $GLOBALS['maintenance_message'] = "<br/>There is no such CRM instance:<br/><br/>".$_SERVER['HTTP_HOST'];
  // The LANGUAGE bootstrap has not yet happened, so the $language global is
  // not set.  Set it here in order to avoid warnings in the logs.
  global $language;
  $language = language_default();
  drupal_maintenance_theme();
  drupal_site_offline();
  exit(1);
}

//deprecated d6 connection method
//$db_url = $bbconfig['drupal_db_url'];
//$db_prefix = '';

//new d7 method for setting db connection
$databases = array (
  'default' =>
  array (
    'default' =>
    array (
      'driver'   => 'mysql',
      'database' => $bbconfig['db.drupal.prefix'].$bbconfig['db.basename'],
      'username' => $bbconfig['db.user'],
      'password' => $bbconfig['db.pass'],
      'host'     => $bbconfig['db.host'],
      'port'     => '',
      'prefix'   => '',
    ),
  ),
);

$drupal_hash_salt = 'Pq2DVthfEKZp4OMhWgx4tMEs6OfPxQo4Zts1ypJ_rgM';

$update_free_access = TRUE;//LCD

ini_set('arg_separator.output',     '&amp;');
ini_set('magic_quotes_runtime',     0);
ini_set('magic_quotes_sybase',      0);
ini_set('session.cache_expire',     200000);
ini_set('session.cache_limiter',    'none');
ini_set('session.cookie_lifetime',  0); //when browser closes
ini_set('session.gc_maxlifetime',   14400); //4hrs
ini_set('session.gc_probability',   1);
ini_set('session.gc_divisor',       100);
ini_set('session.save_handler',     'user');
ini_set('session.use_cookies',      1);
ini_set('session.use_only_cookies', 1);
ini_set('session.use_trans_sid',    0);
ini_set('url_rewriter.tags',        '');

# ini_set('pcre.backtrack_limit', 200000);
# ini_set('pcre.recursion_limit', 200000);

$cookie_domain = $bbconfig['servername'];

# $conf = array(
#   'site_name' => 'My Drupal site',
#   'theme_default' => 'minnelli',
#   'anonymous' => 'Visitor',
#   'maintenance_theme' => 'minnelli',
#   'reverse_proxy' => TRUE,
#   'reverse_proxy_addresses' => array('a.b.c.d', ...),
# );

# $conf['locale_custom_strings_en'] = array(
#   'forum'      => 'Discussion board',
#   '@count min' => '@count minutes',
# );

$GLOBALS['simpletest_installed'] = TRUE;
if (preg_match("/^simpletest\d+$/", $_SERVER['HTTP_USER_AGENT'])) {
  $db_prefix = $_SERVER['HTTP_USER_AGENT'];
}

# Cacherouter: Try to use APC for all local caching
$cache_engine = 'db';
if (function_exists("apc_fetch")) {
  $cache_engine = 'apc';
}
$conf['cache_inc'] = './sites/all/modules/cacherouter/cacherouter.inc';
$conf['cacherouter'] = array(
  'default' => array(
    'engine' => $cache_engine,
    'shared' => FALSE,
    'prefix' => $bbconfig['servername'],
    'static' => FALSE,
    'fast_cache' => TRUE,
  ),
);

# Varnish reverse proxy on localhost
$conf['reverse_proxy'] = TRUE;           
$conf['reverse_proxy_addresses'] = array('127.0.0.1');

/**
 *
 * IP blocking:
 *
 * To bypass database queries for denied IP addresses, use this setting.
 * Drupal queries the {blocked_ips} table by default on every page request
 * for both authenticated and anonymous users. This allows the system to
 * block IP addresses from within the administrative interface and before any
 * modules are loaded. However on high traffic websites you may want to avoid
 * this query, allowing you to bypass database access altogether for anonymous
 * users under certain caching configurations.
 *
 * If using this setting, you will need to add back any IP addresses which
 * you may have blocked via the administrative interface. Each element of this
 * array represents a blocked IP address. Uncommenting the array and leaving it
 * empty will have the effect of disabling IP blocking on your site.
 *
 * Remove the leading hash signs to enable.
 */
$conf['blocked_ips'] = array(
  'a.b.c.d',
);
