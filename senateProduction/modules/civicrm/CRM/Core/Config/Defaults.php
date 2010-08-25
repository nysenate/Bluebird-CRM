<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
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
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

/**
 * This class is a temporary place to store default setting values
 * before they will be distributed in proper places (component configurations
 * and core configuration). The name is intentionally stupid so that it will be fixed
 * ASAP.
 * 
 */
class CRM_Core_Config_Defaults
{
    function setCoreVariables( ) {
        global $civicrm_root;

        // set of base directories relying on $civicrm_root
        $this->smartyDir  =
            $civicrm_root . DIRECTORY_SEPARATOR .
            'packages'    . DIRECTORY_SEPARATOR .
            'Smarty'      . DIRECTORY_SEPARATOR ;

        $this->pluginsDir =
            $civicrm_root . DIRECTORY_SEPARATOR .
            'CRM'         . DIRECTORY_SEPARATOR . 
            'Core'        . DIRECTORY_SEPARATOR .
            'Smarty'      . DIRECTORY_SEPARATOR .
            'plugins'     . DIRECTORY_SEPARATOR ;

        $this->templateDir =
            $civicrm_root . DIRECTORY_SEPARATOR .
            'templates'   . DIRECTORY_SEPARATOR ;
            
        $this->importDataSourceDir =
            $civicrm_root . DIRECTORY_SEPARATOR .
            'CRM'         . DIRECTORY_SEPARATOR .
            'Import'      . DIRECTORY_SEPARATOR .
            'DataSource'  . DIRECTORY_SEPARATOR ;

        $this->gettextResourceDir =
            $civicrm_root . DIRECTORY_SEPARATOR .
            'l10n'        . DIRECTORY_SEPARATOR ;

        // This should be moved to database config.
        $this->sunlight = defined( 'CIVICRM_SUNLIGHT' ) ? true : false;

        // show tree widget
        $this->groupTree = defined( 'CIVICRM_GROUPTREE' ) ? true : false;

        // in hrd mode?
        $this->civiHRD   = defined( 'CIVICRM_HRD' ) ? true : false;
        
        // add UI revamp pages
        //$this->revampPages = array( 'CRM/Admin/Form/Setting/Url.tpl', 'CRM/Admin/Form/Preferences/Address.tpl' );
        $this->revampPages = array( );
        
        $this->profileDoubleOptIn = false;
        // enable profile double Opt-In if Civimail enabled
        if ( in_array( 'CiviMail', $this->enableComponents ) ) {
            // set defined value for Profile double Opt-In from civicrm settings file else true 
            $this->profileDoubleOptIn = defined( 'CIVICRM_PROFILE_DOUBLE_OPTIN' ) ? (bool) CIVICRM_PROFILE_DOUBLE_OPTIN : true;
        }

        //email notifications to activity Assignees
        $this->activityAssigneeNotification = defined( 'CIVICRM_ACTIVITY_ASSIGNEE_MAIL' ) ? (bool) CIVICRM_ACTIVITY_ASSIGNEE_MAIL : true;

        // IDS enablement
        $this->useIDS = defined( 'CIVICRM_IDS_ENABLE' ) ? (bool) CIVICRM_IDS_ENABLE : true;
        
        // 
        $size = trim( ini_get( 'upload_max_filesize' ) );
        if ( $size ) {
            $last = strtolower($size{strlen($size)-1});
            switch($last) {
                // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $size *= 1024;
            case 'm':
                $size *= 1024;
            case 'k':
                $size *= 1024;
            }
            $this->maxImportFileSize = $size;
        }
    }


