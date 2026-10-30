<?php
declare(strict_types = 1);

/**
 * Stand-in for the Drupal permission class, so tests can control the current
 * user's permissions and CMS roles without creating real Drupal users/roles.
 */
class CRM_NYSS_Dashboard_TestPermission extends CRM_Core_Permission_Drupal {

  /**
   * @var string[]
   */
  public array $granted_permissions = [];

  /**
   * @var string[]
   */
  public array $granted_roles = [];

  public function check($str, $userId = NULL) {
    if ($str === CRM_Core_Permission::ALWAYS_ALLOW_PERMISSION) {
      return TRUE;
    }
    return in_array($str, $this->granted_permissions, TRUE);
  }

  public function checkGroupRole($array) {
    return (bool) array_intersect((array) $array, $this->granted_roles);
  }

}
