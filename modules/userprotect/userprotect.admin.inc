<?php

/**
 * @file
 * Administration functions for userprotect module.
 */

/**
 * Builds a table of protected users, and their protections.
 *
 * @return array
 *   A form array representing the table.
 */
function userprotect_protected_users() {
  return userprotect_protections_bypass('user');
}

/**
 * Builds a table of user admin bypass values.
 *
 * @return array
 *   A form array representing the table.
 */
function userprotect_administrator_bypass() {
  return userprotect_protections_bypass('admin');
}

/**
 * Helper function. Builds tables for protected users and admin bypass.
 *
 * @return array
 *   A form array representing the table.
 */
function userprotect_protections_bypass($type) {

  // Build the header.
  $header = array(array('data' => t('User'), 'field' => 'name', 'sort' => 'asc'));

  $protect_columns = userprotect_get_protection_display();
  foreach ($protect_columns as $field => $data) {
    $header[] = array('data' => $data, 'field' => $field);
  }
  $header[] = array('data' => t('Operations'));

  $query = db_select('userprotect', 'up');
  $query->innerJoin('users', 'u', 'up.uid = u.uid');
  $query->condition('up.up_type', $type);

  $count_query = clone $query;
  $count_query->addExpression('COUNT(DISTINCT u.uid)');

  $query = $query->extend('PagerDefault')->extend('TableSort');

  // These are all protection fields in the database.
  $protection_fields = array_keys(userprotect_get_protection_display());

  // Grab the protected users.
  $query
    ->fields('up', $protection_fields)
    ->fields('u', array('uid', 'name'))
    ->limit(25)
    ->orderByHeader($header)
    ->setCountQuery($count_query);
  $protected_users = $query->execute();

  // Set some initial values.
  $delete = t('delete');
  $options = array();

  // These are all available protections.
  $protections = array_keys(userprotect_user_protection_defaults());

  // Pass in the header and list of protections to the form so they'll be
  // available to the theming function.
  $form = array();
  $form['protection']['#tree'] = TRUE;
  $form['#header'] = $header;
  $form['#protections'] = $protections;
  $form['#submit'][] = 'userprotect_protections_bypass_submit';
  $form['#theme'] = 'userprotect_protections_bypass';

  // Build the checkboxes options.
  foreach ($protections as $protection) {
    $options[$protection] = '';
  }

  // For each protected user, build their table row.
  foreach ($protected_users as $protected_user) {
    $defaults = array();

    $user = user_load($protected_user->uid);

    $form['user'][$user->uid]['uid'] = array(
      '#type' => 'value',
      '#value' => $user->uid,
    );
    $form[$user->uid]['name'] = array(
      '#theme' => 'username',
      '#account' => $user,
    );
    $form[$user->uid]['operations'] = array('#markup' => $user->uid ? l($delete, "userprotect/delete/$user->uid/$type") : '');
    // Build the protections for the user row.
    foreach ($protections as $protection) {
      if ($protected_user->$protection) {
        $defaults[] = $protection;
      }
    }

    // The checkboxes for this user.
    $form['protection'][$user->uid] = array(
      '#type' => 'checkboxes',
      '#options' => $options,
      '#default_value' => $defaults,
    );
  }

  // An autocomplete field to add new users for protection.
  // This needs a custom validation function to check the user
  // to be added.
  $form['up_add'] = array(
    '#type' => 'textfield',
    '#maxlength' => 60,
    '#autocomplete_path' => 'user/autocomplete',
    '#element_validate' => array('userprotect_up_add_validate'),
    '#userprotect_type' => $type,
  );
  $form['up_add_text'] = array(
    '#markup' => t('Add user'),
  );
  $form['userprotect_type'] = array(
    '#type' => 'value',
    '#value' => $type,
  );

  $form['pager'] = array(
    '#theme' => 'pager',
    '#tags' => NULL,
  );

  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
  );
  return $form;
}

/**
 * Themes the protected users table.
 *
 * @param array $variables
 *   Contains the form to theme.
 *
 * @return string
 *   An HTML string representing the constructed form.
 */
function theme_userprotect_protections_bypass($variables) {
  $form = $variables['form'];
  $rows = array();
  // Build the row for each user.
  if (isset($form['user'])) {
    foreach (element_children($form['user']) as $uid) {
      $row = array();
      $row[] = drupal_render($form[$uid]['name']);
      // Build the protections for the user row.
      foreach ($form['#protections'] as $protection) {
        $row[] = drupal_render($form['protection'][$uid][$protection]);
      }
      $row[] = drupal_render($form[$uid]['operations']);
      $rows[] = $row;
    }
  }
  // Add the last row with the add textfield.
  $rows[] = array(
    array('data' => drupal_render($form['up_add']), 'colspan' => strval(count($form['#header']) - 1)),
    array('data' => drupal_render($form['up_add_text']), 'colspan' => '1'),
  );

  // Theme the table.
  $output = theme('table', array('header' => $form['#header'], 'rows' => $rows));
  $output .= drupal_render_children($form);

  return $output;
}

