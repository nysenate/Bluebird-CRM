<?php

require_once '../passwd.php';

define('DBHOST', 'crmdbprod');
define('DBUSER', 'crmadmin');
define('HTTPUSER', 'loadsenate');
define('ROOTDIR', '/data/www/');
define('CIVI_TABLE_PREFIX', 'civicrm_');
define('CIVI_TEMPLATEDIR', 'civicrmInstallTemplates/');
define('INSTALLDIR', '/data/senateProduction/');
define('INSTALLDIR_DEV', '/data/senateDevelopment/');
define('ROOTDOMAIN', '.crm.nysenate.gov');
define('ROOTDOMAIN_DEV', '.crmdev.nysenate.gov');
define('DRUPAL_ROOTDIR', 'nyss/');
define('DRUPAL_ROOTDIR_DEV', 'nyssdev/');
define('CIVI_DBPREFIX', 'senate_c_');
define('CIVI_DBPREFIX_DEV', 'senate_dev_c_');
define('DRUPAL_DBPREFIX', 'senate_d_');
define('DRUPAL_DBPREFIX_DEV', 'senate_dev_d_');

if (DBPASS == "" || HTTPPASS == "") {
  die("Must set DBPASS and HTTPPASS in ".__FILE__."\n");
}

if (!defined('RAYDEBUG')) {
  define('RAYDEBUG', false);
}

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

//conmmon params. can override in config section
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
                $SC['templateDir'] = INSTALLDIR.CIVI_TEMPLATEDIR;
                $SC['rootDomain'] = ROOTDOMAIN;

		$SC['dbToCiviPrefix'] = CIVI_DBPREFIX_DEV;
		$SC['dbToDrupalPrefix'] = DRUPAL_DBPREFIX_DEV;
		$SC['toDrupalRootDir'] = DRUPAL_ROOTDIR_DEV;
		$SC['toTemplateDir'] = INSTALLDIR_DEV.CIVI_TEMPLATEDIR;
		$SC['toRootDomain'] = ROOTDOMAIN_DEV;
		break;

        case 'devtoprod':
                $SC['dbCiviPrefix'] = CIVI_DBPREFIX_DEV;
                $SC['dbDrupalPrefix'] = DRUPAL_DBPREFIX_DEV;
                $SC['drupalRootDir'] = DRUPAL_ROOTDIR_DEV;
                $SC['templateDir'] = INSTALLDIR_DEV.CIVI_TEMPLATEDIR;
                $SC['rootDomain'] = ROOTDOMAIN_DEV;
                $SC['dbToCiviPrefix'] = CIVI_DBPREFIX;
                $SC['dbToDrupalPrefix'] = DRUPAL_DBPREFIX;
                $SC['toDrupalRootDir'] = DRUPAL_ROOTDIR;
                $SC['toTemplateDir'] = INSTALLDIR.CIVI_TEMPLATEDIR;
                $SC['toRootDomain'] = ROOTDOMAIN;
                break;

        case 'crmtocrm2':
                $SC['dbCiviPrefix'] = $SC['dbToCiviPrefix'] = CIVI_DBPREFIX;
                $SC['dbDrupalPrefix'] = $SC['dbToDrupalPrefix'] = DRUPAL_DBPREFIX;
                $SC['drupalRootDir'] = $SC['toDrupalRootDir'] = DRUPAL_ROOTDIR;
                $SC['templateDir'] = $SC['toTemplateDir'] = INSTALLDIR.CIVI_TEMPLATEDIR;
                $SC['rootDomain'] = ROOTDOMAIN;
                $SC['toRootDomain'] = ".crm2.nysenate.gov";
                break;

        case 'prod':
                $SC['dbCiviPrefix'] = $SC['dbToCiviPrefix'] = CIVI_DBPREFIX;
                $SC['dbDrupalPrefix'] = $SC['dbToDrupalPrefix'] = DRUPAL_DBPREFIX;
                $SC['drupalRootDir'] = $SC['toDrupalRootDir'] = DRUPAL_ROOTDIR;
                $SC['templateDir'] = $SC['toTemplateDir'] = INSTALLDIR.CIVI_TEMPLATEDIR;
                $SC['rootDomain'] = $SC['toRootDomain'] = ROOTDOMAIN;
                $SC['installDir'] = $SC['toInstallDir'] = INSTALLDIR;
		break;

        case 'dev':
                $SC['dbCiviPrefix'] = $SC['dbToCiviPrefix'] = CIVI_DBPREFIX_DEV;
                $SC['dbDrupalPrefix'] = $SC['dbToDrupalPrefix'] = DRUPAL_DBPREFIX_DEV;
                $SC['drupalRootDir'] = $SC['toDrupalRootDir'] = DRUPAL_ROOTDIR_DEV;
                $SC['templateDir'] = $SC['toTemplateDir'] = INSTALLDIR_DEV.CIVI_TEMPLATEDIR;
                $SC['rootDomain'] = $SC['toRootDomain'] = ROOTDOMAIN_DEV;
                $SC['installDir'] = $SC['toInstallDir'] = INSTALLDIR_DEV;
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

$SC['mysqldump'] = "mysqldump -v -u{$SC['dbUser']} -p{$SC['dbPassword']} -h{$SC['dbHost']}";
$SC['mysqldumpTo'] = "mysqldump -v -u{$SC['dbToUser']} -p{$SC['dbToPassword']} -h{$SC['dbToHost']}";

$SC['tmp'] = "/tmp/";
$SC['copy'] = "cp";

if ($SC['debug']) error_reporting(E_ALL);
if ($SC['debug']) ini_set("display_errors", 1);

?>
