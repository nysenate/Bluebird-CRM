<?php

namespace Civi\Api4;
use Civi\Api4\Action\ContactLayout\GetBlocks;
use Civi\Api4\Generic\AbstractEntity;

/**
 * ContactLayout entity - visual layouts for the contact summary screen.
 *
 * @method static GetBlocks getBlocks
 */
class ContactLayout extends AbstractEntity {

  public static function permissions() {
    return [
      'get' => ['access CiviCRM'],
    ];
  }

}
