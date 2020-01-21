<?php

require_once 'search.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function search_civicrm_config(&$config) {
  _search_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function search_civicrm_xmlMenu(&$files) {
  _search_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function search_civicrm_install() {
  _search_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function search_civicrm_uninstall() {
  _search_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function search_civicrm_enable() {
  _search_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function search_civicrm_disable() {
  _search_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function search_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _search_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function search_civicrm_managed(&$entities) {
  _search_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function search_civicrm_caseTypes(&$caseTypes) {
  _search_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function search_civicrm_angularModules(&$angularModules) {
_search_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function search_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _search_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

function search_civicrm_buildForm($formName, &$form) {
  /*Civi::log()->debug('search_civicrm_buildForm', array(
    'formName' => $formName,
    'form' => $form,
      '_elementIndex' => $form->_elementIndex,
  ));*/

  if ($formName == 'CRM_Contact_Form_Search_Advanced') {
    //3815 add privacy option note
    $ele = $form->addElement('text', 'custom_64', ts('Privacy Option Notes'), ['id'=>'custom_64', 'class' => 'crm-form-text big'], 'size="30"');
    $eleHtml = $ele->toHtml();

    CRM_Core_Resources::singleton()->addVars('NYSS', array('bbPrivacyOptionNotes_Html' => $eleHtml));
    CRM_Core_Resources::singleton()->addScriptFile('gov.nysenate.search', 'js/AdvancedSearch.js');
    CRM_Core_Resources::singleton()->addScriptFile('gov.nysenate.search', 'js/ActivitySearch.js');
    CRM_Core_Resources::singleton()->addStyleFile('gov.nysenate.search', 'css/AdvancedSearch.css');

    //7906 search birth date by month only
    $months = array(
      ''  => '- select month -',
      '1' => 'January',
      '2' => 'February',
      '3' => 'March',
      '4' => 'April',
      '5' => 'May' ,
      '6' => 'June',
      '7' => 'July',
      '8' => 'August',
      '9' => 'September',
      '10' => 'October',
      '11' => 'November',
      '12' => 'December',
    );
    $form->add('select', 'birth_date_month',  ts('Birth Date Month'), array('' => ts('- select month -')) + $months);

    //10557 remove CMS User
    if ($form->elementExists('uf_user')) {
      $form->removeElement('uf_user');
    }

    //11138 remove activity test
    if ($form->elementExists('activity_test')) {
      $form->removeElement('activity_test');
      CRM_Core_Resources::singleton()->addStyle('a.helpicon[title="Test Records Help"] { display: none; }');
    }

    //11134 change tag label text; remove issue codes from list
    if ($form->elementExists('contact_tags')) {
      $contactTags =& $form->getElement('contact_tags');
      unset($contactTags->_options[0]);
      CRM_Core_Resources::singleton()->addScript('cj("select#contact_tags").prev("label").text("Issue Codes")');
    }

    //13256 adv search postal code field
    if ($form->elementExists('postal_code')) {
      $ele =& $form->getElement('postal_code');
      $ele->_attributes['maxLength'] = 128;
      $ele->_attributes['size'] = 45;
    }

    //set defaults
    $defaults = [
      'country' => '', //don't set US as default country in advanced search or contacts with no address are excluded
      'is_deceased' => 0, //3527 set deceased to no
      'activity_role' => 0, //4332 clear activity creator/assigned
    ];
    $form->setDefaults($defaults);
  }

  if ($formName == 'CRM_Activity_Form_Search') {
    CRM_Core_Resources::singleton()->addScriptFile('gov.nysenate.search', 'js/ActivitySearch.js');
  }


  if (strpos($formName, 'CRM_Contact_Form_Search_Custom') !== FALSE) {
    //Civi::log()->debug('', ['form' => $form]);

    $searchId = $form->getVar('_customSearchID');
    $resetUrl = CRM_Utils_System::url('civicrm/contact/search/custom', "reset=1&csid={$searchId}");

    CRM_Core_Resources::singleton()->addStyleFile('gov.nysenate.search', 'css/CustomSearch.css');

    CRM_Core_Region::instance('form-buttons')->add(array(
      'markup' => "
        <div class='crm-submit-buttons reset-custom-search'>
          <a href='{$resetUrl}' id='resetCustomSearch' class='crm-hover-button' title='Clear search criteria'>
            <i class='crm-i fa-undo'></i>&nbsp;Reset Form
          </a>
        </div>
      ",
    ));
  }
}

function search_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  /*Civi::log()->debug('', [
    '$formName' => $formName,
    '$fields' => $fields,
    //'$files' => $files,
    //'$form' => $form,
    //'$errors' => $errors,
  ]);*/

  if ($formName == 'CRM_Activity_Form_Search') {
    $data = &$form->controller->container();
    //Civi::log()->debug('', ['data' => $data]);

    if (!empty($data['values']['Search']['activity_text']) &&
      strpos($data['values']['Search']['activity_text'],'%') === FALSE
    ) {
      $data['values']['Search']['activity_text'] = '%'.$data['values']['Search']['activity_text'].'%';
    }
    //Civi::log()->debug('post', ['data' => $data]);
  }
}

/**
 * Implements hook_civicrm_pageRun().
 */
function search_civicrm_pageRun(&$page) {
  if (!empty($_REQUEST['snippet']) && in_array($_REQUEST['snippet'], ['json', 6])) {
    $page->ajaxResponse['searchresults_items'] = _get_searchresults_items($page);
  }
  else {
    Civi::resources()->addVars('searchresults', _get_searchresults_items($page));
  }
}

/**
 * Implements hook_civicrm_preProcess().
 */
function search_civicrm_preProcess($formName, &$form) {
  if (!empty($_REQUEST['snippet']) && in_array($_REQUEST['snippet'], ['json', 6])) {
    $form->ajaxResponse['searchresults_items'] = _get_searchresults_items($form);
  }
}

/**
 * Implements hook_civicrm_postProcess().
 */
function search_civicrm_postProcess($formName, &$form) {
  if (!empty($_REQUEST['snippet']) && in_array($_REQUEST['snippet'], ['json', 6])) {
    $form->ajaxResponse['searchresults_items'] = _get_searchresults_items($form);
  }
}

/**
 * Implements hook_civicrm_coreResourceList().
 */
function search_civicrm_coreResourceList(&$list, $region) {
  if ($region == 'html-header' && CRM_Core_Permission::check('access CiviCRM')) {
    Civi::resources()
      ->addScriptFile('gov.nysenate.search', 'js/SearchResult.js', 0, 'html-header');
      //->addVars('searchresults', _get_searchresults_items());
  }
}

function _get_searchresults_items($obj = NULL) {
  $menu = [];

  //validate the qfKey
  $qfKey = CRM_Utils_Request::retrieve('key', 'String');
  if (CRM_Utils_Rule::qfKey($qfKey)) {
    $breadCrumb = drupal_get_breadcrumb();
    $searchUrl = '';
    foreach ($breadCrumb as $crumb) {
      if (strpos($crumb, 'Search Results') !== FALSE) {
        $searchUrl = str_replace('<a href="', '', $crumb);
        $searchUrl = str_replace('">Search Results</a>', '', $searchUrl);
      }
    }

    /*Civi::log()->debug('', [
      'searchUrl' => $searchUrl,
      'breadcrumb' => $breadCrumb,
      'obj' => $obj,
      'qfKey' => $qfKey,
    ]);*/

    if ($searchUrl) {
      $menu = [
        'label' => 'Search Results',
        'name' => 'search_results',
        'icon' => 'crm-i fa-search-plus',
        'url' => $searchUrl,
      ];
    }
  }

  return $menu;
}
