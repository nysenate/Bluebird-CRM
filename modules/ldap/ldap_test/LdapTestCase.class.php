<?php

/**
 * @file
 * Simpletest class for LDAP simpletests.
 */

module_load_include('php', 'ldap_test', 'LdapTestFunctions.class');
/**
 *
 */
class LdapTestCase extends DrupalWebTestCase {

  public $testFunctions;
  public $module_name;

  /**
   * Storage for test data.
   */
  public $useFeatureData;
  public $featurePath;
  public $featureName;
  public $consumerAdminConf;

  public $ldapTestId;
  public $authorizationData;
  public $authenticationData;
  public $testData = [];

  /**
   * Current, or only, sid.
   */
  public $sid;

  /**
   *
   */
  public function __construct($test_id = NULL) {
    parent::__construct($test_id);
    $this->testFunctions = new LdapTestFunctions();
  }

  /**
   *
   */
  public function setUp() {
    $modules = func_get_args();
    if (isset($modules[0]) && is_array($modules[0])) {
      $modules = $modules[0];
    }
    parent::setUp($modules);
    variable_set('ldap_simpletest', 2);
    variable_set('ldap_help_watchdog_detail', 0);
    $this->createTestUserFields();

  }

  /**
   *
   */
  public function tearDown() {
    parent::tearDown();
    variable_del('ldap_help_watchdog_detail');
    variable_del('ldap_simpletest');
  }

  /**
   * Setup configuration and fake test data for all ldap modules.
   *
   * @param string $test_ldap_id
   *   name of directory in ldap_test where data is (e.g. hogwarts)
   *
   *   the following params are ids that indicate what config data in /ldap_test/<module_name>.conf.inc to use
   *   for example if $ldap_user_conf_id = 'ad_authentication', the array /ldap_test/ldap_user.conf.inc with the key
   *   'ad_authentication' will be used for the user module cofiguration.
   *
   * @param array $sids
   *   to setup.
   * @param string $ldap_user_conf_id
   * @param string $ldap_authentication_conf_id
   *   = NULL,.
   * @param string $ldap_authorization_conf_id
   *   = NULL,.
   * @param string $ldap_feeds_conf_id
   *   = NULL,.
   * @param string $ldap_query_conf_id
   *   = NULL.
   */
  public function prepTestData(
      $test_ldap_id,
      $sids,
      $ldap_user_conf_id = NULL,
      $ldap_authentication_conf_id = NULL,
      $ldap_authorization_conf_id = NULL,
      $ldap_feeds_conf_id = NULL,
      $ldap_query_conf_id = NULL
    ) {

    $this->testFunctions->configureLdapServers($sids);

    foreach ($sids as $sid) {
      $this->testFunctions->populateFakeLdapServerData($test_ldap_id, $sid);
    }

    if ($ldap_user_conf_id) {
      $this->testFunctions->configureLdapUser($ldap_user_conf_id);
    }
    if ($ldap_authentication_conf_id) {
      $this->testFunctions->configureLdapAuthentication($ldap_authentication_conf_id, $sids);
    }

    if ($ldap_authorization_conf_id) {
      $authorization_data = ldap_test_ldap_authorization_data();
      if (!empty($authorization_data[$ldap_authorization_conf_id])) {
        $this->testFunctions->prepConsumerConf($authorization_data[$ldap_authorization_conf_id]);
        foreach ($authorization_data[$ldap_authorization_conf_id] as $consumer_type => $discard) {
          $this->consumerAdminConf[$consumer_type] = ldap_authorization_get_consumer_admin_object($consumer_type);
        }
      }
    }
  }

  /**
   * Attempt to derive a testid from backtrace.
   */
  public function testId($description = NULL, $method = NULL) {

    static $test_id;
    static $i;

    if ($description || $method) {
      $test_id = NULL;
      $i = 0;
    }
    // Default test id.
    elseif ($test_id) {
      $i++;
      return $test_id . '.' . $i;
    }
    if (!$method) {
      $trace = debug_backtrace();

      $caller = array_shift($trace);
      $caller = array_shift($trace);
      $method = $caller['function'];
      $count = 1;
      $method = str_replace('test', '', $method, $count);
    }

    $test_id = join(".", [$this->module_name, $method, $description]);
    return $test_id;

  }

