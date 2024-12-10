<?php
namespace Civi\Api4;

class WebIntegration extends Generic\AbstractEntity {
  public static function getFields($checkPermissions = TRUE) {
    return (new Generic\BasicGetFieldsAction(__CLASS__, __FUNCTION__, function($getFieldsAction) {
      return [
       /* [
          'name' => 'activity_id',
          'data_type' => 'Integer',
          'description' => 'Activity ID. Will limit migration to the specified Activity.',
        ],
      */
      ];
    }))->setCheckPermissions($checkPermissions);
  }
}