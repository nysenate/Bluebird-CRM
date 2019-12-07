<?php

require_once 'civicase.civix.php';

/**
 * Implements hook_civicrm_tabset().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_tabset
 */
function civicase_civicrm_tabset($tabsetName, &$tabs, $context) {
  $useAng = FALSE;

  switch ($tabsetName) {
    case 'civicrm/contact/view':
      $caseTabPresent = FALSE;

      foreach ($tabs as &$tab) {
        if ($tab['id'] === 'case') {
          $caseTabPresent = TRUE;
          $useAng = TRUE;
          $tab['url'] = CRM_Utils_System::url('civicrm/case/contact-case-tab', array(
            'cid' => $context['contact_id'],
          ));
        }
        if ($tab['id'] === 'activity') {
          $useAng = TRUE;
          $tab['url'] = CRM_Utils_System::url('civicrm/case/contact-act-tab', array(
            'cid' => $context['contact_id'],
          ));
        }
      }

      if (!$caseTabPresent && CRM_Core_Permission::check('basic case information')) {
        $useAng = TRUE;
        $tabs[] = array(
          'id' => 'case',
          'url' => CRM_Utils_System::url('civicrm/case/contact-case-tab', array(
            'cid' => $context['contact_id'],
          )),
          'title' => ts('Cases'),
          'weight' => 20,
          'count' => CRM_Contact_BAO_Contact::getCountComponent('case', $context['contact_id']),
          'class' => 'livePage',
        );
      }

      break;

  }

  if ($useAng) {
    $loader = new \Civi\Angular\AngularLoader();
    $loader->setPageName('civicrm/case/a');
    $loader->setModules(array('civicase'));
    $loader->load();
  }
}

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function civicase_civicrm_config(&$config) {
  _civicase_civix_civicrm_config($config);

  if (isset(Civi::$statics[__FUNCTION__])) {
    return;
  }
  Civi::$statics[__FUNCTION__] = 1;

  Civi::dispatcher()->addListener('civi.api.prepare', array('CRM_Civicase_ActivityFilter', 'onPrepare'), 10);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function civicase_civicrm_xmlMenu(&$files) {
  _civicase_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function civicase_civicrm_install() {
  _civicase_civix_civicrm_install();
}


/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function civicase_civicrm_postInstall() {
  _civicase_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function civicase_civicrm_uninstall() {
  _civicase_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function civicase_civicrm_enable() {
  _civicase_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function civicase_civicrm_disable() {
  _civicase_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function civicase_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _civicase_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function civicase_civicrm_managed(&$entities) {
  _civicase_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function civicase_civicrm_caseTypes(&$caseTypes) {
  _civicase_civix_civicrm_caseTypes($caseTypes);
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
function civicase_civicrm_angularModules(&$angularModules) {
  _civicase_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterMenu
 */
function civicase_civicrm_alterMenu(&$items) {
  $items['civicrm/case/activity']['ids_arguments']['json'][] = 'civicase_reload';
  $items['civicrm/activity']['ids_arguments']['json'][] = 'civicase_reload';
  $items['civicrm/activity/email/add']['ids_arguments']['json'][] = 'civicase_reload';
  $items['civicrm/activity/pdf/add']['ids_arguments']['json'][] = 'civicase_reload';
  $items['civicrm/case/cd/edit']['ids_arguments']['json'][] = 'civicase_reload';
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function civicase_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _civicase_civix_civicrm_alterSettingsFolders($metaDataFolders);
}


/**
 * Implements hook_civicrm_buildForm().
 *
 * @param string $formName
 * @param CRM_Core_Form $form
 */
function civicase_civicrm_buildForm($formName, &$form) {
  // Display category option for activity types and activity statuses
  if ($formName == 'CRM_Admin_Form_Options' && in_array($form->getVar('_gName'), array('activity_type', 'activity_status'))) {
    $options = civicrm_api3('optionValue', 'get', array(
      'option_group_id' => 'activity_category',
      'is_active' => 1,
      'options' => array('limit' => 0, 'sort' => 'weight'),
    ));
    $opts = array();
    if ($form->getVar('_gName') == 'activity_status') {
      $placeholder = ts('All');
      // Activity status can also apply to uncategorized activities
      $opts[] = array(
        'id' => 'none',
        'text' => ts('Uncategorized'),
      );
    }
    else {
      $placeholder = ts('Uncategorized');
    }
    foreach ($options['values'] as $opt) {
      $opts[] = array(
        'id' => $opt['name'],
        'text' => $opt['label'],
      );
    }
    $form->add('select2', 'grouping', ts('Activity Category'), $opts, FALSE, array('class' => 'crm-select2', 'multiple' => TRUE, 'placeholder' => $placeholder));
  }
  // Only show relevant statuses when editing an activity
  if (is_a($form, 'CRM_Activity_Form_Activity') && $form->_action & (CRM_Core_Action::ADD + CRM_Core_Action::UPDATE)) {
    if (!empty($form->_activityTypeId) && $form->elementExists('status_id')) {
      $el = $form->getElement('status_id');
      $cat = civicrm_api3('OptionValue', 'getsingle', array(
        'return' => 'grouping',
        'option_group_id' => "activity_type",
        'value' => $form->_activityTypeId,
      ));
      $cat = !empty($cat['grouping']) ? explode(',', $cat['grouping']) : array('none');
      $options = civicrm_api3('OptionValue', 'get', array(
        'return' => array('label', 'value', 'grouping'),
        'option_group_id' => "activity_status",
        'options' => array('limit' => 0, 'sort' => 'weight'),
      ));
      $newOptions = $el->_options = array();
      $newOptions[''] = ts('- select -');
      foreach ($options['values'] as $option) {
        if (empty($option['grouping']) || array_intersect($cat, explode(',', $option['grouping']))) {
          $newOptions[$option['value']] = $option['label'];
        }
      }
      $el->loadArray($newOptions);
    }
  }
  // If js requests a refresh of case data pass that request along
  if (!empty($_REQUEST['civicase_reload'])) {
    $form->civicase_reload = json_decode($_REQUEST['civicase_reload'], TRUE);
  }
  // Add save draft button to Communication activities
  $specialForms = array('CRM_Contact_Form_Task_PDF', 'CRM_Contact_Form_Task_Email');
  $specialTypes = array('Print PDF Letter', 'Email');
  if (is_a($form, 'CRM_Activity_Form_Activity') || in_array($formName, $specialForms)) {
    $activityTypeId = $form->getVar('_activityTypeId');
    if ($activityTypeId) {
      $activityType = civicrm_api3('OptionValue', 'getvalue', array(
        'return' => "name",
        'option_group_id' => "activity_type",
        'value' => $activityTypeId,
      ));
    }
    else {
      $activityType = $formName == 'CRM_Contact_Form_Task_PDF' ? 'Print PDF Letter' : 'Email';
    }
    $id = $form->getVar('_activityId');
    $status = NULL;
    if ($id) {
      $status = civicrm_api3('Activity', 'getsingle', array('id' => $id, 'return' => 'status_id.name'));
      $status = $status['status_id.name'];
    }
    $checkParams = array('option_group_id' => 'activity_type', 'grouping' => array('LIKE' => '%communication%'), 'value' => $activityTypeId);
    if (in_array($activityType, $specialTypes) || ($activityTypeId && civicrm_api3('OptionValue', 'getcount', $checkParams))) {
      if ($form->_action & (CRM_Core_Action::ADD + CRM_Core_Action::UPDATE)) {
        $buttonGroup = $form->getElement('buttons');
        $buttons = $buttonGroup->getElements();
        $buttons[] = $form->createElement('submit', $form->getButtonName('refresh'), ts('Save Draft'), array(
          'crm-icon' => 'fa-pencil-square-o',
          'class' => 'crm-form-submit',
        ));
        $buttonGroup->setElements($buttons);
        $form->addGroup($buttons, 'buttons');
        $form->setDefaults(array('status_id' => 2));
      }
      if ($status == 'Draft' && ($form->_action & CRM_Core_Action::VIEW)) {
        if (in_array($activityType, $specialTypes)) {
          $atype = $activityType == 'Email' ? 'email' : 'pdf';
          $caseId = civicrm_api3('Activity', 'getsingle', array('id' => $id, 'return' => 'case_id'));
          $composeUrl = CRM_Utils_System::url("civicrm/activity/$atype/add", array(
            'action' => 'add',
            'reset' => 1,
            'caseId' => $caseId['case_id'][0],
            'context' => 'standalone',
            'draft_id' => $id,
          ));
          $buttonMarkup = '<a class="button" href="' . $composeUrl . '"><i class="crm-i fa-pencil-square-o"></i> &nbsp;' . ts('Continue Editing') . '</a>';
          $form->assign('activityTypeDescription', $buttonMarkup);
        }
        else {
          $form->assign('activityTypeDescription', '<i class="crm-i fa-pencil-square-o"></i> &nbsp;' . ts('Saved as a Draft'));
        }
      }
    }
    // Form email/print activities, set defaults from the original draft activity (which will be deleted on submit)
    if (in_array($formName, $specialForms) && !empty($_GET['draft_id'])) {
      $draft = civicrm_api3('Activity', 'get', array('id' => $_GET['draft_id'], 'check_permissions' => TRUE, 'sequential' => TRUE));
      $form->setVar('_activityId', $_GET['draft_id']);
      if (isset($draft['values'][0])) {
        $draft = $draft['values'][0];
        if (in_array($formName, $specialForms)) {
          $draft['html_message'] = CRM_Utils_Array::value('details', $draft);
        }
        // Set defaults for to email addresses
        if ($formName == 'CRM_Contact_Form_Task_Email') {
          $cids = CRM_Utils_Array::value('target_contact_id', civicrm_api3('Activity', 'getsingle', array('id' => $draft['id'], 'return' => 'target_contact_id')));
          if ($cids) {
            $toContacts = civicrm_api3('Contact', 'get', array('id' => array('IN' => $cids), 'return' => array('email', 'sort_name')));
            $toArray = array();
            foreach ($toContacts['values'] as $cid => $contact) {
              $toArray[] = array(
                'text' => '"' . $contact['sort_name'] . '" <' . $contact['email'] . '>',
                'id' => "$cid::{$contact['email']}",
              );
            }
            $form->assign('toContact', json_encode($toArray));
          }
        }
        $form->setDefaults($draft);
      }
    }
  }
}

/**
 * Implements hook_civicrm_validateForm().
 *
 * @param string $formName
 * @param array $fields
 * @param array $files
 * @param CRM_Core_Form $form
 * @param array $errors
 */
function civicase_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  // Save draft feature
  // The validate stage provides an opportunity to bypass normal form processing, save the draft & return early
  $specialForms = array('CRM_Contact_Form_Task_PDF', 'CRM_Contact_Form_Task_Email');
  if (is_a($form, 'CRM_Activity_Form_Activity') || in_array($formName, $specialForms)) {
    if (array_key_exists($form->getButtonName('refresh'), $fields['buttons'])) {
      $activityType = $form->getVar('_activityTypeId');
      $caseId = $form->getVar('_caseId');
      if (!$activityType) {
        $activityType = $formName == 'CRM_Contact_Form_Task_PDF' ? 'Print PDF Letter' : 'Email';
      }
      $params = array(
        'activity_type_id' => $activityType,
        'status_id' => 'Draft',
        'case_id' => $caseId,
        'id' => $form->getVar('_activityId'),
      );
      if (in_array($formName, $specialForms)) {
        $params['details'] = CRM_Utils_Array::value('html_message', $fields);
      }
      if ($formName == 'CRM_Contact_Form_Task_Email') {
        $params['target_contact_id'] = explode(',', CRM_Utils_Array::value('to', $fields));
        $params['target_contact_id'] = array_map('intval', $params['target_contact_id']);
      }
      $newActivity = civicrm_api3('Activity', 'create', $params + $fields);
      $url = CRM_Utils_System::url('civicrm/contact/view/case',
        "reset=1&action=view&cid={$form->_currentlyViewedContactId}&id={$caseId}&show=1"
      );
      $session = CRM_Core_Session::singleton();
      $session->pushUserContext($url);
      CRM_Core_Session::setStatus('Activity saved as a draft', ts('Saved'), 'success');
      if (CRM_Utils_Array::value('snippet', $_GET) === 'json') {
        $response = array();
        if (!empty($form->civicase_reload)) {
          $api = civicrm_api3('Case', 'getdetails', array('check_permissions' => 1) + $form->civicase_reload);
          $response['civicase_reload'] = $api['values'];
        }
        CRM_Core_Page_AJAX::returnJsonResponse($response);
      }
      CRM_Utils_System::redirect($url);
    }
  }
}

/**
 * Implements hook_civicrm_postProcess().
 * @param string $formName
 * @param CRM_Core_Form $form
 * @throws \CiviCRM_API3_Exception
 */
function civicase_civicrm_postProcess($formName, &$form) {
  if (!empty($form->civicase_reload)) {
    $api = civicrm_api3('Case', 'getdetails', array('check_permissions' => 1) + $form->civicase_reload);
    $form->ajaxResponse['civicase_reload'] = $api['values'];
  }
  // When emailing/printing - delete draft.
  $specialForms = array('CRM_Contact_Form_Task_PDF', 'CRM_Contact_Form_Task_Email');
  if (in_array($formName, $specialForms)) {
    $urlParams = parse_url(htmlspecialchars_decode($form->controller->_entryURL), PHP_URL_QUERY);
    parse_str($urlParams, $urlParams);
    if (!empty($urlParams['draft_id'])) {
      civicrm_api3('Activity', 'delete', array('id' => $urlParams['draft_id']));
    }
  }
}

/**
 * Implements hook_civicrm_permission()
 *
 * @param array $permissions
 *   Array of permissions defined on extensions
 */
function civicase_civicrm_permission(&$permissions) {
  $permissions['basic case information'] = array(
    'Civicase: basic case information',
    ts('Allows a user to view only basic information of cases.')
  );
}

/**
 * Implements hook_civicrm_apiWrappers
 */
function civicase_civicrm_apiWrappers(&$wrappers, $apiRequest) {
  if ($apiRequest['entity'] == 'Case') {
    $wrappers[] = new CRM_Civicase_APIHelpers_CaseList();
  }
}

/**
 * Implements hook_civicrm_alterAPIPermissions().
 *
 * @link https://docs.civicrm.org/dev/en/master/hooks/hook_civicrm_alterAPIPermissions/
 */
function civicase_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
  $permissions['case']['getfiles'] = array(
    array('access my cases and activities', 'access all cases and activities'),
    'access uploaded files',
  );

  $permissions['case']['get'] = array(
    array(
      'access my cases and activities',
      'access all cases and activities',
      'basic case information',
    )
  );

  $permissions['case']['getcount'] = array(
    array(
      'access my cases and activities',
      'access all cases and activities',
      'basic case information',
    )
  );

  $permissions['case_type']['get'] = $permissions['casetype']['getcount'] = array(
    array(
      'access my cases and activities',
      'access all cases and activities',
      'basic case information',
    )
  );
}

/**
 * Implements hook_civicrm_pageRun().
 */
function civicase_civicrm_pageRun(&$page) {
  if ($page instanceof CRM_Case_Page_Tab) {
    // OLD: http://localhost/civicrm/contact/view/case?reset=1&action=view&cid=129&id=51
    // NEW: http://localhost/civicrm/case/a/#/case/list?sf=contact_id.sort_name&sd=ASC&focus=0&cf=%7B%7D&caseId=51&tab=summary&sx=0

    $caseId = CRM_Utils_Request::retrieve('id', 'Positive');
    if ($caseId) {
      $case = civicrm_api3('Case', 'getsingle', array(
        'id' => $caseId,
        'return' => array('case_type_id.name', 'status_id.name'),
      ));
      $url = CRM_Utils_System::url('civicrm/case/a/', NULL, TRUE,
        "/case/list?sf=id&sd=DESC&caseId={$caseId}&cf=%7B%22status_id%22:%5B%22{$case['status_id.name']}%22%5D,%22case_type_id%22:%5B%22{$case['case_type_id.name']}%22%5D%7D",
        FALSE);
      CRM_Utils_System::redirect($url);
    }
  }
}

/**
 * Implements hook_civicrm_check().
 */
function civicase_civicrm_check(&$messages) {
  $check = new CRM_Civicase_Check();
  $newMessages = $check->checkAll();
  $messages = array_merge($messages, $newMessages);
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function civicase_civicrm_navigationMenu(&$menu) {
  /**
   * @var array
   *   Array(string $oldUrl => string $newUrl).
   */
  $rewriteMap = array(
    'civicrm/case?reset=1' => 'civicrm/case/a/#/case',
    'civicrm/case/search?reset=1' => 'civicrm/case/a/#/case/list?sx=1',
  );

  _civicase_menu_walk($menu, function(&$item) use ($rewriteMap) {
    if (isset($item['url']) && isset($rewriteMap[$item['url']])) {
      $item['url'] = $rewriteMap[$item['url']];
    }
  });
}

/**
 * Visit every link in the navigation menu, and alter it using $callback.
 *
 * @param array $menu
 *   Tree of menu items, per hook_civicrm_navigationMenu.
 * @param callable $callback
 *   Function(&$item).
 */
function _civicase_menu_walk(&$menu, $callback) {
  foreach (array_keys($menu) as $key) {
    if (isset($menu[$key]['attributes'])) {
      $callback($menu[$key]['attributes']);
    }

    if (isset($menu[$key]['child'])) {
      _civicase_menu_walk($menu[$key]['child'], $callback);
    }
  }
}

/**
 * Implements hook_civicrm_selectWhereClause
 */
function civicase_civicrm_selectWhereClause($entity, &$clauses) {
  if ($entity === 'Case' && CRM_Core_Permission::check('basic case information')) {
    unset($clauses['id']);
  }
}
