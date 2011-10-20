<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

// $Id: example.drush.inc,v 1.5 2009/09/30 13:15:54 weitzman Exp $

/**
 * @file
 *   Example drush command.
 *
 *   Shows how to make your own drush command.
 *
 *   You can copy this file to any of the following
 *     1. A .drush folder in your HOME folder.
 *     2. Anywhere in a folder tree below an active module on your site.
 *     3. In an arbitrary folder specified with the --include option.
 */

/**
 * Implementation of hook_drush_command().
 *
 * In this hook, you specify which commands your
 * drush module makes available, what it does and 
 * description.
 *
 * Notice how this structure closely resembles how 
 * you define menu hooks.
 * 
 * @See drush_parse_command() for a list of recognized keys.
 *
 * @return
 *   An associative array describing your command(s).
 */
function civicrm_drush_command() {
    $items = array();

    // the key in the $items array is the name of the command.
    $items['civicrm-install'] = 
        array('callback'    => 'civicrm_pkg_install',
              'description' => "Install a new instance of CiviCRM.",
              'options'     =>
                array('--dbuser'      => 'MySQL username for your Drupal/CiviCRM database.',
                      '--dbpass'      => 'MySQL password for your Drupal/CiviCRM database.',
                      '--dbhost'      => 'MySQL host for your Drupal/CiviCRM database. Defaults to localhost.',
                      '--dbname'      => 'MySQL database name of your Drupal/CiviCRM database.',
                      '--tarfile'     => 'Path to your CiviCRM tar.gz file.',
                      '--destination' => 'Destination modules path to extract CiviCRM (eg : sites/all/modules ).',
                      ),
              );
    $items['civicrm-upgrade-db']  = 
        array('callback'    => 'civicrm_upgrade_db',
              'description' => "Execute the civicrm/upgrade?reset=1 process from the command line.",
              );
    $items['civicrm-update-cfg']  = 
        array('callback'    => 'civicrm_config_update',
              'description' => "Update config_backend to correct config settings, especially when the CiviCRM site has been cloned / migrated.",
              'examples'    => 
              array('drush -l http://example.com/civicrm civicrm-update-cfg' => 
                    'Update config_backend to correct config settings for civicrm installation on example.com site.',
                    ),
              );
    $items['civicrm-cache-clear'] = 
        array('callback'    => 'civicrm_cache_clear',
              'description' => "Clear all CiviCRM database and smarty caches and session.",
              );
    $items['civicrm-enable-debug'] = 
        array('callback'    => 'civicrm_enable_debug',
              'description' => "Enable CiviCRM Debugging.",
              );
    $items['civicrm-upgrade']  = 
        array('callback'    => 'civicrm_upgrade',
              'description' => "Replace CiviCRM codebase with new specified tarfile and upgrade database by executing the CiviCRM upgrade process - civicrm/upgrade?reset=1.",
              'examples'    => 
              array('drush civicrm-upgrade --tarfile=~/tarballs/civicrm-3.1.4-drupal.tar.gz' => 
                    'Replace old CiviCRM codebase with new v3.1.4 and run upgrade process.',
                    ),
              'options'     => 
              array('--tarfile'    => 
                    'Path of new CiviCRM tarfile, with which current CiviCRM codebase is to be replaced.',
                    '--backup-dir' => 
                    'Specify a directory to backup current CiviCRM codebase and database into, defaults to a backup directory above your Drupal root.'
                    ),
              );
    $items['civicrm-restore'] = 
        array('callback'    => 'civicrm_restore',
              'description' => 'Restore CiviCRM codebase and database back from the specified backup directory.',
              'examples'    => 
              array('drush civicrm-restore --restore-dir=../backup/modules/20100309200249' => 
                    'Replace current civicrm codebase with the $restore-dir/civicrm codebase, and reload the database with $restore-dir/civicrm.sql file',
                    ),
              'options'     => 
              array('--restore-dir'    => 
                    'Path of directory having backed up CiviCRM codebase and database.',
                    '--backup-dir' => 
                    'Specify a directory to backup current CiviCRM codebase and database into, defaults to a backup directory above your Drupal root.'
                    ),
              );
    $items['civicrm-rest'] = 
        array('callback'    => 'civicrm_rest',
              'description' => "Rest interface for accessing CiviCRM APIs. It can return xml or json formatted data.", 
              'examples'    => 
              array("drush civicrm-rest --query='civicrm/contact/search&json=1&key=7decb879f28ac4a0c6a92f0f7889a0c9&api_key=7decb879f28ac4a0c6a92f0f7889a0c9'" => 
                    'Use contact search api to return data in json format.',
                    ),
              'options'     => 
              array('--query' => 'Query part of url. Refer CiviCRM wiki doc for more details.'),
              );
    $items['civicrm-sql-conf'] = 
        array('callback'    => 'civicrm_sql_conf',
              'description' => 'Print CiviCRM database connection details.',
              );
    $items['civicrm-sql-connect'] = 
        array('callback'    => 'civicrm_sql_connect',
              'description' => 'A string for connecting to the CiviCRM DB.',
              );
    $items['civicrm-sql-dump'] = 
        array('callback'    => 'civicrm_sql_dump',
              'description' => 'Exports the CiviCRM DB as SQL using mysqldump.',
              'examples'    => 
              array('drush civicrm-sql-dump --result-file=../CiviCRM.sql' => 
                    'Save SQL dump to the directory above Drupal root.',
                    ),
              'options'     => 
              array('--result-file' => 'Save to a file.'),
              );
    $items['civicrm-sql-query'] = 
        array('callback'    => 'civicrm_sql_query',
              'description' => 'Execute a query against the CiviCRM database.',
              'examples'    => 
              array('drush civicrm-sql-query "SELECT * FROM civicrm_contact WHERE id=1"' => 
                    'Browse user record',
                    ),
              'arguments'   => 
              array('query' => 'A SQL query. Mandatory.',
                    ),
              );
    $items['civicrm-sql-cli'] = 
        array('callback'    => 'civicrm_sql_cli',
              'description' => "Open a SQL command-line interface using CiviCRM's credentials.",
              );
    $items['civicrm-civimail'] = 
        array('callback'    => 'drush_civicrm_civimail_cron',
              'description' => "Run the CiviMail cron.",
              'examples'    => 
              array('drush --uri=http://example.com civicrm-civimail -u admin -p admin -s 12345678912345678912345678' => 
                    'Run CiviMail cron with admin credentials and site key.',
                    ),
              );
    $items['civicrm-member-records'] = 
        array('callback'    => 'drush_civicrm_updatemembershiprecord_cron',
              'description' => "Run the CiviMember UpdateMembershipRecord cron (civicrm-member-records).",
              );

    return $items;
}

