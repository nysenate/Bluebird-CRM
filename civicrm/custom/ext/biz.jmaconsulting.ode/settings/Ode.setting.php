<?php

use CRM_Ode_ExtensionUtil as E;

return [
  'ode_from_allowed' => [
    'group_name' => 'Outbound Domain Enforcement Settings',
    'group' => 'ode',
    'name' => 'ode_from_allowed',
    'type' => 'Boolean',
    'is_domain' => 1,
    'is_contact' => 0,
    'help_text' => E::ts('Enabling this setting will whitelist all emails configured at "Administer > Communications > FROM Email Addresses" so that they can be used as valid FROM email addresses across various forms on the website. Please ensure your server\'s SPF policy is updated to allow sending emails using these email addresses.'),
    'title' => E::ts('Whitelist FROM email addresses?'),
    'html_type'=> 'checkbox',
    'settings_pages' => ['ode' => ['weight' => 10]],
    'default' => 0,
  ]
];