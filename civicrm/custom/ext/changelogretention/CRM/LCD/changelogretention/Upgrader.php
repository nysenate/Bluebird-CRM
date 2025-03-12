<?php

/**
 * Collection of upgrade steps.
 */
class CRM_LCD_changelogretention_Upgrader extends CRM_Extension_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Example: Run an external SQL script when the module is installed.
   */
  public function install() {
    //$this->executeSqlFile('sql/myinstall.sql');

    $dsn = defined('CIVICRM_LOGGING_DSN') ? DB::parseDSN(CIVICRM_LOGGING_DSN) : DB::parseDSN(CIVICRM_DSN);
    $loggingDB = $dsn['database']; //logging database

    $sql = "
    CREATE TABLE IF NOT EXISTS `{$loggingDB}`.`civicrm_logretention_log` (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `action` ENUM('purge') NOT NULL default 'purge' COMMENT 'type of action taken',
      `log_table` varchar(128) NOT NULL,
      `details` TEXT NULL COMMENT 'details/contextual data regarding the action.',
      `action_date` datetime NOT NULL DEFAULT NOW() COMMENT 'when the action occurred, which could be different than create_date',
      `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      INDEX `idx_action` (`action`),
      INDEX `log_table` (`log_table`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ";
    //Civi::log()->debug('install', ['$sql' => $sql]);
    CRM_Core_DAO::executeQuery($sql);
  }

  /**
   * Upgrade to version 2
   * - add action field
   * - add details field
   * - add action_date field
   * - rename log_date to create_date
   * - deprecate / modify log_id to allow null
   * - drop index on log_id
   * - deprecate / modify log_completed to all null
   * - drop index on log_completed
   */
  public function upgrade_2000(): bool {

    $this->ctx->log->info('Applying update 2000');

    $this->ctx->log->info('Updating civicrm_logretention_log');

    // change format of logretention_log table
    $dsn = defined('CIVICRM_LOGGING_DSN') ? DB::parseDSN(CIVICRM_LOGGING_DSN) : DB::parseDSN(CIVICRM_DSN);
    $loggingDB = $dsn['database']; //logging database

    $result = CRM_Core_DAO::executeQuery( "
        ALTER TABLE `{$loggingDB}`.`civicrm_logretention_log`
         ADD COLUMN `action` ENUM('purge') NOT NULL default 'purge' COMMENT 'type of action taken' AFTER `id`,
         ADD COLUMN `details` TEXT NULL COMMENT 'details/contextual data regarding the action.' AFTER `action`,
         ADD COLUMN `action_date` datetime NOT NULL DEFAULT NOW() COMMENT 'when the action occurred, which could be different than create_date' AFTER `details`,
         RENAME COLUMN `log_date` TO `create_date`,
         MODIFY COLUMN `log_table` varchar(128) NOT NULL AFTER `action`,
         MODIFY COLUMN `log_id` int(11) NOT NULL DEFAULT 0 COMMENT 'deprecated' AFTER `create_date`,
         MODIFY COLUMN `log_completed` tinyint(1) NULL DEFAULT 1 COMMENT 'deprecated' AFTER `log_id`,
         DROP INDEX log_id,
         DROP INDEX log_completed,
         ADD INDEX idx_civicrm_logretention_log_action (action)
      ");
    if (is_a($result, 'DB_Error')) {
      throw new Exception($result->getMessage());
    }
    // probably makes the most sense to use log_date for action_date in older
    // records.
    $result = CRM_Core_DAO::executeQuery( "
        UPDATE `{$loggingDB}`.`civicrm_logretention_log`
         SET action_date = create_date
      ");
    if (is_a($result, 'DB_Error')) {
      throw new Exception($result->getMessage());
    }
    
    return true;
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
   * Example: Run a couple simple queries.
   *
   * @return TRUE on success
   * @throws Exception
   *
  public function upgrade_4200() {
    $this->ctx->log->info('Applying update 4200');
    CRM_Core_DAO::executeQuery('UPDATE foo SET bar = "whiz"');
    CRM_Core_DAO::executeQuery('DELETE FROM bang WHERE willy = wonka(2)');
    return TRUE;
  } // */


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
