<?php

/**
 * @file
 * Class for ldap authorization of organic groups.
 *
 * @see LdapAuthorizationConsumerAbstract for property
 */

if (function_exists('ldap_servers_module_load_include')) {
  ldap_servers_module_load_include('php', 'ldap_authorization', 'LdapAuthorizationConsumerAbstract.class');
}
else {
  module_load_include('php', 'ldap_authorization', 'LdapAuthorizationConsumerAbstract.class');
}
/**
 *
 */
class LdapAuthorizationConsumerOG extends LdapAuthorizationConsumerAbstract {

  public $consumerType = 'og_group';
  public $allowConsumerObjectCreation = FALSE;
  public $defaultMembershipRid;
  public $anonymousRid;
  public $defaultConsumerConfProperties = [
    'onlyApplyToLdapAuthenticated' => TRUE,
    'useMappingsAsFilter' => TRUE,
    'synchOnLogon' => TRUE,
    'revokeLdapProvisioned' => TRUE,
    'regrantLdapProvisioned' => TRUE,
    'createConsumers' => TRUE,
  ];

  /**
   *
   */
  public function __construct($consumer_type) {

    $this->defaultMembershipRid = NULL;
    $this->anonymousRid = NULL;

    $params = ldap_authorization_og_ldap_authorization_consumer();
    parent::__construct('og_group', $params['og_group']);
  }

  /**
   *
   */
  public function og2ConsumerIdParts($consumer_id) {
    if (!is_scalar($consumer_id)) {
      return [NULL, NULL, NULL];
    }
    $parts = explode(':', $consumer_id);
    return (count($parts) != 3) ? [NULL, NULL, NULL] : $parts;
  }

  /**
   * @see LdapAuthorizationConsumerAbstract::createConsumer
   *
   * this function is not implemented for og, but could be
   * if a use case for generating og groups and roles on the
   * fly existed.
   */
  public function createConsumer($consumer_id, $consumer) {
    return FALSE;
  }

