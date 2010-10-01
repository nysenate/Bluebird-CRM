#!/bin/sh
#
# dumpInstance.sh - Perform a MySQL dump for a CRM instance
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-12
# Revised: 2010-09-30
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
civi_outfile=
no_civi=0
drup_outfile=
no_drup=0

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--civicrm-outfile file] [--no-civicrm] [--drupal-outfile file] [--no-drupal] instanceName" >&2
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

exit $errcode
