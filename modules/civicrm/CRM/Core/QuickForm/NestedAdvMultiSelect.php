<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
 +--------------------------------------------------------------------+
 | Copyright U.S. PIRG Education Fund (c) 2007                        |
 | Licensed to CiviCRM under the Academic Free License version 3.0.   |
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
 * @copyright U.S. PIRG Education Fund 2007
 * $Id$
 *
 */

require_once 'HTML/QuickForm/advmultiselect.php';

class CRM_Core_QuickForm_NestedAdvMultiSelect extends HTML_QuickForm_advmultiselect {
    
    function load( &$options, $param1 = null, $param2 = null,
        $param3 = null, $param4 = null ) {
        switch ( true ) {
            case ( $options instanceof Iterator ):
                $arr = array( );
                foreach ( $options as $key => $val ) {
                    $arr[$key] = $val;
                }
                return $this->loadArray( $arr, $param1 );
                break;
            default:
                return parent::load( $options, $param1, $param2, $param3, $param4 );
        }
    }
}

