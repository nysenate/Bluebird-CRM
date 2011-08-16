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
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

/**
 * file contains functions used in civicrm configuration
 * 
 */
class CRM_Core_BAO_Setting 
{
    /**
     * Function to add civicrm settings
     *
     * @params array $params associated array of civicrm variables
     *
     * @return null
     * @static
     */
    static function add(&$params) 
    {
        CRM_Core_BAO_Setting::fixParams($params);

        // also set a template url so js files can use this
        // CRM-6194
        $params['civiRelativeURL'] = CRM_Utils_System::url( 'CIVI_BASE_TEMPLATE' );
        $params['civiRelativeURL'] = str_replace( 'CIVI_BASE_TEMPLATE', 
                                                  '',
                                                  $params['civiRelativeURL'] );

        require_once "CRM/Core/DAO/Domain.php";
        $domain = new CRM_Core_DAO_Domain();
        $domain->id = CRM_Core_Config::domainID( );
        $domain->find(true);
        if ($domain->config_backend) {
            $values = unserialize($domain->config_backend);
            CRM_Core_BAO_Setting::formatParams($params, $values);
        }

        // CRM-6151
        if ( isset( $params['localeCustomStrings'] ) &&
             is_array( $params['localeCustomStrings'] ) ) {
            $domain->locale_custom_strings = serialize( $params['localeCustomStrings'] );
        }
            
        // unset any of the variables we read from file that should not be stored in the database
        // the username and certpath are stored flat with _test and _live
        // check CRM-1470
        $skipVars = array( 'dsn', 'templateCompileDir',
                           'userFrameworkDSN', 
                           'userFrameworkBaseURL', 'userFrameworkClass', 'userHookClass',
                           'userPermissionClass', 'userFrameworkURLVar', 'userFrameworkVersion',
                           'newBaseURL', 'newBaseDir', 'newSiteName', 'configAndLogDir',
                           'qfKey', 'gettextResourceDir', 'cleanURL',
                           'locale_custom_strings', 'localeCustomStrings' );
        foreach ( $skipVars as $var ) {
            unset( $params[$var] );
        }

        require_once 'CRM/Core/BAO/Preferences.php';
        CRM_Core_BAO_Preferences::fixAndStoreDirAndURL( $params );

        // also skip all Dir Params, we dont need to store those in the DB!
        foreach ( $params as $name => $val ) {
            if ( substr( $name, -3 ) == 'Dir' ) {
                unset( $params[$name] );
            }
        }
        
        //keep user preferred language upto date, CRM-7746
        $session = CRM_Core_Session::singleton( );
        $lcMessages = CRM_Utils_Array::value( 'lcMessages', $params );
        if ( $lcMessages && $session->get('userID') ) {
            $languageLimit = CRM_Utils_Array::value( 'languageLimit', $params );
            if ( is_array( $languageLimit ) &&
                 !in_array( $lcMessages, array_keys( $languageLimit ) ) ) {
                $lcMessages = $session->get( 'lcMessages' );
            }
            
            require_once 'CRM/Core/DAO/UFMatch.php';
            $ufm = new CRM_Core_DAO_UFMatch();
            $ufm->contact_id = $session->get('userID');
            if ( $lcMessages && $ufm->find( true ) ) {
                $ufm->language = $lcMessages;
                $ufm->save( );
                $session->set( 'lcMessages', $lcMessages );
                $params['lcMessages'] = $lcMessages;
            }
        }
        
        $domain->config_backend = serialize($params);
        $domain->save();
    }

    /**
     * Function to fix civicrm setting variables
     *
     * @params array $params associated array of civicrm variables
     *
     * @return null
     * @static
     */
    static function fixParams(&$params) 
    {
        // in our old civicrm.settings.php we were using ISO code for country and
        // province limit, now we have changed it to use ids

        $countryIsoCodes = CRM_Core_PseudoConstant::countryIsoCode( );
        
        $specialArray = array('countryLimit', 'provinceLimit');
        
        foreach($params as $key => $value) {
            if ( in_array($key, $specialArray) && is_array($value) ) {
                foreach( $value as $k => $val ) {
                    if ( !is_numeric($val) ) {
                        $params[$key][$k] = array_search($val, $countryIsoCodes); 
                    }
                }
            } else if ( $key == 'defaultContactCountry' ) {
                if ( !is_numeric($value) ) {
                    $params[$key] =  array_search($value, $countryIsoCodes); 
                }
            }
        }
    }

