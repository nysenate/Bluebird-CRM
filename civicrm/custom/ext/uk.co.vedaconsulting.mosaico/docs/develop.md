# Development

## Download via Git

This option requires several command-line tools:

 * [`git`](https://git-scm.com/)
 * [`nodejs`](https://nodejs.org/en)
 * [`npm`](https://www.npmjs.com)
 * [`grunt-cli`](http://gruntjs.com/getting-started)
 * [`cv`](https://github.com/civicrm/cv)

!!! note "nodejs"
    Currently the mosaico build script only works with node v8 and older. You can use `nvm` -
    https://github.com/nvm-sh/nvm#installing-and-updating to use multiple versions of nodejs.
    Eg. `nvm install 8 && nvm use 8`

Once these are installed, you should:

```
## Navigate to your extension directory, e.g.
cd sites/default/files/civicrm/ext

## Download the extensions
git clone https://github.com/civicrm/org.civicrm.flexmailer
git clone https://github.com/civicrm/org.civicrm.shoreditch
git clone https://github.com/veda-consulting/uk.co.vedaconsulting.mosaico

## Download additional dependencies
cd uk.co.vedaconsulting.mosaico
./bin/setup.sh -D
```

## Setup.sh

The script `bin/setup.sh` handles various build activities:

```
## Download dependencies
./bin/setup.sh -D

## Regenerate DAOs
./bin/setup.sh -g

## Build zip archive
./bin/setup.sh -z
```

## Styling Changes

We use Gulp and Sass for styling and handle different running tasks. Firstly, you should install node packages using npm package manager:
```
npm install
```

Styling changes should go into `sass` directory and compiled to CSS using the following command:
```
gulp sass
```

Once you are done making your changes, please use BackstopJS (see [Testing](/testing#backstopjs-visual-regression-testing) to check for any possible visual regression issues

## Patching Mosaico

This extension ships with a patched version of Mosaico. The patches are maintained as a fork
in https://github.com/civicrm/mosaico using [Twigflow (Rebase)](https://gist.github.com/totten/39e932e5d10bc9e73e82790b2475eff2).

## Publication

Whenever a change is merged or pushed to `uk.co.vedaconsulting.mosaico`, a bot on [jenkins test-ci](https://test.civicrm.org/view/Tools/job/Tool-Publish-mosaico/) automatically builds a new `zip` archive
and publishes [uk.co.vedaconsulting.mosaico-latest.zip](https://download.civicrm.org/extension/uk.co.vedaconsulting.mosaico/latest/uk.co.vedaconsulting.mosaico-latest.zip).

The build/publish process has a few properties:

 * It combines [`uk.co.vedaconsulting.mosaico`](https://github.com/veda-consulting/uk.co.vedaconsulting.mosaico),
   [`civicrm/mosaico`](https://github.com/civicrm/mosaico), and any other runtime dependencies into one `zip` file.
 * The version number is determined by reading `info.xml` (`<version>`) and appending the current Unix timestamp.
   * Example: If the `version` is declared as `1.0.beta1`, then it will be published as `1.0.beta1.1478151288`.
 * Three files are published:
   * The `zip` archive
   * The new `info.xml` file
   * A JSON document describing the build.
 * An alias is provided under the folder `latest`.

The bot does *not* publish the new version to `civicrm.org`.  To do this, download the `latest.zip` to get the version from the info.xml (
eg. `2.5.1597918155`).

Then add the actual release zip file to the release node on `https://civicrm.org/extensions/email-template-builder`.

Example filename: `https://download.civicrm.org/extension/uk.co.vedaconsulting.mosaico/2.5.1597918155/uk.co.vedaconsulting.mosaico-2.5.1597918155.zip`
