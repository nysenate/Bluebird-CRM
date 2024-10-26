<?php

use CRM_Civicase_Test_Fabricator_Contact as ContactFabricator;
use CRM_Civicase_Test_Fabricator_RelationshipType as RelationshipTypeFabricator;
use CRM_Civicase_Test_Fabricator_Relationship as RelationshipFabricator;
use CRM_Civicase_Test_Fabricator_CaseType as CaseTypeFabricator;
use CRM_Civicase_Test_Fabricator_Case as CaseFabricator;

/**
 * Runs tests for api_v3_Case_Getcaselist api class.
 *
 * @group headless
 */
class api_v3_Case_GetcaselistTest extends BaseHeadlessTest {

  use CRM_Civicase_Helpers_SessionTrait;

  /**
   * Holds logged in contact/case creator id.
   *
   * @var int
   */
  private $creator;

  /**
   * Setup data before tests run.
   */
  public function setUp() {
    $contact = ContactFabricator::fabricate();
    $this->registerCurrentLoggedInContactInSession($contact['id']);
    $this->creator = $contact['id'];
  }

  /**
   * Test fetch cases filtered by relationship type and contact.
   */
  public function testFilterByRelationshipTypeAndRelatedContact() {
    $contact = ContactFabricator::fabricate();
    $client = ContactFabricator::fabricate();
    $caseType = CaseTypeFabricator::fabricate();

    $caseA = CaseFabricator::fabricate(
      [
        'case_type_id' => $caseType['id'],
        'contact_id' => $client['id'],
        'creator_id' => $this->creator,
      ]
    );

    $caseB = CaseFabricator::fabricate(
      [
        'case_type_id' => $caseType['id'],
        'contact_id' => $client['id'],
        'creator_id' => $this->creator,
      ]
    );

    $relationshipTypeParams = [
      'name_a_b' => 'Manager is',
      'name_b_a' => 'Manager',
    ];
    $relationshipType = RelationshipTypeFabricator::fabricate($relationshipTypeParams);
    $params = [
      'contact_id_b' => $contact['id'],
      'contact_id_a' => $client['id'],
      'relationship_type_id' => $relationshipType['id'],
      'case_id' => $caseA['id'],
    ];

    RelationshipFabricator::fabricate($params);

    $caseDetailsParams = [
      'has_role' =>
        [
          'contact' => ['IN' => [$contact['id']]],
          'can_be_client' => FALSE,
          'all_case_roles_selected' => FALSE,
          'role_type' => ['IN' => [$relationshipType['id']]],
        ],
    ];

    $result = civicrm_api3('Case', 'getcaselist', $caseDetailsParams);
    // Only Case A is expected to be returned because that is the only case a
    // relationship type manager was defined for with contact.
    $this->assertEquals($caseA['id'], $result['id']);
  }

}