    /**
     * Function to format the array containing before inserting in db
     *
     * @param  array $params associated array of civicrm variables(submitted)
     * @param  array $values associated array of civicrm variables stored in db
     *
     * @return null
     * @static
     */
    static function formatParams(&$params, &$values) 
    {
        if ( empty( $params ) ||
             ! is_array( $params ) ) {
            $params = $values;
        } else {
            foreach ($params as $key => $val) {
                if ( array_key_exists($key, $values)) {
                    unset($values[$key]);
                }
            }
            $params = array_merge($params, $values);
        }
    }

    /**
     * Function to retrieve the settings values from db
     *
     * @return array $defaults  
     * @static
     */
    static function retrieve(&$defaults) 
    {
        require_once "CRM/Core/DAO/Domain.php";
        $domain = new CRM_Core_DAO_Domain();
        
        //we are initializing config, really can't use, CRM-7863
        $urlVar = 'q';
        if ( defined( 'CIVICRM_UF' ) && CIVICRM_UF == 'Joomla' ) {
            $urlVar = 'task';
        }

        if ( CRM_Utils_Array::value( $urlVar, $_GET ) == 'civicrm/upgrade' || defined('CIVICRM_UPGRADE_ACTIVE') ) {
            $domain->selectAdd( 'config_backend' );
        } else if ( CRM_Utils_Array::value( $urlVar, $_GET ) == 'admin/modules/list/confirm' ) {
            $domain->selectAdd( 'config_backend', 'locales' );
        } else {
            $domain->selectAdd( 'config_backend, locales, locale_custom_strings' );
        }
        
        $domain->id = CRM_Core_Config::domainID( );
        $domain->find(true);
        if ($domain->config_backend) {
            $defaults = unserialize($domain->config_backend);
            if ( $defaults === false ||
                 ! is_array( $defaults ) ) {
                $defaults = array( );
                return;
            }

            $skipVars = array( 'dsn', 'templateCompileDir',
                               'userFrameworkDSN', 
                               'userFrameworkBaseURL', 'userFrameworkClass', 'userHookClass',
                              'userPermissionClass', 'userFrameworkURLVar', 'userFrameworkVersion',
                               'newBaseURL', 'newBaseDir', 'newSiteName', 'configAndLogDir',
                               'qfKey', 'gettextResourceDir', 'cleanURL',
                               'locale_custom_strings', 'localeCustomStrings' );
            foreach ( $skipVars as $skip ) {
                if ( array_key_exists( $skip, $defaults ) ) {
                    unset( $defaults[$skip] );
                }
            }

            // since language field won't be present before upgrade.
            if ( CRM_Utils_Array::value( 'q', $_GET ) == 'civicrm/upgrade' ) {
                return;
            }


            // check if there are any locale strings
            if ( $domain->locale_custom_strings ) {
                $defaults['localeCustomStrings'] = unserialize($domain->locale_custom_strings);
            } else {
                $defaults['localeCustomStrings'] = null;
            }

            // are we in a multi-language setup?
            $multiLang = $domain->locales ? true : false;

            // set the current language
            $lcMessages = null;

            $session = CRM_Core_Session::singleton();

            // for logging purposes, pass the userID to the db
            if ($session->get('userID')) {
                CRM_Core_DAO::executeQuery('SET @civicrm_user_id = %1', array(1 => array($session->get('userID'), 'Integer')));
            }

            // on multi-lang sites based on request and civicrm_uf_match
            if ($multiLang) {
                require_once 'CRM/Utils/Request.php';
                $lcMessagesRequest = CRM_Utils_Request::retrieve('lcMessages', 'String', $this);
                $languageLimit = array( ); 
                if ( array_key_exists( 'languageLimit', $defaults ) && is_array( $defaults['languageLimit'] ) ) {
                    $languageLimit = $defaults['languageLimit'];
                }
                
                if ( in_array($lcMessagesRequest, array_keys( $languageLimit ) ) ) {
                    $lcMessages = $lcMessagesRequest;
                    
                    //CRM-8559, cache navigation do not respect locale if it is changed, so reseting cache.
                    require_once 'CRM/Core/BAO/Cache.php';
                    CRM_Core_BAO_Cache::deleteGroup( 'navigation' );
                } else {
                    $lcMessagesRequest = null;
                }

                if (!$lcMessagesRequest) {
                    $lcMessagesSession = $session->get('lcMessages');
                    if ( in_array( $lcMessagesSession, array_keys( $languageLimit ) ) ) {
                        $lcMessages = $lcMessagesSession;
                    } else {
                        $lcMessagesSession = null;
                    }
                }

                if ($lcMessagesRequest) {
                    require_once 'CRM/Core/DAO/UFMatch.php';
                    $ufm = new CRM_Core_DAO_UFMatch();
                    $ufm->contact_id = $session->get('userID');
                    if ($ufm->find(true)) {
                        $ufm->language = $lcMessages;
                        $ufm->save();
                    }
                    $session->set('lcMessages', $lcMessages);
                }
                
                if (!$lcMessages and $session->get('userID')) {
                    require_once 'CRM/Core/DAO/UFMatch.php';
                    $ufm = new CRM_Core_DAO_UFMatch();
                    $ufm->contact_id = $session->get('userID');
                    if ( $ufm->find( true ) && 
                         in_array( $ufm->language, array_keys( $languageLimit ) ) ) {
                        $lcMessages = $ufm->language;
                    }
                    $session->set('lcMessages', $lcMessages);
                }
            }
            global $dbLocale;

            // if unset and the install is so configured, try to inherit the language from the hosting CMS
            if ($lcMessages === null and CRM_Utils_Array::value( 'inheritLocale', $defaults ) ) {
                // FIXME: On multilanguage installs, CRM_Utils_System::getUFLocale() in many cases returns nothing if $dbLocale is not set
                $dbLocale = $multiLang ? "_{$defaults['lcMessages']}" : '';
                require_once 'CRM/Utils/System.php';
                $lcMessages = CRM_Utils_System::getUFLocale();
                require_once 'CRM/Core/BAO/CustomOption.php';
                if ($domain->locales and !in_array($lcMessages, explode(CRM_Core_DAO::VALUE_SEPARATOR,
                                                                        $domain->locales))) {
                    $lcMessages = null;
                }
            }
            
            if ( $lcMessages ) {
                // update config lcMessages - CRM-5027 fixed.
                $defaults['lcMessages'] = $lcMessages;
            } else {
                // if a single-lang site or the above didn't yield a result, use default
                $lcMessages = $defaults['lcMessages'];
            }
            
            // set suffix for table names - use views if more than one language
            $dbLocale = $multiLang ? "_{$lcMessages}" : '';

            // FIXME: an ugly hack to fix CRM-4041
            global $tsLocale;
            $tsLocale = $lcMessages;

            // FIXME: as bad aplace as any to fix CRM-5428 
            // (to be moved to a sane location along with the above)
            if (function_exists('mb_internal_encoding')) mb_internal_encoding('UTF-8');
        }

        // dont add if its empty
        if ( ! empty( $defaults ) ) {
            // retrieve directory and url preferences also
            require_once 'CRM/Core/BAO/Preferences.php';
            CRM_Core_BAO_Preferences::retrieveDirectoryAndURLPreferences( $defaults );
        }
    }


