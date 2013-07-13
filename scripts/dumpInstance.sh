#!/bin/bash
#
# dumpInstance.sh - Perform a MySQL dump for a CRM instance
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-12
# Revised: 2012-04-18
# Revised: 2013-02-05 - changed temp dir to be within /data
# Revised: 2013-05-14 - added ability to dump only one of the 3 databases
# Revised: 2013-07-12 - remove temp tables prior to dump; option to inhibit
#

prog=`basename $0`
script_dir=`dirname $0`
script_dir=`cd "$script_dir"; echo $PWD`
clearCache=$script_dir/clearCache.sh
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
tmpdir=/data/tmp/dumpInstance_$$
civi_file=
no_civi=0
drup_file=
no_drup=0
log_file=
no_log=0
archive_dump=
archive_file=
no_db_cleanup=0

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--civicrm-file file] [--civicrm-only] [--no-civicrm] [--drupal-file file] [--drupal-only] [--no-drupal] [--logdb-file file] [--logdb-only] [--no-logdb] [--no-db-cleanup] [--tgz | --tbz2 | --zip] [--archive-file file] instance" >&2
}

if [ $# -lt 1 ]; then
  usage
  exit 1
fi

while [ $# -gt 0 ]; do
  case "$1" in
    -c|--civi*-file) shift; civi_file="$1" ;;
    -d|--drup*-file) shift; drup_file="$1" ;;
    -l|--log*-file) shift; log_file="$1" ;;
    -o|--arc*-file) shift; archive_file="$1" ;;
    --civi*-only) no_drup=1; no_log=1 ;;
    --drup*-only) no_civi=1; no_log=1 ;;
    --log*-only) no_civi=1; no_drup=1 ;;
    --no-civi*) no_civi=1 ;;
    --no-drup*) no_drup=1 ;;
    --no-log*) no_log=1 ;;
    --no-db*) no_db_cleanup=1 ;;
    --tgz) archive_dump=tgz ;;
    --tbz2) archive_dump=tbz2 ;;
    --zip) archive_dump=zip ;;
    -*) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
    *) instance="$1" ;;
  esac
  shift
done

if ! $readConfig --instance $instance --quiet; then
  echo "$prog: Instance [$instance] not found in config" >&2
  exit 1
elif [ $no_civi -eq 1 -a $no_drup -eq 1 -a $no_log -eq 1 ]; then
  echo "$prog: Invalid combination of options; must dump at least one db" >&2
  exit 1
fi

db_basename=`$readConfig --ig $instance db.basename` || db_basename="$instance"
db_civi_prefix=`$readConfig --ig $instance db.civicrm.prefix` || db_civi_prefix="$DEFAULT_DB_CIVICRM_PREFIX"
db_drup_prefix=`$readConfig --ig $instance db.drupal.prefix` || db_drup_prefix="$DEFAULT_DB_DRUPAL_PREFIX"
db_log_prefix=`$readConfig --ig $instance db.log.prefix` || db_log_prefix="$DEFAULT_DB_LOG_PREFIX"

if [ "$archive_dump" -o ! "$civi_file" ]; then
  civi_file=$db_civi_prefix$db_basename.sql
fi
if [ "$archive_dump" -o ! "$drup_file" ]; then
  drup_file=$db_drup_prefix$db_basename.sql
fi
if [ "$archive_dump" -o ! "$log_file" ]; then
  log_file=$db_log_prefix$db_basename.sql
fi

errcode=0

if [ "$archive_dump" ]; then
  mkdir -p "$tmpdir"
  pushd "$tmpdir"
fi

# Generate clean dump by remove temporary tables first.
if [ $no_db_cleanup -eq 1 ]; then
  echo "Skipping database cleanup; no temp tables will be dropped"
else
  echo "Cleaning up databases prior to dumping data"
  $clearCache --tmp-only $instance
fi

if [ $no_civi -eq 0 ]; then
  echo "Dumping CiviCRM database for instance [$instance]"
  ( set -x
    $execSql --dump --db-name $db_civi_prefix$db_basename > "$civi_file"
  ) || errcode=$(($errcode | 1))
fi

if [ $no_drup -eq 0 ]; then
  echo "Dumping Drupal database for instance [$instance]"
  ( set -x
    $execSql --dump --db-name $db_drup_prefix$db_basename > "$drup_file"
  ) || errcode=$(($errcode | 2))
fi

if [ $no_log -eq 0 ]; then
  echo "Dumping Logging database for instance [$instance]"
  ( set -x
    $execSql --dump --db-name $db_log_prefix$db_basename > "$log_file"
  ) || errcode=$(($errcode | 4))
fi

if [ "$archive_dump" ]; then
  if [ $errcode -eq 0 ]; then
    case "$archive_dump" in
      tgz) file_ext="tar.gz"; arc_cmd="tar zcvf" ;;
      tbz2) file_ext="tar.bz2"; arc_cmd="tar jcvf" ;;
      *) file_ext="zip"; arc_cmd="zip" ;;
    esac

    todays_date=`date +%Y%m%d`
    arc_file="${instance}_dump_$todays_date.$file_ext"
    $arc_cmd "$arc_file" "$civi_file" "$drup_file" "$log_file" || errcode=$(($errcode | 8))
  fi
  popd

  if [ $errcode -eq 0 ]; then
    if [ "$archive_file" ]; then
      mv "$tmpdir/$arc_file" "$archive_file"
    else
      mv "$tmpdir/$arc_file" .
    fi
  fi

  rm -rf "$tmpdir"
fi

exit $errcode
