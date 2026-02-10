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
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function dashboard_civicrm_install() {
  _dashboard_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function dashboard_civicrm_enable() {
  _dashboard_civix_civicrm_enable();
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
