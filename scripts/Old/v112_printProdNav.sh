#!/bin/sh
#
# v112_printProd.sh
#
# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2011-01-11
#
# enable nyss_io module
# give print production role the 'administer site configuration' permission
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
liveInstances=`$script_dir/iterateInstances.sh --live`
clearCache=$script_dir/clearCache.sh

. $script_dir/defaults.sh

sql_c="UPDATE civicrm_navigation SET permission = 'access CiviCRM,administer site configuration' WHERE name = 'Site Maintenance'; UPDATE civicrm_preferences SET navigation = null;"

for instance in $liveInstances; do
  echo "fixing: $instance"
  $execSql -i $instance -c "$sql_c"
done
