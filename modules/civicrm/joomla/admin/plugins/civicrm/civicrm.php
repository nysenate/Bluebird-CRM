<?php
/**
 * @copyright  Copyright (C) 2005 - 2011 CiviCRM LLC All rights reserved.
 * @license    GNU Affero General Public License version 2 or later
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * CiviCRM User Management Plugin
 *
 * @package     Joomla
 * @subpackage  JFramework
 * @since       1.6
 */
class plgUserCivicrm extends JPlugin
{

  /* resetNavigation after user is saved
   * Method is called after user data is stored in the database
   *
   * @param  array    $user     Holds the new user data.
   * @param  boolean  $isnew    True if a new user is stored.
   * @param  boolean  $success  True if user was succesfully stored in the database.
   * @param  string   $msg      Message.
   *
   * @return  void
   * @since   1.6
   * @throws  Exception on error.
   */
  function onUserAfterSave($user, $isnew, $success, $msg) {
    $app = JFactory::getApplication();
    self::civicrmResetNavigation();
  }

  /* resetNavigation after group is saved (parent/child may impact acl)
   * Method is called after group is stored in the database
   *
   * @var     string  The event to trigger after saving the data.
   *
   * @return  void
   * @since   1.6
   * @throws  Exception on error.
   */
  function onUserAfterSaveGroup($var) {
    $app = JFactory::getApplication();
    self::civicrmResetNavigation();
  }

  /* delete uf_match record after user is deleted
   * Method is called after user is deleted from the database
   *
   * @param  array    $user     Holds the user data.
   * @param  boolean  $success  True if user was succesfully removed from the database.
   * @param  string   $msg      Message.
   *
   * @return  void
   * @since   1.6
   * @throws  Exception on error.
   */
  function onUserAfterDelete($user, $succes, $msg) {
    $app = JFactory::getApplication();

    // Instantiate CiviCRM
    require_once JPATH_ROOT.'/administrator/components/com_civicrm/civicrm.settings.php';
    require_once 'CRM/Core/Config.php';
    $config = CRM_Core_Config::singleton();

    // Delete UFMatch
    CRM_Core_BAO_UFMatch::deleteUser($user['id']);
  }

  /* trigger navigation reset when the user logs in (admin only)
   *
   * @user     Joomla user object
   * @options  array of options to pass
   *
   * @return   void
   * @since    1.6
   */
  public function onUserLogin($user, $options = array()) {
    $app  = JFactory::getApplication();
    if ( $app->isAdmin() ) {
      $jUser =& JFactory::getUser();
      $jId = $jUser->get('id');
      self::civicrmResetNavigation( $jId );
    }
  }

  /**
   * Reset CiviCRM user/contact navigation cache
   *
   * @param $jId - the logged in joomla ID if it exists
   *
   * @return void
   */
  public function civicrmResetNavigation($jId = null) {
    // Instantiate CiviCRM
    if (!class_exists('CRM_Core_Config')) {
      require_once JPATH_ROOT.'/administrator/components/com_civicrm/civicrm.settings.php';
      require_once 'CRM/Core/Config.php';
    }

    $config = CRM_Core_Config::singleton();

    $cId = NULL;

    //retrieve civicrm contact ID if joomla user ID is provided
    if ( $jId ) {
      $params = array(
        'version' => 3,
        'uf_id'   => $jId,
        'return'  => 'contact_id',
      );
      $cId = civicrm_api('uf_match', 'getvalue', $params);
    }

    // Reset Navigation
    CRM_Core_BAO_Navigation::resetNavigation($cId);
  }

}
