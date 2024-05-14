<?php

namespace Civi\Api4\Action\ContactLayout;

/**
 * @inheritDoc
 */
class GetTabs extends \Civi\Api4\Generic\BasicGetAction {

  public function fields() {
    return [
      [
        'name' => 'id',
        'data_type' => 'String',
      ],
      [
        'name' => 'url',
        'data_type' => 'String',
      ],
      [
        'name' => 'title',
        'data_type' => 'String',
      ],
      [
        'name' => 'weight',
        'data_type' => 'Integer',
      ],
      [
        'name' => 'icon',
        'data_type' => 'String',
      ],
      [
        'name' => 'contact_type',
        'data_type' => 'Array',
      ],
      [
        'name' => 'is_active',
        'data_type' => 'Boolean',
      ],
    ];
  }

}
