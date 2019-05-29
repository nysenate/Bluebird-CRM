<?php

namespace Civi\Api4\Action\ContactLayout;

/**
 * @inheritDoc
 */
class Replace extends \Civi\Api4\Action\Replace {

  /**
   * Criteria for selecting items to replace.
   *
   * Not required as this entity allows wholesale replacement of all records.
   *
   * @var array
   */
  protected $where = [];

}
