<?php
// $Id: ldapgroups.admin.inc,v 1.11 2009/05/04 00:26:17 miglius Exp $

/**
 * @file
 * Module admin page callbacks.
 */

//////////////////////////////////////////////////////////////////////////////
// ldapgroups settings

 /**
 * Implements the settings page.
 *
 * @return
 *   The form structure.
 */
function ldapgroups_admin_settings() {
  $form['list']['#value'] = ldapgroups_admin_list();

  return $form;
}

/**
 * Implements the LDAP servers list.
 *
 * @return
 *   The HTML table with the servers list.
 */
function ldapgroups_admin_list() {
  $rows = array();
  $result = db_query("SELECT sid, name, status FROM {ldapauth} ORDER BY weight");
  while ($row = db_fetch_object($result)) {
    $rows[] = array(
      'data' => array(
        $row->name,
        l(t('edit'), 'admin/settings/ldap/ldapgroups/edit/'. $row->sid),
        l(t('reset'), 'admin/settings/ldap/ldapgroups/reset/'. $row->sid),
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

function ldapgroups_admin_edit(&$form_state, $op, $sid) {
  if (($op == 'reset') && $sid) {
    $form['sid'] = array(
      '#type' => 'value',
      '#value' => $sid,
      );
    return confirm_form(
      $form,
      t('Are you sure you want to reset the groups mapping to defaults ?'),
      'admin/settings/ldap/ldapgroups',
      t('<em>This action cannot be undone.</p>'),
      t('Reset'),
      t('Cancel')
    );
  }
  elseif ($op == 'edit' && $sid) {
    $edit = db_fetch_array(db_query("SELECT * FROM {ldapauth} WHERE sid = %d", $sid));

    $form['description'] = array(
      '#value' => t('Configure LDAP groups to Drupal roles mapping settings for the %server.', array('%server' => $edit['name'])),
    );

    $form['group_dn'] = array(
      '#type' => 'fieldset',
      '#title' => t('Group by DN'),
      '#collapsible' => TRUE,
      '#collapsed' => !$edit['ldapgroups_in_dn'],
    );
    $form['group_dn']['ldapgroups_in_dn'] = array(
      '#type' => 'checkbox',
      '#title' => t('Group is specified in user\'s DN'),
      '#default_value' => $edit['ldapgroups_in_dn'],
      '#description' => '<p>Check this option if your users\' DNs look like <em style="font-style: normal; padding: 1px 3px; border: 1px solid #8888CC; background-color: #DDDDFF">cn=jdoe,<strong>ou=Group1</strong>,cn=example,cn=com</em> and <em style="font-style: normal; padding: 1px 3px; border: 1px solid #8888CC; background-color: #DDDDFF">Group1</em> turns out to be the group you want.</p>'
    );
    $form['group_dn']['ldapgroups_dn_attribute'] = array(
      '#type' => 'textfield',
      '#title' => t('Attribute of the DN which contains the group name'),
      '#default_value' => $edit['ldapgroups_dn_attribute'],
      '#size' => 50,
      '#maxlength' => 255,
      '#description' => t('The name of the attribute which contains the group name. In the example above, it would be <em style="font-style: normal; padding: 1px 3px; border: 1px solid #8888CC; background-color: #DDDDFF">ou</em>, as the DN contains the string <em style="font-style: normal; padding: 1px 3px; border: 1px solid #8888CC; background-color: #DDDDFF">ou=Group1</em> and <em style="font-style: normal; padding: 1px 3px; border: 1px solid #8888CC; background-color: #DDDDFF">Group1</em> happens to be the desired group name.'),
    );

    $form['group_attr'] = array(
      '#type' => 'fieldset',
      '#title' => t('Group by attribute'),
      '#collapsible' => TRUE,
      '#collapsed' => !$edit['ldapgroups_in_attr'],
    );
    $form['group_attr']['ldapgroups_in_attr'] = array(
      '#type' => 'checkbox',
      '#title' => t('Groups are specified by LDAP attributes'),
      '#default_value' => $edit['ldapgroups_in_attr'],
    );
    $form['group_attr']['ldapgroups_attr'] = array(
      '#type' => 'textarea',
      '#title' => t('Attribute names (one per line)'),
      '#default_value' => implode("\n", ($edit['ldapgroups_attr'] ? unserialize($edit['ldapgroups_attr']) : array())),
      '#cols' => 50,
      '#rows' => 6,
      '#description' => t('If the groups are stored in the user entries, along with the rest of their data, then enter here a list of attributes which may contain them.'),
    );

    $form['group_entry'] = array(
      '#type' => 'fieldset',
      '#title' => t('Group by entry'),
      '#collapsible' => TRUE,
      '#collapsed' => !$edit['ldapgroups_as_entries'],
    );
    $form['group_entry']['ldapgroups_as_entries'] = array(
      '#type' => 'checkbox',
      '#title' => t('Groups exist as LDAP entries where a multivalued attribute contains the members\' CNs'),
      '#default_value' => $edit['ldapgroups_as_entries'],
    );
    $form['group_entry']['ldapgroups_entries'] = array(
      '#type' => 'textarea',
      '#title' => t('LDAP DNs containing groups (one per line)'),
      '#default_value' => implode("\n", ($edit['ldapgroups_entries'] ? unserialize($edit['ldapgroups_entries']) : array())),
      '#cols' => 50,
      '#rows' => 6,
      '#description' => t('Enter here a list of LDAP nodes from where groups should be searched for. The module will look them up recursively from the given nodes.'),
    );
    $form['group_entry']['ldapgroups_entries_attribute'] = array(
      '#type' => 'textfield',
      '#title' => t('Attribute holding group members'),
      '#default_value' => $edit['ldapgroups_entries_attribute'],
      '#size' => 50,
      '#maxlength' => 255,
      '#description' => t('Name of the multivalued attribute which holds the CNs of group members, for example: !attr', array('!attr' => theme('placeholder', LDAPGROUPS_DEFAULT_ENTRIES_ATTRIBUTE))),
    );
    $form['groups_limit'] = array(
      '#type' => 'fieldset',
      '#title' => t('LDAP group to Drupal role limits'),
      '#collapsible' => TRUE,
      '#collapsed' => !$edit['ldapgroups_groups'],
    );
    $form['groups_limit']['ldapgroups_groups'] = array(
      '#type' => 'textarea',
      '#title' => t('LDAP groups which allow automatic account creation'),
      '#default_value' => implode("\n", ($edit['ldapgroups_groups'] ? unserialize($edit['ldapgroups_groups']) : array())),
      '#cols' => 50,
      '#rows' => 5,
      '#description' => t('Leave blank to automatically create accounts for all LDAP authenticated users. Otherwise, enter a one per line list of LDAP groups. If the user is not in any of those groups, the login will be denied.'),
    );
    $form['group_filter'] = array(
      '#type' => 'fieldset',
      '#title' => t('LDAP group to Drupal role filtering'),
      '#description' => t('The module automatically decides names for the Drupal roles based in the names of the LDAP groups. For example:<ul><li>LDAP group: Admins => Drupal role: Admins</li><li>LDAP group: ou=Underlings,dc=myorg,dc=mytld => Drupal role: Underlings.</li></ul>'),
      '#collapsible' => TRUE,
      '#collapsed' => !($edit['ldapgroups_mappings'] || $edit['ldapgroups_filter_php']),
    );
    $mappings = '';
    foreach (($edit['ldapgroups_mappings'] ? unserialize($edit['ldapgroups_mappings']) : array()) as $group => $role)
      $mappings .= $group .'|'. $role ."\n";
    $form['group_filter']['ldapgroups_mappings'] = array(
      '#type' => 'textarea',
      '#title' => t('Mapping of LDAP groups to Drupal roles'),
      '#default_value' => $mappings,
      '#cols' => 50,
      '#rows' => 5,
      '#description' => t('Enter a list of LDAP groups and their Drupal role mappings, one per line with a | delimiter. Should be in the form [ldap group]|[drupal role] such as:<br/>cn=ED IT NAG Staff,DC=ad,DC=uiuc,DC=edu|admin<br/>cn=Ed Webs UIUC Webmasters,DC=ad,DC=uiuc,DC=edu|committee member'),
    );
    $form['group_filter']['ldapgroups_mappings_filter'] = array(
      '#type' => 'checkbox',
      '#title' => t('Use LDAP group to Drupal roles filtering'),
      '#default_value' => $edit['ldapgroups_mappings_filter'],
      '#description' => t('If enabled, only above mapped groups will be mapped to Drupal roles. If not enabled, a Drupal role will be created for every group the user is associated with.')
    );
    $form['group_filter']['ldapgroups_filter_php'] = array(
      '#type' => 'textarea',
      '#title' => t('PHP to filter roles by'),
      '#default_value' => $edit['ldapgroups_filter_php'],
      '#cols' => 25,
      '#rows' => 5,
      '#description' => t('Enter PHP to filter LDAP groups. Careful, bad PHP code here will break your site. If left empty, no filtering will be done. The groups array <code>$groups</code> is available in the code context. It should return a filtered <code>$groups</code> array as in example below. The code is evaluated before the above mapping is applied.<br /><code>$groups = array_filter($groups, create_function(\'$a\', \'return preg_match(\\\'/Staff/\\\', $a);\'));</code><br /><code>return $groups;</code>'),
    );

    $form['sid'] = array(
      '#type' => 'hidden',
      '#value' => $sid,
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Update'),
    );

    return $form;
  }
  else {
    drupal_goto('admin/settings/ldap/ldapgroups');
  }
}

/**
 * Validate hook for the settings form.
 */
function ldapgroups_admin_edit_validate($form, &$form_state) {
  $op = $form_state['clicked_button']['#value'];
  $values = $form_state['values'];
  switch ($op) {
    case t('Update'):
      if ($values['ldapgroups_in_dn'] && !trim($values['ldapgroups_dn_attribute']))
        form_set_error('ldapgroups_dn_attribute', t('DN attribute is missing.'));
      if ($values['ldapgroups_in_attr'] && !trim($values['ldapgroups_attr']))
        form_set_error('ldapgroups_attr', t('Attribute names are missing.'));
      if ($values['ldapgroups_as_entries'] && !trim($values['ldapgroups_entries']))
        form_set_error('ldapgroups_entries', t('Nodes are missing.'));
      if ($values['ldapgroups_as_entries'] && !trim($values['ldapgroups_entries_attribute']))
        form_set_error('ldapgroups_entries_attribute', t('Attribute is missing.'));

      $form_state['ldapgroups_attr'] = array();
      foreach ((trim($values['ldapgroups_attr']) ? explode("\n", trim($values['ldapgroups_attr'])) : array()) as $line)
        if (trim($line))
          $form_state['ldapgroups_attr'][] = trim($line);
      $form_state['ldapgroups_attr'] = !empty($form_state['ldapgroups_attr']) ? serialize($form_state['ldapgroups_attr']) : '';

      $form_state['ldapgroups_entries'] = array();
      foreach ((trim($values['ldapgroups_entries']) ? explode("\n", trim($values['ldapgroups_entries'])) : array()) as $line)
        if (trim($line))
          $form_state['ldapgroups_entries'][] = trim($line);
      $form_state['ldapgroups_entries'] = !empty($form_state['ldapgroups_entries']) ? serialize($form_state['ldapgroups_entries']) : '';

      $form_state['ldapgroups_mappings'] = array();
      $ldapgroups_role_mappings = TRUE;
      foreach ((trim($values['ldapgroups_mappings']) ? explode("\n", trim($values['ldapgroups_mappings'])) : array()) as $line) {
        if (count($mapping = explode('|', trim($line))) == 2)
          $form_state['ldapgroups_mappings'] += array(trim($mapping[0]) => trim($mapping[1]));
        else
          $ldapgroups_role_mappings = FALSE;
      }
      $form_state['ldapgroups_mappings'] = !empty($form_state['ldapgroups_mappings']) ? serialize($form_state['ldapgroups_mappings']) : '';
      if (!$ldapgroups_role_mappings)
        form_set_error('ldapgroups_mappings', t('Bad mapping syntax.'));

      if ($values['ldapgroups_mappings_filter'] && !trim($values['ldapgroups_mappings']))
        form_set_error('ldapgroups_mappings', t('Mappings are missing.'));

      $form_state['ldapgroups_groups'] = array();
      foreach ((trim($values['ldapgroups_groups']) ? explode("\n", trim($values['ldapgroups_groups'])) : array()) as $line)
        if (trim($line))
          $form_state['ldapgroups_groups'][] = trim($line);
      $form_state['ldapgroups_groups'] = !empty($form_state['ldapgroups_groups']) ? serialize($form_state['ldapgroups_groups']) : '';
      break;
  }
}

/**
 * Submit hook for the settings form.
 */
function ldapgroups_admin_edit_submit($form, &$form_state) {
  $op = $form_state['clicked_button']['#value'];
  $values = $form_state['values'];
  switch ($op) {
    case t('Update'):

      // Update the ldapgroups configuration.
      db_query("UPDATE {ldapauth} SET ldapgroups_in_dn = %d, ldapgroups_dn_attribute = '%s', ldapgroups_in_attr = %d, ldapgroups_attr = '%s', ldapgroups_as_entries = %d, ldapgroups_entries = '%s', ldapgroups_entries_attribute = '%s', ldapgroups_mappings = '%s', ldapgroups_mappings_filter = %d, ldapgroups_filter_php = '%s', ldapgroups_groups = '%s' WHERE sid = %d", $values['ldapgroups_in_dn'], trim($values['ldapgroups_dn_attribute']), $values['ldapgroups_in_attr'], $form_state['ldapgroups_attr'], $values['ldapgroups_as_entries'], $form_state['ldapgroups_entries'], trim($values['ldapgroups_entries_attribute']), $form_state['ldapgroups_mappings'], $values['ldapgroups_mappings_filter'], trim($values['ldapgroups_filter_php']), $form_state['ldapgroups_groups'], $values['sid']);
      drupal_set_message(t('The configuration options have been saved.'));
      $form_state['redirect'] = 'admin/settings/ldap/ldapgroups';
      break;
    case t('Reset'):
      if ($values['confirm'] == 1) {

        // Settings reset.
        db_query("UPDATE {ldapauth} SET ldapgroups_in_dn = 0, ldapgroups_dn_attribute = '', ldapgroups_in_attr = 0, ldapgroups_attr = '', ldapgroups_as_entries = 0, ldapgroups_entries = '', ldapgroups_entries_attribute = '', ldapgroups_mappings = '', ldapgroups_mappings_filter = '0', ldapgroups_filter_php = '', ldapgroups_groups = '' WHERE sid = %d", $values['sid']);
        drupal_set_message(t('The configuration options have been reset to their default values.'));
      }
      $form_state['redirect'] = 'admin/settings/ldap/ldapgroups';
      break;
  }
}

