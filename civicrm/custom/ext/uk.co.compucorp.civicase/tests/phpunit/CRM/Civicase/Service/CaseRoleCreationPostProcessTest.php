<?php

use CRM_Civicase_Test_Fabricator_Relationship as RelationshipFabricator;
use CRM_Civicase_Test_Fabricator_RelationshipType as RelationshipTypeFabricator;
use CRM_Civicase_Test_Fabricator_Contact as ContactFabricator;
use CRM_Civicase_Service_CaseRoleCreationPostProcess as CaseRoleCreationPostProcess;
use CRM_Civicase_Test_Fabricator_CaseType as CaseTypeFabricator;
use CRM_Civicase_Test_Fabricator_Case as CaseFabricator;

/**
 * Runs tests on CaseRoleCreationPostProcess Service tests.
 *
 * @group headless
 */
class CRM_Civicase_Service_CaseRoleCreationPostProcessTest extends BaseHeadlessTest {

  use CRM_Civicase_Helpers_CaseSettingsTrait;
  use CRM_Civicase_Helpers_SessionTrait;

  /**
   * Setup data before tests run.
   */
  public function setUp() {
    $contact = ContactFabricator::fabricate();
    $this->registerCurrentLoggedInContactInSession($contact['id']);
  }

  /**
   * Test All previous relationship set inactive when single case role on.
   */
  public function testAllPreviousRelationshipIsSetInActiveWhenSingleCaseRoleSettingIsOnAndMultiClientOff() {
    $this->setSingleCaseRoleSetting(TRUE);
    $this->setMultiClientCaseSetting(FALSE);
    $caseType = CaseTypeFabricator::fabricate();
    $client = ContactFabricator::fabricate();

    $case = CaseFabricator::fabricate(
      [
        'case_type_id' => $caseType['id'],
        'contact_id' => $client['id'],
        'creator_id' => $client['id'],
      ]
    );

    $relationshipTypeAParams = [
      'name_a_b' => 'Manager is',
      'name_b_a' => 'Manager',
    ];
    $relationshipTypeBParams = [
      'name_a_b' => 'Regulator is',
      'name_b_a' => 'Regulator',
    ];
    $contactBDetails = ['first_name' => 'First B', 'last_name' => 'Last B'];
    $contactCDetails = ['first_name' => 'First C', 'last_name' => 'Last C'];
    $relationshipTypeA = RelationshipTypeFabricator::fabricate($relationshipTypeAParams);
    $relationshipTypeB = RelationshipTypeFabricator::fabricate($relationshipTypeBParams);
    $contactA = ContactFabricator::fabricate();
    $contactB = ContactFabricator::fabricate($contactBDetails);
    $contactC = ContactFabricator::fabricate($contactCDetails);

    // Contact A is Manager to Client.
    $params = [
      'contact_id_b' => $contactA['id'],
      'contact_id_a' => $client['id'],
      'relationship_type_id' => $relationshipTypeA['id'],
      'case_id' => $case['id'],
      'skip_post_processing' => 1,
    ];
    $relationship1 = RelationshipFabricator::fabricate($params);

    // Contact B is Regulator to Client.
    $params = [
      'contact_id_b' => $contactB['id'],
      'contact_id_a' => $client['id'],
      'relationship_type_id' => $relationshipTypeB['id'],
      'case_id' => $case['id'],
      // This will prevent the On respond event from being triggered.
      'skip_post_processing' => 1,
    ];
    $relationship2 = RelationshipFabricator::fabricate($params);

    // Contact C is Regulator to Client.
    $latestRelParams = [
      'contact_id_b' => $contactC['id'],
      'contact_id_a' => $client['id'],
      'relationship_type_id' => $relationshipTypeB['id'],
      'case_id' => $case['id'],
      'skip_post_processing' => 1,
    ];

    $latestRelationship = RelationshipFabricator::fabricate($latestRelParams);
    $caseRolePostProcess = new CaseRoleCreationPostProcess();
    $caseRolePostProcess->onCreate(['params' => $latestRelParams], ['id' => $latestRelationship['id']]);

    // Since the single role per type setting is on. Previous relationship
    // i.e relationship2 will have been set inactive.
    // Relationship 1 should be untouched.
    $rel2Details = $this->getRelationshipDetails($relationship2['id']);
    $isRel2InActive = $rel2Details['is_active'] == 0 && $rel2Details['end_date'] = date('Y-m-d');
    $rel1Details = $this->getRelationshipDetails($relationship1['id']);
    $isRel1Active = $rel1Details['is_active'] == 1 && empty($rel1Details['end_date']);
    $this->assertTrue($isRel2InActive);
    $this->assertTrue($isRel1Active);

    // Check that the appropriate activity is created.
    $activity = $this->getCreatedActivity($case['id']);
    $this->assertCount(1, $activity['values']);
    $this->assertEquals([$client['id']], $activity['values'][0]['target_contact_id']);
    $expectedActivitySubject = "{$contactC['display_name']} replaced {$contactB['display_name']} as {$relationshipTypeBParams['name_b_a']}";
    $this->assertEquals($expectedActivitySubject, $activity['values'][0]['subject']);
  }

