<?php
declare(strict_types = 1);

/**
 * Unit tests for CRM_NYSS_Search_DistrictQueryModifier. Pure logic, no DB.
 *
 * Run from the extension directory:
 *   INSTANCE=<instance> HTTP_HOST=<instance> phpunit
 */
class CRM_NYSS_Search_DistrictQueryModifierTest extends \PHPUnit\Framework\TestCase {

    // -------------------------------------------------------------------------
    // isDistrictIntegerField / isDistrictStringField
    // -------------------------------------------------------------------------

    public static function integerFieldProvider(): array {
        return [
            'congressional_district_46' => [46, TRUE],
            'ny_senate_district_47'     => [47, TRUE],
            'ny_assembly_district_48'   => [48, TRUE],
            'election_district_49'      => [49, TRUE],
            'county_50'                 => [50, TRUE],
            'county_leg_district_51'    => [51, TRUE],
            'ward_53'                   => [53, TRUE],
            'school_district_54'        => [54, TRUE],
            'nyc_council_55'            => [55, TRUE],
            'string field 52 is not int'=> [52, FALSE],
            'string field 56 is not int'=> [56, FALSE],
            'unrelated field id'        => [999, FALSE],
            'zero'                      => [0, FALSE],
            'negative'                  => [-1, FALSE],
        ];
    }

    /** @dataProvider integerFieldProvider */
    public function testIsDistrictIntegerField(int $fieldId, bool $expected): void {
        $this->assertSame($expected, CRM_NYSS_Search_DistrictQueryModifier::isDistrictIntegerField($fieldId));
    }

    public static function stringFieldProvider(): array {
        return [
            'town_52'                    => [52, TRUE],
            'neighborhood_56'            => [56, TRUE],
            'int field 46 is not string' => [46, FALSE],
            'int field 50 is not string' => [50, FALSE],
            'unrelated field id'         => [999, FALSE],
            'zero'                       => [0, FALSE],
        ];
    }

    /** @dataProvider stringFieldProvider */
    public function testIsDistrictStringField(int $fieldId, bool $expected): void {
        $this->assertSame($expected, CRM_NYSS_Search_DistrictQueryModifier::isDistrictStringField($fieldId));
    }

    // -------------------------------------------------------------------------
    // modifyStringFieldClause
    // -------------------------------------------------------------------------

    public function testModifyStringFieldClauseSplitsCommaDelimitedValuesForDistrictField(): void {
        $op = '=';
        $value = 'ALBANY[:comma:] -NYC';
        CRM_NYSS_Search_DistrictQueryModifier::modifyStringFieldClause(52, $op, $value);
        $this->assertSame('IN', $op);
        $this->assertSame(['ALBANY', '-NYC'], $value);
    }

    public function testModifyStringFieldClauseTrimsWhitespaceAroundValues(): void {
        $op = '=';
        $value = ' ALBANY [:comma:]  -NYC ';
        CRM_NYSS_Search_DistrictQueryModifier::modifyStringFieldClause(56, $op, $value);
        $this->assertSame('IN', $op);
        $this->assertSame(['ALBANY', '-NYC'], $value);
    }

    public function testModifyStringFieldClauseLeavesNonEqualsOpUntouched(): void {
        $op = 'LIKE';
        $value = 'ALBANY[:comma:]BUFFAL';
        CRM_NYSS_Search_DistrictQueryModifier::modifyStringFieldClause(52, $op, $value);
        $this->assertSame('LIKE', $op);
        $this->assertSame('ALBANY[:comma:]BUFFAL', $value);
    }

    public function testModifyStringFieldClauseLeavesNonDistrictFieldUntouched(): void {
        $op = '=';
        $value = 'ALBANY[:comma:]BUFFAL';
        CRM_NYSS_Search_DistrictQueryModifier::modifyStringFieldClause(999, $op, $value);
        $this->assertSame('=', $op);
        $this->assertSame('ALBANY[:comma:]BUFFAL', $value);
    }

    // -------------------------------------------------------------------------
    // skipIntegerFieldClause
    // -------------------------------------------------------------------------

    public static function skipIntegerFieldClauseProvider(): array {
        return [
            'district field, =, empty string'  => [50, '=', '', TRUE],
            'district field, =, null'          => [50, '=', NULL, TRUE],
            'district field, =, "0" is empty'  => [50, '=', '0', TRUE],
            'district field, =, non-empty'     => [50, '=', '5', FALSE],
            'district field, LIKE, empty'      => [50, 'LIKE', '', FALSE],
            'non-district field, =, empty'     => [999, '=', '', FALSE],
        ];
    }

    /** @dataProvider skipIntegerFieldClauseProvider */
    public function testSkipIntegerFieldClause(int $fieldId, string $op, $value, bool $expected): void {
        $this->assertSame($expected, CRM_NYSS_Search_DistrictQueryModifier::skipIntegerFieldClause($fieldId, $op, $value));
    }

    // -------------------------------------------------------------------------
    // modifyIntegerFieldClause
    // -------------------------------------------------------------------------

    public function testModifyIntegerFieldClauseSplitsCommaDelimitedValuesForDistrictField(): void {
        $op = '=';
        $value = '5,7';
        $dataType = 'Integer';
        CRM_NYSS_Search_DistrictQueryModifier::modifyIntegerFieldClause(50, $op, $value, $dataType);
        $this->assertSame('IN', $op);
        $this->assertSame(['5', '7'], $value);
        $this->assertSame('Integer', $dataType);
    }

    public function testModifyIntegerFieldClauseTrimsWhitespaceAroundValues(): void {
        $op = '=';
        $value = ' 5 , 7 ';
        $dataType = 'Integer';
        CRM_NYSS_Search_DistrictQueryModifier::modifyIntegerFieldClause(50, $op, $value, $dataType);
        $this->assertSame('IN', $op);
        $this->assertSame(['5', '7'], $value);
    }

    public function testModifyIntegerFieldClauseLeavesSingleValueAsEquals(): void {
        $op = '=';
        $value = '5';
        $dataType = 'Integer';
        CRM_NYSS_Search_DistrictQueryModifier::modifyIntegerFieldClause(50, $op, $value, $dataType);
        $this->assertSame('=', $op);
        $this->assertSame('5', $value);
        $this->assertSame('Integer', $dataType);
    }

    public function testModifyIntegerFieldClauseLeavesEmptyValueUnchanged(): void {
        $op = '=';
        $value = '';
        $dataType = 'Integer';
        CRM_NYSS_Search_DistrictQueryModifier::modifyIntegerFieldClause(50, $op, $value, $dataType);
        $this->assertSame('=', $op);
        $this->assertSame('', $value);
        $this->assertSame('Integer', $dataType);
    }

    public function testModifyIntegerFieldClauseSwitchesLikeToStringDataType(): void {
        $op = 'LIKE';
        $value = '5%';
        $dataType = 'Integer';
        CRM_NYSS_Search_DistrictQueryModifier::modifyIntegerFieldClause(50, $op, $value, $dataType);
        $this->assertSame('LIKE', $op);
        $this->assertSame('5%', $value);
        $this->assertSame('String', $dataType);
    }

    public function testModifyIntegerFieldClauseLeavesNonDistrictFieldUntouched(): void {
        $op = '=';
        $value = '5,7';
        $dataType = 'Integer';
        CRM_NYSS_Search_DistrictQueryModifier::modifyIntegerFieldClause(999, $op, $value, $dataType);
        $this->assertSame('=', $op);
        $this->assertSame('5,7', $value);
        $this->assertSame('Integer', $dataType);
    }

}
