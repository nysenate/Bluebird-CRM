#!/bin/sh
#
# fixSoftDelete.sh - Set the "soft delete" record_type based on saved OMIS data
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2011-11-23
# Revised: 2011-11-23
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
force_ok=0
dry_run=0
verbose=0

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--dry-run] [--verbose] [--ok] instanceName" >&2
}


if [ $# -lt 1 ]; then
  usage
  exit 1
fi

while [ $# -gt 0 ]; do
  case "$1" in
    --ok) force_ok=1 ;;
    -n|--dry-run) dry_run=1 ;;
    -v|--verbose) verbose=1 ;;
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


## 2977 soft delete
soft="
UPDATE civicrm_value_constituent_information_1 cvci
  JOIN civicrm_note cn ON ( cvci.entity_id = cn.entity_id AND
                            cn.entity_table = 'civicrm_contact' AND
                            cn.subject = 'OMIS DATA' )
SET cvci.record_type_61 = preg_capture('/RT:\s([\d])/', cn.note, 1);"
$execSql -i $instance -c "$soft"

trash="
UPDATE civicrm_contact cc
  JOIN civicrm_value_constituent_information_1 cvci
    ON ( cc.id = cvci.entity_id AND
         cvci.record_type_61 = 0 )
SET cc.is_deleted = 1, cc.do_not_email = 1, cc.do_not_mail = 1;"
$execSql -i $instance -c "$trash"


cnt4=`$execSql -q -i $instance -c "$sql"`

echo "Total e-mail records: $cnt1"
echo "Invalid e-mail records: $cnt2"
echo "Invalid e-mail records that are on hold: $cnt3"
echo "Invalid e-mail records that are not on hold: $cnt4"

if [ $cnt4 -gt 0 ]; then
  if [ $dry_run -eq 0 ]; then
    do_cleanup=1
    if [ $force_ok -eq 0 ]; then
      echo -n "Proceed with e-mail flagging operation (N/y)? "
      read ch
      case "$ch" in
        [yY]*) ;;
        *) do_cleanup=0 ;;
      esac
    fi

    if [ $do_cleanup -eq 1 ]; then
      echo "Placing $cnt4 e-mails on hold" >&2
      sql="update civicrm_email
           set on_hold=1
           where $condinvalidactive;"
      $execSql -q -i $instance -c "$sql" || exit 1
    else
      echo "Skipping update for $cnt4 addresses" >&2
    fi
  elif [ $verbose -eq 1 ]; then
    echo "Invalid e-mail addresses that are on hold:"
    sql="select email from civicrm_email where $condinvalidinactive order by email;"
    $execSql -q -i $instance -c "$sql" || exit 1
    echo "Invalid e-mail addresses that are not on hold:"
    sql="select email from civicrm_email where $condinvalidactive order by email;"
    $execSql -q -i $instance -c "$sql" || exit 1
  fi
fi

exit 0
