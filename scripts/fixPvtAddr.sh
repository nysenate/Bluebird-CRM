#!/bin/sh
#
# fixPvtAddr.sh - Remove "Pvt" from street_name and street_unit fields.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2011-09-30
# Revised: 2011-10-04
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
rebuildCache=$script_dir/rebuildCachedValues.sh
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

echo "==> Processing CRM instance [$instance]" >&2

selacnt="select count(*) from civicrm_address"
condname="street_name like '% Pvt'"
condunit="street_unit like '%Pvt%'"

echo "Counting addresses with 'Pvt' in street_name" >&2
sql="$selacnt where $condname;"
cnt1=`$execSql -q $instance -c "$sql"`
echo "Counting addresses with 'Pvt' in street_unit" >&2
sql="$selacnt where $condunit;"
cnt2=`$execSql -q $instance -c "$sql"`

# The master condition for determining opted out contacts.
cond="$condname or $condunit"
sql="$selacnt where $cond"
pvtcnt=`$execSql -q $instance -c "$sql"`

echo "Address records with 'Pvt' in street_name: $cnt1" >&2
echo "Address records with 'Pvt' in street_unit: $cnt2" >&2
echo "Total address records with 'Pvt': $pvtcnt" >&2

newname="trim(regexp_replace(street_name, '[,]?[ ]?(Apt[.]?)? Pvt$', ''))"
newunit="nullif(trim(regexp_replace(street_unit, '[,]?[ ]?(Apt[.]?[ ]?)?Pvt.*', '')), '')"

if [ $pvtcnt -gt 0 ]; then
  if [ $dry_run -eq 0 ]; then
    do_cleanup=1
    if [ $force_ok -eq 0 ]; then
      echo -n "Proceed with clean-up operation (N/y)? "
      read ch
      case "$ch" in
        [yY]*) ;;
        *) do_cleanup=0 ;;
      esac
    fi

    if [ $do_cleanup -eq 1 ]; then
      echo "Removing 'Pvt' from $pvtcnt addresses" >&2
      sql="update civicrm_address
           set street_address=null,
               street_name=$newname,
               street_unit=$newunit
           where $cond;"
      $execSql -q $instance -c "$sql" || exit 1
      $rebuildCache --ok --field-streetaddress $instance
    else
      echo "Skipping update for $pvtcnt addresses" >&2
    fi
  else
    sql="select street_name, street_unit, concat('[',$newname,']'), concat('[',$newunit,']')
         from civicrm_address
         where $cond;"
    $execSql -q $instance -c "$sql" || exit 1
  fi
fi

exit 0
