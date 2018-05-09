#!/bin/sh
#
# v216.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2018-05-09
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

## 11935
echo "$prog: install changelogproofing extension"
$drush $instance cvapi extension.install key=gov.nysenate.changelogproofing --quiet

## record completion
echo "$prog: upgrade process is complete."
