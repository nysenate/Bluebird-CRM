<?php
use CRM_ReportError_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_ReportError_Upgrader extends CRM_ReportError_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Example: Run an external SQL script when the module is installed.
   *
  public function install() {
    $this->executeSqlFile('sql/install.sql');
  }

  /**
   * This method can be used for any post-install tasks. For example, if a step
   * of your installation depends on accessing an entity that is itself
   * created during the installation (e.g., a setting or a managed entity), do
   * so here to avoid order of operation problems.
   *
  public function postInstall() {
  }

  /**
   * Run an external SQL script when the module is uninstalled.
   *
  public function uninstall() {
   $this->executeSqlFile('sql/uninstall.sql');
  }

  /**
   * Run a simple query when a module is enabled.
   *
  public function enable() {
  }

  /**
   * Run a simple query when a module is disabled.
   *
  public function disable() {
  }

  /**
   * Run a couple simple queries.
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
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_300() {
    $this->ctx->log->info('Planning update 300');
    $this->addTask(E::ts('Rename settings to avoid conflicts with other extensions'), 'processRenameSettings');
    return TRUE;
  }

  public function processRenameSettings() {
    $domain_id = CRM_Core_Config::domainID();

    $settings = [
      'noreferer_handle' => 'reporterror_noreferer_handle',
      'noreferer_pageid' => 'reporterror_noreferer_pageid',
      'noreferer_sendreport' => 'reporterror_noreferer_sendreport',
      'noreferer_handle_event' => 'reporterror_noreferer_handle_event',
      'noreferer_handle_eventid' => 'reporterror_noreferer_handle_eventid',
      'noreferer_sendreport_event' => 'reporterror_noreferer_sendreport_event',
      'mailto' => 'reporterror_mailto',
      'show_full_backtrace' => 'reporterror_show_full_backtrace',
      'show_post_data' => 'reporterror_show_post_data',
      'show_session_data' => 'reporterror_show_session_data',
      'bots_sendreport' => 'reporterror_bots_sendreport',
      'bots_404' => 'reporterror_bots_404',
      'bots_regexp' => 'reporterror_bots_regexp',
    ];

    foreach ($settings as $old => $new) {
      $value = Civi::settings()->get($old);
      Civi::settings()->set($new, $value);

      CRM_Core_DAO::executeQuery('DELETE FROM civicrm_setting WHERE name = %1 AND domain_id = %2', [
        1 => [$old, 'String'],
        2 => [$domain_id, 'Positive'],
      ]);
    }

    return TRUE;
  }

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
      $title = E::ts('Upgrade Batch (%1 => %2)', array(
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
