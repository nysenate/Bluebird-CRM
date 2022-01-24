<?php

/**
 * Collection of upgrade steps.
 */
class CRM_Mosaico_Upgrader extends CRM_Mosaico_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Example: Run an external SQL script when the module is installed.
   *
  public function install() {
    $this->executeSqlFile('sql/myinstall.sql');
  }

  /**
   * Example: Run an external SQL script when the module is uninstalled.
   *
  public function uninstall() {
   $this->executeSqlFile('sql/myuninstall.sql');
  }

  /**
   * Example: Run a simple query when a module is enabled.
   *
  public function enable() {
    CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 1 WHERE bar = "whiz"');
  }

  /**
   * Example: Run a simple query when a module is disabled.
   *
  public function disable() {
    CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 0 WHERE bar = "whiz"');
  }

  /**
   * Add table `civicrm_mosaico_template`.
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_4700() {
    $this->ctx->log->info('Applying update 4700');
    CRM_Core_DAO::executeQuery('CREATE TABLE `civicrm_mosaico_template` (
      `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT \'Unique Template ID\',
      `title` varchar(64)    COMMENT \'Title\',
      `base` varchar(64)    COMMENT \'Name of the Mosaico base template (e.g. versafix-1)\',
      `html` longtext    COMMENT \'Fully renderd HTML\',
      `metadata` longtext    COMMENT \'Mosaico metadata (JSON)\',
      `content` longtext    COMMENT \'Mosaico content (JSON)\' ,
       PRIMARY KEY ( `id` )
    )  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;
    ');
    return TRUE;
  }

  /**
   * One of the 2.x-alpha releases was missing the file `Mosaico.setting.php`.
   * If you installed on Civi v4.6 and tried to change the layout setting while
   * this file was missing, you could have a boinked record in "civicrm_setting".
   */
  public function upgrade_4701() {
    $this->ctx->log->info('Applying update 4701');
    if (CRM_Core_DAO::checkFieldExists('civicrm_setting', 'group_name')) {
      CRM_Core_DAO::executeQuery('UPDATE civicrm_setting SET group_name = "Mosaico Preferences" WHERE name="mosaico_layout"');
    }
    return TRUE;
  }

  /**
   * Extend civicrm_mosaico_template.title.
   */
  public function upgrade_4702() {
    $this->ctx->log->info('Applying update 4702');
    CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_mosaico_template MODIFY title varchar(255)');
    return TRUE;
  }

  /**
   * Add menu for traditional mailing.
   */
  public function upgrade_4703() {
    $this->ctx->log->info('Applying update 4703');
    $domainId = CRM_Core_Config::domainID();

    civicrm_api3('Navigation', 'create', [
      'sequential' => 1,
      'domain_id' => $domainId,
      'url' => "civicrm/a/#/mailing/new/traditional",
      'permission' => "access CiviMail,create mailings",
      'label' => "New Mailing (Traditional)",
      'permission_operator' => "OR",
      'has_separator' => 0,
      'is_active' => 1,
      'parent_id' => "Mailings",
    ]);

    CRM_Core_Invoke::rebuildMenuAndCaches(TRUE);

    return TRUE;
  }

  /**
   * Add menu for traditional mailing.
   */
  public function upgrade_4704() {
    $this->ctx->log->info('Applying update 4704');

    CRM_Core_DAO::executeQuery('
      ALTER TABLE civicrm_mosaico_template
      ADD COLUMN `msg_tpl_id` int unsigned NULL COMMENT \'FK to civicrm_msg_template.\'
    ');

    CRM_Core_DAO::executeQuery('
      ALTER TABLE civicrm_mosaico_template
      ADD CONSTRAINT FK_civicrm_mosaico_template_msg_tpl_id
      FOREIGN KEY (`msg_tpl_id`) REFERENCES `civicrm_msg_template`(`id`)
      ON DELETE SET NULL
    ');

    return TRUE;
  }

  /**
   * Example: Run an external SQL script.
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4201() {
    $this->ctx->log->info('Applying update 4201');
    // this path is relative to the extension base dir
    $this->executeSqlFile('sql/upgrade_4201.sql');
    return TRUE;
  } // */


  /**
   * Example: Run a slow upgrade process by breaking it up into smaller chunk.
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4202() {
    $this->ctx->log->info('Planning update 4202'); // PEAR Log interface

    $this->addTask(ts('Process first step'), 'processPart1', $arg1, $arg2);
    $this->addTask(ts('Process second step'), 'processPart2', $arg3, $arg4);
    $this->addTask(ts('Process second step'), 'processPart3', $arg5);
    return TRUE;
  }
  public function processPart1($arg1, $arg2) { sleep(10); return TRUE; }
  public function processPart2($arg3, $arg4) { sleep(10); return TRUE; }
  public function processPart3($arg5) { sleep(10); return TRUE; }
  // */


  /**
   * Example: Run an upgrade with a query that touches many (potentially
   * millions) of records by breaking it up into smaller chunks.
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4203() {
    $this->ctx->log->info('Planning update 4203'); // PEAR Log interface

    $minId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(min(id),0) FROM civicrm_contribution');
    $maxId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(max(id),0) FROM civicrm_contribution');
    for ($startId = $minId; $startId <= $maxId; $startId += self::BATCH_SIZE) {
      $endId = $startId + self::BATCH_SIZE - 1;
      $title = ts('Upgrade Batch (%1 => %2)', array(
        1 => $startId,
        2 => $endId,
      ));
      $sql = '
        UPDATE civicrm_contribution SET foobar = whiz(wonky()+wanker)
        WHERE id BETWEEN %1 and %2
      ';
      $params = array(
        1 => array($startId, 'Integer'),
        2 => array($endId, 'Integer'),
      );
      $this->addTask($title, 'executeSql', $sql, $params);
    }
    return TRUE;
  } // */

}
