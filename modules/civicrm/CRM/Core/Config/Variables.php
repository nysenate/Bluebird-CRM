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
 * Variables class contains definitions of all the core config settings that are allowed on 
 * CRM_Core_Config. If you want a config variable to be present in run time config object,
 * it need to be defined here first.
 * 
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

require_once 'CRM/Core/Config/Defaults.php';

class CRM_Core_Config_Variables extends CRM_Core_Config_Defaults
{


    /** 
     * the debug level for civicrm
     * @var int 
     */ 
    public $debug             = 0; 
    public $backtrace         = 0;

    /**
     * the directory where Smarty and plugins are installed
     * @var string
     */
    public $smartyDir           = null;
    public $pluginsDir          = null;

    /**
     * the root directory of our template tree
     * @var string
     */
    public $templateDir		  = null;

    /**
     * The resourceBase of our application. Used when we want to compose
     * url's for things like js/images/css
     * @var string
     */
    public $resourceBase        = null;

    /**
     * The directory to store uploaded files
     */
    public $uploadDir         = null;
    
    /**
     * The directory to store uploaded image files
     */
    public $imageUploadDir   = null;
    
    /**
     * The directory to store uploaded  files in custom data 
     */
    public $customFileUploadDir   = null;
    
    /**
     * The url that we can use to display the uploaded images
     */
    public $imageUploadURL   = null;

    /**
     * Are we generating clean url's and using mod_rewrite
     * @var string
     */
    public $cleanURL = false;

    /**
     * List of country codes limiting the country list.
     * 1228 is an id for United States.
     * @var string
     */
    public $countryLimit = array( '1228' );

    /**
     * List of country codes limiting the province list.
     * 1228 is an id for United States.     
     * @var string
     */
    public $provinceLimit = array( '1228' );

    /**
     * ISO code of default country for contact.
     * 1228 is an id for United States.     
     * @var int
     */
    public $defaultContactCountry = '1228';

    /**
     * ISO code of default currency.
     * @var int
     */
    public $defaultCurrency = 'USD';

    /**
     * Locale for the application to run with.
     * @var string
     */
    public $lcMessages = 'en_US';

    /**
     * String format for date+time
     * @var string
     */
    public $dateformatDatetime = '%B %E%f, %Y %l:%M %P';

    /**
     * String format for a full date (one with day, month and year)
     * @var string
     */
    public $dateformatFull = '%B %E%f, %Y';

    /**
     * String format for a partial date (one with month and year)
     * @var string
     */
    public $dateformatPartial = '%B %Y';

    /**
     * String format for a year-only date
     * @var string
     */
    public $dateformatYear = '%Y';

    /**
     * Display format for time
     * @var string
     */
    public $dateformatTime = '%l:%M %P';

    /**
     * Input format for time 
     * @var string
     */
    public $timeInputFormat = 1;

    /**
     * Input format for date plugin
     * @var string
     */
    public $dateInputFormat = 'mm/dd/yy';

    /**
     * Month and day on which fiscal year starts.
     *
     * @var array
     */
    public $fiscalYearStart = array(
                                    'M' => 01,
                                    'd' => 01
                                    );

    /**
     * String format for monetary amounts
     * @var string
     */
    public $moneyformat = '%c %a';

    /**
     * String format for monetary values
     * @var string
     */
    public $moneyvalueformat = '%!i';

    /**
     * Format for monetary amounts
     * @var string
     */
    public $currencySymbols = '';
    
    /**
     * Format for monetary amounts
     * @var string
     */
    public $defaultCurrencySymbol = '$';
   
    /**
     * Monetary decimal point character
     * @var string
     */
    public $monetaryDecimalPoint = '.';

    /**
     * Monetary thousands separator
     * @var string
     */
    public $monetaryThousandSeparator = ',';
    /**
     * Default encoding of strings returned by gettext
     * @var string
     */
    public $gettextCodeset = 'utf-8';


