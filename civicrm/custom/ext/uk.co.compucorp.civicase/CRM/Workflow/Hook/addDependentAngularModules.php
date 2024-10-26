<?php

/**
 * Creates a hook to add modules dependent on Workflow.
 */
class CRM_Workflow_Hook_addDependentAngularModules {

  /**
   * This hook allows other extensions add modules dependent on Workflow.
   *
   * @param array $dependentModules
   *   Modules dependent on Workflow.
   *
   * @return array
   *   Array of dependent modules.
   */
  public static function invoke(array $dependentModules) {
    CRM_Utils_Hook::singleton()->invoke(['dependentModules'],
      $dependentModules,
      CRM_Utils_Hook::$_nullObject,
      CRM_Utils_Hook::$_nullObject,
      CRM_Utils_Hook::$_nullObject,
      CRM_Utils_Hook::$_nullObject,
      CRM_Utils_Hook::$_nullObject,
      'addWorkflowDependentAngularModules'
    );

    return $dependentModules;
  }

}
