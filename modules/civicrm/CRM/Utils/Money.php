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
 * Money utilties
 */
class CRM_Utils_Money {
    static $_currencySymbols = null;

    /**
     * format a monetary string
     *
     * Format a monetary string basing on the amount provided,
     * ISO currency code provided and a format string consisting of:
     *
     * %a - the formatted amount
     * %C - the currency ISO code (e.g., 'USD') if provided
     * %c - the currency symbol (e.g., '$') if available
     *
     * @param float  $amount    the monetary amount to display (1234.56)
     * @param string $currency  the three-letter ISO currency code ('USD')
     * @param string $format    the desired currency format
     *
     * @return string  formatted monetary string
     *
     * @static
     */
    static function format($amount, $currency = null, $format = null, $onlyNumber = false )
    {

        if ( CRM_Utils_System::isNull( $amount ) ) {
            return '';
        }

        $config = CRM_Core_Config::singleton();

        if (!$format) {
            $format = $config->moneyformat;
        }

        // money_format() exists only in certain PHP install (CRM-650)
        if (is_numeric($amount) and function_exists('money_format')) {
            $amount = money_format($config->moneyvalueformat, $amount);
        }

        if ( $onlyNumber ) {
            return $amount;
        }
        
        if ( !self::$_currencySymbols ) {
            require_once "CRM/Core/PseudoConstant.php";
            $currencySymbolName = CRM_Core_PseudoConstant::currencySymbols( 'name' );
            $currencySymbol     = CRM_Core_PseudoConstant::currencySymbols( );
           
            self::$_currencySymbols =
                array_combine( $currencySymbolName, $currencySymbol );
        }

        if (!$currency) {
            $currency = $config->defaultCurrency;
        }

        if (!$format) {
            $format = $config->moneyformat;
        }

        setlocale(LC_MONETARY, 'en_US.utf8', 'en_US', 'en_US.utf8', 'en_US', 'C');
        // money_format() exists only in certain PHP install (CRM-650)
        if ( is_numeric($amount) &&
             function_exists('money_format') ) {
            $amount = money_format($config->moneyvalueformat, $amount);
        }
  
        $rep = array( ',' => $config->monetaryThousandSeparator,
                      '.' => $config->monetaryDecimalPoint );

        // If it contains tags, means that HTML was passed and the 
        // amount is already converted properly,
        // so don't mess with it again.
        if ( strip_tags($amount) === $amount ) {
            $money = strtr($amount, $rep);
        } else {
            $money = $amount;
        }


        $replacements = array(
                              '%a' => $money,
                              '%C' => $currency,
                              '%c' => CRM_Utils_Array::value($currency, self::$_currencySymbols, $currency),
                              );
        return strtr($format, $replacements);
    }

}


