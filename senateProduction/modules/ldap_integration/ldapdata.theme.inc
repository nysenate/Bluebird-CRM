<?php
// $Id: ldapdata.theme.inc,v 1.3 2009/03/18 12:12:02 miglius Exp $

/**
 * @file
 * Themes for ldapdata module.
 */

//////////////////////////////////////////////////////////////////////////////
// Theme callbacks

/**
 * Theme function for the admin edit form.
 */
function theme_ldapdata_admin_edit($form) {
  $output = '';
  $rows = array();
  foreach (element_children($form) as $element) {
    if ($element == 'attributes') {
      if (isset($form['attributes']['table'])) {
        foreach (element_children($form['attributes']['table']) as $key) {
          $row = array();
          $row[] = drupal_render($form['attributes']['table'][$key]);
          $row[] = drupal_render($form['attributes']['ldapdata_roattrs'][$key]);
          $row[] = drupal_render($form['attributes']['ldapdata_rwattrs'][$key]);
          $rows[] = $row;
        }
      }
      else {
        $rows[] = array(array('data' => t('No attributes configured.'), 'colspan' => 3));
      }
      $form['attributes']['#children'] = drupal_render($form['attributes']['ldapdata_attrs']);
      $form['attributes']['#children'] .= theme('table', $form['attributes']['header']['#value'], $rows);
      $form['attributes']['#children'] .= drupal_render($form['attributes']['ldapdata_filter_php']);
      $output .= drupal_render($form['attributes']);
    }
    else {
      $output .= drupal_render($form[$element]);
    }
  }

  $output .= drupal_render($form);

  return $output;
}

/**
 * Theme functon for the LDAP attribute.
 */
function theme_ldapdata_ldap_attribute($value, $type) {
  switch ($type) {
    case 'url':
      $ret = strpos($value, '://') ? $value : "http://$value";
      $ret = "<a href=\"$ret\">$ret</a>";
      break;
    case 'txt':
    default:
      $ret = $value;
      break;
  }

  return $ret;
}

