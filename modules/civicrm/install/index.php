<?php

/**
 * Note that this installer has been based of the SilverStripe installer.
 * You can get more information from the SilverStripe Website at
 * http://www.silverstripe.com/. Please check
 * http://www.silverstripe.com/licensing for licensing details.
 *
 * Copyright (c) 2006-7, SilverStripe Limited - www.silverstripe.com
 * All rights reserved.
 *
 * Changes and modifications (c) 2007-8 by CiviCRM LLC
 *
 */

/**
 * CiviCRM Installer
 */

ini_set('max_execution_time', 3000 );

if ( stristr( PHP_OS, 'WIN' ) ) {
    define( 'CIVICRM_DIRECTORY_SEPARATOR', '/' );
} else {
    define( 'CIVICRM_DIRECTORY_SEPARATOR', DIRECTORY_SEPARATOR );
}

// set installation type - drupal
session_start();

// unset civicrm session if any
if ( array_key_exists( 'CiviCRM', $_SESSION ) ) {
    unset($_SESSION['CiviCRM']);
}

if ( isset($_GET['mode']) ) {
    $_SESSION['install_type'] = $_GET['mode'];
} else {
    if (! isset($_SESSION['install_type'])) {
        $_SESSION['install_type'] = "drupal";
    }
}

global $installType;
$installType = strtolower($_SESSION['install_type']);

if ( ! in_array($installType, array('drupal')) ) {
    $errorTitle = "Oops! Unsupported installation mode";
    $errorMsg   = "";
    errorDisplayPage( $errorTitle, $errorMsg );
}

global $crmPath;
$crmPath = dirname ( dirname( $_SERVER['SCRIPT_FILENAME'] ) );
require_once $crmPath.'/CRM/Utils/System.php';

$docLink = CRM_Utils_System::docURL2( 'Installation and Upgrades', false, 'Installation Guide' );

if ( $installType == 'drupal' ) {
    // do not check 'sites/all/modules' only since it could be a multi-site
    // install. Rather check for existance of sites & modules in the url
    
    //old pattern where we do has to have civicrm in sites/.../modules/
    //$pattern =  "/" . preg_quote('sites' . CIVICRM_DIRECTORY_SEPARATOR, CIVICRM_DIRECTORY_SEPARATOR) . 
    //    "([a-zA-Z0-9_.]+)" . 
    //    preg_quote(CIVICRM_DIRECTORY_SEPARATOR . 'modules', CIVICRM_DIRECTORY_SEPARATOR) . "/";
    
    //lets check only /modules/.
    $pattern = '/' . preg_quote( CIVICRM_DIRECTORY_SEPARATOR . 'modules', CIVICRM_DIRECTORY_SEPARATOR ) . '/';
    
    if ( ! preg_match( $pattern,
                       str_replace( "\\","/",$_SERVER['SCRIPT_FILENAME'] ) ) ) {
        $errorTitle = "Oops! Please Correct Your Install Location";
        $errorMsg = "Please untar (uncompress) your downloaded copy of CiviCRM in the <strong>" . implode(CIVICRM_DIRECTORY_SEPARATOR, array('sites', 'all', 'modules')) . "</strong> directory below your Drupal root directory. Refer to the online " . $docLink . " for more information.";
        errorDisplayPage( $errorTitle, $errorMsg );
    }
}

// Load civicrm database config
if(isset($_REQUEST['mysql'])) {
    $databaseConfig = $_REQUEST['mysql'];
} else {
    $databaseConfig = array(
                            "server"   => "localhost",
                            "username" => "civicrm",
                            "password" => "",
                            "database" => "civicrm",
    );
}

if ( $installType == 'drupal' ) {
    // Load drupal database config
    if(isset($_REQUEST['drupal'])) {
        $drupalConfig = $_REQUEST['drupal'];
    } else {
        $drupalConfig = array(
                              "server"   => "localhost",
                              "username" => "drupal",
                              "password" => "",
                              "database" => "drupal",
        );
    }
}

$loadGenerated = 0;
if ( isset($_REQUEST['loadGenerated'] ) ) {
    $loadGenerated = 1;
}

require_once dirname(__FILE__) . CIVICRM_DIRECTORY_SEPARATOR . 'langs.php';
foreach ($langs as $locale => $_) {
    if ($locale == 'en_US') continue;
    if (!file_exists(implode(CIVICRM_DIRECTORY_SEPARATOR, array($crmPath, 'sql', "civicrm_data.$locale.mysql")))) unset($langs[$locale]);
}

