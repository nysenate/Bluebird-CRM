<?php

namespace Civi\Api4;
use Civi\Api4\Action\ContactSummary\GetBlocks;
use Civi\Api4\Generic\AbstractEntity;

/**
 * ContactSummary entity - visual layouts for the contact summary screen.
 *
 * @method static GetBlocks getBlocks
 */
class ContactSummary extends AbstractEntity {

  public static function permissions() {
    return [
      'get' => ['access CiviCRM'],
    ];
  }

}
