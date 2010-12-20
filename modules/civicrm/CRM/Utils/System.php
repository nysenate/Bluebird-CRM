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
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

/**
 * System wide utilities.
 *
 */
class CRM_Utils_System {

    static $_callbacks = null;

    /**
     * Compose a new url string from the current url string
     * Used by all the framework components, specifically,
     * pager, sort and qfc
     *
     * @param string $urlVar the url variable being considered (i.e. crmPageID, crmSortID etc)
     *
     * @return string the url fragment
     * @access public
     */
    static function makeURL( $urlVar, $includeReset = false, $includeForce = true ) {
        $config   = CRM_Core_Config::singleton( );

        if ( ! isset( $_GET[$config->userFrameworkURLVar] ) ) {
            return '';
        }

        return self::url( $_GET[$config->userFrameworkURLVar],
                          CRM_Utils_System::getLinksUrl( $urlVar, $includeReset, $includeForce ) );
    }

    /**
     * get the query string and clean it up. Strip some variables that should not
     * be propagated, specically variable like 'reset'. Also strip any side-affect
     * actions (i.e. export)
     *
     * This function is copied mostly verbatim from Pager.php (_getLinksUrl)
     *
     * @param string  $urlVar       the url variable being considered (i.e. crmPageID, crmSortID etc)
     * @param boolean $includeReset should we include the reset var (generally this variable should be skipped)
     * @return string
     * @access public
     */
    static function getLinksUrl( $urlVar, $includeReset = false, $includeForce = true ) {
        // Sort out query string to prevent messy urls
        $querystring = array();
        $qs          = array();
        $arrays      = array();

        if ( ! empty( $_SERVER['QUERY_STRING'] ) ) {
            $qs = explode('&', str_replace( '&amp;', '&', $_SERVER['QUERY_STRING'] ) );
            for ($i = 0, $cnt = count($qs); $i < $cnt; $i++) {
                if ( strstr( $qs[$i], '=' ) !== false ) { // check first if exist a pair
                    list($name, $value) = explode( '=', $qs[$i] );
                    if ( $name != $urlVar ) {
                        $name = rawurldecode($name);
                        //check for arrays in parameters: site.php?foo[]=1&foo[]=2&foo[]=3
                        if ((strpos($name, '[') !== false) &&
                            (strpos($name, ']') !== false)
                            ) {
                            $arrays[] = $qs[$i];
                        } else {
                            $qs[$name] = $value;
                        }
                    }
                } else {
                    $qs[$qs[$i]] = '';
                }
                unset( $qs[$i] );
            }
        }

        if ($includeForce ) {
            $qs['force'] = 1;
        }
        foreach ($qs as $name => $value) {
            if ( $name == 'snippet' ) {
                continue;
            }

            if ( $name != 'reset' || $includeReset ) {
                $querystring[] = $name . '=' . $value;
            }
        }

        $querystring = array_merge($querystring, array_unique($arrays));
        $querystring = array_map('htmlentities', $querystring);

        return implode('&amp;', $querystring) . (! empty($querystring) ? '&amp;' : '') . $urlVar .'=';
    }

