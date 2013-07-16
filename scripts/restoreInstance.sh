#!/bin/bash
#
# restoreInstance.sh - Restore a CRM instance by loading MySQL dump files.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2011-05-02
# Revised: 2011-08-05
# Revised: 2013-02-05 - changed temp dir to be within /data
#

prog=`basename $0`
script_dir=`dirname $0`
script_dir=`cd "$script_dir"; echo $PWD`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
archive_file=
force_ok=0
ignore_mismatch=0
tmpdir="/data/tmp/restoreInstance_$$"

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--ok] [--ignore-mismatch] {--archive-file file} instanceName" >&2
}

if [ $# -lt 1 ]; then
  usage
  exit 1
fi

while [ $# -gt 0 ]; do
  case "$1" in
    -f|--archive-file) shift; archive_file="$1" ;;
    --ok) force_ok=1 ;;
    -i|--ignore-mismatch) ignore_mismatch=1 ;;
    -*) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
    *) instance="$1" ;;
  esac
  shift
done

if ! $readConfig --instance $instance --quiet; then
  echo "$prog: Instance [$instance] not found in config" >&2
  exit 1
elif [ ! "$archive_file" ]; then
  echo "$prog: Must specify an archive file to restore from" >&2
  exit 1
elif [ ! -r "$archive_file" ]; then
  echo "$prog: $archive_file: File not found" >&2
  exit 1
else
  # If the archive filepath is not absolute, then make it so.
  if [ ${archive_file:0:1} != '/' ]; then
    archive_filename=`basename $archive_file`
    archive_dir=`dirname "$archive_file"`
    archive_dir=`cd "$archive_dir"; echo $PWD`
    archive_file="$archive_dir/$archive_filename"
  fi
fi

db_basename=`$readConfig --ig $instance db.basename` || db_basename="$instance"
db_civi_prefix=`$readConfig --ig $instance db.civicrm.prefix` || db_civi_prefix="$DEFAULT_DB_CIVICRM_PREFIX"
db_drup_prefix=`$readConfig --ig $instance db.drupal.prefix` || db_drup_prefix="$DEFAULT_DB_DRUPAL_PREFIX"
db_log_prefix=`$readConfig --ig $instance db.log.prefix` || db_log_prefix="$DEFAULT_DB_LOG_PREFIX"

errcode=0

archive_ext=${archive_file##*.}

case "$archive_ext" in
  zip) unarc_cmd="unzip" ;;
  tgz) unarc_cmd="tar zxvf" ;;
  tbz2) unarc_cmd="tar jxvf" ;;
  *) echo "$prog: $archive_ext: Unrecognized extension" >&2; exit 1 ;;
esac

mkdir -p $tmpdir/
pushd $tmpdir/
$unarc_cmd $archive_file || exit 1

# Sanity check.  Make sure the SQL dump files are named exactly as we expect.

if [ $ignore_mismatch -eq 1 ]; then
  civi_file=$(echo $db_civi_prefix*.sql)
  drup_file=$(echo $db_drup_prefix*.sql)
  log_file=$(echo $db_log_prefix*.sql)
else
  civi_file=$db_civi_prefix$db_basename.sql
  drup_file=$db_drup_prefix$db_basename.sql
  log_file=$db_log_prefix$db_basename.sql
fi

if [ ! -r "$civi_file" ]; then
  echo "$prog: $civi_file: CiviCRM database file not found in archive." >&2
  popd
  rm -rf $tmpdir/
  exit 1
elif [ ! -r "$drup_file" ]; then
  echo "$prog: $drup_file: Drupal database file not found in archive." >&2
  popd
  rm -rf $tmpdir/
  exit 1
elif [ ! -r "$log_file" ]; then
  echo "$prog: $log_file: Logging database file not found in archive." >&2
  popd
  rm -rf $tmpdir/
  exit 1
fi

if [ $force_ok -eq 0 ]; then
  echo -n "Are you sure you want to restore instance [$instance] ([N]/y)? " >&2
  read ch
  case "$ch" in
    [yY]*) ;;
    *) echo "Aborting."; popd; rm -rf $tmpdir/; exit 0 ;;
  esac
fi

$execSql "$instance" -f "$civi_file"
$execSql "$instance" -f "$drup_file" --drupal
$execSql "$instance" -f "$log_file" --log

popd
rm -rf $tmpdir/

exit $errcode