  /**
   * Test only reassigned relationship is set inactive when passed.
   */
  public function testOnlyReAssignRelIdIsSetInActiveWhenSingleCaseRoleSettingIsOff() {
    $this->setSingleCaseRoleSetting(FALSE);
    $this->setMultiClientCaseSetting(TRUE);
    $caseType = CaseTypeFabricator::fabricate();
    $client = ContactFabricator::fabricate();

    $case = CaseFabricator::fabricate(
      [
        'case_type_id' => $caseType['id'],
        'contact_id' => $client['id'],
        'creator_id' => $client['id'],
      ]
    );

    $relationshipTypeAParams = [
      'name_a_b' => 'Manager is',
      'name_b_a' => 'Manager',
    ];
    $contactADetails = ['first_name' => 'First B', 'last_name' => 'Last B'];
    $contactCDetails = ['first_name' => 'First C', 'last_name' => 'Last C'];
    $relationshipTypeA = RelationshipTypeFabricator::fabricate($relationshipTypeAParams);
    $contactA = ContactFabricator::fabricate($contactADetails);
    $contactB = ContactFabricator::fabricate();
    $contactC = ContactFabricator::fabricate($contactCDetails);

    // Contact A is Manager to Client.
    $params = [
      'contact_id_b' => $contactA['id'],
      'contact_id_a' => $client['id'],
      'relationship_type_id' => $relationshipTypeA['id'],
      'case_id' => $case['id'],
      'skip_post_processing' => 1,
    ];
    $relationship1 = RelationshipFabricator::fabricate($params);

    // Contact B is Manager to Client.
    $params = [
      'contact_id_b' => $contactB['id'],
      'contact_id_a' => $client['id'],
      'relationship_type_id' => $relationshipTypeA['id'],
      'case_id' => $case['id'],
      // This will prevent the On respond event from being triggered.
      'skip_post_processing' => 1,
    ];
    $relationship2 = RelationshipFabricator::fabricate($params);

    // Contact C is Manager to Client.
    $latestRelParams = [
      'contact_id_b' => $contactC['id'],
      'contact_id_a' => $client['id'],
      'relationship_type_id' => $relationshipTypeA['id'],
      'case_id' => $case['id'],
      'reassign_rel_id' => $relationship1['id'],
      'skip_post_processing' => 1,
    ];

    $latestRelationship = RelationshipFabricator::fabricate($latestRelParams);
    $caseRolePostProcess = new CaseRoleCreationPostProcess();
    $caseRolePostProcess->onCreate(['params' => $latestRelParams], ['id' => $latestRelationship['id']]);

    // Single role per type setting is off. Only the relationship passed in
    // assign_rel_id will be set inactive (i.e Rel1). Relationship 2
    // should be untouched.
    $rel1Details = $this->getRelationshipDetails($relationship1['id']);
    $isRel1InActive = $rel1Details['is_active'] == 0 && $rel2Details['end_date'] = date('Y-m-d');
    $rel2Details = $this->getRelationshipDetails($relationship2['id']);
    $isRel2Active = $rel2Details['is_active'] == 1 && empty($rel12etails['end_date']);
    $this->assertTrue($isRel1InActive);
    $this->assertTrue($isRel2Active);

    // Check that the appropriate activity is created.
    $activity = $this->getCreatedActivity($case['id']);
    $this->assertCount(1, $activity['values']);
    $this->assertEquals([$client['id']], $activity['values'][0]['target_contact_id']);
    $expectedActivitySubject = "{$contactC['display_name']} replaced {$contactA['display_name']} as {$relationshipTypeAParams['name_b_a']}";
    $this->assertEquals($expectedActivitySubject, $activity['values'][0]['subject']);
  }

