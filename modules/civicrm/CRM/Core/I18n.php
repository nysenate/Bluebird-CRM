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

require_once 'PHPgettext/streams.php';
require_once 'PHPgettext/gettext.php';
require_once 'CRM/Core/Config.php';

class CRM_Core_I18n
{
    /**
     * A PHP-gettext instance for string translation; should stay null if the strings are not to be translated (en_US).
     */
    private $_phpgettext = null;

    /**
     * A locale-based constructor that shouldn't be called from outside of this class (use singleton() instead).
     *
     * @param  $locale string  the base of this certain object's existence
     * @return         void
     */
    function __construct($locale)
    {
        if ($locale != '' and $locale != 'en_US') {
            $config = CRM_Core_Config::singleton();
            $streamer = new FileReader(implode(DIRECTORY_SEPARATOR, array($config->gettextResourceDir, $locale, 'civicrm.mo')));
            $this->_phpgettext = new gettext_reader($streamer);
        }
    }

    /**
     * Return languages available in this instance of CiviCRM.
     *
     * @param $justEnabled boolean  whether to return all languages or just the enabled ones
     * @return             array    of code/language name mappings
     */
    static function languages($justEnabled = false)
    {
        static $all     = null;
        static $enabled = null;

        if (!$all) {
            require_once 'CRM/Core/I18n/PseudoConstant.php';
            $all = CRM_Core_I18n_PseudoConstant::languages();

            // check which ones are available; add them to $all if not there already
            $config = CRM_Core_Config::singleton();
            $codes = array();
            if (is_dir($config->gettextResourceDir)) {
                $dir = opendir($config->gettextResourceDir);
                while ($filename = readdir($dir)) {
                    if (preg_match('/^[a-z][a-z]_[A-Z][A-Z]$/', $filename)) {
                        $codes[] = $filename;
                        if (!isset($all[$filename])) $all[$filename] = $filename;
                    }
                }
                closedir($dir);
            }

            // drop the unavailable languages (except en_US)
            foreach (array_keys($all) as $code) {
                if ($code == 'en_US') continue;
                if (!in_array($code, $codes)) unset($all[$code]);
            }
        }

        if ($enabled === null) {
            $config = CRM_Core_Config::singleton();
            $enabled = array();
            if (isset($config->languageLimit) and $config->languageLimit) {
                foreach ($all as $code => $name) {
                    if (in_array($code, array_keys($config->languageLimit))) $enabled[$code] = $name;
                }
            }
        }

        return $justEnabled ? $enabled : $all;
    }

    /**
     * Replace arguments in a string with their values. Arguments are represented by % followed by their number.
     *
     * @param  $str string  source string
     * @param       mixed   arguments, can be passed in an array or through single variables
     * @return      string  modified string
     */
    function strarg($str)
    {
        $tr = array();
        $p = 0;
        for ($i = 1; $i < func_num_args(); $i++) {
            $arg = func_get_arg($i);
            if (is_array($arg)) {
                foreach ($arg as $aarg) {
                    $tr['%'.++$p] = $aarg;
                }
            } else {
                $tr['%'.++$p] = $arg;
            }
        }
        return strtr($str, $tr);
    }

