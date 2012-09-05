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
 * This class facilitates the loading of secondary, per-page resources
 * such as JavaScript files and CSS files.
 *
 * TODO: This is currently a thin wrapper over CRM_Core_Region. We
 * should incorporte services for aggregation, minimization, etc.
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2012
 * $Id$
 *
 */
class CRM_Core_Resources {
  const DEFAULT_WEIGHT = 0;
  const DEFAULT_REGION = 'page-footer';

  /**
   * We don't have a container or dependency-injection, so use singleton instead
   *
   * @var object
   * @static
   */
  private static $_singleton = NULL;

  /**
   * @var array(string => string) Map extension names to their base URLs. Note:
   *  - The $extMap['*'] is a grandparent-URL for unknown extension dirs
   *  - URLs should end with a trailing '/'
   */
  private $extMap = NULL;

  /**
   * Get or set the single instance of CRM_Core_Resources
   *
   * @param $instance CRM_Core_Resources, new copy of the manager
   * @return CRM_Core_Resources
   */
  static public function singleton(CRM_Core_Resources $instance = NULL) {
    if ($instance !== NULL) {
      self::$_singleton = $instance;
    }
    if (self::$_singleton === NULL) {
      $config = CRM_Core_Config::singleton();
      $extMap = array();
      $extMap['civicrm'] = $config->userFrameworkResourceURL;
      if (!empty($config->extensionsURL)) {
        $extMap['*'] = rtrim($config->extensionsURL, '/') .'/';
      }
      self::$_singleton = new CRM_Core_Resources($extMap);
    }
    return self::$_singleton;
  }

  /**
   * Construct a resource manager
   *
   * @var $extMap array(extensionName => url) Map extension names to their base URLs. Note:
   *  - The $extMap['*'] is a grandparent-URL for unknown extension dirs
   *  - URLs should end with a trailing '/'
   */
  public function __construct($extMap) {
    $this->extMap = $extMap;
  }

  /**
   * Add a JavaScript file to the current page using <SCRIPT SRC>.
   *
   * @param $ext string, extension name; use 'civicrm' for core
   * @param $file string, file path -- relative to the extension base dir
   * @param $weight int, relative weight within a given region
   * @param $region string, location within the file; 'html-header', 'page-header', 'page-footer'
   * @return CRM_Core_Resources
   */
  public function addScriptFile($ext, $file, $weight = self::DEFAULT_WEIGHT, $region = self::DEFAULT_REGION) {
    return $this->addScriptUrl($this->getUrl($ext, $file), $weight, $region);
  }

  /**
   * Add a JavaScript file to the current page using <SCRIPT SRC>.
   *
   * @param $url string
   * @param $weight int, relative weight within a given region
   * @param $region string, location within the file; 'html-header', 'page-header', 'page-footer'
   * @return CRM_Core_Resources
   */
  public function addScriptUrl($url, $weight = self::DEFAULT_WEIGHT, $region = self::DEFAULT_REGION) {
    $config = CRM_Core_Config::singleton();
    if ($region == 'html-header' && is_callable(array($config->userSystem, 'addHtmlHeadScriptUrl'))) {
      $config->userSystem->addHtmlHeadScriptUrl($url, $weight);
    } else {
      CRM_Core_Region::instance($region)->add(array(
        'name' => $url,
        'type' => 'scriptUrl',
        'scriptUrl' => $url,
        'weight' => $weight,
        'region' => $region,
      ));
    }
    return $this;
  }

  /**
   * Add a JavaScript file to the current page using <SCRIPT SRC>.
   *
   * @param $code string, JavaScript source code
   * @param $weight int, relative weight within a given region
   * @param $region string, location within the file; 'html-header', 'page-header', 'page-footer'
   * @return CRM_Core_Resources
   */
  public function addScript($code, $weight = self::DEFAULT_WEIGHT, $region = self::DEFAULT_REGION) {
    $config = CRM_Core_Config::singleton();
    if ($region == 'html-header' && is_callable(array($config->userSystem, 'addHtmlHeadScriptCode'))) {
      $config->userSystem->addHtmlHeadScriptCode($code, $weight);
    } else {
      CRM_Core_Region::instance($region)->add(array(
        // 'name' => automatic
        'type' => 'script',
        'script' => $code,
        'weight' => $weight,
        'region' => $region,
      ));
    }
    return $this;
  }

  /**
   * Add a CSS file to the current page using <LINK HREF>.
   *
   * @param $ext string, extension name; use 'civicrm' for core
   * @param $file string, file path -- relative to the extension base dir
   * @param $weight int, relative weight within a given region
   * @param $region string, location within the file; 'html-header', 'page-header', 'page-footer'
   * @return CRM_Core_Resources
   */
  public function addStyleFile($ext, $file, $weight = self::DEFAULT_WEIGHT, $region = self::DEFAULT_REGION) {
    return $this->addStyleUrl($this->getUrl($ext, $file), $weight, $region);
  }

  /**
   * Add a CSS file to the current page using <LINK HREF>.
   *
   * @param $url string
   * @param $weight int, relative weight within a given region
   * @param $region string, location within the file; 'html-header', 'page-header', 'page-footer'
   * @return CRM_Core_Resources
   */
  public function addStyleUrl($url, $weight = self::DEFAULT_WEIGHT, $region = self::DEFAULT_REGION) {
    CRM_Core_Region::instance($region)->add(array(
      'name' => $url,
      'type' => 'styleUrl',
      'styleUrl' => $url,
      'weight' => $weight,
      'region' => $region,
    ));
    return $this;
  }

  /**
   * Add a CSS content to the current page using <STYLE>.
   *
   * @param $code string, CSS source code
   * @param $weight int, relative weight within a given region
   * @param $region string, location within the file; 'html-header', 'page-header', 'page-footer'
   * @return CRM_Core_Resources
   */
  public function addStyle($code, $weight = self::DEFAULT_WEIGHT, $region = self::DEFAULT_REGION) {
    CRM_Core_Region::instance($region)->add(array(
      // 'name' => automatic
      'type' => 'style',
      'style' => $code,
      'weight' => $weight,
      'region' => $region,
    ));
    return $this;
  }

  /**
   * Determine public URL of a resource provided by an extension
   *
   * @param $ext string, extension name; use 'civicrm' for core
   * @param $file string, file path -- relative to the extension base dir
   * @return string, URL
   */
  public function getUrl($ext, $file = NULL) {
    if ($file === NULL) {
      $file = '';
    }

    if (isset($this->extMap[$ext])) {
      return $this->extMap[$ext] . $file;
    } elseif (isset($this->extMap['*'])) {
      return $this->extMap['*'] . $ext . '/' . $file;
    } else {
      CRM_Core_Error::debug_log_message("CRM_Core_Resources::getUrl('$ext','$file') failed: please ensure extensionsURL is configured");
      return '/';
    }
  }
}
