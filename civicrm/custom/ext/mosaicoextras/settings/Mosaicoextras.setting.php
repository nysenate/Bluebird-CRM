<?php

use CRM_Mosaico_ExtensionUtil as E;

return [
  'mosaico_plugins' => [
    'group_name' => 'Mosaico Preferences',
    'group' => 'mosaico',
    'name' => 'mosaico_plugins',
    'type' => 'String',
    'html_type' => 'text',
    'html_attributes' => [
      'class' => 'huge40',
    ],
    'default' => 'link hr paste lists textcolor code civicrmtoken mailto',
    'add' => '5.35',
    'title' => E::ts('Mosaico Plugin List'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('Plugins name are separated by space.'),
    'help_text' => NULL,
    'settings_pages' => ['mosaico' => ['weight' => 200]]
  ],
  'mosaico_toolbar' => [
    'group_name' => 'Mosaico Preferences',
    'group' => 'mosaico',
    'name' => 'mosaico_toolbar',
    'type' => 'String',
    'html_type' => 'text',
    'html_attributes' => [
      'class' => 'huge40',
    ],
    'default' => 'bold italic forecolor backcolor hr styleselect removeformat | civicrmtoken | link unlink | pastetext code',
    'add' => '5.35',
    'title' => E::ts('Mosaico Toolbar Settings'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('Tool sets name are separated by space, use | symbol for grouping of tool set.'),
    'help_text' => NULL,
    'settings_pages' => ['mosaico' => ['weight' => 201]]
  ],
];