/**
 * Custom validation function for adding a user for protection.
 */
function userprotect_up_add_validate($form, &$form_state) {
  // If a user has been submitted.
  if ($username = $form['#value']) {
    $type = $form['#userprotect_type'];
    // If the user is valid, and they are not already being protected...
    if ($uid = db_query("SELECT uid FROM {users} WHERE name = :name", array(
      ':name' => $username,
    ))->fetchField()) {
      if (!db_query("SELECT uid FROM {userprotect} WHERE uid = :uid AND up_type = :up_type", array(
        ':uid' => $uid,
        ':up_type' => $type,
      ))->fetchField()) {
        if ($uid != 1 && $type == 'admin' && !db_query("SELECT ur.uid FROM {users_roles} ur INNER JOIN {role_permission} rp ON ur.rid = rp.rid WHERE rp.permission = :permission AND ur.uid = :uid", array(
          ':permission' => 'administer users',
          ':uid' => $uid,
        ))->fetchField()) {
          form_set_error('up_add', t('%user does not have user administration privileges.', array('%user' => $username)));
        }
        else {
          // Transform the username into a uid.
          form_set_value($form, $uid, $form_state);
        }
      }
      // Can't add a user twice.
      else {
        form_set_error('up_add', t('%user is already on this list.', array('%user' => $username)));
      }

    }
    // Can't add a user that doesn't exist.
    else {
      form_set_error('up_add', t('The username is invalid.'));
    }
  }
}

/**
 * Processes the submitted user protection form.
 */
function userprotect_protections_bypass_submit($form, &$form_state) {

  $type = $form_state['values']['userprotect_type'];

  // A user was added, so add them to the protected users table.
  if ($uid = $form_state['values']['up_add']) {
    userprotect_add_user($uid, $type);
    $username = userprotect_get_username($uid);
    if ($type == 'user') {
      drupal_set_message(t('%user is now protected.', array('%user' => $username)));
    }
    elseif ($type == 'admin') {
      drupal_set_message(t('%user now has bypass capabilities matching the default protections for newly protected users.', array('%user' => $username)));
    }
  }
  if (isset($form_state['values']['protection'])) {
    // Load the defaults as a reference to all protections.
    $protections_values = userprotect_user_protection_defaults();
    // Loop through each user.
    foreach ($form_state['values']['protection'] as $uid => $protections) {
      $fields = array();
      // Loop through the submitted user's protections, setting them enabled or
      // disabled as appropriate for the update query. Note: $protection is
      // a module generated string, so it's safe.
      foreach ($protections_values as $protection => $value) {
        $fields[$protection] = $protections[$protection] ? 1 : 0;
      }

      // Update the user's protections.
      db_update('userprotect')
        ->fields($fields)
        ->condition('uid', $uid)
        ->condition('up_type', $type)
        ->execute();
    }
    if ($type == 'user') {
      drupal_set_message(t('Protection settings updated.'));
    }
    elseif ($type == 'admin') {
      drupal_set_message(t('Bypass settings updated.'));
    }
  }
}

/**
 * Menu callback. Removes a user from being protected, or removes an
 * administrator bypass.
 */
function userprotect_protected_users_delete_form($form, &$form_state, $account, $type = 'user') {
  if ($type == 'user') {
    $type_display = t('protections');
    $admin_page = 'protected_users';
  }
  elseif ($type == 'admin') {
    $type_display = t('administrator bypass');
    $admin_page = 'administrator_bypass';
  }
  $form_state['userprotect']['account'] = $account;
  $form_state['userprotect']['type'] = $type;
  $form_state['userprotect']['type_display'] = $type_display;
  $form_state['userprotect']['admin_page'] = $admin_page;
  return confirm_form(array(), t('Are you sure you want to delete the individual !type for %user?', array('!type' => $type_display, '%user' => $account->name)), "admin/config/people/userprotect/$admin_page");
}

/**
 * Submit function for the delete confirmation form.
 */
function userprotect_protected_users_delete_form_submit($form, &$form_state) {
  $account = $form_state['userprotect']['account'];
  $type = $form_state['userprotect']['type'];
  $type_display = $form_state['userprotect']['type_display'];
  $admin_page = $form_state['userprotect']['admin_page'];
  db_delete('userprotect')
    ->condition('uid', $account->uid)
    ->condition('up_type', $type)
    ->execute();
  if ($type == 'user') {
    drupal_set_message(t('%user is no longer protected.', array('%user' => $account->name)));
  }
  elseif ($type == 'admin') {
    drupal_set_message(t('%user is no longer enabled for bypass.', array('%user' => $account->name)));
  }

  $form_state['redirect'] = "admin/config/people/userprotect/$admin_page";
}

