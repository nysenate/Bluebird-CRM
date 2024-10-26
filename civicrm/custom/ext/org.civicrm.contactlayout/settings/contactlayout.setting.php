<?php
use CRM_Contactlayout_ExtensionUtil as E;

return [
  'contactlayout_default_tabs' => [
    'name' => 'contactlayout_default_tabs',
    'title' => E::ts('Default contact summary tabs'),
    'type' => 'Array',
    'description' => E::ts('Default set of tabs to show on the contact summary screen.'),
    'is_domain' => 1,
  ],
];
