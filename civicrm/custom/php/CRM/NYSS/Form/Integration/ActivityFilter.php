<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
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
 * @copyright CiviCRM LLC (c) 2004-2013
 * $Id$
 *
 */

/**
 * This class generates form components for Activity Filter
 *
 */
class CRM_NYSS_Form_Integration_ActivityFilter extends CRM_Core_Form {
  public function buildQuickForm() {
    // add activity search filter
    $activityOptions = array(
      'Bill' => 'Bill',
      'Issue' => 'Issue',
      'Committee' => 'Committee',
      'Direct Message' => 'Direct Message',
      'Context Message' => 'Context Message',
      'Survey' => 'Survey',
      'Petition' => 'Petition',
      'Account' => 'Account',
      'Profile' => 'Profile',
    );

    $this->add('select', 'web_activity_type_filter', ts('Filter by'), array('' => ts('- all activity type(s) -')) + $activityOptions);
    $this->assign('suppressForm', TRUE);
  }

  function setDefaultValues() {
    // CRM-11761 retrieve user's activity filter preferences
    $defaults = array();
    $session = CRM_Core_Session::singleton();
    $userID = $session->get('userID');
    if ($userID) {
      $defaults = Civi::service('settings_manager')
        ->getBagByContact(NULL, $userID)
        ->get('web_activity_filter');
    }
    //CRM_Core_Error::debug_var('setDefaultValues', $defaults);
    return $defaults;
  }
}

