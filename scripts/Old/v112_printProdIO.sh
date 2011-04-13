#!/bin/sh
#
# v112_printProd.sh
#
# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2011-01-11
#
# enable nyss_io module
# give print production role the 'administer site configuration' permission
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
liveInstances=`$script_dir/iterateInstances.sh --live`
clearCache=$script_dir/clearCache.sh
drush=$script_dir/drush.sh

. $script_dir/defaults.sh

for instance in $liveInstances; do
  echo "disabling/enabling nyss_io for: $instance"
  $drush $instance dis nyss_io -y
  $drush $instance en nyss_io -y
done
