<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use Civi\Test;
use CRM_Civicase_Test_Fabricator_Case as CaseFabricator;
use CRM_Civicase_Test_Fabricator_CaseType as CaseTypeFabricator;
use CRM_Civicase_Test_Fabricator_Contact as ContactFabricator;

/**
 * Runs tests on CaseContactLock BAO.
 *
 * @group headless
 */
class CRM_Civicase_BAO_CaseContactLockTest extends PHPUnit_Framework_TestCase implements HeadlessInterface, TransactionalInterface {

  /**
   * {@inheritdoc}
   */
  public function setUpHeadless() {
    return Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  /**
   * Test create locks.
   *
   * Tests creation of a several locks for more than one contact
   * and more than one case.
   */
  public function testCreateLocks() {
    $cases = $contacts = [];
    $caseType = CaseTypeFabricator::fabricate();
    $creator = ContactFabricator::fabricate();

    for ($i = 0; $i < 3; $i++) {
      $contact = ContactFabricator::fabricate();
      $case = CaseFabricator::fabricate(
        [
          'case_type_id' => $caseType['id'],
          'contact_id' => $contact['id'],
          'creator_id' => $creator['id'],
        ]
      );

      $cases[] = $case['id'];
      $contacts[] = $contact['id'];
    }

    CRM_Civicase_BAO_CaseContactLock::createLocks($cases, $contacts);

    foreach ($cases as $currentCase) {
      $result = civicrm_api3('CaseContactLock', 'get', [
        'sequential' => 1,
        'case_id' => $currentCase,
      ]
      );
      $this->assertEquals($result['count'], count($contacts));

      foreach ($result['values'] as $currentLock) {
        $this->assertEquals($currentLock['case_id'], $currentCase);
        $this->assertContains($currentLock['contact_id'], $contacts);
      }
    }
  }

  /**
   * Test on nin input parameters.
   *
   * Tests an exception is thrown if one if either of the parameters passed to
   * createLocks method of BAO is not an array.
   *
   * @expectedException API_Exception
   */
  public function testExceptionThrownOnNonInputParametersToCreateLocks() {
    CRM_Civicase_BAO_CaseContactLock::createLocks(1, 2);
  }

}
