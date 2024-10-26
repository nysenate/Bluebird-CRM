<?php

namespace Civi\Api4\Action\ContactLayout;

/**
 * @inheritDoc
 */
class GetBlocks extends \Civi\Api4\Generic\BasicGetAction {

  public function fields() {
    return [
      [
        'name' => 'name',
        'data_type' => 'String',
      ],
      [
        'name' => 'title',
        'data_type' => 'String',
      ],
      [
        'name' => 'icon',
        'data_type' => 'String',
      ],
      [
        'name' => 'blocks',
        'data_type' => 'Array',
      ],
    ];
  }

}
