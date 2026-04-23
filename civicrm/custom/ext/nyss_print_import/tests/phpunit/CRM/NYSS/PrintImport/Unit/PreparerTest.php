<?php
declare(strict_types = 1);

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for CRM_NYSS_PrintImport_Preparer.
 *
 * These tests cover pure record preparation logic with no CiviCRM or DB dependencies.
 *
 * @group unit
 */
class CRM_NYSS_PrintImport_Unit_PreparerTest extends TestCase {

  // ---------------------------------------------------------------------------
  // fixBirthdate
  // ---------------------------------------------------------------------------

  public function testFixBirthdateConvertsValidDate(): void {
    $l = ['birth_date' => '19850312'];
    CRM_NYSS_PrintImport_Preparer::fixBirthdate($l);
    $this->assertSame('1985-03-12', $l['birth_date']);
  }

  public function testFixBirthdateNullsOnZero(): void {
    $l = ['birth_date' => 0];
    CRM_NYSS_PrintImport_Preparer::fixBirthdate($l);
    $this->assertNull($l['birth_date']);
  }

  public function testFixBirthdateNullsOnSentinelDate19000101(): void {
    $l = ['birth_date' => '19000101'];
    CRM_NYSS_PrintImport_Preparer::fixBirthdate($l);
    $this->assertNull($l['birth_date']);
  }

  public function testFixBirthdateNullsOnSentinelDate19010101(): void {
    $l = ['birth_date' => '19010101'];
    CRM_NYSS_PrintImport_Preparer::fixBirthdate($l);
    $this->assertNull($l['birth_date']);
  }

  public function testFixBirthdateNoOpWhenKeyAbsent(): void {
    $l = [];
    CRM_NYSS_PrintImport_Preparer::fixBirthdate($l);
    $this->assertArrayNotHasKey('birth_date', $l);
  }

  // ---------------------------------------------------------------------------
  // fixBOERegDate
  // ---------------------------------------------------------------------------

  public function testFixBOERegDateConvertsValidDate(): void {
    $l = ['boe_date_of_registration_24' => '20031107'];
    CRM_NYSS_PrintImport_Preparer::fixBOERegDate($l);
    $this->assertSame('2003-11-07', $l['boe_date_of_registration_24']);
  }

  public function testFixBOERegDateNullsOnZero(): void {
    $l = ['boe_date_of_registration_24' => 0];
    CRM_NYSS_PrintImport_Preparer::fixBOERegDate($l);
    $this->assertNull($l['boe_date_of_registration_24']);
  }

  // ---------------------------------------------------------------------------
  // fixGender
  // ---------------------------------------------------------------------------

  public function testFixGenderMale(): void {
    $aGender = ['Male' => 2, 'Female' => 1];
    $l = ['gender' => 'M'];
    CRM_NYSS_PrintImport_Preparer::fixGender($l, $aGender);
    $this->assertSame(2, $l['gender_id']);
    $this->assertArrayNotHasKey('gender', $l);
  }

  public function testFixGenderFemale(): void {
    $aGender = ['Male' => 2, 'Female' => 1];
    $l = ['gender' => 'F'];
    CRM_NYSS_PrintImport_Preparer::fixGender($l, $aGender);
    $this->assertSame(1, $l['gender_id']);
    $this->assertArrayNotHasKey('gender', $l);
  }

  public function testFixGenderNoOpWhenKeyAbsent(): void {
    $aGender = ['Male' => 2, 'Female' => 1];
    $l = [];
    CRM_NYSS_PrintImport_Preparer::fixGender($l, $aGender);
    $this->assertArrayNotHasKey('gender_id', $l);
  }

  // ---------------------------------------------------------------------------
  // fixLocType
  // ---------------------------------------------------------------------------

  public function testFixLocTypeResolvesLabel(): void {
    $aLocType = ['Home' => 1, 'Work' => 2];
    $l = ['location_type_id' => 'Home'];
    CRM_NYSS_PrintImport_Preparer::fixLocType($l, $aLocType);
    $this->assertSame(1, $l['location_type_id']);
  }

  public function testFixLocTypeNoOpWhenAlreadyNumeric(): void {
    $aLocType = ['Home' => 1, 'Work' => 2];
    $l = ['location_type_id' => 1];
    CRM_NYSS_PrintImport_Preparer::fixLocType($l, $aLocType);
    $this->assertSame(1, $l['location_type_id']);
  }

  // ---------------------------------------------------------------------------
  // fixStreetUnit
  // ---------------------------------------------------------------------------

  public function testFixStreetUnitPrependsAptWhenNoPrefix(): void {
    $l = ['street_unit' => '4B'];
    CRM_NYSS_PrintImport_Preparer::fixStreetUnit($l);
    $this->assertSame('Apt. 4B', $l['street_unit']);
  }

  public function testFixStreetUnitNoOpWhenPrefixPresent(): void {
    $l = ['street_unit' => 'Apt 4B'];
    CRM_NYSS_PrintImport_Preparer::fixStreetUnit($l);
    $this->assertSame('Apt 4B', $l['street_unit']);
  }

