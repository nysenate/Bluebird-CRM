<?php

namespace Civi\Api4;
use Civi\Api4\Generic\AbstractEntity;
use Civi\Api4\Action\ContactLayout\GetBlocks;
use Civi\Api4\Action\ContactLayout\GetTabs;

/**
 * ContactLayout entity - visual layouts for the contact summary screen.
 *
 * @method static GetBlocks getBlocks
 * @method static GetTabs getTabs
 */
class ContactLayout extends AbstractEntity {

  public static function permissions() {
    return [
      'get' => ['access CiviCRM'],
    ];
  }

}