/**
 * Builds a form for the role protection settings.
 *
 * @return array
 *   An array representing the form.
 */
function userprotect_protected_roles() {

  $form = array();

  // Get the list of all protections, and the current default settings.
  $options = userprotect_get_protection_display();

  // Build the header.
  $header = array(t('Role'));
  foreach ($options as $field => $data) {
    $header[] = $data;
  }

  // Grab all roles but the anonymous role, and grab the current default
  // settings.
  $roles = user_roles(TRUE);
  $protected_roles = variable_get('userprotect_role_protections', array());

  // This is a complete list of protections for reference.
  $protections = array_keys(userprotect_user_protection_defaults());

  // Pass in the header and protections so they're available for the theme
  // function. Also, we want this as one big array to save in the variables
  // table, so tree it.
  $form['role_table']['#header'] = $header;
  $form['role_table']['#theme'] = 'userprotect_admin_role_table';
  $form['role_table']['#protections'] = $protections;
  $form['role_table']['userprotect_role_protections']['#tree'] = TRUE;

  // Build a row for each role.
  foreach ($roles as $rid => $role) {
    $form['role_table']['userprotect_role_protections'][$rid]['name'] = array('#markup' => $role);
    // Build protections for the row.
    foreach ($protections as $protection) {
      $form['role_table']['userprotect_role_protections'][$rid][$protection] = array(
        '#type' => 'checkbox',
      );
      if (isset($protected_roles[$rid][$protection])) {
        $form['role_table']['userprotect_role_protections'][$rid][$protection]['#default_value'] = $protected_roles[$rid][$protection];
      }
    }
  }

  return system_settings_form($form);
}

/**
 * Builds a form for the userprotect default settings.
 *
 * @return array
 *   An array representing the form.
 */
function userprotect_protection_defaults() {

  // Get the list of all protections, and the current default settings.
  $options = userprotect_get_protection_display();
  $current_defaults = variable_get('userprotect_protection_defaults', userprotect_user_protection_defaults());

  // Transform the defaults into proper checkboxes defaults.
  $defaults = array_keys(array_filter($current_defaults));

  // A set of checkboxes that lists the default protection settings.
  $form['userprotect_protection_defaults'] = array(
    '#type' => 'checkboxes',
    '#title' => t('User protection defaults'),
    '#description' => t('The selected protections will be assigned to users when they are first added for protection.'),
    '#options' => $options,
    '#default_value' => $defaults,
  );
  // A checkbox to enable the auto-protect functionality.
  $form['userprotect_autoprotect'] = array(
    '#type' => 'checkbox',
    '#title' => t('Auto-protect new users'),
    '#description' => t('If selected, all newly created users will automatically be protected and assigned the default protections above.'),
    '#default_value' => variable_get('userprotect_autoprotect', FALSE),
  );
  // A set of checkboxes that lists the default protection settings.
  $form['userprotect_administrator_bypass_defaults'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Administrator bypass defaults'),
    '#description' => t('If selected, all users with the \'administer users\' permission will be allowed to bypass the protection<br \><em>Note: this default setting is overridden by the <a href="!per_user_bypass">per-user administrator bypass settings</a>.</em>.', array('!per_user_bypass' => url('admin/config/people/userprotect/administrator_bypass'))),
    '#options' => $options,
    '#default_value' => variable_get('userprotect_administrator_bypass_defaults', userprotect_administrator_bypass_defaults()),
  );

  return system_settings_form($form);
}

/**
 * Themes the role protections table.
 *
 * @param array $variables
 *   Contains the form for the table.
 *
 * @return string
 *   An HTML string representing the table.
 */
function theme_userprotect_admin_role_table($variables) {
  $form = $variables['form'];
  $rows = array();
  // Build a row for each role.
  foreach (element_children($form['userprotect_role_protections']) as $rid) {
    $row = array();
    $row[] = drupal_render($form['userprotect_role_protections'][$rid]['name']);
    // Build the protections for each row.
    foreach ($form['#protections'] as $protection) {
      $row[] = drupal_render($form['userprotect_role_protections'][$rid][$protection]);
    }
    $rows[] = $row;
  }

  // Theme the table.
  $output = t('<h3>Protections by role</h3>');
  $output .= theme('table', array('header' => $form['#header'], 'rows' => $rows));
  $output .= t('<div class="description">Setting a protection for a role will enable that protection for all users in the role.</div>');
  $output .= drupal_render_children($form);

  return $output;
}
