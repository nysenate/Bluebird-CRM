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
$conf['maintenance_theme'] = 'Bluebird';

$bbconfig = get_bluebird_instance_config();

if ($bbconfig == null) {
  $GLOBALS['maintenance_message'] = "<br/>There is no such CRM instance:<br/><br/>".$_SERVER['HTTP_HOST'];
  require_once DRUPAL_ROOT.'/includes/cache.inc';
  require_once DRUPAL_ROOT.'/includes/common.inc';
  require_once DRUPAL_ROOT.'/includes/lock.inc';
  require_once DRUPAL_ROOT.'/includes/menu.inc';
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

ini_set('arg_separator.output',     '&amp;');
ini_set('magic_quotes_runtime',     0);
ini_set('magic_quotes_sybase',      0);
ini_set('session.cache_expire',     200000);
ini_set('session.cache_limiter',    'none');
ini_set('session.cookie_lifetime',  0); //6741 rollback
ini_set('session.gc_maxlifetime',   360000); //10hrs
ini_set('session.gc_probability',   1);
ini_set('session.gc_divisor',       100);
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

# Try to use APC for all local caching
//$conf['cache_backends'] = array('sites/all/modules/apc/drupal_apc_cache.inc');
//$conf['cache_class_cache'] = 'DrupalAPCCache';
//$conf['cache_class_cache_bootstrap'] = 'DrupalAPCCache';
//$conf['apc_show_debug'] = TRUE;  // Remove the slashes to use debug mode.

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
