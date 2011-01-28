#!/bin/sh
#
# v112_removeDedupe.sh
#
# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2011-01-11
#
# disable mass dedupe/merge menu option
# clear relevant caches
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
liveInstances=`$script_dir/iterateInstances.sh --live`

. $script_dir/defaults.sh

sql="UPDATE civicrm_navigation SET is_active=0 WHERE name='Merge Duplicate Contacts'; truncate civicrm_menu; UPDATE civicrm_preferences SET navigation=NULL;"

for instance in $liveInstances; do
  echo "fixing: $instance"
  $execSql -i $instance -c "$sql"
done
