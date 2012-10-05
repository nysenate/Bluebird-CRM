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
 * This is a part of CiviCRM extension management functionality.
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2012
 * $Id$
 *
 */

/**
 * This page displays the list of extensions registered in the system.
 */
class CRM_Admin_Page_Extensions extends CRM_Core_Page_Basic {

  /**
   * The action links that we need to display for the browse screen
   *
   * @var array
   * @static
   */
  static $_links = NULL;

  static $_extensions = NULL;

  /**
   * Obtains the group name from url and sets the title.
   *
   * @return void
   * @access public
   *
   */
  function preProcess() {
    $ext = new CRM_Core_Extensions();
    if ($ext->enabled === TRUE) {
      self::$_extensions = $ext->getExtensions();
    }
    CRM_Utils_System::setTitle(ts('CiviCRM Extensions'));
        $destination = CRM_Utils_System::url( 'civicrm/admin/extensions',
                                              'reset=1' );
        
        $destination = urlencode( $destination );
        $this->assign( 'destination', $destination );
  }

  /**
   * Get BAO Name
   *
   * @return string Classname of BAO.
   */
  function getBAOName() {
    return 'CRM_Core_BAO_Extension';
  }

  /**
   * Get action Links
   *
   * @return array (reference) of action links
   */
  function &links() {
    if (!(self::$_links)) {
      self::$_links = array(
        CRM_Core_Action::ADD => array(
          'name' => ts('Install'),
          'url' => 'civicrm/admin/extensions',
          'qs' => 'action=add&id=%%id%%&key=%%key%%',
          'title' => ts('Install'),
        ),
        CRM_Core_Action::ENABLE => array(
          'name' => ts('Enable'),
          'url' => 'civicrm/admin/extensions',
          'qs' => 'action=enable&id=%%id%%&key=%%key%%',
          'ref' => 'enable-action',
          'title' => ts('Enable'),
        ),
        CRM_Core_Action::DISABLE => array(
          'name' => ts('Disable'),
          'url' => 'civicrm/admin/extensions',
          'qs' => 'action=disable&id=%%id%%&key=%%key%%',
          'ref' => 'disable-action',
          'title' => ts('Disable'),
        ),
        CRM_Core_Action::DELETE => array(
          'name' => ts('Uninstall'),
          'url' => 'civicrm/admin/extensions',
          'qs' => 'action=delete&id=%%id%%&key=%%key%%',
          'title' => ts('Uninstall Extension'),
        ),
        CRM_Core_Action::UPDATE => array(
          'name' => ts('Upgrade'),
          'url' => 'civicrm/admin/extensions',
          'qs' => 'action=update&id=%%id%%&key=%%key%%',
          'title' => ts('Upgrade Extension'),
        ),
      );
    }
    return self::$_links;
  }

  /**
   * Run the basic page (run essentially starts execution for that page).
   *
   * @return void
   */
  function run() {
    $this->preProcess();
    return parent::run();
  }

  /**
   * Browse all options
   *
   *
   * @return void
   * @access public
   * @static
   */
  function browse() {

    $this->assign('extEnabled', FALSE);
    if (self::$_extensions !== NULL) {
      $this->assign('extEnabled', TRUE);
    }
    else {
      return;
    }

    $this->assign('extDbUpgrades', CRM_Core_Extensions_Upgrades::hasPending());
    $this->assign('extDbUpgradeUrl', CRM_Utils_System::url('civicrm/admin/extensions/upgrade', 'reset=1'));

    $extensionRows = array();
    $em = self::$_extensions;

    $fid = 1;
    foreach ($em as $key => $obj) {

      // for extensions which aren't installed, create a
      // dummy/placeholder id
      if (isset($obj->id)) {
        $id = $obj->id;
      }
      else {
        $id = 'x'. $fid++;
      }

      $extensionRows[$id] = (array) $obj;

      // assign actions
      if ($obj->status == CRM_Core_Extensions_Extension::STATUS_INSTALLED || $obj->status == CRM_Core_Extensions_Extension::STATUS_MISSING) {
        if ($obj->is_active) {
          $action = CRM_Core_Action::DISABLE;
          if ($obj->upgradable) {
            $action += CRM_Core_Action::UPDATE;
          }
        }
        else {
          $action = array_sum(array_keys($this->links()));
          $action -= CRM_Core_Action::DISABLE;
          $action -= CRM_Core_Action::ADD;
          if (!$obj->upgradable) {
            $action -= CRM_Core_Action::UPDATE;
          }
          if ($obj->status == CRM_Core_Extensions_Extension::STATUS_MISSING) {
            // do not allow Enable for a MISSING status extension
            $action -= CRM_Core_Action::ENABLE;
          }
        }
        $extensionRows[$id]['action'] = CRM_Core_Action::formLink(self::links(),
          $action,
          array(
            'id' => $id,
            'key' => $obj->key,
          )
        );
      }
      else {
        $action = array_sum(array_keys($this->links()));
        $action -= CRM_Core_Action::DISABLE;
        $action -= CRM_Core_Action::ENABLE;
        $action -= CRM_Core_Action::DELETE;
        $action -= CRM_Core_Action::UPDATE;
        $extensionRows[$id]['action'] = CRM_Core_Action::formLink(self::links(),
          $action,
          array(
            'id' => $id,
            'key' => $obj->key,
          )
        );
      }
    }

    $this->assign('extensionRows', $extensionRows);
  }

  /**
   * Get name of edit form
   *
   * @return string Classname of edit form.
   */
  function editForm() {
    return 'CRM_Admin_Form_Extensions';
  }

  /**
   * Get edit form name
   *
   * @return string name of this page.
   */
  function editName() {
    return 'CRM_Admin_Form_Extensions';
  }

  /**
   * Get user context.
   *
   * @return string user context.
   */
  function userContext($mode = NULL) {
    return 'civicrm/admin/extensions';
  }

  /**
   * function to get userContext params
   *
   * @param int $mode mode that we are in
   *
   * @return string
   * @access public
   */
  function userContextParams($mode = NULL) {
    return 'reset=1&action=browse';
  }
}

