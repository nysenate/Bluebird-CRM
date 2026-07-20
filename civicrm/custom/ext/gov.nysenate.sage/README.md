# gov.nysenate.sage

![Screenshot](/images/screenshot.png)

(*FIXME: In one or two paragraphs, describe what the extension does and why one would download it. *)

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v7.2+
* CiviCRM (*FIXME: Version number*)

## Installation (Web UI)

Learn more about installing CiviCRM extensions in the [CiviCRM Sysadmin Guide](https://docs.civicrm.org/sysadmin/en/latest/customize/extensions/).

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl gov.nysenate.sage@https://github.com/FIXME/gov.nysenate.sage/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/FIXME/gov.nysenate.sage.git
cv en sage
```

## Getting Started

(* FIXME: Where would a new user navigate to get started? What changes would they see? *)

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

## Known Issues

(* FIXME *)
