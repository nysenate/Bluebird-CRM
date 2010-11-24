#!/bin/sh
#
# copyInstance.sh - Copy one CRM database instance into another.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-28
# Revised: 2010-09-30
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
dumpInstance=$script_dir/dumpInstance.sh
deleteInstance=$script_dir/deleteInstance.sh

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--civicrm-sql-file file] [--drupal-sql-file file] [--delete] sourceInstance targetInstance" >&2
}

die() {
  rc="$1"
  rm -f $temp_civi_file $temp_drup_file
  exit $rc
}

srcinst=
destinst=
civi_sql_file=
drup_sql_file=
delete_instance=0

while [ $# -gt 0 ]; do
  case "$1" in
    --civi*-sql-file|-c) shift; civi_sql_file="$1" ;;
    --drup*-sql-file|-d) shift; drup_sql_file="$1" ;;
    --delete) delete_instance=1 ;;
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
elif [ "$srcinst" = "$destinst" ]; then
  echo "$prog: Warning: Source and destination instances are the same." >&2
fi

# Each instance contains two databases: a civicrm DB and a drupal DB.
# Either of the two source DBs could utilize a SQL dump file, rather than
# performing a live dump from an existing source instance.

temp_civi_file=/tmp/cividb_$$.tmp.sql
temp_drup_file=/tmp/drupdb_$$.tmp.sql

# Generate the SQL dump, either from a live DB or from saved data.

if [ "$civi_sql_file" ]; then
  echo "Using $civi_sql_file as source CiviCRM data."
  cp $civi_sql_file $temp_civi_file
else
  echo "Dumping CiviCRM data from instance database."
  $dumpInstance -c $temp_civi_file --no-drupal $srcinst || die 1
fi

if [ "$drup_sql_file" ]; then
  echo "Using $drup_sql_file as source Drupal data."
  cp $drup_sql_file $temp_drup_file
else
  echo "Dumping Drupal data from instance database."
  $dumpInstance -d $temp_drup_file --no-civicrm $srcinst || die 1
fi

# Create a new database and load it with the saved SQL dump.

if [ $delete_instance -eq 1 ]; then
  echo "Deleting instance [$destinst] first"
  $deleteInstance --ok $destinst
fi

if $execSql -i $destinst 2>/dev/null; then
  echo "$prog: CiviCRM database for instance $destinst already exists; it cannot exist prior to loading." >&2
  die 1
elif $execSql -i $destinst --drupal 2>/dev/null; then
  echo "$prog: Drupal database for instance $destinst already exists; it cannot exist prior to loading." >&2
  die 1
fi

echo "Loading CiviCRM database for instance $destinst with data from $temp_civi_file"
$execSql --create -i $destinst || die 2
$execSql -f $temp_civi_file -i $destinst || die 3

echo "Loading Drupal database for instance $destinst with data from $temp_drup_file"
$execSql --create -i $destinst --drupal || die 4
$execSql -f $temp_drup_file -i $destinst --drupal || die 5

die 0
