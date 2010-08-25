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
 * class to provide simple static functions for file objects
 */
class CRM_Utils_File {

    /**
     * Given a file name, determine if the file contents make it an ascii file
     *
     * @param string $name name of file
     *
     * @return boolean     true if file is ascii
     * @access public
     */
    static function isAscii( $name ) {
        $fd = fopen( $name, "r" );
        if ( ! $fd ) {
            return false;
        }

        $ascii = true;
        while (!feof($fd)) {
            $line = fgets( $fd, 8192 );
            if ( ! CRM_Utils_String::isAscii( $line ) ) {
                $ascii = false;
                break;
            }
        }

        fclose( $fd );
        return $ascii;
    }

    /**
     * Given a file name, determine if the file contents make it an html file
     *
     * @param string $name name of file
     *
     * @return boolean     true if file is html
     * @access public
     */
    static function isHtml( $name ) {
        $fd = fopen( $name, "r" );
        if ( ! $fd ) {
            return false;
        }

        $html = false;
        $lineCount = 0;
        while ( ! feof( $fd ) & $lineCount <= 5 ) {
            $lineCount++;
            $line = fgets( $fd, 8192 );
            if ( ! CRM_Utils_String::isHtml( $line ) ) {
                $html = true;
                break;
            }
        }

        fclose( $fd );
        return $html;
    }

    /**
     * create a directory given a path name, creates parent directories
     * if needed
     * 
     * @param string $path  the path name
     * @param boolean $abort should we abort or just return an invalid code
     *
     * @return void
     * @access public
     * @static
     */
    function createDir( $path, $abort = true ) {
        if ( is_dir( $path ) || empty( $path ) ) {
            return;
        }

        CRM_Utils_File::createDir( dirname( $path ), $abort );
        if ( @mkdir( $path, 0777 ) == false ) {
            if ( $abort ) {
                $docLink = CRM_Utils_System::docURL2( 'Moving an Existing Installation to a New Server or Location', false, 'Moving an Existing Installation to a New Server or Location' );
                echo "Error: Could not create directory: $path.<p>If you have moved an existing CiviCRM installation from one location or server to another there are several steps you will need to follow. They are detailed on this CiviCRM wiki page - {$docLink}. A fix for the specific problem that caused this error message to be displayed is to set the value of the config_backend column in the civicrm_domain table to NULL. However we strongly recommend that you review and follow all the steps in that document.</p>";

                require_once 'CRM/Utils/System.php';
                CRM_Utils_System::civiExit( );
            } else {
                return false;
            }
        }
        return true;
    }

    /** 
     * delete a directory given a path name, delete children directories
     * and files if needed 
     *  
     * @param string $path  the path name 
     * 
     * @return void 
     * @access public 
     * @static 
     */ 
    public function cleanDir( $target, $rmdir = true ) {
        static $exceptions = array( '.', '..' );

        if ( $sourcedir = @opendir( $target ) ) {
            while ( false !== ( $sibling = readdir( $sourcedir ) ) ) {
                if ( ! in_array( $sibling, $exceptions ) ) {
                    $object = $target . DIRECTORY_SEPARATOR . $sibling;
                    
                    if ( is_dir( $object ) ) {
                        CRM_Utils_File::cleanDir( $object, $rmdir );
                    } else if ( is_file( $object ) ) {
                        $result = @unlink( $object );
                    }
                }
            }
            closedir( $sourcedir );
            
            if ( $rmdir ) {
                $result = @rmdir( $target );
            }
        }
    }

    /**
     * Given a file name, recode it (in place!) to UTF-8
     *
     * @param string $name name of file
     *
     * @return boolean  whether the file was recoded properly
     * @access public
     */
    static function toUtf8( $name ) {
        require_once 'CRM/Core/Config.php';
        static $config         = null;
        static $legacyEncoding = null;
        if ($config == null) {
            $config = CRM_Core_Config::singleton();
            $legacyEncoding = $config->legacyEncoding;
        }

        if (!function_exists('iconv')) return false;

        $contents = file_get_contents($name);
        if ($contents === false) return false;

        $contents = iconv($legacyEncoding, 'UTF-8', $contents);
        if ($contents === false) return false;

        $file = fopen($name, 'w');
        if ($file === false) return false;

        $written = fwrite($file, $contents);
        $closed  = fclose($file);
        if ($written === false or !$closed) return false;

        return true;
    }


