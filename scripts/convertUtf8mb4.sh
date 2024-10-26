#!/bin/sh
#
# convertUtf8mb4.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2022-05-31
# Revised: 2022-06-20 - add logic to avoid running on a migrated database
#
# Sample command line:
#   $ scripts/iterateInstances.sh --all "scripts/convertUtf8mb4.sh {}"
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
drush=$script_dir/drush.sh
readConfig=$script_dir/readConfig.sh
collation_name="utf8mb4"

. $script_dir/defaults.sh

if [ $# -ne 1 ]; then
  echo "Usage: $prog instanceName" >&2
  exit 1
fi

instance="$1"

if ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

dbciviprefix=`$readConfig --ig $instance db.civicrm.prefix` || dbciviprefix="$DEFAULT_DB_CIVICRM_PREFIX"
dblogprefix=`$readConfig --ig $instance db.log.prefix` || dblogprefix="$DEFAULT_DB_LOG_PREFIX"
dbbasename=`$readConfig -i $instance db.basename` || dbbasename="$instance"
dbcivi=$dbciviprefix$dbbasename
dblog=$dblogprefix$dbbasename

echo "Converting CiviCRM and Logging databases to $collation_name format"


for dbname in $dbcivi $dblog; do
  echo "Checking database [$dbname]"

  ## determine if any tables do not have the correct collation
  sql="
    SELECT COUNT(table_name)
    FROM information_schema.tables
    WHERE table_schema = '$dbname'
      AND engine = 'InnoDB'
      AND table_collation <> '${collation_name}_unicode_ci';
  "
  tab_count=`$execSql -q $instance -c "$sql"`

  ## determine if any columns do not have the correct collation
  sql="
    SELECT COUNT(table_name)
    FROM information_schema.columns
    WHERE table_schema = '$dbname'
      AND collation_name IS NOT NULL
      AND collation_name <> '${collation_name}_unicode_ci'
      AND collation_name <> '${collation_name}_bin'
      AND table_name IN (
        SELECT table_name from information_schema.tables
        WHERE table_schema = '$dbname'
          AND engine = 'InnoDB'
      );
  "
  col_count=`$execSql -q $instance -c "$sql"`

  if [ $tab_count -gt 0 -o $col_count -gt 0 ]; then
    echo "Converting collation for database [$dbname]"
    $drush $instance cvapi system.utf8conversion patterns="civicrm_%,address_%,fn_%,migrate_%,nyss_%,redist_%,shadow_%,survey_%,log_civicrm_%,log_survey_%" databases=$dbname --quiet
  else
    echo "Tables/columns for database [$dbname] have already been converted"
  fi
done

echo "Completed conversion of CiviCRM and Logging databases to $collation_name format"
exit 0
