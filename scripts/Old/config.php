<?php

$base_dir = realpath(dirname(__FILE__).'/../');
$filename = "bluebird.cfg";

if (file_exists($base_dir."/$filename")) {
  $cfg_file = $base_dir."/$filename";
}
else if (($cfg_file = getenv('BLUEBIRD_CONFIG_FILE')) === false) {
  $cfg_file = "/etc/$filename";
}

define('BLUEBIRD_CONFIG_FILE', $cfg_file);

$bbini = parse_ini_file(BLUEBIRD_CONFIG_FILE, true);

$dbhost = $bbini['globals']['db.host'];
$dbuser = $bbini['globals']['db.user'];
$dbpass = $bbini['globals']['db.pass'];
$httpuser = $bbini['globals']['http.user'];
$httppass = $bbini['globals']['http.pass'];
$dbciviprefix = $bbini['globals']['db.civicrm.prefix'];
$dbdrupprefix = $bbini['globals']['db.drupal.prefix'];
$basedomain = $bbini['globals']['base.domain'];

if (empty($dbhost) || empty($dbuser) || empty($dbpass)
    || empty($httpuser) || empty($httppass)) {
  die("Must set db.host, db.user, db.pass, http.user, http.pass parameters in ".BLUEBIRD_CONFIG_FILE."\n");
}

define('DBHOST', $dbhost);
define('DBUSER', $dbuser);
define('DBPASS', $dbpass);
define('HTTPUSER', $httpuser);
define('HTTPPASS', $httppass);
define('ROOTDIR', $base_dir.'/drupal');
define('CIVI_TABLE_PREFIX', 'civicrm_');
define('CIVI_TEMPLATEDIR', $base_dir.'/templates/site');
define('ROOTDOMAIN', '.'.$basedomain);
define('ROOTDOMAIN_DEV', '.crmdev.nysenate.gov');
define('DRUPAL_ROOTDIR', '');
define('DRUPAL_ROOTDIR_DEV', '');
define('CIVI_DBPREFIX', $dbciviprefix);
define('CIVI_DBPREFIX_DEV', 'senate_dev_c_');
define('DRUPAL_DBPREFIX', $dbdrupprefix);
define('DRUPAL_DBPREFIX_DEV', 'senate_dev_d_');


//load the default configs
//afterwards use overrides if specified (see case statement)
 
//**************************************************************************
// DEBUG SETTINGS
//**************************************************************************

//set debug true/false for logging
//$SC['debug'] = false;
$SC['debug'] = true;

//don't execute runCmd statements
$SC['noExec'] = false;
//$SC['noExec'] = true;


//**************************************************************************
// CONFIG SETTINGS
//**************************************************************************

//TAG file, contains master list of tags
$SC['tagFile'] = 'tags.csv';

//common params. can override in config section
$SC['dbToHost'] = $SC['dbHost'] = DBHOST;
$SC['dbToUser'] = $SC['dbUser'] = DBUSER;
$SC['dbToPassword'] = $SC['dbPassword'] = DBPASS;
$SC['dbToCiviTablePrefix'] = $SC['dbCiviTablePrefix'] = CIVI_TABLE_PREFIX;
$SC['rootDir'] = $SC['toRootDir'] = ROOTDIR;

$SC['httpauth'] = HTTPUSER;
$SC['httppwd'] = HTTPPASS;