    static function getConfigSettings( ) {
        $config =& CRM_Core_Config::singleton( );

        $url = $dir = $siteName = $siteRoot = null;
        if ( $config->userFramework == 'Joomla' ) {
            $url = preg_replace( '|administrator/components/com_civicrm/civicrm/|',
                                 '',
                                 $config->userFrameworkResourceURL );

            // lets use imageUploadDir since we dont mess around with its values
            // in the config object, lets kep it a bit generic since folks
            // might have different values etc
            $dir = preg_replace( '|civicrm/templates_c/.*$|',
                                 '',
                                 $config->templateCompileDir );
            $siteRoot =  preg_replace( '|/media/civicrm/.*$|',
                                       '',
                                       $config->imageUploadDir );
        } else {
            $url = preg_replace( '|sites/[\w\.\-\_]+/modules/civicrm/|',
                                 '',
                                 $config->userFrameworkResourceURL );
            
            // lets use imageUploadDir since we dont mess around with its values
            // in the config object, lets kep it a bit generic since folks
            // might have different values etc
            $dir =  preg_replace( '|/files/civicrm/.*$|',
                                  '/files/',
                                  $config->imageUploadDir );

            $matches = array( );
            if ( preg_match( '|/sites/([\w\.\-\_]+)/|',
                             $config->imageUploadDir,
                             $matches ) ) {
                $siteName = $matches[1];
                if ( $siteName ) {
                    $siteName = "/sites/$siteName/";
                    $siteNamePos = strpos($dir, $siteName);
                    if ( $siteNamePos !== false ) {
                        $siteRoot = substr($dir, 0, $siteNamePos);
                    }
                }
            }
        }


        return array( $url, $dir, $siteName, $siteRoot );
    }