    /**
     * Function to set the default values
     *
     * @param array   $defaults  associated array of form elements
     * @param boolena $formMode  this funtion is called to set default
     *                           values in an empty db, also called when setting component using GUI
     *                           this variable is set true for GUI
     *                           mode (eg: Global setting >> Components)    
     *
     * @access public
     */
    public function setValues(&$defaults, $formMode = false) 
    {
        $config = CRM_Core_Config::singleton( );

        $baseURL = $config->userFrameworkBaseURL;

        if ( $config->templateCompileDir ) {
            $path = dirname( $config->templateCompileDir );
            
            //this fix is to avoid creation of upload dirs inside templates_c directory
            $checkPath = explode( DIRECTORY_SEPARATOR, $path );
            $cnt = count($checkPath) - 1;
            if ( $checkPath[$cnt] == 'templates_c' ) {
                unset( $checkPath[$cnt] );
                $path = implode( DIRECTORY_SEPARATOR, $checkPath );
            }

            $path = CRM_Utils_File::addTrailingSlash( $path );
        }

        //set defaults if not set in db
        if ( ! isset( $defaults['userFrameworkResourceURL'] ) ) {
            $testIMG = "i/tracker.gif";
            if ( $config->userFramework == 'Joomla' ) {
                if ( CRM_Utils_System::checkURL( "{$baseURL}components/com_civicrm/civicrm/{$testIMG}" ) ) {
                    $defaults['userFrameworkResourceURL'] = $baseURL . "components/com_civicrm/civicrm/";
                }
            } else if ( $config->userFramework == 'Standalone' ) {
                // potentially sane default for standalone;
                // could probably be smarter about this, but this
                // should work in many cases
                $defaults['userFrameworkResourceURL'] = str_replace( 'standalone/', '', $baseURL );
            } else {
                // Drupal setting
                // check and see if we are installed in sites/all (for D5 and above)
                // we dont use checkURL since drupal generates an error page and throws
                // the system for a loop on lobo's macosx box
                // or in modules
                global $civicrm_root;
                $civicrmDirName = trim(basename($civicrm_root));
                $defaults['userFrameworkResourceURL'] = $baseURL . "sites/all/modules/$civicrmDirName/";

                if ( strpos( $civicrm_root,
                             DIRECTORY_SEPARATOR . 'sites' .
                             DIRECTORY_SEPARATOR . 'all'   .
                             DIRECTORY_SEPARATOR . 'modules' ) === false ) {
                    $startPos = strpos( $civicrm_root,
                                        DIRECTORY_SEPARATOR . 'sites' . DIRECTORY_SEPARATOR );
                    $endPos   = strpos( $civicrm_root,
                                        DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR );
                    if ( $startPos && $endPos ) {
                        // if component is in sites/SITENAME/modules
                        $siteName = substr( $civicrm_root,
                                            $startPos + 7,
                                            $endPos - $startPos - 7 );
                        $defaults['userFrameworkResourceURL'] = $baseURL . "sites/$siteName/modules/$civicrmDirName/";
                        if ( ! isset( $defaults['imageUploadURL'] ) ) {
                            $defaults['imageUploadURL'] = $baseURL . "sites/$siteName/files/civicrm/persist/contribute/";
                        }
                    }
                }
            }
        }

        if ( ! isset( $defaults['imageUploadURL'] ) ) {
            if ( $config->userFramework == 'Joomla' ) {
                // gross hack
                // we need to remove the administrator/ from the end
                $tempURL = str_replace( "/administrator/", "/", $baseURL );
                $defaults['imageUploadURL'] = $tempURL . "media/civicrm/persist/contribute/";
            } else if ( $config->userFramework == 'Standalone' ) {
                //for standalone no need of sites/defaults directory
                $defaults['imageUploadURL'] = $baseURL . "files/civicrm/persist/contribute/";
            } else {
                $defaults['imageUploadURL'] = $baseURL . "sites/default/files/civicrm/persist/contribute/";
            }
        }

        if ( ! isset( $defaults['imageUploadDir'] ) && is_dir($config->templateCompileDir) ) {
            $imgDir = $path . "persist/contribute/";

            CRM_Utils_File::createDir( $imgDir );
            $defaults['imageUploadDir'] = $imgDir;
        }

        if ( ! isset( $defaults['uploadDir'] ) && is_dir($config->templateCompileDir) ) {
            $uploadDir = $path . "upload/";
            
            CRM_Utils_File::createDir( $uploadDir );
            $defaults['uploadDir'] = $uploadDir;
        }

        if ( ! isset( $defaults['customFileUploadDir'] ) && is_dir($config->templateCompileDir) ) {
            $customDir = $path . "custom/";
            
            CRM_Utils_File::createDir( $customDir );
            $defaults['customFileUploadDir'] = $customDir;
        }

        /* FIXME: hack to bypass the step for generating defaults for components, 
                  while running upgrade, to avoid any serious non-recoverable error 
                  which might hinder the upgrade process. */

        $args = array( );
        if ( isset( $_GET[$config->userFrameworkURLVar] ) ) {
            $args = explode( '/', $_GET[$config->userFrameworkURLVar] );
        }
    
        foreach( $defaults['enableComponents'] as $key => $name ) {
            $comp = $config->componentRegistry->get( $name );
            if ( $comp ) {
                $co = $comp->getConfigObject();
                $co->setDefaults( $defaults );
            }
        }
    }
}

