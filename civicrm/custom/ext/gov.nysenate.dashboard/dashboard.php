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
    CRM_Core_Resources::singleton()->addScriptFile(CRM_Dashboard_ExtensionUtil::LONG_NAME, 'js/dashboard.js');
    CRM_Core_Resources::singleton()->addStyleFile(CRM_Dashboard_ExtensionUtil::LONG_NAME, 'css/dashboard.css');

    CRM_Core_Resources::singleton()->addVars('NYSS', [
      'bbNewsUrl' => '<a href="https://senateonline.nysenate.gov/BluebirdNews.nsf" target="_blank">Bluebird News</a>'
    ]);

    //check to see if already loaded this session
    if (CRM_Core_Session::singleton()->get('bbDashNews')) {
      return;
    }

    $rss_tags = [
      'title',
      'pubDate',
      'description',
      'link',
      'category',
      'creator',
      'comments',
      'guid',
      'encoded',
    ];

    $bbcfg = get_bluebird_instance_config();
    if (isset($bbcfg['news.url'])) {
      $news_url = $bbcfg['news.url'];
    }
    else {
      $news_url = 'https://senateonline.nysenate.gov/BluebirdNews.nsf';
    }
    $rss_url = "{$news_url}/feed.rss";

    $articles = _dashboard_RSStoArray('item', $rss_tags, $rss_url);
    //Civi::log()->debug('', ['$articles' => $articles]);

    $message = [];
    foreach ($articles as $article) {
      $content = CRM_Utils_String::ellipsify(strip_tags($article['encoded']), 100);
      $message[] = "<li><a href='{$article['guid']}' target='_blank' title='{$content}'>{$article['title']}</a></li>";
    }

    if (!empty($message)) {
      $messageHtml = '<ul>'.implode("\n", $message).'</ul>';

      //trigger notification
      CRM_Core_Session::setStatus($messageHtml, 'Latest Bluebird News', 'info', ['unique' => TRUE]);

      //set notification session var
      CRM_Core_Session::singleton()->set('bbDashNews', TRUE);
    }
  }
}

function dashboard_civicrm_alterAngular(\Civi\Angular\Manager $angular) {
  //inject wizard
  $changeSet = \Civi\Angular\ChangeSet::create('user_dashboard')
    ->alterHtml('~/crmDashboard/Dashboard.html', '_dashboard_alterUserDashboard');
  $angular->add($changeSet);
}

function _dashboard_RSStoArray($tag, $array, $url) {
  $doc = new DOMdocument();
  $doc->load($url);
  $rss_array = array();
  $items = array();
  $i = 0;

  foreach ($doc->getElementsByTagName($tag) as $node) {
    //only show the 5 most recent posts
    if ($i > 4) {
      break;
    }
    foreach ($array as $key => $value) {
      $items[$value] = $node->getElementsByTagName($value)->item(0)->nodeValue;
      if ($value == 'pubDate') {
        $items[$value] = date("l, M j, Y g:ia", strtotime($items[$value]));
      }
    }
    array_push($rss_array, $items);
    $i++;
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
