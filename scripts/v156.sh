#!/bin/sh
#
# v156.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2015-03-03
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

echo "$prog: 7651: remove twitter dashlet"
sql="
  SELECT @dashid:=id FROM civicrm_dashboard WHERE name = 'twitter';
  DELETE FROM civicrm_dashboard_contact WHERE dashboard_id = @dashid;
  DELETE FROM civicrm_dashboard WHERE id = @dashid;
"
$execSql $instance -c "$sql" -q