$seedLanguage = 'en_US';
if (isset($_REQUEST['seedLanguage']) and isset($langs[$_REQUEST['seedLanguage']])) {
    $seedLanguage = $_REQUEST['seedLanguage'];
}

if ( $installType == 'drupal' ) {
    global $cmsPath;
    
    //CRM-6840 -don't force to install in sites/all/modules/ 
    require_once "$crmPath/CRM/Utils/System/Drupal.php";
    $cmsPath = CRM_Utils_System_Drupal::cmsRootPath( );

    $siteDir = getSiteDir( $cmsPath, $_SERVER['SCRIPT_FILENAME'] );
    $alreadyInstalled = file_exists( $cmsPath  . CIVICRM_DIRECTORY_SEPARATOR .
                                     'sites'   . CIVICRM_DIRECTORY_SEPARATOR .
                                     $siteDir  . CIVICRM_DIRECTORY_SEPARATOR .
                                     'civicrm.settings.php' );
}

// Exit with error if CiviCRM has already been installed.
if ($alreadyInstalled ) {
    $errorTitle = "Oops! CiviCRM is Already Installed";
    if ( $installType == 'drupal' ) {

        $errorMsg = "CiviCRM has already been installed in this Drupal site. <ul><li>To <strong>start over</strong>, you must delete or rename the existing CiviCRM settings file - <strong>civicrm.settings.php</strong> - from <strong>" . implode(CIVICRM_DIRECTORY_SEPARATOR, array('[your Drupal root directory]', 'sites', $siteDir)) . "</strong>.</li><li>To <strong>upgrade an existing installation</strong>, refer to the online " . $docLink . ".</li></ul>";
    }
    errorDisplayPage( $errorTitle, $errorMsg );
}

$versionFile = $crmPath . CIVICRM_DIRECTORY_SEPARATOR . 'civicrm-version.php';
if(file_exists($versionFile)) {
    require_once( $versionFile );
    $civicrm_version = civicrmVersion( );
} else {
    $civicrm_version = 'unknown';
}

if ( $installType == 'drupal' ) {
    // Ensure that they have downloaded the correct version of CiviCRM
    if ( $civicrm_version['cms'] != 'Drupal' ) {
        $errorTitle = "Oops! Incorrect CiviCRM Version";
        $errorMsg = "This installer can only be used for the Drupal version of CiviCRM. Refer to the online " . $docLink . " for information about installing CiviCRM on PHP4 servers OR installing CiviCRM for Joomla!";
        errorDisplayPage( $errorTitle, $errorMsg );
    }

    $drupalVersionFile = implode(CIVICRM_DIRECTORY_SEPARATOR, array($cmsPath, 'modules', 'system', 'system.module'));

    if ( file_exists( $drupalVersionFile ) ) {
        require_once $drupalVersionFile;
    }

    if ( !defined('VERSION') or version_compare(VERSION, '6.0') < 0 ) {
        $errorTitle = "Oops! Incorrect Drupal Version";
        $errorMsg = "This version of CiviCRM can only be used with Drupal 6.x. Please ensure that '$drupalVersionFile' exists if you are running Drupal 6.0 and over. Refer to the online " . $docLink . " for information about installing CiviCRM.";
        errorDisplayPage( $errorTitle, $errorMsg );
    }
}

// Check requirements
$req = new InstallRequirements();
$req->check();

if($req->hasErrors()) {
    $hasErrorOtherThanDatabase = true;
}

if($databaseConfig) {
    $dbReq = new InstallRequirements();
    $dbReq->checkdatabase($databaseConfig, 'CiviCRM');
    if ( $installType == 'drupal' ) {
        $dbReq->checkdatabase($drupalConfig, 'Drupal');
    }
}

// Actual processor
if(isset($_REQUEST['go']) && !$req->hasErrors() && !$dbReq->hasErrors()) {
    // Confirm before reinstalling
    if(!isset($_REQUEST['force_reinstall']) && $alreadyInstalled) {
        include('template.html');
    } else {
        $inst = new Installer();
        $inst->install($_REQUEST);
    }

    // Show the config form
} else {
    include('template.html');
}

