#!/bin/sh
#
# v3_8_2.sh
# Replaces code dependent on lib_mysqludf_preg UDF libraries with built-in MySQL RegExp functions
#
# Project: BluebirdCRM
# Authors: Nathan Frank
# Organization: New York State Senate
# Date: 2025-04-14
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
drush=$script_dir/drush.sh

. $script_dir/defaults.sh

if [ $# -ne 1 ]; then
  echo "Usage: $prog instanceName" >&2
  exit 1
fi

instance="$1"

if ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

app_rootdir=`$readConfig --ig $instance app.rootdir` || app_rootdir="$DEFAULT_APP_ROOTDIR"

## rebuild shadow table functions
echo "$prog: rebuild shadow table functions\n"
$execSql $instance -f $script_dir/../civicrm/custom/ext/gov.nysenate.dedupe/sql/shadow_func.sql

## rebuild triggers
echo "$prog: rebuild triggers\n"
php $app_rootdir/civicrm/scripts/rebuildTriggers.php -S $instance

$drush $instance cc all -y

## verify that the SQL functions work
echo "\n$prog: verifying BB_ADDR_REPLACE and BB_NORMALIZE_ADDR functions\n"
$execSql $instance -c "SELECT IF(BB_ADDR_REPLACE('90 Main Parkway') = '90 main pkwy', 'BB_ADDR_REPLACE Verified','BB_ADDR_REPLACE Failed');"
$execSql $instance -c "SELECT IF(BB_NORMALIZE_ADDR('90-b Main Avenue') = '90 b main ave', 'BB_NORMALIZE_ADDR Verified','BB_NORMALIZE_ADDR Failed');"
echo "\n"
## record completion
echo "$prog: upgrade process is complete.\n"
