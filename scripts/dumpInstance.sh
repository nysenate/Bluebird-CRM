#!/bin/sh
#
# dumpInstance.sh - Perform a MySQL dump for a CRM instance
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-12
# Revised: 2011-02-24
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
civi_outfile=
no_civi=0
drup_outfile=
no_drup=0
archive_dump=

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--civicrm-outfile file] [--no-civicrm] [--drupal-outfile file] [--no-drupal] [--tgz | --tbz2 | --zip] instanceName" >&2
}

if [ $# -lt 1 ]; then
  usage
  exit 1
fi

while [ $# -gt 0 ]; do
  case "$1" in
    -c|--civi*-file) shift; civi_outfile="$1" ;;
    -d|--drup*-file) shift; drup_outfile="$1" ;;
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

[ "$civi_outfile" ] || civi_outfile=$db_civi_prefix$db_basename.sql
[ "$drup_outfile" ] || drup_outfile=$db_drup_prefix$db_basename.sql

errcode=0

if [ $no_civi -eq 0 ]; then
  echo "Dumping CiviCRM database for instance [$instance]"
  ( set -x
    $execSql --dump $db_civi_prefix$db_basename > $civi_outfile
  ) || errcode=$(($errcode | 1))
fi

if [ $no_drup -eq 0 ]; then
  echo "Dumping Drupal database for instance [$instance]"
  ( set -x
    $execSql --dump $db_drup_prefix$db_basename > $drup_outfile
  ) || errcode=$(($errcode | 2))
fi

if [ "$archive_dump" ]; then
  case "$archive_dump" in
    tgz) file_ext="tar.gz"; arc_cmd="tar zcvf" ;;
    tbz2) file_ext="tar.bz2"; arc_cmd="tar jcvf" ;;
    *) file_ext="zip"; arc_cmd="zip" ;;
  esac

  todays_date=`date +%Y%m%d`
  arc_file="${instance}_dump_$todays_date.$file_ext"
  $arc_cmd $arc_file $civi_outfile $drup_outfile
  if [ $? -eq 0 ]; then
    rm -vf $civi_outfile $drup_outfile
  fi
fi

exit $errcode