/**
 * This class checks requirements
 * Each of the requireXXX functions takes an argument which gives a user description of the test.  It's an array
 * of 3 parts:
 *  $description[0] - The test catetgory
 *  $description[1] - The test title
 *  $description[2] - The test error to show, if it goes wrong
 */

class InstallRequirements {
    var $errors, $warnings, $tests;

        /**
         * Just check that the database configuration is okay
         */
    function checkdatabase($databaseConfig, $dbName) {
        if($this->requireFunction('mysql_connect',
                                  array("PHP Configuration", 
                                        "MySQL support",
                                        "MySQL support not included in PHP."))) {
            $this->requireMySQLServer($databaseConfig['server'],
                                      array("MySQL $dbName Configuration",
                                            "Does the server exist",
                                            "Can't find the a MySQL server on '$databaseConfig[server]'",
                                            $databaseConfig['server']));
            if($this->requireMysqlConnection($databaseConfig['server'],
                                             $databaseConfig['username'],
                                             $databaseConfig['password'],
                    array("MySQL $dbName Configuration",
                          "Are the access credentials correct",
                          "That username/password doesn't work"))) {
                @$this->requireMySQLVersion("5.0",
                                            array("MySQL $dbName Configuration",
                                                  "MySQL version at least 5.0",
                                                  "MySQL version 5.0 is required, you only have ",
                                                  "MySQL " . mysql_get_server_info()));
            }
            $onlyRequire = ( $dbName == 'Drupal' ) ? true : false;
            $this->requireDatabaseOrCreatePermissions(
                $databaseConfig['server'],
                $databaseConfig['username'],
                $databaseConfig['password'],
                $databaseConfig['database'],
                array("MySQL $dbName Configuration",
                      "Can I access/create the database",
                      "I can't create new databases and the database '$databaseConfig[database]' doesn't exist"),
                $onlyRequire );
            if ( $dbName != 'Drupal' ) {
                $this->requireMySQLInnoDB($databaseConfig['server'],
                    $databaseConfig['username'],
                    $databaseConfig['password'],
                    $databaseConfig['database'],
                    array("MySQL $dbName Configuration",
                          "Can I access/create InnoDB tables in the database",
                          "Unable to create InnoDB tables. MySQL InnoDB support is required for CiviCRM but is either not available or not enabled in this MySQL database server." ) );
                $this->requireMySQLTempTables($databaseConfig['server'],
                    $databaseConfig['username'],
                    $databaseConfig['password'],
                    $databaseConfig['database'],
                    array("MySQL $dbName Configuration",
                          'Can I create temporary tables in the database',
                          'Unable to create temporary tables. This MySQL user is missing the CREATE TEMPORARY TABLES privilege.'));
                $this->requireMySQLLockTables($databaseConfig['server'],
                    $databaseConfig['username'],
                    $databaseConfig['password'],
                    $databaseConfig['database'],
                    array("MySQL $dbName Configuration",
                          'Can I create lock tables in the database',
                          'Unable to lock tables. This MySQL user is missing the LOCK TABLES privilege.'));
            }
        }
    }