  public function testFixStreetUnitNoOpWhenEmpty(): void {
    $l = ['street_unit' => ''];
    CRM_NYSS_PrintImport_Preparer::fixStreetUnit($l);
    $this->assertSame('', $l['street_unit']);
  }

  // ---------------------------------------------------------------------------
  // prepareContactType
  // ---------------------------------------------------------------------------

  public function testPrepareContactTypeDefaultsToIndividual(): void {
    $l = ['first_name' => 'Jane', 'last_name' => 'Doe'];
    CRM_NYSS_PrintImport_Preparer::prepareContactType($l);
    $this->assertSame('Individual', $l['contact_type']);
  }

  public function testPrepareContactTypeOrganizationWhenNamesEmptyAndEmployerSet(): void {
    $l = ['first_name' => '', 'last_name' => '', 'current_employer' => 'Acme Corp'];
    CRM_NYSS_PrintImport_Preparer::prepareContactType($l);
    $this->assertSame('Organization', $l['contact_type']);
    $this->assertSame('Acme Corp', $l['organization_name']);
    $this->assertSame('Acme Corp', $l['display_name']);
    $this->assertSame('Acme Corp', $l['sort_name']);
    $this->assertSame('', $l['current_employer']);
  }

  public function testPrepareContactTypeIndividualWhenLastNamePresentEvenIfFirstEmpty(): void {
    $l = ['first_name' => '', 'last_name' => 'Doe', 'current_employer' => 'Acme Corp'];
    CRM_NYSS_PrintImport_Preparer::prepareContactType($l);
    $this->assertSame('Individual', $l['contact_type']);
  }

  public function testPrepareContactTypeIndividualWhenFirstNamePresentEvenIfLastEmpty(): void {
    $l = ['first_name' => 'Jane', 'last_name' => '', 'current_employer' => 'Acme Corp'];
    CRM_NYSS_PrintImport_Preparer::prepareContactType($l);
    $this->assertSame('Individual', $l['contact_type']);
  }

  public function testPrepareContactTypeIndividualWhenNoEmployer(): void {
    $l = ['first_name' => '', 'last_name' => ''];
    CRM_NYSS_PrintImport_Preparer::prepareContactType($l);
    $this->assertSame('Individual', $l['contact_type']);
  }

  // ---------------------------------------------------------------------------
  // prepareContactName
  // ---------------------------------------------------------------------------

  public function testPrepareContactNameBuildsDisplayName(): void {
    $l = ['first_name' => 'Jane', 'last_name' => 'Doe'];
    CRM_NYSS_PrintImport_Preparer::prepareContactName($l, [], []);
    $this->assertSame('Jane Doe', $l['display_name']);
  }

  public function testPrepareContactNameBuildsSortName(): void {
    $l = ['first_name' => 'Jane', 'last_name' => 'Doe'];
    CRM_NYSS_PrintImport_Preparer::prepareContactName($l, [], []);
    $this->assertSame('Doe, Jane', $l['sort_name']);
  }

  public function testPrepareContactNameIncludesMiddleName(): void {
    $l = ['first_name' => 'Jane', 'middle_name' => 'Ann', 'last_name' => 'Doe'];
    CRM_NYSS_PrintImport_Preparer::prepareContactName($l, [], []);
    $this->assertSame('Jane Ann Doe', $l['display_name']);
    $this->assertSame('Doe, Jane Ann', $l['sort_name']);
  }

  public function testPrepareContactNameResolvsMidAlias(): void {
    $l = ['first_name' => 'Jane', 'mid' => 'Ann', 'last_name' => 'Doe'];
    CRM_NYSS_PrintImport_Preparer::prepareContactName($l, [], []);
    $this->assertSame('Ann', $l['middle_name']);
  }

  public function testPrepareContactNameIncludesPrefix(): void {
    $l = ['first_name' => 'Jane', 'last_name' => 'Doe', 'prefix_id' => 'Ms.'];
    CRM_NYSS_PrintImport_Preparer::prepareContactName($l, ['Ms.' => 'Ms.'], []);
    $this->assertSame('Ms. Jane Doe', $l['display_name']);
  }

  public function testPrepareContactNameIncludesSuffix(): void {
    $l = ['first_name' => 'John', 'last_name' => 'Doe', 'suffix_id' => 'Jr.'];
    CRM_NYSS_PrintImport_Preparer::prepareContactName($l, [], ['Jr.' => 'Jr.']);
    $this->assertSame('John Doe, Jr.', $l['display_name']);
  }

  public function testPrepareContactNameNoSortNameWhenDisplayNameEmpty(): void {
    $l = ['first_name' => '', 'last_name' => ''];
    CRM_NYSS_PrintImport_Preparer::prepareContactName($l, [], []);
    $this->assertArrayNotHasKey('sort_name', $l);
  }

}
