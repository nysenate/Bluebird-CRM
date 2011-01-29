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

sql_d="UPDATE permission SET perm='access CiviCRM, access CiviReport, access all custom data, edit groups, import contacts, profile listings, profile view, view all contacts, export print production files, administer site configuration' WHERE rid=7; UPDATE system SET status=1 WHERE name='nyss_io';"

sql_c="INSERT INTO civicrm_navigation VALUES ('', 1, 'BOE Import', 'BOE Import', 'importData', 'access CiviCRM,export print production files', 'AND', 201, 1, 0, 0), ('', 1, 'Site Maintenance', 'Site Maintenance', 'admin/settings/site-maintenance', 'access CiviCRM,export print production files', 'AND', 201, 1, 1, 0);"

for instance in $liveInstances; do
  echo "fixing: $instance"
  $execSql -i $instance -c "$sql_d" --drupal
  $execSql -i $instance -c "$sql_c"
  $clearCache $instance
done
