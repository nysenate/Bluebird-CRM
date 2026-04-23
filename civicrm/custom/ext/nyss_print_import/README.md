# nyss_print_import

A CiviCRM extension for bulk-importing constituent records into Bluebird CRM. Refactored from the legacy `modules/nyss_io/nyss_io.module` Drupal module.

This is an [extension for CiviCRM](https://docs.civicrm.org/sysadmin/en/latest/customize/extensions/), licensed under [AGPL-3.0](LICENSE.txt).

## Testing

Tests are run with PHPUnit from the extension root. `INSTANCE` and `HTTP_HOST` must both be set to the Bluebird instance shortname.

### Run all tests

```bash
cd civicrm/custom/ext/nyss_print_import
INSTANCE=sd99 HTTP_HOST=bb310 phpunit
```

### Run only unit tests (no DB required)

```bash
INSTANCE=sd99 HTTP_HOST=sd99 phpunit --group unit
```

### Run only integration tests (requires a live DB)

```bash
INSTANCE=sd99 HTTP_HOST=sd99 phpunit --group integration
```

### Verbose output with readable test names

```bash
INSTANCE=sd99 HTTP_HOST=sd99 phpunit --group integration --verbose --testdox
```

### Enable debug output from the import classes

Set `NYSS_DEBUG=1` to enable `Utils::out('debug', ...)` messages during the test run:

```bash
INSTANCE=sd99 HTTP_HOST=sd99 NYSS_DEBUG=1 phpunit --group integration --verbose --testdox
```

## Known Issues

None.
