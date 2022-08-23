<?php

namespace Civi\Api4;

/**
 * Tutorial entity - onscreen walk-throughs to help new users learn CiviCRM.
 *
 * @searchable none
 * @see https://lab.civicrm.org/extensions/tutorial
 */
class Tutorial extends Generic\BasicEntity {

  protected static $getter = ['CRM_Tutorial_BAO_Tutorial', 'get'];

  protected static $setter = ['CRM_Tutorial_BAO_Tutorial', 'create'];

  protected static $deleter = ['CRM_Tutorial_BAO_Tutorial', 'delete'];

  /**
   * @param bool $checkPermissions
   * @return Generic\BasicGetFieldsAction
   */
  public static function getFields($checkPermissions = TRUE) {
    return (new Generic\BasicGetFieldsAction(__CLASS__, __FUNCTION__, function() {
      return \CRM_Tutorial_BAO_Tutorial::fields();
    }))->setCheckPermissions($checkPermissions);
  }

  /**
   * Mark tutorial(s) as viewed by the current user
   *
   * @param bool $checkPermissions
   * @return Generic\BasicBatchAction
   */
  public static function mark($checkPermissions = TRUE) {
    return (new Generic\BasicBatchAction(__CLASS__, __FUNCTION__, 'id', ['CRM_Tutorial_BAO_Tutorial', 'mark']))
      ->setCheckPermissions($checkPermissions);
  }

  public static function permissions():array {
    return [
      'mark' => ['access CiviCRM'],
    ];
  }

}