switch ($config) {
  case 'prodtodev':
    $SC['dbCiviPrefix'] = CIVI_DBPREFIX;
    $SC['dbDrupalPrefix'] = DRUPAL_DBPREFIX;
    $SC['drupalRootDir'] = DRUPAL_ROOTDIR;
    $SC['templateDir'] = CIVI_TEMPLATEDIR;
    $SC['rootDomain'] = ROOTDOMAIN;
    $SC['dbToCiviPrefix'] = CIVI_DBPREFIX_DEV;
    $SC['dbToDrupalPrefix'] = DRUPAL_DBPREFIX_DEV;
    $SC['toDrupalRootDir'] = DRUPAL_ROOTDIR_DEV;
    $SC['toTemplateDir'] = CIVI_TEMPLATEDIR;
    $SC['toRootDomain'] = ROOTDOMAIN_DEV;
    break;

  case 'devtoprod':
    $SC['dbCiviPrefix'] = CIVI_DBPREFIX_DEV;
    $SC['dbDrupalPrefix'] = DRUPAL_DBPREFIX_DEV;
    $SC['drupalRootDir'] = DRUPAL_ROOTDIR_DEV;
    $SC['templateDir'] = CIVI_TEMPLATEDIR;
    $SC['rootDomain'] = ROOTDOMAIN_DEV;
    $SC['dbToCiviPrefix'] = CIVI_DBPREFIX;
    $SC['dbToDrupalPrefix'] = DRUPAL_DBPREFIX;
    $SC['toDrupalRootDir'] = DRUPAL_ROOTDIR;
    $SC['toTemplateDir'] = CIVI_TEMPLATEDIR;
    $SC['toRootDomain'] = ROOTDOMAIN;
    break;

  case 'crmtocrm2':
    $SC['dbCiviPrefix'] = $SC['dbToCiviPrefix'] = CIVI_DBPREFIX;
    $SC['dbDrupalPrefix'] = $SC['dbToDrupalPrefix'] = DRUPAL_DBPREFIX;
    $SC['drupalRootDir'] = $SC['toDrupalRootDir'] = DRUPAL_ROOTDIR;
    $SC['templateDir'] = $SC['toTemplateDir'] = CIVI_TEMPLATEDIR;
    $SC['rootDomain'] = ROOTDOMAIN;
    $SC['toRootDomain'] = ".crm2.nysenate.gov";
    break;

  case 'prod':
    $SC['dbCiviPrefix'] = $SC['dbToCiviPrefix'] = CIVI_DBPREFIX;
    $SC['dbDrupalPrefix'] = $SC['dbToDrupalPrefix'] = DRUPAL_DBPREFIX;
    $SC['drupalRootDir'] = $SC['toDrupalRootDir'] = DRUPAL_ROOTDIR;
    $SC['templateDir'] = $SC['toTemplateDir'] = CIVI_TEMPLATEDIR;
    $SC['rootDomain'] = $SC['toRootDomain'] = ROOTDOMAIN;
    break;

  case 'dev':
    $SC['dbCiviPrefix'] = $SC['dbToCiviPrefix'] = CIVI_DBPREFIX_DEV;
    $SC['dbDrupalPrefix'] = $SC['dbToDrupalPrefix'] = DRUPAL_DBPREFIX_DEV;
    $SC['drupalRootDir'] = $SC['toDrupalRootDir'] = DRUPAL_ROOTDIR_DEV;
    $SC['templateDir'] = $SC['toTemplateDir'] = CIVI_TEMPLATEDIR;
    $SC['rootDomain'] = $SC['toRootDomain'] = ROOTDOMAIN_DEV;
    break;

  default:
    die("\n\nrequires a valid configuration\n\n");
    break;	
}

//**************************************************************************
// DO NOT USUALLY EDIT BELOW THIS LINE
//**************************************************************************

//some shell variables
$SC['mysql'] = "mysql -u{$SC['dbUser']} -p{$SC['dbPassword']} -h{$SC['dbHost']}";
$SC['mysqlTo'] = $SC['mysql'];

$SC['mysqldump'] = "mysqldump -u{$SC['dbUser']} -p{$SC['dbPassword']} -h{$SC['dbHost']}";
$SC['mysqldumpTo'] = "mysqldump -u{$SC['dbToUser']} -p{$SC['dbToPassword']} -h{$SC['dbToHost']}";

$SC['tmp'] = "/tmp/";
$SC['copy'] = "cp";

if ($SC['debug']) error_reporting(E_ALL);
if ($SC['debug']) ini_set("display_errors", 1);

?>
