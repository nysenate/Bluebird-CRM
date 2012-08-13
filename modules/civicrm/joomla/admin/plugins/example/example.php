<?php

/**
 * @version
 * @package		Civicrm
 * @subpackage	Joomla Plugin
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');
class plgCiviCRMHookExample extends JPlugin {

  /**
   * Example Civicrm Plugin. For additional examples of hook functions, check
   * the wiki docs
   * http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+hook+specification
   *
   * @package		Civicrm
   * @subpackage	Joomla plugins
   * @since		1.5
   */

  public function civicrm_summary($contactID, &$content, &$contentPlacement = CRM_Utils_Hook::SUMMARY_BELOW) {
    $contentPlacement = $this->params->def('placement');
    $content = "
<table>
   <tr><th>Hook Data</th></tr>
   <tr><td>Data 1</td></tr>
   <tr><td>Data 2</td></tr>
</table>
";
  }

  public function civicrm_tabs(&$tabs, $contactID) {
    // unset the contribution tab, i.e. remove it from the page
    unset($tabs[1]);

    // let's add a new "contribution" tab with a different name and put it last
    // this is just a demo, in the real world, you would create a url which would
    // return an html snippet etc.
    $url = CRM_Utils_System::url('civicrm/contact/view/contribution',
      "reset=1&snippet=1&force=1&cid=$contactID"
    );
    $tabs[] = array(
      'id' => 'mySupercoolTab',
      'url' => $url,
      'title' => 'Contribution Tab Renamed',
      'weight' => 300,
    );
  }
}

