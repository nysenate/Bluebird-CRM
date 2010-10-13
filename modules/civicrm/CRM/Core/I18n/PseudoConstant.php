<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
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

class CRM_Core_I18n_PseudoConstant
{
    static function &languages()
    {
        static $languages = null;
        if ($languages === null) {
            $languages = array(
                'en_US' => 'English (USA)',
                'af_ZA' => 'Afrikaans',
                'ar_EG' => 'العربية',
                'bg_BG' => 'български',
                'ca_ES' => 'Català',
                'cs_CZ' => 'Česky',
                'da_DK' => 'dansk',
                'de_DE' => 'Deutsch',
                'et_EE' => 'Eesti',
                'el_GR' => 'Ελληνικά',
                'en_AU' => 'English (Australia)',
                'en_GB' => 'English (United Kingdom)',
                'es_ES' => 'español',
                'es_MX' => 'español (Mexico)',
                'fr_FR' => 'français',
                'fr_CA' => 'français (Canada)',
                'id_ID' => 'Bahasa Indonesia',
                'hi_IN' => 'हिन्दी',
                'it_IT' => 'Italiano',
                'he_IL' => 'עברית',
                'lt_LT' => 'Lietuvių',
                'hu_HU' => 'Magyar',
                'nl_NL' => 'Nederlands',
                'ja_JP' => '日本語',
                'no_NO' => 'Norsk',
                'km_KH' => 'ភាសាខ្មែរ',
                'pl_PL' => 'polski',
                'pt_PT' => 'Português',
                'pt_BR' => 'Português (Brasil)',
                'ro_RO' => 'română',
                'ru_RU' => 'русский',
                'sq_AL' => 'shqip',
                'sk_SK' => 'slovenčina',
                'sl_SI' => 'slovenščina',
                'fi_FI' => 'suomi',
                'sv_SE' => 'Svenska',
                'th_TH' => 'ไทย',
                'vi_VN' => 'Tiếng Việt',
                'te_IN' => 'తెలుగు',
                'tr_TR' => 'Türkçe',
                'zh_CN' => '中文 (简体)',
                'zh_TW' => '中文 (繁體)',
            );
        }
        return $languages;
    }
}
