#!/bin/sh
#
# v305.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2020-04-06
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

echo "$prog: enable new boe extension"
$drush $instance cvapi extension.install key=gov.nysenate.boe --quiet

echo "$prog: disable old boe modules"
$drush $instance pm-disable nyss_boe -y

## record completion
echo "$prog: upgrade process is complete."
