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
 * Config handles all the run time configuration changes that the system needs to deal with.
 * Typically we'll have different values for a user's sandbox, a qa sandbox and a production area.
 * The default values in general, should reflect production values (minimizes chances of screwing up)
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

require_once 'Log.php';
require_once 'Mail.php';

require_once 'CRM/Core/DAO.php';
require_once 'CRM/Utils/System.php';
require_once 'CRM/Utils/File.php';
require_once 'CRM/Core/Session.php';
require_once 'CRM/Core/Config/Variables.php';

class CRM_Core_Config extends CRM_Core_Config_Variables
{
    ///
    /// BASE SYSTEM PROPERTIES (CIVICRM.SETTINGS.PHP)
    ///

    /**
     * the dsn of the database connection
     * @var string
     */
    public $dsn;

    /**
     * the name of user framework
     * @var string
     */
    public $userFramework               = 'Drupal';

    /**
     * the name of user framework url variable name
     * @var string
     */
    public $userFrameworkURLVar         = 'q';

    /**
     * the dsn of the database connection for user framework
     * @var string
     */
    public $userFrameworkDSN            = null;

    /**
     * The root directory where Smarty should store
     * compiled files
     * @var string
     */
    public $templateCompileDir  = './templates_c/en_US/';

    public $configAndLogDir = null;

    // END: BASE SYSTEM PROPERTIES (CIVICRM.SETTINGS.PHP)

    ///
    /// BEGIN HELPER CLASS PROPERTIES
    ///
    
    /**
     * are we initialized and in a proper state
     * @var string
     */
    public $initialized = 0;

    /**
     * the factory class used to instantiate our DB objects
     * @var string
     */
    private $DAOFactoryClass	  = 'CRM_Contact_DAO_Factory';

    /**
     * The handle to the log that we are using
     * @var object
     */
    private static $_log = null;

    /**
     * the handle on the mail handler that we are using
     * @var object
     */
    private static $_mail = null;
    
    /**
     * We only need one instance of this object. So we use the singleton
     * pattern and cache the instance in this variable
     * @var object
     * @static
     */
    private static $_singleton = null;

    /**
     * component registry object (of CRM_Core_Component type)
     */
    public $componentRegistry  = null;

    ///
    /// END HELPER CLASS PROPERTIES
    ///

    ///
    /// RUNTIME SET CLASS PROPERTIES
    ///

    /**
     * to determine wether the call is from cms or civicrm 
     */
    public $inCiviCRM  = false;

    ///
    /// END: RUNTIME SET CLASS PROPERTIES
    ///

    /**
     *  Define recaptcha key
     */
    
    public $recaptchaPublicKey;
    
    /**
     * The constructor. Sets domain id if defined, otherwise assumes
     * single instance installation.
     *
     * @return void
     * @access private
     */
    private function __construct() 
    {
    }

    /**
     * Singleton function used to manage this object.
     *
     * @param $loadFromDB boolean  whether to load from the database
     * @param $force      boolean  whether to force a reconstruction
     *
     * @return object
     * @static
     */
    static function &singleton($loadFromDB = true, $force = false)
    {
        if ( self::$_singleton === null || $force ) {
            // lets ensure we set E_DEPRECATED to minimize errors
            // CRM-6327
            if ( defined( 'E_DEPRECATED' ) ) {
                error_reporting( error_reporting( ) & ~E_DEPRECATED );
            }

            // first, attempt to get configuration object from cache
            require_once 'CRM/Utils/Cache.php';
            $cache =& CRM_Utils_Cache::singleton( );
            self::$_singleton = $cache->get( 'CRM_Core_Config' );


            // if not in cache, fire off config construction
            if ( ! self::$_singleton ) {
                self::$_singleton = new CRM_Core_Config;
                self::$_singleton->_initialize( $loadFromDB );
                
                //initialize variables. for gencode we cannot load from the
                //db since the db might not be initialized
                if ( $loadFromDB ) {
                    self::$_singleton->_initVariables( );
                    
                    // retrieve and overwrite stuff from the settings file
                    self::$_singleton->setCoreVariables( );
                }
                $cache->set( 'CRM_Core_Config', self::$_singleton );
            } else {
                // we retrieve the object from memcache, so we now initialize the objects
                self::$_singleton->_initialize( $loadFromDB );
                
                // add component specific settings
                self::$_singleton->componentRegistry->addConfig( self::$_singleton );
            }
            
            self::$_singleton->initialized = 1;

            if ( isset( self::$_singleton->customPHPPathDir ) &&
                 self::$_singleton->customPHPPathDir ) {
                $include_path = self::$_singleton->customPHPPathDir . PATH_SEPARATOR . get_include_path( );
                set_include_path( $include_path );
            }

            // set the callback at the very very end, to avoid an infinite loop 
            // set the error callback
            CRM_Core_Error::setCallback();

            // call the hook so other modules can add to the config
            // again doing this at the very very end
            require_once 'CRM/Utils/Hook.php';
            CRM_Utils_Hook::config( self::$_singleton );

            // make sure session is always initialised            
            $session = CRM_Core_Session::singleton();
        }
        return self::$_singleton;
    }


