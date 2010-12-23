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

require_once 'HTML/QuickForm/Rule/Email.php';

class CRM_Utils_Rule 
{

    static function title( $str, $maxLength = 127 ) 
    {
    
        // check length etc
        if ( empty( $str ) || strlen( $str ) > $maxLength ) {
            return false;
        }
    
        // Make sure it include valid characters, alpha numeric and underscores
        if ( ! preg_match('/^\w[\w\s\'\&\,\$\#\-\.\"\?\!]+$/i', $str ) ) {
            return false;
        }

        return true;
    }

    static function longTitle( $str ) 
    {
        return self::title( $str, 255 );
    }
    
    static function variable( $str ) 
    {
        // check length etc
        if ( empty( $str ) || strlen( $str ) > 31 ) {
            return false;
        }
        
        // make sure it include valid characters, alpha numeric and underscores
        if ( ! preg_match('/^[\w]+$/i', $str ) ) {
            return false;
        }

        return true;
    }

    static function qfVariable( $str ) 
    {
        // check length etc 
        //if ( empty( $str ) || strlen( $str ) > 31 ) {  
        if (  strlen(trim($str)) == 0 || strlen( $str ) > 31 ) {  
            return false; 
        } 
        
        // make sure it include valid characters, alpha numeric and underscores 
        // added (. and ,) option (CRM-1336)
        if ( ! preg_match('/^[\w\s\.\,]+$/i', $str ) ) { 
            return false; 
        } 
 
        return true; 
    } 

    static function phone( $phone ) 
    {
        // check length etc
        if ( empty( $phone ) || strlen( $phone ) > 16 ) {
            return false;
        }
    
        // make sure it include valid characters, (, \s and numeric
        if ( preg_match('/^[\d\(\)\-\.\s]+$/', $phone ) ) {
            return true;
        }
        return false;
    }

    static function query( $query ) 
    {
        // check length etc
        if ( empty( $query ) || strlen( $query ) < 3 || strlen( $query ) > 127 ) {
            return false;
        }
    
        // make sure it include valid characters, alpha numeric and underscores
        if ( ! preg_match('/^[\w\s\%\'\&\,\$\#]+$/i', $query ) ) {
            return false;
        }

        return true;
    }

    static function url( $url, $checkDomain = false) 
    {
        $options = array( 'domain_check'    => $checkDomain,
                          'allowed_schemes' => array( 'http', 'https', 'mailto', 'ftp' ) );

        require_once 'Validate.php';
        return Validate::uri( $url, $options );
    }

    static function wikiURL( $string )
    {
        $items = explode( ' ', trim( $string ), 2 );
        return self::url( $items[0] );
    }

    static function domain( $domain ) 
    {
        // not perfect, but better than the previous one; see CRM-1502
        if ( ! preg_match('/^[A-Za-z0-9]([A-Za-z0-9\.\-]*[A-Za-z0-9])?$/', $domain ) ) {
            return false;
        }
        return true;
    }

    static function date($value, $default = null) 
    {
        if (is_string($value) &&
            preg_match('/^\d\d\d\d-?\d\d-?\d\d$/', $value)) {
            return $value;
        }
        return $default;
    }
    
    static function dateTime($value, $default = null) 
    {
        $result = $default;
        if ( is_string( $value ) &&
             preg_match( '/^\d\d\d\d-?\d\d-?\d\d(\s\d\d:\d\d:\d\d|\d\d\d\d\d\d)?$/', $value ) ) {
            $result = $value;
        }
        
        return $result;
    }
    
