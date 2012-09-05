<?php

// No direct access.
defined('_JEXEC') or die;

/**
 * Joomla! master extension plugin.
 *
 * @package    Civicrmsys.Plugin
 * @subpackage  System.Civicrmsys
 * @since    1.6
 */
class plgSystemCivicrmsys extends JPlugin
{
  public $scheduled;

  /**
   * After extension source code has been installed
   *
   * @param  JInstaller  Installer object
   * @param  int      Extension Identifier
   */
  public function onExtensionBeforeInstall()
  {
    // called by "Upload Package" use-case
    $this->scheduleCivicrmRebuild();
  }

  /**
   * After extension source code has been installed
   *
   * @param  JInstaller  Installer object
   * @param  int      Extension Identifier
   */
  public function onExtensionAfterInstall($installer, $eid)
  {
    if ($installer->extension instanceof JTableExtension && $installer->extension->folder == 'civicrm') {
      //x $args = func_get_args(); dump($args, 'onExtensionAfterInstall');
      $this->scheduleCivicrmRebuild();
    }
  }

  /**
   * After extension source code has been updated(?)
   *
   * @param  JInstaller  Installer object
   * @param  int      Extension identifier
   */
  public function onExtensionAfterUpdate($installer, $eid)
  {
    // TODO test //if ($installer->extension instanceof JTableExtension && $installer->extension->folder == 'civicrm') {
      $this->scheduleCivicrmRebuild();
    //}
  }

  /**
   * After extension configuration has been saved
   */
  public function onExtensionAfterSave($type, $ext)
  {
    // Called by "Manage Plugins" use-case -- per-plugin forms
    if ($type == 'com_plugins.plugin' && $ext->folder == 'civicrm') {
      $this->scheduleCivicrmRebuild();
    }
  }

  public function onContentCleanCache($defaultgroup, $cachebase) {
    // Called by "Manage Plugins" use-case -- both bulk operations and per-plugin forms
    if ($defaultgroup == 'com_plugins') {
      $this->scheduleCivicrmRebuild();
    }
  }

  /**
   * After extension source code has been removed
   *
   * @param  JInstaller  Installer object
   * @param  int      Extension identifier
   */
  public function onExtensionAfterUninstall($installer, $eid, $result)
  {
    $this->scheduleCivicrmRebuild();
  }

  /**
   * Ensure that the rebuild will be done
   */
  public function scheduleCivicrmRebuild() {
    if ($this->scheduled) {
      return;
    }
    register_shutdown_function(array($this, 'doCivicrmRebuild'));
    // dump(TRUE, 'scheduled');
    $this->scheduled = TRUE;
  }

  /**
   * Perform the actual rebuild
   */
  public function doCivicrmRebuild() {
    // dump($this, 'doCivicrmRebuild');
    $this->bootstrap();
    CRM_Core_Invoke::rebuildMenuAndCaches(TRUE);
  }

  /**
   * Make sure that CiviCRM is loaded
   */
  protected function bootstrap() {
    if (defined('CIVICRM_UF')) {
      // already loaded settings
      return;
    }

    $app = JFactory::getApplication(); // copied from example -- but why?

    define('CIVICRM_SETTINGS_PATH', JPATH_ROOT . '/' . 'administrator/components/com_civicrm/civicrm.settings.php');
    require_once CIVICRM_SETTINGS_PATH;

    require_once 'CRM/Core/ClassLoader.php';
    CRM_Core_ClassLoader::singleton()->register();

    require_once 'CRM/Core/Config.php';
    $civiConfig = CRM_Core_Config::singleton();
  }
}
