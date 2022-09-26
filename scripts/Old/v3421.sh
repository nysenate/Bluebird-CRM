#!/bin/sh
#
# v3421.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2022-09-09
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

echo "set user_protect admin override"
sql="
  DELETE FROM userprotect WHERE up_type = 'admin' AND uid = 1;
  INSERT INTO userprotect (uid, up_name, up_mail, up_pass, up_status, up_roles, up_cancel, up_edit, up_type, up_openid)
  VALUES (1, 1, 1, 1, 1, 1, 1, 1, 'admin', 1);
"
$execSql $instance -c "$sql" -q -D

## record completion
echo "$prog: upgrade process is complete."
