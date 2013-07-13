#!/bin/sh
#
# setOptOuts.sh - Given a file of opted out e-mail addresses, set the
#                 is_opt_out field for matching contacts.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2011-09-20
# Revised: 2011-09-23
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
tmpetab=temp_email_optout
emailfile=
force_ok=0
dry_run=0

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--dry-run] [--ok] -f opt_out_file instanceName" >&2
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
  echo "$prog: Must specify the file of opted out e-mail addresses." >&2
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

echo "Loading opted out addresses into temporary table" >&2
sql="drop table if exists $tmpetab; create table $tmpetab ( email varchar(64) collate utf8_unicode_ci, index ( email) ); load data local infile '$emailfile' into table $tmpetab lines terminated by '\n' set email=lower(email);"
$execSql -q $instance -c "$sql" || exit 1

selccnt="select count(*) from civicrm_contact c"
selecnt="select count(*) from civicrm_email e, $tmpetab t"
selcid="select distinct e.contact_id from civicrm_email e, $tmpetab t"
emailchk="e.email = t.email"
bulkchk="and e.is_bulkmail = 1"
primchk="and e.is_primary = 1"
bulkonly="select contact_id from civicrm_email where is_primary=0 and is_bulkmail=1"

echo "Counting total matching e-mails" >&2
sql="$selecnt where $emailchk;"
ecnt1=`$execSql -q $instance -c "$sql"`
echo "Counting total matching bulk e-mails" >&2
sql="$selecnt where $emailchk $bulkchk;"
ecnt2=`$execSql -q $instance -c "$sql"`
echo "Counting total matching primary e-mails" >&2
sql="$selecnt where $emailchk $primchk;"
ecnt3=`$execSql -q $instance -c "$sql"`
echo "Counting total matching contacts" >&2
sql="$selccnt where id in ( $selcid where $emailchk );"
ccnt1=`$execSql -q $instance -c "$sql"`
echo "Counting total matching contacts with bulk e-mails" >&2
sql="$selccnt where id in ( $selcid where $emailchk $bulkchk );"
ccnt2=`$execSql -q $instance -c "$sql"`
echo "Counting total matching contacts with primary e-mails but no bulk e-mails" >&2
sql="$selccnt where id in ( $selcid where $emailchk $primchk and e.contact_id not in ( $bulkonly ) );"
ccnt3=`$execSql -q $instance -c "$sql"`

# The master condition for determining opted out contacts.
cond="id in ( $selcid where $emailchk $bulkchk union all ( $selcid where $emailchk $primchk and e.contact_id not in ( $bulkonly ) ) ) and is_opt_out=0"

echo "Counting matching contacts with either matching bulk e-mail or matching primary with no bulk e-mail"
sql="$selccnt where $cond;"
optoutcnt=`$execSql -q $instance -c "$sql"`

echo "Total matching e-mail records: $ecnt1 ($ccnt1 contacts)" >&2
echo "Matching e-mail records marked BULK: $ecnt2 ($ccnt2 contacts)" >&2
echo "Matching e-mail records marked PRIMARY: $ecnt3 ($ccnt3 non-bulk contacts)" >&2
echo "Final opt-out count: $optoutcnt" >&2

if [ $optoutcnt -gt 0 -a $dry_run -eq 0 ]; then
  do_optout=1
  if [ $force_ok -eq 0 ]; then
    echo -n "Proceed with clean-up operation (N/y)? "
    read ch
    case "$ch" in
      [yY]*) ;;
      *) do_optout=0 ;;
    esac
  fi

  if [ $do_optout -eq 1 ]; then
    echo "Activating opt-out status for $optoutcnt contacts" >&2
    sql="update civicrm_contact set is_opt_out=1 where $cond;"
    $execSql -q $instance -c "$sql" || exit 1
  else
    echo "Skipping update of opt-out status for $optoutcnt contacts" >&2
  fi

fi

echo "Dropping temporary table" >&2
sql="drop table $tmpetab;"
$execSql -q $instance -c "$sql" || exit 1

exit 0
