#!/bin/sh
#
# flagBadEmails.sh - Analyze email addresses for validity
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2011-10-25
# Revised: 2011-11-18
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
rebuildCache=$script_dir/rebuildCachedValues.sh
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

selecnt="select count(*) from civicrm_email"
condemail="email<>''"
condinvalid="$condemail and email not rlike '^[A-Za-z0-9._%+-]+@([A-Za-z0-9-]+\\\.)+[A-Za-z]{2,4}\$'"
condinvalidactive="$condinvalid and on_hold=0"
condinvalidinactive="$condinvalid and on_hold=1"

echo "Counting total e-mails" >&2
sql="$selecnt where $condemail;"
cnt1=`$execSql -q $instance -c "$sql"`
echo "Counting e-mails that are invalid" >&2
sql="$selecnt where $condinvalid;"
cnt2=`$execSql -q $instance -c "$sql"`
echo "Counting e-mails that are invalid and on hold" >&2
sql="$selecnt where $condinvalidinactive;"
cnt3=`$execSql -q $instance -c "$sql"`
echo "Counting e-mails that are invalid and not on hold" >&2
sql="$selecnt where $condinvalidactive;"
cnt4=`$execSql -q $instance -c "$sql"`

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
      $execSql -q $instance -c "$sql" || exit 1
    else
      echo "Skipping update for $cnt4 addresses" >&2
    fi
  elif [ $verbose -eq 1 ]; then
    echo "Invalid e-mail addresses that are on hold:"
    sql="select email from civicrm_email where $condinvalidinactive order by email;"
    $execSql -q $instance -c "$sql" || exit 1
    echo "Invalid e-mail addresses that are not on hold:"
    sql="select email from civicrm_email where $condinvalidactive order by email;"
    $execSql -q $instance -c "$sql" || exit 1
  fi
fi

exit 0
