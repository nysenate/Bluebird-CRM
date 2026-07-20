<?php
declare(strict_types = 1);

/**
 * Fast, no-network unit tests for the pure/protected/private logic in
 * CRM_Utils_SAGE. None of these touch the database or the SAGE HTTP API —
 * see ApiIntegrationTest for tests that exercise the live service.
 */
class CRM_SAGE_UtilsSAGETest extends \PHPUnit\Framework\TestCase {

  /**
   * Invokes a protected/private static method on CRM_Utils_SAGE via reflection.
   */
  private static function invokeSage(string $method, array $args = []) {
    $ref = new \ReflectionMethod(CRM_Utils_SAGE::class, $method);
    $ref->setAccessible(TRUE);
    return $ref->invokeArgs(NULL, $args);
  }

  // -------------------------------------------------------------------------
  // buildSAGEUrl
  // -------------------------------------------------------------------------

  public function testBuildSAGEUrlAddsFormatAndKeyWhenMissing(): void {
    $url = self::invokeSage('buildSAGEUrl', ['/geo/geocode', []]);
    $this->assertStringStartsWith(SAGE_API_BASE . '/geo/geocode?', $url);

    parse_str((string) parse_url($url, PHP_URL_QUERY), $q);
    $this->assertSame('xml', $q['format']);
    $this->assertSame(SAGE_API_KEY, $q['key']);
  }

  public function testBuildSAGEUrlDoesNotOverrideExistingParams(): void {
    $url = self::invokeSage('buildSAGEUrl', ['/geo/geocode', ['format' => 'json']]);
    parse_str((string) parse_url($url, PHP_URL_QUERY), $q);

    $this->assertSame('json', $q['format']);
    $this->assertSame(SAGE_API_KEY, $q['key']);
  }

  // -------------------------------------------------------------------------
  // validateResponse
  // -------------------------------------------------------------------------

  public function testValidateResponseTrueForStatusCodeZero(): void {
    $xml = simplexml_load_string('<response><statusCode>0</statusCode></response>');
    $this->assertTrue(self::invokeSage('validateResponse', [$xml]));
  }

  public function testValidateResponseFalseForNonZeroStatusCode(): void {
    $xml = simplexml_load_string('<response><statusCode>1</statusCode></response>');
    $this->assertFalse(self::invokeSage('validateResponse', [$xml]));
  }

  public static function nonXmlProvider(): array {
    return [
      'null' => [NULL],
      'false' => [FALSE],
      'string' => ['garbage'],
    ];
  }

  /**
   * @dataProvider nonXmlProvider
   */
  public function testValidateResponseFalseForNonXmlInput($input): void {
    $this->assertFalse(self::invokeSage('validateResponse', [$input]));
  }

  // -------------------------------------------------------------------------
  // getAddress
  // -------------------------------------------------------------------------

  public function testGetAddressPrefersStreetAddress(): void {
    $result = self::invokeSage('getAddress', [[
      'street_address' => '123 Main St',
      'supplemental_address_1' => 'Apt 2',
    ]]);
    $this->assertSame(['street_address', '123 Main St'], $result);
  }

  public function testGetAddressFallsBackToSupplementalAddress1(): void {
    $result = self::invokeSage('getAddress', [[
      'supplemental_address_1' => '456 Oak Ave',
    ]]);
    $this->assertSame(['supplemental_address_1', '456 Oak Ave'], $result);
  }

  public function testGetAddressReturnsEmptyStreetAddressWhenNoAddressFieldsPresent(): void {
    $result = self::invokeSage('getAddress', [['city' => 'Albany']]);
    $this->assertSame(['street_address', ''], $result);
  }

  // -------------------------------------------------------------------------
  // compareAddressComponents
  // -------------------------------------------------------------------------

  public function testCompareAddressComponentsFalseForNullAddr(): void {
    $this->assertFalse(CRM_Utils_SAGE::compareAddressComponents(NULL, []));
  }

  public function testCompareAddressComponentsFalseForNonArrayParams(): void {
    $addr = new \stdClass();
    $this->assertFalse(CRM_Utils_SAGE::compareAddressComponents($addr, 'not-an-array'));
  }

  private static function fullAddrFixture(): \stdClass {
    $addr = new \stdClass();
    $addr->street_address = '123 Main St';
    $addr->city = 'Albany';
    $addr->postal_code = '12207';
    $addr->postal_code_suffix = '1234';
    $addr->state_province_id = 1000;
    $addr->supplemental_address_1 = 'Apt 2';
    return $addr;
  }

