<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 * CiviCRM Configuration File - v3.1
 */

/**
 * Content Management System (CMS) Host:
 *
 * CiviCRM can be hosted in either Drupal or Joomla.
 * 
 * Settings for Drupal 6.x:
 *      define( 'CIVICRM_UF'        , 'Drupal' );
 *
 * Settings for 1.5.x:
 *      define( 'CIVICRM_UF'        , 'Joomla' );
 *
 * Settings for Standalone:
 *		define( 'CIVICRM_UF'	, 'Standalone');
 *
 * You may have issues with images in CiviCRM. If this is the case, be sure
 * to update the CiviCRM Resource URL field (in Administer CRM: Global
 * Settings: Resource URLS) to your CiviCRM root directory.
 */
define( 'CIVICRM_UF'               , '%%cms%%'        );

/**
 * Content Management System (CMS) Datasource:
 *
 * Update this setting with your CMS (Drupal or Joomla) database username, server and DB name. Comment it out if using CiviCRM standalone.
 * Datasource (DSN) format:
 *      define( 'CIVICRM_UF_DSN', 'mysql://cms_db_username:cms_db_password@db_server/cms_database?new_link=true');
 */
define( 'CIVICRM_UF_DSN'           , 'mysql://%%CMSdbUser%%:%%CMSdbPass%%@%%CMSdbHost%%/%%CMSdbName%%?new_link=true' );

/**
 * CiviCRM Database Settings
 *
 * Database URL (CIVICRM_DSN) for CiviCRM Data:
 * Database URL format:
 *      define( 'CIVICRM_DSN', 'mysql://crm_db_username:crm_db_password@db_server/crm_database?new_link=true');
 *
 * Drupal and CiviCRM can share the same database, or can be installed into separate databases.
 *
 * EXAMPLE: Drupal and CiviCRM running in the same database...
 *      DB Name = drupal, DB User = drupal
 *      define( 'CIVICRM_DSN'         , 'mysql://drupal:YOUR_PASSWORD@localhost/drupal?new_link=true' );
 *
 * EXAMPLE: Drupal and CiviCRM running in separate databases...
 *      Drupal  DB Name = drupal, DB User = drupal
 *      CiviCRM DB Name = civicrm, CiviCRM DB User = civicrm
 *      define( 'CIVICRM_DSN'         , 'mysql://civicrm:YOUR_PASSWORD@localhost/civicrm?new_link=true' );
 *
 */
define( 'CIVICRM_DSN'          , 'mysql://%%dbUser%%:%%dbPass%%@%%dbHost%%/%%dbName%%?new_link=true' );

/**
 * CiviCRM Logging Database
 *
 * Used to point to a different database to use for logging (if desired). If unset defaults to equal CIVICRM_DSN.
 * The CIVICRM_DSN user needs to have the rights to modify the below database schema and be able to write to it.
 */
define('CIVICRM_LOGGING_DSN', CIVICRM_DSN);

/**
 * File System Paths:
 *
 * $civicrm_root is the file system path on your server where the civicrm
 * code is installed. Use an ABSOLUTE path (not a RELATIVE path) for this setting.
 *
 * CIVICRM_TEMPLATE_COMPILEDIR is the file system path where compiled templates are stored.
 * These sub-directories and files are temporary caches and will be recreated automatically
 * if deleted.
 *
 * IMPORTANT: The COMPILEDIR directory must exist,
 * and your web server must have read/write access to these directories.
 *
 *
 * EXAMPLE - CivicSpace / Drupal:
 * If the path to the CivicSpace or Drupal home directory is /var/www/htdocs/civicspace
 * the $civicrm_root setting would be:
 *      $civicrm_root = '/var/www/htdocs/civicspace/modules/civicrm/';
 *
 * the CIVICRM_TEMPLATE_COMPILEDIR would be:
 *      define( 'CIVICRM_TEMPLATE_COMPILEDIR', '/var/www/htdocs/civicspace/files/civicrm/templates_c/' );
 *
 * EXAMPLE - Joomla Installations:
 * If the path to the Joomla home directory is /var/www/htdocs/joomla
 * the $civicrm_root setting would be:
 *      $civicrm_root = '/var/www/htdocs/joomla/administrator/components/com_civicrm/civicrm/';
 *
 * the CIVICRM_TEMPLATE_COMPILEDIR would be:
 *      define( 'CIVICRM_TEMPLATE_COMPILEDIR', '/var/www/htdocs/joomla/media/civicrm/templates_c/' );
 *
 * EXAMPLE - Standalone Installations:
 * If the path to the Standalone home directory is /var/www/htdocs/civicrm
 * the $civicrm_root setting would be:
 *      $civicrm_root = '/var/www/htdocs/civicrm/';
 *
 * the CIVICRM_TEMPLATE_COMPILEDIR might be:
 *      define( 'CIVICRM_TEMPLATE_COMPILEDIR', '/var/www/htdocs/civicrm/standalone/files/templates_c/' );
 */

