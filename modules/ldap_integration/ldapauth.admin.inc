<?php
// $Id: ldapauth.admin.inc,v 1.6 2009/10/27 14:29:16 miglius Exp $

/**
 * @file
 * Module admin page callbacks.
 */

//////////////////////////////////////////////////////////////////////////////
// ldapauth settings

/**
 * Implements the settings page.
 *
 * @return
 *   The form structure.
 */
function ldapauth_admin_settings() {
  $options_login_process = array(
    LDAPAUTH_AUTH_MIXED => t('Mixed mode. The LDAP authentication is performed only if Drupal authentication fails'),
    LDAPAUTH_AUTH_EXCLUSIVED => t('LDAP directory only')
  );
  $options_login_conflict = array(
    LDAPAUTH_CONFLICT_LOG => t('Disallow login and log the conflict'),
    LDAPAUTH_CONFLICT_RESOLVE => t('Associate local account with the LDAP entry')
  );

  $form['system-options'] = array(
    '#type' => 'fieldset',
    '#title' => t('Authentication mode'),
    '#description' => t('<strong>NOTE:</strong> These settings have no effect on Drupal user with uid 1. The admin account never uses LDAP.'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );
  $form['system-options']['ldapauth_login_process'] = array(
    '#type' => 'radios',
    '#title' => t('Choose authentication mode'),
    '#description' => t('Pick the mode based on the types of user accounts and other configuration decisions. If <i>LDAP directory only</i> option is activated some UI modications will be applied.'),
    '#default_value' => LDAPAUTH_LOGIN_PROCESS,
    '#options' => $options_login_process,
    '#required' => TRUE,
  );
  $form['system-options']['ldapauth_login_conflict'] = array(
    '#type' => 'radios',
    '#title' => t('Choose user conflict resolve procedure'),
    '#description' => t('Pick what should be done if the local Drupal account already exists with the same login name.'),
    '#default_value' => LDAPAUTH_LOGIN_CONFLICT,
    '#options' => $options_login_conflict,
    '#required' => TRUE,
  );

  $form['security-options'] = array(
    '#type' => 'fieldset',
    '#title' => t('Security Options'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );
  $form['security-options']['ldapauth_forget_passwords'] = array(
    '#type' => 'checkbox',
    '#title' => t('Do not store users\' passwords during sessions'),
    '#default_value' => LDAPAUTH_FORGET_PASSWORDS,
    '#description' => t('<p>If you use the <strong>ldapdata</strong> module and want to allow users to modify their LDAP attributes, you have two options:</p><ul><li>Setup a special ldap manager DN that has (limited) permissions to edit the requisite LDAP records - using this method means Drupal\'s built in password reset will work;</li> <li>or allow this module to store the user\'s LDAP password, in clear text, during the session;</li></ul><p>Physically, these passwords are stored in the Drupal\'s session table in clear text. This is not ideal and is not the recomended configuration.</p><p>Unless you need to use the latter configuration, leave this checked.</p>'),
  );
  $form['security-options']['ldapauth_sync_passwords'] = array(
    '#type' => 'checkbox',
    '#title' => t('Sync LDAP password with the Drupal password'),
    '#default_value' => LDAPAUTH_SYNC_PASSWORDS,
    '#description' => t('If checked, then LDAP and Drupal passwords will be syncronized. This might be useful if some other modules need to authenticate against the user password hash stored in Drupal and works only in Mixed mode. It might introduce security issues in the Mixed mode since after deletion of the LDAP account user still be able to login to Drupal with his password. If unsure, leave this unchecked.'),
  );

  $form['ldap-ui'] = array(
    '#type' => 'fieldset',
    '#title' => t('LDAP UI Options'),
    '#description' => t('<p>Alters LDAP users\' interface only, though admin accounts can still access email and password fields of LDAP users regardless of selections. Does not effect non-LDAP authenticated accounts. </p>'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );
  $form['ldap-ui']['ldapauth_disable_pass_change'] = array(
    '#type' => 'checkbox',
    '#title' => t('Remove password change fields from user edit form'),
    '#default_value' => LDAPAUTH_DISABLE_PASS_CHANGE,
    '#description' => t('<strong>NOTE:</strong> Request new password feature will be disabled for all users even for the user with uid 1.'),
  );
  $options_email_field = array(
    LDAPAUTH_EMAIL_FIELD_NO => t('Do nothing'),
    LDAPAUTH_EMAIL_FIELD_REMOVE => t('Remove email field from form'),
    LDAPAUTH_EMAIL_FIELD_DISABLE => t('Disable email field on form'),
  );
  $form['ldap-ui']['ldapauth_alter_email_field'] = array(
    '#type' => 'radios',
    '#title' => t('Alter email field on user edit form'),
    '#description' => t('Remove or disable email field from user edit form for LDAP authenticated users.'),
    '#default_value' => LDAPAUTH_ALTER_EMAIL_FIELD,
    '#options' => $options_email_field,
    '#required' => TRUE,
  );

  $form['submit'] = array(
    '#type'  => 'submit',
    '#value' => t('Save configuration'),
  );
  $form['reset'] = array(
    '#type'  => 'submit',
    '#value' => t('Reset to defaults'),
  );
  return $form;
}

/**
 * Submit hook for the settings form.
 */
function ldapauth_admin_settings_submit($form, &$form_state) {
  $op = $form_state['clicked_button']['#value'];
  $values = $form_state['values'];
  switch ($op) {
    case t('Save configuration'):
      variable_set('ldapauth_login_process', $values['ldapauth_login_process']);
      variable_set('ldapauth_login_conflict', $values['ldapauth_login_conflict']);
      variable_set('ldapauth_forget_passwords', $values['ldapauth_forget_passwords']);
      variable_set('ldapauth_sync_passwords', $values['ldapauth_sync_passwords']);
      variable_set('ldapauth_disable_pass_change', $values['ldapauth_disable_pass_change']);
      variable_set('ldapauth_alter_email_field', $values['ldapauth_alter_email_field']);

      drupal_set_message(t('The configuration options have been saved.'));
      break;
    case t('Reset to defaults'):
      variable_del('ldapauth_login_process');
      variable_del('ldapauth_login_conflict');
      variable_del('ldapauth_forget_passwords');
      variable_del('ldapauth_sync_passwords');
      variable_del('ldapauth_disable_pass_change');
      variable_del('ldapauth_alter_email_field');

      drupal_set_message(t('The configuration options have been reset to their default values.'));
      break;
  }

  // Rebuild the menu router.
  menu_rebuild();
}

/**
 * Implements the LDAP servers list.
 *
 * @return
 *   The HTML table with the servers list.
 */
function ldapauth_admin_list() {
  $form['list'] = array();
  $result = db_query("SELECT sid, name, status, weight FROM {ldapauth} ORDER BY weight");
  while ($row = db_fetch_object($result)) {
    $form['list'][$row->sid] = array(
      'name' => array('#value' => $row->name),
      'status' => array('#value' => $row->status),
      'weight' => array(
        '#type' => 'weight',
        '#name' => 'sid_'. $row->sid .'_weight',
        '#delta' => 10,
        '#default_value' => $row->weight,
      ),
    );
  }

  $form['submit'] = array(
    '#type'  => 'submit',
    '#value' => t('Save'),
  );

  return $form;
}

/**
 * Submit hook for the servers list form.
 */
function ldapauth_admin_list_submit($form, &$form_state) {
  $op = $form_state['clicked_button']['#value'];
  switch ($op) {
    case t('Save'):
      foreach ($form_state['clicked_button']['#post'] as $name => $val) {
        if (preg_match('/^sid_.*_weight/', $name)) {
          $sid = preg_replace(array('/^sid_/', '/_weight$/'), array('', ''), $name);
          db_query("UPDATE {ldapauth} SET weight = %d WHERE sid = %d", $val, $sid);
        }
      }
      break;
  }
}

/**
 * Implements the LDAP server edit page.
 *
 * @param $form_state
 *   A form state array.
 * @param $op
 *   An operatin - add or edit.
 * @param $sid
 *   A LDAP server ID.
 *
 * @return
 *   The form structure.
 */
function ldapauth_admin_form(&$form_state, $op = NULL, $sid = NULL) {
  drupal_add_js(drupal_get_path('module', 'ldapauth') .'/ldapauth.admin.js');

  if ($op == "edit" && $sid) {
    $edit = db_fetch_array(db_query("SELECT * FROM {ldapauth} WHERE sid = %d", $sid));
    $form['sid'] = array(
      '#type' => 'hidden',
      '#value' => $sid,
    );
  }
  else {
    $edit = array(
      'name' => '',
      'server' => '',
      'port' => '389',
      'tls' => 0,
      'encrypted' => 0,
      'basedn' => '',
      'user_attr' => LDAPAUTH_DEFAULT_USER_ATTR,
      'mail_attr' => LDAPAUTH_DEFAULT_MAIL_ATTR,
      'binddn' => '',
      'bindpw' => FALSE,
      'login_php' => '',
      'filter_php' => '',
    );
  }

  $form['server-settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('Server settings'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );
  $form['server-settings']['name'] = array(
    '#type' => 'textfield',
    '#title' => t('Name'),
    '#default_value' => $edit['name'],
    '#description' => t('Choose a <em><strong>unique</strong></em> name for this server configuration.'),
    '#size' => 50,
    '#maxlength' => 255,
    '#required' => TRUE,
  );
  $form['server-settings']['server'] = array(
    '#type' => 'textfield',
    '#title' => t('LDAP server'),
    '#default_value' => $edit['server'],
    '#size' => 50,
    '#maxlength' => 255,
    '#description' => t('The domain name or IP address of your LDAP Server.'),
    '#required' => TRUE,
  );
  $form['server-settings']['port'] = array(
    '#type' => 'textfield',
    '#title' => t('LDAP port'),
    '#default_value' => $edit['port'],
    '#size' => 5,
    '#maxlength' => 5,
    '#description' => t('The TCP/IP port on the above server which accepts LDAP connections. Must be an integer.'),
  );
  $form['server-settings']['tls'] = array(
    '#type' => 'checkbox',
    '#title' => t('Use Start-TLS'),
    '#default_value' => $edit['tls'],
    '#description' => t('Secure the connection between the Drupal and the LDAP servers using TLS.<br /><em>Note: To use START-TLS, you must set the LDAP Port to 389.</em>'),
  );
  $form['server-settings']['encrypted'] = array(
    '#type' => 'checkbox',
    '#title' => t('Store passwords in encrypted form'),
    '#default_value' => $edit['encrypted'],
    '#description' => t('Secure the password in LDAP by storing it MD5 encrypted (use with care, as some LDAP directories may do this automatically, what would cause logins problems).'),
  );

  $form['login-procedure'] = array(
    '#type' => 'fieldset',
    '#title' => t('Login procedure'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );
  $form['login-procedure']['basedn'] = array(
    '#type' => 'textarea',
    '#title' => t('Base DNs'),
    '#default_value' => $edit['basedn'],
    '#cols' => 50,
    '#rows' => 6,
    '#description' => t('Base DNs for users. Enter one per line in case you need several of them.'),
  );
  $form['login-procedure']['user_attr'] = array(
    '#type' => 'textfield',
    '#title' => t('UserName attribute'),
    '#default_value' => $edit['user_attr'],
    '#size' => 30,
    '#maxlength' => 255,
    '#description' => t('The attribute that holds the users\' login name. (eg. <em style="font-style: normal; padding: 1px 3px; border: 1px solid #8888CC; background-color: #DDDDFF">cn</em> for eDir or <em style="font-style: normal; padding: 1px 3px; border: 1px solid #8888CC; background-color: #DDDDFF">sAMAccountName</em> for Active Directory).'),
  );
  $form['login-procedure']['mail_attr'] = array(
    '#type' => 'textfield',
    '#title' => t('Email attribute'),
    '#default_value' => $edit['mail_attr'],
    '#size' => 30,
    '#maxlength' => 255,
    '#description' => t('The attribute that holds the users\' email address. (eg. <em style="font-style: normal; padding: 1px 3px; border: 1px solid #8888CC; background-color: #DDDDFF">mail</em>).'),
  );
  $form['login-procedure']['login_php'] = array(
    '#type' => 'textarea',
    '#title' => t('PHP to transform login name'),
    '#default_value' => $edit['login_php'],
    '#cols' => 25,
    '#rows' => 5,
    '#description' => t('Enter PHP to transform login name before it is sent to LDAP for authentication. Careful, bad PHP code here will break your site. If left empty, no name transformation will be done. Change following example code to enable transformation:<br /><code>return $name;</code>'),
  );
  $form['login-procedure']['filter_php'] = array(
    '#type' => 'textarea',
    '#title' => t('PHP to filter users based on their LDAP data'),
    '#default_value' => $edit['filter_php'],
    '#cols' => 25,
    '#rows' => 5,
    '#description' => t('Enter PHP to filter users which are allowed to login based on their LDAP data. Careful, bad PHP code here will break your site. If left empty, no filtering will be done. The code should return TRUE to allow authentication. Following example shows how to disallow users without their homeDirectory set:<br /><code>return isset($ldap[\'homeDirectory\']) && isset($ldap[\'homedirectory\'][0]);</code>'),
  );

  $form['advanced'] = array(
    '#type' => 'fieldset',
    '#title' => t('Advanced configuration'),
    '#description' => t('<p>The process of authentication starts by establishing an anonymous connection to the LDAP directory and looking up for the user on it. Once this user is found, LDAP authentication is performed on them.</p><p>However, some LDAP configurations (specially common in <strong>Active Directory</strong> setups) restrict anonymous searches.</p><p>If your LDAP setup does not allow anonymous searches, or these are restricted in such a way that login names for users cannot be retrieved as a result of them, then you have to specify here a DN//password pair that will be used for these searches.</p><p>For security reasons, this pair should belong to an LDAP account with stripped down permissions.</p>'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );
  if (!$edit['bindpw']) {
    $form['advanced']['binddn'] =  array(
      '#type' => 'textfield',
      '#title' => t('DN for non-anonymous search'),
      '#default_value' => $edit['binddn'],
      '#size' => 50,
      '#maxlength' => 255,
    );
    $form['advanced']['bindpw'] = array(
      '#type' => 'password',
      '#title' => t('Password for non-anonymous search'),
      '#size' => 12,
      '#maxlength' => 255,
    );
  }
  else {
    $form['advanced']['binddn'] =  array(
      '#type' => 'item',
      '#title' => t('DN for non-anonymous search'),
      '#value' => $edit['binddn'],
    );
    // Give an option to clear the password.
    $form['advanced']['bindpw_clear'] = array(
      '#type' => 'checkbox',
      '#title' => t('Clear current password and change DN'),
      '#default_value' => FALSE,
    );
  }
  $form['advanced']['test'] = array(
    '#type'  => 'submit',
    '#value' => t('Test'),
    '#suffix' => '<div id="test-spinner" style="display: none;">'. theme_image(drupal_get_path('module', 'ldapauth') .'/images/spinner.gif') .'</div><div id="test-message" class="messages" style="display: none;"></div>',
  );

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save configuration'),
  );

  return $form;
}

/**
 * Validate hook for the LDAP server form.
 */
function ldapauth_admin_form_validate($form, &$form_state) {
  $values = $form_state['values'];

  if (!isset($values['sid'])) {
    if (db_fetch_object(db_query("SELECT name FROM {ldapauth} WHERE name = '%s'", $values['name']))) {
      form_set_error('name', t('An LDAP config with the  name %name already exists.', array('%name' => $values['name'])));
    }
  }
  if (!is_numeric($values['port'])) {
    form_set_error('port', t('The TCP/IP port must be an integer.'));
  }
}

/**
 * Submit hook for the LDAP server form.
 */
function ldapauth_admin_form_submit($form, &$form_state) {
  $op = $form_state['clicked_button']['#value'];
  $values = $form_state['values'];
  switch ($op) {
    case t('Save configuration'):
      if (!isset($values['sid'])) {
        db_query("INSERT INTO {ldapauth} (name, status, server, port, tls, encrypted, basedn, user_attr, mail_attr, binddn, bindpw, login_php, filter_php) VALUES ('%s', %d, '%s', %d, %d, %d, '%s', '%s', '%s', '%s', '%s', '%s', '%s')", $values['name'], 1, $values['server'], $values['port'], $values['tls'], $values['encrypted'], $values['basedn'], trim($values['user_attr']), trim($values['mail_attr']), $values['binddn'], $values['bindpw'], trim($values['login_php']), trim($values['filter_php']));
        drupal_set_message(t('LDAP configuration %name has been added.', array('%name' => $values['name'])));
        watchdog('ldapauth', 'LDAP configuration %name has been added.', array('%name' => $values['name']));
      }
      else {
        if (isset($values['bindpw_clear'])) {
          db_query("UPDATE {ldapauth} SET name = '%s', server = '%s', port = %d, tls = %d, encrypted = %d, basedn = '%s', user_attr = '%s', mail_attr = '%s', login_php = '%s', filter_php = '%s' WHERE sid = %d", $values['name'], $values['server'], $values['port'], $values['tls'], $values['encrypted'], $values['basedn'], trim($values['user_attr']), trim($values['mail_attr']), trim($values['login_php']), trim($values['filter_php']), $values['sid']);
          if ($values['bindpw_clear']) {
            db_query("UPDATE {ldapauth} SET bindpw = '' WHERE sid = %d", $values['sid']);
          }
        }
        else {
          db_query("UPDATE {ldapauth} SET name = '%s', server = '%s', port = %d, tls = %d, encrypted = %d, basedn = '%s', user_attr = '%s', mail_attr = '%s', binddn = '%s', bindpw = '%s', login_php = '%s', filter_php = '%s' WHERE sid = %d", $values['name'], $values['server'], $values['port'], $values['tls'], $values['encrypted'], $values['basedn'], trim($values['user_attr']), trim($values['mail_attr']), $values['binddn'], $values['bindpw'], trim($values['login_php']), trim($values['filter_php']), $values['sid']);
        }
        drupal_set_message(t('LDAP Configuration %name has been updated.', array('%name' => $values['name'])));
        watchdog('ldapauth', 'LDAP Configuration %name has been updated.', array('%name' => $values['name']));
      }

      $form_state['redirect'] = 'admin/settings/ldap/ldapauth/list';
      break;
    case t('Test'):
      global $_ldapauth_ldap;
      if (isset($values['sid']) && _ldapauth_init($values['sid'])) {
        // Try to authenticate.
        $dn = $_ldapauth_ldap->getOption('binddn');
        $pass = $_ldapauth_ldap->getOption('bindpw');
        if (!$_ldapauth_ldap->connect($dn, $pass)) {
          drupal_set_message(t('Authentication with the LDAP server for the dn %dn and saved password failed.', array('%dn' => $dn)), 'error');
        }
        else {
          drupal_set_message(t('Authentication with the LDAP server for the dn %dn and saved password succeeded.', array('%dn' => $dn)));
        }
      }
      else {
        drupal_set_message(t('Cannot load server settings. Please save configuration first.'), 'error');
      }
      break;
  }
}

/**
 * De-activates the LDAP server.
 *
 * @param $form_State
 *   A form_state array.
 * @param $sid
 *   A LDAP server ID.
 *
 * @return
 *  Form array.
 */
function ldapauth_admin_deactivate(&$form_state, $sid) {
  if (is_numeric($sid) && ($name = db_result(db_query("SELECT name from {ldapauth} WHERE sid = %d", $sid)))) {
    $form['sid'] = array('#type' => 'hidden', '#value' => $sid);

    return confirm_form($form, t('Are you sure you want to de-activate the server %name?', array('%name' => $name)), 'admin/settings/ldap/ldapauth/list', '', t('De-activate'), t('Cancel'));
  }
  else {
    drupal_not_found();
    exit;
  }
}

/**
 * De-activates the LDAP server.
 *
 * @return
 */
function ldapauth_admin_deactivate_submit($form, &$form_state) {
  $sid = $form_state['values']['sid'];
  $result = db_query("SELECT name from {ldapauth} WHERE sid = %d", $sid);
  if ($row = db_fetch_object($result)) {
    db_query("UPDATE {ldapauth} SET status = '0' WHERE sid = %d", $sid);
    drupal_set_message(t('LDAP Configuration %name has been de-activated.', array('%name' => $row->name)));
    watchdog('ldapauth', 'LDAP server %name was de-activated.', array('%name' => $row->name));
  }
  drupal_goto('admin/settings/ldap/ldapauth/list');
}

/**
 * Activates the LDAP server.
 *
 * @param $form_State
 *   A form_state array.
 * @param $sid
 *   A LDAP server ID.
 *
 * @return
 *  Form array.
 */
function ldapauth_admin_activate(&$form_state, $sid) {
  if (is_numeric($sid) && ($name = db_result(db_query("SELECT name from {ldapauth} WHERE sid = %d", $sid)))) {
    $form['sid'] = array('#type' => 'hidden', '#value' => $sid);

    return confirm_form($form, t('Are you sure you want to activate the server %name?', array('%name' => $name)), 'admin/settings/ldap/ldapauth/list', '', t('Activate'), t('Cancel'));
  }
  else {
    drupal_not_found();
    exit;
  }
}

/**
 * Activates the LDAP server.
 *
 * @return
 */
function ldapauth_admin_activate_submit($form, &$form_state) {
  $sid = $form_state['values']['sid'];
  $result = db_query("SELECT name from {ldapauth} WHERE sid = %d", $sid);
  if ($row = db_fetch_object($result)) {
    db_query("UPDATE {ldapauth} SET status = '1' WHERE sid = %d", $sid);
    drupal_set_message(t('LDAP Configuration %name has been activated.', array('%name' => $row->name)));
    watchdog('ldapauth', 'LDAP server %name was activated.', array('%name' => $row->name));
  }
  drupal_goto('admin/settings/ldap/ldapauth/list');
}

/**
 * Implements the LDAP server delete page.
 *
 * @param $form_state
 *   A form state array.
 * @param $sid
 *   A LDAP server ID.
 *
 * @return
 *   The form structure.
 */
function ldapauth_admin_delete(&$form_state, $sid) {
  if (is_numeric($sid) && ($name = db_result(db_query("SELECT name from {ldapauth} WHERE sid = %d", $sid)))) {
    $form = array(
      'sid' => array('#type' => 'hidden', '#value' => $sid),
      'name' => array('#type' => 'hidden', '#value' => $name),
    );

    return confirm_form($form, t('Are you sure you want to delete the LDAP server named %name?', array('%name' => $name)), 'admin/settings/ldap/ldapauth/list', NULL, t('Delete'), t('Cancel'));
  }
  else {
    drupal_not_found();
    exit;
  }
}

/**
 * Submit hook for the LDAP server delete page.
 */
function ldapauth_admin_delete_submit($form, &$form_state) {
  $values = $form_state['values'];
  if ($values['confirm'] && $values['sid']) {
    db_query("DELETE FROM {ldapauth} WHERE sid = %d", $values['sid']);
    drupal_set_message(t('LDAP Configuration %name has been deleted.', array('%name' => $values['name'])));
    watchdog('ldapauth', 'LDAP Configuration %name has been deleted.', array('%name' => $values['name']));
  }
  drupal_goto('admin/settings/ldap/ldapauth/list');
}

/**
 * Implements the LDAP admin  page.
 *
 * @return
 *   The themed HTML page.
 */
function ldapauth_admin_menu_block_page() {
  return theme('admin_block_content', system_admin_menu_block(menu_get_item()));
}

/**
 * Implements the AJAX server test.
 *
 * @param $sid
 *   LDAP server ID.
 *
 * @return
 *   The JSON data.
 */
function _ldapauth_ajax_test($sid) {
  global $_ldapauth_ldap;

  if (!is_numeric($sid)) {
    return;
  }

  _ldapauth_init($sid);

  if ($_POST['bindpw_clear'] == 'undefined') {
    $binddn = $_POST['binddn'];
    $bindpw = $_POST['bindpw'];
  }
  else {
    $binddn = $_ldapauth_ldap->getOption('binddn');
    $bindpw = $_ldapauth_ldap->getOption('bindpw');
  }

  drupal_json($_ldapauth_ldap->connect($binddn, $bindpw) ? array('status' => 1, 'message' => t('Authentication with the LDAP server succeeded.')) : array('status' => 0, 'message' => t('Authentication with the LDAP server failed.')));
  exit;
}

