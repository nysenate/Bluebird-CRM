#!/bin/sh
#
# gen_drupal_hash_salt.sh
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2026-11-13
#

prog=`basename $0`

# Drupal 7 (7.24 to 7.103) uses 32 random bytes for the hash salt, so we
# default to 32.  Drupal 8, 9, 10, and 11 use 55 random bytes.
DEFAULT_COUNT=32

usage() {
  echo "Usage: $prog [count]" >&2
}

if [ $# -gt 1 ]; then
  usage
  exit 1
fi

case "$1" in
  --help) usage; exit 0 ;;
  -*) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
  *[!0-9]*) echo "$prog: $1: [count] must be a number" >&2; usage; exit 1 ;;
  "") count="$DEFAULT_COUNT" ;;
  *) count="$1" ;;
esac

php -r "echo str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(random_bytes($count))), PHP_EOL;"

exit 0