    /**
     * if we are using a theming system, invoke theme, else just print the
     * content
     *
     * @param string  $type    name of theme object/file
     * @param string  $content the content that will be themed
     * @param array   $args    the args for the themeing function if any
     * @param boolean $print   are we displaying to the screen or bypassing theming?
     * @param boolean $ret     should we echo or return output
     * @param boolean $maintenance  for maintenance mode
     * 
     * @return void           prints content on stdout
     * @access public
     */
    function theme( $type, &$content, $args = null, $print = false, $ret = false, $maintenance = false ) {
        if ( function_exists( 'theme' ) && ! $print ) {
            if ( $maintenance ) {
                drupal_set_breadcrumb( '' );
                drupal_maintenance_theme();
            }
            $out = theme( $type, $content, $args );
        } else {
            $out = $content;
        }
        
        if ( $ret ) {
            return $out;
        } else {
            print $out;
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
     *
     * @return string            an HTML string containing a link to the given path.
     * @access public
     *
     */
    function url($path = null, $query = null, $absolute = false,
                 $fragment = null, $htmlize = true, $frontend = false ) {
        // we have a valid query and it has not yet been transformed
        if ( $htmlize && ! empty( $query ) && strpos( $query, '&amp;' ) === false ) {
            $query = htmlentities( $query );
        }

        $config   = CRM_Core_Config::singleton( );
        require_once( str_replace( '_', DIRECTORY_SEPARATOR, $config->userFrameworkClass ) . '.php' );
        return eval( 'return ' .
                     $config->userFrameworkClass .
                     '::url( $path, $query, $absolute, $fragment, $htmlize, $frontend );' );

    }

    function href( $text, $path = null, $query = null, $absolute = true,
                      $fragment = null, $htmlize = true, $frontend = false ) {
        $url = self::url( $path, $query, $absolute, $fragment, $htmlize, $frontend );
        return "<a href=\"$url\">$text</a>";
    }

    function permissionDenied( ) {
        $config   = CRM_Core_Config::singleton( );
        require_once( str_replace( '_', DIRECTORY_SEPARATOR, $config->userFrameworkClass ) . '.php' );
        return eval( "return {$config->userFrameworkClass}::permissionDenied( );" );
    }

    static function logout( ) {
        $config   = CRM_Core_Config::singleton( );
        require_once( str_replace( '_', DIRECTORY_SEPARATOR, $config->userFrameworkClass ) . '.php' );
        return eval( "return {$config->userFrameworkClass}::logout( );" );
    }

    // this is a very drupal specific function for now
    static function updateCategories( ) {
        $config = CRM_Core_Config::singleton( );
        if ( $config->userFramework == 'Drupal' ) {
            require_once 'CRM/Utils/System/Drupal.php';
            CRM_Utils_System_Drupal::updateCategories( );
        }
    }

    /**
     * What menu path are we currently on. Called for the primary tpl
     *
     * @return string the current menu path
     * @access public
     */
    static function currentPath( ) {
        $config = CRM_Core_Config::singleton( );
        return trim( CRM_Utils_Array::value($config->userFrameworkURLVar,$_GET), '/' );
    }

    /**
     * this function is called from a template to compose a url
     *
     * @param array $params list of parameters
     * 
     * @return string url
     * @access public
     */
    function crmURL( $params ) {
        $p = CRM_Utils_Array::value( 'p', $params );
        if ( ! isset( $p ) ) {
            $p = self::currentPath( );
        }

        return self::url( $p,
                          CRM_Utils_Array::value( 'q' , $params        ),
                          CRM_Utils_Array::value( 'a' , $params, false ),
                          CRM_Utils_Array::value( 'f' , $params        ),
                          CRM_Utils_Array::value( 'h' , $params, true  ),
                          CRM_Utils_Array::value( 'fe', $params, false ) );
    }

    /**
     * sets the title of the page
     *
     * @param string $title
     * @param string $pageTitle
     *
     * @return void
     * @access public
     */
    function setTitle( $title, $pageTitle = null ) {
        $config   = CRM_Core_Config::singleton( );
        require_once( str_replace( '_', DIRECTORY_SEPARATOR, $config->userFrameworkClass ) . '.php' );
        return eval( $config->userFrameworkClass . '::setTitle( $title, $pageTitle );' );
    }

    /**
     * figures and sets the userContext. Uses the referer if valid
     * else uses the default
     *
     * @param array  $names   refererer should match any str in this array
     * @param string $default the default userContext if no match found
     *
     * @return void
     * @access public
     */
    static function setUserContext( $names, $default = null ) {
        $url = $default;

        $session = CRM_Core_Session::singleton();
        $referer = CRM_Utils_Array::value( 'HTTP_REFERER', $_SERVER );

        if ( $referer && ! empty( $names ) ) {
            foreach ( $names as $name ) {
                if ( strstr( $referer, $name ) ) {
                    $url = $referer;
                    break;
                }
            }
        }

        if ( $url ) {
            $session->pushUserContext( $url );
        }
    }


    /**
     * gets a class name for an object
     *
     * @param  object $object      - object whose class name is needed
     * @return string $className   - class name
     *
     * @access public
     * @static
     */
    static function getClassName($object)
    {
        return get_class($object);
    }

    /**
     * redirect to another url
     *
     * @param string $url the url to goto
     *
     * @return void
     * @access public
     * @static
     */
    static function redirect( $url = null ) {
        if ( ! $url ) {
            $url = self::url( 'civicrm/dashboard', 'reset=1' );
        }

        // replace the &amp; characters with &
        // this is kinda hackish but not sure how to do it right
        $url = str_replace( '&amp;', '&', $url );
        header( 'Location: ' . $url );
        self::civiExit( );
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
        $config   = CRM_Core_Config::singleton( );
        require_once( str_replace( '_', DIRECTORY_SEPARATOR, $config->userFrameworkClass ) . '.php' );
        return eval( 'return ' . $config->userFrameworkClass . '::appendBreadCrumb( $breadCrumbs );' );
    }

    /**
     * Reset an additional breadcrumb tag to the existing breadcrumb
     *
     * @return void
     * @access public
     * @static
     */
    static function resetBreadCrumb( ) {
        $config   = CRM_Core_Config::singleton( );
        require_once( str_replace( '_', DIRECTORY_SEPARATOR, $config->userFrameworkClass ) . '.php' );
        return eval( 'return ' . $config->userFrameworkClass . '::resetBreadCrumb( );' );
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
    static function addHTMLHead( $bc ) {
        $config   = CRM_Core_Config::singleton( );
        require_once( str_replace( '_', DIRECTORY_SEPARATOR, $config->userFrameworkClass ) . '.php' );
        return eval( 'return ' . $config->userFrameworkClass . '::addHTMLHead( $bc );' );
    }

    /**
     * figure out the post url for the form
     *
     * @param the default action if one is pre-specified
     *
     * @return string the url to post the form
     * @access public
     * @static
     */
    static function postURL( $action ) {
        $config   = CRM_Core_Config::singleton( );
        require_once( str_replace( '_', DIRECTORY_SEPARATOR, $config->userFrameworkClass ) . '.php' );
        return eval( 'return ' . $config->userFrameworkClass . '::postURL( $action  ); ' );
    }

    /**
     * rewrite various system urls to https
     *
     * @return void
     * access public 
     * @static 
     */ 
    static function mapConfigToSSL( ) {
        $config   = CRM_Core_Config::singleton( ); 
        $config->userFrameworkResourceURL = str_replace( 'http://', 'https://', 
                                                         $config->userFrameworkResourceURL );
        $config->resourceBase = $config->userFrameworkResourceURL;
        require_once( str_replace( '_', DIRECTORY_SEPARATOR, $config->userFrameworkClass ) . '.php' );
        return eval( 'return ' . $config->userFrameworkClass . '::mapConfigToSSL( ); ' );
    }

    /**
     * Get the base URL from the system
     *
     * @param
     *
     * @return string
     * @access public
     * @static
     */
    static function baseURL() {
        $config = CRM_Core_Config::singleton( );
        return $config->userFrameworkBaseURL;
    }

    static function authenticateAbort( $message, $abort ) {
        if ( $abort ) {
            echo $message;
            self::civiExit( 0 );
        } else {
            return false;
        }
    }

    static function authenticateKey( $abort = true ) {
        // also make sure the key is sent and is valid
        $key = trim( CRM_Utils_Array::value( 'key', $_REQUEST ) );

        $docAdd = "More info at:" . CRM_Utils_System::docURL2( "Command-line Script Configuration", true ); 

        if ( ! $key ) {
            return self::authenticateAbort( "ERROR: You need to send a valid key to execute this file. " . $docAdd . "\n",
                                            $abort );
        }

        $siteKey = defined( 'CIVICRM_SITE_KEY' ) ? CIVICRM_SITE_KEY : null;
        
        if ( ! $siteKey ||
             empty( $siteKey ) ) {
            return self::authenticateAbort( "ERROR: You need to set a valid site key in civicrm.settings.php. " . $docAdd . "\n",
                                            $abort );
        }

        if ( strlen( $siteKey ) < 8 ) {
            return self::authenticateAbort( "ERROR: Site key needs to be greater than 7 characters in civicrm.settings.php. " . $docAdd . "\n",
                                            $abort );
        }

        if ( $key !== $siteKey ) {
            return self::authenticateAbort( "ERROR: Invalid key value sent. " . $docAdd . "\n",
                                            $abort );
        }

        return true;
    }

    static function authenticateScript( $abort = true, $name = null, $pass = null, $storeInSession = true ) {
        // auth to make sure the user has a login/password to do a shell
        // operation
        // later on we'll link this to acl's
        if ( ! $name ) {
            $name = trim( CRM_Utils_Array::value( 'name', $_REQUEST ) );
            $pass = trim( CRM_Utils_Array::value( 'pass', $_REQUEST ) );
        }

        if ( ! $name ) { // its ok to have an empty password
            return self::authenticateAbort( "ERROR: You need to send a valid user name and password to execute this file\n",
                                            $abort );
        }

        if ( ! self::authenticateKey( $abort ) ) {
            return false;
        }

        $result = CRM_Utils_System::authenticate( $name, $pass );
        if ( ! $result ) {
            return self::authenticateAbort( "ERROR: Invalid username and/or password\n",
                                            $abort );
        } else if ( $storeInSession ) {
            // lets store contact id and user id in session
            list( $userID, $ufID, $randomNumber ) = $result;
            if ( $userID && $ufID ) {
                $session = CRM_Core_Session::singleton( );
                $session->set( 'ufID'  , $ufID );
                $session->set( 'userID', $userID );
            } else {
                return self::authenticateAbort( "ERROR: Unexpected error, could not match userID and contactID",
                                                $abort );
            }
        }

        return $result;
    }

    /** 
     * Authenticate the user against the uf db 
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
        $config = CRM_Core_Config::singleton( ); 
        require_once( str_replace( '_', DIRECTORY_SEPARATOR, $config->userFrameworkClass ) . '.php' );
        return  
            eval( 'return ' . $config->userFrameworkClass . '::authenticate($name, $password);' ); 

    }

    /**  
     * Set a message in the UF to display to a user
     *  
     * @param string $name     the message to set
     *  
     * @access public  
     * @static  
     */  
    static function setUFMessage( $message ) {
        $config = CRM_Core_Config::singleton( );  
        require_once( str_replace( '_', DIRECTORY_SEPARATOR, $config->userFrameworkClass ) . '.php' );
        return   
            eval( 'return ' . $config->userFrameworkClass . '::setMessage( $message );' );
    }

   

    static function isNull( $value ) {
        if ( ! isset( $value ) || $value === null || $value === '' ) {
            return true;
        }
        if ( is_array( $value ) ) {
            foreach ( $value as $key => $value ) {
                if ( ! self::isNull( $value ) ) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    static function mungeCreditCard( $number, $keep = 4 ) {
        $number = trim( $number );
        if ( empty( $number ) ) {
            return null;
        }
        $replace = str_repeat( '*' , strlen( $number ) - $keep );
        return substr_replace( $number, $replace, 0, -$keep );
    }

    /** parse php modules from phpinfo */
    function parsePHPModules() {
        ob_start();
        phpinfo(INFO_MODULES);
        $s = ob_get_contents();
        ob_end_clean();
        
        $s = strip_tags($s,'<h2><th><td>');
        $s = preg_replace('/<th[^>]*>([^<]+)<\/th>/',"<info>\\1</info>",$s);
        $s = preg_replace('/<td[^>]*>([^<]+)<\/td>/',"<info>\\1</info>",$s);
        $vTmp = preg_split('/(<h2>[^<]+<\/h2>)/',$s,-1,PREG_SPLIT_DELIM_CAPTURE);
        $vModules = array();
        for ($i=1;$i<count($vTmp);$i++) {
            if (preg_match('/<h2>([^<]+)<\/h2>/',$vTmp[$i],$vMat)) {
                $vName = trim($vMat[1]);
                $vTmp2 = explode("\n",$vTmp[$i+1]);
                foreach ($vTmp2 AS $vOne) {
                    $vPat = '<info>([^<]+)<\/info>';
                    $vPat3 = "/$vPat\s*$vPat\s*$vPat/";
                    $vPat2 = "/$vPat\s*$vPat/";
                    if (preg_match($vPat3,$vOne,$vMat)) { // 3cols
                        $vModules[$vName][trim($vMat[1])] = array(trim($vMat[2]),trim($vMat[3]));
                    } elseif (preg_match($vPat2,$vOne,$vMat)) { // 2cols
                        $vModules[$vName][trim($vMat[1])] = trim($vMat[2]);
                    }
                }
            }
        }
        return $vModules;
    }

    /** get a module setting */
    function getModuleSetting($pModuleName,$pSetting) {
        $vModules = self:: parsePHPModules();
        return $vModules[$pModuleName][$pSetting];
    }
  
    static function memory( $title = null ) {
        static $pid = null;
        if ( ! $pid ) {
            $pid = posix_getpid( );
        }

        $memory = str_replace("\n", '', shell_exec("ps -p". $pid ." -o rss="));
        $memory .= ", " . time( );
        if ( $title ) {
            CRM_Core_Error::debug_var( $title, $memory );
        }
        return $memory;
    }

    static function download( $name, $mimeType, &$buffer,
                              $ext = null,
                              $output = true ) {
        $now       = gmdate('D, d M Y H:i:s') . ' GMT';

        header('Content-Type: ' . $mimeType); 
        header('Expires: ' . $now);
        
        // lem9 & loic1: IE need specific headers
        $isIE = strstr( $_SERVER['HTTP_USER_AGENT'], 'MSIE' );
        if ( $ext ) {
            $fileString = "filename=\"{$name}.{$ext}\"";
        } else {
            $fileString = "filename=\"{$name}\"";
        }
        if ( $isIE ) {
            header("Content-Disposition: inline; $fileString");
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
        } else {
            header("Content-Disposition: attachment; $fileString");
            header('Pragma: no-cache');
        }

        if ( $output ) {
            print $buffer;
            self::civiExit( );
        }
    }

    static function xMemory( $title = null, $log = false ) {
        $mem = (float ) xdebug_memory_usage( ) / (float ) ( 1024 );
        $mem = number_format( $mem, 5 ) . ", " . time( );
        if ( $log ) {
            echo "<p>$title: $mem<p>";
            flush( );
            CRM_Core_Error::debug_var( $title, $mem );
        } else {
            echo "<p>$title: $mem<p>";
            flush( );
        }
    }

    static function fixURL( $url ) {
        $components = parse_url( $url );

        if ( ! $components ) {
            return null;
        }

        // at some point we'll add code here to make sure the url is not
        // something that will mess up up, so we need to clean it up here
        return $url;
    }

    /**
     * make sure the callback is valid in the current context
     *
     * @param string $callback the name of the function
     *
     * @return boolean
     * @static
     */
    static function validCallback( $callback ) {
        if ( self::$_callbacks === null ) {
            self::$_callbacks = array( );
        }

        if ( ! array_key_exists( $callback, self::$_callbacks ) ) {
            if ( strpos( $callback, '::' ) !== false ) {
                list($className, $methodName) = explode('::', $callback);
                $fileName = str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
                @include_once( $fileName ); // ignore errors if any
                if ( ! class_exists( $className ) ) {
                    self::$_callbacks[$callback] = false;
                } else {
                    // instantiate the class
                    $object = new $className();
                    if ( ! method_exists( $object, $methodName ) ) {
                        self::$_callbacks[$callback] = false; 
                    } else {
                        self::$_callbacks[$callback] = true;
                    }
                }
            } else {
                self::$_callbacks[$callback] = function_exists( $callback );
            }
        }
        return self::$_callbacks[$callback];
    }

    /**
     * This serves as a wrapper to the php explode function
     * we expect exactly $limit arguments in return, and if we dont
     * get them, we pad it with null
     */
    static function explode( $separator, $string, $limit ) {
        $result = explode( $separator, $string, $limit );
        for ( $i = count( $result ); $i < $limit; $i++ ) {
            $result[$i] = null;
        }
        return $result;
    }

    static function checkURL( $url, $addCookie = false ) {
        CRM_Core_Error::ignoreException( );
        require_once 'HTTP/Request.php';
        $params = array( 'method' => 'GET' );
        $request = new HTTP_Request( $url, $params );
        if ( $addCookie ) {
            foreach ( $_COOKIE as $name => $value ) {
                $request->addCookie( $name, $value );
            }
        }
        $config = CRM_Core_Config::singleton( );
        if ( $config->userFramework == 'Standalone' ) {
            session_write_close();
        }
        $request->sendRequest( );
        $result = $request->getResponseCode( ) == 200 ? true : false;
        if ( $config->userFramework == 'Standalone' ) {
            session_start ();
        }
        CRM_Core_Error::setCallback( );
        return $result;
    }

    static function checkPHPVersion( $ver = 5, $abort = true ) {
        $phpVersion = substr( PHP_VERSION, 0, 1 );
        if ( $phpVersion >= $ver ) {
            return true;
        }

        if ( $abort ) {
            CRM_Core_Error::fatal( ts( 'This feature requires PHP Version %1 or greater',
                                       array( 1 => $ver ) ) );
        }
        return false;
    }

    static function formatWikiURL( $string, $encode = false ) {
        $items = explode( ' ', trim( $string ), 2 );
        if ( count( $items ) == 2 ) {
            $title = $items[1];
        } else {
            $title = $items[0];
        }

        // fix for CRM-4044
        $url = $encode ? self::urlEncode( $items[0] ) : $items[0];
        return "<a href=\"$url\">$title</a>";
    }

    static function urlEncode( $url ) {
        $items = parse_url( $url );
        if ( $items === false ) {
            return null;
        }

        if ( ! CRM_Utils_Array::value( 'query', $items ) ) {
            return $url;
        }

        $items['query'] = urlencode( $items['query'] );

        $url = $items['scheme'] . '://';
        if ( CRM_Utils_Array::value( 'user', $items ) ) {
            $url .= "{$items['user']}:{$items['pass']}@";
        }

        $url .= $items['host'];
        if ( CRM_Utils_Array::value( 'port', $items ) ) {
            $url .= ":{$items['port']}";
        }

        $url .= "{$items['path']}?{$items['query']}";
        if ( CRM_Utils_Array::value( 'fragment', $items ) ) {
            $url .= "#{$items['fragment']}";
        }

        return $url;
    }

    /**
     * Function to return the latest civicrm version.
     *
     * @return string civicrm version
     * @access public
     */
    static function version( ) {
        static $version;
        
        if ( ! $version ) {
            $verFile = implode( DIRECTORY_SEPARATOR, 
                                array(dirname(__FILE__), '..', '..', 'civicrm-version.txt') );
            if ( file_exists( $verFile ) ) {
                $str     = file_get_contents( $verFile );
                $parts   = explode( ' ', $str );
                $version = trim( $parts[0] );
            } else {
                // svn installs don't have version.txt by default. In that case version.xml should help - 
                $verFile = implode( DIRECTORY_SEPARATOR,
                                    array( dirname( __FILE__ ), '..', '..', 'xml', 'version.xml' ) );
                if ( file_exists( $verFile ) ) {
                    $str     = file_get_contents( $verFile );
                    $xmlObj  = simplexml_load_string( $str );
                    $version = (string) $xmlObj->version_no;
                }
            }

            // pattern check
            if ( !CRM_Utils_System::isVersionFormatValid( $version ) ) {
                CRM_Core_Error::fatal('Unknown codebase version.');
            }
        }

        return $version;
    }

    static function isVersionFormatValid( $version ) {
        return preg_match("/^(\d{1,2}\.){2}(\d{1,2}|(alpha|beta)\d{1,2})(\.upgrade)?$/", $version );
    }

    static function getAllHeaders( ) {
        if ( function_exists( 'getallheaders' ) ) {
            return getallheaders( );
        }

        // emulate get all headers
        // http://www.php.net/manual/en/function.getallheaders.php#66335
        $headers = array( );
        foreach ( $_SERVER as $name => $value ) {
            if ( substr( $name, 0, 5) == 'HTTP_' ) {
                $headers[str_replace( ' ',
                                      '-',
                                      ucwords( strtolower( str_replace( '_',
                                                                        ' ',
                                                                        substr( $name, 5 ) )
                                                           ) )
                                      )] = $value;
            }
        }
        return $headers;
    }

    static function getRequestHeaders() {
        if (function_exists('apache_request_headers')) {
            return apache_request_headers();
        } else {
            return $_SERVER;
        }
    }

    static function redirectToSSL( $abort = false ) {
        $config = CRM_Core_Config::singleton( );
        $req_headers = CRM_Utils_System::getRequestHeaders();
        if ( $config->enableSSL             &&
             ( ! isset( $_SERVER['HTTPS'] ) ||
               strtolower( $_SERVER['HTTPS'] )  == 'off' ) &&
               strtolower( $req_headers['X_FORWARDED_PROTO'] ) != 'https' ) {
            // ensure that SSL is enabled on a civicrm url (for cookie reasons etc)
            $url = "https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
            if ( ! self::checkURL( $url, true ) ) {
                if ( $abort ) {
                    CRM_Core_Error::fatal( 'HTTPS is not set up on this machine' );
                } else {
                    CRM_Core_Session::setStatus( 'HTTPS is not set up on this machine' );
                    // admin should be the only one following this
                    // since we dont want the user stuck in a bad place
                    return;
                }
            }
            CRM_Utils_System::redirect( $url );
        }
    }

    static function ipAddress( ) {
        $address = CRM_Utils_Array::value( 'REMOTE_ADDR', $_SERVER );
        // hack for safari
        if ( $address == '::1' ) {
            $address = '127.0.0.1';
        }
        return $address;
    }

    /**
     * Returns you the referring / previous page url
     *
     * @return string the previous page url
     * @access public
     */
    static function refererPath( ) {
        return CRM_Utils_Array::value( 'HTTP_REFERER', $_SERVER );
    }
    
    /**
     * Returns documentation URL base
     *
     * @return string documentation url
     * @access public
     */
    static function getDocBaseURL( ) {
        // FIXME: move this to configuration at some stage
        return 'http://wiki.civicrm.org/confluence/display/CRMDOC/';
    }

    /**
     * Returns URL or link to documentation page, based on provided parameters.
     * For use in PHP code.
     * WARNING: Always returns URL, if ts function is not defined ($URLonly has no effect).
     *
     * @param string  $page    Title of documentation wiki page
     * @param boolean $URLonly Whether function should return URL only or whole link (default)
     * @param string  $text    Text of HTML link (no effect if $URLonly = false)
     * @param string  $title   Tooltip text for HTML link (no effect if $URLonly = false)
     * @param string  $style   Style attribute value for HTML link (no effect if $URLonly = false)
     *
     * @return string URL or link to documentation page, based on provided parameters
     * @access public
     */
    static function docURL2( $page, $URLonly = false, $text = null, $title = null, $style = null ) {
        // if ts function doesn't exist, it means that CiviCRM hasn't been fully initialised yet -
        // return just the URL, no matter what other parameters are defined
        if (!function_exists('ts')) {
            $docBaseURL = self::getDocBaseURL( );
            return $docBaseURL . str_replace( ' ', '+', $page );
        } else {
            $params = array(
                'page'    => $page,
                'URLonly' => $URLonly,
                'text'    => $text,
                'title'   => $title,
                'style'   => $style,
            );
            return self::docURL( $params );
        }
    }


    /**
     * Returns URL or link to documentation page, based on provided parameters.
     * For use in templates code.
     *
     * @param array $params An array of parameters (see CRM_Utils_System::docURL2 method for names)
     *
     * @return string URL or link to documentation page, based on provided parameters
     * @access public
     */
    static function docURL( $params ) {

        if ( ! isset( $params['page'] ) ) {
            return;
        }

        $docBaseURL = self::getDocBaseURL( );

        if (!isset($params['title']) or $params['title'] === null) {
            $params['title'] = ts( 'Opens documentation in a new window.' );
        }

        if (!isset($params['text']) or $params['text'] === null) {
            $params['text'] = ts( '(learn more...)' );
        }
    
        if ( ! isset( $params['style'] ) || $params['style'] === null ) {
            $style = '';
        } else {
            $style = "style=\"{$params['style']}\"";
        }

        $link = $docBaseURL . str_replace( ' ', '+', $params['page'] );

        if ( isset( $params['URLonly'] ) && $params['URLonly'] == true ) {
            return $link;
        } else {
            return "<a href=\"{$link}\" $style target=\"_blank\" title=\"{$params['title']}\">{$params['text']}</a>";
        }

    }

    /**
     * Get the locale set in the hosting CMS
     * @return string  the used locale or null for none
     */
    static function getUFLocale()
    {
        $config = CRM_Core_Config::singleton();
        require_once(str_replace('_', DIRECTORY_SEPARATOR, $config->userFrameworkClass) . '.php');
        return eval("return {$config->userFrameworkClass}::getUFLocale();");
    }
    
    /**
     * Execute external or internal urls and return server response
     *
     *  @param string   $url request url 
     *  @param boolean  $addCookie  should be true to access internal urls
     *
     *  @return string  $response response from url
     *  @static
     */
    static function getServerResponse( $url, $addCookie = true) {
        CRM_Core_Error::ignoreException( );
        require_once 'HTTP/Request.php';
        $request = new HTTP_Request( $url );
        
        if ( $addCookie ) {
            foreach ( $_COOKIE as $name => $value ) {
                $request->addCookie( $name, $value );
            }
        }
        
        if ( isset( $_SERVER['AUTH_TYPE'] ) ) {
            $request->setBasicAuth( $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'] );
        } 

        $config = CRM_Core_Config::singleton( );
        if ( $config->userFramework == 'Standalone' ) {
            session_write_close();
        }

        $request->sendRequest( );
        $response = $request->getResponseBody( );

        CRM_Core_Error::setCallback( );
        return $response;
    }

    static function isDBVersionValid( &$errorMessage ) 
    {
        require_once 'CRM/Core/BAO/Domain.php';
        $dbVersion = CRM_Core_BAO_Domain::version( );

        if ( ! $dbVersion ) {
            // if db.ver missing
            $errorMessage = ts( 'Version information found to be missing in database. You will need to determine the correct version corresponding to your current database state.' );
            return false;
        } else if ( !CRM_Utils_System::isVersionFormatValid( $dbVersion ) ) {
            $errorMessage = ts( 'Database is marked with invalid version format. You may want to investigate this before you proceed further.' );
            return false;
        } else if ( stripos($dbVersion, 'upgrade') ) {
            // if db.ver indicates a partially upgraded db
            $upgradeUrl   = CRM_Utils_System::url( "civicrm/upgrade", "reset=1" );
            $errorMessage = ts( 'Database check failed - the database looks to have been partially upgraded. You may want to reload the database with the backup and try the <a href=\'%1\'>upgrade process</a> again.', array( 1 => $upgradeUrl ) );
            return false;
        } else {
            $codeVersion = CRM_Utils_System::version( );

            // if db.ver < code.ver, time to upgrade
            if ( version_compare($dbVersion, $codeVersion) < 0 ) {
                $upgradeUrl   = CRM_Utils_System::url( "civicrm/upgrade", "reset=1" );
                $errorMessage = ts( 'New codebase version detected. You might want to visit <a href=\'%1\'>upgrade screen</a> to upgrade the database.', array( 1 => $upgradeUrl ) );
                return false;
            }

            // if db.ver > code.ver, sth really wrong
            if ( version_compare($dbVersion, $codeVersion) > 0 ) {
                $errorMessage = ts( 'Your database is marked with an unexpected version number: %1. The v%2 codebase may not be compatible with your database state. You will need to determine the correct version corresponding to your current database state. You may want to revert to the codebase you were using until you resolve this problem.',
                                    array( 1 => $dbVersion, 2 => $codeVersion ) );
                $errorMessage .= "<p>" . ts( 'OR if this is an svn install, you might want to fix version.txt file.' ) . "</p>";
                return false;
            }
        }
        // FIXME: there should be another check to make sure version is in valid format - X.Y.alpha_num

        return true;
    }

    static function civiExit( $status = 0 ) {
        // move things to CiviCRM cache as needed
        require_once 'CRM/Core/Session.php';
        CRM_Core_Session::storeSessionObjects( );
        
        exit( $status );
    }

    /**
     * Reset the memory cache, typically memcached
     */
    static function flushCache( $daoName = null ) {
        // flush out all cache entries so we can reload new data
        // a bit aggressive, but livable for now
        require_once 'CRM/Utils/Cache.php';
        $cache =& CRM_Utils_Cache::singleton( );
        $cache->flush( );
    }

    /**
     * load cms bootstrap
     *
     * @param $name string  optional username for login
     * @param $pass string  optional password for login
     */
    static function loadBootStrap($name = null, $pass = null)
    {
        $config = CRM_Core_Config::singleton();
        require_once(str_replace('_', DIRECTORY_SEPARATOR, $config->userFrameworkClass) . '.php');
        return call_user_func("{$config->userFrameworkClass}::loadBootStrap", $name, $pass);
    }
    
    /**
     * check is user logged in.
     *
     * @return boolean.
     */
    public static function isUserLoggedIn( ) {
        $config = CRM_Core_Config::singleton();
        require_once(str_replace('_', DIRECTORY_SEPARATOR, $config->userFrameworkClass) . '.php');
        return eval('return '. $config->userFrameworkClass . '::isUserLoggedIn( );');
    }
    
    /**
     * Get current logged in user id.
     *
     * @return int ufId, currently logged in user uf id.
     */
    public static function getLoggedInUfID( ) {
        $config = CRM_Core_Config::singleton( );
        require_once(str_replace('_', DIRECTORY_SEPARATOR, $config->userFrameworkClass) . '.php');
        return eval('return '. $config->userFrameworkClass . '::getLoggedInUfID( );');
    }

    static function baseCMSURL( ) {
        static $_baseURL = null;
        if ( ! $_baseURL ) {
            $config =& CRM_Core_Config::singleton( );
            $_baseURL = $userFrameworkBaseURL = $config->userFrameworkBaseURL;

            if ( $config->userFramework == 'Joomla' ) {
                // gross hack
                // we need to remove the administrator/ from the end
                $_baseURL = str_replace( "/administrator/", "/", $userFrameworkBaseURL );
            } else {
                // Drupal setting
                global $civicrm_root;
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
                        
                        $_baseURL = $userFrameworkBaseURL . "sites/$siteName/";
                    }
                }
            }
        }
        return $_baseURL;
    }

    static function relativeURL( $url ) {
        // check if url is relative, if so return immediately
        if ( substr( $url, 0, 4 ) != 'http' ) {
            return $url;
        }

        // make everything relative from the baseFilePath
        $baseURL = self::baseCMSURL( );

        // check if baseURL is a substr of $url, if so
        // return rest of string
        if ( substr( $url, 0, strlen( $baseURL ) ) == $baseURL ) {
            return substr( $url, strlen( $baseURL ) );
        }
        
        // return the original value
        return $url;
    }

    static function absoluteURL( $url ) {
        // check if url is already absolute, if so return immediately
        if ( substr( $url, 0, 4 ) == 'http' ) {
            return $url;
        }

        // make everything absolute from the baseFileURL
        $baseURL = self::baseCMSURL( );

        return $baseURL . $url;
    }
    
}
