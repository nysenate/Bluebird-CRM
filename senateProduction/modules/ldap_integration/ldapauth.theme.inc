<?php
// $Id: ldapauth.theme.inc,v 1.2 2009/10/27 14:29:17 miglius Exp $

/**
 * @file
 * Themes for ldapauth module.
 */

//////////////////////////////////////////////////////////////////////////////
// Theme callbacks

/**
 * Theme function for the admin list form.
 */
function theme_ldapauth_admin_list($form) {
  drupal_add_tabledrag('ldapauth-list-table', 'order', 'sibling', 'server-weight');

  $header = array(
    '',
    t('Server'),
    array('data' => t('Operations'), 'colspan' => 3),
    t('Weight'),
  );

  $rows = array();
  foreach ($form['list'] as $sid => $element) {
    if (is_numeric($sid)) {
      $row = array('');
      $row[] = check_plain($element['name']['#value']);
      $row[] = l(t('edit'), 'admin/settings/ldap/ldapauth/edit/'. $sid);
      $row[] = l($element['status']['#value'] ? t('de-activate') : t('activate'), 'admin/settings/ldap/ldapauth/'. ($element['status']['#value'] ? 'deactivate' : 'activate') .'/'. $sid);
      $row[] = l(t('delete'), 'admin/settings/ldap/ldapauth/delete/'. $sid);
      $element['weight']['#attributes']['class'] = "server-weight";
      $row[] = drupal_render($element['weight']);
      $rows[] = array('data' => $row, 'class' => 'draggable'. ($element['status']['#value'] ? ' menu-enabled' : ' menu-disabled'));
      unset($form['list'][$sid]);
    }
  }

  if (empty($rows)) {
    $rows[] = array(array('data' => t('No servers defined.'), 'colspan' => 5));
    array_pop($header);
    unset($form['submit']);
  }

  $form['list']['table'] = array('#value' => theme('table', $header, $rows, array('id' => 'ldapauth-list-table')));
  return drupal_render($form);
}

