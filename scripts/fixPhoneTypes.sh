#!/bin/sh
#
# fixPhoneTypes.sh - correct phone types assigned during OMIS import
#
# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2011-03-01
# Revised: 2011-03-01
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
liveInstances=`$script_dir/iterateInstances.sh --live`

. $script_dir/defaults.sh

sql="UPDATE civicrm_phone SET phone_type_id=1 WHERE phone_type_id=246; UPDATE civicrm_phone SET phone_type_id=2 WHERE phone_type_id=247; UPDATE civicrm_phone SET phone_type_id=3 WHERE phone_type_id=248;"

for instance in $liveInstances; do
  echo "fixing: $instance"
  $execSql -i $instance -c "$sql"
done

