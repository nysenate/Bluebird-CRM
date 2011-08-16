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
 * File for the CiviCRM APIv3 group functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Survey
 * @copyright CiviCRM LLC (c) 2004-2011
 */

require_once 'CRM/Campaign/BAO/Survey.php';
require_once 'api/v3/utils.php';

/**
 * create/update survey
 *
 * This API is used to create new survey or update any of the existing
 * In case of updating existing survey, id of that particular survey must
 * be in $params array. 
 *
 * @param array $params  (referance) Associative array of property
 *                       name/value pairs to insert in new 'survey'
 *
 * @return array   survey array
 *
 * @access public
 */
function civicrm_api3_survey_create( $params )
{
    civicrm_api3_verify_mandatory($params,null,array('title'));
    return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);

}

/**
 * Returns array of surveys  matching a set of one or more group properties
 *
 * @param array $params  (referance) Array of one or more valid
 *                       property_name=>value pairs. If $params is set
 *                       as null, all surveys will be returned
 *
 * @return array  (referance) Array of matching surveys
 * @access public
 */
function civicrm_api3_survey_get( $params )
{
    civicrm_api3_verify_mandatory($params);
    return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);

}

/**
 * delete an existing survey
 *
 * This method is used to delete any existing survey. id of the group
 * to be deleted is required field in $params array
 *
 * @param array $params  (reference) array containing id of the group
 *                       to be deleted
 *
 * @return array  (referance) returns flag true if successfull, error
 *                message otherwise
 *
 * @access public
 */
function civicrm_api3_survey_delete( $params )
{
    return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}
