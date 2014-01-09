#!/bin/sh
#
# v152.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2014-01-09
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

echo "cleaning up contact subrecords..."
sql="
  DELETE FROM civicrm_email WHERE contact_id IS NULL AND id != 1;
  DELETE FROM civicrm_address WHERE contact_id IS NULL;
  DELETE FROM civicrm_phone WHERE contact_id IS NULL;
  DELETE FROM civicrm_im WHERE contact_id IS NULL;
  DELETE FROM civicrm_website WHERE contact_id IS NULL;
"
$execSql $instance -c "$sql" -q