  /**
   * Test exception is thrown when rel type Id's don't match for reassigned.
   */
  public function testExceptionIsThrownWhenRelTypeIdOfReassignRelationshipDoesNotMatchRelTypeIdOfNewRelationship() {
    $this->setSingleCaseRoleSetting(FALSE);
    $this->setMultiClientCaseSetting(TRUE);
    $caseType = CaseTypeFabricator::fabricate();
    $client = ContactFabricator::fabricate();

    $case = CaseFabricator::fabricate(
      [
        'case_type_id' => $caseType['id'],
        'contact_id' => $client['id'],
        'creator_id' => $client['id'],
      ]
    );

    $contactADetails = ['first_name' => 'First B', 'last_name' => 'Last B'];
    $contactCDetails = ['first_name' => 'First C', 'last_name' => 'Last C'];
    $relationshipTypeAParams = [
      'name_a_b' => 'Manager is',
      'name_b_a' => 'Manager',
    ];
    $relationshipTypeA = RelationshipTypeFabricator::fabricate($relationshipTypeAParams);
    $relationshipTypeBParams = [
      'name_a_b' => 'Regulator is',
      'name_b_a' => 'Regulator',
    ];
    $relationshipTypeB = RelationshipTypeFabricator::fabricate($relationshipTypeBParams);
    $contactA = ContactFabricator::fabricate($contactADetails);
    $contactC = ContactFabricator::fabricate($contactCDetails);

    // Contact A is Manager to Client.
    $params = [
      'contact_id_b' => $contactA['id'],
      'contact_id_a' => $client['id'],
      'relationship_type_id' => $relationshipTypeB['id'],
      'case_id' => $case['id'],
      'skip_post_processing' => 1,
    ];
    $relationship1 = RelationshipFabricator::fabricate($params);

    // Contact C is Manager to Client.
    $latestRelParams = [
      'contact_id_b' => $contactC['id'],
      'contact_id_a' => $client['id'],
      'relationship_type_id' => $relationshipTypeA['id'],
      'case_id' => $case['id'],
      'reassign_rel_id' => $relationship1['id'],
      'skip_post_processing' => 1,
    ];

    $latestRelationship = RelationshipFabricator::fabricate($latestRelParams);
    $caseRolePostProcess = new CaseRoleCreationPostProcess();
    $this->setExpectedException(
      'Exception',
      'The relationship type Id of the role to reassign must match the new relationship type Id'
    );
    $caseRolePostProcess->onCreate(['params' => $latestRelParams], ['id' => $latestRelationship['id']]);
  }

  /**
   * Test when single case role on and no previous relationships exist.
   */
  public function testCreateWhenSingleCaseRoleSettingIsOnAndMultiClientOffAndNoPreviousRelationshipExist() {
    $this->setSingleCaseRoleSetting(TRUE);
    $this->setMultiClientCaseSetting(FALSE);
    $caseType = CaseTypeFabricator::fabricate();
    $client = ContactFabricator::fabricate();

    $case = CaseFabricator::fabricate(
      [
        'case_type_id' => $caseType['id'],
        'contact_id' => $client['id'],
        'creator_id' => $client['id'],
      ]
    );

    $relationshipTypeBParams = [
      'name_a_b' => 'Regulator is',
      'name_b_a' => 'Regulator',
    ];
    $contactCDetails = ['first_name' => 'First C', 'last_name' => 'Last C'];
    $relationshipTypeB = RelationshipTypeFabricator::fabricate($relationshipTypeBParams);
    $contactC = ContactFabricator::fabricate($contactCDetails);

    // Contact C is Regulator to Client.
    $latestRelParams = [
      'contact_id_b' => $contactC['id'],
      'contact_id_a' => $client['id'],
      'relationship_type_id' => $relationshipTypeB['id'],
      'case_id' => $case['id'],
      'skip_post_processing' => 1,
    ];

    $latestRelationship = RelationshipFabricator::fabricate($latestRelParams);
    $caseRolePostProcess = new CaseRoleCreationPostProcess();
    $caseRolePostProcess->onCreate(['params' => $latestRelParams], ['id' => $latestRelationship['id']]);

    // Check that the appropriate activity is created.
    $activity = $this->getCreatedActivity($case['id']);
    $this->assertCount(1, $activity['values']);
    $this->assertEquals([$client['id']], $activity['values'][0]['target_contact_id']);

    $expectedActivitySubject = "{$contactC['display_name']} added as {$relationshipTypeBParams['name_b_a']}";
    $this->assertEquals($expectedActivitySubject, $activity['values'][0]['subject']);
  }

