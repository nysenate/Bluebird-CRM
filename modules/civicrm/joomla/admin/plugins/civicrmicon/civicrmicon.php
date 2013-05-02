<?php

/**
 * @copyright	Copyright (C) 2005 - 2011 CiviCRM LLC All rights reserved.
 * @license		GNU Affero General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * CiviCRM QuickIcon plugin
 */
class plgQuickiconCivicrmicon extends JPlugin {

  /**
   * Constructor
   *
   * @param       object  $subject The object to observe
   * @param       array   $config  An array that holds the plugin configuration
   *
   * @since       2.5
   */
  public function __construct(&$subject, $config) {
    parent::__construct($subject, $config);
    $this->loadLanguage();
  }

  /**
   * This method is called when the Quick Icons module is constructing its set
   * of icons. You can return an array which defines a single icon and it will
   * be rendered right after the stock Quick Icons.
   *
   * @param  $context  The calling context
   *
   * @return array A list of icon definition associative arrays, consisting of the
   *				 keys link, image, text and access.
   *
   * @since       2.5
   */
  public function onGetIcons($context) {
    jimport('joomla.environment.uri');
    return array(
      array(
        'link' => 'index.php?option=com_civicrm',
        'image' => JURI::base() . 'components/com_civicrm/civicrm/i/smallLogo.png',
        'text' => 'CiviCRM',
        'id' => 'plg_quickicon_civicrmicon',
      ));
  }
}

