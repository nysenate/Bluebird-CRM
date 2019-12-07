<?php

/**
 * @file
 * Administation settings for Adminimal Admin Menu.
 */

/**
 * Settings page form.
 */
function adminimal_admin_menu_settings($form, &$form_state) {

  // Create the shortcut category.
  $form['shortcut'] = array(
    '#type' => 'fieldset',
    '#title' => t('Shortcut options'),
  );

  if (module_exists('shortcut')) {
    // Create the select list.
    $form['shortcut']['adminimal_admin_menu_render'] = array(
      '#type' => 'radios',
      '#title' => t('Rendering method'),
      '#default_value' => variable_get('adminimal_admin_menu_render', 'collapsed'),
      '#options' => array(
        'hidden' => t('Hidden'),
        'inline' => t('Inline'),
        'collapsed' => t('Collapsed'),
        'newline' => t('Newline'),
        'dropdown' => t('Dropdown'),
        'exclusive' => t('Exclusive'),
      ),
      '#required' => TRUE,
    );

    $descriptions = &$form['shortcut']['adminimal_admin_menu_render'];
    $descriptions['hidden']['#description'] = t('Shortcuts will not be rendered inside the admin menu.');
    $descriptions['inline']['#description'] = t('Shortcuts will be rendered on the same line with the root menu links.');
    $descriptions['collapsed']['#description'] = t('Shortcuts will be collapsed like a normal menu.');
    $descriptions['newline']['#description'] = t('<b>(Default)</b>') . ' ' . t('Shortcuts will be rendered on a new line, below the root menu links.');
    $descriptions['dropdown']['#description'] = t('Shortcuts will be rendered inside a dropdown using the select html tag.');
    $descriptions['exclusive']['#description'] = t('Only the shortcuts will be rendered; the normal menu will be hidden.');
  }
  else {
    $form['shortcut']['#description'] = t('Module Shortcut is supported, but not required by Adminimal Admin Menu.');
  }

  // Create the advanced settings category.
  $form['advanced_settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('Advanced settings'),
    '#description' => t('Do not change any of the advanced setting unless you know what you are doing!'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );

  $form['advanced_settings']['adminimal_admin_menu_slicknav'] = array(
    '#type' => 'checkbox',
    '#default_value' => variable_get('adminimal_admin_menu_slicknav', TRUE),
    '#title' => t('Enable Responsive Menu.'),
    '#description' => t('Enable this option if you want to have responsive menu and mobile device support. While disabling this option could save you few kilobytes (around 3KB), it will completely disable the responsive menu functionality.'),
  );

  $form['advanced_settings']['adminimal_admin_menu_jquery'] = array(
    '#type' => 'checkbox',
    '#default_value' => variable_get('adminimal_admin_menu_jquery', TRUE),
    '#title' => t('Load the required jQuery 1.7 library automagically.'),
    '#description' => t('This will load the newer jQuery version 1.7 using the no-conflict method so it won\'t interfere with any existing jQuery or other JavaScript libraries. The only reason to un-check this if you are already using a newer version of jQuery site-wide and its globally accessible by the "$" variable. Un-cheking this option could save you 33KB, but it may also break your JavaScript if not used correctly.'),
    '#states' => array(
      // Hide the settings when the cancel notify checkbox is disabled.
      'visible' => array(
        ':input[name="adminimal_admin_menu_slicknav"]' => array('checked' => TRUE),
      ),
    ),
  );

  $form['advanced_settings']['adminimal_admin_menu_viewport'] = array(
    '#type' => 'checkbox',
    '#default_value' => variable_get('adminimal_admin_menu_viewport', TRUE),
    '#title' => t('Set the metatag viewport <em>\'width=device-width, initial-scale=1\'</em> for devices.'),
    '#description' => t('This is needed for the responsive menu, if no other theme or module sets the viewport.'),
    '#states' => array(
      // Hide the settings when the cancel notify checkbox is disabled.
      'visible' => array(
        ':input[name="adminimal_admin_menu_slicknav"]' => array('checked' => TRUE),
      ),
    ),
  );

  $form = system_settings_form($form);

  $form['#submit'][] = 'adminimal_admin_menu_settings_submit';

  return $form;
}

/**
 * Submit callback for adminimal_admin_menu_settings();
 */
function adminimal_admin_menu_settings_submit($form, &$form_state) {
  // Clear the admin menu cache.
  admin_menu_flush_caches();
}
