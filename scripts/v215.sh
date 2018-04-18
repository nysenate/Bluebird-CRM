#!/bin/sh
#
# v215.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2018-04-16
#

prog=`basename $0`
script_dir=`dirname $0`
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

## 11864
echo "$prog: upgrade Drupal"
$drush $instance updb -y -q

## record completion
echo "$prog: upgrade process is complete."
