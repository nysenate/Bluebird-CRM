#!/bin/sh
#
# v3502.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2022-10-18
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

echo "#14958 remove mailing viewer role..."
sql="
  SELECT @viewer:= rid FROM role WHERE name = 'Mailing Viewer';
  DELETE FROM users_roles WHERE rid = @viewer;
  DELETE FROM role_permission WHERE rid = @viewer;
  DELETE FROM role WHERE name = 'Mailing Viewer';
"
$execSql $instance -c "$sql" -q -D

## record completion
echo "$prog: upgrade process is complete."
