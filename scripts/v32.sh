#!/bin/sh
#
# v32.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2020-12-28
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

echo "reinstall flexmailer from core package..."
$drush $instance cvapi extension.disable key=uk.co.vedaconsulting.mosaico --quiet
$drush $instance cvapi extension.disable key=org.civicrm.flexmailer --quiet
$drush $instance cvapi extension.uninstall key=org.civicrm.flexmailer --quiet
$drush $instance cvapi extension.install key=org.civicrm.flexmailer --quiet
$drush $instance cvapi extension.enable key=uk.co.vedaconsulting.mosaico --quiet

echo "upgrade extensions..."
$drush $instance cvapi extension.upgrade --quiet

## upgrade drupal db
echo "running drupal db upgrade..."
$drush $instance updb -y -q

## upgrade civicrm db
echo "running civicrm db upgrade..."
$drush $instance civicrm-upgrade-db -y -q

## record completion
echo "$prog: upgrade process is complete."