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


/**
 * Joomla specific stuff goes here
 */
class CRM_Utils_System_Joomla {

    /**
     * sets the title of the page
     *
     * @param string $title title to set
     * @param string $pageTitle
     *
     * @return void
     * @access public
     */
    function setTitle( $title, $pageTitle = null ) {
        if ( ! $pageTitle ) {
            $pageTitle = $title;
        }

        $template = CRM_Core_Smarty::singleton( );
        $template->assign( 'pageTitle', $pageTitle );

		$document=& JFactory::getDocument();
		$document->setTitle($title);

        return;
    }

    /**
     * Append an additional breadcrumb tag to the existing breadcrumb
     *
     * @param string $title
     * @param string $url   
     *
     * @return void
     * @access public
     * @static
     */
    static function appendBreadCrumb( $breadCrumbs ) {
        $template = CRM_Core_Smarty::singleton( );
        $bc = $template->get_template_vars( 'breadcrumb' );

        if ( is_array( $breadCrumbs ) ) {
            foreach ( $breadCrumbs as $crumbs ) {
                if ( stripos($crumbs['url'], 'id%%') ) {
                    $args = array( 'cid', 'mid' );
                    foreach ( $args as $a ) {
                        $val  = CRM_Utils_Request::retrieve( $a, 'Positive', CRM_Core_DAO::$_nullObject,
                                                             false, null, $_GET );
                        if ( $val ) {
                            $crumbs['url'] = str_ireplace( "%%{$a}%%", $val, $crumbs['url'] );
                        }
                    }
                }
                $bc[] = $crumbs;
            }
        }
        $template->assign_by_ref( 'breadcrumb', $bc );
        return;
    }

    /**
     * Reset an additional breadcrumb tag to the existing breadcrumb
     *
     * @param string $bc the new breadcrumb to be appended
     *
     * @return void
     * @access public
     * @static
     */
    static function resetBreadCrumb( ) {
        return;
    }

    /**
     * Append a string to the head of the html file
     *
     * @param string $head the new string to be appended
     *
     * @return void
     * @access public
     * @static
     */
    static function addHTMLHead( $string = null, $includeAll = false ) {
        $document =& JFactory::getDocument( );

        if ( $string ) {
            $document->addCustomTag( $string );
        }

        if ( $includeAll ) {
            require_once 'CRM/Core/Config.php';
            $config = CRM_Core_Config::singleton();

            $document->addStyleSheet( "{$config->resourceBase}css/deprecate.css" );
            $document->addStyleSheet( "{$config->resourceBase}css/civicrm.css" );

            if ( ! $config->userFrameworkFrontend ) {
                $document->addStyleSheet( "{$config->resourceBase}css/joomla.css" );
            } else {
                $document->addStyleSheet( "{$config->resourceBase}css/joomla_frontend.css" );
            }
            if ( isset( $config->customCSSURL ) && ! empty( $config->customCSSURL ) ) {
                $document->addStyleSheet( $config->customCSSURL );
            }

            $document->addStyleSheet( "{$config->resourceBase}css/extras.css" );

            $document->addScript( "{$config->resourceBase}js/Common.js" );
    
            $template = CRM_Core_Smarty::singleton( );

            // CRM-6819 + CRM-7086
            $lang     = substr($config->lcMessages, 0, 2);
            $l10nFile = "{$config->smartyDir}../jquery/jquery-ui-1.8.11/development-bundle/ui/i18n/jquery.ui.datepicker-{$lang}.js";
            $l10nURL  = "{$config->resourceBase}packages/jquery/jquery-ui-1.8.11/development-bundle/ui/i18n/jquery.ui.datepicker-{$lang}.js";
            if (file_exists($l10nFile)) {
                $template->assign('l10nURL', $l10nURL);
            }

            $document->addCustomTag( $template->fetch( 'CRM/common/jquery.tpl' ) );
            $document->addCustomTag( $template->fetch( 'CRM/common/action.tpl' ) );
        }
    }

    /**
     * Generate an internal CiviCRM URL
     *
     * @param $path     string   The path being linked to, such as "civicrm/add"
     * @param $query    string   A query string to append to the link.
     * @param $absolute boolean  Whether to force the output to be an absolute link (beginning with http:).
     *                           Useful for links that will be displayed outside the site, such as in an
     *                           RSS feed.
     * @param $fragment string   A fragment identifier (named anchor) to append to the link.
     * @param $htmlize  boolean  whether to convert to html eqivalant
     * @param $frontend boolean  a gross joomla hack
     *
     * @return string            an HTML string containing a link to the given path.
     * @access public
     *
     */
    function url($path = null, $query = null, $absolute = true,
                 $fragment = null, $htmlize = true,
                 $frontend = false ) {
        $config    = CRM_Core_Config::singleton( );
 		$separator = $htmlize ? '&amp;' : '&';
 		$Itemid    = '';

        require_once 'CRM/Utils/String.php';
        $path = CRM_Utils_String::stripPathChars( $path );

        if ( $config->userFrameworkFrontend ) {
            $script = 'index.php';
            if ( JRequest::getVar("Itemid") ) {
                $Itemid = "{$separator}Itemid=" . JRequest::getVar("Itemid");
            }
        } else {
            $script = 'index2.php';
        }

        if (isset($fragment)) {
            $fragment = '#'. $fragment;
        }

        if ( ! isset( $config->useFrameworkRelativeBase ) ) {
            $base = parse_url( $config->userFrameworkBaseURL );
            $config->useFrameworkRelativeBase = $base['path'];
        }
        $base = $absolute ? $config->userFrameworkBaseURL : $config->useFrameworkRelativeBase;

        if ( !empty ( $query ) ) {
            $url = "{$base}{$script}?option=com_civicrm{$separator}task={$path}{$Itemid}{$separator}{$query}{$fragment}";
        } else {
            $url ="{$base}{$script}?option=com_civicrm{$separator}task={$path}{$Itemid}{$fragment}";
        }

        // gross hack for joomla, we are in the backend and want to send a frontend url
        if ( $frontend &&
             $config->userFramework == 'Joomla' ) {
            // handle both joomla v1.5 and v1.6, CRM-7939
            $url = str_replace( '/administrator/index2.php', '/index.php', $url );
            $url = str_replace( '/administrator/index.php' , '/index.php', $url );

            // CRM-8215
            $url = str_replace( '/administrator/', '/index.php', $url );
        }

        return $url;
    }