function civicrm_pkg_install( ) {
    if ( !$dbuser = drush_get_option('dbuser', false) ) {
        drush_die(dt('CiviCRM database username not specified.'));
    }
    if ( !$dbpass = drush_get_option('dbpass', false) ) {
        drush_die(dt('CiviCRM database password not specified.'));
    }
    if ( !$dbhost = drush_get_option('dbhost', false) ) {
        drush_die(dt('CiviCRM database host not specified.'));
    }
    if ( !$dbname = drush_get_option('dbname', false) ) {
        drush_die(dt('CiviCRM database name not specified.'));
    }
    $crmpath = _civicrm_get_crmpath( );
    $drupalRoot = drush_get_context('DRUSH_DRUPAL_ROOT');
    $modPath    = "$drupalRoot/$crmpath";
    
    if ( !is_dir( "$modPath/civicrm" ) ) {
        // extract tarfile at right place
        _civicrm_extract_tarfile( $modPath );
        
        // include civicrm installer helper file
        $civicrmInstallerHelper = "$modPath/civicrm/install/civicrm.php";
        if ( !file_exists($civicrmInstallerHelper) ) {
            drush_die(dt("Tarfile could not be unpacked OR CiviCRM installer helper file is missing."));
        }
        drush_log(dt("Tarfile unpacked."), 'ok');
        
        // setup all required files/civicrm/* directories
        _civicrm_create_files_dirs( $civicrmInstallerHelper, $modPath );
        
        // install database
        _civicrm_install_db( $dbuser, $dbpass, $dbhost, $dbname, $modPath );
        
        // generate civicrm.settings.php file
        _civicrm_generate_settings_file( $dbuser, $dbpass, $dbhost, $dbname, $modPath );

        module_enable(array('CiviCRM'));

        drush_log(dt("CiviCRM installed."), 'ok');
    } else {
        drush_log(dt("Existing CiviCRM found. No action taken."), 'ok');
    }
}

