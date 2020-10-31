<?php

class CRM_Civicase_APIHelpers_CasesByManager {
  /**
   * Adds joins and conditions to the given query in order to filter cases by
   * manager.
   *
   * @param CRM_Utils_SQL_Select $query
   *   The SQL object reference.
   * @param Int|Array $caseManager
   *   The ID of the case manager.
   */
  public static function filter($query, $caseManager) {
    if (!is_array($caseManager)) {
      $caseManager = ['=' => $caseManager];
    }

    \Civi\CCase\Utils::joinOnRelationship($query, 'manager');
    $query->where(CRM_Core_DAO::createSQLFilter('manager.id', $caseManager));
  }
}
