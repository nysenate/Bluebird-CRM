<?php

/**
 * @file
 * Extension file.
 */

use Civi\Angular\AngularLoader;

require_once 'civicase.civix.php';

/**
 * Implements hook_civicrm_tabset().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_tabset
 */
function civicase_civicrm_tabset($tabsetName, &$tabs, $context) {
  $useAng = FALSE;
  $hooks = [
    new CRM_Civicase_Hook_Tabset_CaseTabModifier(),
    new CRM_Civicase_Hook_Tabset_CaseCategoryTabAdd(),
    new CRM_Civicase_Hook_Tabset_ActivityTabModifier(),
  ];

  foreach ($hooks as $hook) {
    $hook->run($tabsetName, $tabs, $context, $useAng);
  }

  if ($useAng) {
    $loader = new AngularLoader();
    $loader->setPageName('civicrm/case/a');
    $loader->setModules(['civicase']);
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

  Civi::dispatcher()->addListener('civi.api.prepare', ['CRM_Civicase_Event_Listener_ActivityFilter', 'onPrepare'], 10);
  Civi::dispatcher()->addListener('civi.api.respond', ['CRM_Civicase_Event_Listener_CaseTypeCategoryIsActiveToggler', 'onRespond'], 10);
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
  $items['civicrm/export/standalone']['ids_arguments']['json'][] = 'civicase_reload';
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
 */
function civicase_civicrm_buildForm($formName, &$form) {
  $hooks = [
    new CRM_Civicase_Hook_BuildForm_CaseClientPopulator(),
    new CRM_Civicase_Hook_BuildForm_CaseCategoryCustomFieldsProcessing(),
    new CRM_Civicase_Hook_BuildForm_FilterByCaseCategoryOnChangeCaseType(),
    new CRM_Civicase_Hook_BuildForm_CaseCategoryFormLabelTranslationForNewCase(),
    new CRM_Civicase_Hook_BuildForm_CaseCategoryFormLabelTranslationForChangeCase(),
    new CRM_Civicase_Hook_BuildForm_EnableCaseCategoryIconField(),
    new CRM_Civicase_Hook_BuildForm_CaseCategoryCustomGroupDisplay(),
    new CRM_Civicase_Hook_BuildForm_ModifyCaseTypesForAdvancedSearch(),
  ];

  foreach ($hooks as $hook) {
    $hook->run($form, $formName);
  }

  // Display category option for activity types and activity statuses.
  if ($formName == 'CRM_Admin_Form_Options' && in_array($form->getVar('_gName'), ['activity_type', 'activity_status'])) {
    $options = civicrm_api3('optionValue', 'get', [
      'option_group_id' => 'activity_category',
      'is_active' => 1,
      'options' => ['limit' => 0, 'sort' => 'weight'],
    ]);
    $opts = [];
    if ($form->getVar('_gName') == 'activity_status') {
      $placeholder = ts('All');
      // Activity status can also apply to uncategorized activities.
      $opts[] = [
        'id' => 'none',
        'text' => ts('Uncategorized'),
      ];
    }
    else {
      $placeholder = ts('Uncategorized');
    }
    foreach ($options['values'] as $opt) {
      $opts[] = [
        'id' => $opt['name'],
        'text' => $opt['label'],
      ];
    }
    $form->add('select2', 'grouping', ts('Activity Category'), $opts, FALSE, [
      'class' => 'crm-select2',
      'multiple' => TRUE,
      'placeholder' => $placeholder,
    ]);
  }
  // Only show relevant statuses when editing an activity.
  if (is_a($form, 'CRM_Activity_Form_Activity') && $form->_action & (CRM_Core_Action::ADD + CRM_Core_Action::UPDATE)) {
    if (!empty($form->_activityTypeId) && $form->elementExists('status_id')) {
      $el = $form->getElement('status_id');
      $cat = civicrm_api3('OptionValue', 'getsingle', [
        'return' => 'grouping',
        'option_group_id' => "activity_type",
        'value' => $form->_activityTypeId,
      ]);
      $cat = !empty($cat['grouping']) ? explode(',', $cat['grouping']) : ['none'];
      $options = civicrm_api3('OptionValue', 'get', [
        'return' => ['label', 'value', 'grouping'],
        'option_group_id' => "activity_status",
        'options' => ['limit' => 0, 'sort' => 'weight'],
      ]);
      $newOptions = $el->_options = [];
      $newOptions[''] = ts('- select -');
      foreach ($options['values'] as $option) {
        if (empty($option['grouping']) || array_intersect($cat, explode(',', $option['grouping']))) {
          $newOptions[$option['value']] = $option['label'];
        }
      }
      $el->loadArray($newOptions);
    }
  }
  // If js requests a refresh of case data pass that request along.
  if (!empty($_REQUEST['civicase_reload'])) {
    $form->civicase_reload = json_decode($_REQUEST['civicase_reload'], TRUE);
  }
  // Add save draft button to Communication activities.
  $specialForms = ['CRM_Contact_Form_Task_PDF', 'CRM_Contact_Form_Task_Email'];
  $specialTypes = ['Print PDF Letter', 'Email'];
  if (is_a($form, 'CRM_Activity_Form_Activity') || in_array($formName, $specialForms)) {
    $activityTypeId = $form->getVar('_activityTypeId');
    if ($activityTypeId) {
      $activityType = civicrm_api3('OptionValue', 'getvalue', [
        'return' => "name",
        'option_group_id' => "activity_type",
        'value' => $activityTypeId,
      ]);
    }
    else {
      $activityType = $formName == 'CRM_Contact_Form_Task_PDF' ? 'Print PDF Letter' : 'Email';
    }
    $id = $form->getVar('_activityId');
    $status = NULL;
    if ($id) {
      $status = civicrm_api3('Activity', 'getsingle', ['id' => $id, 'return' => 'status_id.name']);
      $status = $status['status_id.name'];
    }
    $checkParams = [
      'option_group_id' => 'activity_type',
      'grouping' => ['LIKE' => '%communication%'],
      'value' => $activityTypeId,
    ];
    if (in_array($activityType, $specialTypes) || ($activityTypeId && civicrm_api3('OptionValue', 'getcount', $checkParams))) {
      if ($form->_action & (CRM_Core_Action::ADD + CRM_Core_Action::UPDATE)) {
        $buttonGroup = $form->getElement('buttons');
        $buttons = $buttonGroup->getElements();
        $buttons[] = $form->createElement('submit', $form->getButtonName('refresh'), ts('Save Draft'), [
          'crm-icon' => 'fa-pencil-square-o',
          'class' => 'crm-form-submit',
        ]);
        $buttonGroup->setElements($buttons);
        $form->addGroup($buttons, 'buttons');
        $form->setDefaults(['status_id' => 2]);
      }
      if ($status == 'Draft' && ($form->_action & CRM_Core_Action::VIEW)) {
        if (in_array($activityType, $specialTypes)) {
          $atype = $activityType == 'Email' ? 'email' : 'pdf';
          $caseId = civicrm_api3('Activity', 'getsingle', ['id' => $id, 'return' => 'case_id']);
          $composeUrl = CRM_Utils_System::url("civicrm/activity/$atype/add", [
            'action' => 'add',
            'reset' => 1,
            'caseId' => $caseId['case_id'][0],
            'context' => 'standalone',
            'draft_id' => $id,
          ]);
          $buttonMarkup = '<a class="button" href="' . $composeUrl . '"><i class="crm-i fa-pencil-square-o"></i> &nbsp;' . ts('Continue Editing') . '</a>';
          $form->assign('activityTypeDescription', $buttonMarkup);
        }
        else {
          $form->assign('activityTypeDescription', '<i class="crm-i fa-pencil-square-o"></i> &nbsp;' . ts('Saved as a Draft'));
        }
      }
    }
    // Form email/print activities, set defaults from the original draft
    // activity (which will be deleted on submit)
    if (in_array($formName, $specialForms) && !empty($_GET['draft_id'])) {
      $draft = civicrm_api3('Activity', 'get', [
        'id' => $_GET['draft_id'],
        'check_permissions' => TRUE,
        'sequential' => TRUE,
      ]);
      $form->setVar('_activityId', $_GET['draft_id']);
      if (isset($draft['values'][0])) {
        $draft = $draft['values'][0];
        if (in_array($formName, $specialForms)) {
          $draft['html_message'] = CRM_Utils_Array::value('details', $draft);
        }
        // Set defaults for to email addresses.
        if ($formName == 'CRM_Contact_Form_Task_Email') {
          $cids = CRM_Utils_Array::value('target_contact_id', civicrm_api3('Activity', 'getsingle', ['id' => $draft['id'], 'return' => 'target_contact_id']));
          if ($cids) {
            $toContacts = civicrm_api3('Contact', 'get', [
              'id' => ['IN' => $cids],
              'return' => ['email', 'sort_name'],
            ]);
            $toArray = [];
            foreach ($toContacts['values'] as $cid => $contact) {
              $toArray[] = [
                'text' => '"' . $contact['sort_name'] . '" <' . $contact['email'] . '>',
                'id' => "$cid::{$contact['email']}",
              ];
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
 */
function civicase_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  $hooks = [
    new CRM_Civicase_Hook_ValidateForm_SaveActivityDraft(),
    new CRM_Civicase_Hook_ValidateForm_SaveCaseTypeCategory(),
  ];

  foreach ($hooks as $hook) {
    $hook->run($formName, $fields, $files, $form, $errors);
  }
}

/**
 * Implements hook_civicrm_post().
 */
function civicase_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  $hooks = [
    new CRM_Civicase_Hook_Post_PopulateCaseCategoryForCaseType(),
    new CRM_Civicase_Hook_Post_CaseCategoryCustomGroupSaver(),
    new CRM_Civicase_Hook_Post_UpdateCaseTypeListForCaseCategoryCustomGroup(),
  ];

  foreach ($hooks as $hook) {
    $hook->run($op, $objectName, $objectId, $objectRef);
  }
}

/**
 * Implements hook_civicrm_postProcess().
 */
function civicase_civicrm_postProcess($formName, &$form) {
  $hooks = [
    new CRM_Civicase_Hook_PostProcess_SetUserContextForSaveAndNewCase(),
    new CRM_Civicase_Hook_PostProcess_CaseCategoryPostProcessor(),
    new CRM_Civicase_Hook_PostProcess_ActivityFormStatusWordReplacement(),
    new CRM_Civicase_Hook_PostProcess_RedirectToCaseDetails(),
  ];

  foreach ($hooks as $hook) {
    $hook->run($formName, $form);
  }

  if (!empty($form->civicase_reload)) {
    $api = civicrm_api3('Case', 'getdetails', ['check_permissions' => 1] + $form->civicase_reload);
    $form->ajaxResponse['civicase_reload'] = $api['values'];
  }
  // When emailing/printing - delete draft.
  $specialForms = ['CRM_Contact_Form_Task_PDF', 'CRM_Contact_Form_Task_Email'];
  if (in_array($formName, $specialForms)) {
    $urlParams = parse_url(htmlspecialchars_decode($form->controller->_entryURL), PHP_URL_QUERY);
    parse_str($urlParams, $urlParams);
    if (!empty($urlParams['draft_id'])) {
      civicrm_api3('Activity', 'delete', ['id' => $urlParams['draft_id']]);
    }
  }
}

/**
 * Implements hook_civicrm_permission().
 */
function civicase_civicrm_permission(&$permissions) {
  $permissionHook = new CRM_Civicase_Hook_Permissions_CaseCategory($permissions);
  $permissionHook->run();
}

/**
 * Implements hook_civicrm_apiWrappers().
 */
function civicase_civicrm_apiWrappers(&$wrappers, $apiRequest) {
  if ($apiRequest['entity'] == 'Case') {
    $wrappers[] = new CRM_Civicase_Api_Wrapper_CaseList();
    $wrappers[] = new CRM_Civicase_Api_Wrapper_CaseGetList();
  }
}

/**
 * Implements hook_civicrm_alterAPIPermissions().
 *
 * @link https://docs.civicrm.org/dev/en/master/hooks/hook_civicrm_alterAPIPermissions/
 */
function civicase_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
  $hooks = [
    new CRM_Civicase_Hook_alterAPIPermissions_CaseCategory(),
    new CRM_Civicase_Hook_alterAPIPermissions_CaseGetList(),
  ];

  foreach ($hooks as $hook) {
    $hook->run($entity, $action, $params, $permissions);
  }
}

/**
 * Implements hook_civicrm_pageRun().
 */
function civicase_civicrm_pageRun(&$page) {
  $hooks = [
    new CRM_Civicase_Hook_PageRun_ViewCasePageRedirect(),
    new CRM_Civicase_Hook_PageRun_AddCaseAngularPageResources(),
    new CRM_Civicase_Hook_PageRun_AddContactPageSummaryResources(),
    new CRM_Civicase_Hook_PageRun_CaseCategoryCustomGroupListing(),
  ];

  foreach ($hooks as $hook) {
    $hook->run($page);
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
  $hooks = [
    new CRM_Civicase_Hook_NavigationMenu_AlterForCaseMenu(),
  ];

  foreach ($hooks as $hook) {
    $hook->run($menu);
  }
}

/**
 * Implements hook_civicrm_selectWhereClause().
 */
function civicase_civicrm_selectWhereClause($entity, &$clauses) {
  if ($entity === 'Case') {
    unset($clauses['id']);
  }
}

/**
 * Implements hook_civicrm_entityTypes().
 */
function civicase_civicrm_entityTypes(&$entityTypes) {
  $entityTypes[] = [
    'name'  => 'CaseContactLock',
    'class' => 'CRM_Civicase_DAO_CaseContactLock',
    'table' => 'civicase_contactlock',
  ];

  _civicase_add_case_category_case_type_entity($entityTypes);
}

/**
 * Implements hook_civicrm_queryObjects().
 */
function civicase_civicrm_queryObjects(&$queryObjects, $type) {
  if ($type == 'Contact') {
    $queryObjects[] = new CRM_Civicase_BAO_Query_ContactLock();
    $queryObjects[] = new CRM_Civicase_BAO_Query_CaseCategory();
  }
}

/**
 * Implements hook_civicrm_permission_check().
 */
function civicase_civicrm_permission_check($permission, &$granted, $contactId) {
  $hooks = [
    new CRM_Civicase_Hook_PermissionCheck_ActivityPageView(),
    new CRM_Civicase_Hook_PermissionCheck_CaseCategory(),
  ];

  foreach ($hooks as $hook) {
    $hook->run($permission, $granted, $contactId);
  }
}

/**
 * Implements hook_civicrm_preProcess().
 */
function civicase_civicrm_preProcess($formName, &$form) {
  $hooks = [
    new CRM_Civicase_Hook_PreProcess_CaseCategoryWordReplacementsForNewCase(),
    new CRM_Civicase_Hook_PreProcess_CaseCategoryWordReplacementsForChangeCase(),
    new CRM_Civicase_Hook_PreProcess_AddCaseAdminSettings(),
  ];

  foreach ($hooks as $hook) {
    $hook->run($formName, $form);
  }
}

/**
 * Adds Case Type Category field to the Case Type entity DAO.
 *
 * @param array $entityTypes
 *   Entity types array.
 */
function _civicase_add_case_category_case_type_entity(array &$entityTypes) {
  $entityTypes['CRM_Case_DAO_CaseType']['fields_callback'][] = function ($class, &$fields) {
    $fields['case_type_category'] = [
      'name' => 'case_type_category',
      'type' => CRM_Utils_Type::T_INT,
      'title' => ts('Case Type Category'),
      'description' => ts('FK to a civicrm_option_value (case_type_categories)'),
      'required' => FALSE,
      'where' => 'civicrm_case_type.case_type_category',
      'table_name' => 'civicrm_case_type',
      'entity' => 'CaseType',
      'bao' => 'CRM_Case_BAO_CaseType',
      'localizable' => 1,
      'html' => [
        'type' => 'Select',
      ],
      'pseudoconstant' => [
        'optionGroupName' => 'case_type_categories',
        'optionEditPath' => 'civicrm/admin/options/case_type_categories',
      ],
    ];
  };
}

/**
 * Implements hook_civicrm_alterMailParams().
 */
function civicase_civicrm_alterMailParams(&$params, $context) {
  $hooks = [
    new CRM_Civicase_Hook_alterMailParams_SubjectCaseTypeCategoryProcessor(),
  ];

  foreach ($hooks as &$hook) {
    $hook->run($params, $context);
  }
}
