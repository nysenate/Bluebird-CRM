
Integrates with the Senate SAGE geocoding and district assignment service to automatically resolve coordinates and political districts for addresses.

## Manual Testing

Navigate to `civicrm/sage/test` (menu title "SageTest") for a quick manual
smoke test — it runs `checkAddress()`, `geocode()`, `distassign()`, and
`lookup()` against a few known NY addresses and displays the resulting
field values.

## Automated Testing

### Prerequisites

`cv` and `phpunit` (the **9.x** line specifically — this repo's

### Available Tests

This extension has two PHPUnit test classes under `tests/phpunit/CRM/SAGE/`:

* **`UtilsSAGETest`** — fast, no-network unit tests for the pure/protected/private
  logic in `CRM_Utils_SAGE` (URL building, response validation, address-field
  selection, address-component comparison, district-population checks, and the
  `normalizeAddr()` string normalization rules). Never touches the database or
  the SAGE API.
* **`ApiIntegrationTest`** (`@group e2e`) — live integration tests that call the
  real SAGE API configured for the Bluebird instance the suite runs against
  (via `SAGE_API_KEY`/`SAGE_API_BASE`, sourced from that instance's
  `bluebird.cfg`). It self-skips if those resolve to the placeholder values
  `NO_KEY`/`NO_API` (i.e. SAGE isn't configured for that instance).

### How to Run Automated Tests
Run all tests from the extension directory:

```bash
cd civicrm/custom/ext/gov.nysenate.sage
INSTANCE=[instance name] HTTP_HOST=[instance name] phpunit
```

To run only the fast, no-network suite (e.g. in CI):

```bash
INSTANCE=[instance name] HTTP_HOST=[instance name] phpunit --exclude-group e2e
```
