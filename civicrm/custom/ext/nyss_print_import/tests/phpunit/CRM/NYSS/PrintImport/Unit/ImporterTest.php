<?php
declare(strict_types = 1);

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for CRM_NYSS_PrintImport_Importer::buildUpdateFields().
 *
 * These tests cover SQL SET clause generation with no CiviCRM or DB dependencies.
 *
 * @group unit
 */
class CRM_NYSS_PrintImport_Unit_ImporterTest extends TestCase {

  private function makeImporter(array $options): CRM_NYSS_PrintImport_Importer {
    $conn = $this->createMock(\mysqli::class);
    return new CRM_NYSS_PrintImport_Importer($conn, $options);
  }

  private function buildUpdateFields(CRM_NYSS_PrintImport_Importer $importer, string $tbl, array $l): array {
    $method = new \ReflectionMethod($importer, 'buildUpdateFields');
    $method->setAccessible(true);
    return $method->invoke($importer, $tbl, $l);
  }

  // ---------------------------------------------------------------------------
  // buildUpdateFields
  // ---------------------------------------------------------------------------

  public function testNonEmptyValueIsIncluded(): void {
    $importer = $this->makeImporter([]);
    $result = $this->buildUpdateFields($importer, 'civicrm_contact', ['first_name' => 'John']);
    $this->assertContains("first_name='John'", $result);
  }

  public function testEmptyValueIsSkippedWhenOptNullOff(): void {
    $importer = $this->makeImporter([]);
    $result = $this->buildUpdateFields($importer, 'civicrm_contact', ['first_name' => '']);
    $this->assertEmpty($result);
  }

  public function testEmptyValueIsNulledWhenOptNullOn(): void {
    $importer = $this->makeImporter([CRM_NYSS_PrintImport_Importer::OPT_NULL => true]);
    $result = $this->buildUpdateFields($importer, 'civicrm_contact', ['first_name' => '']);
    $this->assertContains("first_name=NULL", $result);
  }

  public function testFieldNotInDefinitionsIsSkipped(): void {
    $importer = $this->makeImporter([]);
    $result = $this->buildUpdateFields($importer, 'civicrm_contact', ['nonexistent_field' => 'value']);
    $this->assertEmpty($result);
  }

  public function testMultipleFieldsBuiltCorrectly(): void {
    $importer = $this->makeImporter([]);
    $result = $this->buildUpdateFields($importer, 'civicrm_contact', [
      'first_name' => 'Jane',
      'last_name'  => 'Doe',
    ]);
    $this->assertContains("first_name='Jane'", $result);
    $this->assertContains("last_name='Doe'", $result);
    $this->assertCount(2, $result);
  }

  public function testMixedEmptyAndNonEmptyWithOptNullOff(): void {
    $importer = $this->makeImporter([]);
    $result = $this->buildUpdateFields($importer, 'civicrm_contact', [
      'first_name' => 'Jane',
      'last_name'  => '',
    ]);
    $this->assertContains("first_name='Jane'", $result);
    $this->assertCount(1, $result);
  }

}
