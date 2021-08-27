# Composer Compile Plugin [![Build Status](https://travis-ci.com/civicrm/composer-compile-plugin.svg?branch=master)](https://travis-ci.com/civicrm/composer-compile-plugin)

The "Compile" plugin enables developers of PHP libraries to define free-form "compilation" tasks, such as:

* Converting SCSS to CSS
* Generating PHP wrappers based on an XML schema

For site-builders who use these PHP libraries, compilation tasks run seamlessly during the regular download (`composer install`, etc).

For developers who publish PHP libraries, a task can be as simple as:

```json
{
  "require": {"civicrm/composer-compile-plugin": "~0.14"},
  "extra": {
    "compile": [
      {"run": "@sh cd css; cat one.css two.css three.css > all.css"}
    ]
  }
}
```

Tasks may be defined in several ways, including:

* Shell command (`@sh cat file-{1,2,3} > big-file`)
* PHP method (`@php-method MyBuilder::build`)
* PHP eval (`@php-eval file_put_contents('big-file', make_big_file());`)
* PHP script file (`@php-script my-script.php`)
* Composer subcommand (`@composer dump-autoload`)

Features:

* Easy to enable. No manual configuration for downstream site-builders. Framework agnostic.
* Plays well with other `composer` tooling, like [forked repositories](https://matthewsetter.com/series/tooling/composer/forked-repositories/), [composer-patches](https://github.com/cweagans/composer-patches), [composer-locator](https://github.com/mindplay-dk/composer-locator), [composer-downloads](https://github.com/civicrm/composer-downloads-plugin), and the autoloader.
* Allows library repos to remain "clean" without committing build artifacts.
* Runs locally in PHP. Does not require external/hosted services or additional interpreters.
* Supports file monitoring for automatic rebuilds (`composer compile:watch`)
* Enforces permission model to address historical concerns about `composer` hooks and untrusted libraries.
* Integration-tests pass on both `composer` v1.10 and v2.0.

## Documentation

* [site-build.md](doc/site-build.md): Managing the root package (for site-builders)
* [tasks.md](doc/tasks.md): Working with tasks (for library developers)
* [evaluation.md](doc/evaluation.md): Evaluate and compare against similar options
* [develop.md](doc/develop.md): How to work with `composer-compile-plugin.git` (for plugin-development)

## See also

* [composer-compile-lib](https://github.com/civicrm/composer-compile-lib): Small library of opinionated helpers/examples for specific compilation tasks -- meta-PHP, SCSS, etc
* [composer#1193](https://github.com/composer/composer/issues/1193): Old discussion thread about post-install hooks for dependencies
