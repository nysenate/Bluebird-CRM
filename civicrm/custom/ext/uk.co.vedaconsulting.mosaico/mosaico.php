<?php

require_once 'mosaico.civix.php';
use CRM_Mosaico_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function mosaico_civicrm_config(&$config) {
  _mosaico_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function mosaico_civicrm_xmlMenu(&$files) {
  _mosaico_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function mosaico_civicrm_install() {
  _mosaico_civix_civicrm_install();

  $schema = new CRM_Logging_Schema();
  $schema->fixSchemaDifferences();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function mosaico_civicrm_postInstall() {
  _mosaico_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function mosaico_civicrm_uninstall() {
  _mosaico_civix_civicrm_uninstall();

  $schema = new CRM_Logging_Schema();
  $schema->fixSchemaDifferences();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function mosaico_civicrm_enable() {
  _mosaico_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function mosaico_civicrm_disable() {
  _mosaico_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function mosaico_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _mosaico_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function mosaico_civicrm_managed(&$entities) {
  _mosaico_civix_civicrm_managed($entities);
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
function mosaico_civicrm_angularModules(&$angularModules) {
  $canRead = Civi::service('civi_api_kernel')->runAuthorize(
    'MosaicoTemplate', 'get', ['version' => 3, 'check_permissions' => 1]);
  if (!$canRead) {
    return;
  }
  _mosaico_civix_civicrm_angularModules($angularModules);
}

function mosaico_civicrm_alterAngular(\Civi\Angular\Manager $angular) {
  $changeSet = \Civi\Angular\ChangeSet::create('mosaico_subject_list')
    ->alterHtml('~/crmMailing/BlockMailing.html', function (phpQueryObject $doc) {
      $field = $doc->find('[name=subject]')->parent('[crm-ui-field]');
      $field->html('<crm-mosaico-subject-list crm-mailing="mailing"/>');
      $field->after('<div crm-ui-field="{name: \'subform.dist\', title: ts(\'Distribution\')}" ng-if="mailing.template_options.variants"><crm-mosaico-distribution crm-mailing="mailing" /></div>');
    });
  $angular->add($changeSet);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function mosaico_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _mosaico_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

function mosaico_civicrm_navigationMenu(&$params) {
  _mosaico_civix_insert_navigation_menu($params, 'Mailings', [
    'label' => E::ts('Mosaico Templates'),
    'name' => 'mosaico_templates',
    'permission' => 'edit message templates',
    'child' => [],
    'operator' => 'AND',
    'separator' => 0,
    'url' => CRM_Utils_System::url('civicrm/mosaico-template-list'),
  ]);

  _mosaico_civix_insert_navigation_menu($params, 'Mailings', [
    'label' => E::ts('New Mailing (Traditional)'),
    'name' => 'traditional_mailing',
    'permission' => 'access CiviMail,create mailings',
    'child' => [],
    'operator' => 'OR',
    'separator' => 0,
    'url' => CRM_Utils_System::url('civicrm/a/', NULL, TRUE, '/mailing/new/traditional'),
  ]);

  _mosaico_civix_insert_navigation_menu($params, 'Administer/CiviMail', [
    'label' => E::ts('Mosaico Settings'),
    'name' => 'mosaico_settings',
    'permission' => 'administer CiviCRM',
    'child' => [],
    'operator' => 'AND',
    'separator' => 0,
    'url' => CRM_Utils_System::url('civicrm/admin/setting/mosaico', 'reset=1', TRUE),
  ]);

  _mosaico_civix_navigationMenu($params);
}

/**
 * Implements hook_civicrm_alterAPIPermissions().
 *
 * Grant permissions for mosaico_base_template and mosaico_template based on
 * those granted for message_template.
 *
 * If no permissions are defined on message_template then we do not grant any
 * permissions.
 *
 * @link https://docs.civicrm.org/dev/en/master/hooks/hook_civicrm_alterAPIPermissions/
 */
function mosaico_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {

  if (isset($permissions['message_template'])) {
    // Permissions are defined for message_template, so use those to grant our
    // new permissions.
    $permissions['mosaico_base_template'] = $permissions['message_template'];
    $permissions['mosaico_template'] = $permissions['message_template'];
    if (isset($permissions['message_template']['create'])) {
      $permissions['mosaico_template']['clone'] = $permissions['message_template']['create'];
    }
  }
}

/**
 * Implements hook_civicrm_check().
 */
function mosaico_civicrm_check(&$messages) {
  //Make sure the ImageMagick library is loaded.
  try {
    Civi::service('mosaico_graphics');
  }
  catch (CRM_Mosaico_Graphics_Exception $e) {
    $messages[] = new CRM_Utils_Check_Message(
      'mosaico_graphics',
      E::ts('Mosaico requires a graphics driver such as PHP-ImageMagick or PHP-GD. For more information, see <a href="%1">Mosaico Settings</a>.', [
        1 => \CRM_Utils_System::url('civicrm/admin/setting/mosaico', 'reset=1'),
      ])
      . "<p><em>" . E::ts("Error: %1", [1 => $e->getMessage()]) . "</em></p>",
      E::ts('Graphics driver not available'),
      \Psr\Log\LogLevel::CRITICAL,
      'fa-chain-broken'
    );
  }
  if (!extension_loaded('fileinfo')) {
    $messages[] = new CRM_Utils_Check_Message('mosaico_fileinfo', E::ts('May experience mosaico template or thumbnail loading issues (404 errors).'), E::ts('PHP extension Fileinfo not loaded or enabled'));
  }
  if (!file_exists(E::path('packages/mosaico/dist/mosaico.min.js')) || !file_exists(E::path('packages/mosaico/dist/vendor/jquery.min.js'))) {
    $messages[] = new CRM_Utils_Check_Message(
      'mosaico_packages',
      E::ts('Mosaico requires dependencies in its "packages" folder. Please consult the README.md for current installation instructions.'),
      E::ts('Mosaico: Packages are missing'),
      \Psr\Log\LogLevel::CRITICAL,
      'fa-chain-broken'
    );
  }
  if (CRM_Mailing_Info::workflowEnabled()) {
    //NYSS
    /*$messages[] = new CRM_Utils_Check_Message(
      'mosaico_workflow',
      E::ts('CiviMail is configured to support advanced workflows. This is currently incompatible with the Mosaico mailer. Navigate to "Administer => CiviMail => CiviMail Component Settings" to disable it.'),
      E::ts('Advanced CiviMail workflows unsupported'),
      \Psr\Log\LogLevel::CRITICAL,
      'fa-chain-broken'
    );*/
  }
  if (!CRM_Extension_System::singleton()->getMapper()->isActiveModule('flexmailer')) {
    $messages[] = new CRM_Utils_Check_Message(
      'mosaico_flexmailer',
      E::ts('Mosaico uses FlexMailer for delivery. Please install the extension "org.civicrm.flexmailer".'),
      E::ts('FlexMailer required'),
      \Psr\Log\LogLevel::CRITICAL,
      'fa-chain-broken'
    );
  }
  else {
    $RECOMMENDED_FLEXMAILER = '1.1.1';
    $fmInfo = CRM_Extension_System::singleton()->getMapper()->keyToInfo('org.civicrm.flexmailer');
    if (version_compare($fmInfo->version, $RECOMMENDED_FLEXMAILER, '<')) {
      $messages[] = new CRM_Utils_Check_Message(
        'mosaico_flexmailer_ver',
        E::ts('The extension %1 expects %2 version <code>%3</code> or newer. Found version <code>%4</code>.', [
          1 => 'Mosaico',
          2 => 'FlexMailer',
          3 => $RECOMMENDED_FLEXMAILER,
          4 => $fmInfo->version,
        ]),
        E::ts('Outdated dependency'),
        \Psr\Log\LogLevel::WARNING
      );
    }
  }

  if (!empty($mConfig['BASE_URL'])) {
    // detect incorrect image upload url. (Note: Since v4.4.4, CRM_Utils_Check_Security has installed index.html placeholder.)
    $handle = curl_init($mConfig['BASE_URL'] . '/index.html');
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
    $response = curl_exec($handle);
    $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    if ($httpCode == 404) {
      $messages[] = new CRM_Utils_Check_Message('mosaico_base_url', E::ts('BASE_URL seems incorrect - %1. Images when uploaded, may not appear correctly as thumbnails. Make sure "Image Upload URL" is configured correctly with Administer » System Settings » Resouce URLs.', [1 => $mConfig['BASE_URL']]), E::ts('Incorrect image upload url'));
    }
  }

  $oldTplCount = CRM_Core_DAO::singleValueQuery('SELECT count(*) FROM civicrm_mosaico_msg_template');
  if ($oldTplCount > 0) {
    $messages[] = new CRM_Utils_Check_Message(
      'mosaico_migrate_1x',
      E::ts('Found %1 template(s) from CiviCRM-Mosaico v1.x. Use the <a href="%2">Migration Assistant</a> to load them in v2.x.', [
        1 => $oldTplCount,
        2 => CRM_Utils_System::url('civicrm/admin/mosaico/migrate', 'reset=1'),
      ]),
      E::ts('Mosaico: Migrate templates (1.x => 2.x)'),
      \Psr\Log\LogLevel::WARNING
    );
  }

  _mosaico_civicrm_check_dirs($messages);
}

function _mosaico_civicrm_check_dirs(&$messages) {
  $mConfig = CRM_Mosaico_Utils::getConfig();

  // Check if UPLOADS directory exists and create it if it doesn't
  if (!is_dir($mConfig['BASE_DIR'] . $mConfig['UPLOADS_DIR'])) {
    if (!mkdir($mConfig['BASE_DIR'] . $mConfig['UPLOADS_DIR'], 0775, TRUE)) {
      $messages[] = new CRM_Utils_Check_Message('mosaico_uploads_dir', E::ts('%1 not writable or configured.', [1 => $mConfig['BASE_DIR'] . $mConfig['UPLOADS_DIR']]), E::ts('UPLOADS_DIR not writable or configured'));
    }
  }
  elseif (!is_writable($mConfig['BASE_DIR'] . $mConfig['UPLOADS_DIR'])) {
    $messages[] = new CRM_Utils_Check_Message('mosaico_uploads_dir', E::ts('%1 not writable or configured.', [1 => $mConfig['BASE_DIR'] . $mConfig['UPLOADS_DIR']]), E::ts('UPLOADS_DIR not writable or configured'));
  }

  // Check if uploads/STATIC directory exists and create it if it doesn't
  if (!is_dir($mConfig['BASE_DIR'] . $mConfig['STATIC_DIR'])) {
    if (!mkdir($mConfig['BASE_DIR'] . $mConfig['STATIC_DIR'], 0775, TRUE)) {
      $messages[] = new CRM_Utils_Check_Message('mosaico_static_dir', E::ts('%1 not writable or configured.', [1 => $mConfig['BASE_DIR'] . $mConfig['STATIC_DIR']]), E::ts('STATIC_DIR not writable or configured'));
    }
  }
  elseif (!is_writable($mConfig['BASE_DIR'] . $mConfig['STATIC_DIR'])) {
    $messages[] = new CRM_Utils_Check_Message('mosaico_static_dir', E::ts('%1 not writable or configured.', [1 => $mConfig['BASE_DIR'] . $mConfig['STATIC_DIR']]), E::ts('STATIC_DIR not writable or configured'));
  }

  // Check if uploads/THUMBNAILS directory exists and create it if it doesn't
  if (!is_dir($mConfig['BASE_DIR'] . $mConfig['THUMBNAILS_DIR'])) {
    if (!mkdir($mConfig['BASE_DIR'] . $mConfig['THUMBNAILS_DIR'], 0775, TRUE)) {
      $messages[] = new CRM_Utils_Check_Message('mosaico_thumbnails_dir', E::ts('%1 not writable or configured.', [1 => $mConfig['BASE_DIR'] . $mConfig['THUMBNAILS_DIR']]), E::ts('THUMBNAILS_DIR not writable or configured'));
    }
  }
  elseif (!is_writable($mConfig['BASE_DIR'] . $mConfig['THUMBNAILS_DIR'])) {
    $messages[] = new CRM_Utils_Check_Message('mosaico_thumbnails_dir', E::ts('%1 not writable or configured.', [1 => $mConfig['BASE_DIR'] . $mConfig['THUMBNAILS_DIR']]), E::ts('THUMBNAILS_DIR not writable or configured'));
  }
}

/**
 * Convert dyanmic-y image URLs to static-y URLs.
 *
 * This is analogous to alterMailContent, but we only apply to Mosaico mailings.
 *
 * @param $content
 *   This parameter seems a bit confused.
 * @see CRM_Mosaico_MosaicoComposer
 */
function _mosaico_civicrm_alterMailContent(&$content) {

  // Mosaico templates have a few of their own tokens which are named differently from
  // CiviMail tokens. By treating these as aliases, we can get more compatibility between
  // Civi's delivery system and upstream Mosaico templates.
  $tokenAliases = [
    // '[profile_link]' => 'FIXME',
    '[show_link]' => '{mailing.viewUrl}&cid={contact.contact_id}&{contact.checksum}',
    '[subject]' => '{mailing.subject}',
    '[unsubscribe_link]' => '{action.unsubscribeUrl}',
  ];
  $content = str_replace(array_keys($tokenAliases), array_values($tokenAliases), $content);

  // Some existing and customized templates have awkward HTML <TITLE>s, which show up when viewing the mailing the browser.
  $content = preg_replace(';(\<head.*\<title\>\s*)TITLE(\s*\</title\>.*\</head\>);ms', '\\1{mailing.subject}\\2', $content, 1);
}

/**
 * Implements hook_civicrm_mailingTemplateTypes().
 *
 * @throws \CRM_Core_Exception
 */
function mosaico_civicrm_mailingTemplateTypes(&$types) {
  $messages = [];
  mosaico_civicrm_check($messages);
  foreach (array_keys($messages) as $key) {
    if ($messages[$key]->getLevel() <= 4) {
      unset($messages[$key]);
    }
  }

  // v4.6 compat
  require_once 'CRM/Mosaico/Utils.php';

  $editorUrl = empty($messages)
    ? CRM_Mosaico_Utils::getLayoutPath()
    : '~/crmMosaico/requirements.html';

  $types[] = [
    'name' => 'mosaico',
    'editorUrl' => $editorUrl,
    'weight' => -10,
  ];
}

/**
 * Implements hook_civicrm_entityTypes().
 */
function mosaico_civicrm_entityTypes(&$entityTypes) {
  $entityTypes[] = [
    'name' => 'MosaicoTemplate',
    'class' => 'CRM_Mosaico_DAO_MosaicoTemplate',
    'table' => 'civicrm_mosaico_template',
  ];
}

/**
 * Implements hook_civicrm_pre().
 */
function mosaico_civicrm_pre($op, $objectName, $id, &$params) {
  if ($objectName === 'Mailing' && $op === 'create') {
    if (isset($params['template_type']) && $params['template_type'] === 'mosaico') {
      $params['header_id'] = NULL;
      $params['footer_id'] = NULL;
    }
  }
}

/**
 * Implements hook_civicrm_container().
 */
function mosaico_civicrm_container(\Symfony\Component\DependencyInjection\ContainerBuilder $container) {
  $container->addResource(new \Symfony\Component\Config\Resource\FileResource(__FILE__));
  require_once 'CRM/Mosaico/Services.php';
  CRM_Mosaico_Services::registerServices($container);
}

/**
 * Implements hook_civicrm_searchTasks();
 */
function mosaico_civicrm_searchTasks($objectName, &$tasks) {
  if ($objectName == 'contact') {
    $tasks[] = [
      'title' => E::ts('Email - schedule/send via CiviMail (traditional)'),
      'class' => 'CRM_Mosaico_Form_Task_AdhocMailingTraditional',
    ];
  }
}

/**
 * Listen to prepare event and a mailing wrapper for the
 * Mailing.submit and Mailing.send_test api actions.
 *
 * @param \Civi\API\Event\PrepareEvent $event
 */
function mosaico_wrapMailingApi($event) {
  $a = $event->getApiRequest();
  switch ($a['entity'] . '.' . $a['action']) {
    case 'Mailing.submit':
      if (is_numeric($a['params']['id'])) {
        $abMux = \Civi::service('mosaico_ab_demux');
        $event->wrapApi([$abMux,'onSubmitMailing']);
      }
      break;

    case 'Mailing.send_test':
      if (is_numeric($a['params']['mailing_id'])) {
        $abMux = \Civi::service('mosaico_ab_demux');
        $event->wrapApi([$abMux, 'onSendTestMailing']);
      }
      break;
  }
}
