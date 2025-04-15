#!/bin/bash
set -e

PHAR_CACHE="$HOME/.cache/phar"
PHAR_BIN="$HOME/.local/phar"
PATH="$PHAR_BIN:$PATH"

export PATH

##########################################################################
## Utilities

function phar_inst() {
  local prog="$1"
  local url="$2"
  local md5="$3"
  local cachefile="$PHAR_CACHE/$prog"
  local minsize=250000
  local binfile="$PHAR_BIN/$prog"

  mkdir -p "$PHAR_CACHE" "$PHAR_BIN"

  echo "[[ Setup PHAR: $prog ($url) ]]"

  if [ -f "$cachefile" ]; then
    if echo "$md5 $cachefile" | md5sum -c ; then
      echo "- Found cache: $cachefile"
    else
      echo "- Found bad cache: $cachefile"
      rm -f "$cachefile"
    fi
  fi

  if [ ! -f "$cachefile" ]; then
    echo "- Download to cache: $cachefile"
    curl -sSfL -o "$cachefile" "$url"
    chmod 755 "$cachefile"
  fi

  echo "- Install: $binfile"
  cp -f "$cachefile" "$binfile"

  local activefile=$( which $prog )
  echo "- Active file: $activefile"
}

##########################################################################
## Main

case "$1" in

  before_script)
    phar_inst phpunit https://phar.phpunit.de/phpunit-8.5.25.phar 6eacbbb16c3d3ba21223c4672b912e6a
    phar_inst composer https://getcomposer.org/download/1.10.13/composer.phar 56f13c034e5e0c58de35b77cbd0f1b0b

    echo "[[ Switch composer ]]"
    composer self-update $COMPOSER_VERSION

    echo "[[ Run 'composer install' ]]"
    composer install --no-progress --no-interaction
    ;;

  script)
    echo "[[ Run phpunit ]]"
    phpunit
    ;;

  all)
    "$0" before_script
    "$0" script
    ;;

  *)
    echo "usage: $0 before_script|script|all"
    ;;

esac