    /** 
     * rewrite various system urls to https 
     * 
     * @return void 
     * access public  
     * @static  
     */  
    static function mapConfigToSSL( ) {
        // dont need to do anything, let CMS handle their own switch to SSL
        return;
    }

    /**
     * figure out the post url for the form
     *
     * @param $action the default action if one is pre-specified
     *
     * @return string the url to post the form
     * @access public
     * @static
     */
    function postURL( $action ) {
        if ( ! empty( $action ) ) {
            return $action;
        }

        return self::url( CRM_Utils_Array::value( 'task', $_GET ),
                          null, true, null, false );
    }

    /**
     * Function to set the email address of the user
     *
     * @param object $user handle to the user object
     *
     * @return void
     * @access public
     */
    function setEmail( &$user ) {
        global $database;
        $query = "SELECT email FROM #__users WHERE id='$user->id'";
        $database->setQuery( $query );
        $user->email = $database->loadResult();
    }

    /**
     * Authenticate the user against the joomla db
     *
     * @param string $name     the user name
     * @param string $password the password for the above user name
     *
     * @return mixed false if no auth
     *               array( contactID, ufID, unique string ) if success
     * @access public
     * @static
     */
    static function authenticate( $name, $password ) {
        require_once 'DB.php';

        $config = CRM_Core_Config::singleton( );
        
        $dbJoomla = DB::connect( $config->userFrameworkDSN );
        if ( DB::isError( $dbJoomla ) ) {
            CRM_Core_Error::fatal( "Cannot connect to joomla db via $config->userFrameworkDSN, " . $dbJoomla->getMessage( ) ); 
        }                                                      

        $strtolower = function_exists('mb_strtolower') ? 'mb_strtolower' : 'strtolower';
        $name      = $dbJoomla->escapeSimple( $strtolower( $name ) );
        $sql = 'SELECT u.* FROM ' . $config->userFrameworkUsersTableName .
            " u WHERE LOWER(u.username) = '$name' AND u.block = 0";
        $query = $dbJoomla->query( $sql );

        $user = null;
        require_once 'CRM/Core/BAO/UFMatch.php';
        if ( $row = $query->fetchRow( DB_FETCHMODE_ASSOC ) ) {
            // now check password
            if ( strpos( $row['password'], ':' ) === false ) {
                if ( $row['password'] != md5( $password ) ) {
                    return false;
                }
            } else {
                list( $hash, $salt ) = explode( ':', $row['password'] );
                $cryptpass           = md5( $password . $salt );
                if ( $hash != $cryptpass ) {
                    return false;
                }
            }
            
            CRM_Core_BAO_UFMatch::synchronizeUFMatch( $user, $row['id'], $row['email'], 'Joomla' );
            $contactID = CRM_Core_BAO_UFMatch::getContactId( $row['id'] );
            if ( ! $contactID ) {
                return false;
            }
            return array( $contactID, $row['id'], mt_rand() );
        }
        return false;
    }

    /**    
     * Set a message in the UF to display to a user  
     *    
     * @param string $message  the message to set  
     *    
     * @access public    
     * @static    
     */    
    static function setMessage( $message ) { 
        return;
    }

    static function permissionDenied( ) {
        CRM_Core_Error::fatal( ts( 'You do not have permission to access this page' ) );
    }

    static function logout( ) {
        session_destroy();
        header("Location:index.php");
    }

    /**
     * Get the locale set in the hosting CMS
     * @return string  the used locale or null for none
     */
    static function getUFLocale()
    {
        if ( defined('_JEXEC') ) {
            $conf	=& JFactory::getConfig();
            $locale	= $conf->getValue('config.language');
            return str_replace('-', '_', $locale);
        }
        return null;
    }

    /* 
     * load joomla bootstrap
     *
     * @param $name string  optional username for login
     * @param $pass string  optional password for login
     */
    static function loadBootStrap($user = null, $pass = null, $uid = null )
    {
        return true;
    }
    
    /**
     * check is user logged in.
     *
     * @return boolean true/false.
     */
    public static function isUserLoggedIn( ) {
        $user = JFactory::getUser();
        return ( $user->guest ) ? false : true; 
    }
    
    /**
     * Get currently logged in user uf id.
     *
     * @return int logged in user uf id.
     */
    public static function getLoggedInUfID( ) {
        $user = JFactory::getUser( );
        return ( $user->guest ) ? null : $user->id;
    }
    
}