    /** 
     * check the validity of the date (in qf format) 
     * note that only a year is valid, or a mon-year is 
     * also valid in addition to day-mon-year. The date
     * specified has to be beyond today. (i.e today or later)
     * 
     * @param array $date 
     * @param bool  $monthRequired check whether month is mandatory
     *
     * @return bool true if valid date 
     * @static 
     * @access public 
     */
    static function currentDate( $date, $monthRequired = true ) 
    {
        $config = CRM_Core_Config::singleton( );
        
        $d = CRM_Utils_Array::value( 'd', $date );
        $m = CRM_Utils_Array::value( 'M', $date );
        $y = CRM_Utils_Array::value( 'Y', $date );

        if ( ! $d && ! $m && ! $y ) {
            return true; 
        } 

        $day = $mon = 1; 
        $year = 0; 
        if ( $d ) $day  = $d;
        if ( $m ) $mon  = $m;
        if ( $y ) $year = $y;
 
        // if we have day we need mon, and if we have mon we need year 
        if ( ( $d && ! $m ) || 
             ( $d && ! $y ) || 
             ( $m && ! $y ) ) { 
            return false; 
        } 

        $result = false;
        if ( ! empty( $day ) || ! empty( $mon ) || ! empty( $year ) ) { 
            $result = checkdate( $mon, $day, $year ); 
        }

        if ( ! $result ) {
            return false;
        }

        // ensure we have month if required
        if ( $monthRequired && ! $m ) {
            return false;
        }

        // now make sure this date is greater that today
        $currentDate = getdate( );
        if ( $year > $currentDate['year'] ) {
            return true;
        } else if ( $year < $currentDate['year'] ) {
            return false;
        }

        if ( $m ) {
            if ( $mon > $currentDate['mon'] ) {
                return true;
            } else if ( $mon < $currentDate['mon'] ) {
                return false;
            }
        }

        if ( $d ) {
            if ( $day > $currentDate['mday'] ) {
                return true;
            } else if ( $day < $currentDate['mday'] ) {
                return false;
            }
        }

        return true;
    }

    /**
     * check the validity of a date or datetime (timestamp)
     * value which is in YYYYMMDD or YYYYMMDDHHMMSS format
     *
     * Uses PHP checkdate() - params are ( int $month, int $day, int $year )
     * @param string $date
     *
     * @return bool true if valid date
     * @static
     * @access public
     */
    static function mysqlDate($date)
    {
        // allow date to be null
        if ( $date == null ) {
            return true;
        }

        if (checkdate( substr($date, 4, 2), substr($date, 6, 2), substr($date, 0, 4) )) {
            return true;
        }
        
        return false;
    }
    
    static function integer($value) 
    {
        if ( is_int($value)) {
            return true;
        }
        
        if (($value < 0)) {
            $negValue = -1 * $value;
            if(is_int($negValue)) {
                return true;
            }
        }

        if (is_numeric($value) && preg_match('/^\d+$/', $value)) {
            return true;
        }

        return false;
    }

    static function positiveInteger($value) 
    {
        if ( is_int($value) ) {
            return ( $value < 0 ) ? false : true;
        }

        if (is_numeric($value) && preg_match('/^\d+$/', $value)) {
            return true;
        }
        
        return false;
    }
    
    static function numeric($value) 
    {
        return preg_match( '/(^-?\d\d*\.\d*$)|(^-?\d\d*$)|(^-?\.\d\d*$)/', $value ) ? true : false;
    }

    static function numberOfDigit($value, $noOfDigit) 
    {
        return preg_match( '/^\d{'.$noOfDigit.'}$/', $value ) ? true : false;
    }

    static function cleanMoney( $value ) {
        // first remove all white space
        $value = str_replace( array( ' ', "\t", "\n" ), '', $value );

        $config =& CRM_Core_Config::singleton( );

        if ( $config->monetaryThousandSeparator ) {
            $mon_thousands_sep = $config->monetaryThousandSeparator;
        } else {
            $mon_thousands_sep = ',';
        }

        // ugly fix for CRM-6391: do not drop the thousand separator if
        // it looks like it’s separating decimal part (because a given
        // value undergoes a second cleanMoney() call, for example)
        if ($mon_thousands_sep != '.' or substr($value, -3, 1) != '.') {
            $value = str_replace($mon_thousands_sep, '', $value);
        }

        if ( $config->monetaryDecimalPoint ) {
            $mon_decimal_point = $config->monetaryDecimalPoint;
        } else {
            $mon_decimal_point = '.';
        }
        $value = str_replace( $mon_decimal_point, '.', $value );

        return $value;
    }