    static function getBestGuessSettings( ) {
        $config =& CRM_Core_Config::singleton( );

        $url = $config->userFrameworkBaseURL;
        $siteName = $siteRoot = null;
        if ( $config->userFramework == 'Joomla' ) {
            $url = preg_replace( '|/administrator|',
                                 '',
                                 $config->userFrameworkBaseURL );
            $siteRoot =  preg_replace( '|/media/civicrm/.*$|',
                                       '',
                                       $config->imageUploadDir );
        }
        $dir = preg_replace( '|civicrm/templates_c/.*$|',
                             '',
                             $config->templateCompileDir );

        if ( $config->userFramework != 'Joomla' ) {
            $matches = array( );
            if ( preg_match( '|/sites/([\w\.\-\_]+)/|',
                             $config->templateCompileDir,
                             $matches ) ) {
                $siteName = $matches[1];
                if ( $siteName ) {
                    $siteName = "/sites/$siteName/";
                    $siteNamePos = strpos($dir, $siteName);
                    if ( $siteNamePos !== false ) {
                        $siteRoot = substr($dir, 0, $siteNamePos);
                    }
                }
            }
        }
        
        return array( $url, $dir, $siteName, $siteRoot );
    }

    static function doSiteMove( $defaultValues = array( ) ) {
        $moveStatus = ts('Beginning site move process...') . '<br />';
        // get the current and guessed values
        list( $oldURL, $oldDir, $oldSiteName, $oldSiteRoot ) = self::getConfigSettings( );
        list( $newURL, $newDir, $newSiteName, $newSiteRoot ) = self::getBestGuessSettings( );
    
        require_once 'CRM/Utils/Request.php';

        // retrieve these values from the argument list 
        $variables = array( 'URL', 'Dir', 'SiteName', 'SiteRoot', 'Val_1', 'Val_2', 'Val_3' );
        $states     = array( 'old', 'new' );
        foreach ( $variables as $varSuffix ) {
            foreach ( $states as $state ) {
                $var = "{$state}{$varSuffix}";
                if ( ! isset( $$var ) ) {
                    if ( isset( $defaultValues[$var] ) ) {
                        $$var = $defaultValues[$var];
                    } else {
                        $$var = null;
                    }
                }
                $$var = CRM_Utils_Request::retrieve( $var,
                                                     'String',
                                                     CRM_Core_DAO::$_nullArray,
                                                     false,
                                                     $$var,
                                                     'REQUEST' );
            }
        }

        $from = $to = array( );
        foreach ( $variables as $varSuffix ) {
            $oldVar = "old{$varSuffix}";
            $newVar = "new{$varSuffix}";
            //skip it if either is empty or both are exactly the same
            if ( $$oldVar &&
                 $$newVar &&
                 $$oldVar != $$newVar ) {
                $from[]  = $$oldVar;
                $to[]    = $$newVar;
            }
        }

        $sql = "
SELECT config_backend
FROM   civicrm_domain
WHERE  id = %1
";
        $params = array( 1 => array( CRM_Core_Config::domainID( ), 'Integer' ) );
        $configBackend = CRM_Core_DAO::singleValueQuery( $sql, $params );
        if ( ! $configBackend ) {
            CRM_Core_Error::fatal( ts('Returning early due to unexpected error - civicrm_domain.config_backend column value is NULL. Try visiting CiviCRM Home page.') );
        }
        $configBackend = unserialize( $configBackend );

        $configBackend = str_replace( $from,
                                      $to  ,
                                      $configBackend );

        $configBackend = serialize( $configBackend );
        $sql = "
UPDATE civicrm_domain
SET    config_backend = %2
WHERE  id = %1
";
        $params[2] = array( $configBackend, 'String' );
        CRM_Core_DAO::executeQuery( $sql, $params );

        // Apply the changes to civicrm_option_values
        $optionGroups = array('url_preferences', 'directory_preferences');
        foreach ($optionGroups as $option) {
            foreach ( $variables as $varSuffix ) {
                $oldVar = "old{$varSuffix}";
                $newVar = "new{$varSuffix}";

                $from = $$oldVar;
                $to   = $$newVar;

                if ($from && $to && $from != $to) {
                    $sql = '
UPDATE civicrm_option_value 
SET    value = REPLACE(value, %1, %2) 
WHERE  option_group_id = ( 
  SELECT id 
  FROM   civicrm_option_group 
  WHERE  name = %3 )
';
                    $params = array( 1 => array ( $from, 'String' ),
                                     2 => array ($to, 'String'),
                                     3 => array($option, 'String') );
                    CRM_Core_DAO::executeQuery( $sql, $params );
                }
            }
        }
        
        $moveStatus .= 
            ts('Directory and Resource URLs have been updated in the moved database to reflect current site location.') .
            '<br />';

        $config =& CRM_Core_Config::singleton( );

        // clear the template_c and upload directory also
        $config->cleanup( 3, true );
        $moveStatus .= ts('Template cache and upload directory have been cleared.') . '<br />';
    
        // clear all caches
        CRM_Core_Config::clearDBCache( );
        $moveStatus .= ts('Database cache tables cleared.') . '<br />';

        $resetSessionTable = CRM_Utils_Request::retrieve( 'resetSessionTable',
                                                          'Boolean',
                                                          CRM_Core_DAO::$_nullArray,
                                                          false,
                                                          false,
                                                          'REQUEST' );
        if ( $config->userFramework == 'Drupal' &&
             $resetSessionTable ) {
            db_query("DELETE FROM {sessions} WHERE 1");
            $moveStatus .= ts('Drupal session table cleared.') . '<br />';
        } else {
            $session =& CRM_Core_Session::singleton( );
            $session->reset( 2 );
            $moveStatus .= ts('Session has been reset.') . '<br />';
        }

        return $moveStatus;

    }
    
