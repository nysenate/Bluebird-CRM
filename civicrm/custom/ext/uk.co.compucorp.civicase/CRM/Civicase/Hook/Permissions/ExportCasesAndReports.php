<?php

/**
 * Adds cases and reports export permissions.
 */
class CRM_Civicase_Hook_Permissions_ExportCasesAndReports {

  /**
   * Name of the permission for exporting cases and reports.
   *
   * @var string Permission name
   */
  const PERMISSION_NAME = 'access export action';

  /**
   * Civi permissions array.
   *
   * @var array
   */
  private $permissions;

  /**
   * CRM_Civicase_Hook_Permissions_CaseCategory constructor.
   *
   * @param array $permissions
   *   Civi permissions array.
   */
  public function __construct(array &$permissions) {
    $this->permissions = &$permissions;
  }

  /**
   * Run function.
   */
  public function run() {
    $this->addExportPermission();
  }

  /**
   * Adds the export permission.
   */
  private function addExportPermission() {
    $this->permissions[self::PERMISSION_NAME] = [
      'CiviCase: ' . self::PERMISSION_NAME,
      ts('Access export action in manage cases and reports'),
    ];
  }

}
