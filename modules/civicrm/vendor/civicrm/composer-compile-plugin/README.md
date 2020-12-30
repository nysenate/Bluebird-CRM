# Composer Compile Plugin [![Build Status](https://travis-ci.com/civicrm/composer-compile-plugin.svg?branch=master)](https://travis-ci.com/civicrm/composer-compile-plugin)

The "Compile" plugin enables developers of PHP libraries to define free-form "compilation" tasks, such as:

* Converting SCSS to CSS
* Generating PHP wrappers based on an XML schema

For PHP site-builders who use these libraries, the compilation process is a seamless part of the regular download (`composer install`, etc). 

Tasks may be defined in several ways, such as:

* Shell command (`@sh cat file-{1,2,3} > big-file`)
* PHP method (`@php-method MyBuilder::build`)
* PHP eval (`@php-eval file_put_contents('big-file', make_big_file());`)
* PHP script file (`@php-script my-script.php`)
* Composer subcommand (`@composer dump-autoload`)

Features:

* Easy to enable. No manual configuration for downstream site-builders. Framework agnostic.
* Plays well with other `composer` tooling, like [forked repositories](https://matthewsetter.com/series/tooling/composer/forked-repositories/), [composer-patches](https://github.com/cweagans/composer-patches), [composer-locator](https://github.com/mindplay-dk/composer-locator), and [composer-downloads](https://github.com/civicrm/composer-downloads-plugin).
* Allows library repos to remain "clean" without committing build artifacts.
* Runs locally in PHP. Does not require external/hosted services or additional interpreters.
* Supports file monitoring for automatic rebuilds (`composer compile:watch`)
* Enforces permission model to address historical concerns about `composer` hooks and untrusted libraries.
* Integration-tests pass on both `composer` v1.10 and v2.0-dev (*at time of writing*).

The plugin is currently in version `0.x`. The integration-tests are passing, and it seems to be working for the original need, but it's also new and hasn't seen wide-spread testing yet.

## More information

* [doc/site-build.md](doc/site-build.md): Managing the root package (for site-builders)
* [doc/tasks.md](doc/tasks.md): Working with tasks (for library developers)
* [doc/evaluation.md](doc/evaluation.md): Evaluate and compare against similar options
* [doc/develop.md](doc/develop.md): How to work with `composer-compile-plugin.git` (for plugin-development)
