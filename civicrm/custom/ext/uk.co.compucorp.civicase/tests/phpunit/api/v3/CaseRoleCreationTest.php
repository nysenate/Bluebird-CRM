<?php

use CRM_Civicase_Test_Fabricator_Relationship as RelationshipFabricator;
use CRM_Civicase_Test_Fabricator_RelationshipType as RelationshipTypeFabricator;
use CRM_Civicase_Test_Fabricator_CaseType as CaseTypeFabricator;
use CRM_Civicase_Test_Fabricator_Case as CaseFabricator;
use CRM_Civicase_Test_Fabricator_Contact as ContactFabricator;

/**
 * Runs tests on api_v3_CaseRoleCreationTest tests.
 *
 * @group headless
 */
class api_v3_CaseRoleCreationTest extends BaseHeadlessTest {

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
   * Test case role post processing is triggered.
   */
  public function testCaseRolePostProcessingIsTriggeredForCaseRelatedRelationshipApiCall() {
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
    $relationshipTypeA = RelationshipTypeFabricator::fabricate($relationshipTypeAParams);
    $contactA = ContactFabricator::fabricate();

    // Contact A is Manager to Client.
    $params = [
      'contact_id_b' => $contactA['id'],
      'contact_id_a' => $client['id'],
      'relationship_type_id' => $relationshipTypeA['id'],
      'case_id' => $case['id'],
      'start_date' => '',
    ];

    // PostProcessing Should Be Triggered Here.
    $relationship1 = RelationshipFabricator::fabricate($params);

    // Case start date should be set to today's date as the single
    // case role setting is on.
    $relStartDate = new DateTime($relationship1['start_date']);
    $this->assertEquals(date('Y-m-d'), $relStartDate->format('Y-m-d'));

    // Verify created activity.
    $activity = $this->getCreatedActivity($case['id']);
    $this->assertCount(1, $activity['values']);
    $this->assertEquals([$client['id']], $activity['values'][0]['target_contact_id']);

    $expectedActivitySubject = "{$contactA['display_name']} added as {$relationshipTypeAParams['name_b_a']}";
    $this->assertEquals($expectedActivitySubject, $activity['values'][0]['subject']);

  }

  /**
   * Test case role post processing is not triggered for non case related api.
   */
  public function testCaseRolePostProcessingIsNotTriggeredForNonCaseRelatedRelationshipApiCall() {
    $this->setSingleCaseRoleSetting(TRUE);
    $this->setMultiClientCaseSetting(FALSE);
    $client = ContactFabricator::fabricate();

    $relationshipTypeAParams = [
      'name_a_b' => 'Manager is',
      'name_b_a' => 'Manager',
    ];
    $relationshipTypeA = RelationshipTypeFabricator::fabricate($relationshipTypeAParams);
    $contactA = ContactFabricator::fabricate();

    // Contact A is Manager to Client.
    $params = [
      'contact_id_b' => $contactA['id'],
      'contact_id_a' => $client['id'],
      'relationship_type_id' => $relationshipTypeA['id'],
      'start_date' => '2020-06-01',
    ];

    // PostProcessing will not be Triggered Here.
    $relationship1 = RelationshipFabricator::fabricate($params);

    // Case start date will not.
    $relStartDate = new DateTime($relationship1['start_date']);
    $this->assertEquals($params['start_date'], $relStartDate->format('Y-m-d'));

    // No activity will be created.
    $activity = $this->getCreatedActivity();
    $this->assertCount(0, $activity['values']);
  }

  /**
   * Get created activity data.
   *
   * @param int|null $caseId
   *   Case Id.
   *
   * @return array
   *   Activity data
   */
  private function getCreatedActivity($caseId = NULL) {
    $params = [
      'sequential' => 1,
      'activity_type_id' => 'Assign Case Role',
      'status_id' => 'Completed',
      'return' => ['target_contact_id', 'subject'],
    ];

    if (!empty($caseId)) {
      $params['case_id'] = $caseId;
    }

    $result = civicrm_api3('Activity', 'get', $params);

    return $result;
  }

}
