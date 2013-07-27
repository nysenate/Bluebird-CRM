#!/bin/sh
#
# convertLogInnoDB.sh - Convert logging tables to compressed InnoDB.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2013-04-01
# Revised: 2013-04-15
# Revised: 2013-07-27 - Convert ALL logging tables, not just 17 of them
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh

# The 17 special tables that were previously the only ones converted
# to the InnoDB engine.  Now, all logging tables are converted.
INNO_NAMES="address contact dashboard_contact email entity_tag group group_contact note phone relationship value_constituent_information_1 value_district_information_7 activity activity_assignment activity_target job subscription_history"

if [ $# -ne 1 ]; then
  echo "Usage: $prog instance" >&2
  exit 1
fi

instance="$1"

. $script_dir/defaults.sh

db_basename=`$readConfig --ig $instance db.basename` || db_basename="$instance"
log_db_prefix=`$readConfig --ig $instance db.log.prefix` || log_db_prefix="$DEFAULT_BASE_DOMAIN"
ldb="$log_db_prefix$db_basename"

sql="
SELECT table_name
FROM information_schema.tables
WHERE table_schema = '$ldb'
  AND engine <> 'InnoDB'
  AND table_name like 'log_civicrm_%';"

tbls=`$execSql -c "$sql" -q`

if [ "$tbls" ]; then
  echo "Altering non-InnoDB log tables to compressed InnoDB for $instance..."
  for tbl in $tbls; do
    echo "table: $tbl"
    sql="ALTER TABLE $tbl ENGINE=InnoDB ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=4;"
    $execSql $instance -c "$sql" -q --log
  done
  echo "Log table engine alteration complete."
else
  echo "There are no log tables that require conversion to InnoDB."
fi

exit 0
