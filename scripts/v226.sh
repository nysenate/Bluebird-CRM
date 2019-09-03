#!/bin/sh
#
# v226.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2019-08-26
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

echo "$prog: Starting v2.2.6 upgrade process"

## 12639
echo "$prog: nyss #12871 - alter fn_group.given"
sql="
  ALTER TABLE fn_group
  MODIFY COLUMN given VARCHAR(64);
"
$execSql $instance -c "$sql" -q

## 12878
echo "$prog: nyss #12878 - disable merge module; enable merge extension;"
$drush $instance pm-disable nyss_massmerge -y
$drush $instance pm-uninstall nyss_massmerge -y
$drush $instance cvapi extension.install key=gov.nysenate.merge --quiet

## record completion
echo "$prog: Finished the v2.2.6 upgrade process"
