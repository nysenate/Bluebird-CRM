<?php

/**
 * @file
 * Admin interface for general ldap api settings.
 */

/**
 *
 */
function ldap_servers_settings() {
  ldap_servers_module_load_include('inc', 'ldap_servers', 'ldap_servers.functions');

  if (!ldap_servers_ldap_extension_loaded()) {
    drupal_set_message(t('PHP LDAP Extension is not loaded.'), "warning");
  }

  $form['#title'] = "Configure LDAP Preferences";

  $options = ldap_servers_encrypt_types();

  /**  when this is changed, need to decrypt and possibly encrypt pwd in newly selected format
    *   ... thus default needs to be "No Encryption" to avoid confusion.
    */

  $form['previous_encryption'] = ['#type' => 'hidden', '#default_value' => variable_get('ldap_servers_encryption', LDAP_SERVERS_ENC_TYPE_CLEARTEXT)];
  $form['encryption'] = ['#type' => 'fieldset', '#title' => t('Encryption')];
  $form['encryption']['ldap_servers_encryption'] = [
    '#type' => 'select',
    '#options' => $options,
    '#title' => t('Obfuscate LDAP Passwords?'),
    '#default_value' => variable_get('ldap_servers_encryption', LDAP_SERVERS_ENC_TYPE_CLEARTEXT),
    '#description' => t('With obfuscation enabled, passwords will be stored in encrypted form and decrypted with the site hash.'),
  ];

  // Disable the form field and explain this to the user.
  if (count($options) == 1) {
    $form['encryption']['ldap_servers_encryption']['#disabled'] = TRUE;
    $form['encryption']['ldap_servers_encryption']['#description'] = ' <strong>' . t('Obfuscation is not supported on this web server.') . '</strong>';
  }

  $form = system_settings_form($form);
  // Needs to be first.
  array_unshift($form['#submit'], 'ldap_servers_settings_submit');
  return $form;
}

/**
 *
 */
function ldap_servers_settings_submit($form, &$form_state) {
  if ($form_state['submitted']) {
    $new_encyption = $form_state['values']['ldap_servers_encryption'];
    $old_encyption = $form_state['values']['previous_encryption'];

    // Use db instead of functions to avoid classes encryption and decryption.
    if ($new_encyption != $old_encyption) {
      $servers = db_query("SELECT sid, bindpw FROM {ldap_servers} WHERE bindpw is not NULL AND bindpw <> ''")->fetchAllAssoc('sid');
      foreach ($servers as $sid => $server) {
        if ($server->bindpw != '') {
          $decrypted_bind_pwd = ldap_servers_decrypt($server->bindpw, $old_encyption);
          $rencrypted = ldap_servers_encrypt($decrypted_bind_pwd, $new_encyption);
        }
        else {
          $rencrypted = '';
        }
        db_query("UPDATE {ldap_servers} SET bindpw = :bindpw WHERE sid = :sid", [':bindpw' => $rencrypted, ':sid' => $sid]);
      }
    }
  }
}
