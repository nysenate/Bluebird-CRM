# Chrome Mink Driver

Mink driver for controlling Chrome without the overhead of Selenium.

It communicates directly with Google Chrome over HTTP and WebSockets, which allows it to work at least twice as fast as Chrome with Selenium. For Chrome 59+ it supports headless mode, eliminating the need to install a display server, and the overhead that comes with it. This driver is tested and benchmarked against a behat suite of 1800 scenarios and 19000 steps. It can successfully run it in less than 18 minutes with Chrome 60 headless. The same suite running against Chrome 58 with xvfb and Selenium takes ~60 minutes.

[![Gitlab CI pipeline](https://gitlab.com/behat-chrome/chrome-mink-driver/badges/main/pipeline.svg)](https://gitlab.com/behat-chrome/chrome-mink-driver/badges/main/pipeline.svg)
[![OpenSSF Best Practices](https://bestpractices.coreinfrastructure.org/projects/6489/badge)](https://bestpractices.coreinfrastructure.org/projects/6489)

## Installation:

```bash
composer require dmore/chrome-mink-driver
```

## Requirements:

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

## Contributing

Contributions are welcome! Use the [issue queue and merge requests to propose changes](https://gitlab.com/behat-chrome/chrome-mink-driver). Please refer to [Gitlab documentation](https://docs.gitlab.com/ee/user/) for how to use the Gitlab interface.

- To report an issue (bug, feature request etc) use the [issue queue](https://gitlab.com/behat-chrome/chrome-mink-driver/-/issues).
- If you are reporting a potential security issue, please check "This issue is confidential" when reporting the issue to the project.
- To propose code changes or a solution for an issue, use [merge requests](https://gitlab.com/behat-chrome/chrome-mink-driver/-/merge_requests).
- Test coverage is executed on merge requests. Contributions should extend test coverage where possible and ensure all tests pass.
- Coding standards checks are executed on merge requests. Contributions should maintain coding standards.

## Tests

The project has test coverage, which you can execute using the commands below.

Test execution requires a webserver configured to serve fixtures from [minkphp/driver-testsuite](https://github.com/minkphp/driver-testsuite/), which is provided by a docker image from the related [behat-chrome/docker-chrome-headless](https://gitlab.com/behat-chrome/docker-chrome-headless/) project.  Tests executed are both [tests specific to this driver](https://gitlab.com/behat-chrome/chrome-mink-driver/-/tree/main/tests) and the more comprehensive test suite from [mink/driver-testsuite](https://github.com/minkphp/driver-testsuite/), which is the common testsuite to ensure consistency across Mink driver implementations.

### Using `make`

| command | purpose |
|--|--|
| `make install` | Install dependencies with `composer` |
| `make test` | Run tests with `phpunit` |
| `make phpcbf` | Tidy code using `phpcbf` |
| `make phpcs` | Check coding standards with `phpcs` |

### Without `make`

To perform these tasks without `make`, you can execute the same commands as above in a container. To run the tests using `phpunit`:
```text
docker run --rm -it -v .:/code -e DOCROOT=/code/vendor/mink/driver-testsuite/web-fixtures registry.gitlab.com/behat-chrome/docker-chrome-headless bash
```
then, in the container shell:
```text
composer install
vendor/bin/phpunit
```

## Versioning & releases

- Releases are distributed through Packagist at https://packagist.org/packages/dmore/chrome-mink-driver
- This project aims to follow [Semantic Versioning](https://semver.org/).
- Releases are communicated using release co-ordination issues in Gitlab.
- New releases are created via [git tags being pushed to Gitlab](https://gitlab.com/behat-chrome/chrome-mink-driver/-/tags).

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