    /**
     * Smarty block function, provides gettext support for smarty.
     *
     * The block content is the text that should be translated.
     *
     * Any parameter that is sent to the function will be represented as %n in the translation text,
     * where n is 1 for the first parameter. The following parameters are reserved:
     *   - escape - sets escape mode:
     *       - 'html' for HTML escaping, this is the default.
     *       - 'js' for javascript escaping.
     *       - 'no'/'off'/0 - turns off escaping
     *   - plural - The plural version of the text (2nd parameter of ngettext())
     *   - count - The item count for plural mode (3rd parameter of ngettext())
     *   - context - gettext context of that string (for homonym handling)
     *
     * @param $text   string  the original string
     * @param $params array   the params of the translation (if any)
     * @return        string  the translated string
     */
    function crm_translate($text, $params = array())
    {
        if (isset($params['escape'])) {
            $escape = $params['escape'];
            unset($params['escape']);
        }

        // sometimes we need to {ts}-tag a string, but don’t want to
        // translate it in the template (like civicrm_navigation.tpl),
        // because we handle the translation in a different way (CRM-6998)
        // in such cases we return early, only doing SQL/JS escaping
        if (isset($params['skip']) and $params['skip']) {
            if (isset($escape) and ($escape == 'sql')) $text = mysql_escape_string($text);
            if (isset($escape) and ($escape == 'js'))  $text = addcslashes($text, "'");
            return $text;
        }

        if (isset($params['plural'])) {
            $plural = $params['plural'];
            unset($params['plural']);
            if (isset($params['count'])) {
                $count = $params['count'];
            }
        }

        if (isset($params['context'])) {
            $context = $params['context'];
            unset($params['context']);
        } else {
            $context = null;
        }

        // do all wildcard translations first
        require_once 'CRM/Utils/Array.php';
        $config =& CRM_Core_Config::singleton( );
        $stringTable = CRM_Utils_Array::value( $config->lcMessages,
                                               $config->localeCustomStrings );
        
        $exactMatch = false;
        if ( isset( $stringTable['enabled']['exactMatch'] ) ) {
            foreach ( $stringTable['enabled']['exactMatch'] as $search => $replace ) {
                if ( $search === $text ) {
                    $exactMatch = true;
                    $text = $replace;
                    break;
                }
            }
        }
        
        if ( ! $exactMatch &&
             isset( $stringTable['enabled']['wildcardMatch'] ) ) {
            $search  = array_keys  ( $stringTable['enabled']['wildcardMatch'] );
            $replace = array_values( $stringTable['enabled']['wildcardMatch'] );
            $text = str_replace( $search,
                                 $replace,
                                 $text );
        }

        // dont translate if we've done exactMatch already
        if ( ! $exactMatch ) {
            // use plural if required parameters are set
            if (isset($count) && isset($plural)) {
                
                if ($this->_phpgettext) {
                    $text = $this->_phpgettext->ngettext($text, $plural, $count);
                } else {
                    // if the locale's not set, we do ngettext work by hand
                    // if $count == 1 then $text = $text, else $text = $plural
                    if ($count != 1) $text = $plural;
                }
                
                // expand %count in translated string to $count
                $text = strtr($text, array('%count' => $count));
                
                // if not plural, but the locale's set, translate
            } elseif ($this->_phpgettext) {
                if ($context) {
                    $text = $this->_phpgettext->pgettext($context, $text);
                } else {
                    $text = $this->_phpgettext->translate($text);
                }
            }
        }

        // replace the numbered %1, %2, etc. params if present
        if (count($params)) {
            $text = $this->strarg($text, $params);
        }

        // escape SQL if we were asked for it
        if (isset($escape) and ($escape == 'sql')) $text = mysql_escape_string($text);

        // escape for JavaScript (if requested)
        if (isset($escape) and ($escape == 'js'))  $text = addcslashes($text, "'");

        return $text;
    }

    /**
     * Translate a string to the current locale.
     *
     * @param  $string string  this string should be translated
     * @return         string  the translated string
     */
    function translate($string)
    {
        return ($this->_phpgettext) ? $this->_phpgettext->translate($string) : $string;
    }

    /**
     * Localize (destructively) array values.
     *
     * @param  $array array  the array for localization (in place)
     * @param  $params array an array of additional parameters
     * @return        void
     */
    function localizeArray(&$array, $params = array())
    {
        foreach ($array as &$value) {
            if ($value) $value = ts($value, $params);
        }
    }

    /**
     * Localize (destructively) array elements with keys of 'title'.
     *
     * @param  $array array  the array for localization (in place)
     * @return        void
     */
    function localizeTitles(&$array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->localizeTitles($value);
                $array[$key] = $value;
            } elseif ((string ) $key == 'title') {
                $array[$key] = ts($value);
            }
        }
    }

    /**
     * Static instance provider - return the instance for the current locale.
     */
    static function &singleton()
    {
        static $singleton = array();

        global $tsLocale;
        if (!isset($singleton[$tsLocale])) {
            $singleton[$tsLocale] = new CRM_Core_I18n($tsLocale);
        }

        return $singleton[$tsLocale];
    }

    /**
     * Set the LC_TIME locale if it's not set already (for a given language choice).
     *
     * @return string  the final LC_TIME that got set
     */
    static function setLcTime()
    {
        static $locales = array();

        global $tsLocale;
        if (!isset($locales[$tsLocale])) {
            // with the config being set to pl_PL: try pl_PL.UTF-8,
            // then pl_PL, if neither present fall back to C
            $locales[$tsLocale] = setlocale(LC_TIME, $tsLocale . '.UTF-8', $tsLocale, 'C');
        }

        return $locales[$tsLocale];
    }

}

/**
 * Short-named function for string translation, defined in global scope so it's available everywhere.
 * @param  $text   string  string for translating
 * @param  $params array   an array of additional parameters
 * @return         string  the translated string
 */
function ts($text, $params = array())
{
    static $config   = null;
    static $locale   = null;
    static $i18n     = null;
    static $function = null;

    if ($text == '') {
        return '';
    }

    if (!$config) {
        $config = CRM_Core_Config::singleton();
    }

    global $tsLocale;
    if (!$i18n or $locale != $tsLocale) {
        $i18n =& CRM_Core_I18n::singleton();
        $locale = $tsLocale;
        if (isset($config->customTranslateFunction) and function_exists($config->customTranslateFunction)) {
            $function = $config->customTranslateFunction;
        }
    }

    if ($function) {
        return $function($text, $params);
    } else {
        return $i18n->crm_translate($text, $params);
    }
}