  /**
   *
   */
  public function removeUserFromGroup(&$test_data, $user_dn, $group_dn, $domain = "dc=hogwarts,dc=edu") {

    $filter = "(&(objectClass=group)(member=$user_dn))";
    if (!empty($test_data['search_results'][$filter][$domain]) &&
            in_array($group_dn, $test_data['search_results'][$filter][$domain])) {
      $test_data['search_results'][$filter][$domain] = array_diff($test_data['search_results'][$filter][$domain], [$group_dn]);
      $test_data['search_results'][$filter][$domain]['count'] = count($test_data['search_results'][$filter][$domain] - 1);
    }

    if (!empty($test_data['users'][$user_dn]['attr']['memberof']) && in_array($group_dn, $test_data['users'][$user_dn]['attr']['memberof'])) {
      $test_data['users'][$user_dn]['attr']['memberof'] = array_diff($test_data['users'][$user_dn]['attr']['memberof'], [$group_dn]);
      $test_data['users'][$user_dn]['attr']['memberof']['count'] = count($test_data['users'][$user_dn]['attr']['memberof'] - 1);
    }

    if (!empty($test_data['ldap'][$user_dn]['memberof']) && in_array($group_dn, $test_data['ldap'][$user_dn]['memberof'])) {
      $test_data['ldap'][$user_dn]['memberof'] = array_diff($test_data['ldap'][$user_dn]['memberof'], [$group_dn]);
      $test_data['ldap'][$user_dn]['memberof']['count'] = count($test_data['ldap'][$user_dn]['memberof']) - 1;
    }

    if (!empty($test_data['groups'][$group_dn]['attr']['member']) && in_array($group_dn, $test_data['groups'][$group_dn]['attr']['member'])) {
      $members = array_diff($test_data['groups'][$group_dn]['attr']['member'], [$group_dn]);
      $test_data['groups'][$group_dn]['attr']['member'] = $members;
      $test_data['groups'][$group_dn]['attr']['member'][$i]['count'] = count($members - 1);
    }
  }

  /**
   *
   */
  public function AttemptLogonNewUser($name, $goodpwd = TRUE) {

    $this->drupalLogout();

    $edit = [
      'name' => $name,
      'pass' => ($goodpwd) ? "goodpwd" : "badpwd",
    ];
    $user = user_load_by_name($name);
    if ($user) {
      user_delete($user->uid);
    }
    $this->drupalPost('user', $edit, t('Log in'));
  }

  /**
   * Keep user entity fields function for ldap_user
   * in base class instead of user test class in case
   * module integration testing is needed.
   */
  public function createTestUserFields() {
    foreach ($this->ldap_user_test_entity_fields() as $field_id => $field_conf) {
      $field_info = field_info_field($field_id);
      if (!$field_info) {
        field_create_field($field_conf['field']);
        field_create_instance($field_conf['instance']);
      }
      $field_info = field_info_field($field_id);
    }
  }

