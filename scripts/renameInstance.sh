#!/bin/sh
#
# renameInstance.sh - Rename a CRM database instance.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2015-11-10
# Revised: 2015-11-11
# Revised: 2016-04-28 - removed data.basename; using data.dirname instead
#                     - removed all references to base.domain
#                     - removed renaming of instance site directory, since
#                       /sites/ is no longer polluted with directories
# Revised: 2023-01-03 - add --no-run-if-empty to xargs
# Revised: 2023-01-04 - add new options for fine-tuned control
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
default_db_types="civicrm drupal log"

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--delete-before-create] [--no-create] [--skip-civicrm-database] [--skip-drupal-database] [--skip-log-database] [--skip-files] [--skip-trigger-check] [--skip-views] oldInstanceName newInstanceName" >&2
}

if [ $# -lt 2 ]; then
  usage
  exit 1
fi

delete_before_create=0
no_create=0
skip_civi=0
skip_drup=0
skip_log=0
skip_files=0
skip_trigger_check=0
skip_views=0
srcinst=
destinst=

while [ $# -gt 0 ]; do
  case "$1" in
    --delete-before-create) delete_before_create=1 ;;
    --no-create) no_create=1 ;;
    --skip-civi*) skip_civi=1 ;;
    --skip-drup*) skip_drup=1 ;;
    --skip-log*) skip_log=1 ;;
    --skip-files) skip_files=1 ;;
    --skip-trigger-check) skip_trigger_check=1 ;;
    --skip-views) skip_views=1 ;;
    -*) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
    *) [ "$srcinst" ] && destinst="$1" || srcinst="$1" ;;
  esac
  shift
done

if [ ! "$srcinst" -o ! "$destinst" ]; then
  echo "$prog: Must specify a source and destination instance" >&2
  exit 1
elif [ $delete_before_create -eq 1 -a $no_create -eq 1 ]; then
  echo "$prog: Cannot specify --delete-before-create and --no-create at the same time" >&2
  exit 1
elif ! $readConfig --instance $srcinst --quiet; then
  echo "$prog: $srcinst: Source instance not found in config" >&2
  exit 1
elif ! $readConfig --instance $destinst --quiet; then
  echo "$prog: $destinst: Destination instance not found in config" >&2
  exit 1
elif [ "$srcinst" = "$destinst" ]; then
  echo "$prog: Source and destination instances cannot be the same" >&2
  exit 1
fi

# Check to see if there are any triggers on the source database.  Triggers
# cannot be moved from one database to another, and must be dropped prior
# to renaming.

if [ $skip_trigger_check -ne 1 ]; then
  echo "Checking for triggers on [$srcinst]"
  sql="SELECT count(*) FROM information_schema.triggers
       WHERE trigger_schema='@CIVIDB@';"
  trig_count=`$execSql -q --replace-macros $srcinst -c "$sql"`
  if [ $trig_count -ne 0 ]; then
    echo "$prog: There are $trig_count triggers on the CiviCRM database for [$srcinst]; please remove triggers before renaming" >&2
    echo "$prog: Hint: Use the dropCiviTriggers.sh script" >&2
    exit 1
  fi
fi

# Each instance contains three databases: a CiviCRM DB, a Drupal DB, and
# a Logging DB.

db_types=
[ $skip_civi -ne 1 ] && db_types="$db_types civicrm"
[ $skip_drup -ne 1 ] && db_types="$db_types drupal"
[ $skip_log -ne 1 ] && db_types="$db_types log"

if [ $no_create -ne 1 ]; then
  # Create new databases for the destination instance.

  if [ $delete_before_create -eq 1 ]; then
    $script_dir/deleteInstance.sh $destinst
  fi

  if [ "$db_types" ]; then
    for db in $db_types; do
      if $execSql -q --$db $destinst; then
        echo "$prog: $db database for instance [$destinst] already exists; it cannot exist prior to renaming" >&2
        exit 1
      fi
    done

    echo "Creating databases for instance [$destinst]"
    for db in $db_types; do
      $execSql -q --create --$db $destinst || exit 2
    done
  else
    echo "All database types were skipped; no databases will be created"
  fi
fi

tmpfile=/tmp/${prog}_$$.sql

if [ $skip_views -ne 1 -a $skip_civi -ne 1 ]; then
  echo "Saving database views from [$srcinst]"
  $execSql -q --civicrm $srcinst -c "show full tables where table_type = 'VIEW'" | cut -f1 | xargs -n 1 --no-run-if-empty $execSql $srcinst --dump-table >$tmpfile
fi

if [ "$db_types" ]; then
  echo "Moving tables from [$srcinst] to [$destinst]"
  for db in $db_types; do
    destdbname=`$execSql --$db $destinst --get-db-name`
    sql=`$execSql --$db $srcinst -c "show full tables where table_type != 'VIEW'" | cut -f1 | sed "s/^\(.*\)$/RENAME TABLE \1 TO $destdbname.\1;/"`
    $execSql --$db $srcinst -c "$sql"
  done
else
  echo "All database types were skipped; no tables will be renamed"
fi

if [ $skip_views -ne 1 -a $skip_civi -ne 1 ]; then
  echo "Restoring database views from [$srcinst] to [$destinst]"
  $execSql -q --civicrm $destinst -f "$tmpfile"
  rc=$?
  rm -f "$tmpfile"

  if [ $rc -ne 0 ]; then
    echo "$prog: Unable to create views on [$destinst] from saved data" >&2
    exit 1
  fi
fi

app_rootdir=`$readConfig --global app.rootdir` || app_rootdir="$DEFAULT_APP_ROOTDIR"
drupal_rootdir=`$readConfig --ig $srcinst drupal.rootdir` || drupal_rootdir="$DEFAULT_DRUPAL_ROOTDIR"
data_rootdir_src=`$readConfig --ig $srcinst data.rootdir` || data_rootdir_src="$DEFAULT_DATA_ROOTDIR"
data_rootdir_dest=`$readConfig --ig $destinst data.rootdir` || data_rootdir_dest="$DEFAULT_DATA_ROOTDIR"
data_dirname_src=`$readConfig --ig $srcinst data.dirname` || data_dirname_src="$srcinst"
data_dirname_dest=`$readConfig --ig $destinst data.dirname` || data_dirname_dest="$destinst"
data_dir_src="$data_rootdir_src/$data_dirname_src"
data_dir_dest="$data_rootdir_dest/$data_dirname_dest"

if [ $skip_files -ne 1 ]; then
  echo "Moving data directory [$data_dir_src] to [$data_dir_dest]"
  if [ -d $data_dir_dest ]; then
    echo "$prog: Destination data directory [$data_dir_dest] already exists; please remove it before running this script" >&2
    exit 1
  fi

  mv $data_dir_src $data_dir_dest || exit 1
else
  echo "Data directory [$data_dir_src] will not be moved"
fi

echo "Configuring instance [$destinst]"
$script_dir/manageCiviConfig.sh $destinst --update --all

echo "Rebuilding triggers"
php $app_rootdir/civicrm/scripts/rebuildTriggers.php -S$destinst

echo "Recreating shadow-table functions and triggers"
$execSql --civicrm $destinst -f "$app_rootdir/modules/nyss_dedupe/shadow_func.sql"

echo "Clearing cache"
$script_dir/clearCache.sh $destinst --all

echo "Done renaming $srcinst -> $destinst.  You will probably want to"
echo "delete the old CRM ($srcinst) using deleteInstance.sh"

exit 0
