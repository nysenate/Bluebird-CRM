#!/bin/sh
#
# v142a.sh - add "Mayor" as a prefix
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2013-10-22
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

## 4298
echo "Examining option_value table for Mayor prefix..."
sql="select count(*) from civicrm_option_value where name like 'Mayor%' and option_group_id=(select id from civicrm_option_group where name='individual_prefix')"
cnt=`$execSql $instance -c "$sql" -q`

if [ $cnt -eq 0 ]; then
  echo "Adding Mayor prefix to individual_prefix right after Major General"
  sql="
SELECT @optgrp:=id FROM civicrm_option_group WHERE name='individual_prefix';
SELECT @maxval:=max(cast(value as unsigned)) FROM civicrm_option_value WHERE option_group_id = @optgrp;
SELECT @wght:=weight FROM civicrm_option_value WHERE option_group_id = @optgrp AND name = 'Major General';
UPDATE civicrm_option_value SET weight = weight + 1 WHERE option_group_id = @optgrp AND weight > @wght;
INSERT INTO civicrm_option_value (option_group_id, label, value, name, weight, is_active)
VALUES (@optgrp, 'Mayor', @maxval+1, 'Mayor', @wght+1, 1);
"
  $execSql $instance -c "$sql" -q
  exit $?
elif [ $cnt -eq 1 ]; then
  echo "The Mayor prefix is already available"
  exit 0
else
  echo "ERROR: Found $cnt Mayor prefixes, but we are expecting 0 or 1; this must be manually fixed"
  exit 1
fi

