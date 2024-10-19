<?php
use CRM_NYSS_Contact_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_NYSS_Contact_Upgrader extends CRM_NYSS_Contact_Upgrader_Base {

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
    $this->ctx->log->info('Applying contact extension update 1100');

    try {
      civicrm_api3('CustomField', 'create', [
        'sequential' => 1,
        'custom_group_id' => "Additional_Constituent_Information",
        'name' => "preferred_pronouns",
        'label' => "Preferred Pronouns",
        'data_type' => "String",
        'html_type' => "Text",
        'is_required' => 0,
        'is_searchable' => 1,
        'is_active' => 1,
        'text_length' => 64,
        'column_name' => "preferred_pronouns",
      ]);
    }
    catch (CiviCRM_API3_Exception $e) {
      Civi::log()->debug(__METHOD__, ['e' => $e]);
    }

    //get custom field ID
    $cfId = CRM_Core_BAO_CustomField::getCustomFieldID('preferred_pronouns', 'Additional_Constituent_Information', TRUE);

    //store to profile
    try {
      civicrm_api3('UFField', 'create', [
        'debug' => 1,
        'uf_group_id' => "Contact_Summary_Individual",
        'field_name' => $cfId,
        'is_active' => 1,
        'is_view' => 0,
        'is_required' => 0,
        'weight' => 7,
        'visibility' => "User and User Admin Only",
        'is_searchable' => 0,
        'label' => "Preferred Pronoun",
        'field_type' => "Individual",
      ]);
    }
    catch (CiviCRM_API3_Exception $e) {
      Civi::log()->debug(__METHOD__, ['e' => $e]);
    }

    return TRUE;
  }

  public function upgrade_1200(): bool {
    $this->ctx->log->info('Applying contact extension update 1200');

    try {
      $cfDistrict = CRM_Core_BAO_CustomField::getCustomFieldID('New_York_City_Council', 'District_Information');
      civicrm_api3('CustomField', 'create', [
        'id' => $cfDistrict,
        'label' => 'City Council District',
      ]);

      $cfNeighborhood = CRM_Core_BAO_CustomField::getCustomFieldID('Neighborhood', 'District_Information');
      civicrm_api3('CustomField', 'create', [
        'id' => $cfNeighborhood,
        'is_active' => 0,
      ]);
    }
    catch (CiviCRM_API3_Exception $e) {
      Civi::log()->debug(__METHOD__, ['e' => $e]);
    }

    return TRUE;
  }

  public function upgrade_1300(): bool {
    $this->ctx->log->info('Contact extension update 1300: Guardianship relationship type');

    try {
      $existing = \Civi\Api4\RelationshipType::get(FALSE)
        ->selectRowCount()
        ->addWhere('name_a_b', '=', 'guardian_of')
        ->execute()
        ->countMatched();

      if ($existing) {
        Civi::log()->debug(__METHOD__, ['Contact 1300: Guardian relationship type already exists.']);
        return TRUE;
      }

      \Civi\Api4\RelationshipType::create(FALSE)
        ->addValue('name_a_b', 'guardian_of')
        ->addValue('label_a_b', 'Guardian of')
        ->addValue('name_b_a', 'guardian_is')
        ->addValue('label_b_a', 'Guardian is')
        ->addValue('contact_type_a', 'Individual')
        ->addValue('contact_type_b', 'Individual')
        ->addValue('is_reserved', FALSE)
        ->addValue('is_active', TRUE)
        ->execute();
    }
    catch (CiviCRM_API3_Exception $e) {
      Civi::log()->debug(__METHOD__, ['e' => $e]);
    }

    return TRUE;
  }

  public function upgrade_1400(): bool {
    $this->ctx->log->info('Contact extension update 1400: v3.7 upgrade');

    //correct bad data
    CRM_Core_DAO::executeQuery("
      UPDATE civicrm_activity
      SET activity_date_time = NULL
      WHERE activity_date_time LIKE '0000-00-00 00:00:00'
    ");

    //set entity_id default NULL (should have happened during an earlier release...)
    CRM_Core_DAO::executeQuery("
      ALTER TABLE `civicrm_managed`
          CHANGE `entity_id` `entity_id` INT(10) UNSIGNED NULL DEFAULT NULL
          COMMENT 'Soft foreign key to the referenced item.';
    ");

    //remove note_used_for option values that have since been migrated to activities
    \Civi\Api4\OptionValue::delete(FALSE)
      ->addWhere('option_group_id:name', '=', 'note_used_for')
      ->addWhere('value', 'IN', ['nyss_directmsg', 'nyss_contextmsg'])
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
