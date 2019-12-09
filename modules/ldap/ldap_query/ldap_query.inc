<?php

/**
 * @file
 */

/**
 * See ldap_query_get_servers()
 */
function _ldap_query_get_queries($qid, $type, $flatten, $reset) {
  ldap_servers_module_load_include('php', 'ldap_query', 'LdapQueryAdmin.class');
  static $queries;

  if ($reset) {
    $queries = [];
  }
  if (!isset($queries['all'])) {
    $queries['all'] = LdapQueryAdmin::getLdapQueryObjects('all', 'all');
  }
  if (!isset($queries['enabled'])) {
    $queries['enabled'] = [];
    foreach ($queries['all'] as $_qid => $ldap_query) {
      if ($ldap_query->status == 1) {
        $queries['enabled'][$_qid] = $ldap_query;
      }
    }
  }

  if ($qid) {
    if (!isset($queries[$type][$qid])) {
      return FALSE;
    }
    return ($flatten) ? $queries[$type][$qid] : $queries[$type];
  }

  if (isset($queries[$type])) {
    return $queries[$type];
  }
}
