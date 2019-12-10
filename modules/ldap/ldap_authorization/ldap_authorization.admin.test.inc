<?php

/**
 * @file
 * Form to test a ldap authorization consumer configuration.
 */

/**
 * Form for adding, updating, and deleting a single ldap authorization consumer configuration.
 *
 * @param mixed $form
 * @param mixed $form_state
 *
 * @return array drupal form array
 */
function ldap_authorization_test_form($form, &$form_state, $consumer_type) {
  $consumer = ldap_authorization_get_consumer_object($consumer_type);
  $consumer_tokens = ldap_authorization_tokens($consumer);

  if (isset($_SESSION['ldap_authorization_test_query']['result'])) {
    $form['result'] = [
      '#type' => 'item',
      '#markup' => $_SESSION['ldap_authorization_test_query']['result'],
    ];

    $form['pre-filtered'] = [
      '#type' => 'fieldset',
      '#title' => t('Prefiltered and Final Mappings'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];
    $form['pre-filtered']['overview'] = [
      '#type' => 'item',
      '#markup' => t('Below are the "raw authorizations" and which strategy they are derived from.  If enabled, the mapping filter is applied to these.'),
    ];

    if (isset($_SESSION['ldap_authorization_test_query']['maps'])) {
      $i = 0;
      foreach ($_SESSION['ldap_authorization_test_query']['maps'] as $map => $data) {
        $i++;
        $form['pre-filtered']['map' . $i] = [
          '#type' => 'item',
          '#markup' => is_scalar($data) ? "<h2>$map (without filter)</h2>" . $data : theme('item_list', ['items' => $data, 'type' => 'ul', 'title' => "$map (without filter)"]),
        ];
      }
    }

    $form['use_first_attr_as_groupid'] = [
      '#type' => 'fieldset',
      '#title' => t('After "Convert full dn to value of first attribute before mapping"'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];

    if ($consumer->consumerConf->useFirstAttrAsGroupId) {
      if (isset($_SESSION['ldap_authorization_test_query']['useFirstAttrAsGroupId'])) {
        $form['use_first_attr_as_groupid']['data'] = [
          '#type' => 'item',
          '#markup' => theme('item_list', ['items' => $_SESSION['ldap_authorization_test_query']['useFirstAttrAsGroupId'], 'type' => 'ul', 'title' => "Convert full dn to value of first attribute before mapping"]),
        ];
      }
    }
    else {
      $form['use_first_attr_as_groupid']['data'] = [
        '#type' => 'item',
        '#markup' => "Convert full dn to value of first attribute before mapping. NOT enabled",
      ];
    }

    $form['post mappings'] = [
      '#type' => 'fieldset',
      '#title' => t('After Mappings and Filters Applied'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];

    $maps = [];
    foreach ($consumer->consumerConf->mappings as $i => $mapping) {
      $maps[] = $mapping['from'] . '|' . $mapping['user_entered'];
    }

    $form['post mappings']['conf'] = [
      '#type' => 'item',
      '#markup' => 'Use Mappings as Filter = ' . (int) $consumer->consumerConf->useMappingsAsFilter .
      theme('item_list', [
        'items' => $maps,
        'type' => 'ul',
        'title' => 'Configured Mappings',
      ]
      ),
    ];
    if ($consumer->consumerConf->mappings) {
      $form['post mappings']['conf']['#markup'] =
      'Use Mappings as Filter = ' . (int) $consumer->consumerConf->useMappingsAsFilter .
          theme('item_list', [
            'items' => $maps,
            'type' => 'ul',
            'title' => 'Configured Mappings',
          ]
      );
    }
    else {
      $form['post mappings']['conf']['#markup'] =
      'Use Mappings as Filter = ' .
      (int) $consumer->consumerConf->useMappingsAsFilter .
      '<br/>No Mappings Configured';
    }

    if (isset($_SESSION['ldap_authorization_test_query']['post mappings'])) {
      $form['post mappings']['data'] = [
        '#type' => 'item',
        '#markup' => theme('item_list', ['items' => $_SESSION['ldap_authorization_test_query']['post mappings'], 'type' => 'ul', 'title' => "Results after any filtering and mappings applied"]),
      ];
    }

  }

  if (isset($_SESSION['ldap_authorization_test_query']['setting_data'])) {
    foreach ($_SESSION['ldap_authorization_test_query']['setting_data'] as $title => $data) {
      $form[$title] = [
        '#type' => 'fieldset',
        '#title' => $title,
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
      ];
      $form[$title]['overview'] = [
        '#type' => 'item',
        '#markup' => "<pre>" . print_r($data, TRUE) . "</pre>",
      ];
    }
  }

  $form['intro'] = [
    '#type' => 'item',
    '#markup' => t('<h1>Test LDAP to !consumer_name Configuration</h1>
      <p>This form will not actually grant any authorizations, its just to show
      what authorizations would be granted with this configuration.</p>', $consumer_tokens),
  ];

  $form['consumer_type'] = [
    '#type' => 'hidden',
    '#default_value' => $consumer_type,
  ];

  $form['usernames'] = [
    '#type' => 'textarea',
    '#title' => t('Drupal usernames to test !consumer_shortName authorizations results for.  One per line.', $consumer_tokens),
    '#default_value' => @$_SESSION['ldap_authorization_test_query']['usernames'],
    '#cols' => 50,
    '#rows' => 6,
    '#description' => '',
  ];

  $form['random_users'] = [
    '#type' => 'checkbox',
    '#default_value' => @$_SESSION['ldap_authorization_test_form']['random_users'],
    '#title' => t('Use 10 random users', $consumer_tokens),
  ];

  $form['execute_authorizations'] = [
    '#type' => 'checkbox',
    '#default_value' => @$_SESSION['ldap_authorization_test_form']['execute_authorizations'],
    '#title' => t('Actually grant or revoke example authorizations.
      This will grant and revoke based on the ldap authorization configuration
      options such as whether to revoke or regrant manually applied authorizations.
      Try with this unchecked first, then check to see how authorizations are applied.', $consumer_tokens),
  ];

  $form['user_data_clear'] = [
    '#type' => 'checkbox',
    '#default_value' => @$_SESSION['ldap_authorization_test_form']['user_data_clear'],
    '#title' => t('Clear <pre>$user->data[ldap_authorization][<consumer type>]</pre> data for test users.', $consumer_tokens),
  ];

  $form['submit'] = [
    '#type' => 'submit',
    '#value' => 'test',
  ];
  unset($_SESSION['ldap_authorization_test_query']);
  return $form;
}

/**
 * Validate handler for the ldap_authorization_test.
 */
function ldap_authorization_test_form_validate($form, &$form_state) {

  $values = $form_state['values'];
  if (!($values['usernames'] || $values['random_users'])) {
    form_set_error('No options chosen', t('Usernames must be given or random users checked.'));
  }

}

/**
 * Submit handler function for ldap_authorization_test.
 */
function ldap_authorization_test_form_submit($form, &$form_state) {
  $consumer_type = $form_state['values']['consumer_type'];
  $consumer = ldap_authorization_get_consumer_object($consumer_type);
  $consumer_tokens = ldap_authorization_tokens($consumer);

  $results = [];
  $users_listed = $array = preg_split('/[\n\r]+/', $form_state['values']['usernames']);
  $random_users = [];
  $_SESSION['ldap_authorization_test_query']['random_users'] = $form_state['values']['random_users'];
  $_SESSION['ldap_authorization_test_query']['usernames'] = $form_state['values']['usernames'];
  // Add 10 random usernames to test.
  if ($form_state['values']['random_users']) {
    // Not using user_load_multiple because need randomness outside of query.
    $select = db_select('users', 'u');
    $select->fields('u');

    try {
      $random_users = $select->execute()->fetchAllAssoc('name', PDO::FETCH_ASSOC);
    }
    catch (Exception $e) {
      drupal_set_message(t('db users query failed. Message = %message, query= %query',
        ['%message' => $e->getMessage(), '%query' => $e->query_string]), 'error');
      return "";
    }

  }
  $user_names = array_unique(array_merge(array_keys($random_users), $users_listed));
  $i = 0;
  foreach ($user_names as $username) {
    if ($username) {
      if (!$user = user_load_by_name($username)) {
        // If not existing user, create fake user assumed to be ldap authenticated.
        $user = new stdClass();
        $user->name = $username;
        $user->ldap_test = TRUE;
        $user->ldap_authenticated = TRUE;
      }
      else {
        if (function_exists('dpm')) {
          dpm("user: $username"); dpm($user);
        }
      }

      if ($form_state['values']['user_data_clear'] == 1) {
        $user_data = $user->data;
        unset($user_data['ldap_authorizations'][$consumer_type]);
        $user = user_save($user, ['data' => $user_data]);
      }

      $action = ($form_state['values']['execute_authorizations'] == 1) ? "test_query_set" : 'test_query';
      list($results[$username], $notifications[$username]) = ldap_authorizations_user_authorizations($user, $action, $consumer_type, 'logon');
      // Remove authorizations from other consumer types.
      $results[$username] = [$consumer_type => $results[$username][$consumer_type]];
      $i++;
      if ($i == 10) {
        break;
      }
    }
  }

  $table = theme('ldap_authorization_test_results', ['results' => $results, 'consumer' => $consumer, 'notifications' => $notifications]);
  $_SESSION['ldap_authorization_test_query']['result'] = $table;
  $form_state['redirect'] = LDAP_SERVERS_MENU_BASE_PATH . '/authorization/test/' . $consumer->consumerType;

}
