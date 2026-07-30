<?php
declare(strict_types = 1);

use Civi\Test\EndToEndInterface;

/**
 * Live integration tests against the real SAGE API. These hit whatever
 * SAGE_API_BASE/SAGE_API_KEY are configured for the Bluebird instance this
 * suite runs against (see bluebird.cfg's global sage.api.* settings, which
 * the 'dev' instance inherits unmodified).
 *
 * Assertions are deliberately structural (booleans, non-empty, numeric,
 * broad NY lat/lon bounding box) rather than exact district numbers, since
 * redistricting can shift real values over time.
 *
 * Run from the extension directory:
 *   INSTANCE=dev HTTP_HOST=dev phpunit
 *
 * @group e2e
 */
class CRM_SAGE_ApiIntegrationTest extends \PHPUnit\Framework\TestCase implements EndToEndInterface {

  public static function setUpBeforeClass(): void {
    \Civi\Test::e2e()->installMe(__DIR__)->apply();
  }

  public function setUp(): void {
    parent::setUp();
    if (!defined('SAGE_API_KEY') || !defined('SAGE_API_BASE')
      || SAGE_API_KEY === 'NO_KEY' || SAGE_API_BASE === 'NO_API') {
      $this->markTestSkipped(
        'SAGE API is not configured for this Bluebird instance ' .
        '(SAGE_API_KEY/SAGE_API_BASE resolve to placeholder values). ' .
        'Run with INSTANCE=<instance-with-sage-config> HTTP_HOST=<instance>.'
      );
    }
  }

  private static function validAddress(): array {
    return [
      'street_address' => 'State St',
      'city' => 'Albany',
      'state_province' => 'NY',
      'postal_code' => '12224',
    ];
  }

  private static function secondValidAddress(): array {
    return [
      'street_address' => '24 Eagle St',
      'city' => 'Albany',
      'state_province' => 'NY',
      'postal_code' => '12207',
    ];
  }

  private static function garbageAddress(): array {
    return [
      'street_address' => 'Qzxjklw Nonexistent Blvd 999999',
      'city' => 'Notarealcityxyz',
      'state_province' => 'NY',
      'postal_code' => '00000',
    ];
  }

  // -------------------------------------------------------------------------
  // checkAddress
  // -------------------------------------------------------------------------

  /**
   * Establishes a baseline for the intended contract of checkAddress():
   * a valid, real address should validate successfully. As of this writing,
   * this fails against the 'dev' Bluebird instance's configured sage-dev
   * endpoint, because checkAddress() hardcodes provider=usps and that
   * SAGE instance returns PROVIDER_NOT_SUPPORTED for it (confirmed via
   * direct API calls; omitting the provider param entirely succeeds via
   * the default AMS provider instead). This is a known, separate issue to
   * fix — left failing here intentionally rather than weakened to match
   * current behavior, so the fix has a test to turn green.
   */
  public function testCheckAddressValidatesKnownGoodAddress(): void {
    $values = self::validAddress();
    $result = CRM_Utils_SAGE::checkAddress($values);

    $this->assertTrue($result);
    $this->assertNotEmpty($values['city']);
    $this->assertSame('NY', $values['state_province']);
    $this->assertMatchesRegularExpression('/^\d{5}$/', $values['postal_code']);
    $this->assertArrayHasKey('postal_code_suffix', $values);
  }

  public function testCheckAddressReturnsFalseWhenNoAddressProvided(): void {
    $values = ['street_address' => ''];
    $this->assertFalse(CRM_Utils_SAGE::checkAddress($values));
  }

  public function testCheckAddressReturnsFalseForGarbageAddress(): void {
    $values = self::garbageAddress();
    $this->assertFalse(CRM_Utils_SAGE::checkAddress($values));
  }

  /**
   * Two valid addresses should both validate successfully via
   * /address/validate/batch. Originally failed for two independent reasons,
   * both since fixed: the SAGE developer's endpoint fix required the
   * request body field `city` to be renamed `postalCity`
   * (getAddressesFromRows()), and batchCheckAddress() had its own hardcoded
   * `provider=usps` param (separate from checkAddress()'s, which was fixed
   * earlier) that the batch endpoint doesn't support either.
   */
  public function testBatchCheckAddressValidatesMultipleRows(): void {
    $rows = [self::validAddress(), self::secondValidAddress()];
    $result = CRM_Utils_SAGE::batchCheckAddress($rows);

    $this->assertTrue($result);
    foreach ($rows as $row) {
      $this->assertNotEmpty($row['city']);
      $this->assertSame('NY', $row['state_province']);
      $this->assertMatchesRegularExpression('/^\d{5}$/', $row['postal_code']);
    }
  }

  // -------------------------------------------------------------------------
  // geocode
  // -------------------------------------------------------------------------

