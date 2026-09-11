# nyss_print_export

Implements exports / reports for the New York State Senate Production Services team.
Implemented exports/reports include:
1. Export for Print Productions
2. Export District for Merge/Purge


## Getting Started

To use either of these reports:
1. Go to Advanced Search
2. Do a search
3. Select a subset of results
4. In the Actions menu, select the appropriate report: Export for Print Productions or Export District for Merge/Purge
5. Submit the settings for the export, eg. Avanti Job ID
6. Download the export file

## Testing

### Test suite

An **end-to-end test** (`@group e2e`) has been added to exercise `processDistrictExclude()` — the district-exclusion logic behind the "District # to Process Exclusions and Add Seeds" field on the print export form.

### Choosing an instance — read this before running

`processDistrictExclude()` resolves a district ID to a Bluebird instance via `bluebird.cfg`, then queries **that instance's database**. To avoid ever writing test data into a different instance's real database, the test replicates that same resolution in `setUp()` and compares it against `SELECT DATABASE()` on the live connection — if the district doesn't resolve back to the database the test is already connected to, the test **skips** with an explanation instead of running.

In practice this means:

- You must run against an instance whose `bluebird.cfg` entry has a `district = <n>` value that is the **first** instance in the file with that district number (`processDistrictExclude()` matches on first-found).

### Running the tests

Tests must be run **from the extension directory** so that `phpunit.xml.dist` is picked up automatically. The `INSTANCE` and `HTTP_HOST` environment variables are required for Bluebird's custom bootstrap.

```bash
cd civicrm/custom/ext/nyss_print_export
INSTANCE=training1 HTTP_HOST=training1 phpunit
```

