#!/bin/sh
#
# convertLogInnoDB.sh
# convert current MyISAM log tables to compressed InnoDB format
#

# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2013-04-01
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

data_rootdir=`$readConfig --ig $instance data.rootdir` || data_rootdir="$DEFAULT_DATA_ROOTDIR"
app_rootdir=`$readConfig --ig $instance app.rootdir` || app_rootdir="$DEFAULT_APP_ROOTDIR"
webdir=`$readConfig --global drupal.rootdir` || webdir="$DEFAULT_DRUPAL_ROOTDIR"
base_domain=`$readConfig --ig $instance base.domain` || base_domain="$DEFAULT_BASE_DOMAIN"
db_basename=`$readConfig --ig $instance db.basename` || db_basename="$instance"
log_db_prefix=`$readConfig --ig $instance db.log.prefix` || log_db_prefix="$DEFAULT_BASE_DOMAIN"
civi_db_prefix=`$readConfig --ig $instance db.civicrm.prefix` || civi_db_prefix="$DEFAULT_BASE_DOMAIN"
cdb="$civi_db_prefix$db_basename"
ldb="$log_db_prefix$db_basename"

sql="
SELECT table_name
FROM information_schema.tables
WHERE table_schema = '$ldb'
  AND ( engine = 'MyISAM' OR table_name = 'log_civicrm_group' OR table_name = 'log_civicrm_dashboard_contact' );"
tbls=`$execSql -c "$sql" -q`

sfx="_tmp"

echo "altering MyISAM log tables for $instance..."
for tbl in $tbls; do
  echo "table: $tbl"
  tmp="$tbl$sfx"
  sql="
    CREATE TABLE $tmp LIKE $tbl;
    ALTER TABLE $tmp ENGINE InnoDB ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=4;
    INSERT INTO $tmp SELECT * FROM $tbl;
    DROP TABLE $tbl;
    RENAME TABLE $tmp to $tbl;
  "
  $execSql -i $instance -c "$sql" -q --log
done

echo "log table engine alteration complete."