  /**
   *
   */
  public function ldap_user_test_entity_fields() {

    $fields = [];

    $fields['field_lname']['field'] = [
      'field_name' => 'field_lname',
      'type' => 'text',
      'settings' => [
        'max_length' => 64,
      ],
    ];

    $fields['field_lname']['instance'] = [
      'field_name' => 'field_lname',
      'entity_type' => 'user',
      'label' => 'Last Name',
      'bundle' => 'user',
      'required' => FALSE,
      'widget' => [
        'type' => 'text_textfield',
      ],
      'display' => [
        'default' => [
          'type' => 'text_default',
        ],
      ],
      'settings' => ['user_register_form' => FALSE],
    ];

    $fields['field_department']['field'] = [
      'field_name' => 'field_department',
      'type' => 'text',
      'settings' => [
        'max_length' => 64,
      ],
    ];

    $fields['field_department']['instance'] = [
      'field_name' => 'field_department',
      'entity_type' => 'user',
      'label' => 'Department',
      'bundle' => 'user',
      'required' => FALSE,
      'widget' => [
        'type' => 'text_textfield',
      ],
      'display' => [
        'default' => [
          'type' => 'text_default',
        ],
      ],
      'settings' => ['user_register_form' => FALSE],
    ];

    $fields['field_fname']['field'] = [
      'field_name' => 'field_fname',
      'type' => 'text',
      'settings' => [
        'max_length' => 64,
      ],
    ];

    $fields['field_fname']['instance'] = [
      'field_name' => 'field_fname',
      'entity_type' => 'user',
      'label' => 'Last Name',
      'bundle' => 'user',
      'required' => FALSE,
      'widget' => [
        'type' => 'text_textfield',
      ],
      'display' => [
        'default' => [
          'type' => 'text_default',
        ],
      ],
      'settings' => ['user_register_form' => FALSE],
    ];

    // Display name for testing compound tokens.
    $fields['field_display_name']['field'] = [
      'field_name' => 'field_display_name',
      'type' => 'text',
      'settings' => [
        'max_length' => 64,
      ],
    ];

    $fields['field_display_name']['instance'] = [
      'field_name' => 'field_display_name',
      'entity_type' => 'user',
      'label' => 'Display Name',
      'bundle' => 'user',
      'required' => FALSE,
      'widget' => [
        'type' => 'text_textfield',
      ],
      'display' => [
        'default' => [
          'type' => 'text_default',
        ],
      ],
      'settings' => ['user_register_form' => FALSE],
    ];

    // Display name for testing compound tokens.
    $fields['field_binary_test']['field'] = [
      'field_name' => 'field_binary_test',
      'type' => 'text',
      'size' => 'big',
    ];

    $fields['field_binary_test']['instance'] = [
      'field_name' => 'field_binary_test',
      'entity_type' => 'user',
      'label' => 'Binary Field',
      'bundle' => 'user',
      'required' => FALSE,
      'widget' => [
        'type' => 'text_textfield',
      ],
      'display' => [
        'default' => [
          'type' => 'text_default',
        ],
      ],
      'settings' => ['user_register_form' => FALSE],
    ];

    return $fields;

  }

  /**
   *
   */
  public function checkConsumerConfSetup($conf_id) {

    $authorization_data = ldap_test_ldap_authorization_data();
    $props_set_correctly = TRUE;

    foreach ($authorization_data[$conf_id] as $consumer_type => $conf) {
      $props_set_display = [];
      foreach ($conf as $prop => $values) {
        if (!property_exists($this->consumerAdminConf[$consumer_type], $prop)) {
          $props_set_correctly = FALSE;
          $props_set_display[] = $prop . " $consumer_type property does not exist in consumer conf object";
        }
        elseif (is_scalar($this->consumerAdminConf[$consumer_type]->{$prop})) {
          if ($this->consumerAdminConf[$consumer_type]->{$prop} == $values) {
            $props_set_display[] = $prop . " $consumer_type set to $values correctly";
          }
          else {
            $props_set_correctly = FALSE;
            $props_set_display[] = $prop . " $consumer_type not set to $values correctly";
          }
        }
      }
    }

    return [$props_set_display, $props_set_correctly];
  }

  /**
   *
   */
  public function compareFormToProperties($object, $data, $item_id, $map, $lcase_transformed) {

    $mismatches = [];
    foreach ($data as $field_id => $values) {
      $field_id = drupal_strtolower($field_id);
      if (!isset($map[$field_id])) {
        continue;
      }
      $property = $map[$field_id];
      if (!is_object($object) || !property_exists($object, $property) && !property_exists($object, drupal_strtolower($property))) {
        continue;
      }
      $property_value = $object->{$property};

      // For cases where string input is not same as array.
      $field_value = isset($values[$item_id + 2]) ? $values[$item_id + 2] : $values[$item_id];

      if (in_array($field_id, $lcase_transformed) && is_scalar($field_value)) {
        $field_value = drupal_strtolower($field_value);
      }
      $property_value_show = (is_scalar($property_value)) ? $property_value : serialize($property_value);
      $field_value_show = (is_scalar($field_value)) ? $field_value : serialize($field_value);

      if (is_array($property_value) && is_array($field_value)) {
        $pass = count(array_diff($property_value, $field_value)) == 0;
      }
      elseif (is_scalar($property_value) && is_scalar($field_value)) {
        $pass = ($property_value == $field_value);
      }
      else {
        $pass = FALSE;
      }

      if (!$pass) {
        $mismatches[] = "property $property ($property_value_show) does not match field $field_id value ($field_value_show)";
      }
    }

    return $mismatches;
  }

}
