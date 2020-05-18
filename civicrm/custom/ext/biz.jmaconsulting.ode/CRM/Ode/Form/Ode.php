<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2016                                |
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
 * @copyright JMA Consulting (c) 2015-2016
 * $Id$
 *
 */
class CRM_Ode_Form_Ode extends CRM_Admin_Form_Setting {

  public function buildQuickForm() {
    checkValidEmails();
    $this->addYesNo('ode_from_allowed', ts('Whitelist FROM email addresses?'), NULL);

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts("Save"),
        'isDefault' => TRUE,
      ),
      array(
        'type' => 'cancel',
        'name' => ts("Cancel"),
      ),
    ));
    parent::buildQuickForm();
  }

  public function setDefaultValues() {
    $defaults['ode_from_allowed'] = ode_get_settings_value();
    return $defaults;
  }

  public function postProcess() {
    $params = $this->controller->exportValues($this->_name);
    ode_set_settings_value(CRM_Utils_Array::value('ode_from_allowed', $params));
    CRM_Core_Session::setStatus(ts('ODE Settings has been saved.'), ts('Saved'), 'success');
  }

}
