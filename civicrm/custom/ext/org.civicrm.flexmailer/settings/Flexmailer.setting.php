<?php
return [
  'flexmailer_traditional' => [
    'group_name' => 'Flexmailer Preferences',
    'group' => 'flexmailer',
    'name' => 'flexmailer_layout',
    'quick_form_type' => 'Select',
    'type' => 'String',
    'html_type' => 'select',
    'html_attributes' => ['class' => 'crm-select2'],
    'pseudoconstant' => ['callback' => '_flexmailer_traditional_options'],
    'default' => 'auto',
    'add' => '5.13',
    'title' => 'Traditional Mailing Handler',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => NULL,
    'help_text' => NULL,
  ],
];
