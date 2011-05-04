#!/bin/sh
#
# dumpInstance.sh - Perform a MySQL dump for a CRM instance
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-12
# Revised: 2011-05-04
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
civi_file=
no_civi=0
drup_file=
no_drup=0
archive_dump=
archive_file=

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--civicrm-file file] [--no-civicrm] [--drupal-file file] [--no-drupal] [--tgz | --tbz2 | --zip] [--archive-file file] instanceName" >&2
}

if [ $# -lt 1 ]; then
  usage
  exit 1
fi

while [ $# -gt 0 ]; do
  case "$1" in
    -c|--civi*-file) shift; civi_file="$1" ;;
    -d|--drup*-file) shift; drup_file="$1" ;;
    -o|--arc*-file) shift; archive_file="$1" ;;
    --no-civi*) no_civi=1 ;;
    --no-drup*) no_drup=1 ;;
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
fi

db_basename=`$readConfig --ig $instance db.basename` || db_basename="$instance"
db_civi_prefix=`$readConfig --ig $instance db.civicrm.prefix` || db_civi_prefix="$DEFAULT_DB_CIVICRM_PREFIX"
db_drup_prefix=`$readConfig --ig $instance db.drupal.prefix` || db_drup_prefix="$DEFAULT_DB_DRUPAL_PREFIX"

[ "$civi_file" ] || civi_file=$db_civi_prefix$db_basename.sql
[ "$drup_file" ] || drup_file=$db_drup_prefix$db_basename.sql

errcode=0

if [ $no_civi -eq 0 ]; then
  echo "Dumping CiviCRM database for instance [$instance]"
  ( set -x
    $execSql --dump $db_civi_prefix$db_basename > $civi_file
  ) || errcode=$(($errcode | 1))
fi

if [ $no_drup -eq 0 ]; then
  echo "Dumping Drupal database for instance [$instance]"
  ( set -x
    $execSql --dump $db_drup_prefix$db_basename > $drup_file
  ) || errcode=$(($errcode | 2))
fi

if [ "$archive_dump" ]; then
  case "$archive_dump" in
    tgz) file_ext="tar.gz"; arc_cmd="tar zcvf" ;;
    tbz2) file_ext="tar.bz2"; arc_cmd="tar jcvf" ;;
    *) file_ext="zip"; arc_cmd="zip" ;;
  esac

  if [ "$archive_file" ]; then
    arc_file="$archive_file"
  else
    todays_date=`date +%Y%m%d`
    arc_file="${instance}_dump_$todays_date.$file_ext"
  fi
  $arc_cmd $arc_file $civi_file $drup_file
  if [ $? -eq 0 ]; then
    rm -vf $civi_file $drup_file
  fi
fi

exit $errcode