    /**
     * Default name for gettext domain.
     * @var string
     */
    public $gettextDomain = 'civicrm';

    /**
     * Default location of gettext resource files.
     */
    public $gettextResourceDir = './l10n/';

    /**
     * Default user framework
     */
    public $userFramework               = 'Drupal';
    public $userFrameworkVersion        = 6.3;
    public $userFrameworkUsersTableName = 'users';
    public $userFrameworkClass          = 'CRM_Utils_System_Drupal';
    public $userHookClass               = 'CRM_Utils_Hook_Drupal';
    public $userPermissionClass         = 'CRM_Core_Permission_Drupal';
    public $userFrameworkURLVar         = 'q';
    public $userFrameworkDSN            = null;
    public $userFrameworkBaseURL        = null;
    public $userFrameworkResourceURL    = null;
    public $userFrameworkFrontend       = false;
    public $userFrameworkLogging        = false;

    /**
     * the handle for import file size 
     * @var int
     */
    public $maxImportFileSize = 1048576;
    public $maxAttachments    = 3;
    public $maxFileSize       = 2;

    /**
     * The custom locale strings. Note that these locale strings are stored
     * in a separate column in civicrm_domain
     * @var array
     */
    public $localeCustomStrings = null;

    /**
     * Map Provider 
     *
     * @var boolean
     */
    public $mapProvider = null;

    /**
     * Map API Key 
     *
     * @var boolean
     */
    public $mapAPIKey = null;
    
    /**
     * How should we get geo code information if google map support needed
     *
     * @var boolean
     */
    public $geocodeMethod    = '';
    
    /**
     *
     * 
     * @var boolean
     */
    public $mapGeoCoding = 1;
    
    /**
     * Whether deleted contacts should be moved to trash instead
     * @var boolean
     */
    public $contactUndelete = true;

    /**
     * Whether database-level logging should be performed
     * @var boolean
     */
    public $logging = false;

    /**
     * Whether CiviCRM should check for newer versions
     *
     * @var boolean
     */
    public $versionCheck = true;

    /**
     * Array of enabled add-on components (e.g. CiviContribute, CiviMail...)
     *
     * @var array
     */
    public $enableComponents   = array( 'CiviContribute','CiviPledge','CiviMember',
                                        'CiviEvent', 'CiviMail', 'CiviReport' );
    public $enableComponentIDs = array( 1, 6, 2, 3, 4, 8 );

    /**
     * Should payments be accepted only via SSL?
     *
     * @var boolean
     */
    public $enableSSL = false;

    /**
     * error template to use for fatal errors
     *
     * @var string
     */
    public $fatalErrorTemplate = 'CRM/common/fatal.tpl';

    /**
     * fatal error handler
     *
     * @var string
     */
    public $fatalErrorHandler = null;

    /**
     * legacy encoding for file encoding conversion
     *
     * @var string
     */
    public $legacyEncoding = 'Windows-1252';

    /**
     * field separator for import/export csv file
     *
     * @var string
     */
    public $fieldSeparator = ',';

    /**
     * max location blocks in address
     *
     * @var integer
     */
    public $maxLocationBlocks        = 2;

    /**
     * the font path where captcha fonts are stored
     *
     * @var string
     */
    public $captchaFontPath = '/usr/X11R6/lib/X11/fonts/';

    /**
     * the font to use for captcha
     *
     * @var string
     */
    public $captchaFont = 'HelveticaBold.ttf';

    /**
     * Some search settings
     */
    public $includeWildCardInName  = 1;
    public $includeEmailInName     = 1;
    public $includeNickNameInName  = 0;
    public $smartGroupCacheTimeout = 0;

    public $defaultSearchProfileID = null;
    
    /**
     * Dashboard timeout
     */
    public $dashboardCacheTimeout = 1440;    
    
    /**
     * flag to indicate if acl cache is NOT to be reset 
     */
    public $doNotResetCache       = 0;    