  public function testGeocodeReturnsNumericLatLonForKnownAddress(): void {
    $values = self::validAddress();
    $result = CRM_Utils_SAGE::geocode($values);

    $this->assertTrue($result);
    $this->assertIsNumeric($values['geo_code_1']);
    $this->assertIsNumeric($values['geo_code_2']);

    // Broad NY State bounding box sanity check (not an exact-value assertion).
    $this->assertGreaterThan(40.4, (float) $values['geo_code_1']);
    $this->assertLessThan(45.1, (float) $values['geo_code_1']);
    $this->assertGreaterThan(-79.9, (float) $values['geo_code_2']);
    $this->assertLessThan(-71.7, (float) $values['geo_code_2']);
  }

  public function testGeocodeSetsNullStringFallbackOnFailure(): void {
    $values = self::garbageAddress();
    $result = CRM_Utils_SAGE::geocode($values);

    $this->assertFalse($result);
    $this->assertSame('null', $values['geo_code_1']);
    $this->assertSame('null', $values['geo_code_2']);
  }

  /**
   * Two valid addresses should both come back geocoded via
   * /geo/geocode/batch, once getAddressesFromRows() sends `postalCity`
   * instead of `city` (the SAGE developer's fix for this endpoint).
   */
  public function testBatchGeocodeHandlesMultipleRows(): void {
    $rows = [self::validAddress(), self::secondValidAddress()];
    $result = CRM_Utils_SAGE::batchGeocode($rows);

    $this->assertTrue($result);
    foreach ($rows as $row) {
      $this->assertIsNumeric($row['geo_code_1']);
      $this->assertIsNumeric($row['geo_code_2']);
    }
  }

  // -------------------------------------------------------------------------
  // distAssign
  // -------------------------------------------------------------------------

  public function testDistAssignReturnsFalseWhenNoAddressProvided(): void {
    $values = ['street_address' => ''];
    $this->assertFalse(CRM_Utils_SAGE::distAssign($values));
  }

  public function testDistAssignPopulatesCoreDistrictFieldsForKnownAddress(): void {
    $values = self::validAddress();
    $result = CRM_Utils_SAGE::distAssign($values);

    $this->assertTrue($result);
    // Confirmed via a direct live call: for this address, SAGE's
    // district/assign response reliably populates congressional, senate,
    // assembly, and county, but leaves election and school district empty
    // (those are precinct-level assignments SAGE doesn't have for every
    // address, not a guaranteed part of every response) — so this test
    // only asserts on the fields that are actually always present here.
    foreach ([46, 47, 48, 50] as $id) {
      $key = "custom_{$id}_-1";
      $this->assertArrayHasKey($key, $values);
      $this->assertIsNumeric($values[$key]);
    }
    // Town (52) is stored as a string, not necessarily numeric.
    $this->assertArrayHasKey('custom_52_-1', $values);
    $this->assertNotSame('', $values['custom_52_-1']);

    $this->assertIsNumeric($values['geo_code_1']);
  }

  /**
   * Two valid addresses should both come back district-assigned via
   * /district/assign/batch, once getAddressesFromRows() sends `postalCity`
   * instead of `city`. Same reasoning as the single-item test above for
   * which district fields are asserted on (congressional/senate/assembly/
   * county reliably present for both addresses; election/school/ward/
   * cityCouncil are precinct-level and address-dependent).
   */
  public function testBatchDistAssignHandlesMultipleRows(): void {
    $rows = [self::validAddress(), self::secondValidAddress()];
    $result = CRM_Utils_SAGE::batchDistAssign($rows);

    $this->assertTrue($result);
    foreach ($rows as $row) {
      foreach ([46, 47, 48, 50] as $id) {
        $key = "custom_{$id}_-1";
        $this->assertArrayHasKey($key, $row);
        $this->assertIsNumeric($row[$key]);
      }
      $this->assertArrayHasKey('custom_52_-1', $row);
      $this->assertNotSame('', $row['custom_52_-1']);
      $this->assertIsNumeric($row['geo_code_1']);
    }
  }

  // -------------------------------------------------------------------------
  // lookup (combined bluebird lookup)
  // -------------------------------------------------------------------------

  public function testLookupPerformsCombinedValidationGeocodeAndDistrictAssignment(): void {
    $values = self::validAddress();
    $result = CRM_Utils_SAGE::lookup($values);

    $this->assertTrue($result);
    $this->assertIsNumeric($values['geo_code_1']);
    $this->assertArrayHasKey('custom_47_-1', $values);
    $this->assertNotSame('', $values['custom_47_-1']);
  }