  /**
   * @see LdapAuthorizationConsumerAbstract::normalizeMappings
   */
  public function normalizeMappings($mappings) {
    $new_mappings = [];
    $group_entity_types = og_get_all_group_bundle();
    foreach ($mappings as $i => $mapping) {
      $from = $mapping[0];
      $to = $mapping[1];
      $to_parts = explode('(raw: ', $to);
      $user_entered = $to_parts[0];
      $new_mapping = [
        'from' => $from,
        'user_entered' => $user_entered,
        'valid' => TRUE,
        'error_message' => '',
      ];

      // Has simplified and normalized part in (). update normalized part as validation.
      if (count($to_parts) == 2) {
        $to_normalized = trim($to_parts[1], ')');
        /**
         * users (node:35:1)
         * node:students (node:21:1)
         * faculty (node:33:2)
         * node:35:1 (node:35:1)
         * node:35 (node:35:1)
         */

        $to_simplified = $to_parts[0];
        $to_simplified_parts = explode(':', trim($to_simplified));
        $entity_type = (count($to_simplified_parts) == 1) ? 'node' : $to_simplified_parts[0];
        $role = (count($to_simplified_parts) < 3) ? OG_AUTHENTICATED_ROLE : $to_simplified_parts[2];
        $group_name = (count($to_simplified_parts) == 1) ? $to_simplified_parts[0] : $to_simplified_parts[1];
        list($group_entity, $group_entity_id) = ldap_authorization_og2_get_group_from_name($entity_type, $group_name);
        $to_simplified = join(':', [$entity_type, $group_name]);
      }
      // May be simplified or normalized, but not both.
      else {
        /**
         * users
         * node:students
         * faculty
         * node:35:1
         * node:35
         */
        $to_parts = explode(':', trim($to));
        $entity_type = (count($to_parts) == 1) ? 'node' : $to_parts[0];
        $role = (count($to_parts) < 3) ? OG_AUTHENTICATED_ROLE : $to_parts[2];
        $group_name_or_entity_id = (count($to_parts) == 1) ? $to_parts[0] : $to_parts[1];
        list($group_entity, $group_entity_id) = ldap_authorization_og2_get_group_from_name($entity_type, $group_name_or_entity_id);
        // If load by name works, $group_name_or_entity_id is group title.
        if ($group_entity) {
          $to_simplified = join(':', [$entity_type, $group_name_or_entity_id]);
        }
        else {
          $to_simplified = FALSE;
        }
        $simplified = (boolean) ($group_entity);
        if (!$group_entity && ($group_entity = @entity_load_single($entity_type, $group_name_or_entity_id))) {
          $group_entity_id = $group_name_or_entity_id;
        }
      }
      if (!$group_entity) {
        $new_mapping['normalized'] = FALSE;
        $new_mapping['simplified'] = FALSE;
        $new_mapping['valid'] = FALSE;
        $new_mapping['error_message'] = t("cannot find matching group: !to", ['!to' => $to]);
      }
      else {
        $role_id = is_numeric($role) ? $role : ldap_authorization_og2_rid_from_role_name($entity_type, $group_entity->type, $group_entity_id, $role);
        $roles = og_roles($entity_type, isset($group_entity->type) ? $group_entity->type : NULL, 0, FALSE, TRUE);
        $role_name = is_numeric($role) ? $roles[$role] : $role;
        $to_normalized = join(':', [$entity_type, $group_entity_id, $role_id]);
        $to_simplified = ($to_simplified) ? $to_simplified . ':' . $role_name : $to_normalized;
        $new_mapping['normalized'] = $to_normalized;
        $new_mapping['simplified'] = $to_simplified;
        if ($to == $to_normalized) {
          /**  if not using simplified notation, do not convert to simplified.
           * this would create a situation where an og group
           * can change its title and the authorizations change when the
           * admin specified the group by entity id
           */
          $new_mapping['user_entered'] = $to;
        }
        else {
          $new_mapping['user_entered'] = $to_simplified . ' (raw: ' . $to_normalized . ')';
        }

      }

      $new_mappings[] = $new_mapping;
    }
    return $new_mappings;
  }

  /**
   * In organic groups 7.x-1.x, consumer ids are in form gid-rid such as 3-2, 3-3.  We want highest authorization available granted.
   * But, granting member role (2), revokes other roles such as admin in OG.  So for granting we want the order:
   * 3-1, 3-2, 3-3 such that 3-3 is retained.  For revoking, the order should not matter, but reverse sorting makes
   * intuitive sense.
   */
  public function sortConsumerIds($op, &$consumers) {
    if ($op == 'revoke') {
      krsort($consumers, SORT_STRING);
    }
    else {
      ksort($consumers, SORT_STRING);
    }
  }

  /**
   * @see LdapAuthorizationConsumerAbstract::populateConsumersFromConsumerIds
   */
  public function populateConsumersFromConsumerIds(&$consumers, $create_missing_consumers = FALSE) {

    // Generate a query for all og groups of interest.
    $gids = [];
    foreach ($consumers as $consumer_id => $consumer) {
      list($entity_type, $gid, $rid) = explode(':', $consumer_id);
      $gids[$entity_type][] = $gid;

    }

    foreach ($gids as $entity_type => $gid_x_entity) {
      $og_group_entities[$entity_type] = @entity_load($entity_type, $gid_x_entity);
    }

    foreach ($consumers as $consumer_id => $consumer) {
      list($entity_type, $gid, $rid) = explode(':', $consumer_id);
      $consumer['exists'] = isset($og_group_entities[$entity_type][$gid]);
      $consumer['value'] = ($consumer['exists']) ? $og_group_entities[$entity_type][$gid] : NULL;
      $consumer['map_to_string'] = $consumer_id;
      if (
        empty($consumer['name']) &&
        !empty($og_group_entities[$entity_type][$gid]) &&
        property_exists($og_group_entities[$entity_type][$gid], 'title')
      ) {
        $consumer['name'] = $og_group_entities[$entity_type][$gid]->title;
      }

      if (!$consumer['exists'] && $create_missing_consumers) {
        // @todo if creation of og groups were implemented, function would be called here
        // this would mean mapping would need to have enough info to configure a group,
        // or settings would need to include a default group type to create (entity type,
        // bundle, etc.)
      }
      $consumers[$consumer_id] = $consumer;
    }
  }