function _civicrm_extract_tarfile( $destinationPath, $option = 'tarfile' ) {
    if ( !$tarfile = drush_get_option($option, false) ) {
        drush_die(dt('CiviCRM tarfile not specified.'));
    }
    $tarpath = $tarfile;
    if ( drush_shell_exec("gzip -d " . $tarfile) ) {
        $tarpath = substr($tarfile, 0, strlen($tarfile)-3);
    }
    drush_shell_exec("tar -xf $tarpath -C \"$destinationPath\"");
}

function _civicrm_install_db( $dbuser, $dbpass, $dbhost, $dbname, $modPath ) {
    $drupalRoot = drush_get_context('DRUSH_DRUPAL_ROOT');
    $siteRoot   = drush_get_context('DRUSH_DRUPAL_SITE_ROOT', FALSE);
    
    $sqlPath    = "$modPath/civicrm/sql";
    $conn = @mysql_connect($dbhost, $dbuser, $dbpass);
    if( !@mysql_select_db($dbname) && 
        !@mysql_query("CREATE DATABASE $database") ) {
        drush_die(dt('CiviCRM database was not found. Failed to create one.'));
    }
    
    // setup database with civicrm structure and data
    $dsn = "mysql://{$dbuser}:{$dbpass}@{$dbhost}/{$dbname}?new_link=true";
    drush_print(dt("Loading CiviCRM database structure .."));
    civicrm_source($dsn, $sqlPath . '/civicrm.mysql'   );
    drush_print(dt("Loading CiviCRM database with required data .."));
    civicrm_source($dsn, $sqlPath . '/civicrm_data.mysql');
    civicrm_source($dsn, $sqlPath . '/civicrm_acl.mysql' );
    drush_log(dt("CiviCRM database loaded successfully."), 'ok');
}

function _civicrm_create_files_dirs( $civicrmInstallerHelper, $modPath ) {
    $drupalRoot = drush_get_context('DRUSH_DRUPAL_ROOT' );
    $siteRoot   = drush_get_context('DRUSH_DRUPAL_SITE_ROOT', FALSE );
    
    if ( !file_exists($civicrmInstallerHelper) ) {
        drush_die(dt("CiviCRM installer helper file is missing."));
    }
    require_once "$civicrmInstallerHelper";
    
    // create files/civicrm/* dirs
    global $crmPath;
    $crmPath ="$modPath/civicrm";
    civicrm_setup("$drupalRoot/$siteRoot/files");
    @drush_op('chmod',"$drupalRoot/$siteRoot/files/civicrm", 0777);
}

// generates civicrm.settings.php file
function _civicrm_generate_settings_file( $dbuser, $dbpass, $dbhost, $dbname, $modPath) {
    $drupalRoot = drush_get_context('DRUSH_DRUPAL_ROOT');
    $siteRoot   = drush_get_context('DRUSH_DRUPAL_SITE_ROOT', FALSE);
    $crmPath    = "$modPath/civicrm";
    
    $settingsTplFile = "$crmPath/templates/CRM/common/civicrm.settings.php.tpl";
    if ( !file_exists($settingsTplFile) ) {
        drush_die(dt("Could not find CiviCRM settings template and therefore could not create settings file."));
    }

    drush_print(dt("Generating civicrm settings file .."));
    $baseUrl = drush_get_option('site_url', false);
    $baseUrl = !$baseUrl ? ($GLOBALS['base_url']) : ('http://' . $baseUrl);
    
    $db_spec = _drush_sql_get_db_spec();
    $params  = array(
                     'crmRoot'   => $crmPath,
                     'templateCompileDir' => "$drupalRoot/$siteRoot/files/civicrm/templates_c",
                     'frontEnd'  => 0,
                     'cms'       => 'Drupal',
                     'baseURL'   => $baseUrl,
                     'dbUser'    => $dbuser,
                     'dbPass'    => $dbpass,
                     'dbHost'    => $dbhost,
                     'dbName'    => $dbname,
                     'CMSdbUser' => $db_spec['username'],
                     'CMSdbPass' => $db_spec['password'],
                     'CMSdbHost' => $db_spec['host'],
                     'CMSdbName' => $db_spec['database'],
                     'siteKey'   => md5(uniqid( '', true ) . $baseUrl),
                     );
    $str = file_get_contents( $settingsTplFile );
    foreach ( $params as $key => $value ) { 
        $str = str_replace( '%%' . $key . '%%', $value, $str ); 
    } 
    $str = trim( $str );

    $configFile = "$drupalRoot/$siteRoot/civicrm.settings.php";
    civicrm_write_file( $configFile, $str );
    @drush_op('chmod',"$configFile", 0644);
    drush_log(dt("Settings file generated."), 'ok');
}

