<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
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
 * @copyright CiviCRM LLC (c) 2004-2012
 * $Id$
 *
 */

/**
 * This class generates form components generic to CiviCRM settings
 *
 */
class CRM_Admin_Form_Setting extends CRM_Core_Form {

  protected $_defaults;

  /**
   * This function sets the default values for the form.
   * default values are retrieved from the database
   *
   * @access public
   *
   * @return None
   */ function setDefaultValues() {
    if (!$this->_defaults) {
      $this->_defaults = array();
      $formArray       = array('Component', 'Localization');
      $formMode        = FALSE;
      if (in_array($this->_name, $formArray)) {
        $formMode = TRUE;
      }

      CRM_Core_BAO_ConfigSetting::retrieve($this->_defaults);

      CRM_Core_Config_Defaults::setValues($this->_defaults, $formMode);

      $list = array_flip(CRM_Core_OptionGroup::values('contact_autocomplete_options',
          FALSE, FALSE, TRUE, NULL, 'name'
        ));

      $cRlist = array_flip(CRM_Core_OptionGroup::values('contact_reference_options',
          FALSE, FALSE, TRUE, NULL, 'name'
        ));

      $listEnabled = CRM_Core_BAO_Setting::valueOptions(CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
        'contact_autocomplete_options'
      );
      $cRlistEnabled = CRM_Core_BAO_Setting::valueOptions(CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
        'contact_reference_options'
      );

      $autoSearchFields = array();
      if (!empty($list) && !empty($listEnabled)) {
        $autoSearchFields = array_combine($list, $listEnabled);
      }

      $cRSearchFields = array();
      if (!empty($cRlist) && !empty($cRlistEnabled)) {
        $cRSearchFields = array_combine($cRlist, $cRlistEnabled);
      }

      //Set defaults for autocomplete and contact reference options
      $this->_defaults['autocompleteContactSearch'] = array(
        '1' => 1) + $autoSearchFields;
      $this->_defaults['autocompleteContactReference'] = array(
        '1' => 1) + $cRSearchFields;
      $this->_defaults['enableSSL'] = CRM_Core_BAO_Setting::getItem(CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME, 'enableSSL', NULL, 0);
      $this->_defaults['verifySSL'] = CRM_Core_BAO_Setting::getItem(CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME, 'verifySSL', NULL, 1);

      $sql = "
SELECT time_format
FROM   civicrm_preferences_date
WHERE  time_format IS NOT NULL
AND    time_format <> ''
LIMIT  1
";
      $this->_defaults['timeInputFormat'] = CRM_Core_DAO::singleValueQuery($sql);
    }

    return $this->_defaults;
  }

  /**
   * Function to actually build the form
   *
   * @return None
   * @access public
   */
  public function buildQuickForm() {
    $session = CRM_Core_Session::singleton();
    $session->pushUserContext(CRM_Utils_System::url('civicrm/admin', 'reset=1'));
    $args = func_get_args();
    $check = reset($args);
    $this->addButtons(array(
        array(
          'type' => 'next',
          'name' => ts('Save'),
          'isDefault' => TRUE,
        ),
        array(
          'type' => 'cancel',
          'name' => ts('Cancel'),
        ),
      )
    );
  }

  /**
   * Function to process the form
   *
   * @access public
   *
   * @return None
   */
  public function postProcess() {
    // store the submitted values in an array
    $params = $this->controller->exportValues($this->_name);

    self::commonProcess($params);
  }

  public function commonProcess(&$params) {

    // save autocomplete search options
    if (CRM_Utils_Array::value('autocompleteContactSearch', $params)) {
      $value = CRM_Core_DAO::VALUE_SEPARATOR . implode(CRM_Core_DAO::VALUE_SEPARATOR,
        array_keys($params['autocompleteContactSearch'])
      ) . CRM_Core_DAO::VALUE_SEPARATOR;

      CRM_Core_BAO_Setting::setItem($value,
        CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
        'contact_autocomplete_options'
      );

      unset($params['autocompleteContactSearch']);
    }

    // save autocomplete contact reference options
    if (CRM_Utils_Array::value('autocompleteContactReference', $params)) {
      $value = CRM_Core_DAO::VALUE_SEPARATOR . implode(CRM_Core_DAO::VALUE_SEPARATOR,
        array_keys($params['autocompleteContactReference'])
      ) . CRM_Core_DAO::VALUE_SEPARATOR;

      CRM_Core_BAO_Setting::setItem($value,
        CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
        'contact_reference_options'
      );

      unset($params['autocompleteContactReference']);
    }

    // save checksum timeout
    if (CRM_Utils_Array::value('checksumTimeout', $params)) {
      CRM_Core_BAO_Setting::setItem($params['checksumTimeout'],
        CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
        'checksum_timeout'
      );
    }

    // update time for date formats when global time is changed
    if (CRM_Utils_Array::value('timeInputFormat', $params)) {
      $query = "
UPDATE civicrm_preferences_date
SET    time_format = %1
WHERE  time_format IS NOT NULL
AND    time_format <> ''
";
      $sqlParams = array(1 => array($params['timeInputFormat'], 'String'));
      CRM_Core_DAO::executeQuery($query, $sqlParams);

      unset($params['timeInputFormat']);
    }

    // verify ssl peer option
    if (isset($params['verifySSL'])) {
      CRM_Core_BAO_Setting::setItem($params['verifySSL'],
        CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
        'verifySSL'
      );
      unset($params['verifySSL']);
    }

    // force secure URLs
    if (isset($params['enableSSL'])) {
      CRM_Core_BAO_Setting::setItem($params['enableSSL'],
        CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
        'enableSSL'
      );
      unset($params['enableSSL']);
    }

    CRM_Core_BAO_ConfigSetting::add($params);

    // also delete the CRM_Core_Config key from the database
    $cache = CRM_Utils_Cache::singleton();
    $cache->delete('CRM_Core_Config');

    CRM_Core_Session::setStatus(ts('Your changes have been saved.'));
  }

  public function rebuildMenu() {
    // ensure config is set with new values
    $config = CRM_Core_Config::singleton(TRUE, TRUE);

    // rebuild menu items
    CRM_Core_Menu::store();

    // also delete the IDS file so we can write a new correct one on next load
    $configFile = $config->uploadDir . 'Config.IDS.ini';
    @unlink($configFile);
  }
}

