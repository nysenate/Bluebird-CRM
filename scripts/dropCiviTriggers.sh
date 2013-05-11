#!/bin/sh
#
# dropCiviTriggers.sh
#
# drop CiviCRM triggers

# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2012-09-01
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

db_basename=`$readConfig --ig $instance db.basename` || db_basename="$instance"
log_db_prefix=`$readConfig --ig $instance db.log.prefix` || log_db_prefix="$DEFAULT_BASE_DOMAIN"
civi_db_prefix=`$readConfig --ig $instance db.civicrm.prefix` || civi_db_prefix="$DEFAULT_BASE_DOMAIN"
cdb="$civi_db_prefix$db_basename"

triggersql="
SELECT trigger_name
FROM information_schema.triggers
WHERE trigger_schema = '$cdb'
AND trigger_name LIKE 'civicrm_%';"
triggers=`$execSql -c "$triggersql" -q`

echo "removing triggers..."
for trigger in $triggers; do
  $execSql -i $instance -c "DROP TRIGGER IF EXISTS $trigger" -q
done