  /**
   *
   */
  public function hasAuthorization(&$user, $consumer_id) {
    return ldap_authorization_og2_has_consumer_id($consumer_id, $user->uid);
  }

  /**
   *
   */
  public function flushRelatedCaches($consumers = NULL, $user = NULL) {
    if ($user) {
      // Clear user authorizations cache.
      $this->usersAuthorizations($user, TRUE, FALSE);
    }

    og_membership_invalidate_cache();

    if ($consumers) {
      $gids_to_clear_cache = [];
      foreach ($consumers as $i => $consumer_id) {
        list($entity_type, $gid, $rid) = $this->og2ConsumerIdParts($consumer_id);
        $gids_to_clear_cache[$gid] = $gid;
      }
      og_invalidate_cache(array_keys($gids_to_clear_cache));
    }
    else {
      og_invalidate_cache();
    }
  }

  /**
   * @param string $op
   *   'grant' or 'revoke' signifying what to do with the $consumer_ids.
   * @param drupal user object $object
   * @param array $user_auth_data
   *   is array specific to this consumer_type.  Stored at $user->data['ldap_authorizations'][<consumer_type>].
   * @param $consumers
   *   as associative array in form of LdapAuthorizationConsumerAbstract::populateConsumersFromConsumerIds
   * @param array $ldap_entry,
   *   when available user's ldap entry.
   * @param bool $user_save
   *   indicates is user data array should be saved or not.  this is always overridden for og.
   */
  public function authorizationDiff($existing, $desired) {
    return parent::authorizationDiff($existing, $desired);
  }

