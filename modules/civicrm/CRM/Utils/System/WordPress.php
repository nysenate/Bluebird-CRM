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
 * WordPress specific stuff goes here
 */
class CRM_Utils_System_WordPress {

    /**
     * sets the title of the page
     *
     * @param string $title
     * @paqram string $pageTitle
     *
     * @return void
     * @access public
     */
    function setTitle( $title, $pageTitle = null ) {
        if ( !$pageTitle ) {
            $pageTitle = $title;
        }
        if ( civicrm_wp_in_civicrm( ) ) {
            global $civicrm_wp_title;
            $civicrm_wp_title = $pageTitle;
        }
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
        $breadCrumb = drupal_get_breadcrumb( );

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
                $breadCrumb[]  = "<a href=\"{$crumbs['url']}\">{$crumbs['title']}</a>";
            }
        }
        drupal_set_breadcrumb( $breadCrumb );
    }

    /**
     * Reset an additional breadcrumb tag to the existing breadcrumb
     *
     * @return void
     * @access public
     * @static
     */
    static function resetBreadCrumb( ) {
        $bc = array( );
        drupal_set_breadcrumb( $bc );
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
    static function addHTMLHead( $head ) {
      drupal_set_html_head( $head );
    }

    /** 
     * rewrite various system urls to https 
     *  
     * @param null 
     *
     * @return void 
     * @access public  
     * @static  
     */  
    static function mapConfigToSSL( ) {
        global $base_url;
        $base_url = str_replace( 'http://', 'https://', $base_url );
    }

    /**
     * figure out the post url for the form
     *
     * @param mix $action the default action if one is pre-specified
     *
     * @return string the url to post the form
     * @access public
     * @static
     */
    static function postURL( $action ) {
        if ( ! empty( $action ) ) {
            return $action;
        }

        return self::url( $_GET['q'], null, true, null, false );
    }

    /**
     * Generate an internal CiviCRM URL (copied from DRUPAL/includes/common.inc#url)
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
    function url($path = null, $query = null, $absolute = false,
                 $fragment = null, $htmlize = true,
                 $frontend = false ) {
        $config = CRM_Core_Config::singleton( );
        $script =  'index.php';

        require_once 'CRM/Utils/String.php';
        $path = CRM_Utils_String::stripPathChars( $path );

        if (isset($fragment)) {
            $fragment = '#'. $fragment;
        }

        if ( ! isset( $config->useFrameworkRelativeBase ) ) {
            $base = parse_url( $config->userFrameworkBaseURL );
            $config->useFrameworkRelativeBase = $base['path'];
        }
        $base = $absolute ? $config->userFrameworkBaseURL : $config->useFrameworkRelativeBase;
        $base = 'http://wp/wp-admin/admin.php';
        $script = '';
        $separator = $htmlize ? '&amp;' : '&';

        if (! $config->cleanURL ) {
            if ( isset( $path ) ) {
                if ( isset( $query ) ) {
                    return $base . $script .'?page=CiviCRM&q=' . $path . $separator . $query . $fragment;
                } else {
                    return $base . $script .'?page=CiviCRM&q=' . $path . $fragment;
                }
            } else {
                if ( isset( $query ) ) {
                    return $base . $script .'?'. $query . $fragment;
                } else {
                    return $base . $fragment;
                }
            }
        } else {
            if ( isset( $path ) ) {
                if ( isset( $query ) ) {
                    return $base . $path .'?'. $query . $fragment;
                } else {
                    return $base . $path . $fragment;
                }
            } else {
                if ( isset( $query ) ) {
                    return $base . $script .'?'. $query . $fragment;
                } else {
                    return $base . $fragment;
                }
            }
        }
    }

    /**
     * Authenticate the user against the drupal db
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
        
        $dbDrupal = DB::connect( $config->userFrameworkDSN );
        if ( DB::isError( $dbDrupal ) ) {
            CRM_Core_Error::fatal( "Cannot connect to drupal db via $config->userFrameworkDSN, " . $dbDrupal->getMessage( ) ); 
        }                                                      

        $strtolower = function_exists('mb_strtolower') ? 'mb_strtolower' : 'strtolower';
        $password  = md5( $password );
        $name      = $dbDrupal->escapeSimple( $strtolower( $name ) );
        $sql = 'SELECT u.* FROM ' . $config->userFrameworkUsersTableName .
            " u WHERE LOWER(u.name) = '$name' AND u.pass = '$password' AND u.status = 1";
        $query = $dbDrupal->query( $sql );

        $user = null;
        // need to change this to make sure we matched only one row
        require_once 'CRM/Core/BAO/UFMatch.php';
        while ( $row = $query->fetchRow( DB_FETCHMODE_ASSOC ) ) { 
            CRM_Core_BAO_UFMatch::synchronizeUFMatch( $user, $row['uid'], $row['mail'], 'Drupal' );
            $contactID = CRM_Core_BAO_UFMatch::getContactId( $row['uid'] );
            if ( ! $contactID ) {
                return false;
            }
            return array( $contactID, $row['uid'], mt_rand() );
        }
        return false;
    }

    /**   
     * Set a message in the UF to display to a user 
     *   
     * @param string $message the message to set 
     *   
     * @access public   
     * @static   
     */   
    static function setMessage( $message ) {
    }

    static function permissionDenied( ) {
    }

    static function logout( ) {
    }

    static function updateCategories( ) {
    }

    /**
     * Get the locale set in the hosting CMS
     * @return string  with the locale or null for none
     */
    static function getUFLocale()
    {
        return null;
    }

    /**
     * load drupal bootstrap
     *
     * @param $name string  optional username for login
     * @param $pass string  optional password for login
     */
    static function loadBootStrap($name = null, $pass = null)
    {
    }
    
    static function cmsRootPath( ) 
    {
        $cmsRoot  = $valid = null;
        $pathVars = explode( '/', str_replace( '\\', '/', $_SERVER['SCRIPT_FILENAME'] ) );
        
        //might be windows installation.
        $firstVar = array_shift( $pathVars );
        if ( $firstVar ) $cmsRoot = $firstVar;
        
        //start w/ csm dir search.
        foreach ( $pathVars as $var ) {
            $cmsRoot .= "/$var";
            $cmsIncludePath = "$cmsRoot/wp-includes";
            //stop as we found bootstrap.
            if ( @opendir( $cmsIncludePath ) && 
                 file_exists( "$cmsIncludePath/version.php" ) ) { 
                $valid = true;
                break;
            }
        }
        
        return ( $valid ) ? $cmsRoot : null; 
    }
    
    /**
     * check is user logged in.
     *
     * @return boolean true/false.
     */
    public static function isUserLoggedIn( ) {
        $isloggedIn = false;
        if ( function_exists( 'user_is_logged_in' ) ) {
            $isloggedIn = user_is_logged_in( );
        }
        
        return $isloggedIn;
    }
    
    /**
     * Get currently logged in user uf id.
     *
     * @return int $userID logged in user uf id.
     */
    public static function getLoggedInUfID( ) {
        $ufID = null;
        if ( function_exists( 'user_is_logged_in' ) && 
             user_is_logged_in( ) && 
             function_exists( 'user_uid_optional_to_arg' ) ) {
            $ufID = user_uid_optional_to_arg( array( ) );
        }
        
        return $ufID;
    }
    
}
