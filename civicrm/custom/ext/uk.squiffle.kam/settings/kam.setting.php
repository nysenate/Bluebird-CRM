<?php
/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2013
 *
 */
/*
 * Settings metadata file
 */
$cms = preg_replace('/[0-9]/', '', CRM_Core_Config::singleton()->userFramework);
return [
  'menubar_position' => [
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'menubar_position',
    'type' => 'String',
    'html_type' => 'select',
    'default' => 'over-cms-menu',
    'add' => '5.9',
    'title' => ts('Menubar position'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => ts('Location of the CiviCRM main menu.'),
    'help_text' => NULL,
    'options' => [
      'over-cms-menu' => ts('Replace %1 menu', [1 => $cms]),
      'below-cms-menu' => ts('Below %1 menu', [1 => $cms]),
      'above-crm-container' => ts('Above content area'),
      'none' => ts('None - disable menu'),
    ],
  ],
];
