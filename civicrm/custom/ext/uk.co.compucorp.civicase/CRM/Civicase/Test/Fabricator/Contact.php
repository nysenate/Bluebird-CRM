<?php

/**
 * Fabricates contacts using API calls.
 */
class CRM_Civicase_Test_Fabricator_Contact {

  private static $defaultParams = array(
    'contact_type' => 'Individual',
    'first_name'   => 'John',
    'last_name'    => 'Doe',
    'sequential'   => 1
  );

  public static function fabricate($params = array()) {
    $params = array_merge(self::$defaultParams, $params);
    $params['display_name'] = "{$params['first_name']} {$params['last_name']}";
    $result = civicrm_api3(
      'Contact',
      'create',
      $params
    );
    return array_shift($result['values']);
  }

  public static function fabricateWithEmail($params = array(), $email = 'johndoe@test.com') {
    $contact = self::fabricate($params);
    civicrm_api3('Email', 'create', array(
      'email' => $email,
      'contact_id' => $contact['id'],
      'is_primary' => 1
    ));
    return $contact;
  }

  public static function fabricateOrganization($params = array()) {
    $params['contact_type'] = 'Organization';
    $params['organization_name'] = empty($params['organization_name'])
      ? 'Organization ' . rand(1000, 9999)
      : $params['organization_name'];
    $result = civicrm_api3(
      'Contact',
      'create',
      $params
    );
    return array_shift($result['values']);
  }

}
