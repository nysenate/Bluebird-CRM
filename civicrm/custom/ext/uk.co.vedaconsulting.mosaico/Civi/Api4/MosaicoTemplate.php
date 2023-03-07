<?php
namespace Civi\Api4;

/**
 * MosaicoTemplate entity.
 *
 * Provided by the Mosaico extension.
 *
 * @package Civi\Api4
 */
class MosaicoTemplate extends Generic\DAOEntity {

  /**
   * @return array
   */
  public static function permissions():array {
    //NYSS 14657 alter perms
    return [
      'get' => ['access CiviCRM'],
      'create' => ['edit message templates', 'create mailings'],
      'update' => ['edit message templates', 'create mailings'],
      'delete' => ['edit message templates'],
    ];
  }

}
