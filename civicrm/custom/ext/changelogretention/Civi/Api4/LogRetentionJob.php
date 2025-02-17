<?php

namespace Civi\Api4;

/**
 * Change Log Retention
 */
class LogRetentionJob extends Generic\AbstractEntity {
  /**
   * @param bool $checkPermissions
   * @return Action\ChangeLogRetention\PurgeData
   */
  public static function purgeData(bool $checkPermissions = TRUE) {
    return (new Action\ChangeLogRetention\PurgeData('LogRetentionJob', __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  public static function getFields($checkPermissions = TRUE) {
    return (new Generic\BasicGetFieldsAction(__CLASS__, __FUNCTION__, function($getFieldsAction) {
      return [

      ];
    }))->setCheckPermissions($checkPermissions);
  }
}