    /** 
     * Appends trailing slashed to paths
     * 
     * @return string
     * @access public
     * @static
     */
    static function addTrailingSlash( $name, $separator = null ) 
    {
        if ( ! $separator ) {
            $separator = DIRECTORY_SEPARATOR;
        }
            
        if ( substr( $name, -1, 1 ) != $separator ) {
            $name .= $separator;
        }
        return $name;
    }


    function sourceSQLFile( $dsn, $fileName, $prefix = null, $isQueryString = false, $dieOnErrors = true ) {
        require_once 'DB.php';

        $db  =& DB::connect( $dsn );
        if ( PEAR::isError( $db ) ) {
            die( "Cannot open $dsn: " . $db->getMessage( ) );
        }

        if ( ! $isQueryString ) {
            $string = $prefix . file_get_contents( $fileName );
        } else {
            // use filename as query string
            $string = $prefix . $fileName;
        }

        //get rid of comments starting with # and --

        $string = preg_replace("/^#[^\n]*$/m", "\n", $string );
        $string = preg_replace("/^(--[^-]).*/m", "\n", $string );
        
        $queries  = preg_split('/;$/m', $string);
        foreach ( $queries as $query ) {
            $query = trim( $query );
            if ( ! empty( $query ) ) {
                $res =& $db->query( $query );
                if ( PEAR::isError( $res ) ) {
                    if ( $dieOnErrors ) {
                        die( "Cannot execute $query: " . $res->getMessage( ) );
                    } else {
                        echo "Cannot execute $query: " . $res->getMessage( ) . "<p>";
                    }
                }
            }
        }
    }

    static function isExtensionSafe( $ext ) {
        static $extensions = null;
        if ( ! $extensions ) {
            require_once 'CRM/Core/OptionGroup.php';
            $extensions = CRM_Core_OptionGroup::values( 'safe_file_extension', true );
            
            //make extensions to lowercase
            $extensions = array_change_key_case( $extensions, CASE_LOWER );
            // allow html/htm extension ONLY if the user is admin 
            // and/or has access CiviMail
            require_once 'CRM/Core/Permission.php';
            if ( ! CRM_Core_Permission::check( 'access CiviMail' ) &&
                 ! CRM_Core_Permission::check( 'administer CiviCRM' ) ) {
                unset( $extensions['html'] );
                unset( $extensions['htm' ] );
            }
        }
        //support lower and uppercase file extensions
        return isset( $extensions[strtolower( $ext )] ) ? true : false;
    }

    /**
     * remove the 32 bit md5 we add to the fileName
     * also remove the unknown tag if we added it
     */
    static function cleanFileName( $name ) {
        // replace the last 33 character before the '.' with null
        $name = preg_replace( '/(_[\w]{32})\./', '.', $name );
        return $name;
    }

    static function makeFileName( $name ) {
        $uniqID = md5( uniqid( rand( ), true ) );
        $info   = pathinfo( $name );
        $basename = substr($info['basename'],
                           0,
                           -( strlen( CRM_Utils_Array::value( 'extension', $info ) ) + ( CRM_Utils_Array::value( 'extension', $info ) == '' ? 0 : 1 ) ) );
        if ( ! self::isExtensionSafe( CRM_Utils_Array::value( 'extension', $info ) ) ) {
            // munge extension so it cannot have an embbeded dot in it
            // The maximum length of a filename for most filesystems is 255 chars.  
            // We'll truncate at 240 to give some room for the extension.
            return CRM_Utils_String::munge( "{$basename}_". CRM_Utils_Array::value( 'extension', $info ) . "_{$uniqID}", '_',  240 ) . ".unknown";
        } else {
            return CRM_Utils_String::munge( "{$basename}_{$uniqID}", '_',  240 ) . "." . CRM_Utils_Array::value( 'extension', $info );
        }
    }

    static function getFilesByExtension( $path, $ext ) {
        $path = self::addTrailingSlash( $path );
        $dh = opendir( $path );
        $files = array();
        while( false !== ( $elem = readdir( $dh ) ) ) {
            if( substr( $elem, -(strlen( $ext ) + 1 ) ) == '.' . $ext ) {
                $files[] .= $path . $elem;
            }
        }
        closedir( $dh );
        return $files;
    }

}


