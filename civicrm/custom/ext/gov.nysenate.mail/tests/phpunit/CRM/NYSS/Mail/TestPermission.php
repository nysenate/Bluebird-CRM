<?php
declare(strict_types = 1);

/**
 * Stand-in for the Drupal permission class, so tests can control the current
 * user's permissions without creating real Drupal users/roles.
 */
class CRM_NYSS_Mail_TestPermission extends CRM_Core_Permission_Drupal {

  /**
   * @var string[]
   */
  public array $granted_permissions = [];

  public function check($str, $userId = NULL) {
    if ($str === CRM_Core_Permission::ALWAYS_ALLOW_PERMISSION) {
      return TRUE;
    }
    return in_array($str, $this->granted_permissions, TRUE);
  }

}
