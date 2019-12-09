<?php

/**
 * @file
 * Theming functions for ldap_servers module.
 */

/**
 * Returns HTML for ldap servers list.
 *
 * @param $variables
 *   An associative array containing:
 *   - ldap_servers: an array of one or more ldap server configurations.
 *   - actions:  true or false indicating include update, delete, etc. links
 *   - type:  'table', 'list', etc for style to render
 *
 * @ingroup themeable
 *
 * @return string
 */
function theme_ldap_servers_list($variables) {
  /** @var array $ldap_servers */
  /** @var bool $actions */
  /** @var string $type */
  extract($variables);

  $table = [
    'header' => [t('Name'), t('Type'), t('Enabled'), t('Server Address')],
    'attributes' => ['id' => 'ldap_servers_servers', 'class' => 'data'],
    'colgroups' => [],
    'sticky' => FALSE,
    'empty' => '',
    'caption' => 'LDAP Server Configurations',
  ];

  if ($actions) {
    $table['header'][] = "Operations";
  }
  if (count($ldap_servers)) {
    foreach ($ldap_servers as $sid => $ldap_server) {
      $row = [
        $ldap_server->name,
        $ldap_server->ldap_type,
        ($ldap_server->status == 1) ? "Yes" : "No",
        $ldap_server->address,
      ];
      if ($actions) {
        $admin = new LdapServerAdmin($ldap_server->sid);
        $row[] = join(' | ', $admin->getLdapServerActions());
      }
      $table['rows'][] = $row;
    }
  }
  else {
    $table['rows'] = [];
  }
  $output = theme('table', $table);

  return $output;
}

/**
 * Returns HTML for ldap server.
 *
 * @param $variables
 *   An associative array containing:
 *   - ldap_server: an array of one or more ldap server configurations.
 *   - actions:  true or false indicating include update, delete, etc. links
 *   - type:  'table', 'list', etc for style to render
 *
 * @ingroup themeable
 *
 * @return string
 */
function theme_ldap_servers_server($variables) {
  /** @var array $ldap_server */
  /** @var bool $actions */
  /** @var string $type */
  extract($variables);

  ldap_servers_module_load_include('php', 'ldap_servers', 'LdapServer.class');
  $properties = [];
  foreach (LdapServer::field_to_properties_map() as $field_name => $property_name) {
    $properties[] = "$field_name = " . print_r($ldap_server->$property_name, TRUE);
  }

  if ($actions) {
    $admin = new LdapServerAdmin($ldap_server->sid);
    $properties = join(' | ', $admin->getLdapServerActions());
  }

  $output = theme_item_list(
    [
      'items' => $properties,
      'type' => 'ul',
      'title' => 'Server Properties',
      'attributes' => [],
    ]
    );

  return $output;
}

/**
 *
 */
function theme_ldap_server_token_table($variables) {
  $header = [
    ['data' => 'Token', 'sort' => 'asc'],
    ['data' => 'Value'],
  ];
  foreach ($variables['tokens'] as $token => $value) {
    $rows[] = ['data' => [$token, $value]];
  }
  $token_table = '<div class="content"><h2>' . t('Available Tokens') . '</h2>' .
    theme_table(['colgroups' => NULL, 'caption' => 'tokens', 'header' => $header, 'rows' => $rows, 'sticky' => TRUE, 'attributes' => [], 'empty' => 'No data']) .
    '</div>';

  return $token_table;
}

/**
 *
 */
function theme_ldap_server_ldap_entry_table($variables) {
  if (!isset($variables['entry']) || !is_array($variables['entry'])) {
    return '<p>' . t('No Entry Returned.') . t('</p>');
  }

  $header = ['Attribute Name', 'Instance', 'Value', 'token'];
  $rows = [];
  foreach ($variables['entry'] as $key => $value) {
    if (is_numeric($key) || $key == 'count') {
    }
    elseif (is_array($value) && count($value) > 1) {
      $count = (int) $value['count'];
      foreach ($value as $i => $value2) {

        if ((string) $i == 'count') {
          continue;
        }
        elseif ($i == 0 && $count == 1) {
          $token = LDAP_SERVERS_TOKEN_PRE . $key . LDAP_SERVERS_TOKEN_POST;
        }
        elseif ($i == 0 && $count > 1) {
          $token = LDAP_SERVERS_TOKEN_PRE . $key . LDAP_SERVERS_TOKEN_DEL . '0' . LDAP_SERVERS_TOKEN_POST;
        }
        elseif (($i == $count - 1) && $count > 1) {
          $token = LDAP_SERVERS_TOKEN_PRE . $key . LDAP_SERVERS_TOKEN_DEL . 'last' . LDAP_SERVERS_TOKEN_POST;
        }
        elseif ($count > 1) {
          $token = LDAP_SERVERS_TOKEN_PRE . $key . LDAP_SERVERS_TOKEN_DEL . $i . LDAP_SERVERS_TOKEN_POST;
        }
        else {
          $token = "";
        }
        $rows[] = ['data' => [$key, $i, ldap_servers_string_binary_check($value2), $token]];
      }
    }
  }
  $heading = sprintf('<h2>%s</h2>', t('LDAP Entry for %username (dn: %dn)', [
    '%dn' => $variables['dn'],
    '%username' => $variables['username'],
  ]));
  $table = theme('table', [
    'colgroups' => NULL,
    'caption' => 'ldap entry array',
    'header' => $header,
    'rows' => $rows,
    'sticky' => TRUE,
    'attributes' => [],
    'empty' => 'No data',
  ]);
  $entry_table = '<div class="content">' . $heading . $table . '</div>';
  return $entry_table;
}