    private function _setUserFrameworkConfig( $userFramework ) {

        $this->userFrameworkClass  = 'CRM_Utils_System_'    . $userFramework;
        $this->userHookClass       = 'CRM_Utils_Hook_'      . $userFramework;
        $this->userPermissionClass = 'CRM_Core_Permission_' . $userFramework;            

        if ( $userFramework == 'Joomla' ) {
            $this->userFrameworkURLVar = 'task';
        }

        if ( defined( 'CIVICRM_UF_BASEURL' ) ) {
            $this->userFrameworkBaseURL = CRM_Utils_File::addTrailingSlash( CIVICRM_UF_BASEURL, '/' );
            if ($userFramework == 'Drupal' and function_exists('variable_get')) {
                global $language;
                if (module_exists('locale') && $mode = variable_get('language_negotiation', LANGUAGE_NEGOTIATION_NONE)) {
                    if (isset($language->prefix) and $language->prefix
                        and ($mode == LANGUAGE_NEGOTIATION_PATH_DEFAULT or $mode == LANGUAGE_NEGOTIATION_PATH)) {
                        $this->userFrameworkBaseURL .= $language->prefix . '/';
                }
                    if (isset($language->domain) and $language->domain and $mode == LANGUAGE_NEGOTIATION_DOMAIN) {
                        $this->userFrameworkBaseURL = CRM_Utils_File::addTrailingSlash( $language->domain, '/' );
                    }
                }
            }
            if ( isset( $_SERVER['HTTPS'] ) &&
                 strtolower( $_SERVER['HTTPS'] ) != 'off' ) {
                $this->userFrameworkBaseURL     = str_replace( 'http://', 'https://', 
                                                               $this->userFrameworkBaseURL );
            }
        }

        if ( defined( 'CIVICRM_UF_DSN' ) ) { 
            $this->userFrameworkDSN = CIVICRM_UF_DSN;
        }

        // this is dynamically figured out in the civicrm.settings.php file
        if ( defined( 'CIVICRM_CLEANURL' ) ) {        
            $this->cleanURL = CIVICRM_CLEANURL;
        } else {
            $this->cleanURL = 0;
        }

        if ( $userFramework == 'Joomla' ) {
            $this->userFrameworkVersion = '1.5';
            if ( class_exists('JVersion') ) {
                $version = new JVersion;
                $this->userFrameworkVersion = $version->getShortVersion();
            }

            global $mainframe;
            $dbprefix = $mainframe ? $mainframe->getCfg( 'dbprefix' ) : 'jos_';
            $this->userFrameworkUsersTableName = $dbprefix . 'users';
        }

        if ( $userFramework == 'Drupal' && defined('VERSION') ) {
            $this->userFrameworkVersion = VERSION;
        }    
    }