  /**
   *
   */
  protected function grantsAndRevokes($op, &$user, &$user_auth_data, $consumers, &$ldap_entry = NULL, $user_save = TRUE) {

    if (!is_array($user_auth_data)) {
      $user_auth_data = [];
    }

    $detailed_watchdog_log = variable_get('ldap_help_watchdog_detail', 0);
    $this->sortConsumerIds($op, $consumers);

    $results = [];
    $watchdog_tokens = [];
    $watchdog_tokens['%username'] = $user->name;
    $watchdog_tokens['%action'] = $op;
    $watchdog_tokens['%user_save'] = $user_save;

    /**
     * get authorizations that exist, regardless of origin or ldap_authorization $user->data
     * in form $users_authorization_consumer_ids = array('3-2', '3,3', '4-2')
     */
    $users_authorization_consumer_ids = $this->usersAuthorizations($user, TRUE);

    $watchdog_tokens['%users_authorization_ids'] = join(', ', $users_authorization_consumer_ids);
    if ($detailed_watchdog_log) {
      watchdog('ldap_authorization', "on call of grantsAndRevokes: user_auth_data=" . print_r($user_auth_data, TRUE), $watchdog_tokens, WATCHDOG_DEBUG);
    }

    /**
     * step #1:  generate $og_actions = array of form $og_actions['revokes'|'grants'][$gid] = $rid
     *  based on all consumer ids granted and revokes
     */
    $og_actions = ['grants' => [], 'revokes' => []];
    $consumer_ids_log = "";
    $log = "";

    foreach ($consumers as $consumer_id => $consumer) {
      if ($detailed_watchdog_log) {
        watchdog('ldap_authorization', "consumer_id=$consumer_id, user_save=$user_save, op=$op", $watchdog_tokens, WATCHDOG_DEBUG);
      }
      $log = "consumer_id=$consumer_id, op=$op,";

      // Does user already have authorization ?
      $user_has_authorization = in_array($consumer_id, $users_authorization_consumer_ids);
      // Is authorization attribute to ldap_authorization_og in $user->data ?
      $user_has_authorization_recorded = isset($user_auth_data[$consumer_id]);

      list($entity_type, $gid, $rid) = $this->og2ConsumerIdParts($consumer_id);

      /** grants **/
      if ($op == 'grant') {
        if ($user_has_authorization && !$user_has_authorization_recorded) {
          // Grant case 1: authorization id already exists for user, but is not ldap provisioned.  mark as ldap provisioned, but don't regrant.
          $results[$consumer_id] = TRUE;
          $user_auth_data[$consumer_id] = [
            'date_granted' => time(),
            'consumer_id_mixed_case' => $consumer_id,
          ];
          $log .= "grant case 1: authorization id already exists for user, but is not ldap provisioned.  mark as ldap provisioned, but don't regrant";
          $log .= $consumer_id;
        }
        elseif (!$user_has_authorization && $consumer['exists']) {
          // Grant case 2: consumer exists, but user is not member. grant authorization.
          $og_actions['grants'][$entity_type][$gid][] = $rid;
          $log .= "grant case 2: consumer exists, but user is not member. grant authorization";
          $log .= " " . $entity_type . ":" . $gid . ":" . $rid;
        }
        elseif ($consumer['exists'] !== TRUE) {
          // Grant case 3: something is wrong. consumers should have been created before calling grantsAndRevokes.
          $results[$consumer_id] = FALSE;
          $log .= "grant case 3: something is wrong. consumers should have been created before calling grantsAndRevokes";
          $log .= " " . $consumer_id;
        }
        elseif ($consumer['exists'] === TRUE) {
          // Grant case 4: consumer exists and user has authorization recorded. do nothing.
          $results[$consumer_id] = TRUE;
          $log .= "grant case 4: consumer exists and user has authorization recorded. do nothing";
          $log .= " " . $consumer_id;
        }
        else {
          // Grant case 5: $consumer['exists'] has not been properly set before calling function.
          $results[$consumer_id] = FALSE;
          watchdog('ldap_authorization', "grantsAndRevokes consumer[exists] not properly set. consumer_id=$consumer_id, op=$op, username=%username", $watchdog_tokens, WATCHDOG_ERROR);
          $log .= "grantsAndRevokes consumer[exists] not properly set. consumer_id=$consumer_id, op=$op, username=%username";
        }
        $consumer_ids_log .= $log;
      }
      /** revokes **/
      elseif ($op == 'revoke') {
        if ($user_has_authorization) {
          // Revoke case 1: user has authorization, revoke it.  revokeSingleAuthorization will remove $user_auth_data[$consumer_id].
          $og_actions['revokes'][$entity_type][$gid][] = $rid;
          $log .= "revoke case 1: user has authorization, revoke it.  revokeSingleAuthorization will remove $consumer_id";
          $log .= " " . $entity_type . ":" . $gid . ":" . $rid;
        }
        elseif ($user_has_authorization_recorded) {
          // Revoke case 2: user does not have authorization, but has record of it. remove record of it.
          unset($user_auth_data[$consumer_id]);
          $results[$consumer_id] = TRUE;
          $log .= "revoke case 2: user does not have authorization, but has record of it. remove record of it.";
          $log .= $consumer_id;
        }
        else {
          // Revoke case 3: trying to revoke something that isn't there.
          $results[$consumer_id] = TRUE;
          $log .= "revoke case 3: trying to revoke something that isn't there";
          $log .= $consumer_id;
        }
      }
      if ($detailed_watchdog_log) {
        watchdog('ldap_authorization', "user_auth_data after consumer $consumer_id" . print_r($user_auth_data, TRUE), $watchdog_tokens, WATCHDOG_DEBUG);
      }
      $consumer_ids_log .= $log;
    }

    $watchdog_tokens['%consumer_ids_log'] = $consumer_ids_log;

    /**
     * Step #2: from array of form:
     *   $og_actions['grants'|'revokes'][$entity_type][$gid][$rid]
     * - generate $user->data['ldap_authorizations'][<consumer_id>]
     * - remove and grant og memberships
     * - remove and grant og roles
     * - flush appropriate caches
     */
    $this->og2Grants($og_actions, $user, $user_auth_data);
    $this->og2Revokes($og_actions, $user, $user_auth_data);

    $user_edit = ['data' => $user->data];
    $user_edit['data']['ldap_authorizations'][$this->consumerType] = $user_auth_data;
    // Force a reload of the user object, since changes made through the grant-
    // and revoke-functions above might have changed og-related field data.
    // Those changes will not yet be reflected in $user, potentially causing
    // data loss when user_save() is called with stale data.
    $user = user_load($user->uid, TRUE);
    $user = user_save($user, $user_edit);

    // Reset this variable because user save hooks can impact it.
    $user_auth_data = $user->data['ldap_authorizations'][$this->consumerType];

    $this->flushRelatedCaches($consumers, $user);

    if ($detailed_watchdog_log) {
      watchdog('ldap_authorization', '%username:
        <hr/>LdapAuthorizationConsumerAbstract grantsAndRevokes() method log.  action=%action:<br/> %consumer_ids_log
        ',
        $watchdog_tokens, WATCHDOG_DEBUG);
    }
  }