    /**
     * takes a componentName and enables it in the config
     * Primarily used during unit testing
     *
     * @param string $componentName name of the component to be enabled, needs to be valid
     *
     * @return boolean - true if valid component name and enabling succeeds, else false
     * @static
     */
    static function enableComponent( $componentName ) {
        $config =& CRM_Core_Config::singleton( );
        if ( in_array( $componentName, $config->enableComponents ) ) {
            // component is already enabled
            return true;
        }
        require_once 'CRM/Core/Component.php';
        $components = CRM_Core_Component::getComponents();

        // return if component does not exist
        if ( ! array_key_exists( $componentName, $components ) ) {
            return false;
        }

        // get config_backend value
        $sql = "
SELECT config_backend
FROM   civicrm_domain
WHERE  id = %1
";
        $params = array( 1 => array( CRM_Core_Config::domainID( ), 'Integer' ) );
        $configBackend = CRM_Core_DAO::singleValueQuery( $sql, $params );

        if ( ! $configBackend ) {
            CRM_Core_Error::fatal( ts('Returning early due to unexpected error - civicrm_domain.config_backend column value is NULL. Try visiting CiviCRM Home page.') );
        }
        $configBackend = unserialize( $configBackend );
        
        $configBackend['enableComponents'][] = $componentName;
        $configBackend['enableComponentIDs'][] = $components[$componentName]->componentID;

        // fix the config object
        $config->enableComponents   =  $configBackend['enableComponents'];
        $config->enableComponentIDs =  $configBackend['enableComponentIDs'];

        // also force reset of component array
        CRM_Core_Component::getEnabledComponents( true );

        // check if component is already there, is so return
        $configBackend = serialize( $configBackend );
        $sql = "
UPDATE civicrm_domain
SET    config_backend = %2
WHERE  id = %1
";
        $params[2] = array( $configBackend, 'String' );
        CRM_Core_DAO::executeQuery( $sql, $params );

        return true;
    }

}
