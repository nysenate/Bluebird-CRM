<?php
use CRM_NYSS_Mail_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_NYSS_Mail_Upgrader extends CRM_Extension_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Example: Run an external SQL script when the module is installed.
   *
  public function install() {
    $this->executeSqlFile('sql/myinstall.sql');
  }

  /**
   * Example: Work with entities usually not available during the install step.
   *
   * This method can be used for any post-install tasks. For example, if a step
   * of your installation depends on accessing an entity that is itself
   * created during the installation (e.g., a setting or a managed entity), do
   * so here to avoid order of operation problems.
   */
  // public function postInstall() {
  //  $customFieldId = civicrm_api3('CustomField', 'getvalue', array(
  //    'return' => array("id"),
  //    'name' => "customFieldCreatedViaManagedHook",
  //  ));
  //  civicrm_api3('Setting', 'create', array(
  //    'myWeirdFieldSetting' => array('id' => $customFieldId, 'weirdness' => 1),
  //  ));
  // }

  /**
   * Example: Run an external SQL script when the module is uninstalled.
   */
  // public function uninstall() {
  //  $this->executeSqlFile('sql/myuninstall.sql');
  // }

  /**
   * Example: Run a simple query when a module is enabled.
   */
  // public function enable() {
  //  CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 1 WHERE bar = "whiz"');
  // }

  /**
   * Example: Run a simple query when a module is disabled.
   */
  // public function disable() {
  //   CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 0 WHERE bar = "whiz"');
  // }

  /**
   * Example: Run a couple simple queries.
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_1100(): bool {
    $this->ctx->log->info('Applying update 1100');

    try {
      $existingOpts = civicrm_api3('OptionValue', 'get', ['option_group_id' => "mailing_template_category"]);
      foreach ($existingOpts['values'] as $opt) {
        civicrm_api3('OptionValue', 'delete', ['id' => $opt['id']]);
      }
    }
    catch (CiviCRM_API3_Exception $e) {}

    $cats = [
      [
        'label' => 'Budget Updates',
        'name' => 'budget_updates',
        'value' => 1,
      ],
      [
        'label' => 'Community & Special Event Notices',
        'name' => 'community_special_event_notices',
        'value' => 2,
      ],
      [
        'label' => 'Emergency & Public Safety Alerts',
        'name' => 'emergency_public_safety_alerts',
        'value' => 3,
      ],
      [
        'label' => 'Issue/Bill Updates',
        'name' => 'issue_bill_updates',
        'value' => 4,
      ],
      [
        'label' => 'Newsletter',
        'name' => 'newsletter',
        'value' => 5,
      ],
      [
        'label' => 'Press Releases',
        'name' => 'press_releases',
        'value' => 6,
      ],
    ];

    foreach ($cats as $cat) {
      try {
        civicrm_api3('OptionValue', 'create', [
          'option_group_id' => "mailing_template_category",
          'label' => $cat['label'],
          'value' => $cat['value'],
          'name' => $cat['name'],
          'weight' => $cat['value'],
          'grouping' => NULL,
          'filter' => 0,
          'is_default' => 0,
          'is_reserved' => 0,
          'is_active' => 1,
          'visibility_id' => NULL,
          'component_id' => NULL,
          'domain_id' => NULL,
          'icon' => NULL,
        ]);
      }
      catch (CiviCRM_API3_Exception $e) {}
    }

    return TRUE;
  }

  public function upgrade_1200(): bool {
    $this->ctx->log->info('Applying update 1200 (v1.2)');

    \Civi\Api4\Job::create(FALSE)
      ->addValue('run_frequency', 'Daily')
      ->addValue('name', 'Process Mosaico Thumbnails')
      ->addValue('api_entity', 'Nyss')
      ->addValue('api_action', 'processmosaicothumbnails')
      ->addValue('is_active', TRUE)
      ->addValue('domain_id', 1)
      ->execute();

    return TRUE;
  }

  /**
   * Example: Run an external SQL script.
   *
   * @return TRUE on success
   * @throws Exception
   */
  // public function upgrade_4201(): bool {
  //   $this->ctx->log->info('Applying update 4201');
  //   // this path is relative to the extension base dir
  //   $this->executeSqlFile('sql/upgrade_4201.sql');
  //   return TRUE;
  // }


  /**
   * Example: Run a slow upgrade process by breaking it up into smaller chunk.
   *
   * @return TRUE on success
   * @throws Exception
   */
  // public function upgrade_4202(): bool {
  //   $this->ctx->log->info('Planning update 4202'); // PEAR Log interface

  //   $this->addTask(E::ts('Process first step'), 'processPart1', $arg1, $arg2);
  //   $this->addTask(E::ts('Process second step'), 'processPart2', $arg3, $arg4);
  //   $this->addTask(E::ts('Process second step'), 'processPart3', $arg5);
  //   return TRUE;
  // }
  // public function processPart1($arg1, $arg2) { sleep(10); return TRUE; }
  // public function processPart2($arg3, $arg4) { sleep(10); return TRUE; }
  // public function processPart3($arg5) { sleep(10); return TRUE; }

  /**
   * Example: Run an upgrade with a query that touches many (potentially
   * millions) of records by breaking it up into smaller chunks.
   *
   * @return TRUE on success
   * @throws Exception
   */
  // public function upgrade_4203(): bool {
  //   $this->ctx->log->info('Planning update 4203'); // PEAR Log interface

  //   $minId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(min(id),0) FROM civicrm_contribution');
  //   $maxId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(max(id),0) FROM civicrm_contribution');
  //   for ($startId = $minId; $startId <= $maxId; $startId += self::BATCH_SIZE) {
  //     $endId = $startId + self::BATCH_SIZE - 1;
  //     $title = E::ts('Upgrade Batch (%1 => %2)', array(
  //       1 => $startId,
  //       2 => $endId,
  //     ));
  //     $sql = '
  //       UPDATE civicrm_contribution SET foobar = whiz(wonky()+wanker)
  //       WHERE id BETWEEN %1 and %2
  //     ';
  //     $params = array(
  //       1 => array($startId, 'Integer'),
  //       2 => array($endId, 'Integer'),
  //     );
  //     $this->addTask($title, 'executeSql', $sql, $params);
  //   }
  //   return TRUE;
  // }

}