  /**
   * Contract test for the /district/bluebird response shape that lookup()
   * parses. Calls callSAGE() directly (bypassing lookup()'s own
   * state-mutation logic) so a failure points precisely at which field
   * changed, rather than surfacing as a generic assertTrue(false) in
   * testLookupPerformsCombinedValidationGeocodeAndDistrictAssignment.
   * Mirrors testBatchLookupResponseHasExpectedContractFields, but for the
   * single-item GET endpoint — note this one still uses `city`, not
   * `postalCity`; that field rename only applied to the POST batch bodies,
   * confirmed separately against the live API before writing this test.
   */
  public function testLookupResponseHasExpectedContractFields(): void {
    $xml = CRM_Utils_SAGE::callSAGE('/district/bluebird', [
      'addr1' => 'State St',
      'city' => 'Albany',
      'state' => 'NY',
      'zip5' => '12224',
    ]);

    $this->assertInstanceOf(SimpleXMLElement::class, $xml);
    $this->assertTrue(isset($xml->statusCode), 'response must have a statusCode field');
    $this->assertSame('0', (string) $xml->statusCode);

    $this->assertTrue(isset($xml->uspsValidated), 'response must have a uspsValidated field');
    $this->assertTrue(isset($xml->geocoded), 'response must have a geocoded field');
    $this->assertTrue(isset($xml->districtAssigned), 'response must have a districtAssigned field');

    $this->assertSame('true', (string) $xml->uspsValidated);
    $this->assertSame('true', (string) $xml->geocoded);
    $this->assertSame('true', (string) $xml->districtAssigned);
  }

  // -------------------------------------------------------------------------
  // batchLookup
  // -------------------------------------------------------------------------

  /**
   * Two valid addresses should both come back geocoded and
   * district-assigned. /district/bluebird/batch (the endpoint this method
   * originally used) was confirmed broken server-side — even a
   * single-item batch against it 500s, regardless of request format — and
   * per the SAGE developer, is meant to be replaced by
   * /district/assign/batch, which performs the same combined
   * validate+geocode+district-assign operation and returns the same
   * response shape. batchLookup() now targets that endpoint instead (with
   * districtStrategy=streetFallback to preserve bluebird's behavior).
   */
  public function testBatchLookupHandlesMultipleRows(): void {
    $rows = [self::validAddress(), self::secondValidAddress()];
    $result = CRM_Utils_SAGE::batchLookup($rows);

    $this->assertTrue($result);
    foreach ($rows as $row) {
      $this->assertIsNumeric($row['geo_code_1']);
      $this->assertIsNumeric($row['geo_code_2']);
      $this->assertArrayHasKey('custom_47_-1', $row);
      $this->assertNotSame('', $row['custom_47_-1']);
    }
  }

  /**
   * Contract test for the /district/assign/batch response shape that
   * batchLookup() parses. This calls callSAGEPost() directly (bypassing
   * batchLookup()'s own row-mutation logic) so a failure here points
   * precisely at which field changed, rather than surfacing as a generic
   * assertTrue(false) in testBatchLookupHandlesMultipleRows. Written
   * because this exact class of drift already happened once (the SAGE
   * developer renamed `city` to `postalCity` in the request body) — this
   * guards the response side: `total`, `results.results[]`, and each
   * result's `statusCode`/`uspsValidated`/`geocoded`/`districtAssigned`
   * fields, which is exactly what batchLookup()'s parsing loop reads.
   */
  public function testBatchLookupResponseHasExpectedContractFields(): void {
    $addresses = [
      ['addr1' => 'State St', 'postalCity' => 'Albany', 'state' => 'NY', 'zip5' => '12224'],
      ['addr1' => '24 Eagle St', 'postalCity' => 'Albany', 'state' => 'NY', 'zip5' => '12207'],
    ];
    $batchXml = CRM_Utils_SAGE::callSAGEPost(
      '/district/assign/batch',
      ['districtStrategy' => 'streetFallback'],
      json_encode($addresses)
    );

    $this->assertInstanceOf(SimpleXMLElement::class, $batchXml);
    $this->assertSame(
      count($addresses),
      (int) $batchXml->total,
      '`total` must equal the number of addresses submitted'
    );
    $this->assertSame(
      count($addresses),
      $batchXml->results->results->count(),
      '`results.results` must contain one entry per submitted address'
    );

    foreach ($batchXml->results->results as $result) {
      $this->assertTrue(isset($result->statusCode), 'each result must have a statusCode field');
      $this->assertTrue(isset($result->uspsValidated), 'each result must have a uspsValidated field');
      $this->assertTrue(isset($result->geocoded), 'each result must have a geocoded field');
      $this->assertTrue(isset($result->districtAssigned), 'each result must have a districtAssigned field');
    }

    // Both fixture addresses are known-good, so also pin down the values
    // batchLookup() checks for (`== 'true'`), not just field presence.
    foreach ($batchXml->results->results as $result) {
      $this->assertSame('true', (string) $result->uspsValidated);
      $this->assertSame('true', (string) $result->geocoded);
      $this->assertSame('true', (string) $result->districtAssigned);
    }
  }

  // -------------------------------------------------------------------------
  // callSAGE resilience (verifies the CRM/Utils/SAGE.php reliability fix)
  // -------------------------------------------------------------------------

  public function testCallSAGEReturnsNullInsteadOfThrowingForBadEndpoint(): void {
    // A nonexistent path returns a 4xx/5xx from the real server, which
    // Guzzle turns into an exception by default. Prior to the reliability
    // fix in CRM_Utils_SAGE::callSAGE(), this would propagate as an
    // uncaught exception; now it should be caught internally and return null.
    $result = CRM_Utils_SAGE::callSAGE('/this/endpoint/does/not/exist', []);
    $this->assertNull($result);
  }

}
