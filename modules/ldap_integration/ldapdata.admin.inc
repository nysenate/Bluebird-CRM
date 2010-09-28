<?php
// $Id: ldapdata.admin.inc,v 1.9 2009/07/28 14:03:05 miglius Exp $

/**
 * @file
 * Module admin page callbacks.
 */

//////////////////////////////////////////////////////////////////////////////
// ldapdata settings

 /**
 * Implements the settings page.
 *
 * @return
 *   The form structure.
 */
function ldapdata_admin_settings() {
  $form['sync'] = array(
    '#type' => 'fieldset',
    '#title' => t('Synchronizing options'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );
  $form['sync']['ldapdata_sync'] = array(
    '#type' => 'radios',
    '#title' => t('Synchronize LDAP data with Drupal profiles'),
    '#options' => array(t('When user logs in. (Use when LDAP rarely changes.)'), t('On each page load.'), t('Every time user object loaded in Drupal. (May cause high LDAP traffic.)')),
    '#default_value' => LDAPDATA_SYNC,
    '#description' => t('User edition will always synchronize the data despite the selection above.'),
  );

  $form['submit'] = array(
   '#type'  => 'submit',
    '#value' => t('Save configuration'),
  );
  $form['reset'] = array(
    '#type'  => 'submit',
    '#value' => t('Reset to defaults'),
  );

  $form['list']['#value'] = ldapdata_admin_list();

  return $form;
}

/**
 * Submit hook for the settings form.
 */
function ldapdata_admin_settings_submit($form, &$form_state) {
  $op = $form_state['clicked_button']['#value'];
  $values = $form_state['values'];
  switch ($op) {
    case t('Save configuration'):
      variable_set('ldapdata_sync', $values['ldapdata_sync']);

      drupal_set_message(t('The configuration options have been saved.'));
      break;
    case t('Reset to defaults'):
      variable_del('ldapdata_sync');

      drupal_set_message(t('The configuration options have been reset to their default values.'));
      break;
  }
}

/**
 * Implements the LDAP servers list.
 *
 * @return
 *   The HTML table with the servers list.
 */
function ldapdata_admin_list() {
  $rows = array();
  $result = db_query("SELECT sid, name, status FROM {ldapauth} ORDER BY weight");
  while ($row = db_fetch_object($result)) {
    $rows[] = array(
      'data' => array(
        $row->name,
        l(t('edit'), 'admin/settings/ldap/ldapdata/edit/'. $row->sid),
        l(t('reset'), 'admin/settings/ldap/ldapdata/reset/'. $row->sid),
      ),
      'class' => $row->status ? 'menu-enabled' : 'menu-disabled',
    );
  }

  $header = array(
    t('Server'),
    array('data' => t('Operations'), 'colspan' => 2),
  );

  return theme('table', $header, $rows);
}

/**
 * Implements the LDAP server edit page.
 *
 * @param $form_state
 *   A form state array.
 * @param $op
 *   An operatin - edit or reset.
 * @param $sid
 *   A LDAP server ID.
 *
 * @return
 *   The form structure.
 */
function ldapdata_admin_edit(&$form_state, $op, $sid) {
  drupal_add_js(drupal_get_path('module', 'ldapdata') .'/ldapdata.admin.js');

  if ($op == "reset" && $sid) {
    $form['sid'] = array(
      '#type' => 'value',
      '#value' => $sid,
      );
    return confirm_form(
      $form,
      t('Are you sure you want to reset the fields mapping to defaults ?'),
      'admin/settings/ldap/ldapdata',
      t('<em>This action cannot be undone.</p>'),
      t('Reset'),
      t('Cancel')
    );
  }
  elseif ($op == "edit" && $sid) {
    $edit = db_fetch_array(db_query("SELECT * FROM {ldapauth} WHERE sid = %d", $sid));

    $ldapdata_mappings = $edit['ldapdata_mappings'] ? unserialize($edit['ldapdata_mappings']) : array();
    $ldapdata_roattrs = $edit['ldapdata_roattrs'] ? unserialize($edit['ldapdata_roattrs']) : array();
    $ldapdata_rwattrs = $edit['ldapdata_rwattrs'] ? unserialize($edit['ldapdata_rwattrs']) : array();
    $ldapdata_attrs = $edit['ldapdata_attrs'] ? unserialize($edit['ldapdata_attrs']) : array();

    $form['description'] = array(
      '#value' => t('Configure profile synchronization settings for %server.', array('%server' => $edit['name'])),
    );

    // Attribute mapping.
    $form['mapping'] = array(
      '#type' => 'fieldset',
      '#title' => t('Drupal-LDAP fields mapping'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    );
    $form['mapping']['ldapdata_mapping'] = array(
      '#type' => 'radios',
      '#title' => t('Drupal user profile field mapping'),
      '#default_value' => isset($ldapdata_mappings['access']) ? $ldapdata_mappings['access'] : LDAPDATA_MAP_ATTRIBUTES,
      '#options' => array(
        LDAPDATA_MAP_NOTHING => t('No mapping. (Clears any mappings defined below.)'),
        LDAPDATA_MAP_ATTRIBUTES_READ_ONLY => t('Read only: Drupal user profile fields have LDAP attributes.'),
        LDAPDATA_MAP_ATTRIBUTES => t('Read/write: Drupal user profile fields have LDAP attributes. LDAP attributes updated upon Drupal profile change.'),
      ),
    );
    $profile_fields = _ldapdata_retrieve_profile_fields();
    $standard_fields = _ldapdata_retrieve_standard_user_fields();
    $drupal_fields = $profile_fields + $standard_fields;
    $form['mapping']['mapping_pre'] = array(
      '#value' => t('<div class="form-item"><label>Specify mappings below if you selected the second or third option. </label><table><thead><tr><th> Drupal field</th><th>LDAP attribute</th></tr></thead><tbody>'),
    );
    $ldap_drupal_reverse_mappings = _ldapdata_reverse_mappings($sid);
    foreach ($drupal_fields as $key => $field) {
      $field_tmp = "ldap_amap-". $key;
      $_prefix = "<tr><td><label for=\"edit[$field_tmp]\">$field</label></td><td>";
      $form['mapping'][$field_tmp] = array(
        '#type' => 'textfield',
        '#default_value' => isset($ldapdata_mappings[$field_tmp]) ? $ldapdata_mappings[$field_tmp] : NULL,
        '#size' => '20',
        '#prefix' => $_prefix,
        '#suffix' => '</td>',
      );
    }
    $form['mapping']['mapping_post'] = array(
      '#value' => '</tbody></table></div>',
    );

    // Attribute access control.
    $form["attributes"] = array(
      '#type' => 'fieldset',
      '#title' => t('Attribute visibility & access control'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#description' => t('Allows users to view or edit their LDAP attributes.'),
      '#tree' => TRUE,
    );
    $attributes = '';
    foreach ($ldapdata_attrs as $attr => $data)
      $attributes .= $attr .'|'. implode('|', $data) ."\n";
    $form['attributes']['ldapdata_attrs'] = array(
      '#type' => 'textarea',
      '#title' => t('Attributes'),
      '#default_value' => $attributes,
      '#cols' => 25,
      '#rows' => 5,
      '#description' => t('A list of the LDAP attributes and corresponding form data. If configured, they will be listed in a table below for a more control. The element type may be \'text\' or \'url\', the form element should be \'textfield\'. Please look at the following examples:<br /><code>cn|text|textfield|Common Name|64|64</code><br /><code>homePage|url|textfield|Other web pages|64|64</code>'),
    );
    $fields = $rooptions = $rwoptions = $roattrs = $rwattrs = array();
    foreach ($ldapdata_attrs as $attr => $data) {
      $fields[$attr] = $data[2];
    }
    foreach ($fields as $attr => $attr_name) {
      $rooptions[$attr]  = '';
      $rwoptions[$attr]  = '';

      if (in_array($attr, $ldapdata_roattrs))
        $roattrs[] = $attr;
      if (in_array($attr, $ldapdata_rwattrs))
        $rwattrs[] = $attr;

      $form['attributes']['table'][$attr] = array(
        '#value' => $attr_name,
      );
    }
    $form['attributes']['ldapdata_roattrs'] = array(
      '#type' => 'checkboxes',
      '#options' => $rooptions,
      '#default_value' => $roattrs,
    );
    $form['attributes']['ldapdata_rwattrs'] = array(
      '#type' => 'checkboxes',
      '#options' => $rwoptions,
      '#default_value' => $rwattrs,
    );
    $form['attributes']['header'] = array(
      '#type' => 'value',
      '#value' => array(
        array('data' => t('Attribute name')),
        array('data' => t('Readable by user?')),
        array('data' => t('Editable by user?')),
      )
    );
    $form['attributes']['ldapdata_filter_php'] = array(
      '#type' => 'textarea',
      '#title' => t('PHP to filter attributes'),
      '#default_value' => $edit['ldapdata_filter_php'],
      '#cols' => 25,
      '#rows' => 5,
      '#description' => t('Enter PHP to filter LDAP attributes. Careful, bad PHP code here will break your site. If left empty, no filtering will be done. If filter is set, then attributes will be only readable. The LDAP atributes array <code>$attributes</code> is available in the code context. The code should return a filtered <code>$attributes</code> array as in example bellow:<br /><code>$attributes[\'mail\'][0] = preg_replace(\'/([^@]+@).*/\', \'$1mail.com\', $attributes[\'mail\'][0]);</code><br /><code>return $attributes;</code>'),
    );

    // Advanced configuration.
    $form['advanced'] = array(
      '#type' => 'fieldset',
      '#title' => t('Advanced configuration'),
      '#description' => t('<p>When reading/editing attributes, this module logs on to the LDAP directory using the user\'s DN/pass pair. However, many LDAP setups do not allow their users to edit attributes.</p><p>If this is your case, but still you want users to edit their LDAP attributes via Drupal, you should set up an special user on your directory, with special access to edit your users\' attributes. Then this module will use it to log on and edit data.</p>'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    );
    if (!$edit['ldapdata_bindpw']) {
      $form['advanced']['ldapdata_binddn'] = array(
        '#type' => 'textfield',
        '#title' => t('DN for reading/editing attributes'),
        '#default_value' => $edit['ldapdata_binddn'],
        '#size' => 50,
        '#maxlength' => 255,
      );
      $form['advanced']['ldapdata_bindpw'] = array(
        '#type' => 'password',
        '#title' => t('Password for reading/editing attributes'),
        '#size' => 50,
        '#maxlength' => 255,
      );
    }
    else {
      $form['advanced']['ldapdata_binddn'] =  array(
        '#type' => 'item',
        '#title' => t('DN for non-anonymous search'),
        '#value' => $edit['ldapdata_binddn'],
      );
      // Given an option to clear the password.
      $form['advanced']['ldapdata_bindpw_clear'] = array(
        '#type' => 'checkbox',
        '#default_value' => FALSE,
        '#title' => t('Clear current password and change DN'),
      );
    }
    $form['advanced']['test'] = array(
      '#type'  => 'submit',
      '#value' => t('Test'),
      '#suffix' => '<div id="test-spinner" style="display: none;">'. theme_image(drupal_get_path('module', 'ldapdata') .'/images/spinner.gif') .'</div><div id="test-message" class="messages" style="display: none;"></div>',
    );

    $form['sid'] = array(
      '#type' => 'hidden',
      '#value' => $sid,
    );

    $form['buttons']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Update'),
    );

    return $form;
  }
  else {
    drupal_goto('admin/settings/ldap/ldapdata');
  }
}

/**
 * Validate hook for the settings form.
 */
function ldapdata_admin_edit_validate($form, &$form_state) {
  $op = $form_state['clicked_button']['#value'];
  $values = $form_state['values'];
  switch ($op) {
    case t('Update'):
      $form_state['ldapdata_attrs'] = array();
      $ldapdata_attrs = TRUE;
      foreach ((trim($values['attributes']['ldapdata_attrs']) ? explode("\n", trim($values['attributes']['ldapdata_attrs'])) : array()) as $line) {
        if (count($data = explode('|', trim($line))) == 6)
          $form_state['ldapdata_attrs'] += array(trim(array_shift($data)) => $data);
        else
          $ldapdata_attrs = FALSE;
      }
      if (!$ldapdata_attrs)
        form_set_error('attributes][ldapdata_attrs', t('Bad attribute syntax.'));

      $form_state['ldapdata_mappings'] = array();
      $form_state['ldapdata_mappings']['access'] = $values['ldapdata_mapping'];
      if ($form_state['ldapdata_mappings']['access'] >= 4) {
        foreach (element_children($values) as $attr) {
          if (preg_match("/ldap_amap/", $attr) && $values[$attr])
            $form_state['ldapdata_mappings'][$attr] = $values[$attr];
        }
      }
      $form_state['ldapdata_mappings'] = !empty($form_state['ldapdata_mappings']) ? serialize($form_state['ldapdata_mappings']) : '';


      $form_state['ldapdata_roattrs'] = isset($values['attributes']['ldapdata_roattrs']) ? array_values(array_intersect($values['attributes']['ldapdata_roattrs'], array_keys($form_state['ldapdata_attrs']))) : array();
      $form_state['ldapdata_roattrs'] = !empty($form_state['ldapdata_roattrs']) ? serialize($form_state['ldapdata_roattrs']) : '';

      $form_state['ldapdata_rwattrs'] = isset($values['attributes']['ldapdata_rwattrs']) ? array_values(array_intersect($values['attributes']['ldapdata_rwattrs'], array_keys($form_state['ldapdata_attrs']))) : array();
      $form_state['ldapdata_rwattrs'] = !empty($form_state['ldapdata_rwattrs']) ? serialize($form_state['ldapdata_rwattrs']) : '';

      $form_state['ldapdata_attrs'] = !empty($form_state['ldapdata_attrs']) ? serialize($form_state['ldapdata_attrs']) : '';

      $form_state['ldapdata_filter_php'] = trim($values['attributes']['ldapdata_filter_php']);
      $form_state['ldapdata_rwattrs'] = empty($form_state['ldapdata_filter_php']) ? $form_state['ldapdata_rwattrs'] : '';
      break;
  }
}

/**
 * Submit hook for the settings form.
 */
function ldapdata_admin_edit_submit($form, &$form_state) {
  $op = $form_state['clicked_button']['#value'];
  $values = $form_state['values'];
  switch ($op) {
    case t('Update'):
      if (isset($values['ldapdata_bindpw_clear'])) {
        db_query("UPDATE {ldapauth} SET ldapdata_mappings = '%s', ldapdata_roattrs = '%s', ldapdata_rwattrs = '%s', ldapdata_attrs = '%s', ldapdata_filter_php = '%s' WHERE sid = %d", $form_state['ldapdata_mappings'], $form_state['ldapdata_roattrs'], $form_state['ldapdata_rwattrs'], $form_state['ldapdata_attrs'], $form_state['ldapdata_filter_php'], $values['sid']);
        if ($values['ldapdata_bindpw_clear']) {
          db_query("UPDATE {ldapauth} SET ldapdata_bindpw = '' WHERE sid = %d", $values['sid']);
        }
      }
      else {
        db_query("UPDATE {ldapauth} SET ldapdata_mappings = '%s', ldapdata_roattrs = '%s', ldapdata_rwattrs = '%s', ldapdata_binddn = '%s', ldapdata_bindpw = '%s', ldapdata_attrs = '%s', ldapdata_filter_php = '%s' WHERE sid = %d", $form_state['ldapdata_mappings'], $form_state['ldapdata_roattrs'], $form_state['ldapdata_rwattrs'], $values['ldapdata_binddn'], $values['ldapdata_bindpw'], $form_state['ldapdata_attrs'], $form_state['ldapdata_filter_php'], $values['sid']);
      }
      drupal_set_message(t('The configuration options have been saved.'));
      $form_state['redirect'] = 'admin/settings/ldap/ldapdata';
      break;
    case t('Reset'):
      if ($values['confirm'] == 1) {

        // Settings reset.
        db_query("UPDATE {ldapauth} SET ldapdata_mappings = '', ldapdata_roattrs = '', ldapdata_rwattrs = '', ldapdata_binddn = '', ldapdata_bindpw = '', ldapdata_attrs = '', ldapdata_filter_php = '' WHERE sid = %d", $values['sid']);
        drupal_set_message(t('The configuration options have been reset to their default values.'));
      }
      $form_state['redirect'] = 'admin/settings/ldap/ldapdata';
      break;
    case t('Test'):
      global $_ldapdata_ldap;
      if (isset($values['sid']) && _ldapdata_init($values['sid'])) {
        // Try to authenticate.
        $bind_info = _ldapdata_edition($values['sid']);
        if (!$_ldapdata_ldap->connect($bind_info['dn'], $bind_info['pass'])) {
          drupal_set_message(t('Authentication with the LDAP server for the dn %dn and saved password failed.', array('%dn' => $bind_info['dn'])), 'error');
        }
        else {
          drupal_set_message(t('Authentication with the LDAP server for the dn %dn and saved password succeeded.', array('%dn' => $bind_info['dn'])));
        }
      }
      else {
        drupal_set_message(t('Cannot load server settings. Please save configuration first.'), 'error');
      }
      break;
  }
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
function _ldapdata_ajax_test($sid) {
  global $_ldapdata_ldap;

  if (!is_numeric($sid)) {
    return;
  }

  _ldapdata_init($sid);

  if ($_POST['bindpw_clear'] == 'undefined') {
    $binddn = $_POST['binddn'];
    $bindpw = $_POST['bindpw'];
  }
  else {
    $binddn = $_ldapdata_ldap->getOption('binddn');
    $bindpw = $_ldapdata_ldap->getOption('bindpw');
  }

  drupal_json($_ldapdata_ldap->connect($binddn, $bindpw) ? array('status' => 1, 'message' => t('Authentication with the LDAP server succeeded.')) : array('status' => 0, 'message' => t('Authentication with the LDAP server failed.')));
  exit;
}

