#!/bin/sh
#
# v1576.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2015-12-28
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh

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

echo "$prog: 9784: increase length of various columns"
sql="
  ALTER TABLE civicrm_custom_group CHANGE title title VARCHAR(128);
  ALTER TABLE civicrm_custom_field DROP INDEX UI_label_custom_group_id;
  ALTER TABLE civicrm_custom_field CHANGE label label VARCHAR(1020);
  ALTER TABLE civicrm_custom_field ADD UNIQUE UI_label_custom_group_id (label(255), custom_group_id) USING BTREE;
"
$execSql $instance -c "$sql" -q
