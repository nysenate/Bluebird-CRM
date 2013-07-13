#!/bin/sh
#
# fixSpaces.sh - Remove extraneous spaces from various databases fields.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2011-04-09
# Revised: 2011-06-01
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
force_ok=0
dry_run=0

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--ok] [--dry-run] instanceName" >&2
}


do_field_trim() {
  tab_name="$1"
  shift
  fld_names=$@

  echo "Checking for leading and trailing spaces in $tab_name" >&2
  where=
  got_first=0
  for fldname in $fld_names; do
    [ $got_first -eq 1 ] && where="$where or "
    where="$where $fldname like ' %' or $fldname like '% '"
    got_first=1
  done

  sql="select count(*) from $tab_name where $where"
  cnt=`$execSql -q $instance -c "$sql;"`
  echo "Records to be trimmed: $cnt"

  if [ $cnt -gt 0 -a $dry_run -eq 0 ]; then
    if [ $force_ok -eq 0 ]; then
      confirm_yes_no "Proceed with trim operation" || return 1
    fi

    sql="update $tab_name set "
    got_first=0
    for fldname in $fld_names; do
      [ $got_first -eq 1 ] && sql="$sql, "
      sql="$sql $fldname=trim($fldname)"
      got_first=1
    done
    sql="$sql where $where"

    $execSql $instance -c "$sql;"
  fi
}


do_field_space_compress()
{
  tab_name="$1"
  shift
  fld_names=$@

  echo "Checking for extra space within $tab_name" >&2
  where=
  got_first=0
  for fldname in $fld_names; do
    [ $got_first -eq 1 ] && where="$where or "
    where="$where $fldname like '%  %'"
    got_first=1
  done

  sql="select count(*) from $tab_name where $where"
  cnt=`$execSql -q $instance -c "$sql;"`
  echo "Records to be compressed: $cnt"

  if [ $cnt -gt 0 -a $dry_run -eq 0 ]; then
    if [ $force_ok -eq 0 ]; then
      confirm_yes_no "Proceed with space compression operation" || return 1
    fi

    while [ $cnt -gt 0 ]; do
      sql="update $tab_name set "
      got_first=0
      for fldname in $fld_names; do
        [ $got_first -eq 1 ] && sql="$sql, "
        sql="$sql $fldname=replace($fldname, '  ', ' ')"
        got_first=1
      done
      sql="$sql where $where"
      $execSql $instance -c "$sql;"

      sql="select count(*) from $tab_name where $where"
      cnt=`$execSql -q $instance -c "$sql;"`
      echo "Records remaining to be compressed: $cnt"
    done
  fi
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

do_field_trim civicrm_contact display_name sort_name addressee_custom addressee_display postal_greeting_custom postal_greeting_display email_greeting_custom email_greeting_display organization_name

do_field_trim civicrm_address street_address street_number street_name street_unit supplemental_address_1 supplemental_address_2 city

do_field_space_compress civicrm_contact display_name sort_name addressee_custom addressee_display postal_greeting_custom postal_greeting_display email_greeting_custom email_greeting_display organization_name

do_field_space_compress civicrm_address street_address street_number street_name street_unit supplemental_address_1 supplemental_address_2 city

exit 0
