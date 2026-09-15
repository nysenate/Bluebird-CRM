#!/bin/bash
#
# v3_10_3_fix_18887.sh
# Hot fix
# - NYSS #18887 - removes emoji checks from shadow_func.sql
#
# Project: BluebirdCRM
# Authors: Nate Frank
# Organization: New York State Senate
# Date: 2026-07-03
#

prog=`basename $0`
script_dir=`dirname $0`
drush=$script_dir/drush.sh
cv=$script_dir/cv.sh
clearCache=$script_dir/clearCache.sh
readConfig=$script_dir/readConfig.sh
execSql=$script_dir/execSql.sh

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
echo "$prog: rebuild shadow table functions"
$execSql $instance -f $script_dir/../civicrm/custom/ext/gov.nysenate.dedupe/sql/shadow_func.sql

## rebuild triggers
echo "$prog: rebuild triggers"
php $app_rootdir/civicrm/scripts/rebuildTriggers.php -S $instance

## verify that the SQL functions work
echo "$prog: verifying BB_NORMALIZE and BB_NORMALIZE_ADDR functions"
$execSql $instance -q -c "SELECT IF(BB_NORMALIZE('😊Main-Park') = 'mainpark', 'BB_NORMALIZE with Emoji Failed','BB_NORMALIZE with Emoji Verified');"
$execSql $instance -q -c "SELECT IF(BB_NORMALIZE('Main-Park') = 'mainpark', 'BB_NORMALIZE without Emoji Verified','BB_NORMALIZE without Emoji Failed');"
$execSql $instance -q -c "SELECT IF(BB_NORMALIZE_ADDR('90-b 😊Main Avenue') = '90 b main ave', 'BB_NORMALIZE_ADDR with Emoji Failed','BB_NORMALIZE_ADDR with Emoji Verified');"
$execSql $instance -q -c "SELECT IF(BB_NORMALIZE_ADDR('90-b Main Avenue') = '90 b main ave', 'BB_NORMALIZE_ADDR without Emoji Verified','BB_NORMALIZE_ADDR without Emoji Failed');"
## clear cache again
$clearCache $instance

echo "$prog: fix is complete for $instance."
