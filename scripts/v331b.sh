#!/bin/sh
#
# v333.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2022-01-01
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

data_rootdir=`$readConfig --ig $instance data.rootdir` || data_rootdir="$DEFAULT_DATA_ROOTDIR"
pubfiles_dir="$data_rootdir/$instance/pubfiles"

if ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

## begin upgrade steps

echo "$prog: enable recalculate recipients extension"
$drush $instance cvapi extension.install key=civicrm-recalculate-recipients --quiet

## record completion
echo "$prog: upgrade process is complete."