    static function money($value) 
    {
        $config = CRM_Core_Config::singleton( );
        
        //only edge case when we have a decimal point in the input money
        //field and not defined in the decimal Point in config settings
        if ($config->monetaryDecimalPoint && 
            $config->monetaryDecimalPoint != '.' &&
            substr_count( $value, '.' ) ) {
            return false;
        }

        $value = self::cleanMoney( $value );

        if ( self::integer( $value ) ) {
            return true;
        }

        return preg_match( '/(^-?\d+\.\d?\d?$)|(^-?\.\d\d?$)/', $value ) ? true : false;
    }

    static function string($value, $maxLength = 0) 
    {
        if (is_string($value) &&
            ($maxLength === 0 || strlen($value) <= $maxLength)) {
            return true;
        }
        return false;
    }

    static function boolean($value) 
    {
        return preg_match( 
            '/(^(1|0)$)|(^(Y(es)?|N(o)?)$)|(^(T(rue)?|F(alse)?)$)/i', $value) ?
            true : false;
    }

    static function email($value, $checkDomain = false) 
    {
        static $qfRule = null;
        if ( ! isset( $qfRule ) ) {
            $qfRule = new HTML_QuickForm_Rule_Email();
        }
        return $qfRule->validate( $value, $checkDomain );
    }

    static function emailList( $list, $checkDomain = false ) 
    {
        $emails = explode( ',', $list );
        foreach ( $emails as $email ) {
            $email = trim( $email );
            if ( ! self::email( $email, $checkDomain ) ) {
                return false;
            }
        }
        return true;
    }

    // allow between 4-6 digits as postal code since india needs 6 and US needs 5 (or 
    // if u disregard the first 0, 4 (thanx excel!)
    // FIXME: we need to figure out how to localize such rules
    static function postalCode($value) 
    {
        if ( preg_match('/^\d{4,6}(-\d{4})?$/', $value) ) {
            return true;
        }
        return false;
    }

    /**
     * see how file rules are written in HTML/QuickForm/file.php
     * Checks to make sure the uploaded file is ascii
     *
     * @param     array     Uploaded file info (from $_FILES)
     * @access    private
     * @return    bool      true if file has been uploaded, false otherwise
     */
    static function asciiFile( $elementValue ) 
    {
        if ((isset($elementValue['error']) && $elementValue['error'] == 0) ||
            (!empty($elementValue['tmp_name']) && $elementValue['tmp_name'] != 'none')) {
            return CRM_Utils_File::isAscii($elementValue['tmp_name']);
        }
        return false;
    }

    /**
     * Checks to make sure the uploaded file is in UTF-8, recodes if it's not
     *
     * @param     array     Uploaded file info (from $_FILES)
     * @access    private
     * @return    bool      whether file has been uploaded properly and is now in UTF-8
     */
    static function utf8File( $elementValue ) 
    {
        $success = false;

        if ((isset($elementValue['error']) && $elementValue['error'] == 0) ||
            (!empty($elementValue['tmp_name']) && $elementValue['tmp_name'] != 'none')) {

            $success = CRM_Utils_File::isAscii($elementValue['tmp_name']);

            // if it's a file, but not UTF-8, let's try and recode it
            // and then make sure it's an UTF-8 file in the end
            if (!$success) {
                $success = CRM_Utils_File::toUtf8($elementValue['tmp_name']);
                if ($success) {
                    $success = CRM_Utils_File::isAscii($elementValue['tmp_name']);
                }
            }
        }
        return $success;
    }

    /**
     * see how file rules are written in HTML/QuickForm/file.php
     * Checks to make sure the uploaded file is html
     *
     * @param     array     Uploaded file info (from $_FILES)
     * @access    private
     * @return    bool      true if file has been uploaded, false otherwise
     */
    static function htmlFile( $elementValue ) 
    {
        if ((isset($elementValue['error']) && $elementValue['error'] == 0) ||
            (!empty($elementValue['tmp_name']) && $elementValue['tmp_name'] != 'none')) {
            return CRM_Utils_File::isHtmlFile($elementValue['tmp_name']);
        }
        return false;
    }

