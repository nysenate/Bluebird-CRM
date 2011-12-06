#!/bin/sh
#
# fixEmptyPrefix.sh
# 
# If prefix is empty and gender is set, set prefix to Mr/Ms
# Run greeting/addressee recache
#
# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2011-12-06
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
app_rootdir=`$readConfig --global app.rootdir` || app_rootdir="$DEFAULT_APP_ROOTDIR"

if [ $# -ne 1 ]; then
  echo "Usage: $prog instance" >&2
  exit 1
fi

instance="$1"

. $script_dir/defaults.sh

# set prefix and gender constants
prf_m="3"
prf_f="2"
gen_m="2"
gen_f="1"

setprefix="
UPDATE civicrm_contact
SET prefix_id = CASE gender_id WHEN $gen_m THEN $prf_m WHEN $gen_f THEN $prf_f END
WHERE prefix_id IS NULL AND gender_id IS NOT NULL;"

echo "Setting the prefix based on gender if not currently set for: [$instance]"
$execSql -i $instance -c "$setprefix"

php $app_rootdir/civicrm/scripts/updateAllGreetings.php -S $instance -f

$script_dir/rebuildCachedValues.sh $instance --field-displayname --rebuild-all --ok
