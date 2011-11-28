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



require_once 'HTML/QuickForm/Rule/Email.php';

/**
 * This class contains string functions
 *
 */

class CRM_Utils_String {
  
    const
        COMMA          = ","   ,
        SEMICOLON      = ";"   ,
        SPACE          = " "   ,
        TAB            = "\t"  ,
        LINEFEED       = "\n"  ,
        CARRIAGELINE   = "\r\n",
        LINECARRIAGE   = "\n\r",
        CARRIAGERETURN = "\r"  ;

    /**
     * Convert a display name into a potential variable
     * name that we could use in forms/code
     * 
     * @param  name    Name of the string
     * @return string  An equivalent variable name
     *
     * @access public
     * @return string (or null)
     * @static
     */
    static function titleToVar( $title, $maxLength = 31 ) {
        $variable = self::munge( $title, '_', $maxLength );
      
        require_once "CRM/Utils/Rule.php";
        if ( CRM_Utils_Rule::title( $variable, $maxLength ) ) {
            return $variable;
        }

        // if longer than the maxLength lets just return a substr of the
        // md5 to prevent errors downstream
        return substr( md5( $title ), 0, $maxLength );
    }

    /**
     * given a string, replace all non alpha numeric characters and
     * spaces with the replacement character
     *
     * @param string $name the name to be worked on
     * @param string $char the character to use for non-valid chars
     * @param int    $len  length of valid variables
     *
     * @access public
     * @return string returns the manipulated string
     * @static
     */
    static function munge( $name, $char = '_', $len = 63 ) {
        // replace all white space and non-alpha numeric with $char
        $name = preg_replace('/\s+|\W+/', $char, trim($name) );

        if ( $len ) {
            // lets keep variable names short
            return substr( $name, 0, $len );
        } else {
            return $name;
        }
    }


    /* 
     * Takes a variable name and munges it randomly into another variable name
     *  
     * @param  string $name    Initial Variable Name
     * @param int     $len  length of valid variables
     *
     * @return string  Randomized Variable Name
     * @access public 
     * @static
     */
    static function rename( $name, $len = 4 ) {
        $rand = substr( uniqid(), 0, $len );
        return substr_replace( $name, $rand, -$len, $len );
    }

    /**
     * takes a string and returns the last tuple of the string.
     * useful while converting file names to class names etc
     *
     * @param string $string the input string
     * @param char   $char   the character used to demarcate the componets
     *
     * @access public
     * @return string the last component
     * @static
     */
    static function getClassName( $string, $char = '_' ) {
        if( !is_array( $string ) ) {
            $names = explode( $char, $string );
        }
        if( is_array( $names ) )  return array_pop( $names ); 
    }
    
    /**
     * appends a name to a string and seperated by delimiter.
     * does the right thing for an empty string
     *
     * @param string $str   the string to be appended to
     * @param string $delim the delimiter to use
     * @param mixed  $name  the string (or array of strings) to append 
     *
     * @return void
     * @access public
     * @static
     */
    static function append( &$str, $delim, $name ) {
        if ( empty( $name ) ) {
            return;
        }

        if ( is_array( $name ) ) {
            foreach ( $name as $n ) {
                if ( empty( $n ) ) {
                    continue;
                }
                if ( empty( $str ) ) {
                    $str = $n;
                } else {
                    $str .= $delim . $n;
                }
            }
        } else {
            if ( empty( $str ) ) {
                $str = $name;
            } else {
                $str .= $delim . $name;
            }
        }
    }

    /**
     * determine if the string is composed only of ascii characters
     *
     * @param string  $str input string
     * @param boolean $utf8 attempt utf8 match on failure (default yes)
     *
     * @return boolean    true if string is ascii
     * @access public
     * @static
     */
    static function isAscii( $str, $utf8 = true ) {
        if( ! function_exists( 'mb_detect_encoding' ) ) {
            $str = preg_replace( '/\s+/', '', $str ); // eliminate all white space from the string
            /* FIXME:  This is a pretty brutal hack to make utf8 and 8859-1 work.
             */
        
            /* match low- or high-ascii characters */
            if ( preg_match( '/[\x00-\x20]|[\x7F-\xFF]/', $str ) )  {
            // || // low ascii characters
            //  preg_match( '/[\x7F-\xFF]/', $str ) ) {   // high ascii characters
                if ($utf8) {
                    /* if we did match, try for utf-8, or iso8859-1 */
                    return self::isUtf8( $str );
                } else {
                    return false;
                }
            }
            return true;
        } else {
            $order = array( 'ASCII' ); 
            if ($utf8) {
                $order[] = 'UTF-8';
            }
            $enc = mb_detect_encoding($str, $order, true); 
            return ($enc == 'ASCII' || $enc == 'UTF-8');
        }
    }
    
