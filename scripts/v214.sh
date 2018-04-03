#!/bin/sh
#
# v214.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2018-03-24
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

app_rootdir=`$readConfig --ig $instance app.rootdir` || app_rootdir="$DEFAULT_APP_ROOTDIR"

## 11700
echo "$prog: implement new export extension"
$drush $instance dis nyss_export
$drush $instance cvapi extension.install key=gov.nysenate.export --quiet

## 11273
echo "$prog: 11273 restore website tags"
php $script_dir/../civicrm/scripts/11273_restoreWebsiteTags.php -S $instance

## record completion
echo "$prog: upgrade process is complete."
