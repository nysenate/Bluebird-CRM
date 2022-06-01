#!/bin/sh
#
# convertUtf8mb4.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2022-05-31
# scripts/iterateInstances.sh --all "scripts/convertUtf8mb4.sh {}"
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

dbciviprefix=`$readConfig --ig $instance db.civicrm.prefix` || dbciviprefix="$DEFAULT_DB_CIVICRM_PREFIX"
dblogprefix=`$readConfig --ig $instance db.log.prefix` || dblogprefix="$DEFAULT_DB_LOG_PREFIX"
dbbasename=`$readConfig -i $instance db.basename` || dbbasename="$instance"
dbcivi=$dbciviprefix$dbbasename
dblog=$dblogprefix$dbbasename

echo "Converting CiviCRM and Logging tables to utf8mb4 format..."
echo "processing $dbcivi..."
$drush $instance cvapi system.utf8conversion patterns="civicrm_%,address_%,fn_%,nyss_%,shadow_%" databases=$dbcivi --quiet
echo "processing $dblog..."
$drush $instance cvapi system.utf8conversion patterns="log_civicrm_%" databases=$dblog --quiet

## record completion
echo "$prog: UTF8 MB4 conversion complete."
