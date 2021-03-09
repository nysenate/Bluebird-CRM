<?php

use CRM_Civicase_Hook_PostProcess_AttachEmailActivityToAllCases as AttachEmailActivityToAllCases;
use CRM_Civicase_Test_Fabricator_Case as CaseFabricator;
use CRM_Civicase_Test_Fabricator_CaseType as CaseTypeFabricator;
use CRM_Civicase_Test_Fabricator_Contact as ContactFabricator;

/**
 * Tests for the CRM_Civicase_Hook_PostProcess_AttachEmailActivityToAllCases.
 *
 * @group headless
 */
class CRM_Civicase_Hook_PostProcess_AttachEmailActivityToAllCasesTest extends BaseHeadlessTest {

  /**
   * Test the run method.
   *
   * @param int $activityId
   *   Activity id.
   * @param int $caseId
   *   First case id.
   * @param string $allCaseIds
   *   All case ids.
   *
   * @dataProvider getTestDataForRunMethod
   */
  public function testRun($activityId, $caseId, $allCaseIds) {
    $_GET['caseid'] = $_REQUEST['caseid'] = $caseId;
    $_GET['allCaseIds'] = $_REQUEST['allCaseIds'] = $allCaseIds;
    $hook = new AttachEmailActivityToAllCases();
    $hook->run(CRM_Contact_Form_Task_Email::class, new CRM_Contact_Form_Task_Email());
    $allCaseIds = array_diff(explode(',', $allCaseIds), [$caseId]);
    foreach ($allCaseIds as $caseId) {
      $activity = civicrm_api3('Activity', 'get', [
        'case_id' => $caseId,
        'id' => $activityId,
        'sequential' => 1,
        'return' => ['id'],
      ]);
      $this->assertNotEmpty($activity['id']);
    }
  }

  /**
   * Provides data for run method testing.
   *
   * @return array
   *   List of case and activity id.
   */
  public function getTestDataForRunMethod() {
    $return = [];
    for ($i = 0; $i < 2; $i++) {
      $cases = $this->createCases();
      $activityId = $this->createActivity($cases[0]);
      $return[] = [
        $activityId,
        $cases[0],
        implode(',', $cases),
      ];
    }

    return $return;
  }

  /**
   * Provides case ids after creating cases.
   *
   * @return array
   *   List of case ids.
   */
  private function createCases() {
    $cases = [];
    $creator = ContactFabricator::fabricate();
    $contact = ContactFabricator::fabricate();
    $caseType = CaseTypeFabricator::fabricate();
    for ($i = 0; $i < 2; $i++) {
      $case = CaseFabricator::fabricate(
        [
          'case_type_id' => $caseType['id'],
          'contact_id' => $contact['id'],
          'creator_id' => $creator['id'],
        ]
      );
      $cases[] = $case['id'];
    }

    return $cases;
  }

  /**
   * Provides activity id after creating activity.
   *
   * @param int $caseId
   *   Case id.
   *
   * @return int
   *   Activity id.
   */
  private function createActivity($caseId) {
    $creator = ContactFabricator::fabricate();
    $activity = civicrm_api3('Activity', 'create', [
      'source_contact_id' => $creator['id'],
      'activity_type_id' => 3,
      'activity_date_time' => date('YmdHis'),
      'subject' => 'Test activity',
      'details' => '',
      'status_id' => CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'status_id', 'Completed'),
      'case_id' => $caseId,
    ]);

    return $activity['id'];
  }

}