    /**
     * Optimization related variables
     */
    public $includeAlphabeticalPager = 1;
    public $includeOrderByClause     = 1;
    public $oldInputStyle            = 1;

    /**
     * should we disbable key generation for forms
     *
     * @var boolean
     */
    public $formKeyDisable = false;

    /**
     * to determine wether the call is from cms or civicrm 
     */
    public $inCiviCRM  = false;

    /**
     * component registry object (of CRM_Core_Component type)
     */
    public $componentRegistry  = null;


    /**
     * Provide addressSequence
     *
     * @param
     * @return string
     */
    public function addressSequence( ) {
        require_once 'CRM/Core/BAO/Preferences.php';
        return CRM_Core_BAO_Preferences::value( 'address_sequence' );
    }

    /**
     * Provide cached default currency symbol
     *
     * @param
     * @return string
     */
    public function defaultCurrencySymbol( $defaultCurrency = null ) {
        static $cachedSymbol = null;
        if ( ! $cachedSymbol || $defaultCurrency ) {
            if ( $this->defaultCurrency || $defaultCurrency ) {
                require_once "CRM/Core/PseudoConstant.php";
                $currencySymbolName = CRM_Core_PseudoConstant::currencySymbols( 'name' );
                $currencySymbol     = CRM_Core_PseudoConstant::currencySymbols( );
                
                $this->currencySymbols = array_combine( $currencySymbolName, $currencySymbol );
                $currency     = $defaultCurrency ? $defaultCurrency : $this->defaultCurrency;
                $cachedSymbol = CRM_Utils_Array::value( $currency, $this->currencySymbols, '');
            } else {
                $cachedSymbol = '$';
            }
        }
        return $cachedSymbol;
    }

    /**
     * Provide cached default currency symbol
     *
     * @param
     * @return string
     */
    public function defaultContactCountry( ) {
        static $cachedContactCountry = null;
        if ( ! $cachedContactCountry ) {
            $countryIsoCodes = CRM_Core_PseudoConstant::countryIsoCode( );
            $cachedContactCountry = $countryIsoCodes[$this->defaultContactCountry];
        }
        return $cachedContactCountry;
    }

    /**
     * Provide cached default country name
     *
     * @param
     * @return string
     */
    public function defaultContactCountryName( ) {
        static $cachedContactCountryName = null;
        if ( ! $cachedContactCountryName ) {
            $countryCodes = CRM_Core_PseudoConstant::country( );
            $cachedContactCountryName = $countryCodes[$this->defaultContactCountry];
        }
        return $cachedContactCountryName;
    }

    /**
     * Provide cached country limit translated to names
     *
     * @param
     * @return array
     */
    public function countryLimit( ) {
        static $cachedCountryLimit = null;
        if ( ! $cachedCountryLimit ) {
            $countryIsoCodes = CRM_Core_PseudoConstant::countryIsoCode( );
            $country = array();
            if ( is_array( $this->countryLimit ) ) {
                foreach( $this->countryLimit as $val ) {
                    $country[] = $countryIsoCodes[$val]; 
                }
            } else {
                $country[] = $countryIsoCodes[$this->countryLimit];
            }
            $cachedCountryLimit = $country;
        }
        return $cachedCountryLimit;
    }

    /**
     * Provide cached province limit translated to names
     *
     * @param
     * @return array
     */
    public function provinceLimit( ) {
        static $cachedProvinceLimit = null;
        if ( ! $cachedProvinceLimit ) {
            $countryIsoCodes = CRM_Core_PseudoConstant::countryIsoCode( );
            $country = array();
            if ( is_array( $this->provinceLimit ) ) {
                foreach( $this->provinceLimit as $val ) {
                    $country[] = $countryIsoCodes[$val]; 
                }
            } else {
                $country[] = $countryIsoCodes[$this->provinceLimit];
            }
            $cachedProvinceLimit = $country;
        }
        return $cachedProvinceLimit;
    }

} // end CRM_Core_Config