        /**
         * Check everything except the database
         */
    function check() {
        global $crmPath, $installType;

        $this->errors = null;

        $this->requirePHPVersion('5.2.0', array("PHP Configuration", "PHP5 installed", null, "PHP version " . phpversion()));

        // Check that we can identify the root folder successfully
        $this->requireFile($crmPath . CIVICRM_DIRECTORY_SEPARATOR . 'README.txt',
            array("File permissions",
                                 "Does the webserver know where files are stored?",
                                 "The webserver isn't letting me identify where files are stored.",
                $this->getBaseDir()
            ),
            true );

        // CRM-6485: make sure the path does not contain PATH_SEPARATOR, as we don’t know how to escape it
        $this->requireNoPathSeparator(
            array(
                'File permissions',
                'does the CiviCRM path contain PATH_SEPARATOR?',
                'the ' . $this->getBaseDir() . ' path contains PATH_SEPARATOR (the ' . PATH_SEPARATOR . ' character)',
                $this->getBaseDir(),
            )
        );

        $requiredDirectories = array( 'CRM', 'packages', 'templates', 'js', 'api', 'i', 'sql' );
        foreach ( $requiredDirectories as $dir ) {
            $this->requireFile( $crmPath . CIVICRM_DIRECTORY_SEPARATOR . $dir, array("File permissions", "$dir folder exists", "There is no $dir folder" ), true );
        }

        $configIDSiniDir = null;
        if ( $installType == 'drupal' ) {
            global $cmsPath;
            $siteDir = getSiteDir( $cmsPath, $_SERVER['SCRIPT_FILENAME'] );

            // make sure that we can write to sites/default and files/
            $writableDirectories = array( 'sites' . CIVICRM_DIRECTORY_SEPARATOR . $siteDir . CIVICRM_DIRECTORY_SEPARATOR . 'files',
                                          'sites' . CIVICRM_DIRECTORY_SEPARATOR . $siteDir );
            foreach ( $writableDirectories as $dir ) {
                $this->requireWriteable( $cmsPath . CIVICRM_DIRECTORY_SEPARATOR . $dir,
                    array("File permissions", "Is the $dir folder writeable?", null ),
                    true );
            }
            //check for Config.IDS.ini, file may exist in re-install
            $configIDSiniDir  = array( $cmsPath ,'sites', $siteDir, 'files', 'civicrm', 'upload' ,'Config.IDS.ini' );
        }
        if ( is_array( $configIDSiniDir ) && !empty( $configIDSiniDir ) ) {
            $configIDSiniFile = implode( CIVICRM_DIRECTORY_SEPARATOR, $configIDSiniDir );
            if ( file_exists( $configIDSiniFile ) ) {
                unlink($configIDSiniFile);
            }
        }

        // Check for rewriting        
        if (isset($_SERVER['SERVER_SOFTWARE'])) {
            $webserver = strip_tags(trim($_SERVER['SERVER_SOFTWARE']));
        } elseif (isset($_SERVER['SERVER_SIGNATURE']))  {
            $webserver = strip_tags(trim($_SERVER['SERVER_SIGNATURE']));
        }
                 
        if ($webserver == '') {
            $webserver = "I can't tell what webserver you are running";
        }

        // Check for $_SERVER configuration
        $this->requireServerVariables(array('SCRIPT_NAME','HTTP_HOST','SCRIPT_FILENAME'), array("Webserver config", "Recognised webserver", "You seem to be using an unsupported webserver.  The server variables SCRIPT_NAME, HTTP_HOST, SCRIPT_FILENAME need to be set."));

        // Check for MySQL support
        $this->requireFunction('mysql_connect', array("PHP Configuration", "MySQL support", "MySQL support not included in PHP."));

        // Check for JSON support
        $this->requireFunction('json_encode', array("PHP Configuration", "JSON support", "JSON support not included in PHP."));
        
        // Check memory allocation
        $this->requireMemory(32*1024*1024, 64*1024*1024, array("PHP Configuration", "Memory allocated (PHP config option 'memory_limit')", "CiviCRM needs a minimum of 32M allocated to PHP, but recommends 64M.", ini_get("memory_limit")));

        return $this->errors;
    }

    function suggestPHPSetting($settingName, $settingValues, $testDetails) {
        $this->testing($testDetails);

        $val = ini_get($settingName);
        if(!in_array($val, $settingValues) && $val != $settingValues) {
            $testDetails[2] = "$settingName is set to '$val' in php.ini.  $testDetails[2]";
            $this->warning($testDetails);
        }
    }

    function requireMemory($min, $recommended, $testDetails) {
        $this->testing($testDetails);
        $mem = $this->getPHPMemory();

        if($mem < $min && $mem > 0) {
            $testDetails[2] .= " You only have " . ini_get("memory_limit") . " allocated";
            $this->error($testDetails);
        } else if($mem < $recommended && $mem > 0) {
            $testDetails[2] .= " You only have " . ini_get("memory_limit") . " allocated";
            $this->warning($testDetails);
        } elseif($mem == 0) {
            $testDetails[2] .= " We can't determine how much memory you have allocated. Install only if you're sure you've allocated at least 20 MB.";
            $this->warning($testDetails);
        }
    }

    function getPHPMemory() {
        $memString = ini_get("memory_limit");

        switch(strtolower(substr($memString,-1))) {
            case "k":
            return round(substr($memString,0,-1)*1024);

            case "m":
            return round(substr($memString,0,-1)*1024*1024);

            case "g":
            return round(substr($memString,0,-1)*1024*1024*1024);

            default:
            return round($memString);
        }
    }

