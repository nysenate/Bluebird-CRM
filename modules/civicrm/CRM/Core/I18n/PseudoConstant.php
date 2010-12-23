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

require_once 'CRM/Core/OptionValue.php';

class CRM_Core_I18n_PseudoConstant
{
    static function &languages()
    {
        static $languages = null;
        if ($languages === null) {
            $rows = array();
            CRM_Core_OptionValue::getValues(array('name' => 'languages'), $rows, 'weight', true);

            $languages = array();
            foreach ($rows as $row) {
                $languages[$row['name']] = $row['label'];
            }
        }
        return $languages;
    }

    static function longForShort($short)
    {
        $longForShortMapping =& self::longForShortMapping();
        return $longForShortMapping[$short];
    }

    static function &longForShortMapping()
    {
        static $longForShortMapping = null;
        if ($longForShortMapping === null) {
            $rows = array();
            CRM_Core_OptionValue::getValues(array('name' => 'languages'), $rows);

            $longForShortMapping = array();
            foreach ($rows as $row) {
                $longForShortMapping[$row['value']] = $row['name'];
            }
            // hand-crafted enforced overrides for language variants
            $longForShortMapping['zh'] = 'zh_CN';
            $longForShortMapping['en'] = 'en_US';
            $longForShortMapping['fr'] = 'fr_FR';
            $longForShortMapping['pt'] = 'pt_PT';
            $longForShortMapping['es'] = 'es_ES';
        }
        return $longForShortMapping;
    }

    static function shortForLong($long)
    {
        return substr($long, 0, 2);
    }
}
