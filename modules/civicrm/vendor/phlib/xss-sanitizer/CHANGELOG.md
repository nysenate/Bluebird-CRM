# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

## [2.1.0] - 2024-06-08
### Added
- Optional constructor params to specify extra tags or attributes to be removed.
  HT @EmanuelAugusto

## [2.0.2] - 2023-08-01
### Removed
- Remove test dependency asset files, and remove test files from dist package.

## [2.0.1] - 2022-06-08
### Fixed
- Attribute cleaner error when filtered tag has empty content.

## [2.0.0] - 2021-08-17
### Added
- Add specific support for PHP v8.
- Type declarations have been added to all properties, method parameters and
  return types where possible.
- Add explicit dependency for `mbstring` PHP extension. This has always been
  required, but not previously listed in the platform dependencies.
### Changed
- Use SemVer for dependency versions.
- **BC break**: Reduce visibility of internal methods and properties. These
  members are not part of the public API. No impact to standard use of this
  package. If an implementation has a use case which needs to override these
  members, please submit a pull request explaining the change.
### Removed
- **BC break**: Removed support for PHP versions <= v7.3 as they are no longer
  [actively supported](https://php.net/supported-versions.php) by the PHP project.

## [1.1.1] - 2022-09-06
### Fixed
- Attribute cleaner error when filtered tag has empty content.

## [1.1.0] - 2016-11-01
- Adds a method for sanitizing an array of strings.

## [1.0.0] - 2016-03-18
- Reduce regex backtracking.
