#!/bin/sh
#
# v3_11_1.sh
#
# Project: BluebirdCRM
# Authors: Nate Frank
# Organization: New York State Senate
# Date: 2026-11-2
#

prog=`basename $0`
script_dir=`dirname $0`
drush=$script_dir/drush.sh
cv=$script_dir/cv.sh
clearCache=$script_dir/clearCache.sh
readConfig=$script_dir/readConfig.sh
php=/usr/bin/php

. $script_dir/defaults.sh

if [ $# -ne 1 ]; then
  echo "Usage: $prog instanceName" >&2
  exit 1
fi

instance="$1"

data_rootdir=`$readConfig --ig $instance data.rootdir` || data_rootdir="$DEFAULT_DATA_ROOTDIR"
pubfiles_dir="$data_rootdir/$instance/pubfiles"

if ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

## verify the Drupal themes symlink resolves after themes/ moved to bb4-transitional/themes
drupal_rootdir=`$readConfig --ig $instance drupal.rootdir` || drupal_rootdir="$DEFAULT_DRUPAL_ROOTDIR"
themes_link="$drupal_rootdir/sites/default/themes"

if [ -L "$themes_link" ] && [ -f "$themes_link/BluebirdSeven/BluebirdSeven.info" ]; then
  echo "$prog: themes symlink OK: $themes_link -> `readlink "$themes_link"`"
else
  echo "$prog: ERROR: themes symlink broken or missing: $themes_link -> `readlink "$themes_link"`" >&2
  exit 1
fi

new_hash_salt=`php -r 'echo rtrim(strtr(base64_encode(random_bytes(32)), "+/", "-_"), "="), PHP_EOL;'`

cat <<EOF


The Drupal hash salt has been compromised.
It was hard-coded in drupal/sites/default/settings.php.

Please take the following steps to create a new Drupal hash salt in bluebird.cfg

1. Verify that site.drupal_hash_salt is not already defined in bluebird.cfg.
2. Copy and paste the following into bluebird.cfg global section

site.drupal_hash_salt = $new_hash_salt

EOF

old_settings=$(stty -g)
stty -icanon -echo

printf "When you're done updating bluebird.cfg, press any key to continue..."

# 2. Read exactly one byte from standard input
dd bs=1 count=1 >/dev/null 2>&1

# 3. Restore original terminal settings and print a newline
stty "$old_settings"
echo ""

## clear cache again
$clearCache $instance

echo "$prog: upgrade process is complete for $instance."
