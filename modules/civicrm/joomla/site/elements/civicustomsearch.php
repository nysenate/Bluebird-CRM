<?php
/*
   +--------------------------------------------------------------------+
   | CiviCRM version 4.2                                                |
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


// Retrieve list of CiviCRM profiles
// Active
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
class JFormFieldCivicustomsearch extends JFormField {

  /**
   * Element name
   *
   * @access	protected
   * @var		string
   */
  var $type = 'Civicustomsearch';

  protected function getInput() {
    $value = $this->value;
    $name = $this->name;

    // Initiate CiviCRM
    define('CIVICRM_SETTINGS_PATH', JPATH_ROOT . '/' . 'administrator/components/com_civicrm/civicrm.settings.php');
    require_once CIVICRM_SETTINGS_PATH;

    require_once 'CRM/Core/ClassLoader.php';
    CRM_Core_ClassLoader::singleton()->register();

    require_once 'CRM/Core/Config.php';
    $config = CRM_Core_Config::singleton();
    $query = "
SELECT value, description
FROM civicrm_option_value
WHERE option_group_id =24
";
    $dao = CRM_Core_DAO::executeQuery($query);
    while ($dao->fetch()) {
      $options[] = JHTML::_('select.option', $dao->value, $dao->description);
    }

    return JHTML::_('select.genericlist', $options, $name, NULL, 'value', 'text', $dao->value);
  }
}

