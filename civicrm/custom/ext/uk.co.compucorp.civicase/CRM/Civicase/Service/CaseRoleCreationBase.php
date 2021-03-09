<?php

/**
 * Case role creation base class.
 */
class CRM_Civicase_Service_CaseRoleCreationBase {

  /**
   * Whether the Case multiclient setting is active or not.
   *
   * @var bool
   */
  protected $isMultiClient;

  /**
   * Whether the single case role per type setting is active or not.
   *
   * @var bool
   */
  protected $isSingleCaseRole;

  /**
   * CRM_Civicase_Service_CaseRoleCreationBase constructor.
   */
  public function __construct() {
    $this->isMultiClient = (bool) Civi::settings()->get('civicaseAllowMultipleClients');
    $this->isSingleCaseRole = (bool) Civi::settings()->get('civicaseSingleCaseRolePerType');
  }

}