    function listErrors() {
        if($this->errors) {
            echo "<p>The following problems are preventing me from installing CiviCRM:</p>";
            foreach($this->errors as $error) {
                echo "<li>" . htmlentities($error) . "</li>";
            }
        }
    }

    function showTable($section = null) {
        if($section) {
            $tests = $this->tests[$section];
            echo "<table class=\"testResults\" width=\"100%\">";
            foreach($tests as $test => $result) {
                echo "<tr class=\"$result[0]\"><td>$test</td><td>" . nl2br(htmlentities($result[1])) . "</td></tr>";
            }
            echo "</table>";

        } else {
            foreach($this->tests as $section => $tests) {
                echo "<h3>$section</h3>";
                echo "<table class=\"testResults\" width=\"100%\">";

                foreach($tests as $test => $result) {
                    echo "<tr class=\"$result[0]\"><td>$test</td><td>" . nl2br(htmlentities($result[1])) . "</td></tr>";
                }
                echo "</table>";
            }
        }
    }

    function requireFunction($funcName, $testDetails) {
        $this->testing($testDetails);
        if(!function_exists($funcName)) $this->error($testDetails);
        else return true;
    }

    function requirePHPVersion($minVersion, $testDetails, $maxVersion = null) {

        $this->testing($testDetails);

        $phpVersion = phpversion();
        $aboveMinVersion = version_compare($phpVersion, $minVersion) >= 0;
        $belowMaxVersion = $maxVersion ? version_compare($phpVersion, $maxVersion) <  0 : true;

        if ( $maxVersion && $aboveMinVersion && $belowMaxVersion ) {
            return true;
        } else if ( !$maxVersion && $aboveMinVersion ) {
            return true;
        }

        if( ! $testDetails[2] ) {
            if( !$aboveMinVersion ) {
                $testDetails[2] = "You need PHP version $minVersion or later, only {$phpVersion} is installed.  Please upgrade your server, or ask your web-host to do so.";
            } else {
                $testDetails[2] = "PHP version {$phpVersion} is not supported. PHP version earlier than $maxVersion is required. You might want to downgrade your server, or ask your web-host to do so.";
            }
        }

        $this->error($testDetails);
    }

    function requireFile($filename, $testDetails, $absolute = false) {
        $this->testing($testDetails);
        if ( ! $absolute ) {
            $filename = $this->getBaseDir() . $filename;
        }
        if(!file_exists($filename)) {
            $testDetails[2] .= " (file '$filename' not found)";
            $this->error($testDetails);
        }
    }

    function requireNoPathSeparator($testDetails)
    {
        $this->testing($testDetails);
        if (substr_count($this->getBaseDir(), PATH_SEPARATOR)) {
            $this->error($testDetails);
        }
    }

    function requireNoFile($filename, $testDetails) {
        $this->testing($testDetails);
        $filename = $this->getBaseDir() . $filename;
        if(file_exists($filename)) {
            $testDetails[2] .= " (file '$filename' found)";
            $this->error($testDetails);
        }
    }
    function moveFileOutOfTheWay($filename, $testDetails) {
        $this->testing($testDetails);
        $filename = $this->getBaseDir() . $filename;
        if(file_exists($filename)) {
            if(file_exists("$filename.bak")) rm("$filename.bak");
            rename($filename, "$filename.bak");
        }
    }

    function requireWriteable($filename, $testDetails, $absolute = false) {
        $this->testing($testDetails);
        if ( ! $absolute ) {
            $filename = $this->getBaseDir() . $filename;
        }

        if(!is_writeable($filename)) {
            $name = null;
            if ( function_exists( 'posix_getpwuid' ) ) {
                $user = posix_getpwuid(posix_geteuid());
                $name = '- ' . $user['name'] . ' -';
            }

            if ( ! isset( $testDetails[2] ) ) {
                $testDetails[2] = null;
            }
            $testDetails[2] .= "The user account used by your web-server $name needs to be granted write access to the following directory in order to configure the CiviCRM settings file:\n$filename";
            $this->error($testDetails);
        }
    }
    function requireApacheModule($moduleName, $testDetails) {
        $this->testing($testDetails);
        if(!in_array($moduleName, apache_get_modules())) $this->error($testDetails);
    }

    function requireMysqlConnection($server, $username, $password, $testDetails) {
        $this->testing($testDetails);
        $conn = @mysql_connect($server, $username, $password);

        if($conn) {
            return true;
        } else {
            $testDetails[2] .= ": " . mysql_error();
            $this->error($testDetails);
        }
    }

