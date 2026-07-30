#!/bin/bash
#
# v3.10.3.sh
# Minor upgrade
# - NYSS #18386 - rebuilds database objects defined in shadow_func.sql
#
# Project: BluebirdCRM
# Authors: Nate Frank
# Organization: New York State Senate
# Date: 2026-06-10
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
echo "$prog: verifying BB_ADDR_REPLACE and BB_NORMALIZE_ADDR functions"
$execSql $instance -q -c "SELECT IF(BB_NORMALIZE('😊Main-Park') = 'mainpark', 'BB_NORMALIZE Verified','BB_NORMALIZE Failed');"
$execSql $instance -q -c "SELECT IF(BB_NORMALIZE_ADDR('90-b 😊Main Avenue') = '90 b main ave', 'BB_NORMALIZE_ADDR Verified','BB_NORMALIZE_ADDR Failed');"

## clear cache again
$clearCache $instance

echo "$prog: upgrade process is complete for $instance."