/**
 * Implementation of hook_drush_help().
 *
 * This function is called whenever a drush user calls
 * 'drush help <name-of-your-command>'
 *
 * @param
 *   A string with the help section (prepend with 'drush:')
 *
 * @return
 *   A string with the help text for your command.
 */
function civicrm_drush_help($section) {
    switch ($section) {
    case 'drush:civicrm-upgrade-db':
        return dt("Run civicrm/upgrade?reset=1 just as a web browser would.");
    case 'drush:civicrm-update-cfg':
        return dt("Update config_backend to correct config settings, especially when the CiviCRM site has been cloned / migrated.");
    case 'drush:civicrm-cache-clear':
        return dt("Clear all CiviCRM database and smarty caches.");
    case 'drush:civicrm-upgrade':
        return dt("Take backups, replace CiviCRM codebase with new specified tarfile and upgrade database by executing the CiviCRM upgrade process - civicrm/upgrade?reset=1. Use civicrm-restore to revert to previous state in case anything goes wrong.");
    case 'drush:civicrm-restore':
        return dt("Restore CiviCRM codebase and database back from the specified backup directory.");
    case 'drush:civicrm-rest':
        return dt("Rest interface for accessing CiviCRM APIs. It can return xml or json formatted data.");
    case 'drush:civicrm-sql-conf':
      return dt('Show civicrm database connection details.');
    case 'drush:civicrm-sql-connect':
      return dt('A string which connects to the civicrm database.');
    case 'drush:civicrm-sql-cli':
      return dt('Quickly enter the mysql command line.');
    case 'drush:civicrm-sql-dump':
      return dt('Prints the whole CiviCRM database to STDOUT or save to a file.');
    case 'drush:civicrm-sql-query':
      return dt("Usage: drush [options] civicrm-sql-query <query>...\n<query> is a SQL statement, which can alternatively be passed via STDIN. Any additional arguments are passed to the mysql command directly.");
    }
}

/**
 * Example drush command callback.
 *
 * This is where the action takes place.
 *
 * In this function, all of Drupals API is (usually) available, including
 * any functions you have added in your own modules/themes.
 *
 * To print something to the terminal window, use drush_print().
 *
 */
function civicrm_upgrade_db() {
    if ( !defined('CIVICRM_UPGRADE_ACTIVE') ) { 
        define('CIVICRM_UPGRADE_ACTIVE', 1); 
    }
    _civicrm_init();

    $_POST['upgrade'] = 1;
    $_GET['q'] = 'civicrm/upgrade';
    require_once 'CRM/Core/Config.php';
    require_once 'CRM/Core/Smarty.php';
    $template =& CRM_Core_Smarty::singleton( );

    require_once 'CRM/Utils/System.php';
    require_once 'CRM/Core/BAO/Domain.php';
    $codeVer = CRM_Utils_System::version();
    $dbVer   = CRM_Core_BAO_Domain::version();
    if ( !$dbVer ) {
        drush_die(dt('Version information missing in civicrm database.'));
    } else if ( stripos($dbVer, 'upgrade') ) {
        drush_die(dt('Database check failed - the database looks to have been partially upgraded. You may want to reload the database with the backup and try the upgrade process again.'));
    } else if ( !$codeVer ) {
        drush_die(dt('Version information missing in civicrm codebase.'));
    } else if ( version_compare($codeVer, $dbVer) > 0 ) {
        drush_print(dt("Starting with v!dbVer -> v!codeVer upgrade ..", 
                       array('!dbVer' => $dbVer, '!codeVer' => $codeVer)));
    } else if ( version_compare($codeVer, $dbVer) < 0 ) {
        drush_die(dt("Database is marked with an unexpected version '!dbVer' which is higher than that of codebase version '!codeVer'.", array('!dbVer' => $dbVer, '!codeVer' => $codeVer)));
    } else {
        drush_print(dt("Starting with upgrade .."));
    }

    require_once( 'CRM/Upgrade/Page/Upgrade.php' );
    $upgrade = new CRM_Upgrade_Page_Upgrade( );
    ob_start(); // to suppress html output /w source code.
    $upgrade->run( );
    $result = $template->get_template_vars('message'); // capture the required message.
    ob_end_clean();
    drush_print("Upgrade outputs: " . "\"$result\"");
}