  private static function fullParamsFixture(): array {
    return [
      'street_address' => '123 Main St',
      'city' => 'Albany',
      'postal_code' => '12207',
      'postal_code_suffix' => '1234',
      'state_province_id' => 1000,
      'supplemental_address_1' => 'Apt 2',
    ];
  }

  public function testCompareAddressComponentsTrueWhenAllFieldsMatch(): void {
    $this->assertTrue(CRM_Utils_SAGE::compareAddressComponents(self::fullAddrFixture(), self::fullParamsFixture()));
  }

  public function testCompareAddressComponentsFalseWhenOneFieldDiffers(): void {
    $params = self::fullParamsFixture();
    $params['postal_code'] = '99999';
    $this->assertFalse(CRM_Utils_SAGE::compareAddressComponents(self::fullAddrFixture(), $params));
  }

  public function testCompareAddressComponentsTrueWhenBothMissingSameOptionalKey(): void {
    $addr = self::fullAddrFixture();
    unset($addr->supplemental_address_1);

    $params = self::fullParamsFixture();
    unset($params['supplemental_address_1']);

    $this->assertTrue(CRM_Utils_SAGE::compareAddressComponents($addr, $params));
  }

  // -------------------------------------------------------------------------
  // customValue
  // -------------------------------------------------------------------------

  public function testCustomValueFindsMatchingCustomKey(): void {
    $params = ['custom_46_5' => '21', 'other' => 'x'];
    $this->assertSame('21', CRM_Utils_SAGE::customValue($params, 46));
  }

  public function testCustomValueReturnsNullWhenNoMatchingKey(): void {
    $params = ['custom_47_5' => '3'];
    $this->assertNull(CRM_Utils_SAGE::customValue($params, 46));
  }

  // -------------------------------------------------------------------------
  // districtInfoPopulated
  // -------------------------------------------------------------------------

  private static function coreDistrictIds(): array {
    return [46, 47, 48, 49, 50, 52, 54];
  }

  public function testDistrictInfoPopulatedTrueWhenAllSevenCoreFieldsPresent(): void {
    $params = [];
    foreach (self::coreDistrictIds() as $id) {
      $params["custom_{$id}_-1"] = '5';
    }
    $this->assertTrue(CRM_Utils_SAGE::districtInfoPopulated($params));
  }

  public function testDistrictInfoPopulatedFalseWhenOneCoreFieldMissing(): void {
    $params = [];
    foreach (self::coreDistrictIds() as $id) {
      if ($id === 50) {
        continue;
      }
      $params["custom_{$id}_-1"] = '5';
    }
    $this->assertFalse(CRM_Utils_SAGE::districtInfoPopulated($params));
  }

  public function testDistrictInfoPopulatedFalseWhenValueIsZero(): void {
    // Documents an existing quirk: districtInfoPopulated uses empty(), so a
    // legitimate district value of "0" reads as "missing". Not a bug to fix.
    $params = [];
    foreach (self::coreDistrictIds() as $id) {
      $params["custom_{$id}_-1"] = '5';
    }
    $params['custom_46_-1'] = '0';
    $this->assertFalse(CRM_Utils_SAGE::districtInfoPopulated($params));
  }

  // -------------------------------------------------------------------------
  // normalizeAddr
  // -------------------------------------------------------------------------

  public static function normalizeAddrProvider(): array {
    return [
      'PO Box casing fixed' => ['Po Box 123', 'PO Box 123', 'PO Box 123'],
      'Mc-name capitalized correctly' => ['123 Mcdonald St', '123 McDonald St', '123 McDonald St'],
      'alphanumeric street number uppercased' => ['19a Main St', '19a Main St', '19A Main St'],
      'ordinal suffix left alone' => ['1st Ave', '1st Ave', '1st Ave'],
      'hyphenated street number format retained from original' => ['7B Main St', '7-B Main St', '7-B Main St'],
      'plain address unchanged' => ['100 Main St', '100 Main St', '100 Main St'],
    ];
  }

  /**
   * @dataProvider normalizeAddrProvider
   */
  public function testNormalizeAddr(string $addr, string $origAddr, string $expected): void {
    $this->assertSame($expected, self::invokeSage('normalizeAddr', [$addr, $origAddr]));
  }

}
