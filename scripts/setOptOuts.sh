#!/bin/sh
#
# setOptOuts.sh - Given a file of opted out e-mail addresses, set the
#                 is_opt_out field for matching contacts.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2011-09-20
# Revised: 2011-09-21
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
tmptab=temp_email_optout
optoutfile=
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
    --file|-f) shift; optoutfile="$1" ;;
    --ok) force_ok=1 ;;
    -n|--dry-run) dry_run=1 ;;
    -*) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
    *) instance="$1" ;;
  esac
  shift
done

if [ ! "$optoutfile" ]; then
  echo "$prog: Must specify the file of opted out e-mail addresses." >&2
  usage
  exit 1
elif [ ! -r "$optoutfile" ]; then
  echo "$prog: $optoutfile: File not found" >&2
  exit 1
elif ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

echo "==> Processing CRM instance [$instance]" >&2

echo "Loading opted out addresses into temporary table" >&2
sql="create table if not exists $tmptab ( email varchar(64) collate utf8_unicode_ci ); truncate table $tmptab; load data local infile '$optoutfile' into table $tmptab lines terminated by '\n' set email=lower(email);"
$execSql -i $instance -c "$sql" || exit 1

selccnt="select count(*) from civicrm_contact c"
selecnt="select count(*) from civicrm_email e, $tmptab t"
selcid="select distinct e.contact_id from civicrm_email e, $tmptab t"
emailchk="lower(e.email) = t.email"
bulkchk="and e.is_bulkmail = 1"
primchk="and e.is_primary = 1"

sql="$selecnt where $emailchk;"
ecnt1=`$execSql -q -i $instance -c "$sql"`
sql="$selecnt where $emailchk $bulkchk;"
ecnt2=`$execSql -q -i $instance -c "$sql"`
sql="$selecnt where $emailchk $primchk;"
ecnt3=`$execSql -q -i $instance -c "$sql"`
sql="$selccnt where id in ( $selcid where $emailchk );"
ccnt1=`$execSql -q -i $instance -c "$sql"`
sql="$selccnt where id in ( $selcid where $emailchk $bulkchk );"
ccnt2=`$execSql -q -i $instance -c "$sql"`
sql="$selccnt where id in ( $selcid where $emailchk $primchk );"
ccnt3=`$execSql -q -i $instance -c "$sql"`

echo "Total matching e-mail records: $ecnt1 ($ccnt1 contacts)" >&2
echo "Matching e-mail records marked BULK: $ecnt2 ($ccnt2 contacts)" >&2
echo "Matching e-mail records marked PRIMARY: $ecnt3 ($ccnt3 contacts)" >&2

if [ $ccnt1 -gt 0 -a $dry_run -eq 0 ]; then
  if [ $force_ok -eq 0 ]; then
    echo -n "Proceed with clean-up operation (N/y)? "
    read ch
    case "$ch" in
      [yY]*) ;;
      *) echo "Aborting."; exit 0 ;;
    esac
  fi

fi

exit 0
