#!/bin/bash
#
# v3.10.4.sh
# Minor upgrade
# - NYSS #18119 - add 'access caseload dashboard' permission to roles
#
# Project: BluebirdCRM
# Authors: Nate Frank
# Organization: New York State Senate
# Date: 2026-06-29
#

prog=`basename $0`
script_dir=`dirname $0`
drush=$script_dir/drush.sh
cv=$script_dir/cv.sh
clearCache=$script_dir/clearCache.sh
readConfig=$script_dir/readConfig.sh
execSql=$script_dir/execSql.sh
resetRoles=$script_dir/resetRolePerms.sh

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

## uninstall angularprofiles extension -- See NYSS #18926
$cv $instance ext:uninstall org.civicrm.angularprofiles

## install nyss_caseload_dash extension -- NYSS #18119
$cv $instance ext:enable nyss_caseload_dash

## Adds perms for caseload dashboard -- NYSS #18119
$resetRoles $instance

## clear cache again
$clearCache $instance

echo "$prog: upgrade process is complete for $instance."