    /**
     * Initializes the entire application.
     * Reads constants defined in civicrm.settings.php and
     * stores them in config properties.
     *
     * @return void
     * @access public
     */
    private function _initialize( $loadFromDB = true ) 
    {

        // following variables should be set in CiviCRM settings and
        // as crucial ones, are defined upon initialisation
        // instead of in CRM_Core_Config_Defaults
        if (defined( 'CIVICRM_DSN' )) {
            $this->dsn = CIVICRM_DSN;
        } else if ( $loadFromDB ) {
            // bypass when calling from gencode
            echo 'You need to define CIVICRM_DSN in civicrm.settings.php';
            exit( );
        }

        if (defined('CIVICRM_TEMPLATE_COMPILEDIR')) {
            $this->templateCompileDir = CRM_Utils_File::addTrailingSlash(CIVICRM_TEMPLATE_COMPILEDIR);

            // we're automatically prefixing compiled templates directories with country/language code
            global $tsLocale;
            if (!empty($tsLocale)) {
                $this->templateCompileDir .= CRM_Utils_File::addTrailingSlash($tsLocale);
            } elseif (!empty($this->lcMessages)) {
                $this->templateCompileDir .= CRM_Utils_File::addTrailingSlash($this->lcMessages);
            }

            // also make sure we create the config directory within this directory
            // the below statement will create both the templates directory and the config and log directory
            $this->configAndLogDir = $this->templateCompileDir . 'ConfigAndLog' . DIRECTORY_SEPARATOR;
            CRM_Utils_File::createDir( $this->configAndLogDir );
        } else if ( $loadFromDB ) {
            echo 'You need to define CIVICRM_TEMPLATE_COMPILEDIR in civicrm.settings.php';
            exit( );
        }

        if ( defined( 'CIVICRM_UF' ) ) {
            $this->userFramework       = CIVICRM_UF;
            $this->_setUserFrameworkConfig( $this->userFramework );
        } else {
            echo 'You need to define CIVICRM_UF in civicrm.settings.php';
            exit( );
        }

        $this->_initDAO( );

        // also initialize the logger
        self::$_log =& Log::singleton( 'display' );

        // initialize component registry early to avoid "race" 
        // between CRM_Core_Config and CRM_Core_Component (they
        // are co-dependant)
        require_once 'CRM/Core/Component.php';
        $this->componentRegistry = new CRM_Core_Component();
    }


    /**
     * initialize the DataObject framework
     *
     * @return void
     * @access private
     */
    private function _initDAO() 
    {
        CRM_Core_DAO::init( $this->dsn );

        $factoryClass = $this->DAOFactoryClass;
        require_once str_replace('_', DIRECTORY_SEPARATOR, $factoryClass) . '.php';
        CRM_Core_DAO::setFactory(new $factoryClass());
    }

    /**
     * returns the singleton logger for the application
     *
     * @param
     * @access private
     * @return object
     */
    static public function &getLog() 
    {
        if ( ! isset( self::$_log ) ) {
            self::$_log =& Log::singleton( 'display' );
        }

        return self::$_log;
    }

    /**
     * initialize the config variables
     *
     * @return void
     * @access private
     */
    private function _initVariables() 
    {
        // retrieve serialised settings
        require_once "CRM/Core/BAO/Setting.php";
        $variables = array();
        CRM_Core_BAO_Setting::retrieve($variables);  

        // if settings are not available, go down the full path
        if ( empty( $variables ) ) {
            // Step 1. get system variables with their hardcoded defaults
            $variables = get_object_vars($this);
            
            // Step 2. get default values (with settings file overrides if
            // available - handled in CRM_Core_Config_Defaults)
            require_once 'CRM/Core/Config/Defaults.php';
            CRM_Core_Config_Defaults::setValues( $variables );

            // retrieve directory and url preferences also
            require_once 'CRM/Core/BAO/Preferences.php';
            CRM_Core_BAO_Preferences::retrieveDirectoryAndURLPreferences( $defaults );

            // add component specific settings
            $this->componentRegistry->addConfig( $this );

            // serialise settings 
            CRM_Core_BAO_Setting::add($variables);            
        }

        $urlArray     = array('userFrameworkResourceURL', 'imageUploadURL');
        $dirArray     = array('uploadDir','customFileUploadDir');
        
        foreach($variables as $key => $value) {
            if ( in_array($key, $urlArray) ) {
                $value = CRM_Utils_File::addTrailingSlash( $value, '/' );
            } else if ( in_array($key, $dirArray) ) {
                $value = CRM_Utils_File::addTrailingSlash( $value );
                if ( CRM_Utils_File::createDir( $value, false ) === false ) {
                    // seems like we could not create the directories
                    // settings might have changed, lets suppress a message for now
                    // so we can make some more progress and let the user fix their settings 
                    // for now we assign it to a know value
                    // CRM-4949
                    $value = $this->templateCompileDir;
                    $url = CRM_Utils_System::url( 'civicrm/admin/setting/path', 'reset=1' );
                    CRM_Core_Session::setStatus(ts('%1 has an incorrect directory path. Please go to the <a href="%2">path setting page</a> and correct it.', array(1 => $key, 2 => $url)) . '<br/>');
                }
            } else if ( $key == 'lcMessages' ) {
                // reset the templateCompileDir to locale-specific and make sure it exists
                if ( substr( $this->templateCompileDir, -1 * strlen( $value ) - 1, -1 ) != $value ) {
                    $this->templateCompileDir .= CRM_Utils_File::addTrailingSlash($value);
                    CRM_Utils_File::createDir( $this->templateCompileDir );
                }
            }
            
            $this->$key = $value;
        }
        
        if ( $this->userFrameworkResourceURL ) {
            // we need to do this here so all blocks also load from an ssl server
            if ( isset( $_SERVER['HTTPS'] ) &&
                 strtolower( $_SERVER['HTTPS'] ) != 'off' ) {
                CRM_Utils_System::mapConfigToSSL( );
            }
            $rrb = parse_url( $this->userFrameworkResourceURL );
            // dont use absolute path if resources are stored on a different server
            // CRM-4642
            $this->resourceBase = $this->userFrameworkResourceURL;
            if ( isset( $_SERVER['HTTP_HOST']) ) {
                $this->resourceBase = ($rrb['host'] == $_SERVER['HTTP_HOST']) ? $rrb['path'] : $this->userFrameworkResourceURL;
            }
        } 
            
        if ( !$this->customFileUploadDir ) {
            $this->customFileUploadDir = $this->uploadDir;
        }
        
        if ( $this->mapProvider ) {
            $this->geocodeMethod = 'CRM_Utils_Geocode_'. $this->mapProvider ;
        }
    }

