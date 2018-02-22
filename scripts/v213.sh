#!/bin/sh
#
# v211.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2018-02-02
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

## 11738 restructure modified date search
echo "$prog: restructure modified date search"
$drush $instance cvapi extension.install key=gov.nysenate.modifieddate --quiet
php $app_rootdir/civicrm/scripts/rebuildTriggers.php -S $instance

## record completion
echo "$prog: upgrade process is complete."
