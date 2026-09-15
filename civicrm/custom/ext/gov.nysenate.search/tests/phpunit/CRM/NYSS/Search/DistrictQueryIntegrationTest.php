<?php
declare(strict_types = 1);

use Civi\Test\EndToEndInterface;

/**
 * Integration tests exercising CRM_Core_BAO_CustomQuery / CRM_Contact_BAO_Query::apiQuery()
 * end-to-end for the "District Information" custom field group (civicrm_value_district_information_7),
 * whose comma-delimited multi-value / LIKE-% / empty-value handling now lives in
 * CRM_NYSS_Search_DistrictQueryModifier.
 *
 * Custom field 50 (county_50, Int) and custom field 52 (town_52, String) are used as
 * representative fields for the Int and String cases respectively. Both fields are attached
 * to the Address entity.
 *
 * Runs against a real instance DB (no transactional rollback), so every contact created is
 * explicitly deleted in tearDown().
 *
 * Run from the extension directory:
 *   INSTANCE=<instance> HTTP_HOST=<instance> phpunit
 *
 * @group e2e
 */
class CRM_NYSS_Search_DistrictQueryIntegrationTest extends \PHPUnit\Framework\TestCase implements EndToEndInterface {

    private const COUNTY_FIELD_ID = 50;
    private const TOWN_FIELD_ID = 52;

    private array $contactIds = [];

    public static function setUpBeforeClass(): void {
        \Civi\Test::e2e()->installMe(__DIR__)->apply();
    }

    public function tearDown(): void {
        foreach ($this->contactIds as $contactId) {
            civicrm_api3('Contact', 'delete', ['id' => $contactId, 'skip_undelete' => 1]);
        }
        $this->contactIds = [];
        parent::tearDown();
    }

    /**
     * Creates a contact with an address carrying the given district custom field values.
     *
     * @return int contact id
     */
    private function createContactWithDistrict(?int $county, ?string $town): int {
        $contact = civicrm_api3('Contact', 'create', [
            'contact_type' => 'Individual',
            'first_name' => 'Test',
            'last_name' => 'Contact-' . uniqid(),
        ]);
        $contactId = (int) $contact['id'];
        $this->contactIds[] = $contactId;

        $addressParams = [
            'contact_id' => $contactId,
            'location_type_id' => 1,
        ];
        if ($county !== NULL) {
            $addressParams['custom_' . self::COUNTY_FIELD_ID] = $county;
        }
        if ($town !== NULL) {
            $addressParams['custom_' . self::TOWN_FIELD_ID] = $town;
        }
        civicrm_api3('Address', 'create', $addressParams);

        return $contactId;
    }

    /**
     * Runs apiQuery() with a single [name, op, value, grouping, wildcard] param
     * and returns the set of matched contact ids.
     */
    private function searchContactIds(string $fieldName, string $op, $value): array {
        $params = [
            [$fieldName, $op, $value, 0, 0],
        ];
        [$values] = CRM_Contact_BAO_Query::apiQuery($params, ['contact_id' => 1], NULL, NULL, 0, 0);
        return array_keys($values);
    }

    public function testCommaDelimitedStringSearchMatchesDistrictTownCodes(): void {
        $albany = $this->createContactWithDistrict(NULL, 'ALBANY');
        $nyc = $this->createContactWithDistrict(NULL, '-NYC');
        $buffalo = $this->createContactWithDistrict(NULL, 'BUFFAL');

        // NOTE: a plain, literal comma here -- not the "[:comma:]" token. That token is an
        // internal artifact core's own escaping step produces from a literal comma before
        // DistrictQueryModifier::modifyStringFieldClause() ever runs; supplying it directly
        // gets double-escaped and never splits.
        $matched = $this->searchContactIds('custom_' . self::TOWN_FIELD_ID, '=', 'ALBANY, -NYC');

        $this->assertContains($albany, $matched);
        $this->assertContains($nyc, $matched);
        $this->assertNotContains($buffalo, $matched);
    }

    public function testCommaDelimitedIntegerSearchMatchesDistrictCounties(): void {
        $five = $this->createContactWithDistrict(5, NULL);
        $seven = $this->createContactWithDistrict(7, NULL);
        $nine = $this->createContactWithDistrict(9, NULL);

        $matched = $this->searchContactIds('custom_' . self::COUNTY_FIELD_ID, '=', '5,7');

        $this->assertContains($five, $matched);
        $this->assertContains($seven, $matched);
        $this->assertNotContains($nine, $matched);
    }

    public function testLikePercentSearchMatchesDistrictCountyPrefix(): void {
        $five = $this->createContactWithDistrict(5, NULL);
        $fiftyTwo = $this->createContactWithDistrict(52, NULL);
        $six = $this->createContactWithDistrict(6, NULL);

        $matched = $this->searchContactIds('custom_' . self::COUNTY_FIELD_ID, 'LIKE', '5%');

        $this->assertContains($five, $matched);
        $this->assertContains($fiftyTwo, $matched);
        $this->assertNotContains($six, $matched);
    }

    public function testEmptyValueDoesNotRestrictIntegerFieldSearch(): void {
        $withCounty = $this->createContactWithDistrict(5, NULL);
        $withoutCounty = $this->createContactWithDistrict(NULL, NULL);

        $matched = $this->searchContactIds('custom_' . self::COUNTY_FIELD_ID, '=', '');

        // An empty value on a district int field must not filter out contacts --
        // both should still be present since the clause is skipped entirely.
        $this->assertContains($withCounty, $matched);
        $this->assertContains($withoutCounty, $matched);
    }

}
