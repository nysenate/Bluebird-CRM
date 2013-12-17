#!/bin/sh
#
# v151.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2013-12-17
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

app_rootdir=`$readConfig --ig $instance app.rootdir` || app_rootdir="$DEFAULT_APP_ROOTDIR"

## 7495
echo "Examining option_value table for Chief prefix..."
sql="
  SELECT count(*)
  FROM civicrm_option_value
  WHERE name LIKE 'Chief'
    AND option_group_id = (SELECT id FROM civicrm_option_group WHERE name = 'individual_prefix')
"
cnt=`$execSql $instance -c "$sql" -q`

if [ $cnt -eq 0 ]; then
  echo "Adding Chief prefix to individual_prefix right after Chancellor"
  sql="
SELECT @optgrp:=id FROM civicrm_option_group WHERE name='individual_prefix';
SELECT @maxval:=max(cast(value as unsigned)) FROM civicrm_option_value WHERE option_group_id = @optgrp;
SELECT @wght:=weight FROM civicrm_option_value WHERE option_group_id = @optgrp AND name = 'Chancellor';
UPDATE civicrm_option_value SET weight = weight + 1 WHERE option_group_id = @optgrp AND weight > @wght;
INSERT INTO civicrm_option_value (option_group_id, label, value, name, weight, is_active)
VALUES (@optgrp, 'Chief', @maxval+1, 'Chief', @wght+1, 1);
"
  $execSql $instance -c "$sql" -q
elif [ $cnt -eq 1 ]; then
  echo "The Chief prefix is already available"
else
  echo "ERROR: Found $cnt Chief prefixes, but we are expecting 0 or 1; this must be manually fixed"
fi

echo "Examining option_value table for Ret. suffix..."
sql="
  SELECT count(*)
  FROM civicrm_option_value
  WHERE name LIKE 'Ret.%'
    AND option_group_id = (SELECT id FROM civicrm_option_group WHERE name = 'individual_suffix')
"
cnt=`$execSql $instance -c "$sql" -q`

if [ $cnt -eq 0 ]; then
  echo "Adding Ret. suffix to individual_suffix"
  sql="
SELECT @optgrp:=id FROM civicrm_option_group WHERE name='individual_suffix';
SELECT @maxval:=max(cast(value as unsigned)) FROM civicrm_option_value WHERE option_group_id = @optgrp;
SELECT @wght:=max(cast(weight as unsigned)) FROM civicrm_option_value WHERE option_group_id = @optgrp;
INSERT INTO civicrm_option_value (option_group_id, label, value, name, weight, is_active)
VALUES (@optgrp, 'Ret.', @maxval+1, 'Chief', @wght+1, 1);
"
  $execSql $instance -c "$sql" -q
elif [ $cnt -eq 1 ]; then
  echo "The Ret. suffix is already available"
else
  echo "ERROR: Found $cnt Ret. suffixes, but we are expecting 0 or 1; this must be manually fixed"
fi