  /**
   *
   */
  public function og2Grants($og_actions, &$user, &$user_auth_data) {
    foreach ($og_actions['grants'] as $group_entity_type => $gids) {
      // All rids ldap believes user should be granted and attributed to ldap.
      foreach ($gids as $gid => $granting_rids) {
        // All roles rid => role_name array w/ authen or anon roles.
        $all_group_roles = og_roles($group_entity_type, FALSE, $gid, FALSE, TRUE);
        $authenticated_rid = array_search(OG_AUTHENTICATED_ROLE, $all_group_roles);
        $anonymous_rid = array_search(OG_ANONYMOUS_ROLE, $all_group_roles);
        // All rids array w/ authen or anon rids.
        $all_group_rids = array_keys($all_group_roles);
        // Users current rids w/authen or anon roles returned.
        $users_group_rids = array_keys(og_get_user_roles($group_entity_type, $gid, $user->uid, TRUE));
        $users_group_rids = array_diff($users_group_rids, [$anonymous_rid]);
        // Rids to be added without anonymous rid.
        $new_rids = array_diff($granting_rids, $users_group_rids, [$anonymous_rid]);

        // If adding OG_AUTHENTICATED_ROLE or any other role and does not currently have OG_AUTHENTICATED_ROLE, group.
        if (!in_array($authenticated_rid, $users_group_rids) && count($new_rids) > 0) {
          $values = [
            'entity_type' => 'user',
            'entity' => $user->uid,
            'field_name' => FALSE,
            'state' => OG_STATE_ACTIVE,
          ];
          $og_membership = og_group($group_entity_type, $gid, $values);
          $consumer_id = join(':', [$group_entity_type, $gid, $authenticated_rid]);
          $user_auth_data[$consumer_id] = [
            'date_granted' => time(),
            'consumer_id_mixed_case' => $consumer_id,
          ];
          // Granted on membership creation.
          $new_rids = array_diff($new_rids, [$authenticated_rid]);

        }
        foreach ($new_rids as $i => $rid) {
          og_role_grant($group_entity_type, $gid, $user->uid, $rid);
        }
        foreach ($granting_rids as $i => $rid) {
          // Attribute to ldap regardless of if is being granted.
          $consumer_id = join(':', [$group_entity_type, $gid, $rid]);
          $user_auth_data[$consumer_id] = [
            'date_granted' => time(),
            'consumer_id_mixed_case' => $consumer_id,
          ];
        }
      }
    }
  }

