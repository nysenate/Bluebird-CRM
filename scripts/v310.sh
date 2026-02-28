#!/bin/sh
#
# v39.sh
# Upgrade Drupal Core to version 6.4.0
#
# Project: BluebirdCRM
# Authors: Nate Frank
# Organization: New York State Senate
# Date: 2025-08-15
#

prog=`basename $0`
script_dir=`dirname $0`
drush=$script_dir/drush.sh
cv=$script_dir/cv.sh
clearCache=$script_dir/clearCache.sh
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

## clear cache first
$clearCache $instance

## upgrade civicrm db
echo "running civicrm db upgrade and extension upgrades..."
$cv $instance upgrade:db -n -q

## enable nyss_print_export
echo "enabling extension nyss_print_export"
$cv $instance en nyss_print_export -n -q

## clear cache again
$clearCache $instance

echo "$prog: upgrade process is complete for $instance."
