<?php
use CRM_Contactlayout_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_Contactlayout_Upgrader extends CRM_Contactlayout_Upgrader_Base {

  /**
   * Install script
   */
  public function install() {
  }

  /**
   * Called just after installation.
   */
  public function postInstall() {
    // Add menu item for contact summary editor.
    try {
      $parent = Civi\Api4\Navigation::get()
        ->addWhere('name', '=', 'Customize Data and Screens')
        ->setCheckPermissions(FALSE)
        ->setLimit(1)
        ->execute()
        ->first();
      Civi\Api4\Navigation::create()
        ->setCheckPermissions(FALSE)
        ->addValue('label', E::ts('Contact Summary Layouts'))
        ->addValue('name', 'contact_summary_editor')
        ->addValue('permission', 'administer CiviCRM')
        ->addValue('url', 'civicrm/a/#/contact-summary-editor')
        ->addValue('parent_id', $parent['id'])
        ->execute();
    }
    catch (Exception $e) {
      // Couldn't create menu item.
    }
  }

  /**
   * Uninstall routine.
   */
  public function uninstall() {
    try {
      Civi\Api4\Navigation::delete()
        ->setCheckPermissions(FALSE)
        ->addWhere('name', '=', 'contact_summary_editor')
        ->execute();
    }
    catch (Exception $e) {
      // Couldn't delete menu item.
    }
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

    $this->addTask(E::ts('Process first step'), 'processPart1', $arg1, $arg2);
    $this->addTask(E::ts('Process second step'), 'processPart2', $arg3, $arg4);
    $this->addTask(E::ts('Process second step'), 'processPart3', $arg5);
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