  /**
   * Test when single case role setting is off and reassigned rel Id not passed.
   */
  public function testOtherRelationshipsNotSetInActiveWhenSingleCaseRoleSettingIsOffAndReAssignedRelIdNotPassed() {
    $this->setSingleCaseRoleSetting(FALSE);
    $this->setMultiClientCaseSetting(TRUE);
    $caseType = CaseTypeFabricator::fabricate();
    $client = ContactFabricator::fabricate();

    $case = CaseFabricator::fabricate(
      [
        'case_type_id' => $caseType['id'],
        'contact_id' => $client['id'],
        'creator_id' => $client['id'],
      ]
    );

    $relationshipTypeAParams = [
      'name_a_b' => 'Manager is',
      'name_b_a' => 'Manager',
    ];
    $contactCDetails = ['first_name' => 'First C', 'last_name' => 'Last C'];
    $relationshipTypeA = RelationshipTypeFabricator::fabricate($relationshipTypeAParams);
    $contactB = ContactFabricator::fabricate();
    $contactC = ContactFabricator::fabricate($contactCDetails);

    // Contact B is Manager to Client.
    $params = [
      'contact_id_b' => $contactB['id'],
      'contact_id_a' => $client['id'],
      'relationship_type_id' => $relationshipTypeA['id'],
      'case_id' => $case['id'],
      // This will prevent the On respond event from being triggered.
      'skip_post_processing' => 1,
    ];
    $relationship2 = RelationshipFabricator::fabricate($params);

    // Contact C is Manager to Client.
    $latestRelParams = [
      'contact_id_b' => $contactC['id'],
      'contact_id_a' => $client['id'],
      'relationship_type_id' => $relationshipTypeA['id'],
      'case_id' => $case['id'],
      'skip_post_processing' => 1,
    ];

    $latestRelationship = RelationshipFabricator::fabricate($latestRelParams);
    $caseRolePostProcess = new CaseRoleCreationPostProcess();
    $caseRolePostProcess->onCreate(['params' => $latestRelParams], ['id' => $latestRelationship['id']]);

    // Relationship 2 should be untouched and still be active.
    $rel2Details = $this->getRelationshipDetails($relationship2['id']);
    $isRel2Active = $rel2Details['is_active'] == 1 && empty($rel12etails['end_date']);
    $this->assertTrue($isRel2Active);

    // Check that the appropriate activity is created.
    $activity = $this->getCreatedActivity($case['id']);
    $this->assertCount(1, $activity['values']);
    $this->assertEquals([$client['id']], $activity['values'][0]['target_contact_id']);

    $expectedActivitySubject = "{$contactC['display_name']} added as {$relationshipTypeAParams['name_b_a']}";
    $this->assertEquals($expectedActivitySubject, $activity['values'][0]['subject']);
  }

