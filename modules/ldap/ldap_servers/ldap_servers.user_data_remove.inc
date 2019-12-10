<?php

/**
 * @file
 * Collection of functions related to removing user data.
 */

/**
 *
 */
function ldap_severs_user_data_setup_batch($consumer_type = NULL) {

  $max_uid = db_query("SELECT max(uid) FROM {users}")->fetchField();
  $step = 100;
  $operations = [];
  for ($uid = 2; $uid <= $max_uid; $uid += $step) {
    $operations[] = ["ldap_servers_empty_user_data", [$uid, $step, $consumer_type]];
  }

  // Put all that information into our batch array.
  return [
    'operations' => $operations,
    'title' => t('Empty LDAP Authorization Data in user->data[ldap_authorizations][%consumer_id]', ['%consumer_id' => $consumer_type]),
    'init_message' => t('Initializing'),
    'error_message' => t('An error occurred'),
    'finished' => t('Finished.'),
  ];

}

/**
 * Function to remove $user->data['ldap_authorizations'] on uninstall
 * which is called from ldap_authorization uninstall batches.
 */
function ldap_servers_empty_user_data($start, $step, $consumer_type, &$context) {

  $query = new EntityFieldQuery();
  $query->entityCondition('entity_type', 'user')
    ->entityCondition('entity_id', [$start, $start + $step - 1], 'BETWEEN');
  $results = $query->execute();

  if (isset($results['user'])) {
    foreach ($results['user'] as $uid => $entity_data) {
      if ($uid > 1 && $account = user_load($uid, TRUE)) {
        // Remove all authorization data.
        if ($consumer_type == NULL && isset($account->data['ldap_authorizations'])) {
          $names[] = $account->name;
          unset($account->data['ldap_authorizations']);
          $updated_account = user_save($account, ['data' => $account->data]);
        }
        // Remove only a particular consumers authorization data.
        elseif ($consumer_type != NULL && isset($account->data['ldap_authorizations'][$consumer_type])) {
          unset($account->data['ldap_authorizations'][$consumer_type]);
          $updated_account = user_save($account, ['data' => $account->data]);
        }
      }
    }
  }
}

/**
 *
 */
function ldap_authorization_generate_users() {
  $response = "";
  for ($i = 1; $i < 1000; $i++) {
    $name = "user" . $i;
    if ($account = user_load_by_name($name)) {
      user_delete($account->uid);
    }
    $account = new stdClass();
    $account->is_new = TRUE;
    $account->name = "user" . $i;
    $user_edit = [
      'data' => ['ldap_authorizations' => ['og_group' => 7, 'drupal_role' => 8]],
    ];

    $user_response = user_save($account, $user_edit);
    $response .= $user_response->name . "<br/>";
  }
  return $response;

}