    function requireMySQLServer($server, $testDetails) {
        $this->testing($testDetails);
        $conn = @mysql_connect($server, null, null);

        if($conn || mysql_errno() < 2000) {
            return true;
        } else {
            $testDetails[2] .= ": " . mysql_error();
            $this->error($testDetails);
        }
    }

    function requireMySQLVersion($version, $testDetails) {
        $this->testing($testDetails);

        if(!mysql_get_server_info()) {
            $testDetails[2] = 'Cannot determine the version of MySQL installed. Please ensure at least version 4.1 is installed.';
            $this->warning($testDetails);
        } else {
            list($majorRequested, $minorRequested) = explode('.', $version);
            list($majorHas, $minorHas) = explode('.', mysql_get_server_info());

            if(($majorHas > $majorRequested) || ($majorHas == $majorRequested && $minorHas >= $minorRequested)) {
                return true;
            } else {
                $testDetails[2] .= "{$majorHas}.{$minorHas}.";
                $this->error($testDetails);
            }
        }
    }

    function requireMySQLInnoDB( $server, $username, $password, $database, $testDetails) {
        $this->testing($testDetails);
        $conn = @mysql_connect($server, $username, $password);
        if ( ! $conn ) {
            $testDetails[2] .= ' Could not determine if mysql has innodb support. Assuming no';
            $this->error($testDetails);
            return;
        }

        $result = mysql_query( "SHOW variables like 'have_innodb'", $conn );
        if ( $result ) {
            $values = mysql_fetch_row( $result );
            if ( strtolower( $values[1] ) != 'yes' ) {
                $this->error($testDetails);
            } else {
                $testDetails[3] = 'MySQL server does have innodb support';
            }
        } else {
            $testDetails[2] .= ' Could not determine if mysql has innodb support. Assuming no';
        }
    }

    function requireMySQLTempTables($server, $username, $password, $database, $testDetails) {
        $this->testing($testDetails);
        $conn = @mysql_connect($server, $username, $password);
        if (!$conn) {
            $testDetails[2] = 'Could not login to the database.';
            $this->error($testDetails);
            return;
        }

        if (! @mysql_select_db($database,$conn)) {
            $testDetails[2] = 'Could not select the database.';
            $this->error($testDetails);
            return;
        }

        $result = mysql_query('CREATE TEMPORARY TABLE civicrm_install_temp_table_test (test text)', $conn);
        if (!$result) {
            $this->error($testDetails);
        }
        $result = mysql_query('DROP TEMPORARY TABLE civicrm_install_temp_table_test');
    }

    function requireMySQLLockTables($server, $username, $password, $database, $testDetails) {
        $this->testing($testDetails);
        $conn = @mysql_connect($server, $username, $password);
        if (!$conn) {
            $testDetails[2] = 'Could not login to the database.';
            $this->error($testDetails);
            return;
        }

        if (! @mysql_select_db($database,$conn)) {
            $testDetails[2] = 'Could not select the database.';
            $this->error($testDetails);
            return;
        }

        $result = mysql_query('CREATE TEMPORARY TABLE civicrm_install_temp_table_test (test text)', $conn);
        if (!$result) {
            $testDetails[2] = 'Could not create a table.';
            $this->error($testDetails);
            return;
        }

        $result = mysql_query('LOCK TABLES civicrm_install_temp_table_test WRITE', $conn);
        if (!$result) {
            $testDetails[2] = 'Could not obtain a write lock for the table.';
            $this->error($testDetails);
            $result = mysql_query('DROP TEMPORARY TABLE civicrm_install_temp_table_test');
            return;
        }

        $result = mysql_query('UNLOCK TABLES', $conn);
        if (!$result) {
            $testDetails[2] = 'Could not release the lock for the table.';
            $this->error($testDetails);
            $result = mysql_query('DROP TEMPORARY TABLE civicrm_install_temp_table_test');
            return;
        }

        $result = mysql_query('DROP TEMPORARY TABLE civicrm_install_temp_table_test');
        return;
        
    }