  /**
   * Test reassigning case role with no start date.
   */
  public function testRoleReassignment() {
    $caseType = CaseTypeFabricator::fabricate();
    $client = ContactFabricator::fabricate();
    $previousManager = ContactFabricator::fabricate();
    $existingManager = ContactFabricator::fabricate();

    $case = CaseFabricator::fabricate(
      [
        'case_type_id' => $caseType['id'],
        'contact_id' => $client['id'],
        'creator_id' => $client['id'],
      ]
    );

    $managerRelationshipType = RelationshipTypeFabricator::fabricate([
      'name_a_b' => 'Manager is',
      'name_b_a' => 'Manager',
    ]);

    $previousManagerRelationship = RelationshipFabricator::fabricate([
      'contact_id_b' => $previousManager['id'],
      'contact_id_a' => $client['id'],
      'relationship_type_id' => $managerRelationshipType['id'],
      'case_id' => $case['id'],
    ]);

    $currentManagerRelationship = RelationshipFabricator::fabricate([
      'contact_id_b' => $existingManager['id'],
      'contact_id_a' => $client['id'],
      'relationship_type_id' => $managerRelationshipType['id'],
      'case_id' => $case['id'],
      'reassign_rel_id' => $previousManagerRelationship['id'],
    ]);

    $previousManagerRelationshipDetails = $this->getRelationshipDetails($previousManagerRelationship['id']);
    $currentManagerRelationshipDetails = $this->getRelationshipDetails($currentManagerRelationship['id']);

    $this->assertEquals('0', $previousManagerRelationshipDetails['is_active'], 'previous manager is not active');
    $this->assertEquals(date('Y-m-d'), $previousManagerRelationshipDetails['end_date'], 'previous manager end date is today');
    $this->assertEquals('1', $currentManagerRelationshipDetails['is_active'], 'current manager is active');
  }

  /**
   * Test reassign case role with start and end date.
   */
  public function testRoleReassignmentWithStartDate() {
    $caseType = CaseTypeFabricator::fabricate();
    $client = ContactFabricator::fabricate();
    $previousManager = ContactFabricator::fabricate();
    $existingManager = ContactFabricator::fabricate();
    $fiveDaysAgo = date('Y-m-d', strtotime('-5 days'));

    $case = CaseFabricator::fabricate(
      [
        'case_type_id' => $caseType['id'],
        'contact_id' => $client['id'],
        'creator_id' => $client['id'],
      ]
    );

    $managerRelationshipType = RelationshipTypeFabricator::fabricate([
      'name_a_b' => 'Manager is',
      'name_b_a' => 'Manager',
    ]);

    $previousManagerRelationship = RelationshipFabricator::fabricate([
      'contact_id_b' => $previousManager['id'],
      'contact_id_a' => $client['id'],
      'relationship_type_id' => $managerRelationshipType['id'],
      'case_id' => $case['id'],
    ]);

    $currentManagerRelationship = RelationshipFabricator::fabricate([
      'contact_id_b' => $existingManager['id'],
      'contact_id_a' => $client['id'],
      'relationship_type_id' => $managerRelationshipType['id'],
      'case_id' => $case['id'],
      'start_date' => $fiveDaysAgo,
      'reassign_rel_id' => $previousManagerRelationship['id'],
    ]);

    $previousManagerRelationshipDetails = $this->getRelationshipDetails($previousManagerRelationship['id']);
    $currentManagerRelationshipDetails = $this->getRelationshipDetails($currentManagerRelationship['id']);

    $this->assertEquals('0', $previousManagerRelationshipDetails['is_active'], 'previous manager is not active');
    $this->assertEquals($fiveDaysAgo, $previousManagerRelationshipDetails['end_date'], 'previous manager end date is 5 days ago');
    $this->assertEquals('1', $currentManagerRelationshipDetails['is_active'], 'current manager is active');
    $this->assertEquals($fiveDaysAgo, $currentManagerRelationshipDetails['start_date'], 'currrent manager start date is 5 days ago');
  }

  /**
   * Get relationship details.
   *
   * @param int $relId
   *   Relationship Id.
   *
   * @return array
   *   Relationship details.
   */
  private function getRelationshipDetails($relId) {
    $result = civicrm_api3('Relationship', 'getsingle', [
      'id' => $relId,
    ]);

    return $result;
  }

  /**
   * Get created case activity.
   *
   * @param int $caseId
   *   Case Id.
   *
   * @return array
   *   created activity data.
   */
  private function getCreatedActivity($caseId) {
    $result = civicrm_api3('Activity', 'get', [
      'sequential' => 1,
      'case_id' => $caseId,
      'activity_type_id' => 'Assign Case Role',
      'status_id' => 'Completed',
      'return' => ['target_contact_id', 'subject'],
    ]);

    return $result;
  }

}
