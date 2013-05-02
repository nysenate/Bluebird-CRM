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
 * Page for displaying Administer CiviCRM Control Panel
 */
class CRM_Admin_Page_Admin extends CRM_Core_Page {
  function run() {
    // ensure that all CiviCRM tables are InnoDB, else abort
    // this is not a very fast operation, so we do it randomly 10% of the times
    // but we do it for most / all tables
    // http://bugs.mysql.com/bug.php?id=43664
    if (rand(1, 10) == 3 &&
      CRM_Core_DAO::isDBMyISAM(150)
    ) {
      $errorMessage = 'Your database is configured to use the MyISAM database engine. CiviCRM  requires InnoDB. You will need to convert any MyISAM tables in your database to InnoDB. Using MyISAM tables will result in data integrity issues.';
      CRM_Core_Session::setStatus($errorMessage);
    }

    if (!CRM_Utils_System::isDBVersionValid($errorMessage)) {
      CRM_Core_Session::setStatus($errorMessage);
    }

    $groups = array('Customize Data and Screens' => ts('Customize Data and Screens'),
      'Communications' => ts('Communications'),
      'Localization' => ts('Localization'),
      'Users and Permissions' => ts('Users and Permissions'),
      'System Settings' => ts('System Settings'),
    );

    $config = CRM_Core_Config::singleton();
    if (in_array('CiviContribute', $config->enableComponents)) {
      $groups['CiviContribute'] = ts('CiviContribute');
    }

    if (in_array('CiviMember', $config->enableComponents)) {
      $groups['CiviMember'] = ts('CiviMember');
    }

    if (in_array('CiviEvent', $config->enableComponents)) {
      $groups['CiviEvent'] = ts('CiviEvent');
    }

    if (in_array('CiviMail', $config->enableComponents)) {
      $groups['CiviMail'] = ts('CiviMail');
    }

    if (in_array('CiviCase', $config->enableComponents)) {
      $groups['CiviCase'] = ts('CiviCase');
    }

    if (in_array('CiviReport', $config->enableComponents)) {
      $groups['CiviReport'] = ts('CiviReport');
    }

    if (in_array('CiviCampaign', $config->enableComponents)) {
      $groups['CiviCampaign'] = ts('CiviCampaign');
    }

    $values = CRM_Core_Menu::getAdminLinks();

    $this->_showHide = new CRM_Core_ShowHideBlocks();
    foreach ($groups as $group => $title) {
      $this->_showHide->addShow("id_{$group}_show");
      $this->_showHide->addHide("id_{$group}");
      $v = CRM_Core_ShowHideBlocks::links($this, $group, '', '', FALSE);
      if (isset($values[$group])) {
        $adminPanel[$group] = $values[$group];
        $adminPanel[$group]['show'] = $v['show'];
        $adminPanel[$group]['hide'] = $v['hide'];
        $adminPanel[$group]['title'] = $title;
      } else {
        $adminPanel[$group] = array();
        $adminPanel[$group]['show'] = '';
        $adminPanel[$group]['hide'] = '';
        $adminPanel[$group]['title'] = $title;
      }
    }
    $versionCheck = CRM_Utils_VersionCheck::singleton();
    $this->assign('newVersion', $versionCheck->newerVersion());
    $this->assign('localVersion', $versionCheck->localVersion);
    $this->assign('adminPanel', $adminPanel);
    $this->_showHide->addToTemplate();
    return parent::run();
  }
}

