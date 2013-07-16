#!/bin/sh
#
# copyInstance.sh - Copy one CRM database instance into another.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-28
# Revised: 2010-09-30
# Revised: 2013-05-14 - added logic to support logging database
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
dumpInstance=$script_dir/dumpInstance.sh
deleteInstance=$script_dir/deleteInstance.sh

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--civicrm-sql-file|-c file] [--drupal-sql-file|-d file] [--log-sql-file|-l file] [--delete] [--copy-log-db] sourceInstance targetInstance" >&2
}

die() {
  rc="$1"
  rm -f $temp_civi_file $temp_drup_file $temp_log_file
  exit $rc
}

srcinst=
destinst=
civi_sql_file=
drup_sql_file=
log_sql_file=
delete_instance=0
copy_log_db=0

while [ $# -gt 0 ]; do
  case "$1" in
    --civi*-sql-file|-c) shift; civi_sql_file="$1" ;;
    --drup*-sql-file|-d) shift; drup_sql_file="$1" ;;
    --log-sql-file|-l) shift; log_sql_file="$1" ;;
    --delete) delete_instance=1 ;;
    --copy-log-db) copy_log_db=1 ;;
    -*) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
    *) [ "$srcinst" ] && destinst="$1" || srcinst="$1" ;;
  esac
  shift
done

if [ ! "$srcinst" -o ! "$destinst" ]; then
  echo "$prog: Must specify a source and destination instance." >&2
  exit 1
elif ! $readConfig --instance $srcinst --quiet; then
  echo "$prog: $srcinst: Source instance not found in config." >&2
  exit 1
elif ! $readConfig --instance $destinst --quiet; then
  echo "$prog: $destinst: Destination instance not found in config." >&2
  exit 1
elif [ "$civi_sql_file" -a ! -r "$civi_sql_file" ]; then
  echo "$prog: $civi_sql_file: CiviCRM SQL dump file not found." >&2
  exit 1
elif [ "$drup_sql_file" -a ! -r "$drup_sql_file" ]; then
  echo "$prog: $drup_sql_file: Drupal SQL dump file not found." >&2
  exit 1
elif [ "$log_sql_file" -a ! -r "$log_sql_file" ]; then
  echo "$prog: $log_sql_file: Log SQL dump file not found." >&2
  exit 1
elif [ "$srcinst" = "$destinst" ]; then
  echo "$prog: Warning: Source and destination instances are the same." >&2
fi

# Each instance contains three databases: a CiviCRM DB, a Drupal DB, and
# a Logging DB.  Any of these source DBs could utilize a SQL dump file,
# rather than performing a live dump from an existing source instance.
# The log db is only copied if --copy-log-db is specified.

temp_civi_file=/tmp/cividb_$$.tmp.sql
temp_drup_file=/tmp/drupdb_$$.tmp.sql
temp_log_file=/tmp/logdb_$$.tmp.sql

# Generate the SQL dump, either from a live DB or from saved data.

if [ "$civi_sql_file" ]; then
  echo "Using $civi_sql_file as source CiviCRM data."
  cp "$civi_sql_file" "$temp_civi_file"
else
  echo "Dumping CiviCRM data from instance database."
  $dumpInstance -c "$temp_civi_file" --civi-only $srcinst || die 1
fi

if [ "$drup_sql_file" ]; then
  echo "Using $drup_sql_file as source Drupal data."
  cp "$drup_sql_file" "$temp_drup_file"
else
  echo "Dumping Drupal data from instance database."
  $dumpInstance -d "$temp_drup_file" --drup-only $srcinst || die 1
fi

if [ $copy_log_db -eq 1 ]; then
  if [ "$log_sql_file" ]; then
    echo "Using $log_sql_file as source Logging data."
    cp "$log_sql_file" "$temp_log_file"
  else
    echo "Dumping Logging data from instance database."
    $dumpInstance -l "$temp_log_file" --log-only $srcinst || die 1
  fi
fi


# Create a new database and load it with the saved SQL dump.

if [ $delete_instance -eq 1 ]; then
  echo "Deleting instance [$destinst] first"
  $deleteInstance --ok $destinst
fi

if $execSql $destinst 2>/dev/null; then
  echo "$prog: CiviCRM database for instance $destinst already exists; it cannot exist prior to loading." >&2
  die 1
elif $execSql --drupal $destinst 2>/dev/null; then
  echo "$prog: Drupal database for instance $destinst already exists; it cannot exist prior to loading." >&2
  die 1
elif $execSql $destinst --log 2>/dev/null; then
  echo "$prog: Logging database for instance $destinst already exists; it cannot exist prior to loading." >&2
  die 1
fi

echo "Loading CiviCRM database for instance $destinst with data from $temp_civi_file"
$execSql --create $destinst || die 2
$execSql -f "$temp_civi_file" $destinst || die 3

echo "Loading Drupal database for instance $destinst with data from $temp_drup_file"
$execSql --create --drupal $destinst || die 4
$execSql -f "$temp_drup_file" --drupal $destinst || die 5

if [ $copy_log_db -eq 1 ]; then
  echo "Loading Logging database for instance $destinst with data from $temp_log_file"
  $execSql --create --log $destinst || die 6
  $execSql -f "$temp_log_file" --log $destinst || die 7
else
  echo "Creating empty Logging database for instance $destinst"
  $execSql --create --log $destinst || die 6
fi

die 0
