#!/bin/sh
#
# fixSuppAddr2.sh - Move e-mail addresses and/or phone numbers from
#                   supplemental_address_2 into e-mail/phone tables.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2011-05-02
# Revised: 2011-05-05
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

echo "Checking for e-mail addresses in supplemental_address_2" >&2
cond1="supplemental_address_2 like '%@%'"
cond2="( contact_id,supplemental_address_2 ) not in ( select contact_id, email from civicrm_email)"
sql1="select count(*) from civicrm_address where $cond1"
sql2="select count(*) from civicrm_address where $cond1 and $cond2"
sql3="select count(*) from civicrm_email"

cnt1=`$execSql -q $instance -c "$sql1;"`
cnt2=`$execSql -q $instance -c "$sql2;"`
cnt3=`$execSql -q $instance -c "$sql3;"`
echo "Total address records with e-mail in supp2: $cnt1" >&2
echo "E-mails that will be inserted into civicrm_email: $cnt2" >&2
echo "Total e-mails before insertion: $cnt3" >&2

if [ $cnt1 -gt 0 -a $dry_run -eq 0 ]; then
  if [ $force_ok -eq 0 ]; then
    echo -n "Proceed with clean-up operation (N/y)? "
    read ch
    case "$ch" in
      [yY]*) ;;
      *) echo "Aborting."; exit 0 ;;
    esac
  fi


  echo "Copying e-mails to civicrm_email table" >&2
  sql="insert into civicrm_email ( contact_id, location_type_id, email, is_primary ) select contact_id, 1, supplemental_address_2, 0 from civicrm_address where $cond1 and $cond2"
  $execSql -q $instance -c "$sql;" || exit 1
  echo "Removing supp2 e-mails from civicrm_address table" >&2
  sql="update civicrm_address set supplemental_address_2=null where $cond1"
  $execSql -q $instance -c "$sql;"
fi

echo "Checking for phone numbers in supplemental_address_2" >&2
cond="supplemental_address_2 rlike '[0-9]{3}-[0-9]{4}'"
sql1="select count(*) from civicrm_address where $cond"
sql2="select count(*) from civicrm_phone"

cnt1=`$execSql -q $instance -c "$sql1;"`
cnt2=`$execSql -q $instance -c "$sql2;"`
echo "Total address records with phone number in supp2: $cnt1" >&2
echo "Phone numbers that will be inserted into civicrm_phone: $cnt1" >&2
echo "Total phone records before insertion: $cnt2" >&2

if [ $cnt1 -gt 0 -a $dry_run -eq 0 ]; then
  if [ $force_ok -eq 0 ]; then
    echo -n "Proceed with clean-up operation (N/y)? "
    read ch
    case "$ch" in
      [yY]*) ;;
      *) echo "Aborting."; exit 0 ;;
    esac
  fi


  echo "Copying phone numbers to civicrm_phone table" >&2
  sql="insert into civicrm_phone ( contact_id, location_type_id, is_primary, phone, phone_type_id ) select contact_id, 1, 0, supplemental_address_2, 1 from civicrm_address where $cond"
  $execSql -q $instance -c "$sql;" || exit 1
  echo "Removing supp2 phones from civicrm_address table" >&2
  sql="update civicrm_address set supplemental_address_2=null where $cond"
  $execSql -q $instance -c "$sql;"
fi

exit 0
