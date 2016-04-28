#!/bin/sh
#
# deleteInstance.sh - Delete the databases and files for a CRM instance
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-14
# Revised: 2011-03-21
# Revised: 2014-07-22 - allow hyphens in database names by backquoting names
# Revised: 2016-04-28 - removed data.basename; using data.dirname instead
#                     - removed --domain option and all references to domain
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--ok] [--files-only] [--db-only] instanceName" >&2
}

force_ok=0
files_only=0
db_only=0
instance=

while [ $# -gt 0 ]; do
  case "$1" in
    --help|-h) usage; exit 0 ;;
    --ok) force_ok=1 ;;
    --files-only) files_only=1 ;;
    --db-only) db_only=1 ;;
    -*) echo "$prog: $1: Invalid option" >&2 ; usage ; exit 1 ;;
    *) instance="$1" ;;
  esac
  shift
done

if [ ! "$instance" ]; then
  echo "$prog: Must specify an instance to delete" >&2
  exit 1
elif ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

db_civi_prefix=`$readConfig --ig $instance db.civicrm.prefix` || db_civi_prefix="$DEFAULT_DB_CIVICRM_PREFIX"
db_drup_prefix=`$readConfig --ig $instance db.drupal.prefix` || db_drup_prefix="$DEFAULT_DB_DRUPAL_PREFIX"
db_log_prefix=`$readConfig --ig $instance db.log.prefix` || db_log_prefix="$DEFAULT_DB_LOG_PREFIX"
db_basename=`$readConfig --ig $instance db.basename` || db_basename="$instance"
drupal_rootdir=`$readConfig --ig $instance drupal.rootdir` || drupal_rootdir="$DEFAULT_DRUPAL_ROOTDIR"
data_rootdir=`$readConfig --ig $instance data.rootdir` || data_rootdir="$DEFAULT_DATA_ROOTDIR"
data_dirname=`$readConfig --ig $instance data.dirname` || data_dirname="$instance"
errcode=0

instance_data_dir="$data_rootdir/$data_dirname"

if [ $force_ok -eq 0 ]; then
  echo "Please review before deleting:"
  echo
  echo "CiviCRM DB Prefix: $db_civi_prefix"
  echo "Drupal DB Prefix: $db_drup_prefix"
  echo "Log DB Prefix: $db_log_prefix"
  echo "Drupal Root Directory: $drupal_rootdir"
  echo "Data Root Directory: $data_rootdir"
  if [ $db_only -ne 1 ]; then
    echo "Will delete dir: $instance_data_dir"
  fi
  if [ $files_only -ne 1 ]; then
    echo "Will delete DB: $db_drup_prefix$db_basename"
    echo "Will delete DB: $db_civi_prefix$db_basename"
    echo "Will delete DB: $db_log_prefix$db_basename"
  fi
  echo
  echo -n "Are you sure that you want to delete instance $instance ([N]/y)? "
  read ch
  case "$ch" in
    [yY]*) ;;
    *) echo "Aborting."; exit 0 ;;
  esac
fi

if [ $db_only -ne 1 ]; then
  echo "Deleting site files for instance [$instance]"
  ( set -x
    rm -rf "$instance_data_dir"
  ) || errcode=$(($errcode | 1))
fi

if [ $files_only -ne 1 ]; then
  echo "Deleting Drupal database for instance [$instance]"
  ( set -x
    $execSql --no-db -c "drop database \`$db_drup_prefix$db_basename\`"
  ) || errcode=$(($errcode | 2))
  set +x

  echo "Deleting CiviCRM database for instance [$instance]"
  ( set -x
    $execSql --no-db -c "drop database \`$db_civi_prefix$db_basename\`"
  ) || errcode=$(($errcode | 4))
  set +x

  echo "Deleting Log database for instance [$instance]"
  ( set -x
    $execSql --no-db -c "drop database \`$db_log_prefix$db_basename\`"
  ) || errcode=$(($errcode | 4))
  set +x
fi

exit $errcode
