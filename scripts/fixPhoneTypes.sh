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

if [ $# -ne 1 ]; then
  echo "Usage: $prog instance" >&2
  exit 1
fi

instance="$1"

. $script_dir/defaults.sh

sql="UPDATE civicrm_phone SET phone_type_id=1 WHERE phone_type_id=246; UPDATE civicrm_phone SET phone_type_id=2 WHERE phone_type_id=247; UPDATE civicrm_phone SET phone_type_id=3 WHERE phone_type_id=248;"

echo "Fixing phone types for instance [$instance]"
$execSql $instance -c "$sql"

