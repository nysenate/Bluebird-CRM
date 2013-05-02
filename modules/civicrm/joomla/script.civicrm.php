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
class Com_CiviCRMInstallerScript {
  function install($parent) {
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'configure.php';

    global $civicrmUpgrade;

    $script          = 'index.php';
    $liveSite        = substr_replace(JURI::root(), '', -1, 1);
    $configTaskUrl   = $liveSite . "/administrator/?option=com_civicrm&task=civicrm/admin/configtask&reset=1";
    $upgradeUrl      = $liveSite . "/administrator/?option=com_civicrm&task=civicrm/upgrade&reset=1";
    $registerSiteURL = "http://civicrm.org/civicrm/profile/create?reset=1&gid=15";

    require_once 'CRM/Utils/System.php';
    require_once 'CRM/Utils/Array.php';
    if ($civicrmUpgrade) {
      $docLink = CRM_Utils_System::docURL2('Installation and Upgrades', TRUE, 'Upgrade Guide',NULL,NULL,"wiki");
      // UPGRADE successful status and links
      $content = '
  <center>
  <table width="100%" border="0">
    <tr>
        <td>
            <strong>CiviCRM component files have been UPGRADED <font color="green">succesfully</font></strong>.
            <p><strong>Please run the <a href="' . $upgradeUrl . '">CiviCRM Database Upgrade Utility</a> now. This utility will check your database and perform any needed upgrades.</strong></p>
            <p>Also review the <a href="' . $docLink . '">Upgrade Guide</a> for any additional steps required to complete this upgrade.</p>
        </td>
    </tr>
  </table>
  </center>';
    }
    else {
      $docLink  = CRM_Utils_System::docURL2('Installation and Upgrades', FALSE, 'Installation Guide',NULL,NULL,"wiki");
      $frontEnd = CRM_Utils_System::docURL2('Configuring Front-end Profile Listings and Forms in Joomla! Sites', FALSE, 'Create front-end forms and searchable directories using Profiles',NULL,NULL,"wiki");
      $contri   = CRM_Utils_System::docURL2('Displaying Online Contribution Pages in Joomla! Frontend Sites', FALSE, 'Create online contribution pages',NULL,NULL,"wiki");
      $event    = CRM_Utils_System::docURL2('Configuring Front-end Event Info and Registration in Joomla! Sites', FALSE, 'Create events with online event registration',NULL,NULL,"wiki");

      // INSTALL successful status and links
      $content = '
  <center>
  <table width="100%" border="0">
    <tr>
        <td>
            <strong>CiviCRM component files and database tables have been INSTALLED <font color="green">succesfully</font></strong>.
            <p><strong>Please review the ' . $docLink . ' for any additional steps required to complete the installation.</strong></p>
            <p><strong>Then use the <a href="' . $configTaskUrl . '">Configuration Checklist</a> to review and configure CiviCRM settings for your new site.</strong></p>
            <p><strong>Additional Resources:</strong>
                <ul>
                    <li>' . $frontEnd . '</li>
                    <li>' . $contri . '</li>
                    <li>' . $event . '</li>
                </ul>
            </p>
           <p><strong>We have integrated KCFinder with CKEditor and TinyMCE, which enables user to upload images. Note that all the images uploaded using KCFinder will be public.</strong>
            </p>
           <p><strong>Have you registered this site at CiviCRM.org? If not, please help strengthen the CiviCRM ecosystem by taking a few minutes to <a href="' . $registerSiteURL . '" target="_blank">fill out the site registration form</a>. The information collected will help us prioritize improvements, target our communications and build the community. If you have a technical role for this site, be sure to check Keep in Touch to receive technical updates (a low volume  mailing list).</strong></p>
        </td>
    </tr>
  </table>
  </center>';
    }

    //install and enable plugins
    $manifest  = $parent->get("manifest");
    $parent    = $parent->getParent();
    $source    = $parent->getPath("source");
    $installer = new JInstaller();
    $plgArray  = array();

    foreach ($manifest->plugins->plugin as $plugin) {
      $attributes = $plugin->attributes();
      $plg = $source . DS . $attributes['folder'] . DS . $attributes['plugin'];
      $installer->install($plg);
      $plgArray[] = "'" . $attributes['plugin'] . "'";
    }

    $db              = JFactory::getDbo();
    $tableExtensions = $db->nameQuote("#__extensions");
    $columnElement   = $db->nameQuote("element");
    $columnType      = $db->nameQuote("type");
    $columnEnabled   = $db->nameQuote("enabled");
    $plgList         = implode(',', $plgArray);

    // Enable plugins
    $db->setQuery("UPDATE $tableExtensions
                        SET $columnEnabled = 1
                        WHERE $columnElement IN ($plgList)
                        AND $columnType = 'plugin'"
    );
    $db->query();

    echo $content;
  }