  /**
   *
   */
  public function og2Revokes($og_actions, &$user, &$user_auth_data) {
    foreach ($og_actions['revokes'] as $group_entity_type => $gids) {
      // $revoking_rids are all rids to be removed.  may include authen rids.
      foreach ($gids as $gid => $revoking_rids) {
        // All roles rid => role_name array w/ authen or anon roles.
        $all_group_roles = og_roles($group_entity_type, FALSE, $gid, FALSE, TRUE);
        // All rids array w/ authen or anon rids.
        $all_group_rids = array_keys($all_group_roles);
        // Users current rids w/authen or anon roles returned.
        $users_group_rids = array_keys(og_get_user_roles($group_entity_type, $gid, $user->uid, TRUE));
        // Rids to be left at end of revoke process.
        $remaining_rids = array_diff($users_group_rids, $revoking_rids);
        $authenticated_rid = array_search(OG_AUTHENTICATED_ROLE, $all_group_roles);
        // Remove autenticated and anon rids here.
        foreach ($revoking_rids as $i => $rid) {
          // Revoke if user has role.
          if (in_array($rid, $users_group_rids)) {
            og_role_revoke($group_entity_type, $gid, $user->uid, $rid);
          }
          // Unattribute to ldap even if user does not currently have role.
          unset($user_auth_data[ldap_authorization_og_authorization_id($gid, $rid, $group_entity_type)]);
        }
        // Ungroup if only authenticated and anonymous role left.
        if (in_array($authenticated_rid, $revoking_rids) || count($remaining_rids) == 0) {
          $entity = og_ungroup($group_entity_type, $gid, 'user', $user->uid);
          $result = (boolean) ($entity);
        }
      }
    }
  }

  /**
   * @see ldapAuthorizationConsumerAbstract::usersAuthorizations
   */
  public function usersAuthorizations(&$user, $reset = FALSE, $return_data = TRUE) {

    static $users;
    if (!is_array($users)) {
      // No cache exists, create static array.
      $users = [];
    }
    elseif ($reset && isset($users[$user->uid])) {
      // Clear users cache.
      unset($users[$user->uid]);
    }
    elseif (!$return_data) {
      // Simply clearing cache.
      return NULL;
    }
    elseif (!empty($users[$user->uid])) {
      // Return cached data.
      return $users[$user->uid];
    }

    $authorizations = [];

    $user_entities = entity_load('user', [$user->uid]);
    $memberships = og_get_entity_groups('user', $user_entities[$user->uid]);
    foreach ($memberships as $entity_type => $entity_memberships) {
      foreach ($entity_memberships as $og_membership_id => $gid) {
        $roles = og_get_user_roles($entity_type, $gid, $user->uid);
        foreach ($roles as $rid => $discard) {
          $authorizations[] = ldap_authorization_og_authorization_id($gid, $rid, $entity_type);
        }
      }
    }
    $users[$user->uid] = $authorizations;

    return $authorizations;
  }

  /**
   * @see ldapAuthorizationConsumerAbstract::convertToFriendlyAuthorizationIds
   */
  public function convertToFriendlyAuthorizationIds($authorizations) {
    $authorization_ids_friendly = [];
    foreach ($authorizations as $authorization_id => $authorization) {
      $authorization_ids_friendly[] = $authorization['name'] . '  (' . $authorization_id . ')';
    }
    return $authorization_ids_friendly;
  }