    /**
     * determine the string replacements for redaction
     * on the basis of the regular expressions
     *
     * @param string $str        input string
     * @param array  $regexRules regular expression to be matched w/ replacements
     *
     * @return array $match      array of strings w/ corresponding redacted outputs 
     * @access public
     * @static
     */
    static function regex( $str, $regexRules ) {
        //redact the regular expressions
        if ( !empty( $regexRules ) && isset( $str ) ) {
            static $matches, $totalMatches, $match = array();
            foreach ( $regexRules as $pattern => $replacement ) {
                preg_match_all( $pattern, $str, $matches );
                if ( !empty( $matches[0] ) ) {
                    if ( empty( $totalMatches ) ) {
                        $totalMatches = $matches[0];
                    } else { 
                        $totalMatches = array_merge( $totalMatches, $matches[0] );
                    }
                     $match = array_flip( $totalMatches );
                }
            }
        } 
        
        if ( !empty( $match ) ) {
            foreach ( $match as $matchKey => &$dontCare ) {
                foreach ( $regexRules as $pattern => $replacement ) {
                    if ( preg_match( $pattern, $matchKey ) ) {
                        $dontCare = $replacement .substr(md5($matchKey),0,5);
                        break;
                    }
                }
            }
            return $match;
        }
        return CRM_Core_DAO::$_nullArray;
    }
    
    static function redaction( $str, $stringRules ) {
        //redact the strings
        if (!empty($stringRules)){
            foreach ($stringRules as $match => $replace) {
                $str = str_ireplace($match, $replace, $str);
            }
        }
        
        //return the redacted output
        return $str;
    }
    
    /**
     * Determine if a string is composed only of utf8 characters
     *
     * @param string $str  input string
     * @access public
     * @static
     * @return boolean
     */
    static function isUtf8( $str ) {
        if( ! function_exists( mb_detect_encoding ) ) {
            $str = preg_replace( '/\s+/', '', $str ); // eliminate all white space from the string
        
            /* pattern stolen from the php.net function documentation for
             * utf8decode();
             * comment by JF Sebastian, 30-Mar-2005
             */
            return  preg_match( '/^([\x00-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xec][\x80-\xbf]{2}|\xed[\x80-\x9f][\x80-\xbf]|[\xee-\xef][\x80-\xbf]{2}|f0[\x90-\xbf][\x80-\xbf]{2}|[\xf1-\xf3][\x80-\xbf]{3}|\xf4[\x80-\x8f][\x80-\xbf]{2})*$/' , $str );
            // || 
            // iconv('ISO-8859-1', 'UTF-8', $str);
        } else {
            $enc = mb_detect_encoding($str, array('UTF-8'), true); 
            return ($enc !== false);         
        }
    }
    /**
     * determine if two href's are equivalent (fuzzy match)
     *
     * @param string $url1 the first url to be matched
     * @param string $url2 the second url to be matched against
     *
     * @return boolean true if the urls match, else false
     * @access public
     * @static
     */
    function match( $url1, $url2 ) {
        $url1 = strtolower( $url1 );
        $url2 = strtolower( $url2 );

        $url1Str = parse_url( $url1 );
        $url2Str = parse_url( $url2 );

        if ( $url1Str['path'] == $url2Str['path'] && 
             self::extractURLVarValue( CRM_Utils_Array::value( 'query', $url1Str) ) == self::extractURLVarValue(  CRM_Utils_Array::value( 'query', $url2Str) ) ) {
            return true;
        }
        return false;
    }

    /**
     * Function to extract variable values
     *
     * @param  mix $query this is basically url
     *
     * @return mix $v  returns civicrm url (eg: civicrm/contact/search/...)
     * @access public
     */
    function extractURLVarValue( $query ) {
        $config = CRM_Core_Config::singleton( );
        $urlVar =  $config->userFrameworkURLVar;

        $params = explode( '&', $query );
        foreach ( $params as $p ) {
            if ( strpos( $p, '=' ) ) {
                list( $k, $v ) = explode( '=', $p );
                if ( $k == $urlVar ) {
                    return $v;
                }
            }
        }
        return null;
    }

    /**
     * translate a true/false/yes/no string to a 0 or 1 value
     *
     * @param string $str  the string to be translated
     * @return boolean
     * @access public
     * @static
     */
    static function strtobool($str) {
        if ( preg_match('/^(y(es)?|t(rue)?|1)$/i', $str) ) {
            return true;
        }
        return false;
    }

    /**
     * returns string '1' for a true/yes/1 string, and '0' for no/false/0 else returns false
     *
     * @param string $str  the string to be translated
     * @return boolean
     * @access public
     * @static
     */
    static function strtoboolstr($str) {
        if ( preg_match('/^(y(es)?|t(rue)?|1)$/i', $str) ) {
            return '1';
        } else if ( preg_match('/^(n(o)?|f(alse)?|0)$/i', $str) ) {
            return '0';
        }else {            
            return false;
        }
    }