    /**
     * Check if there is a record with the same name in the db
     *
     * @param string $value     the value of the field we are checking
     * @param array  $options   the daoName and fieldName (optional )
     *
     * @return boolean     true if object exists
     * @access public
     * @static
     */
    static function objectExists( $value, $options ) 
    {
        $name = 'name';
        if ( isset($options[2]) ) {
            $name = $options[2];
        }
        
        return CRM_Core_DAO::objectExists( $value, $options[0], $options[1], CRM_Utils_Array::value( 2, $options, $name ) );
    }
    
    static function optionExists( $value, $options ) 
    {
        require_once 'CRM/Core/OptionValue.php';
        return CRM_Core_OptionValue::optionExists( $value, $options[0], $options[1], $options[2], CRM_Utils_Array::value( 3, $options, 'name' ) );
    }
    
    static function creditCardNumber( $value, $type ) 
    {
        require_once 'Validate/Finance/CreditCard.php';
        return Validate_Finance_CreditCard::number( $value, $type );
    }

    static function cvv( $value, $type ) 
    {
        require_once 'Validate/Finance/CreditCard.php';

        return Validate_Finance_CreditCard::cvv( $value, $type );
    }

    static function currencyCode($value) 
    {
        static $currencyCodes = null;
        if (!$currencyCodes) {
            $currencyCodes =& CRM_Core_PseudoConstant::currencyCode();
        }
        if (in_array($value, $currencyCodes)) {
            return true;
        }
        return false;
    }

    static function xssString( $value )
    {
        if ( is_string( $value ) ) {
            return preg_match( '!<(vb)?script[^>]*>.*</(vb)?script.*>!ims',
                               $value ) ? false : true;
        } else {
            return true;
        }
    }

    static function fileExists( $path ) {
        return file_exists( $path );
    }

    static function autocomplete( $value, $options )
    {
        if ( $value ) {            
            require_once 'CRM/Core/BAO/CustomOption.php';
            $selectOption =& CRM_Core_BAO_CustomOption::valuesByID( $options['fieldID'], $options['optionGroupID'] );
            
            if ( !in_array( $value, $selectOption ) ) {
                return false;
            }
        }
        return true;
    }
    
    static function validContact( $value, $actualElementValue = null )
    {
        if ( $actualElementValue ) {
            $value = $actualElementValue;
        }
        
        if ( $value && !is_numeric( $value ) ) {
            return false;
        }
        return true;
    }
    
    /**
     * check the validity of the date (in qf format)
     * note that only a year is valid, or a mon-year is
     * also valid in addition to day-mon-year
     *
     * @param array $date
     *
     * @return bool true if valid date
     * @static
     * @access public
     */
    static function qfDate( $date ) 
    {
        $config = CRM_Core_Config::singleton( );

        $d = CRM_Utils_Array::value( 'd', $date );
        $m = CRM_Utils_Array::value( 'M', $date );
        $y = CRM_Utils_Array::value( 'Y', $date );
        if ( isset( $date['h'] ) ||
            isset( $date['g'] ) ){
            $m = CRM_Utils_Array::value( 'M', $date );
        }

        if ( ! $d && ! $m && ! $y ) {
            return true; 
        } 
 
        $day = $mon = 1; 
        $year = 0;
        if ( $d ) $day  = $d;
        if ( $m ) $mon  = $m;
        if ( $y ) $year = $y;

        // if we have day we need mon, and if we have mon we need year 
        if ( ( $d && ! $m ) || 
             ( $d && ! $y ) || 
             ( $m && ! $y ) ) { 
            return false; 
        } 

        if ( ! empty( $day ) || ! empty( $mon ) || ! empty( $year ) ) {
            return checkdate( $mon, $day, $year );
        }
        return false;
    }

    static function qfKey( $key ) {
        require_once 'CRM/Core/Key.php';
        return ( $key ) ? CRM_Core_Key::valid( $key ) : false;
    }
}