function civicrm_config_update() {
    _civicrm_init();

    $defaultValues = array( );
    $states = array( 'old', 'new' );
    for ( $i = 1 ; $i <= 3; $i++ ) {
        foreach ( $states as $state ) {
            $name = "{$state}Val_{$i}";
            $value = drush_get_option( $name, null );
            if ( $value ) {
                $defaultValues[$name] = $value;
            }
        }
    }

    require_once 'CRM/Core/I18n.php';
    require_once 'CRM/Core/BAO/Setting.php';
    $result = CRM_Core_BAO_Setting::doSiteMove( $defaultValues );

    if ( $result )
        drush_log(dt('Config successfully updated.'), 'completed');
    else
        drush_log(dt('Config update failed.'), 'failed');
}

function civicrm_cache_clear() {
    _civicrm_init();

    require_once 'CRM/Core/Config.php';
    $config = CRM_Core_Config::singleton( );

    // clear db caching
    $config->clearDBCache( );

    // also cleanup the templates_c directory
    $config =& CRM_Core_Config::singleton( );
    $config->cleanup( 1 , false );

    // also cleanup the session object
    $session =& CRM_Core_Session::singleton( );
    $session->reset(1);

    drush_log(dt('Cache cleared.'), 'ok');
}

function civicrm_enable_debug( ) {
    _civicrm_init();

    $params['debug'] = 1;
    $params['backtrace'] = 1;

    require_once 'CRM/Admin/Form/Setting.php';
    CRM_Admin_Form_Setting::commonProcess( $params );

    drush_log(dt('Debug setting enabled.'), 'ok');
}


function civicrm_upgrade() {
    $tarfile = drush_get_option('tarfile', false);
    if ( !$tarfile ) {
        drush_die(dt('Tarfile not specified.'));
    }
    //FIXME: throw error if tarfile is not in a valid format.
   
    if ( !defined('CIVICRM_UPGRADE_ACTIVE') ) { 
        define('CIVICRM_UPGRADE_ACTIVE', 1); 
    }
    _civicrm_init();

    global $civicrm_root;

    $date = date('YmdHis');
    $backup_file = "civicrm";

    $basepath     = explode('/', $civicrm_root);
    array_pop($basepath);
    $project_path = implode('/', $basepath). '/';

    $drupal_root = drush_get_context('DRUSH_DRUPAL_ROOT');
    $backup_dir  = drush_get_option('backup-dir', $drupal_root  . '/../backup');
    $backup_dir  = rtrim($backup_dir, '/');

    drush_print(dt("\nThe upgrade process involves - "));
    drush_print(dt("1. Backing up current CiviCRM code as => !path", 
                   array('!path' => "$backup_dir/modules/$date/$backup_file")));
    drush_print(dt("2. Backing up database as => !path", 
                   array('!path' => "$backup_dir/modules/$date/$backup_file.sql")));
    drush_print(dt("3. Unpacking tarfile to => !path", 
                   array('!path' => "$project_path")));
    drush_print(dt("4. Executing civicrm/upgrade?reset=1 just as a browser would.\n"));
    if( !drush_confirm(dt('Do you really want to continue?')) ) {
        drush_die('Aborting.');
    }

    @drush_op('mkdir', $backup_dir, 0777);
    $backup_dir .= '/modules';
    @drush_op('mkdir', $backup_dir, 0777);
    $backup_dir .= "/$date";
    @drush_op('mkdir', $backup_dir, 0777);
    $backup_target = $backup_dir . '/'. $backup_file;
    if (!drush_op('rename', $civicrm_root, $backup_target)) {
        drush_die(dt('Failed to backup CiviCRM project directory !source to !backup_target', 
                     array('!source' => $civicrm_root, '!backup_target' => $backup_target)));
    }
    drush_log(dt("\n1. Code backed up."), 'ok');

    drush_set_option('result-file', $backup_target . '.sql');
    civicrm_sql_dump();
    drush_log(dt('2. Database backed up.'), 'ok');

    // Decompress
    drush_shell_exec("gzip -d " . $tarfile);
    $tarpath = substr($tarfile, 0, strlen($tarfile)-3);
    // Untar
    drush_shell_exec("tar -xf $tarpath -C \"$project_path\"");
    // drush: not using tar -xzf because that's not working on windows...
    drush_log(dt('3. Tarfile unpacked.'), 'ok');

    drush_print(dt("4. "));
    civicrm_upgrade_db();
    drush_log(dt("\nProcess completed."), 'completed');
}

