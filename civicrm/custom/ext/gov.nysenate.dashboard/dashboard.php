<?php

require_once 'dashboard.civix.php';
use CRM_Dashboard_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function dashboard_civicrm_config(&$config) {
  _dashboard_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function dashboard_civicrm_xmlMenu(&$files) {
  _dashboard_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function dashboard_civicrm_install() {
  _dashboard_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function dashboard_civicrm_postInstall() {
  _dashboard_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function dashboard_civicrm_uninstall() {
  _dashboard_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function dashboard_civicrm_enable() {
  _dashboard_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function dashboard_civicrm_disable() {
  _dashboard_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function dashboard_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _dashboard_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function dashboard_civicrm_managed(&$entities) {
  _dashboard_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function dashboard_civicrm_caseTypes(&$caseTypes) {
  _dashboard_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function dashboard_civicrm_angularModules(&$angularModules) {
  _dashboard_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function dashboard_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _dashboard_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function dashboard_civicrm_entityTypes(&$entityTypes) {
  _dashboard_civix_civicrm_entityTypes($entityTypes);
}

function dashboard_civicrm_pageRun(&$page) {
  if (is_a($page, 'CRM_Contact_Page_DashBoard')) {
    $bbcfg = get_bluebird_instance_config();
    $intranet_url = $bbcfg['intranet.url'] ?? '';
    $intranet_label = $bbcfg['intranet.label'] ?? 'Bluebird Info';
    $rss_url = $bbcfg['rss.url'] ?? '';
    $rss_label = $bbcfg['rss.label'] ?? 'Latest Bluebird News';

    CRM_Core_Resources::singleton()->addScriptFile(CRM_Dashboard_ExtensionUtil::LONG_NAME, 'js/dashboard.js');
    CRM_Core_Resources::singleton()->addStyleFile(CRM_Dashboard_ExtensionUtil::LONG_NAME, 'css/dashboard.css');

    if ($intranet_url) {
      $val = [
        'bbNewsUrl' => "<a href=\"$intranet_url\" target=\"_blank\">$intranet_label</a>"
      ];
      CRM_Core_Resources::singleton()->addVars('NYSS', $val);
    }

    //check to see if already loaded this session
    if (CRM_Core_Session::singleton()->get('bbDashNews')) {
      return;
    }

    // if rss.url was specified in the config file, use it to pull the latest
    // Bluebird News from an RSS feed.
    if ($rss_url) {
      $articles = _dashboard_RssToArray($rss_url);
      //Civi::log()->debug('', ['$articles' => $articles]);

      $news_html_items = [];
      foreach ($articles as $article) {
        $title = $article['title'];
        $link = $article['link'];
        $news_html_items[] = "<li><a href=\"$link\" target=\"_blank\">$title</a></li>";
      }

      if (count($news_html_items) > 0) {
        $news_html_list = '<ul>'.implode("\n", $news_html_items).'</ul>';

        //trigger notification
        CRM_Core_Session::setStatus($news_html_list, $rss_label, 'info', ['unique' => TRUE]);

        //set notification session var
        CRM_Core_Session::singleton()->set('bbDashNews', TRUE);
      }
    }
  }
}

function dashboard_civicrm_alterAngular(\Civi\Angular\Manager $angular) {
  //inject wizard
  $changeSet = \Civi\Angular\ChangeSet::create('user_dashboard')
    ->alterHtml('~/crmDashboard/Dashboard.html', '_dashboard_alterUserDashboard');
  $angular->add($changeSet);
}

function _dashboard_RssToArray($url) {
  $tags = [ 'title', 'link', 'pubDate' ];
  $rss_array = [];
  $i = 0;
  $doc = new DOMdocument();
  $doc->load($url);

  foreach ($doc->getElementsByTagName('item') as $node) {
    $items = [];
    foreach ($tags as $value) {
      $items[$value] = $node->getElementsByTagName($value)->item(0)->nodeValue;
    }
    $rss_array[] = $items;
    // Only show the 5 most recent posts.
    if (++$i > 4) {
      break;
    }
  }

  return $rss_array;
}

/**
 * @param phpQueryObject $doc
 *
 * main user dashboard html
 * limit columns to 1
 */
function _dashboard_alterUserDashboard(phpQueryObject $doc) {
  $extDir = CRM_Core_Resources::singleton()->getPath(E::LONG_NAME);
  $html = file_get_contents($extDir.'/html/dashboard.html');
  $doc->find('div.crm-flex-box')->html($html);
}