    function requireDatabaseOrCreatePermissions($server,
                                                $username,
                                                $password,
                                                $database,
                                                $testDetails,
                                                $onlyRequire = false) {
        $this->testing($testDetails);
        $conn = @mysql_connect($server, $username, $password);

        $okay = null;
        if(@mysql_select_db($database)) {
            $okay = "Database '$database' exists";
        } else if ( $onlyRequire ) {
            $testDetails[2] = "The database: '$database' does not exist";
            $this->error($testDetails);
            return;
        } else {
            if(@mysql_query("CREATE DATABASE $database")) {
                $okay = "Able to create a new database";
            } else {
                $testDetails[2] .= " (user '$username' doesn't have CREATE DATABASE permissions.)";
                $this->error($testDetails);
                return;
            }
        }

        if($okay) {
            $testDetails[3] = $okay;
            $this->testing($testDetails);
        }

    }

    function requireServerVariables($varNames, $errorMessage) {
        //$this->testing($testDetails);
        foreach($varNames as $varName) {
            if(!$_SERVER[$varName]) $missing[] = '$_SERVER[' . $varName . ']';
        }
        if(!isset($missing)) {
            return true;
        } else {
            $testDetails[2] .= " (the following PHP variables are missing: " . implode(", ", $missing) . ")";
            $this->error($testDetails);
        }
    }

    function isRunningApache($testDetails) {
        $this->testing($testDetails);
        if(function_exists('apache_get_modules') || stristr($_SERVER['SERVER_SIGNATURE'], 'Apache'))
        return true;

        $this->warning($testDetails);
        return false;
    }


    function getBaseDir() {
        return dirname($_SERVER['SCRIPT_FILENAME']) . CIVICRM_DIRECTORY_SEPARATOR;
    }

    function testing($testDetails) {
        if(!$testDetails) return;

        $section = $testDetails[0];
        $test = $testDetails[1];

        $message = "OK";
        if(isset($testDetails[3])) $message .= " ($testDetails[3])";

        $this->tests[$section][$test] = array("good", $message);
    }

    function error($testDetails) {
        $section = $testDetails[0];
        $test = $testDetails[1];

        $this->tests[$section][$test] = array("error", $testDetails[2]);
        $this->errors[] = $testDetails;

    }
    function warning($testDetails) {
        $section = $testDetails[0];
        $test = $testDetails[1];


        $this->tests[$section][$test] = array("warning", $testDetails[2]);
        $this->warnings[] = $testDetails;
    }

    function hasErrors() {
        return sizeof($this->errors);
    }
    function hasWarnings() {
        return sizeof($this->warnings);
    }

}

class Installer extends InstallRequirements {
    function createDatabaseIfNotExists( $server, $username, $password, $database ) {
        $conn = @mysql_connect($server, $username, $password);

        if(@mysql_select_db($database)) {
            // skip if database already present
            return;
        }

        if (@mysql_query("CREATE DATABASE $database")) {
        } else {
            $errorTitle = "Oops! Could not create Database $database";
            $errorMsg = "We encountered an error when attempting to create the database. Please check your mysql server permissions and the database name and try again.";
            errorDisplayPage( $errorTitle, $errorMsg );
        }
    }