global $civicrm_root;

$civicrm_root = '%%crmRoot%%';
define( 'CIVICRM_TEMPLATE_COMPILEDIR', '%%templateCompileDir%%' );

/**
 * Site URLs:
 *
 * This section defines absolute and relative URLs to access the host CMS (Drupal or Joomla) resources.
 *
 * IMPORTANT: Trailing slashes should be used on all URL settings.
 * 
 *
 * EXAMPLE - Drupal Installations:
 * If your site's home url is http://www.example.com/drupal/
 * these variables would be set as below. Modify as needed for your install. 
 *
 * CIVICRM_UF_BASEURL - home URL for your site:
 *      define( 'CIVICRM_UF_BASEURL' , 'http://www.example.com/drupal/' );
 *
 * EXAMPLE - Joomla Installations:
 * If your site's home url is http://www.example.com/joomla/
 *
 * CIVICRM_UF_BASEURL - home URL for your site:
 * Administration site:
 *      define( 'CIVICRM_UF_BASEURL' , 'http://www.example.com/joomla/administrator/' );
 * Front-end site:
 *      define( 'CIVICRM_UF_BASEURL' , 'http://www.example.com/joomla/' );
 *
 * EXAMPLE - Standalone Installations:
 * If your site's home url is http://www.example.com/civicrm/
 *
 * CIVICRM_UF_BASEURL - home URL for your site:
 *      define( 'CIVICRM_UF_BASEURL' , 'http://www.example.com/civicrm/standalone/' );
 */
define( 'CIVICRM_UF_BASEURL'      , '%%baseURL%%' );

/*
 * If you are using any CiviCRM script in the bin directory that
 * requires authentication, then you also need to set this key.
 * We recommend using a 16-32 bit alphanumeric/punctuation key. 
 * More info at http://wiki.civicrm.org/confluence/display/CRMDOC/Command-line+Script+Configuration
 */
define( 'CIVICRM_SITE_KEY', null );

/*
 * If you want to disable IDS, set this to 0.
 */
define( 'CIVICRM_IDS_ENABLE', 1);

/**
 * Multi org / Multi site settings:
 *
 */
//define( 'CIVICRM_MULTISITE'           , null );
//define( 'CIVICRM_UNIQ_EMAIL_PER_SITE' , null );
define( 'CIVICRM_DOMAIN_ID'      , 1 );
define( 'CIVICRM_DOMAIN_GROUP_ID', null );
define( 'CIVICRM_DOMAIN_ORG_ID'  , null );

define( 'CIVICRM_EVENT_PRICE_SET_DOMAIN_ID', 0 );

/**
 * Setting to disable email notifications to activity assignees
 *
 */
 define( 'CIVICRM_ACTIVITY_ASSIGNEE_MAIL' , 1 ); 

/**
 * Setting to disable ajax check if similar contacts exist when creating a new contact
 *
 */
 define( 'CIVICRM_CONTACT_AJAX_CHECK_SIMILAR' , 1 ); 

/**
 * Setting to disable or enable profile double optin.
 * This is enable by default and functions only if Civimail is enabled.
 */
 define( 'CIVICRM_PROFILE_DOUBLE_OPTIN', 1 );

/**
 * If set, makes CiviMail default to tracking replies (i.e., using VERP-ed Reply-To:)
 */
define('CIVICRM_TRACK_CIVIMAIL_REPLIES', false);

/**
 * This setting logs all emails to a file. Useful for debugging any mail (or civimail) issues.
 * This will not send any email, so ensure this is commented out in production
 */
// define( 'CIVICRM_MAIL_LOG', '%%templateCompileDir%%/mail.log' );

/**
 * For use with CiviCampaign Petitions
 * If set, contacts that are created when signing a petition are tagged with the 
 * defined tag name (default below is 'Unconfirmed')
 */
define('CIVICRM_TAG_UNCONFIRMED', 'Unconfirmed');

/** 
 * Defines the group name containing all contacts that have signed a CiviCampaign petition.
 * Do not unset - required for email verification. Group will be created if it does not exist.
 */
define('CIVICRM_PETITION_CONTACTS','Petition Contacts');

/**
 * 
 * Do not change anything below this line. Keep as is
 *
 */

$include_path = '.'        . PATH_SEPARATOR .
                $civicrm_root . PATH_SEPARATOR . 
                $civicrm_root . DIRECTORY_SEPARATOR . 'packages' . PATH_SEPARATOR .
                get_include_path( );
set_include_path( $include_path );

if ( function_exists( 'variable_get' ) && variable_get('clean_url', '0') != '0' ) {
    define( 'CIVICRM_CLEANURL', 1 );
} else {
    define( 'CIVICRM_CLEANURL', 0 );
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
    ini_set('memory_limit', '64M');
}

