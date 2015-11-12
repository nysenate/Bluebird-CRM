#!/bin/sh
#
# dropCiviTriggers.sh
#
# drop CiviCRM triggers

# Project: BluebirdCRM
# Author: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2012-09-01
# Revised: 2015-11-12 - handle changelog summary/detail and shadow contact tabs
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh

if [ $# -ne 1 ]; then
  echo "Usage: $prog instance" >&2
  exit 1
fi

instance="$1"

. $script_dir/defaults.sh

dbname=`$execSql $instance --civicrm --get-db-name`

sql="
SELECT trigger_name
FROM information_schema.triggers
WHERE trigger_schema = '$dbname';"

triggers=`$execSql --no-db -c "$sql" -q`

echo "Removing all triggers from $dbname"
for trigger in $triggers; do
  $execSql $instance -c "DROP TRIGGER IF EXISTS $trigger" -q
done

exit $?
