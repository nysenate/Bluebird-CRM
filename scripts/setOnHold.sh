#!/bin/sh
#
# setOnHold.sh - Given a file of undeliverable e-mail addresses, set the
#                on_hold field for all matching e-mails in the CRM.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2011-10-28
# Revised: 2011-10-28
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
tmpetab=temp_email_invalid
emailfile=
force_ok=0
dry_run=0

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--dry-run] [--ok] -f invalid_email_file instanceName" >&2
}


if [ $# -lt 1 ]; then
  usage
  exit 1
fi

while [ $# -gt 0 ]; do
  case "$1" in
    --file|-f) shift; emailfile="$1" ;;
    --ok) force_ok=1 ;;
    -n|--dry-run) dry_run=1 ;;
    -*) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
    *) instance="$1" ;;
  esac
  shift
done

if [ ! "$emailfile" ]; then
  echo "$prog: Must specify the file of invalid e-mail addresses." >&2
  usage
  exit 1
elif [ ! -r "$emailfile" ]; then
  echo "$prog: $emailfile: File not found" >&2
  exit 1
elif ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

echo "==> Processing CRM instance [$instance]" >&2

echo "Loading invalid e-mail addresses into temporary table" >&2
sql="drop table if exists $tmpetab; create table $tmpetab ( email varchar(64) collate utf8_unicode_ci, index ( email) ); load data local infile '$emailfile' into table $tmpetab lines terminated by '\n' set email=lower(email);"
$execSql -q $instance -c "$sql" || exit 1

selecnt="select count(*) from civicrm_email e, $tmpetab t"
emailchk="e.email = t.email"
bulkchk="and e.is_bulkmail = 1"
primchk="and e.is_primary = 1"
holdchk="and e.on_hold = 0"

echo "Counting total matching e-mails" >&2
sql="$selecnt where $emailchk;"
ecnt1=`$execSql -q $instance -c "$sql"`
echo "Counting total matching bulk e-mails" >&2
sql="$selecnt where $emailchk $bulkchk;"
ecnt2=`$execSql -q $instance -c "$sql"`
echo "Counting total matching primary e-mails" >&2
sql="$selecnt where $emailchk $primchk;"
ecnt3=`$execSql -q $instance -c "$sql"`
echo "Counting total matching e-mails that are not on hold" >&2
sql="$selecnt where $emailchk $holdchk;"
ecnt4=`$execSql -q $instance -c "$sql"`


echo "Total matching e-mail records: $ecnt1" >&2
echo "[Matching e-mail records marked BULK: $ecnt2]" >&2
echo "[Matching e-mail records marked PRIMARY: $ecnt3]" >&2
echo "Matching e-mail records that are not ON-HOLD: $ecnt4" >&2

if [ $ecnt4 -gt 0 -a $dry_run -eq 0 ]; then
  do_update=1
  if [ $force_ok -eq 0 ]; then
    echo -n "Proceed with clean-up operation (N/y)? "
    read ch
    case "$ch" in
      [yY]*) ;;
      *) do_update=0 ;;
    esac
  fi

  if [ $do_update -eq 1 ]; then
    echo "Activating on-hold status for $ecnt4 e-mail addresses" >&2
    sql="update civicrm_email e, $tmpetab t set e.on_hold=1 where $emailchk $holdchk;"
    $execSql -q $instance -c "$sql" || exit 1
  else
    echo "Skipping update of on-hold status for $ecnt4 contacts" >&2
  fi

fi

echo "Dropping temporary table" >&2
sql="drop table $tmpetab;"
$execSql -q $instance -c "$sql" || exit 1

exit 0
