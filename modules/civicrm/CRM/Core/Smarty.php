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

require_once 'CRM/Utils/Recent.php';

/**
 * Fix for bug CRM-392. Not sure if this is the best fix or it will impact
 * other similar PEAR packages. doubt it
 */
if ( ! class_exists( 'Smarty' ) ) {
    require_once 'Smarty/Smarty.class.php';
}


/**
 *
 */
class CRM_Core_Smarty extends Smarty {

    const
        PRINT_PAGE    = 1,
        PRINT_SNIPPET = 2,
        PRINT_PDF     = 3,
        PRINT_NOFORM  = 4;

    /**
     * We only need one instance of this object. So we use the singleton
     * pattern and cache the instance in this variable
     *
     * @var object
     * @static
     */
    static private $_singleton = null;

    /**
     * class constructor
     *
     * @return CRM_Core_Smarty
     * @access private
     */
    function __construct( ) {
        parent::__construct( );

        $config = CRM_Core_Config::singleton( );

        if ( isset( $config->customTemplateDir ) && $config->customTemplateDir ) {
            $this->template_dir = array_merge( array( $config->customTemplateDir ),
                                               $config->templateDir );
        } else {
            $this->template_dir = $config->templateDir;
        }
        $this->compile_dir  = $config->templateCompileDir;
        
        //Check for safe mode CRM-2207
        if ( ini_get('safe_mode') ) {
            $this->use_sub_dirs = false;
        } else {
            $this->use_sub_dirs = true;
        }

        $customPluginsDir = null;
        if ( isset( $config->customPHPPathDir ) ) {
            $customPluginsDir = 
                $config->customPHPPathDir . DIRECTORY_SEPARATOR .
                'CRM'         . DIRECTORY_SEPARATOR . 
                'Core'        . DIRECTORY_SEPARATOR .
                'Smarty'      . DIRECTORY_SEPARATOR .
                'plugins'     . DIRECTORY_SEPARATOR ;
            if ( ! file_exists( $customPluginsDir ) ) {
                $customPluginsDir = null;
            }
        }

        if ( $customPluginsDir ) {
            $this->plugins_dir  = array ( $customPluginsDir, $config->smartyDir . 'plugins', $config->pluginsDir );
        } else {
            $this->plugins_dir  = array ( $config->smartyDir . 'plugins', $config->pluginsDir );
        }

        // add the session and the config here
        $session = CRM_Core_Session::singleton();

        $this->assign_by_ref( 'config'        , $config  );
        $this->assign_by_ref( 'session'       , $session );
        
        // check default editor and assign to template, store it in session to reduce db calls
        $defaultWysiwygEditor = $session->get( 'defaultWysiwygEditor');
        if ( !$defaultWysiwygEditor && !CRM_Core_Config::isUpgradeMode() ) {
            require_once 'CRM/Core/BAO/Preferences.php';
            $defaultWysiwygEditor = CRM_Core_BAO_Preferences::value( 'editor_id' );            
            $session->set( 'defaultWysiwygEditor', $defaultWysiwygEditor );
        }
        
        $this->assign( 'defaultWysiwygEditor', $defaultWysiwygEditor );
 
        global $tsLocale;
        $this->assign('tsLocale',   $tsLocale);
        
        // CRM-7163 hack: we don’t display langSwitch on upgrades anyway
        if ( CRM_Utils_Array::value( $config->userFrameworkURLVar, $_REQUEST ) != 'civicrm/upgrade' ) {
            $this->assign('langSwitch', CRM_Core_I18n::languages(true));
        }
        
        //check if logged in use has access CiviCRM permission and build menu
        require_once 'CRM/Core/Permission.php';
        $buildNavigation = CRM_Core_Permission::check( 'access CiviCRM' );
        $this->assign('buildNavigation', $buildNavigation );
        
        if ( !CRM_Core_Config::isUpgradeMode() && $buildNavigation ) {
            require_once 'CRM/Core/BAO/Navigation.php';
            $contactID = $session->get('userID');
            if ( $contactID ) {
                $navigation =& CRM_Core_BAO_Navigation::createNavigation( $contactID );
                $this->assign('navigation', $navigation );
            }
        }

        $this->register_function ( 'crmURL' , array( 'CRM_Utils_System', 'crmURL' ) );

    }

    /**
     * Static instance provider.
     *
     * Method providing static instance of SmartTemplate, as
     * in Singleton pattern.
     */
    static function &singleton( ) {
        if ( ! isset( self::$_singleton ) ) {
            $config = CRM_Core_Config::singleton( );
            self::$_singleton = new CRM_Core_Smarty( $config->templateDir, $config->templateCompileDir );
        }
        return self::$_singleton;
    }

    /**
     * executes & returns or displays the template results
     *
     * @param string $resource_name
     * @param string $cache_id
     * @param string $compile_id
     * @param boolean $display
     */
    function fetch($resource_name, $cache_id = null, $compile_id = null, $display = false)
    {
        require_once 'CRM/Core/Menu.php';
        return parent::fetch( $resource_name, $cache_id, $compile_id, $display );
    }

    function appendValue( $name, $value ) {
        $currentValue = $this->get_template_vars( $name );
        if ( ! $currentValue ) {
            $this->assign( $name, $value );
        } else {
            if ( strpos( $currentValue, $value ) === false ) {
                $this->assign( $name, $currentValue . $value );
            }
        }
    }

    function clearTemplateVars( ) {
        foreach ( array_keys( $this->_tpl_vars ) as $key ) {
            if ( $key == 'config' || $key == 'session' ) {
                continue;
            }
            unset( $this->_tpl_vars[$key] );
        }
    }

}


