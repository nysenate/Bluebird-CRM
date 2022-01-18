<?php
return [
  [
    'name' => 'SavedSearch_Mosaico_Template_List',
    'entity' => 'SavedSearch',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'Mosaico_Template_List',
        'label' => 'Mosaico Templates',
        'form_values' => NULL,
        'search_custom_id' => NULL,
        'api_entity' => 'MosaicoTemplate',
        'api_params' => [
          'version' => 4,
          'select' => [
            'id',
            'title',
            'base',
            'category_id:label',
          ],
          'orderBy' => [],
          'where' => [],
          'groupBy' => [],
          'join' => [],
          'having' => [],
        ],
        'expires_date' => NULL,
        'description' => NULL,
        'mapping_id' => NULL,
      ],
    ],
  ],
  /*[
    'name' => 'SavedSearch_Mosaico_Template_List_SearchDisplay_Mosaico_Template_List', 
    'entity' => 'SearchDisplay', 
    'cleanup' => 'unused', 
    'update' => 'unmodified', 
    'params' => [
      'version' => 4, 
      'values' => [
        'name' => 'Mosaico_Template_List', 
        'label' => 'Mosaico Template List', 
        'saved_search_id' => 'Mosaico_Template_List',
        'type' => 'table', 
        'settings' => [
          'actions' => TRUE, 
          'limit' => 50, 
          'classes' => [
            'table', 
            'table-striped',
          ], 
          'pager' => [
            'show_count' => TRUE, 
            'expose_limit' => TRUE,
          ], 
          'sort' => [], 
          'columns' => [
            [
              'type' => 'field', 
              'key' => 'title', 
              'dataType' => 'String', 
              'label' => 'Title', 
              'sortable' => TRUE,
            ], 
            [
              'type' => 'field', 
              'key' => 'base', 
              'dataType' => 'String', 
              'label' => 'Base Template', 
              'sortable' => TRUE,
            ], 
            [
              'type' => 'field', 
              'key' => 'category_id:label', 
              'dataType' => 'Integer', 
              'label' => 'Category', 
              'sortable' => TRUE,
            ], 
            [
              'size' => 'btn-xs', 
              'links' => [
                [
                  'path' => 'civicrm/', 
                  'icon' => 'fa-eye',
                  'text' => 'Preview',
                  'style' => 'default', 
                  'target' => 'crm-popup',
                ],
                [
                  'path' => 'civicrm/',
                  'icon' => 'fa-pencil',
                  'text' => 'Edit',
                  'style' => 'default',
                  'target' => 'crm-popup',
                ],
                [
                  'path' => 'civicrm/', 
                  'icon' => 'fa-wrench', 
                  'text' => 'Settings', 
                  'style' => 'default',
                  'target' => 'crm-popup',
                ],
                [
                  'path' => 'civicrm/', 
                  'icon' => 'fa-clone', 
                  'text' => 'Copy', 
                  'style' => 'default',
                  'target' => 'crm-popup',
                ],
                [
                  'path' => 'civicrm/', 
                  'icon' => 'fa-trash', 
                  'text' => 'Delete', 
                  'style' => 'default', 
                  'target' => 'crm-popup',
                ],
              ], 
              'type' => 'buttons', 
              'alignment' => 'text-right',
            ],
          ],
          'cssRules' => [],
        ],
        'acl_bypass' => FALSE,
      ],
    ],
  ],*/
];