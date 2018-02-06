#!/bin/sh
#
# v213a.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2018-02-05
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

## 11728 matched messages
echo "$prog: add row_id to messages_matched table"
sql="
  ALTER TABLE nyss_inbox_messages_matched ADD row_id INT(10) NOT NULL AFTER id, ADD INDEX (row_id);
"
$execSql $instance -c "$sql" -q

## record completion
echo "$prog: upgrade process is complete."
