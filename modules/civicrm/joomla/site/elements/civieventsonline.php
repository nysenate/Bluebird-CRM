<?php
// Retrieve list of CiviCRM events
// Active, current or future, online

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
class JFormFieldCiviEventsOnline extends JFormField {

  /**
   * Element name
   *
   * @access	protected
   * @var		string
   */
  var $type = 'CiviEventsOnline';

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

    $params = array(
      'version' => '3',
      'is_online_registration' => 1,
      'is_active' => 1,
      'isCurrent' => 1,
      'return.title' => 1,
      'return.id' => 1,
      'return.end_date' => 1,
      'return.start_date' => 1,
    );
    $events      = civicrm_api('event', 'get', $params);
    $currentdate = date("Y-m-d H:i:s");
    $options     = array();
    $options[]   = JHTML::_('select.option', '', JText::_('- Select Event -'));
    foreach ($events['values'] as $event) {
      if (strtotime($event['start_date']) >= strtotime($currentdate) ||
        strtotime($event['end_date']) >= strtotime($currentdate)
      ) {
        $options[] = JHTML::_('select.option', $event['id'], $event['event_title']);
      }
    }
    return JHTML::_('select.genericlist', $options, $name, NULL, 'value', 'text', $value);
  }
}


