#!/bin/sh
#
# v123_img.sh
#
# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2011-07-20
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

###### Begin Upgrade Scripts ######

### CiviCRM ###

## update navigation items
imgUrl="UPDATE civicrm_option_value SET value = 'images/' WHERE name = 'imageUploadDir';
UPDATE civicrm_option_value SET value = 'images/' WHERE name = 'imageUploadURL';"
$execSql -i $instance -c "$imgUrl"



