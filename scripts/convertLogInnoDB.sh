#!/bin/sh
#
# convertLogInnoDB.sh - Convert certain logging tables to compressed InnoDB.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2013-04-01
# Revised: 2013-04-15
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh

INNO_NAMES="address contact dashboard_contact email entity_tag group group_contact note phone relationship value_constituent_information_1 value_district_information_7"

if [ $# -ne 1 ]; then
  echo "Usage: $prog instance" >&2
  exit 1
fi

instance="$1"

. $script_dir/defaults.sh

db_basename=`$readConfig --ig $instance db.basename` || db_basename="$instance"
log_db_prefix=`$readConfig --ig $instance db.log.prefix` || log_db_prefix="$DEFAULT_BASE_DOMAIN"
ldb="$log_db_prefix$db_basename"

inno_tabs=
for t in $INNO_NAMES; do
  if [ "$inno_tabs" ]; then
    inno_tabs="$inno_tabs, 'log_civicrm_$t'"
  else
    inno_tabs="'log_civicrm_$t'"
  fi
done

sql="
SELECT table_name
FROM information_schema.tables
WHERE table_schema = '$ldb'
  AND engine <> 'InnoDB'
  AND table_name in ( $inno_tabs );"
tbls=`$execSql -c "$sql" -q`

if [ "$tbls" ]; then
  echo "Altering certain non-InnoDB log tables to compressed InnoDB for $instance..."
  for tbl in $tbls; do
    echo "table: $tbl"
    sql="ALTER TABLE $tbl ENGINE=InnoDB ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=4;"
    $execSql -i $instance -c "$sql" -q --log
  done
  echo "Log table engine alteration complete."
else
  echo "There are no log tables that require conversion to InnoDB."
fi

sql="
SELECT table_name
FROM information_schema.tables
WHERE table_schema='$ldb'
  AND engine <> 'Archive'
  AND table_name not in ( $inno_tabs );"
tbls=`$execSql -c "$sql" -q`

if [ "$tbls" ]; then
  echo "Warning: The following tables should be in ARCHIVE format, but are not:"
  echo "$tbls"
fi

exit 0