    /**
     * Convert a HTML string into a text one using html2text
     *
     * @param string $html  the tring to be converted
     * @return string       the converted string
     * @access public
     * @static
     */
    static function htmlToText($html) {
        require_once 'packages/html2text/class.html2text.inc';
        $converter = new html2text($html);
        return $converter->get_text();
    }

    static function extractName( $string, &$params ) {
        $name = trim( $string );
        if ( empty( $name ) ) {
            return;
        }

        // strip out quotes
        $name = str_replace('"', '', $name);
        $name = str_replace('\'', '', $name);
        
        // check for comma in name
        if ( strpos( $name, ',' ) !== false ) {
            
            // name has a comma - assume lname, fname [mname]
            $names = explode( ',', $name );
            if ( count( $names ) > 1) {
                $params['last_name'] = trim( $names[0] );
                
                // check for space delim
                $fnames = explode( ' ', trim( $names[1] ) );
                if ( count( $fnames ) > 1 ) {
                    $params['first_name' ] = trim( $fnames[0] );
                    $params['middle_name'] = trim( $fnames[1] );
                } else {
                    $params['first_name'] = trim( $fnames[0] );
                }
            } else {
                $params['first_name'] = trim( $names[0] );
            }
        } else {
            
            // name has no comma - assume fname [mname] fname
            $names = explode( ' ', $name );
            if ( count( $names ) == 1 ) {
                $params['first_name'] = $names[0];
            } else if ( count( $names ) == 2 ) {
                $params['first_name'] = $names[0];
                $params['last_name' ] = $names[1];
            } else {
                $params['first_name' ] = $names[0];
                $params['middle_name'] = $names[1];
                $params['last_name'  ] = $names[2];
            }
        }
    }

    static function &makeArray( $string ) {
        $string = trim( $string );

        $values = explode( "\n", $string );
        $result = array( );
        foreach ( $values as $value ) {
            list( $n, $v ) = CRM_Utils_System::explode( '=', $value, 2 );
            if ( ! empty( $v ) ) {
                $result[trim($n)] = trim($v);
            }
        }
        return $result;
    }

    /**
     * Function to add include files needed for jquery
     */
    static function addJqueryFiles( &$html ) {
        $smarty = CRM_Core_Smarty::singleton( );
        return $smarty->fetch( 'CRM/common/jquery.tpl' ) . $html;
    }

    /**
     * Given an ezComponents-parsed representation of
     * a text with alternatives return only the first one
     *
     * @param string $full  all alternatives as a long string (or some other text)
     *
     * @return string       only the first alternative found (or the text without alternatives)
     */
    static function stripAlternatives($full)
    {
        $matches = array();
        preg_match('/-ALTERNATIVE ITEM 0-(.*?)-ALTERNATIVE ITEM 1-.*-ALTERNATIVE END-/s', $full, $matches);

        if ( isset( $matches[1] ) &&
             trim( strip_tags( $matches[1] ) ) != '' ) {
            return $matches[1];
        } else {
            return $full;
        }
    }

    /** 
     * strip leading, trailing, double spaces from string
     * used for postal/greeting/addressee
     * @param string  $string input string to be cleaned
     *
     * @return string the cleaned string
     * @access public
     * @static
     */
	static function stripSpaces( $string ) 
	{
        if ( empty($string) ) {
            return $string;
        }
        
        $pat = array( 0 => "/^\s+/",
                      1 =>  "/\s{2,}/", 
                      2 => "/\s+\$/" );
        
        $rep = array( 0 => "",
                      1 => " ",
                      2 => "" );
        
        return preg_replace( $pat, $rep, $string );
	}

    /**
     * This function is used to clean the URL 'path' variable that we use 
     * to construct CiviCRM urls by removing characters from the path variable
     *
     * @param string $string  the input string to be sanitized
     * @param array  $search  the characters to be sanitized
     * @param string $replace the character to replace it with
     *
     * @return string the sanitized string
     * @access public
     * @static
     */
    static function stripPathChars( $string,
                                    $search  = null,
                                    $replace = null ) {
        static $_searchChars  = null;
        static $_replaceChar  = null;

        if ( empty( $string ) ) {
            return $string;
        }
        
        if ( $_searchChars == null ) {
            $_searchChars = array( '&', ';', ',', '=', '$',
                                   '"', "'", '\\',
                                   '<', '>', '(', ')',
                                   ' ', "\r", "\r\n", "\n", "\t" );
            $_replaceChar = '_';
        }
                                   
        
        if ( $search == null ) {
            $search = $_searchChars;
        }

        if ( $replace == null ) {
            $replace = $_replaceChar;
        }

        return str_replace( $search, $replace, $string );
    }
}

