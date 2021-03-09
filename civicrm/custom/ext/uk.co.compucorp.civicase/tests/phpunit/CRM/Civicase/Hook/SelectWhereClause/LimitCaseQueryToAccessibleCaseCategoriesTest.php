<?php

use CRM_Civicase_Test_Fabricator_Contact as ContactFabricator;
use CRM_Civicase_Test_Fabricator_CaseType as CaseTypeFabricator;
use CRM_Civicase_Test_Fabricator_Case as CaseFabricator;

/**
 * Tests for limiting case query to accessible case categories.
 *
 * @group headless
 */
class CRM_Civicase_Hook_SelectWhereClause_LimitCaseQueryToAccessibleCaseCategoriesTest extends BaseHeadlessTest {

  use CRM_Civicase_Helpers_SessionTrait;

  /**
   * Fabricated client.
   *
   * @var array
   */
  private static $client;

  /**
   * Fabricated case types.
   *
   * @var array
   */
  private static $caseTypes;

  /**
   * Setup case types and cases before running of tests.
   */
  public static function setupBeforeClass() {
    civicrm_api3('OptionValue', 'create', [
      'option_group_id' => 'case_type_categories',
      'name' => 'award',
      'value' => 2,
    ]);
    static::$caseTypes[] = CaseTypeFabricator::fabricate([
      'name' => 'Case_type_first',
      'case_type_category' => 1,

    ]);
    static::$caseTypes[] = CaseTypeFabricator::fabricate([
      'name' => 'Case_type_second',
      'case_type_category' => 2,
    ]);
    static::$client = ContactFabricator::fabricate();

    CaseFabricator::fabricate(
      [
        'case_type_id' => static::$caseTypes[0]['id'],
        'contact_id' => static::$client['id'],
        'creator_id' => static::$client['id'],
      ]
    );
    CaseFabricator::fabricate(
      [
        'case_type_id' => static::$caseTypes[1]['id'],
        'contact_id' => static::$client['id'],
        'creator_id' => static::$client['id'],
      ]
    );
  }

  /**
   * Test that all records are returned if user has no permissions.
   */
  public function testAllCasesAreReturnedForNoPermissions() {
    $contact = ContactFabricator::fabricate();
    $this->registerCurrentLoggedInContactInSession($contact['id']);
    $this->setPermissions(['access my cases and activities']);

    $result = civicrm_api3('Case', 'get', [
      'contact_id' => static::$client['id'],
      'check_permissions' => 1,
    ]);

    $this->assertEquals($result['count'], 2);
  }

  /**
   * Test that all records are returned if user has all permissions.
   */
  public function testAllCasesAreReturnedForAllPermissions() {
    $contact = ContactFabricator::fabricate();
    $this->registerCurrentLoggedInContactInSession($contact['id']);
    $this->setPermissions(['basic case information', 'basic award information']);

    $result = civicrm_api3('Case', 'get', [
      'contact_id' => static::$client['id'],
      'check_permissions' => 1,
    ]);

    $this->assertEquals($result['count'], 2);
  }

  /**
   * Test that only accessible records are returned for limited permissions.
   */
  public function testOnlyAccessibleCasesAreReturnedForLimitedPermissions() {
    $contact = ContactFabricator::fabricate();
    $this->registerCurrentLoggedInContactInSession($contact['id']);
    $this->setPermissions(['basic case information']);

    $result = civicrm_api3('Case', 'get', [
      'contact_id' => static::$client['id'],
      'check_permissions' => 1,
      'sequential' => 1,
    ]);

    $this->assertEquals($result['count'], 1);
    $this->assertEquals($result['values'][0]['case_type_id'], static::$caseTypes[0]['id']);
  }

  /**
   * Test that only accessible records are returned for limited permissions.
   */
  public function testNoCasesAreReturnedWhenUserTriesToFetchCasesForCategoryItDoesNotHaveAccessTo() {
    $contact = ContactFabricator::fabricate();
    $this->registerCurrentLoggedInContactInSession($contact['id']);
    $this->setPermissions([
      'basic case information',
      'access CiviCRM',
      'access my award and activities',
    ]);

    $result = civicrm_api3('Case', 'getcaselist', [
      'contact_id' => static::$client['id'],
      'case_type_id.case_type_category' => [
        'IN' => ['award'],
      ],
      'check_permissions' => 1,
      'sequential' => 1,
    ]);

    $this->assertEquals($result['count'], 0);
  }

  /**
   * Test that only accessible records are returned for limited permissions.
   */
  public function testCasesAreReturnedOnlyForCasesUserHasAccessToWhenCaseCategoryParameterIsSent() {
    $contact = ContactFabricator::fabricate();
    $this->registerCurrentLoggedInContactInSession($contact['id']);
    $this->setPermissions([
      'basic award information',
      'access CiviCRM',
      'access my award and activities',
    ]);

    $result = civicrm_api3('Case', 'getcaselist', [
      'contact_id' => static::$client['id'],
      'case_type_id.case_type_category' => [
        'IN' => ['award'],
      ],
      'check_permissions' => 1,
      'sequential' => 1,
    ]);

    $this->assertEquals($result['count'], 1);
    $this->assertEquals($result['values'][0]['case_type_id'], static::$caseTypes[1]['id']);
  }

}
