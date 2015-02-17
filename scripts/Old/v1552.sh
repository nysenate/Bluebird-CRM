#!/bin/sh
#
# v1552.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2014-12-16
# Revised: 2015-01-29
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

echo "$prog: 8379: fixing duplicate activity type values"
sql="
  SELECT @optgrp:=id FROM civicrm_option_group WHERE name = 'activity_type';
  SELECT @maxval:=max(cast(value as unsigned)) FROM civicrm_option_value WHERE option_group_id = @optgrp;
  UPDATE civicrm_option_value
    SET value = @maxval+1
    WHERE option_group_id = @optgrp
      AND name = 'Pledge Acknowledgment';
"
$execSql $instance -c "$sql" -q
