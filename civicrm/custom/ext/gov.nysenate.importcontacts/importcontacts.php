<?php

require_once 'importcontacts.civix.php';
// phpcs:disable
use CRM_Importcontacts_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function importcontacts_civicrm_config(&$config) {
  _importcontacts_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function importcontacts_civicrm_install() {
  _importcontacts_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function importcontacts_civicrm_postInstall() {
  _importcontacts_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function importcontacts_civicrm_uninstall() {
  _importcontacts_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function importcontacts_civicrm_enable() {
  _importcontacts_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function importcontacts_civicrm_disable() {
  _importcontacts_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function importcontacts_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _importcontacts_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function importcontacts_civicrm_entityTypes(&$entityTypes) {
  _importcontacts_civix_civicrm_entityTypes($entityTypes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function importcontacts_civicrm_preProcess($formName, &$form) {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
//function importcontacts_civicrm_navigationMenu(&$menu) {
//  _importcontacts_civix_insert_navigation_menu($menu, 'Mailings', [
//    'label' => E::ts('New subliminal message'),
//    'name' => 'mailing_subliminal_message',
//    'url' => 'civicrm/mailing/subliminal',
//    'permission' => 'access CiviMail',
//    'operator' => 'OR',
//    'separator' => 0,
//  ]);
//  _importcontacts_civix_navigationMenu($menu);
//}

function importcontacts_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Contact_Import_Form_DataSource') {
    $dataSource = $form->getElement('dataSource');
    $dataSource->freeze();
  }

  if ($formName == 'CRM_Contact_Import_Form_Summary') {
    //collect the set of fuzzy rules to show the user
    $dao = new CRM_Dedupe_DAO_RuleGroup();
    $dao->contact_type = 'Individual';
    $dao->find();
    $form->dedupeRules = [];
    while ($dao->fetch()) {
      $form->dedupeRules[$dao->id] = $dao->title;
    }

    $importGroupId = $form->get('importGroupId');
    $form->assign( 'importGroupId', $importGroupId );

    $form->add('select', 'dedupeRules', ts('Dedupe with a different rule'), $form->dedupeRules, FALSE, []);
  }
}

function importcontacts_civicrm_postProcess($formName, &$form) {
  if ($formName == 'CRM_Contact_Import_Form_DataSource') {
    //make a record for the preferences with a unique name and user id
    $cid = CRM_Core_Session::getLoggedInContactID();

    $importJobName = "import_job_".date('Ymd-His');
    $importTableName = "civicrm_tmp_{$importJobName}";
    $form->add('hidden', 'import_job_table', $importTableName);

    // Make a new entry into the civicrm_importer_jobs table. This should save all import job related
    // Settings so that the job can be resumed by anyone with permissions at a later point in time.
    // I'll be adding settings here as I discover their location and relevance to the process.
    $sql = "
      INSERT INTO civicrm_importer_jobs
       (name, table_name, source_file, file_type, field_separator, created_on, created_by)
      VALUES
       ('$importJobName',
        '{$importTableName}',
        '{$form->_params['uploadFile']['name']}',
        '{$form->_params['dataSource']}',
        '{$form->_params['fieldSeparator']}',
        NOW(),
        {$cid}
       )
    ";
  }

  if ($formName == 'CRM_Contact_Import_Form_Preview') {
    //Civi::log()->debug(__FUNCTION__, ['form' => $form]);

    return;

    //TODO after processing should add all contacts to a hidden group
    //old code below

    // NYSS 4053
    // Create a new group and add all the newly imported contacts to it
    // Make sure to save the new group id to the import record
    /*$group_params = [
      'title' => substr($this->_tableName, 19),
      'description' => '',
      'is_active' => TRUE,
      'is_hidden' => TRUE,
      'group_type' => 'imported_contacts'
    ];
    $group = CRM_Contact_BAO_Group::create($group_params);
    CRM_Contact_BAO_GroupContact::addContactsToGroup($contactIds, $group->id);
    $form->set('importGroupId', $group->id);
    CRM_Core_DAO::executeQuery("
      UPDATE civicrm_importer_jobs
      SET contact_group_id={$group->id}, created_on = NOW()
      WHERE table_name='{$this->_tableName}'
    ");*/
  }
}

function importcontacts_civicrm_import($object, $usage, &$objectRef, &$params) {
  return;

  Civi::log()->debug(__FUNCTION__, [
    //'object' => $object,
    'usage' => $usage,
    //'objectRef' => $objectRef,
    'params' => $params,
    '_REQUEST' => $_REQUEST,
  ]);

  //during import, accept special tag_import column for processing
  $cType = $objectRef->_contactType;
  $dupeType = $objectRef->_onDuplicate;
  $contactID = $params['contactID'];
  $importID = $params['importID'];
  $importTempTable = $params['importTempTable'];
  $fields = $params['fields'];

  //only proceed if tag_import exists in import
  if (CRM_Core_DAO::checkFieldExists($importTempTable, 'tag_import')) {
    $sqlTags = "
      SELECT tag_import
      FROM $importTempTable
      WHERE _id = $importID;
    ";
    $taglist = CRM_Core_DAO::singleValueQuery( $sqlTags );

    if ($taglist) {
      $keywords = explode( '|', $taglist );

      foreach ($keywords as $keyword) {
        try {
          $params = [
            'name' => $keyword,
            'parent_id' => '296'
          ];

          //lookup tag; create new if nonexist
          $tag = civicrm_api3('Tag', 'get', $params);

          if (empty($tag['count'])) {
            $tag = civicrm_api3('Tag', 'create', $params);
            $tagid = $tag['tag_id'];
          } else {
            $tagid = $tag['id'];
          }

          //only add tag to contact if not already present
          $entityTags = CRM_Core_BAO_EntityTag::getTag($contactID);
          if (!in_array($tagid, $entityTags)) {
            $entityParams = [
              'tag_id' => $tagid,
              'contact_id' => $contactID
            ];
            civicrm_api3('EntityTag', 'create', $entityParams);
          }
        }
        catch (CiviCRM_API3_Exception $e) {}
      }
    }
  }
}
