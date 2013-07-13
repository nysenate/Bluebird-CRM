#!/bin/sh
#
# fixDupAddr.sh - Nullify supp1 or supp2 values if they duplicate street_address
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2011-08-24
# Revised: 2011-08-24
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
force_ok=0
dry_run=0

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--dry-run] [--ok] instanceName" >&2
}


if [ $# -lt 1 ]; then
  usage
  exit 1
fi

while [ $# -gt 0 ]; do
  case "$1" in
    --ok) force_ok=1 ;;
    -n|--dry-run) dry_run=1 ;;
    -*) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
    *) instance="$1" ;;
  esac
  shift
done

if ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

echo "==> Examining CRM instance [$instance]" >&2

for fld in supplemental_address_1 supplemental_address_2; do
  echo "Checking for street_addresses that are duplicated by $fld" >&2
  cond="$fld <> '' and street_address = $fld"
  sql="select count(*) from civicrm_address where $cond"
  cnt=`$execSql -q $instance -c "$sql;"`
  echo "Total address records with duplicate street_address and $fld: $cnt" >&2

  if [ $cnt -gt 0 -a $dry_run -eq 0 ]; then
    if [ $force_ok -eq 0 ]; then
      echo -n "Proceed with clean-up operation (N/y)? "
      read ch
      case "$ch" in
        [yY]*) ;;
        *) echo "Aborting."; exit 0 ;;
      esac
    fi


    echo "Nullifying $fld values that duplicate the street_address" >&2
    sql="update civicrm_address set $fld=null where $cond"
    $execSql -q $instance -c "$sql;"
  fi
done

exit 0
