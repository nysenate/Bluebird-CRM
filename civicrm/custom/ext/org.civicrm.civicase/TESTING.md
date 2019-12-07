# CiviCase v5: Testing

## Tests (Buildkit)

If you have created the build using `civibuild`, then simply run `phpunit4`:

```
me@localhost:~/buildkit/build/dcase/sites/all/modules/civicrm/ext/civicase$ phpunit4
PHPUnit 4.8.21 by Sebastian Bergmann and contributors.

...................................................

Time: 16.86 seconds, Memory: 52.25Mb

OK (51 tests, 381 assertions)
```

## Tests (Manual)

If you have created the build by other means, you may need to provide some
configuration details that help with executing the tests, such as

 * Credentials for an administrative CMS user (`ADMIN_USER`, `ADMIN_PASS`, `ADMIN_EMAIL`)
 * Credentials for a non-administrative CMS user (`DEMO_USER`, `DEMO_PASS`, `DEMO_EMAIL`)
 * Credentials for an empty test database (`TEST_DB_DSN`)

To initialize the configuration file, run [`cv vars:fill`](https://github.com/civicrm/cv):

```
me@localhost:~/buildkit/build/dcase/sites/all/modules/civicrm/ext/civicase$ cv vars:fill
Site: /home/me/buildkit/build/dcase/sites/default/civicrm.settings.php
These fields were missing. Setting defaults:
{
  "ADMIN_EMAIL" => "admin@example.com",
  "ADMIN_PASS" => "t0ps3cr3t",
  "ADMIN_USER" => "admin",
  "TEST_DB_DSN" => "mysql://dbUser:dbPass@dbHost/dbName?new_link=true"
}
Please edit /home/me/.cv.json
```

Then edit that file:

```
me@localhost:~/buildkit/build/dcase/sites/all/modules/civicrm/ext/civicase$ vi ~/.cv.json
```

Now run the tests

```
me@localhost:~/buildkit/build/dcase/sites/all/modules/civicrm/ext/civicase$ phpunit4
PHPUnit 4.8.21 by Sebastian Bergmann and contributors.

...................................................

Time: 16.86 seconds, Memory: 52.25Mb

OK (51 tests, 381 assertions)
```
