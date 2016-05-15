#!/bin/sh
#
# v122_dashcache.sh
#
# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2011-07-02
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

###### Begin Update Scripts ######

### CiviCRM ###

## alter dashboard tables
dashcache="ALTER TABLE civicrm_dashboard DROP content, DROP created_date;
ALTER TABLE civicrm_dashboard_contact  ADD content TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER weight,  ADD created_date DATETIME NULL DEFAULT NULL AFTER content;"
$execSql -i $instance -c "$dashcache"



### Cleanup ###

$script_dir/fixPermissions.sh
$script_dir/clearCache.sh $instance
