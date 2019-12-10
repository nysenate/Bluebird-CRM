<?php

/**
 * @file
 * Definition of ldap_views module.
 */

/**
 * Implements hook_views_data().
 */
function ldap_views_views_data() {
  $data['ldap']['table']['group'] = t('LDAP Query');

  $data['ldap']['table']['base'] = [
    'field' => 'dn',
    'title' => t('LDAP Query'),
    'help' => t('Data from LDAP Server.'),
    'query class' => 'ldap_views',
  ];
  // Basedn.
  $data['ldap']['basedn'] = [
    'title' => t('LDAP Base DN'),
    'help' => t('The LDAP search base entry'),
    'argument' => [
      'handler' => 'ldap_views_handler_argument',
    ],
  ];
  // filter
  // Generic column.
  $data['ldap']['attribute'] = [
    'title' => t('LDAP Attribute'),
    'help' => t('An attribute in LDAP Server'),
    'argument' => [
      'handler' => 'ldap_views_handler_argument_attribute',
    ],
    'field' => [
      'handler' => 'ldap_views_handler_field_attribute',
    ],
    'sort' => [
      'handler' => 'ldap_views_handler_sort_attribute',
    ],
    'filter' => [
      'handler' => 'ldap_views_handler_filter_attribute',
    ],
  ];
  $data['ldap']['dn'] = [
    'title' => t('DN'),
    'help' => t('Distinguished name'),
    'field' => [
      'handler' => 'ldap_views_handler_field',
    ],
    'sort' => [
      'handler' => 'ldap_views_handler_sort',
    ],
    'filter' => [
      'handler' => 'views_handler_filter_string',
    ],
  ];
  $data['ldap']['cn'] = [
    'title' => t('CN'),
    'help' => t('Common name'),
    'argument' => [
      'handler' => 'ldap_views_handler_argument',
    ],
    'field' => [
      'handler' => 'ldap_views_handler_field',
    ],
    'sort' => [
      'handler' => 'ldap_views_handler_sort',
    ],
    'filter' => [
      'handler' => 'ldap_views_handler_filter',
    ],
  ];
  $data['ldap']['objectClass'] = [
    'title' => t('Object Class'),
    'help' => t('The LDAP objectClass'),
    'argument' => [
      'handler' => 'ldap_views_handler_argument',
    ],
    'field' => [
      'handler' => 'ldap_views_handler_field',
    ],
    'sort' => [
      'handler' => 'ldap_views_handler_sort',
    ],
    'filter' => [
      'handler' => 'ldap_views_handler_filter',
    ],
  ];

  return $data;
}

/*
Alternative to 'attribute' field: add fields here, but we need the qid. Alternative: bundles + entity fields
function ldap_views_views_data_alter(&$data) {
$data['ldap']['example_field'] = array(
'title' => t('Example field'),
'help' => t('Some example content that references a user'),
'field' => array(
'handler' => 'ldap_views_handler_field',
),
);

}
 */

/**
 * Implements hook_views_plugins().
 */
function ldap_views_views_plugins() {
  return [
    'query' => [
      'ldap_views' => [
        'title' => t('LDAP'),
        'help' => t('Reads from an ldap server.'),
        'handler' => 'ldap_views_plugin_query_ldap',
      ],
    ],
  ];
}