  function uninstall($parent) {
    $uninstall = FALSE;
    // makes it easier if folks want to really uninstall
    if ($uninstall) {
      define('CIVICRM_SETTINGS_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'civicrm.settings.php');
      require_once CIVICRM_SETTINGS_PATH;

      require_once 'CRM/Core/Config.php';
      $config = CRM_Core_Config::singleton();

      require_once 'CRM/Core/DAO.php';
      CRM_Core_DAO::dropAllTables();

      echo "You have uninstalled CiviCRM. All CiviCRM related tables have been dropped from the database.";
    }
    else {
      echo "You have uninstalled CiviCRM.";
    }
  }

  function update($parent) {
    $this->install($parent);
  }

  function preflight($type, $parent) {}

  function postflight($type, $parent) {
    // set the default permissions
    // only on new install
    // CRM-9418
    global $civicrmUpgrade;

    if (!$civicrmUpgrade) {
      $this->setDefaultPermissions();
    }
  }

  function setDefaultPermissions() {
    // get the current perms from the assets table and
    // only set if its empty
    $db = JFactory::getDbo();
    $db->setQuery('SELECT rules FROM #__assets WHERE name = ' . $db->quote('com_civicrm'));
    $assetRules = json_decode((string ) $db->loadResult());


    if (count($assetRules) > 1) {
      return;
    }

    $rules = new stdClass;

    $permissions = array(
      'Public' =>
      array(
        'access CiviMail subscribe/unsubscribe pages',
        'access all custom data',
        'access uploaded files',
        'make online contributions',
        'profile listings and forms',
        'register for events',
        'view event info',
        'view event participants',
      ),
      'Registered' =>
      array(
        'access CiviMail subscribe/unsubscribe pages',
        'access all custom data',
        'access uploaded files',
        'make online contributions',
        'profile listings and forms',
        'register for events',
        'view event info',
        'view event participants',
      ),
    );

    require_once 'CRM/Utils/String.php';

    $newPerms = array();
    foreach ($permissions as $group => $perms) {

      // get user group ID
      $userGroupID = $this->getJoomlaUserGroupID($group);
      if (empty($userGroupID)) {
        // since we cant resolve this, we move on
        continue;
      }


      foreach ($perms as $perm) {
        $permString = 'civicrm.' . CRM_Utils_String::munge(strtolower($perm));
        if (!array_key_exists($permString, $newPerms)) {
          $newPerms[$permString] = array();
        }
        $newPerms[$permString][] = $userGroupID;
      }
    }

    if (empty($newPerms)) {
      return;
    }

    // now merge the two newPerms and rules
    foreach ($newPerms as $perm => $groups) {
      if (empty($rules->$perm)) {
        $rulesArray = array();
      }
      else {
        $rulesArray = (array ) $rules->$perm;
      }

      foreach ($groups as $group) {
        $present = FALSE;
        foreach ($rulesArray as $key => $val) {
          if ((int ) $key == $group) {
            $present = TRUE;
            break;
          }
        }
        if (!$present) {
          $rulesArray[(string ) $group] = 1;
        }
      }

      $rules->$perm = (object ) $rulesArray;
    }

    $rulesString = json_encode($rules);
    $db->setQuery('UPDATE #__assets SET rules = ' .
      $db->quote($rulesString) .
      ' WHERE name = ' .
      $db->quote('com_civicrm')
    );
    if (!$db->query()) {
      echo 'Seems like setting default actions failed<p>';
    }
  }

  function getJoomlaUserGroupID($title) {
    $db = JFactory::getDbo();
    $db->setQuery('SELECT id FROM #__usergroups where title = ' . $db->quote($title));
    return (int) $db->loadResult();
  }
}