    /**
     * retrieve a mailer to send any mail from the applciation
     *
     * @param
     * @access private
     * @return object
     */
    static function &getMailer() 
    {
        if ( ! isset( self::$_mail ) ) {
            require_once "CRM/Core/BAO/Preferences.php";
            $mailingInfo =& CRM_Core_BAO_Preferences::mailingPreferences();;
                        
            if ( defined( 'CIVICRM_MAILER_SPOOL' ) &&
                 CIVICRM_MAILER_SPOOL ) {
                require_once 'CRM/Mailing/BAO/Spool.php';
                self::$_mail = new CRM_Mailing_BAO_Spool();
            } elseif ($mailingInfo['outBound_option'] == 0 ) {
                if ( $mailingInfo['smtpServer'] == '' ||
                     ! $mailingInfo['smtpServer'] ) {
                    CRM_Core_Error::fatal( ts( 'There is no valid smtp server setting. Click <a href=\'%1\'>Administer CiviCRM >> Global Settings</a> to set the SMTP Server.', array( 1 => CRM_Utils_System::url('civicrm/admin/setting', 'reset=1')))); 
                }
                
                $params['host'] = $mailingInfo['smtpServer'] ? $mailingInfo['smtpServer'] : 'localhost';
                $params['port'] = $mailingInfo['smtpPort'] ? $mailingInfo['smtpPort'] : 25;
                
                if ($mailingInfo['smtpAuth']) {
                    require_once 'CRM/Utils/Crypt.php';
                    $params['username'] = $mailingInfo['smtpUsername'];
                    $params['password'] = CRM_Utils_Crypt::decrypt( $mailingInfo['smtpPassword'] );
                    $params['auth']     = true;
                } else {
                    $params['auth']     = false;
                }

                // set the localhost value, CRM-3153
                $params['localhost'] = $_SERVER['SERVER_NAME'];

                self::$_mail =& Mail::factory( 'smtp', $params );
            } elseif ($mailingInfo['outBound_option'] == 1) {
                if ( $mailingInfo['sendmail_path'] == '' ||
                     ! $mailingInfo['sendmail_path'] ) {
                    CRM_Core_Error::fatal( ts( 'There is no valid sendmail path setting. Click <a href=\'%1\'>Administer CiviCRM >> Global Settings</a> to set the Sendmail Server.', array( 1 => CRM_Utils_System::url('civicrm/admin/setting', 'reset=1')))); 
                }
                $params['sendmail_path'] = $mailingInfo['sendmail_path'];
                $params['sendmail_args'] = $mailingInfo['sendmail_args'];
                
                self::$_mail =& Mail::factory( 'sendmail', $params );
            } elseif ($mailingInfo['outBound_option'] == 3) {
                $params = array( );
                self::$_mail =& Mail::factory( 'mail', $params );
            } else {
                CRM_Core_Session::setStatus( ts( 'There is no valid SMTP server Setting Or SendMail path setting. Click <a href=\'%1\'>Administer CiviCRM >> Global Settings</a> to set the OutBound Email.', array( 1 => CRM_Utils_System::url('civicrm/admin/setting', 'reset=1'))));
 
            }
        }
        return self::$_mail;
    }
    
