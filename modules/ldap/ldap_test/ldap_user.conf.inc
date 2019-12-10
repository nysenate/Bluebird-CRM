<?php

/**
 * @file
 * Simpletest ldap user config.
 *
 * Should only contain saveable properties of ldapUserConf class.
 */

/**
 *
 */
function ldap_test_ldap_user_data() {

  $conf['default'] = [
    'drupalAcctProvisionServer' => 'activedirectory1',
    'ldapEntryProvisionServer' => LDAP_USER_NO_SERVER_SID,
    'drupalAcctProvisionTriggers' => [
      LDAP_USER_DRUPAL_USER_PROV_ON_USER_UPDATE_CREATE,
      LDAP_USER_DRUPAL_USER_PROV_ON_AUTHENTICATE,
    ],
    'ldapEntryProvisionTriggers' => [],
    'userConflictResolve' => LDAP_USER_CONFLICT_LOG,
    'acctCreation'  => LDAP_USER_ACCT_CREATION_LDAP_BEHAVIOR,
    'ldapUserSynchMappings' => [
      LDAP_USER_PROV_DIRECTION_TO_DRUPAL_USER => [],
      LDAP_USER_PROV_DIRECTION_TO_LDAP_ENTRY => [],
    ],
    'orphanedCheckQty' => 50,
    'manualAccountConflict' => LDAP_USER_MANUAL_ACCT_CONFLICT_SHOW_OPTION_ON_FORM,
  ];

  $conf['ad_authentication'] = [
    'drupalAcctProvisionServer' => 'activedirectory1',
    'ldapEntryProvisionServer' => LDAP_USER_NO_SERVER_SID,
    'drupalAcctProvisionTriggers' => [
      LDAP_USER_DRUPAL_USER_PROV_ON_USER_UPDATE_CREATE,
      LDAP_USER_DRUPAL_USER_PROV_ON_AUTHENTICATE,
    ],
    'ldapEntryProvisionTriggers' => [],
    'userConflictResolve' => LDAP_USER_CONFLICT_LOG,
    'acctCreation'  => LDAP_USER_ACCT_CREATION_LDAP_BEHAVIOR,
    'ldapUserSynchMappings' => [
      LDAP_USER_PROV_DIRECTION_TO_DRUPAL_USER => [],
      LDAP_USER_PROV_DIRECTION_TO_LDAP_ENTRY => [],
    ],
    'orphanedCheckQty' => 50,
    'manualAccountConflict' => LDAP_USER_MANUAL_ACCT_CONFLICT_SHOW_OPTION_ON_FORM,
  ];

  $conf['ad_authorization'] = [
    'drupalAcctProvisionServer' => 'ldapauthor1',
    'ldapEntryProvisionServer' => LDAP_USER_NO_SERVER_SID,
    'drupalAcctProvisionTriggers' => [
      LDAP_USER_DRUPAL_USER_PROV_ON_USER_UPDATE_CREATE,
      LDAP_USER_DRUPAL_USER_PROV_ON_AUTHENTICATE,
    ],
    'ldapEntryProvisionTriggers' => [],
    'userConflictResolve' => LDAP_USER_CONFLICT_LOG,
    'acctCreation'  => LDAP_USER_ACCT_CREATION_LDAP_BEHAVIOR,

    'ldapUserSynchMappings' => [
      LDAP_USER_PROV_DIRECTION_TO_DRUPAL_USER => [],
      LDAP_USER_PROV_DIRECTION_TO_LDAP_ENTRY => [],
    ],
    'orphanedCheckQty' => 50,
    'manualAccountConflict' => LDAP_USER_MANUAL_ACCT_CONFLICT_SHOW_OPTION_ON_FORM,
  ];

  $conf['provisionToDrupal'] = [
    'drupalAcctProvisionServer' => 'activedirectory1',
    'ldapEntryProvisionServer' => LDAP_USER_NO_SERVER_SID,
    'drupalAcctProvisionTriggers' => [
      LDAP_USER_DRUPAL_USER_PROV_ON_USER_UPDATE_CREATE,
      LDAP_USER_DRUPAL_USER_PROV_ON_AUTHENTICATE,
    ],
    'ldapEntryProvisionTriggers' => [],
    'userConflictResolve' => LDAP_USER_CONFLICT_LOG,
    'acctCreation'  => LDAP_USER_ACCT_CREATION_LDAP_BEHAVIOR,
    'ldapUserSynchMappings' => [
      LDAP_USER_PROV_DIRECTION_TO_DRUPAL_USER => [],
      LDAP_USER_PROV_DIRECTION_TO_LDAP_ENTRY => [],
    ],
    'orphanedCheckQty' => 50,
    'manualAccountConflict' => LDAP_USER_MANUAL_ACCT_CONFLICT_SHOW_OPTION_ON_FORM,
  ];

  $conf['provisionToDrupalWithMappings'] = [
    'drupalAcctProvisionServer' => 'activedirectory1',
    'ldapEntryProvisionServer' => LDAP_USER_NO_SERVER_SID,
    'drupalAcctProvisionTriggers' => [
      LDAP_USER_DRUPAL_USER_PROV_ON_USER_UPDATE_CREATE,
      LDAP_USER_DRUPAL_USER_PROV_ON_AUTHENTICATE,
    ],
    'ldapEntryProvisionTriggers' => [
      LDAP_USER_LDAP_ENTRY_PROV_ON_USER_UPDATE_CREATE,
    ],
    'userConflictResolve' => LDAP_USER_CONFLICT_LOG,
    'acctCreation'  => LDAP_USER_ACCT_CREATION_LDAP_BEHAVIOR,
    'ldapUserSynchMappings' => [
      LDAP_USER_PROV_DIRECTION_TO_DRUPAL_USER => [
        '[field.field_display_name]' => [
          'sid' => 'activedirectory1',
          'ldap_attr' => '[givenname] [sn]',
          'user_attr' => 'user_tokens',
          'convert' => 0,
          'direction' => LDAP_USER_PROV_DIRECTION_TO_LDAP_ENTRY,
          'user_tokens' => '[field.field_display_name]',
          'config_module' => 'ldap_user',
          'synch_module' => 'ldap_user',
          'enabled' => 1,
          'prov_events' => [LDAP_USER_EVENT_CREATE_DRUPAL_USER, LDAP_USER_EVENT_SYNCH_TO_DRUPAL_USER],
        ],
        '[field.field_lname]' => [
          'sid' => 'activedirectory1',
          'ldap_attr' => '[sn:0]',
          'user_attr' => 'user_tokens',
          'convert' => 0,
          'direction' => LDAP_USER_PROV_DIRECTION_TO_LDAP_ENTRY,
          'user_tokens' => '[field.field_lname]',
          'config_module' => 'ldap_user',
          'synch_module' => 'ldap_user',
          'enabled' => 1,
          'prov_events' => [LDAP_USER_EVENT_CREATE_DRUPAL_USER, LDAP_USER_EVENT_SYNCH_TO_DRUPAL_USER],
        ],
        '[field.field_fname]' => [
          'sid' => 'activedirectory1',
          'ldap_attr' => '[givenName]',
          'user_attr' => 'user_tokens',
          'convert' => 0,
          'direction' => LDAP_USER_PROV_DIRECTION_TO_LDAP_ENTRY,
          'user_tokens' => '[field.field_fname]',
          'config_module' => 'ldap_user',
          'synch_module' => 'ldap_user',
          'enabled' => 1,
          'prov_events' => [LDAP_USER_EVENT_CREATE_DRUPAL_USER, LDAP_USER_EVENT_SYNCH_TO_DRUPAL_USER],
        ],
        '[field.field_department]' => [
          'sid' => 'activedirectory1',
          'ldap_attr' => 'Physics',
          'user_attr' => 'user_tokens',
          'convert' => 0,
          'direction' => LDAP_USER_PROV_DIRECTION_TO_LDAP_ENTRY,
          'user_tokens' => '[field.field_department]',
          'config_module' => 'ldap_user',
          'synch_module' => 'ldap_user',
          'enabled' => 1,
          'prov_events' => [LDAP_USER_EVENT_CREATE_DRUPAL_USER, LDAP_USER_EVENT_SYNCH_TO_DRUPAL_USER],
        ],
      ],
      LDAP_USER_PROV_DIRECTION_TO_LDAP_ENTRY => [],
    ],
    'orphanedCheckQty' => 50,
    'manualAccountConflict' => LDAP_USER_MANUAL_ACCT_CONFLICT_SHOW_OPTION_ON_FORM,
  ];

  $conf['provisionToLdap_activedirectory1'] = [
    'drupalAcctProvisionServer' => LDAP_USER_NO_SERVER_SID,
    'ldapEntryProvisionServer' => 'activedirectory1',
    'drupalAcctProvisionTriggers' => [],
    'ldapEntryProvisionTriggers' => [
      LDAP_USER_LDAP_ENTRY_PROV_ON_USER_UPDATE_CREATE,
      LDAP_USER_LDAP_ENTRY_DELETE_ON_USER_DELETE,
    ],
    'userConflictResolve' => LDAP_USER_CONFLICT_LOG,
    'acctCreation'  => LDAP_USER_ACCT_CREATION_LDAP_BEHAVIOR,
    'manualAccountConflict' => LDAP_USER_MANUAL_ACCT_CONFLICT_SHOW_OPTION_ON_FORM,
    'ldapUserSynchMappings' => [
      LDAP_USER_PROV_DIRECTION_TO_DRUPAL_USER => [],
      LDAP_USER_PROV_DIRECTION_TO_LDAP_ENTRY => [
        '[dn]' => [
          'sid' => 'activedirectory1',
          'ldap_attr' => '[dn]',
          'user_attr' => 'user_tokens',
          'convert' => 0,
          'direction' => LDAP_USER_PROV_DIRECTION_TO_LDAP_ENTRY,
          'user_tokens' => 'cn=[property.name],ou=people,dc=hogwarts,dc=edu',
          'config_module' => 'ldap_user',
          'synch_module' => 'ldap_user',
          'enabled' => 1,
          'prov_events' => [LDAP_USER_EVENT_CREATE_LDAP_ENTRY, LDAP_USER_EVENT_SYNCH_TO_LDAP_ENTRY],
        ],
        '[cn]' => [
          'sid' => 'activedirectory1',
          'ldap_attr' => '[cn]',
          'user_attr' => '[property.name]',
          'convert' => 0,
          'direction' => LDAP_USER_PROV_DIRECTION_TO_LDAP_ENTRY,
          'user_tokens' => '',
          'config_module' => 'ldap_user',
          'synch_module' => 'ldap_user',
          'enabled' => 1,
          'prov_events' => [LDAP_USER_EVENT_CREATE_LDAP_ENTRY, LDAP_USER_EVENT_SYNCH_TO_LDAP_ENTRY],
        ],
        '[displayname]' => [
          'sid' => 'activedirectory1',
          'ldap_attr' => '[displayname]',
          'user_attr' => 'user_tokens',
          'convert' => 0,
          'direction' => LDAP_USER_PROV_DIRECTION_TO_LDAP_ENTRY,
          'user_tokens' => '[field.field_fname] [field.field_lname]',
          'config_module' => 'ldap_user',
          'synch_module' => 'ldap_user',
          'enabled' => 1,
          'prov_events' => [LDAP_USER_EVENT_CREATE_LDAP_ENTRY, LDAP_USER_EVENT_SYNCH_TO_LDAP_ENTRY],
        ],
        '[samaccountname]' => [
          'sid' => 'activedirectory1',
          'ldap_attr' => '[samaccountname]',
          'user_attr' => '[property.name]',
          'convert' => 0,
          'direction' => LDAP_USER_PROV_DIRECTION_TO_LDAP_ENTRY,
          'user_tokens' => '',
          'config_module' => 'ldap_user',
          'synch_module' => 'ldap_user',
          'prov_events' => [LDAP_USER_EVENT_CREATE_LDAP_ENTRY],
        ],
        '[sn]' => [
          'sid' => 'activedirectory1',
          'ldap_attr' => '[sn]',
          'user_attr' => '[field.field_lname]',
          'convert' => 0,
          'direction' => LDAP_USER_PROV_DIRECTION_TO_LDAP_ENTRY,
          'user_tokens' => '',
          'config_module' => 'ldap_user',
          'synch_module' => 'ldap_user',
          'enabled' => 1,
          'prov_events' => [LDAP_USER_EVENT_CREATE_LDAP_ENTRY, LDAP_USER_EVENT_SYNCH_TO_LDAP_ENTRY],
        ],
        '[givenname]' => [
          'sid' => 'activedirectory1',
          'ldap_attr' => '[givenname]',
          'user_attr' => '[field.field_fname]',
          'convert' => 0,
          'direction' => LDAP_USER_PROV_DIRECTION_TO_LDAP_ENTRY,
          'user_tokens' => '',
          'config_module' => 'ldap_user',
          'synch_module' => 'ldap_user',
          'enabled' => 1,
          'prov_events' => [LDAP_USER_EVENT_CREATE_LDAP_ENTRY, LDAP_USER_EVENT_SYNCH_TO_LDAP_ENTRY],
        ],
        '[guid]' => [
          'sid' => 'activedirectory1',
          'ldap_attr' => '[guid]',
          'user_attr' => 'user_tokens',
          'convert' => 0,
          'direction' => LDAP_USER_PROV_DIRECTION_TO_LDAP_ENTRY,
          'user_tokens' => '151',
          'config_module' => 'ldap_user',
          'synch_module' => 'ldap_user',
          'enabled' => 1,
          'prov_events' => [LDAP_USER_EVENT_CREATE_LDAP_ENTRY],
        ],
        '[provisionsource]' => [
          'sid' => 'activedirectory1',
          'ldap_attr' => '[provisionsource]',
          'user_attr' => 'user_tokens',
          'convert' => 0,
          'direction' => LDAP_USER_PROV_DIRECTION_TO_LDAP_ENTRY,
          'user_tokens' => 'drupal.hogwarts.edu',
          'config_module' => 'ldap_user',
          'synch_module' => 'ldap_user',
          'enabled' => 1,
          'prov_events' => [LDAP_USER_EVENT_CREATE_LDAP_ENTRY],
        ],
      ],
    ],
  ];

  $conf['provisionToLdap_openldap1'] = [
    'drupalAcctProvisionServer' => LDAP_USER_NO_SERVER_SID,
    'ldapEntryProvisionServer' => 'openldap1',
    'drupalAcctProvisionTriggers' => [],
    'ldapEntryProvisionTriggers' => [
      LDAP_USER_LDAP_ENTRY_PROV_ON_USER_UPDATE_CREATE,
      LDAP_USER_LDAP_ENTRY_DELETE_ON_USER_DELETE,
    ],
    'userConflictResolve' => LDAP_USER_CONFLICT_LOG,
    'acctCreation'  => LDAP_USER_ACCT_CREATION_LDAP_BEHAVIOR,
    'manualAccountConflict' => LDAP_USER_MANUAL_ACCT_CONFLICT_SHOW_OPTION_ON_FORM,
    'ldapUserSynchMappings' => [
      LDAP_USER_PROV_DIRECTION_TO_DRUPAL_USER => [],
      LDAP_USER_PROV_DIRECTION_TO_LDAP_ENTRY => [
        '[dn]' => [
          'sid' => 'openldap1',
          'ldap_attr' => '[dn]',
          'user_attr' => 'user_tokens',
          'convert' => 0,
          'direction' => LDAP_USER_PROV_DIRECTION_TO_LDAP_ENTRY,
          'user_tokens' => 'cn=[property.name],ou=people,dc=hogwarts,dc=edu',
          'config_module' => 'ldap_user',
          'synch_module' => 'ldap_user',
          'enabled' => 1,
          'prov_events' => [LDAP_USER_EVENT_CREATE_LDAP_ENTRY, LDAP_USER_EVENT_SYNCH_TO_LDAP_ENTRY],
        ],
        '[displayname]' => [
          'sid' => 'openldap1',
          'ldap_attr' => '[displayname]',
          'user_attr' => 'user_tokens',
          'convert' => 0,
          'direction' => LDAP_USER_PROV_DIRECTION_TO_LDAP_ENTRY,
          'user_tokens' => '[field.field_fname] [field.field_lname]',
          'config_module' => 'ldap_user',
          'synch_module' => 'ldap_user',
          'enabled' => 1,
          'prov_events' => [LDAP_USER_EVENT_CREATE_LDAP_ENTRY, LDAP_USER_EVENT_SYNCH_TO_LDAP_ENTRY],
        ],
        '[cn]' => [
          'sid' => 'openldap1',
          'ldap_attr' => '[cn]',
          'user_attr' => '[property.name]',
          'convert' => 0,
          'direction' => LDAP_USER_PROV_DIRECTION_TO_LDAP_ENTRY,
          'user_tokens' => '',
          'config_module' => 'ldap_user',
          'synch_module' => 'ldap_user',
          'prov_events' => [LDAP_USER_EVENT_CREATE_LDAP_ENTRY],
        ],
        '[sn]' => [
          'sid' => 'openldap1',
          'ldap_attr' => '[sn]',
          'user_attr' => '[field.field_lname]',
          'convert' => 0,
          'direction' => LDAP_USER_PROV_DIRECTION_TO_LDAP_ENTRY,
          'user_tokens' => '',
          'config_module' => 'ldap_user',
          'synch_module' => 'ldap_user',
          'enabled' => 1,
          'prov_events' => [LDAP_USER_EVENT_CREATE_LDAP_ENTRY, LDAP_USER_EVENT_SYNCH_TO_LDAP_ENTRY],
        ],
        '[givenname]' => [
          'sid' => 'openldap1',
          'ldap_attr' => '[givenname]',
          'user_attr' => '[field.field_fname]',
          'convert' => 0,
          'direction' => LDAP_USER_PROV_DIRECTION_TO_LDAP_ENTRY,
          'user_tokens' => '',
          'config_module' => 'ldap_user',
          'synch_module' => 'ldap_user',
          'enabled' => 1,
          'prov_events' => [LDAP_USER_EVENT_CREATE_LDAP_ENTRY, LDAP_USER_EVENT_SYNCH_TO_LDAP_ENTRY],
        ],
        '[guid]' => [
          'sid' => 'openldap1',
          'ldap_attr' => '[guid]',
          'user_attr' => 'user_tokens',
          'convert' => 0,
          'direction' => LDAP_USER_PROV_DIRECTION_TO_LDAP_ENTRY,
          'user_tokens' => '151',
          'config_module' => 'ldap_user',
          'synch_module' => 'ldap_user',
          'enabled' => 1,
          'prov_events' => [LDAP_USER_EVENT_CREATE_LDAP_ENTRY],
        ],
        '[provisionsource]' => [
          'sid' => 'openldap1',
          'ldap_attr' => '[provisionsource]',
          'user_attr' => 'user_tokens',
          'convert' => 0,
          'direction' => LDAP_USER_PROV_DIRECTION_TO_LDAP_ENTRY,
          'user_tokens' => 'drupal.hogwarts.edu',
          'config_module' => 'ldap_user',
          'synch_module' => 'ldap_user',
          'enabled' => 1,
          'prov_events' => [LDAP_USER_EVENT_CREATE_LDAP_ENTRY],
        ],
      ],
    ],
  ];

  return $conf;

}
