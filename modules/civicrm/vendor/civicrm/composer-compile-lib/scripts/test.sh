#!/usr/bin/env bash

## Determine the absolute path of the directory with the file
## usage: absdirname <file-path>
function absdirname() {
  pushd $(dirname $0) >> /dev/null
    pwd
  popd >> /dev/null
}

SCRIPTDIR=$(absdirname "$0")
PRJDIR=$(dirname "$SCRIPTDIR")
set -ex

PHPUNIT_VERSION=8.5.27
PHPUNIT_URL="https://phar.phpunit.de/phpunit-{$PHPUNIT_VERSION}.phar"
PHPUNIT_DIR="$PRJDIR/extern/phpunit-$PHPUNIT_VERSION"
PHPUNIT_BIN="$PHPUNIT_DIR/phpunit"
[ ! -f "$PHPUNIT_BIN" ] && ( mkdir -p "$PHPUNIT_DIR" ; curl -L "$PHPUNIT_URL" -o "$PHPUNIT_BIN" )

pushd "$PRJDIR" >> /dev/null
  composer install --prefer-dist --no-progress --no-suggest --no-dev --no-interaction
  php "$PHPUNIT_BIN"
popd >> /dev/null
