#!/bin/sh
#
# v227.sh
#
# Project: BluebirdCRM
# Authors: Ken Zalewski
# Organization: New York State Senate
# Date: 2019-12-05
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
drush=$script_dir/drush.sh
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

echo "$prog: Starting v2.2.7 upgrade process"

sql="DELETE FROM system WHERE type='module' AND name='nyss_massmerge';"
$execSql $instance -c "$sql" -q

## record completion
echo "$prog: Finished the v2.2.7 upgrade process"
