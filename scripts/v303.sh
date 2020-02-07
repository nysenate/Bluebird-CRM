#!/bin/sh
#
# v303.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2020-01-28
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

echo "$prog: #13255 disable access control group type"
sql="
  UPDATE civicrm_option_value
  SET is_active = 0
  WHERE name LIKE 'Access Control';
"
$execSql $instance -c "$sql"

echo "$prog: #13292 remove print prod from role assign"
sql="
  UPDATE variable
  SET value = 0x613a31373a7b693a383b733a313a2238223b693a353b733a313a2235223b693a31323b733a323a223132223b693a31363b733a323a223136223b693a31343b733a323a223134223b693a31353b733a323a223135223b693a31373b733a323a223137223b693a31393b733a323a223139223b693a393b733a313a2239223b693a31303b733a323a223130223b693a363b733a313a2236223b693a31313b733a323a223131223b693a31333b733a323a223133223b693a343b693a303b693a373b693a303b693a31383b693a303b693a333b693a303b7d
  WHERE name = 'roleassign_roles';
"
$execSql -i $instance -c "$sql" --drupal

## record completion
echo "$prog: upgrade process is complete."
