# NYSS Dedupe Extension

CiviCRM extension providing custom deduplication rules and shadow tables for the NY Senate Bluebird CRM.

## SQL Functions

Three stored functions are defined in `sql/shadow_func.sql`:

| Function | Purpose |
|---|---|
| `BB_NORMALIZE(value)` | Strips punctuation and spaces, lowercases. Used for names. |
| `BB_NORMALIZE_ADDR(value)` | Normalizes address strings: strips ordinals, standardizes abbreviations, expands directional shorthands. |
| `BB_ADDR_REPLACE(address)` | Helper called by `BB_NORMALIZE_ADDR`; replaces street-type words using the `address_abbreviations` table. |

`BB_NORMALIZE` logic is duplicated in PHP at `CRM/NYSS/Dedupe/Service/Normalizer.php` for performance-sensitive paths. If you change one, change the other.

### Installing the functions

```bash
# From the APPROOT/scripts dir:
./execSql.sh INSTANCE -f ../civicrm/custom/ext/gov.nysenate.dedupe/sql/shadow_func.sql
```

Or directly:
```bash
mysql -u <user> -p <database> < civicrm/custom/ext/gov.nysenate.dedupe/sql/shadow_func.sql
```

The functions must be reinstalled in the dabtabase whenever `sql/shadow_func.sql` is modified.

`templates/sql/cividb_template.sql` should be updated whenever `sql/shadow_func.sql` is modified.

## Testing

### Test suite

Integration tests live in `tests/phpunit/CRM/NYSS/Dedupe/SqlFunctionTest.php`. They are **end-to-end tests** that run against a live Bluebird database instance and exercise the MySQL functions directly via `CRM_Core_DAO`.

Three groups of tests are included:

- **`testBbNormalize`** — verifies `BB_NORMALIZE` output for null, empty, punctuation, and casing inputs.
- **`testBbNormalizeAddr`** — verifies `BB_NORMALIZE_ADDR` for ordinal stripping, directional shorthands, abbreviation lookup, compact address numbers (`7B`, `7-B`), and multi-word normalization.
- **`testSqlMatchesPhpNormalizer`** — cross-checks `BB_NORMALIZE_ADDR` against the PHP `normalize_addr()` implementation to catch drift between the two.

### Prerequisites

| Requirement | Version |
|---|---|
| PHP | 8.x |
| PHPUnit | **9.x** (not 10+) |
| CiviCRM | 5.75+ |
| MySQL | 8.0+ (ICU regex required for `REGEXP_REPLACE`) |

> **PHPUnit version note:** The test suite uses `@dataProvider` docblock annotations, which are the PHPUnit 9 syntax. PHPUnit 10+ deprecated these in favor of `#[DataProvider]` attributes. Do not upgrade to PHPUnit 10+ without updating the annotations in the test file.

### Running the tests

Tests must be run **from the extension directory** so that `phpunit.xml.dist` is picked up automatically. The `INSTANCE` and `HTTP_HOST` environment variables are required for Bluebird's custom bootstrap.

```bash
cd civicrm/custom/ext/gov.nysenate.dedupe
INSTANCE=<instance> HTTP_HOST=<instance> phpunit
```