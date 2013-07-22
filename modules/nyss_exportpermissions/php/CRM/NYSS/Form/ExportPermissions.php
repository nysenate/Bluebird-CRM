<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
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
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

require_once 'CRM/Core/Form.php';

/**
 * This class generates form components for DedupeRules
 *
 */
class CRM_NYSS_Form_ExportPermissions extends CRM_Core_Form
{

  /**
   * Function to pre processing
   *
   * @return None
   * @access public
   */
  function preProcess() {
  }//preProcess

  /**
   * Function to build the form
   *
   * @return None
   * @access public
   */
  public
  function buildQuickForm() {
    $this->addButtons(
      array(
        array(
          'type' => 'next',
          'name' => ts('CiviCRM Permissions'),
          'isDefault' => TRUE,
        ),
        array(
          'type' => 'submit',
          'name' => ts('All Permissions'),
        ),
        array(
          'type' => 'cancel',
          'name' => ts('Cancel')
        ),
      )
    );
  }

  public function postProcess() {
    $bbcfg = get_bluebird_instance_config();
    $dDB = $bbcfg['db.drupal.prefix'].$bbcfg['db.basename'];
    $rolePerms = $emptySet = array();

    $btnName = $this->controller->getButtonName();
    //CRM_Core_Error::debug('$btnName',$btnName);exit();

    //cols are roles, thus array keys
    $sql = "
      SELECT *
      FROM {$dDB}.role
      WHERE name NOT IN ('anonymous user', 'authenticated user', 'Superuser')
      ORDER BY name
    ";
    $roles = CRM_Core_DAO::executeQuery($sql);

    //build header
    $headers = array(
      'Module',
      'Permission',
    );
    $emptySet = array(
      '',
      '',
    );
    while ( $roles->fetch() ) {
      $headers[$roles->rid] = $roles->name;
      $emptySet[$roles->rid] = '';
    }

    //get distinct list of all perms so we can cycle through
    //if civicrm, get only civicrm + nyss
    $additionalWhere = '';
    if ( $btnName == '_qf_ExportPermissions_next' ) {
      $additionalWhere = "
        AND module IN ('civicrm', 'nyss_civihooks')
      ";
    }

    $sql = "
      SELECT *
      FROM {$dDB}.role_permission
      WHERE module != ''
      {$additionalWhere}
      GROUP BY module, permission
      ORDER BY module, permission
    ";
    $ap = CRM_Core_DAO::executeQuery($sql);

    while ( $ap->fetch() ) {
      $allPerms[$ap->module][] = $ap->permission;
    }
    //CRM_Core_Error::debug('$allPerms',$allPerms);

    foreach ( $allPerms as $module => $mPerms ) {
      foreach ( $mPerms as $mPerm ) {
        //initialize row so we have all cols
        $row = $emptySet;
        $row[0] = $module;
        $row[1] = $mPerm;

        //rows are perms
        $sql = "
          SELECT *
          FROM {$dDB}.role_permission
          WHERE module = '{$module}'
            AND permission = '{$mPerm}'
            AND rid NOT IN (1, 2, 3)
          ORDER BY permission
        ";
        $perms = CRM_Core_DAO::executeQuery($sql);

        //build rows
        while ( $perms->fetch() ) {
          $row[$perms->rid] = 'X';
        }

        $rolePerms[] = $row;
      }
    }
    //CRM_Core_Error::debug('$rolePerms',$rolePerms);

    CRM_Core_Report_Excel::writeCSVFile('RolePermissions', $headers, $rolePerms);
    CRM_Utils_System::civiExit();
  }//postProcess
}