function civicrm_restore() {
    _civicrm_dsn_init();

    $restore_dir = drush_get_option('restore-dir', false);
    $restore_dir = rtrim($restore_dir, '/');
    if ( !$restore_dir ) {
        drush_die(dt('restore-dir not specified.'));
    }
    $sql_file = $restore_dir . '/civicrm.sql';
    if ( !file_exists($sql_file) ) {
        drush_die(dt('Could not locate civicrm.sql file in the restore directory.'));
    }
    $code_dir = $restore_dir . '/civicrm';
    if ( !is_dir($code_dir) ) {
        drush_die(dt('Could not locate civicrm directory inside restore-dir.'));
    } else if ( !file_exists("$code_dir/civicrm-version.txt") ) {
        drush_die(dt('civicrm directory inside restore-dir, doesn\'t look to be a valid civicrm codebase.'));
    }

    $date = date('YmdHis');

    global $civicrm_root;
    $civicrm_root_base = explode('/', $civicrm_root);
    array_pop($civicrm_root_base);
    $civicrm_root_base = implode('/', $civicrm_root_base). '/';

    $drupal_root = drush_get_context('DRUSH_DRUPAL_ROOT');
    $restore_backup_dir  = drush_get_option('backup-dir', $drupal_root  . '/../backup');
    $restore_backup_dir  = rtrim($restore_backup_dir, '/');

    // get confirmation from user - 
    $db_spec = _drush_sql_get_db_spec();
    drush_print(dt("\nProcess involves :"));
    drush_print(dt("1. Restoring '\$restore-dir/civicrm' directory to '!toDir'.", array('!toDir' => $civicrm_root_base)));
    drush_print(dt("2. Dropping and creating '!db' database.", array('!db' => $db_spec['database'])));
    drush_print(dt("3. Loading '\$restore-dir/civicrm.sql' file into the database."));
    drush_print();
    drush_print(dt("Note: Before restoring a backup will be taken in '!path' directory.", 
                   array('!path' => "$restore_backup_dir/modules/restore")));
    drush_print();
    if( !drush_confirm(dt('Do you really want to continue?')) ) {
        drush_die('Aborting.');
    }

    // create restore-backup-dir if not already exists
    @drush_op('mkdir', $restore_backup_dir, 0777);
    $restore_backup_dir .= '/modules';
    @drush_op('mkdir', $restore_backup_dir, 0777);
    $restore_backup_dir .= '/restore';
    @drush_op('mkdir', $restore_backup_dir, 0777);
    $restore_backup_dir .= "/$date";
    @drush_op('mkdir', $restore_backup_dir, 0777);

    // 1. backup and restore codebase
    drush_print(dt('Restoring civicrm codebase ..'));
    if (is_dir($civicrm_root) && !drush_op('rename', $civicrm_root, $restore_backup_dir . '/civicrm')) {
        drush_die(dt("Failed to take backup for '!destination' directory", 
                     array('!destination' => $civicrm_root)));
    }
    if (!drush_op('rename', $code_dir, $civicrm_root)) {
        drush_die(dt("Failed to restore civicrm directory '!source' to '!dest'", 
                     array('!source' => $code_dir, '!dest' => $civicrm_root_base)));
    }
    drush_log(dt('Codebase restored.'), 'ok');

    // 2. backup, drop and create database
    drush_set_option('result-file', $restore_backup_dir . '/civicrm.sql');
    civicrm_sql_dump();
    _civicrm_dsn_init();// reinitialize dsn

    drush_log(dt('Database backed up.'), 'ok');

    $exec = 'mysql' . _drush_sql_get_credentials() . ' -e ';
    drush_print(dt("\nDropping database '!db' ..", array('!db' => $db_spec['database'])));
    if (drush_op('system', $exec . '"DROP DATABASE IF EXISTS ' . $db_spec['database'] . '"')) {
        drush_set_error(dt('Could not drop database: @name', array('@name' => $db_spec['database'])));
        return;
    }
    drush_log(dt('Database dropped.'), 'ok');
    $exec = str_replace($db_spec['database'], '', $exec);
    if (drush_op('system', $exec . '"CREATE DATABASE ' . $db_spec['database'] . '"')) {
        drush_set_error(dt('Could not create new database: @name', array('@name' => $db_spec['database'])));
        return;
    }
    drush_log(dt('Database created.'), 'ok');

    // 3. restore database
    switch (_drush_sql_get_scheme()) {
    case 'mysql':
        $send = 'mysql' . _drush_sql_get_credentials();
        break;
    case 'pgsql':
        $send .= 'psql -d ' . _drush_sql_get_credentials() . ' --file -';
        break;
    }
    $exec = "$send < $sql_file";
    drush_print(dt('Loading civicrm.sql file from restore-dir ..'));
    drush_op('system', $exec);
    drush_log(dt('Database restored.'), 'ok');

    drush_log(dt('Restore process completed.'), 'completed');

    _civicrm_dsn_close();
}