  /**
   * @see ldapAuthorizationConsumerAbstract::validateAuthorizationMappingTarget
   */
  public function validateAuthorizationMappingTarget($mapping, $form_values = NULL, $clear_cache = FALSE) {
    // These mappings have already been through the normalizeMappings() method, so no real querying needed here.
    $has_form_values = is_array($form_values);
    $message_type = NULL;
    $message_text = NULL;
    $pass = !empty($mapping['valid']) && $mapping['valid'] === TRUE;

    /**
     * @todo need to look this over
     *
     */
    if (!$pass) {
      $tokens = [
        '!from' => $mapping['from'],
        '!user_entered' => $mapping['user_entered'],
        '!error' => $mapping['error_message'],
      ];
      $message_text = '<code>"' . t('!map_to|!user_entered', $tokens) . '"</code> ' . t('has the following error: !error.', $tokens);
    }
    return [$message_type, $message_text];
  }

  /**
   * Get list of mappings based on existing Organic Groups and roles.
   *
   * @param array $tokens
   *   Array of tokens and replacement values.
   *
   * @return
   *   HTML examples of mapping values.
   */
  public function mappingExamples($tokens) {
    /**
     * OG 7.x-2.x mappings:
     * $entity_type = $group_type,
     * $bundle = $group_bundle
     * $etid = $gid where edid is nid, uid, etc.
     *
     * og group is: entity_type (eg node) x entity_id ($gid) eg. node:17
     * group identifier = group_type:gid; aka entity_type:etid e.g. node:17
     *
     * membership identifier is:  group_type:gid:entity_type:etid
     * in our case: group_type:gid:user:uid aka entity_type:etid:user:uid e.g. node:17:user:2
     *
     * roles are simply rids ((1,2,3) and names (non-member, member, and administrator member) in og_role table
     * og_users_roles is simply uid x rid x gid
     *
     * .. so authorization mappings should look like:
     *    <ldap group>|group_type:gid:rid such as staff|node:17:2
     */

    $og_fields = field_info_field(OG_GROUP_FIELD);
    $rows = [];
    $role_name = OG_AUTHENTICATED_ROLE;

    if (!empty($og_fields['bundles'])) {
      foreach ($og_fields['bundles'] as $entity_type => $bundles) {
        foreach ($bundles as $i => $bundle) {
          $query = new EntityFieldQuery();
          $query->entityCondition('entity_type', $entity_type)
            ->entityCondition('bundle', $bundle)
            ->range(0, 5)
          // Run the query as user 1.
            ->addMetaData('account', user_load(1));
          $result = $query->execute();
          if (!empty($result)) {
            $entities = entity_load($entity_type, array_keys($result[$entity_type]));
            $i = 0;
            if ($entities) {
              foreach ($entities as $entity_id => $entity) {
                $i++;
                $rid = ldap_authorization_og2_rid_from_role_name($entity_type, $bundle, $entity_id, OG_AUTHENTICATED_ROLE);
                $title = (is_object($entity) && property_exists($entity, 'title')) ? $entity->title : '';
                $middle = ($title && $i < 3) ? $title : $entity_id;
                $group_role_identifier = ldap_authorization_og_authorization_id($middle, $rid, $entity_type);
                $example = "<code>ou=IT,dc=myorg,dc=mytld,dc=edu|$group_role_identifier</code>";
                $rows[] = ["$entity_type $title - $role_name", $example];
              }
            }
          }
        }
      }
    }

    $variables = [
      'header' => ['Group Entity - Group Title - OG Membership Type', 'example'],
      'rows' => $rows,
      'attributes' => [],
    ];

    $table = theme('table', $variables);
    $link = l(t('admin/config/people/ldap/authorization/test/og_group'), 'admin/config/people/ldap/authorization/test/og_group');

    $examples =
    <<<EOT

<br/>
Examples for some (or all) existing OG Group IDs can be found in the table below.
This is complex.  To test what is going to happen, uncheck "When a user logs on" in IV.B.
and use $link to see what memberships sample users would receive.

$table

EOT;
    $examples = t($examples, $tokens);
    return $examples;
  }

}
