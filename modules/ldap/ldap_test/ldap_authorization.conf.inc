<?php

/**
 * @file
 */

// For og constants.
module_load_include('php', 'og', 'module');

/**
 *
 */
function ldap_test_ldap_authorization_data() {

  $empty_mappings = [
    'from' => '',
    'user_entered' => '',
    'normalized' => '',
    'simplified' => '',
    'valid' => '',
    'error_message' => '',
  ];
  // Cant use constant OG_AUTHENTICATED_ROLE here.
  $OG_AUTHENTICATED_ROLE = 'member';
  $conf['og_group2']['og_group'] = [

    'sid' => 'activedirectory1',
    'consumerType' => 'og_group',
    'consumerModule' => 'ldap_authorization_og_group',

    'description' => 'Hogwarts AD',
    'status' => 1,
    'onlyApplyToLdapAuthenticated' => 1,

    'mappings' => [
    [
      'from' => 'cn=students,ou=groups,dc=hogwarts,dc=edu',
      'user_entered' => 'students',
      'normalized' => 'node:students:' . $OG_AUTHENTICATED_ROLE,
      'simplified' => '',
      'valid' => '',
      'error_message' => '',
    ],
    [
      'from' => 'cn=faculty,ou=groups,dc=hogwarts,dc=edu',
      'user_entered' => 'faculty',
      'normalized' => 'node:faculty:' . $OG_AUTHENTICATED_ROLE,
      'simplified' => '',
      'valid' => '',
      'error_message' => '',
    ],
    [
      'from' => 'cn=gryffindor,ou=groups,dc=hogwarts,dc=edu',
      'user_entered' => 'gryffindor',
      'normalized' => 'node:gryffindor:' . $OG_AUTHENTICATED_ROLE,
      'simplified' => '',
      'valid' => '',
      'error_message' => '',
    ],
    [
      'from' => 'cn=users,ou=groups,dc=hogwarts,dc=edu',
      'user_entered' => 'users',
      'normalized' => 'node:users:' . $OG_AUTHENTICATED_ROLE,
      'simplified' => '',
      'valid' => '',
      'error_message' => '',
    ],
    ],

    'useMappingsAsFilter' => 1,

    'synchOnLogon' => 1,

    'revokeLdapProvisioned' => 1,
    'createConsumers' => 0,
    'regrantLdapProvisioned' => 1,

  ];

  $conf['og_group15']['og_group'] = $conf['og_group2']['og_group'];
  $conf['og_group15']['og_group']['mappings'] = [
    [
      'from' => 'cn=students,ou=groups,dc=hogwarts,dc=edu',
      'user_entered' => 'group-name=students,role-name=member',
      'simplified' => '',
      'valid' => '',
      'error_message' => '',
    ],
    [
      'from' => 'cn=faculty,ou=groups,dc=hogwarts,dc=edu',
      'user_entered' => 'group-name=faculty,role-name=member',
      'simplified' => '',
      'valid' => '',
      'error_message' => '',
    ],
    [
      'from' => 'cn=gryffindor,ou=groups,dc=hogwarts,dc=edu',
      'user_entered' => 'group-name=gryffindor,role-name=member',
      'simplified' => '',
      'valid' => '',
      'error_message' => '',
    ],
    [
      'from' => 'cn=users,ou=groups,dc=hogwarts,dc=edu',
      'user_entered' => 'group-name=users,role-name=member',
      'simplified' => '',
      'valid' => '',
      'error_message' => '',
    ],
  ];

  $conf['drupal_role_default']['drupal_role'] = [

    'sid' => 'activedirectory1',
    'consumerType' => 'drupal_role',
    'consumerModule' => 'ldap_authorization_drupal_role',

    'description' => 'Hogwarts AD',
    'status' => 1,
    'onlyApplyToLdapAuthenticated' => 1,

    'mappings' => [
    [
      'from' => 'cn=students,ou=groups,dc=hogwarts,dc=edu',
      'user_entered' => 'students',
      'normalized' => 'students',
      'simplified' => 'students',
      'valid' => TRUE,
      'error_message' => '',
    ],
    [
      'from' => 'cn=faculty,ou=groups,dc=hogwarts,dc=edu',
      'user_entered' => 'administrator',
      'normalized' => 'administrator',
      'simplified' => 'administrator',
      'valid' => TRUE,
      'error_message' => '',
    ],
    [
      'from' => 'gryffindor',
      'user_entered' => 'gryffindor',
      'normalized' => 'gryffindor',
      'simplified' => 'gryffindor',
      'valid' => TRUE,
      'error_message' => '',
    ],
    [
      'from' => 'cn=users,ou=groups,dc=hogwarts,dc=edu',
      'user_entered' => 'users',
      'normalized' => 'users',
      'simplified' => 'users',
      'valid' => TRUE,
      'error_message' => '',
    ],
    ],

    'useMappingsAsFilter' => 0,

    'synchOnLogon' => 1,

    'revokeLdapProvisioned' => 1,
    'createConsumers' => 1,
    'regrantLdapProvisioned' => 1,
  ];

  $conf['drupal_role_authentication_test']['drupal_role'] = [
    'sid' => 'activedirectory1',
    'consumerType' => 'drupal_role',
    'consumerModule' => 'ldap_authorization_drupal_role',

    'description' => 'Hogwarts AD',
    'status' => 1,
    'onlyApplyToLdapAuthenticated' => 1,

    'mappings' => [
    [
      'from' => 'cn=students,ou=groups,dc=hogwarts,dc=edu',
      'user_entered' => 'students',
      'normalized' => 'node:students:' . $OG_AUTHENTICATED_ROLE,
      'simplified' => 'students',
      'valid' => TRUE,
      'error_message' => '',
    ],
    [
      'from' => 'gryffindor',
      'user_entered' => 'gryffindor',
      'normalized' => 'node:gryffindor:' . $OG_AUTHENTICATED_ROLE,
      'simplified' => 'gryffindor',
      'valid' => TRUE,
      'error_message' => '',
    ],
    [
      'from' => 'cn=users,ou=groups,dc=hogwarts,dc=edu',
      'user_entered' => 'users',
      'normalized' => 'node:users:' . $OG_AUTHENTICATED_ROLE,
      'simplified' => 'users',
      'valid' => TRUE,
      'error_message' => '',
    ],
    ],

    'useMappingsAsFilter' => 1,

    'synchOnLogon' => 1,

    'revokeLdapProvisioned' => 1,
    'createConsumers' => 1,
    'regrantLdapProvisioned' => 1,
  ];

  return $conf;
}
