<?php
use CRM_Mosaico_ExtensionUtil as E;
return [
  'mosaico_layout' => [
    'group_name' => 'Mosaico Preferences',
    'group' => 'mosaico',
    'name' => 'mosaico_layout',
    'type' => 'String',
    'html_type' => 'select',
    'html_attributes' => [
      'class' => 'crm-select2',
    ],
    'pseudoconstant' => [
      'callback' => 'CRM_Mosaico_Utils::getLayoutOptions',
    ],
    'default' => 'auto',
    'add' => '4.7',
    'title' => E::ts('Mosaico editor layout'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('How should the CiviMail composition screen look?'),
    'help_text' => NULL,
    'settings_pages' => ['mosaico' => ['weight' => 10]]
  ],
  'mosaico_graphics' => [
    'group_name' => 'Mosaico Preferences',
    'group' => 'mosaico',
    'name' => 'mosaico_graphics',
    'type' => 'String',
    'html_type' => 'select',
    'html_attributes' => [
      'class' => 'crm-select2',
    ],
    'pseudoconstant' => [
      'callback' => 'CRM_Mosaico_Utils::getGraphicsOptions',
    ],
    'default' => 'auto',
    'add' => '4.7',
    'title' => E::ts('Mosaico graphics driver'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('Which backend should process images?'),
    'help_text' => NULL,
    'settings_pages' => ['mosaico' => ['weight' => 20]]
  ],
  'mosaico_scale_factor1' => [
    'group_name' => 'Mosaico Preferences',
    'group' => 'mosaico',
    'name' => 'mosaico_scale_factor1',
    'type' => 'String',
    'html_type' => 'select',
    'html_attributes' => [
      'class' => 'crm-select2 six',
    ],
    'pseudoconstant' => [
      'callback' => 'CRM_Mosaico_Utils::getResizeScaleFactor',
    ],
    'default' => '',
    'add' => '5.24',
    'title' => E::ts('Image resize scale factor'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('for resize of images with width'),
    'help_text' => NULL,
    'settings_pages' => ['mosaico' => ['weight' => 50]]
  ],
  'mosaico_scale_factor2' => [
    'group_name' => 'Mosaico Preferences',
    'group' => 'mosaico',
    'name' => 'mosaico_scale_factor2',
    'type' => 'String',
    'html_type' => 'select',
    'html_attributes' => [
      'class' => 'crm-select2 six',
    ],
    'pseudoconstant' => [
      'callback' => 'CRM_Mosaico_Utils::getResizeScaleFactor',
    ],
    'default' => '',
    'add' => '5.24',
    'title' => E::ts('Image resize scale factor'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('for resize of images with width'),
    'help_text' => NULL,
    'settings_pages' => ['mosaico' => ['weight' => 70]]
  ],
  'mosaico_scale_width_limit1' => [
    'group_name' => 'Mosaico Preferences',
    'group' => 'mosaico',
    'name' => 'mosaico_scale_width_limit1',
    'type' => 'String',
    'html_type' => 'select',
    'html_attributes' => [
      'class' => 'crm-select2 huge',
    ],
    'pseudoconstant' => [
      'callback' => 'CRM_Mosaico_Utils::getResizeScaleWidthLimit',
    ],
    'default' => '',
    'add' => '5.24',
    'title' => E::ts('Image resize scale factor width limit'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('When uploading images, the mosaico editor trims it down to very required size (in pixels). Use scale factor setting to keep some buffer (2x or 3x) so upscale doesn\'t look distorted or low resolution. Example') . '<br/>' . E::ts('3x => Upto 285 pixels (covers both 2 and 3 column block images)') . '<br/>' . E::ts('2x => All other sizes (single column block images)'),
    'help_text' => NULL,
    'settings_pages' => ['mosaico' => ['weight' => 60]]
  ],
  'mosaico_scale_width_limit2' => [
    'group_name' => 'Mosaico Preferences',
    'group' => 'mosaico',
    'name' => 'mosaico_scale_width_limit2',
    'type' => 'String',
    'html_type' => 'select',
    'html_attributes' => [
      'class' => 'crm-select2 huge',
    ],
    'pseudoconstant' => [
      'callback' => 'CRM_Mosaico_Utils::getResizeScaleWidthLimit',
    ],
    'default' => '',
    'add' => '5.24',
    'title' => E::ts('Image resize scale factor width limit'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('When uploading images, the mosaico editor trims it down to very required size (in pixels). Use scale factor setting to keep some buffer (2x or 3x) so upscale doesn\'t look distorted or low resolution. Example') . '<br/>' . E::ts('3x => Upto 285 pixels (covers both 2 and 3 column block images)') . '<br/>' . E::ts('2x => All other sizes (single column block images)'),
    'help_text' => NULL,
    'settings_pages' => ['mosaico' => ['weight' => 80]]
  ],
  'mosaico_custom_templates_dir' => [
    'group_name' => 'Mosaico Preferences',
    'group' => 'mosaico',
    'name' => 'mosaico_custom_templates_dir',
    'type' => 'String',
    'html_type' => 'text',
    'html_attributes' => [
      'class' => 'huge40',
    ],
    'default' => '[civicrm.files]/mosaico_tpl',
    'add' => '4.7',
    'title' => E::ts('Mosaico Custom Templates Directory'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => NULL,
    'help_text' => NULL,
    'settings_pages' => ['mosaico' => ['weight' => 30]]
  ],
  'mosaico_custom_templates_url' => [
    'group_name' => 'Mosaico Preferences',
    'group' => 'mosaico',
    'name' => 'mosaico_custom_templates_url',
    'type' => 'String',
    'html_type' => 'text',
    'html_attributes' => [
      'class' => 'huge40',
    ],
    'default' => '[civicrm.files]/mosaico_tpl',
    'add' => '4.7',
    'title' => E::ts('Mosaico Custom Templates URL'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => NULL,
    'help_text' => NULL,
    'settings_pages' => ['mosaico' => ['weight' => 40]]
  ],
  'mosaico_hide_base_templates' => [
    'group_name' => 'Mosaico Preferences',
    'group' => 'mosaico',
    'name' => 'mosaico_hide_base_templates',
    'type' => 'Array',
    'html_type' => 'select',
    'html_attributes' => [
      'multiple' => 1,
      'class' => 'huge crm-select2',
    ],
    'pseudoconstant' => [
      'callback' => 'CRM_Mosaico_Utils::findBaseTemplatesFromDisk',
    ],
    'default' => [],
    'add' => '5.25',
    'title' => E::ts('Hide these base templates'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => NULL,
    'help_text' => NULL,
    'settings_pages' => ['mosaico' => ['weight' => 90]]
  ],
  'mosaico_hotlist_tokens' => [
    'group_name' => 'Mosaico Preferences',
    'group' => 'mosaico',
    'name' => 'mosaico_hotlist_tokens',
    'type' => 'Array',
    'html_type' => 'select',
    'html_attributes' => [
      'multiple' => 1,
      'class' => 'huge crm-select2',
    ],
    'pseudoconstant' => [
      'callback' => 'CRM_Mosaico_Utils::getMailingTokens',
    ],
    'default' => [
      '{contact.first_name}',
      '{contact.last_name}',
      '{contact.display_name}',
      '{contact.contact_id}',
    ],
    'add' => '5.41',
    'title' => E::ts('Mosaico hotlist tokens'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => NULL,
    'help_text' => NULL,
    'settings_pages' => ['mosaico' => ['weight' => 90]]
  ],
];