    function install($config) {
        echo '<link rel="stylesheet" type="text/css" href="template.css" />';
        echo '<div style="padding: 1em;"><h1>Installing CiviCRM...</h1>
              <p>I am now running through the installation steps (this should take a few minutes)<p/>
              <p>If you receive a fatal error, refresh this page to continue the installation</p>';

        flush();

        // Load the sapphire runtime
        echo '<br/>Building database schema and setup files...</div>';
        flush();

        // create database if does not exists
        $this->createDatabaseIfNotExists( $config['mysql']['server'],
            $config['mysql']['username'],
            $config['mysql']['password'],
            $config['mysql']['database'] );
        // Build database
        require_once 'civicrm.php';
        civicrm_main( $config );

        // clean output
        @ob_clean();

        if(! $this->errors) {
            global $installType;
            echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
            echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">';
            echo '<head>';
            echo '<title>CiviCRM Installed</title>';
            echo '<link rel="stylesheet" type="text/css" href="template.css" />';
            echo '</head>';
            echo '<body>';
            echo '<div style="padding: 1em;"><p class="good">CiviCRM has been successfully installed</p>';
            echo '<ul>';
            $docLinkConfig = CRM_Utils_System::docURL2( 'Configuring a New Site', false, 'here' );
            if (!function_exists('ts')) {
                $docLinkConfig = "<a href=\"{$docLinkConfig}\">here</a>";
            }
            if ( $installType == 'drupal' ) {
                $drupalURL     = civicrm_cms_base( );
                $drupalPermissionsURL = "{$drupalURL}index.php?q=admin/user/permissions";
                $drupalURL .= "index.php?q=civicrm/admin/configtask&reset=1";
                $registerSiteURL = "http://civicrm.org/civicrm/profile/create?reset=1&gid=15";
                
                echo "<li>Drupal user permissions have been automatically set - giving anonymous and authenticated users access to public CiviCRM forms and features. We recommend that you <a target='_blank' href={$drupalPermissionsURL}>review these permissions</a> to ensure that they are appropriate for your requirements (<a target='_blank' href='http://wiki.civicrm.org/confluence/display/CRMDOC/Default+Permissions+and+Roles'>learn more...</a>)</li>
                      <li>Use the <a target='_blank' href=\"$drupalURL\">Configuration Checklist</a> to review and configure settings for your new site</li>
                      <li> Have you registered this site at CiviCRM.org? If not, please help strengthen the CiviCRM ecosystem by taking a few minutes to <a href='$registerSiteURL' target='_blank'>fill out the site registration form</a>. The information collected will help us prioritize improvements, target our communications and build the community. If you have a technical role for this site, be sure to check Keep in Touch to receive technical updates (a low volume  mailing list).</li>";
                
                // explicitly setting error reporting, since we cannot handle drupal related notices
                error_reporting(1);
                
                // automatically enable CiviCRM module once it is installed successfully.
                // so we need to Bootstrap Drupal, so that we can call drupal hooks.
                global $cmsPath, $crmPath;

                // relative / abosolute paths are not working for drupal, hence using chdir()
                chdir( $cmsPath ); 

                include_once "./includes/bootstrap.inc";
                drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

                // rebuild modules, so that civicrm is added
                module_rebuild_cache( );

                // now enable civicrm module.
                module_enable( array('civicrm') );

                // clear block and page cache, to make sure civicrm link is present in navigation block
                cache_clear_all();
                
                //add basic drupal permissions
                db_query( 'UPDATE {permission} SET perm = CONCAT( perm, \', access CiviMail subscribe/unsubscribe pages, access all custom data, access uploaded files, make online contributions, profile create, profile edit, profile view, register for events, view event info\') WHERE rid IN (1, 2)' );
                
            }

            echo '</ul>';
            echo '</div>';
            echo '</body>';
            echo '</html>';
        }

        return $this->errors;
    }
}

function getSiteDir( $cmsPath, $str ) {
    static $siteDir = '';
    
    if ( $siteDir ) {
        return $siteDir;
    }
    
    $sites   = CIVICRM_DIRECTORY_SEPARATOR . 'sites'   . CIVICRM_DIRECTORY_SEPARATOR;
    $modules = CIVICRM_DIRECTORY_SEPARATOR . 'modules' . CIVICRM_DIRECTORY_SEPARATOR;
    preg_match( "/" . preg_quote($sites, CIVICRM_DIRECTORY_SEPARATOR) . 
                "([a-zA-Z0-9_.]+)" . 
                preg_quote($modules, CIVICRM_DIRECTORY_SEPARATOR) . "/",
                $_SERVER['SCRIPT_FILENAME'], $matches );
    $siteDir = isset($matches[1]) ? $matches[1] : 'default';
    
    if ( strtolower( $siteDir ) == 'all' ) {
        // For this case - use drupal's way of finding out multi-site directory
        $uri    = explode(CIVICRM_DIRECTORY_SEPARATOR, $_SERVER['SCRIPT_FILENAME']);
        $server = explode('.', implode('.', array_reverse(explode(':', rtrim($_SERVER['HTTP_HOST'], '.')))));
        for ($i = count($uri) - 1; $i > 0; $i--) {
            for ($j = count($server); $j > 0; $j--) {
                $dir = implode('.', array_slice($server, -$j)) . implode('.', array_slice($uri, 0, $i));
                if (file_exists($cmsPath  . CIVICRM_DIRECTORY_SEPARATOR . 
                                'sites'   . CIVICRM_DIRECTORY_SEPARATOR . $dir)) {
                    $siteDir = $dir;
                    return $siteDir;
                }
            }
        }
        $siteDir = 'default';
    }

    return $siteDir;
}

function errorDisplayPage( $errorTitle, $errorMsg ) {
    include('error.html');
    exit();
}