    /**
     * delete the web server writable directories
     *
     * @param int $value 1 - clean templates_c, 2 - clean upload, 3 - clean both
     *
     * @access public
     * @return void
     */
    public function cleanup( $value , $rmdir = true ) 
    {
        $value = (int ) $value;

        if ( $value & 1 ) {
            // clean templates_c
            CRM_Utils_File::cleanDir ( $this->templateCompileDir, $rmdir );
            CRM_Utils_File::createDir( $this->templateCompileDir );
        }
        if ( $value & 2 ) {
            // clean upload dir
            CRM_Utils_File::cleanDir ( $this->uploadDir );
            CRM_Utils_File::createDir( $this->uploadDir );
            CRM_Utils_File::restrictAccess($this->uploadDir);
        }
    }


    /**
     * verify that the needed parameters are not null in the config
     *
     * @param CRM_Core_Config (reference ) the system config object
     * @param array           (reference ) the parameters that need a value
     *
     * @return boolean
     * @static
     * @access public
     */
    static function check( &$config, &$required ) 
    {
        foreach ( $required as $name ) {
            if ( CRM_Utils_System::isNull( $config->$name ) ) {
                return false;
            }
        }
        return true;
    }

    /**
     * reset the serialized array and recompute
     * use with care
     */
    function reset( ) {
        $query = "UPDATE civicrm_domain SET config_backend = null";
        CRM_Core_DAO::executeQuery( $query );
    }

    /**
     * one function to get domain ID
     */
    static function domainID( ) {
        return defined( 'CIVICRM_DOMAIN_ID' ) ? CIVICRM_DOMAIN_ID : 1;
    }

    /**
     * clear db cache
     */
    function clearDBCache( ) {
        $queries = array( 'TRUNCATE TABLE civicrm_acl_cache',
                          'TRUNCATE TABLE civicrm_acl_contact_cache',
                          'TRUNCATE TABLE civicrm_cache',
                          'UPDATE civicrm_group SET cache_date = NULL',
                          'TRUNCATE TABLE civicrm_group_contact_cache',
                          'TRUNCATE TABLE civicrm_menu',
                          'UPDATE civicrm_preferences SET navigation = NULL WHERE contact_id IS NOT NULL',
                          );

        foreach ( $queries as $query ) {
            CRM_Core_DAO::executeQuery( $query );
        }
    }

    /**
     * clear leftover temporary tables
     */
    function clearTempTables( ) {
        // CRM-5645
        require_once 'CRM/Contact/DAO/Contact.php';
        $dao = new CRM_Contact_DAO_Contact( );
        $query = "
 SELECT TABLE_NAME as import_table
   FROM INFORMATION_SCHEMA.TABLES
  WHERE TABLE_SCHEMA = %1 AND TABLE_NAME LIKE 'civicrm_import_job_%'";
        $params = array( 1 => array( $dao->database(), 'String' ) );
        $tableDAO = CRM_Core_DAO::executeQuery( $query, $params );
        $importTables = array();
        while ( $tableDAO->fetch() ) {
            $importTables[] = $tableDAO->import_table;
        }
        if ( !empty( $importTables ) ) {
                $importTable = implode(',', $importTables);
                // drop leftover import temporary tables
                CRM_Core_DAO::executeQuery( "DROP TABLE $importTable" );
        }

    }
    
    /**
     * function to check if running in upgrade mode
     */
    function isUpgradeMode( $path = null ) {
        if ( $path && $path == 'civicrm/upgrade' ) {
            return true;
        }
        $config =& self::singleton( );
        if ( CRM_Utils_Array::value( $config->userFrameworkURLVar, $_GET ) == 'civicrm/upgrade' ) {
            return true;
        }
        return false;
    }
    
    /**
     * Wrapper function to allow unit tests to switch user framework on the fly
     */    
    public function setUserFramework( $userFramework = null ) {
        $this->userFramework       = $userFramework;
        $this->_setUserFrameworkConfig( $userFramework );
    }
    
} // end CRM_Core_Config
