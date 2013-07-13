#!/bin/sh
#
# fixSuppAddr1.sh - Set supplemental_address_1 to NULL wherever possible.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2011-09-19
# Revised: 2011-09-26
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
force_ok=0
dry_run=0
loctype_boe=6
loctype_boe_mail=13

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

selacnt="select count(*) from civicrm_address"
condloctypeboe="location_type_id=$loctype_boe"
condloctypeboemail="location_type_id=$loctype_boe_mail"
condsupp1val="supplemental_address_1<>''"
cond="$condloctypeboe and $condsupp1val and contact_id in ( select contact_id from ( select contact_id from civicrm_address where $condloctypeboemail ) as temp )"
# This SHOULD be the query, but MySQL has a limitation where a table cannot
# be updated if that same table is accessed in a subquery.
#cond="$condloctypeboe and $condsupp1val and contact_id in ( select contact_id from civicrm_address where $condloctypeboemail )"

echo "Counting all addresses with non-empty supp1" >&2
sql="$selacnt where $condsupp1val;"
acnt1=`$execSql -q $instance -c "$sql"`
echo "Counting BOE addresses with non-empty supp1" >&2
sql="$selacnt where $condloctypeboe and $condsupp1val;"
acnt2=`$execSql -q $instance -c "$sql"`
echo "Counting BOE mailing addresses" >&2
sql="$selacnt where $condloctypeboemail;"
acnt3=`$execSql -q $instance -c "$sql"`
echo "Counting BOE mailing addresses with non-empty supp1" >&2
sql="$selacnt where $condloctypeboemail and $condsupp1val;"
acnt4=`$execSql -q $instance -c "$sql"`
echo "Counting BOE addresses with non-empty supp1, with an associated BOE mailing address" >&2
sql="$selacnt where $cond;"
supp1cnt=`$execSql -q $instance -c "$sql"`

echo "Total address records with non-empty supp1: $acnt1" >&2
echo "Total BOE address records with non-empty supp1: $acnt2" >&2
echo "Total BOE mailing address records: $acnt3" >&2
echo "Total BOE mailing address records with non-empty supp1: $acnt4" >&2
echo "Total BOE address records with non-empty supp1 and with an associated BOE mailing address: $supp1cnt" >&2

if [ $supp1cnt -gt 0 -a $dry_run -eq 0 ]; then
  if [ $force_ok -eq 0 ]; then
    echo -n "Proceed with clean-up operation (N/y)? "
    read ch
    case "$ch" in
      [yY]*) ;;
      *) echo "Aborting."; exit 0 ;;
    esac
  fi


  echo "Setting supp1 to NULL for BOE address records where supp1 is set and an associated BOE mailing address exists for that contact" >&2
  sql="update civicrm_address set supplemental_address_1=null where $cond;"
  $execSql $instance -c "$sql" || exit 1
fi

exit 0
