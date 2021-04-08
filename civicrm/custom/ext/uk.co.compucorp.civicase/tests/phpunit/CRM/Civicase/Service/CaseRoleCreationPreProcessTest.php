<?php

use CRM_Civicase_Service_CaseRoleCreationPreProcess as CaseRoleCreationPreProcess;

/**
 * Runs tests on CaseRoleCreationPreProcess Service tests.
 *
 * @group headless
 */
class CRM_Civicase_Service_CaseRoleCreationPreProcessTest extends BaseHeadlessTest {
  use CRM_Civicase_Helpers_CaseSettingsTrait;

  /**
   * Test start date is modified when single case role setting on.
   *
   * Start date must be empty or NULL.
   */
  public function testStartDateIsModifiedWhenSingleCaseRoleSettingIsOnAndMultiClientIsOff() {
    $this->setSingleCaseRoleSetting(TRUE);
    $this->setMultiClientCaseSetting(FALSE);
    $params = ['start_date' => ''];
    $apiRequestParams = $this->callPreProcessCreate($params);

    $this->assertEquals(date('Y-m-d'), $apiRequestParams['params']['start_date']);
  }

  /**
   * Test the start date is not modified when it is present.
   */
  public function testStartDateNotModifiedWhenPresent() {
    $this->setSingleCaseRoleSetting(TRUE);
    $this->setMultiClientCaseSetting(FALSE);

    $params = ['start_date' => '2020-06-05'];
    $apiRequestParams = $this->callPreProcessCreate($params);
    $this->assertEquals($params['start_date'], $apiRequestParams['params']['start_date']);
  }

  /**
   * Test start date not modified when single case role setting off.
   */
  public function testStartDateIsNotModifiedWhenSingleCaseRoleSettingIsOff() {
    $this->setSingleCaseRoleSetting(FALSE);

    $params = ['start_date' => '2020-06-05'];
    $apiRequestParams = $this->callPreProcessCreate($params);
    $this->assertEquals($params['start_date'], $apiRequestParams['params']['start_date']);
  }

  /**
   * Test relationship ID is filled when is not on request params.
   */
  public function testIdIsFilledWhenIsNotReceived() {
    $this->setSingleCaseRoleSetting(FALSE);

    $params = [];
    $apiRequestParams = $this->callPreProcessCreate($params);

    $this->assertArrayHasKey('id', $apiRequestParams['params']);
    $this->assertFalse($apiRequestParams['params']['id']);
  }

  /**
   * Test relationship ID is not replaced when is already present on params.
   */
  public function testIdIsNotReplacedWhenIsReceived() {
    $this->setSingleCaseRoleSetting(FALSE);

    $params = ['id' => rand()];
    $apiRequestParams = $this->callPreProcessCreate($params);

    $this->assertEquals($params['id'], $apiRequestParams['params']['id']);
  }

  /**
   * Call the case role pre process and return results.
   *
   * @param array $params
   *   API parameters.
   *
   * @return array
   *   Modified API params.
   */
  private function callPreProcessCreate(array $params) {
    $defaultParams = [
      'contact_id_b' => 1,
      'contact_id_a' => 2,
      'relationship_type_id' => 3,
      'case_id' => 2,
      'start_date' => '2020-06-05',
      // This will prevent the On respond event from being triggered.
      'skip_post_processing' => 1,
    ];
    $apiRequestParams = ['params' => array_merge($defaultParams, $params)];
    $caseRolePostProcess = new CaseRoleCreationPreProcess();
    $caseRolePostProcess->onCreate($apiRequestParams);

    return $apiRequestParams;
  }

}