function drush_civicrm_civimail_cron() {
    $_GET['q'] = 'civicrm/mailing/queue';
    $_GET['reset'] = '1';
    menu_execute_active_handler();
}

function drush_civicrm_updatemembershiprecord_cron() {
  _civicrm_init();

  $_REQUEST['name'] = drush_get_option('civicrm_cron_username', NULL);
  $_REQUEST['pass'] = drush_get_option('civicrm_cron_password', NULL);
  $_REQUEST['key'] = drush_get_option('civicrm_sitekey', NULL);

  global $argv;
  $argv = array(
            0 => "drush",
            1 => "-u" . $_REQUEST['name'],
            2 => "-p" . $_REQUEST['pass'],
            3 => "-s" . drush_get_option('uri', false),
         );

  if (! defined('CIVICRM_CONFDIR')) {
    define('CIVICRM_CONFDIR', drush_get_context('DRUSH_DRUPAL_ROOT') . '/sites/');
  }

  include "bin/UpdateMembershipRecord.php";
}

function civicrm_rest() {
    _civicrm_init();

    $query = drush_get_option('query', false);
    if ( !$query ) {
        drush_die(dt('query not specified.'));
    }
    $query     = explode( '&', $query );
    $_GET['q'] = array_shift($query);
    foreach ( $query as $keyVal ) {
        list($key, $val) = explode( '=', $keyVal );
        $_REQUEST[$key] = $val;
        $_GET    [$key] = $val;
    }

    require_once 'CRM/Utils/REST.php';
    $rest = new CRM_Utils_REST();

    require_once 'CRM/Core/Config.php';
    $config =& CRM_Core_Config::singleton();

    global $civicrm_root;
    // adding dummy script, since based on this api file path is computed.
    $_SERVER['SCRIPT_FILENAME'] = "$civicrm_root/extern/rest.php";

    if ( isset( $_GET['json'] ) &&
         $_GET['json'] ) {
        header( 'Content-Type: text/javascript' );
    } else {
        header( 'Content-Type: text/xml' );
    }
    echo $rest->run( $config );
}

