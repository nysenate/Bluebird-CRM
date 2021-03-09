<?php

/**
 * Case setting helper trait.
 */
trait CRM_Civicase_Helpers_CaseSettingsTrait {

  /**
   * Toggle the single case role per type setting.
   *
   * @param bool $isActive
   *   Whether active or not.
   */
  private function setSingleCaseRoleSetting($isActive = TRUE) {
    Civi::settings()->set('civicaseSingleCaseRolePerType', $isActive);
  }

  /**
   * Toggle the multi case client setting.
   *
   * @param bool $isActive
   *   Whether active or not.
   */
  private function setMultiClientCaseSetting($isActive = TRUE) {
    Civi::settings()->set('civicaseAllowMultipleClients', $isActive);
  }

}
