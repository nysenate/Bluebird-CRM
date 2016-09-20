#!/bin/sh
#
# changeCollation.sh
#
# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2011-10-14
# Updated: 2016-09-20
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh

if [ $# -lt 1 ]; then
  echo "Usage: $prog instanceName" >&2
  exit 1
fi

instance="$1"
collation="utf8_unicode_ci"

if [ ! "$instance" ]; then
  echo "$prog: Must specify an instance to manage" >&2
  usage
  exit 1
elif ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

. $script_dir/defaults.sh

db_basename=`$readConfig --ig $instance db.basename` || db_basename="$instance"
civi_db_prefix=`$readConfig --ig $instance db.civicrm.prefix` || civi_db_prefix="$DEFAULT_BASE_DOMAIN"
log_db_prefix=`$readConfig --ig $instance db.log.prefix` || log_db_prefix="$DEFAULT_BASE_DOMAIN"
cdb="$civi_db_prefix$db_basename"
ldb="$log_db_prefix$db_basename"

sql="
SELECT table_name
FROM information_schema.tables
WHERE table_schema = '$cdb'
  AND table_collation <> 'utf8_unicode_ci';"

tbls=`$execSql -q $instance -c "$sql"`

if [ "$tbls" ]; then
  echo "Altering table collation to utf8_unicode_ci for $instance..."
  for tbl in $tbls; do
    echo "table: $tbl"
    sql="ALTER TABLE $tbl CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;"
    $execSql $instance -c "$sql" -q
  done
  echo "Table collation alteration complete."
else
  echo "There are no tables that require conversion to utf8_unicode_ci."
fi

exit 0
