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
 * File for CiviCRM APIv3 pseudoconstants
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Constant
 * @copyright CiviCRM LLC (c) 2004-2011
 * @version $Id: Constant.php 30171 2010-10-14 09:11:27Z mover $
 *
 */

/**
 * Include utility functions
 */
require_once 'api/v3/utils.php';

/**
 * Generic file to retrieve all the constants and
 * pseudo constants used in CiviCRM
 *
 *  @param  string  Name of a public static method of
 *                  CRM_Core_PseudoContant: one of
 *  <ul>
 *    <li>activityStatus</li>
 *    <li>activityType</li>
 *    <li>addressee</li>
 *    <li>allGroup</li>
 *    <li>country</li>
 *    <li>countryIsoCode</li>
 *    <li>county</li>
 *    <li>currencyCode</li>
 *    <li>currencySymbols</li>
 *    <li>customGroup</li>
 *    <li>emailGreeting</li>
 *    <li>fromEmailAddress</li>
 *    <li>gender</li>
 *    <li>group</li>
 *    <li>groupIterator</li>
 *    <li>honor</li>
 *    <li>IMProvider</li>
 *    <li>individualPrefix</li>
 *    <li>individualSuffix</li>
 *    <li>locationType</li>
 *    <li>locationVcardName</li>
 *    <li>mailProtocol</li>
 *    <li>mappingTypes</li>
 *    <li>paymentProcessor</li>
 *    <li>paymentProcessorType</li>
 *    <li>pcm</li>
 *    <li>phoneType</li>
 *    <li>postalGreeting</li>
 *    <li>priority</li>
 *    <li>relationshipType</li>
 *    <li>stateProvince</li>
 *    <li>stateProvinceAbbreviation</li>
 *    <li>stateProvinceForCountry</li>
 *    <li>staticGroup</li>
 *    <li>tag</li>
 *    <li>tasks</li>
 *    <li>ufGroup</li>
 *    <li>visibility</li>
 *    <li>worldRegion</li>
 *    <li>wysiwygEditor</li>
 *  </ul>
 */
function civicrm_api3_constant_get($params)
{
 
    civicrm_api3_verify_mandatory ($params,null,array ('name'));
    $name= $params ['name'];
    require_once 'CRM/Core/PseudoConstant.php';
    $className = 'CRM_Core_PseudoConstant';
    $callable  = "$className::$name";
    if (is_callable($callable)) {
      if (empty($params)) {
        $values = call_user_func( array( $className, $name ) );
      } else {
        $values = call_user_func( array( $className, $name ) );
        //@TODO XAV take out the param the COOKIE, Entity, Action and so there are only the "real param" in it
        //$values = call_user_func_array( array( $className, $name ), $params );
      }
      return civicrm_api3_create_success($values,$params);
    }

    return civicrm_api3_create_error('Unknown civicrm constant or method not callable');

}



function civicrm_api3_constant_getfields($params) {

  return civicrm_api3_create_success (array ('name' => array('options' =>
   'activityStatus',
   'activityType',
   'addressee',
   'allGroup',
   'country',
   'countryIsoCode',
   'county',
   'currencyCode',
   'currencySymbols',
   'customGroup',
   'emailGreeting',
   'fromEmailAddress',
   'gender',
   'group',
   'groupIterator',
   'honor',
   'IMProvider',
   'individualPrefix',
   'individualSuffix',
   'locationType',
   'locationVcardName',
   'mailProtocol',
   'mappingTypes',
   'paymentProcessor',
   'paymentProcessorType',
   'pcm',
   'phoneType',
   'postalGreeting',
   'priority',
   'relationshipType',
   'stateProvince',
   'stateProvinceAbbreviation',
   'stateProvinceForCountry',
   'staticGroup',
   'tag',
   'tasks',
   'ufGroup',
   'visibility',
   'worldRegion',
   'wysiwygEditor')),
   $params);
} 

