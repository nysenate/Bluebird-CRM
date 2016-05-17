#!/bin/sh
#
# v138.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2012-07-10
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

### Drupal ###


### CiviCRM ###

## 5442 changelog report permission
changeRpt="
UPDATE civicrm_report_instance
SET permission = 'access CiviCRM'
WHERE report_id = 'logging/contact/detail' OR report_id = 'logging/contact/summary';
"
$execSql -i $instance -c "$changeRpt"


### Cleanup ###

$script_dir/clearCache.sh $instance
