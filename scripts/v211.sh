#!/bin/sh
#
# v211.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2018-01-11
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

## 11653 cleanup unmatched records
echo "$prog: cleanup orphaned match records"
sql="
  DELETE FROM nyss_inbox_messages_matched
  WHERE matched_id = 0;
"
$execSql $instance -c "$sql" -q

## record completion
echo "$prog: upgrade process is complete."
