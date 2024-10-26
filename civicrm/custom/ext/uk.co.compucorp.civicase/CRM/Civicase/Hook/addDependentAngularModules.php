<?php

/**
 * Class CRM_Civicase_Hook_addDependentAngularModules
 */
class CRM_Civicase_Hook_addDependentAngularModules {

  /**
   * This hook allows other extensions add modules dependent on Civicase.
   *
   * @param array $dependentModules
   *   Modules dependent on Civicase.
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
      'addCiviCaseDependentAngularModules'
    );

    return $dependentModules;
  }

}

