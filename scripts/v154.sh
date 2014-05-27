#!/bin/sh
#
# v153.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2014-03-24
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

## run drupal upgrade
echo "7887: run drupal db upgrade..."
$drush $instance updb -y -q

#echo "upgrade CiviCRM core to v4.4.4..."
#$drush $instance civicrm-upgrade-db
