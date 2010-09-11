<?php
# bluebird_config.php - Initial configuration for Drupal and CiviCRM settings
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-10
#

define('BLUEBIRD_CONFIG_FILE', '/etc/bluebird.ini');
$servername = $_SERVER['SERVER_NAME'];
$shortname = substr($servername, 0, strpos($servername, '.'));
$curdir = dirname(__FILE__);
$drupalroot = realpath($curdir."/../../");

$bbini = parse_ini_file(BLUEBIRD_CONFIG_FILE, true);

$dbhost = $bbini['global:db']['host'];
$dbuser = $bbini['global:db']['user'];
$dbpass = $bbini['global:db']['pass'];
$drupal_db_url = "mysql://$dbuser:$dbpass@$dbhost/senate_d_$shortname";
$civicrm_db_url = "mysql://$dbuser:$dbpass@$dbhost/senate_c_$shortname";

$bbconfig = array();
$bbconfig['servername'] = $servername;
$bbconfig['shortname'] = $shortname;
$bbconfig['drupal_db_url'] = $drupal_db_url;
$bbconfig['civicrm_db_url'] = $civicrm_db_url;
$bbconfig['drupal_root'] = $drupalroot;

