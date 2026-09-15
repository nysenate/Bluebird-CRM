# Chrome Mink Driver

Mink driver for controlling Chrome without the overhead of Selenium.

It communicates directly with Google Chrome over HTTP and WebSockets, which allows it to work at least twice as fast as Chrome with Selenium. For Chrome 59+ it supports headless mode, eliminating the need to install a display server, and the overhead that comes with it. This driver is tested and benchmarked against a behat suite of 1800 scenarios and 19000 steps. It can successfully run it in less than 18 minutes with Chrome 60 headless. The same suite running against Chrome 58 with xvfb and Selenium takes ~60 minutes.

[![Gitlab CI pipeline](https://gitlab.com/behat-chrome/chrome-mink-driver/badges/main/pipeline.svg)](https://gitlab.com/behat-chrome/chrome-mink-driver/badges/main/pipeline.svg)
[![OpenSSF Best Practices](https://bestpractices.coreinfrastructure.org/projects/6489/badge)](https://bestpractices.coreinfrastructure.org/projects/6489)

## Installation

```bash
composer require dmore/chrome-mink-driver
```

## Requirements

* Google Chrome or Chromium running with remote debugging.

Example:

```bash
google-chrome-stable --remote-debugging-address=0.0.0.0 --remote-debugging-port=9222
```

or headless (v59+):

```bash
google-chrome-unstable --disable-gpu --headless --remote-debugging-address=0.0.0.0 --remote-debugging-port=9222
```

It is recommended to start Chrome with the `--disable-extensions` flag.

See https://gitlab.com/DMore/behat-chrome-skeleton for a fully working example.

### Checking Chrome connectivity

To manually test connectivity, you can use `curl` or similar and request the `json/version` endpoint:

```
curl http://localhost:9222/json/version
```

## Contributing

Contributions are welcome! Use the [issue queue and merge requests to propose changes](https://gitlab.com/behat-chrome/chrome-mink-driver). Please refer to
[Gitlab documentation](https://docs.gitlab.com/ee/user/) for how to use the Gitlab interface.

- To report an issue (bug, feature request etc) use the [issue queue](https://gitlab.com/behat-chrome/chrome-mink-driver/-/issues).
- If you are reporting a potential security issue, please check "This issue is confidential" when reporting the issue to
  the project.
- To propose code changes or a solution for an issue, use [merge requests](https://gitlab.com/behat-chrome/chrome-mink-driver/-/merge_requests).
- Test coverage is executed on merge requests. Contributions should extend test coverage where possible and ensure all
  tests pass.
- Coding standards checks are executed on merge requests. Contributions should maintain coding standards.
- PHP code should adhere to [PSR12](https://www.php-fig.org/psr/psr-12/).
- `composer.json` should be normalized using `composer normalize`.

## Tests

The base test coverage is that from upstream `mink/driver-testsuite`, and validates that core DriverInterface
functionality is correct per those expectations.

Test execution requires a webserver configured to serve fixtures from
[minkphp/driver-testsuite](https://github.com/minkphp/driver-testsuite/), which is provided by a docker image from the related
[behat-chrome/docker-chrome-headless](https://gitlab.com/behat-chrome/docker-chrome-headless/) project.

Tests are executed on each merge request via Gitlab CI. New functionality or bugfixes should seek to expand
test or at least maintain existing coverage.

### Using `make` to execute commands in Docker

| command | purpose |
|--|--|
| `make install` | Install dependencies with `composer` |
| `make test` | Run tests with `phpunit` |
| `make phpcbf` | Tidy code using `phpcbf` |
| `make phpcs` | Check coding standards with `phpcs` |

### Docker environment to run commands

To perform these tasks without `make`, you can execute the same commands as above in a container. To run the tests using `phpunit`:
```text
docker run --rm -it -v .:/code -e DOCROOT=/code/vendor/mink/driver-testsuite/web-fixtures registry.gitlab.com/behat-chrome/docker-chrome-headless bash
```

then, in the container shell:
```text
composer install
vendor/bin/phpunit
```

### Executing Gitlab CI pipeline locally

You can also run the Gitlab CI pipeline in your local environment, using [firecow/gitlab-ci-local](https://github.com/firecow/gitlab-ci-local)!

## Versioning & releases

- Releases are distributed through Packagist at https://packagist.org/packages/dmore/chrome-mink-driver
- This project aims to follow [Semantic Versioning](https://semver.org/).
- Releases are coordinated using (["Release" label](https://gitlab.com/behat-chrome/chrome-mink-driver/-/issues/?state=all&label_name%5B%5D=Release)) issues in Gitlab.
- New releases are created via [git tags being pushed to Gitlab](https://gitlab.com/behat-chrome/chrome-mink-driver/-/tags).

### Making a release

When it's time to release:

1. Ensure a header for the release is added to CHANGELOG.md, retaining the "Unreleased" header at top.
2. A new release should be created using [Gitlab's Release UI](https://gitlab.com/behat-chrome/chrome-mink-driver/-/releases)
  - The release title and tag is the version only.
  - The release notes are the CHANGELOG entries for the version.
3. Packagist will detect the new release and [make it available](https://packagist.org/packages/dmore/chrome-mink-driver).

## Usage

```php
use Behat\Mink\Mink;
use Behat\Mink\Session;
use DMore\ChromeDriver\ChromeDriver;

$mink = new Mink([
  'browser' => new Session(new ChromeDriver('http://localhost:9222', null, 'http://www.google.com'))
]);
```

## Configuration

| Option           | Value                    | Description                               |
|------------------|--------------------------|-------------------------------------------|
| socketTimeout    | int, default: 10         | Connection timeout (seconds)              |
| domWaitTimeout   | int, default: 3000       | DOM ready waiting timeout (milliseconds)  |
| downloadBehavior | allow, default, deny     | Chrome switch to permit downloads. (v62+) |
| downloadPath     | e.g. /tmp/ (the default) | Where to download files to, if permitted. |

Pass configuration values as the third parameter to `new ChromeDriver()`.

## Rendering PDF and Screenshots

Despite the Mink functionality the driver supports printing PDF pages or capturing a screenshot.

```php
use Behat\Mink\Mink;
use Behat\Mink\Session;
use DMore\ChromeDriver\ChromeDriver;
$mink = new Mink(array(
    'browser' => new Session(new ChromeDriver('http://localhost:9222', null, 'http://www.google.com'))
));
$mink->setDefaultSessionName('browser');
$mink->getSession()->visit('https://gitlab.com/behat-chrome/chrome-mink-driver/blob/master/README.md');
$driver = $mink->getSession()->getDriver();
$driver->printToPdf('/tmp/readme.pdf');
```

The available options are documented here: https://chromedevtools.github.io/devtools-protocol/tot/Page/#method-printToPDF

Screenshots are supported using the Mink driver interface method `getScreenshot()`.

## Related projects

### Behat extension

To use this driver with [Behat](https://docs.behat.org/en/latest/), try [the `dmore/behat-chrome-extension` Behat extension](https://gitlab.com/behat-chrome/behat-chrome-extension).

### Docker image

A [Docker image](https://gitlab.com/behat-chrome/docker-chrome-headless) is used to execute tests against the targeted browser(s), and includes recent Chrome Stable, Chrome Beta and Chromium.