function civicrm_sql_dump() {
    _civicrm_dsn_init();

    drush_sql_dump_execute();

    _civicrm_dsn_close();
}

function civicrm_sql_conf() {
    _civicrm_dsn_init();

    drush_sql_conf();

    _civicrm_dsn_close();
}

function civicrm_sql_connect() {
    _civicrm_dsn_init();

    drush_sql_connect();

    _civicrm_dsn_close();
}

function civicrm_sql_query( $query ) {
    _civicrm_dsn_init();

    drush_sql_query( $query );

    _civicrm_dsn_close();
}

function civicrm_sql_cli( ) {
    _civicrm_dsn_init();

    drush_sql_cli();

    _civicrm_dsn_close();
}

function _civicrm_dsn_init( $reset = false ) {
    static $globalDbUrl = null;

    _civicrm_init();

    // check if we're using the old-style $GLOBALS['db_url']
    // or the new style ( > drupal 7 )
    if (drush_drupal_major_version() >= 7) {
      $database = drush_get_option('database', 'default');
      $target = drush_get_option('target', 'default');
      if ( !$globalDbUrl && CIVICRM_DSN ) {
        if ( isset($GLOBALS['databases'][$database][$target]) ) {
            $globalDbUrl = $GLOBALS['database'][$database][$target]; // keep a copy so that we can put it back.
        }
        // now modify $GLOBALS so that drush works on CIVICRM_DSN instead of drupal's
        $GLOBALS['databases'][$database][$target] = drush_convert_db_from_db_url(CIVICRM_DSN);
      }
    } else {
      if ( !$globalDbUrl && CIVICRM_DSN ) {
            $globalDbUrl = $GLOBALS['db_url']; // keep a copy so that we can put it back.
      }
      // now modify $GLOBALS so that drush works on CIVICRM_DSN instead of drupal's
      $GLOBALS['db_url'] = CIVICRM_DSN;
    }
    $dbUrl = $globalDbUrl;
    $globalDbUrl = $reset ? null : $globalDbUrl;

    return $dbUrl;
}

function _civicrm_dsn_close( ) {
    $globalDbUrl = _civicrm_dsn_init( true );
    if ( $globalDbUrl ) {
        if (drush_drupal_major_version() >= 7) {
            $database = drush_get_option('database', 'default');
            $target = drush_get_option('target', 'default');
            $GLOBALS['databases'][$database][$target] = $globalDbUrl;
        } else {
            $GLOBALS['db_url'] = $globalDbUrl;
        }
    }
}

function _civicrm_init( ) {
    static $init = null;

    if ( $init ) return $init; // return if already initialized

    global $cmsPath;
    $cmsPath = $drupal_root = drush_get_context('DRUSH_DRUPAL_ROOT');
    $site_root   = drush_get_context('DRUSH_DRUPAL_SITE_ROOT', FALSE);
    $civicrmSettingsFile = "$drupal_root/$site_root/civicrm.settings.php";

    if ( !file_exists( $civicrmSettingsFile ) ) {
        $sites_subdir = drush_get_option('sites-subdir', 'default');
        $civicrmSettingsFile = "$drupal_root/sites/$sites_subdir/civicrm.settings.php";
        if ( !file_exists( $civicrmSettingsFile ) ) {
            drush_die(dt("Could not locate civicrm settings file."));
        }
    }
    include_once $civicrmSettingsFile; // include settings file
    global $civicrm_root;
    if ( ! is_dir($civicrm_root) ) {
        drush_die(dt('Could not locate CiviCRM codebase. Make sure CiviCRM settings file has correct information.'));
    }

    // also initialize config object
    require_once 'CRM/Core/Config.php';
    $config =& CRM_Core_Config::singleton( );

    $init = true;
    return $init;
}

function _civicrm_get_crmpath( ) {
    
    if ( !$crmpath = drush_get_option('destination', false) ) {
        $crmpath = drush_get_context('DRUSH_DRUPAL_SITE_ROOT', FALSE).'/modules/'; 
        if ( !is_dir( $crmpath ) ) {
            $crmpath = "sites/all/modules";
        }
    }
    return $crmpath;        
}
