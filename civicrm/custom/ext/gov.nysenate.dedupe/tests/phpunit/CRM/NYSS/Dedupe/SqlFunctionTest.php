<?php
declare(strict_types = 1);

use CRM_NYSS_Dedupe_ExtensionUtil as E;
use Civi\Test\EndToEndInterface;

/**
 * Integration tests for BB_NORMALIZE and BB_NORMALIZE_ADDR MySQL functions
 * defined in sql/shadow_func.sql.
 *
 * Run from the extension directory:
 *   INSTANCE=<instance> HTTP_HOST=<instance> phpunit
 *
 * @group e2e
 */
class CRM_NYSS_Dedupe_SqlFunctionTest extends \PHPUnit\Framework\TestCase implements EndToEndInterface {

    private static ?bool $functionsInstalled = NULL;

    public static function setUpBeforeClass(): void {
        \Civi\Test::e2e()->installMe(__DIR__)->apply();
    }

    public function setUp(): void {
        parent::setUp();
        if (self::$functionsInstalled === NULL) {
            $count = (int) CRM_Core_DAO::singleValueQuery(
                "SELECT COUNT(*) FROM information_schema.ROUTINES
                 WHERE ROUTINE_SCHEMA = DATABASE()
                   AND ROUTINE_NAME IN ('BB_NORMALIZE', 'BB_NORMALIZE_ADDR', 'BB_ADDR_REPLACE')"
            );
            self::$functionsInstalled = ($count === 3);
        }
        if (!self::$functionsInstalled) {
            $this->markTestSkipped(
                'SQL functions not installed. Fix any syntax errors in sql/shadow_func.sql then re-run it against the database.'
            );
        }
    }

    // -------------------------------------------------------------------------
    // BB_NORMALIZE
    // -------------------------------------------------------------------------

    public static function normalizeProvider(): array {
        return [
            'null returns null'             => [null,              null],
            'empty string returns null'     => ['',               null],
            'lowercases input'              => ['Hello',          'hello'],
            'strips spaces'                 => ['Hello World',    'helloworld'],
            'strips apostrophe'             => ["O'Brien",        'obrien'],
            'strips hyphen'                 => ['Smith-Jones',    'smithjones'],
            'strips comma and period'       => ['Hello, World.',  'helloworld'],
            'strips semicolon colon hash'   => ['a;b:c#d',        'abcd'],
            'already normalized passthrough'=> ['smith',          'smith'],
        ];
    }

    /**
     * @dataProvider normalizeProvider
     */
    public function testBbNormalize(?string $input, ?string $expected): void {
        if ($input === null) {
            $result = CRM_Core_DAO::singleValueQuery('SELECT BB_NORMALIZE(NULL)');
        } else {
            $escaped = CRM_Core_DAO::escapeString($input);
            $result = CRM_Core_DAO::singleValueQuery("SELECT BB_NORMALIZE('$escaped')");
        }
        $this->assertSame($expected, $result);
    }

    // -------------------------------------------------------------------------
    // BB_NORMALIZE_ADDR
    //
    // Expected values are derived by tracing through shadow_func.sql logic:
    //   1. TRIM + LCASE
    //   2. Strip ordinals: (?<=[0-9])(?:st|nd|rd|th)
    //   3. Spacing regex: ^(\d+)-?(\w+)\s  → '$1 $2 '
    //   4. Punctuation: , . - ; : # → space; ' → removed
    //   5. BB_ADDR_REPLACE: per address_abbreviations table (e.g. street→st, avenue→ave)
    //   6. Adhoc: apt→'', floor→fl, east→e, north→n, west→w, south→s
    //   7. TRIM + collapse multiple spaces
    // -------------------------------------------------------------------------

    public static function normalizeAddrProvider(): array {
        return [
            'null returns null'                => [null,                    null],
            'empty string returns null'        => ['',                      null],

            // basic
            'lowercase passthrough'            => ['Main St',               'main st'],

            // ordinal stripping
            'strips ordinal st from 1st'       => ['1st Street',            '1 st'],
            'strips ordinal nd from 22nd'      => ['22nd Avenue',           '22 ave'],
            'strips ordinal rd from 3rd'       => ['3rd Ave',               '3 ave'],
            'strips ordinal th from 4th'       => ['4th Pl',                '4 pl'],

            // adhoc word replacements
            'floor becomes fl'                 => ['3rd Floor',             '3 fl'],
            'apt removed'                      => ['Apt 4B',                '4b'],
            'east becomes e'                   => ['East Main',             'e main'],
            'west becomes w'                   => ['West Broadway',         'w broadway'],
            'north becomes n'                  => ['100 North Ave',         '100 n ave'],
            'south becomes s'                  => ['South St',              's st'],

            // punctuation
            'comma becomes space'              => ['123 Main St, Apt 4',    '123 main st 4'],

            // spacing normalization (^(\d+)-?(\w+)\s pattern)
            'digit-dash-letter normalized'     => ['7-B Main St',           '7 b main st'],
            'compact digit-letter normalized'  => ['7B Main St',            '7 b main st'],

            // whitespace
            'multiple spaces collapsed'        => ['  Multiple   Spaces  ', 'multiple spaces'],

            // combined
            'full address with ordinal and direction' => ['100 East 42nd Street', '100 e 42 st'],

            // unicode / emoji stripping
            'supplementary char stripped'            => ["100 Main \u{1F600} St",  '100 main st'],
            'bmp symbol stripped'                    => ["100 Main \u{2603} St",   '100 main st'],
        ];
    }

    /**
     * @dataProvider normalizeAddrProvider
     */
    public function testBbNormalizeAddr(?string $input, ?string $expected): void {
        if ($input === null) {
            $result = CRM_Core_DAO::singleValueQuery('SELECT BB_NORMALIZE_ADDR(NULL)');
        } else {
            $escaped = CRM_Core_DAO::escapeString($input);
            $result = CRM_Core_DAO::singleValueQuery("SELECT BB_NORMALIZE_ADDR('$escaped')");
        }
        $this->assertSame($expected, $result);
    }

    // -------------------------------------------------------------------------
    // Parity: BB_NORMALIZE_ADDR (SQL) vs CRM_NYSS_Dedupe_Service_Normalizer (PHP)
    //
    // The PHP normalizer is documented as a duplicate of BB_NORMALIZE_ADDR.
    // These tests catch drift between the two implementations.
    // -------------------------------------------------------------------------

    public static function parityProvider(): array {
        return [
            ['100 Main Street'],
            ['1st Avenue'],
            ['22nd Street'],
            ['East 42nd St'],
            ['West 14th Street'],
            ['South Park Ave'],
            ['100 Main St, Suite 500'],
            ['3rd Floor'],
            ['Apt 4'],
            ['100 North Ave'],
            ['7B Main St'],
            ['7-B Main St'],
            // Unicode/emoji stripping
            ["100 Main \u{1F600} St"],
            ["100 Main \u{2603} St"],
        ];
    }

    /**
     * @dataProvider parityProvider
     */
    public function testSqlMatchesPhpNormalizer(string $input): void {
        $normalizer = Civi::service('nyssdedupe.normalizer');
        $phpResult = $normalizer->normalize_addr($input);

        $escaped = CRM_Core_DAO::escapeString($input);
        $sqlResult = CRM_Core_DAO::singleValueQuery("SELECT BB_NORMALIZE_ADDR('$escaped')");

        // PHP returns '' for inputs that normalize to empty; SQL returns NULL.
        $this->assertSame(
            $phpResult === '' ? null : $phpResult,
            $sqlResult,
            "PHP and SQL normalizers differ for input: \"$input\""
        );
    }

}
