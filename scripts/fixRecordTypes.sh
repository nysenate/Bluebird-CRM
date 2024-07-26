#!/bin/sh
#
# fixRecordTypes.sh - Set the record_type based on saved OMIS data
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2011-11-23
# Revised: 2012-07-01
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
force_ok=0
dry_run=0
update_contacts=0
verbose=0

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--dry-run] [--verbose] [--ok] [--update-contacts] instance" >&2
}

deprecated() {
  echo "This script has been deprecated due to it's dependency on the mysqludf_preg library." >&2
  echo "Should you ever need to run it again, first rewrite all calls to preg_capture() with" >&2
  echo "mysql built-in functions" >&2
}

deprecated;
exit 1


if [ $# -lt 1 ]; then
  usage
  exit 1
fi

while [ $# -gt 0 ]; do
  case "$1" in
    -h|--help) usage; exit 0 ;;
    --ok) force_ok=1 ;;
    -n|--dry-run) dry_run=1 ;;
    -u|--update*) update_contacts=1 ;;
    -v|--verbose) verbose=1 ;;
    -*) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
    *) instance="$1" ;;
  esac
  shift
done

if [ ! "$instance" ]; then
  echo "$prog: Must specify an instance to delete" >&2
  exit 1
elif ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

echo "==> Processing CRM instance [$instance]" >&2

# First, compare OMIS RT values to Bluebird record_type values, and fix
# mismatched values using the OMIS RT value as the authoritative source.

##
# preg_capture along with the mysqludf_preg library has been deprecated (or removed).
# If you ever need to use this script again, then this code
# will need to be rewritten using built-in mysql functions
##
rt_capture="preg_capture('/RT: ([0-9]+)/',n.note,1)"
tabs="civicrm_note n, civicrm_value_constituent_information_1 ci"
updt="ci.record_type_61=$rt_capture"
cond="n.entity_id=ci.entity_id and n.subject='OMIS DATA' and n.entity_table='civicrm_contact' and ifnull(ci.record_type_61,'')<>$rt_capture"
sql="select count(*) from $tabs where $cond;"
cnt=`$execSql -q $instance -c "$sql"`

echo "Number of records with mismatched OMIS and Bluebird record types: $cnt" >&2

if [ $cnt -gt 0 ]; then
  if [ $dry_run -eq 0 ]; then
    do_cleanup=1
    if [ $force_ok -eq 0 ]; then
      echo -n "Proceed with record_type fix-up operation (N/y)? "
      read ch
      case "$ch" in
        [yY]*) ;;
        *) do_cleanup=0 ;;
      esac
    fi

    if [ $do_cleanup -eq 1 ]; then
      echo "Fixing $cnt records with record_type mismatches" >&2
      sql="update $tabs set $updt where $cond;"
      $execSql -q $instance -c "$sql" || exit 1
    else
      echo "Skipping update for $cnt records" >&2
      if [ $update_contacts -eq 1 ]; then
        echo "Soft-deleted contact records will not be trashed because mismatched record_type data exists" >&2
        update_contacts=0
      fi
    fi
  elif [ $verbose -eq 1 ]; then
    echo "Contact records with mismatched OMIS RT and Bluebird record_type:" >&2
    echo "ID\tOMIS RT\tBluebird RT"
    sql="select n.entity_id, $rt_capture, ci.record_type_61 from $tabs where $cond;"
    $execSql -q $instance -c "$sql" || exit 1
  fi
else
  echo "There are no mismatched records to be fixed." >&2
fi

# If --update_contacts was selected, then update contact records with
# a record_type of 0 (soft delete) so that the is_deleted, do_not_email,
# and do_not_mail flags are all true.

if [ $update_contacts -eq 0 ]; then
  echo "Skipping contact record trashing analysis and update" >&2
  exit 0
fi

tabs="civicrm_contact c, civicrm_value_constituent_information_1 ci"
updt="c.is_deleted=1, c.do_not_email=1, c.do_not_mail=1"
cond1="c.id=ci.entity_id and ci.record_type_61=0"
cond2="$cond1 and (c.is_deleted<>1 or c.do_not_email<>1 or c.do_not_mail<>1)"

sql="select count(*) from $tabs where $cond1;"
cnt1=`$execSql -q $instance -c "$sql"`
sql="select count(*) from $tabs where $cond2;"
cnt2=`$execSql -q $instance -c "$sql"`

echo "Number of soft-deleted contact records: $cnt1" >&2
echo "Number of soft-deleted contact records that need to be trashed: $cnt2" >&2

if [ $cnt2 -gt 0 ]; then
  if [ $dry_run -eq 0 ]; then
    do_cleanup=1
    if [ $force_ok -eq 0 ]; then
      echo -n "Proceed with contact trashing operation (N/y)? "
      read ch
      case "$ch" in
        [yY]*) ;;
        *) do_cleanup=0 ;;
      esac
    fi

    if [ $do_cleanup -eq 1 ]; then
      echo "Trashing $cnt2 contacts with soft-delete record types" >&2
      sql="update $tabs set $updt where $cond2;"
      $execSql -q $instance -c "$sql" || exit 1
    else
      echo "Skipping update for $cnt2 contact records" >&2
    fi
  elif [ $verbose -eq 1 ]; then
    echo "Soft-deleted contact records that need to be trashed:" >&2
    echo "ID\tFIRST\tLAST"
    sql="select c.id, c.first_name, c.last_name from $tabs where $cond2;"
    $execSql -q $instance -c "$sql" || exit 1
  fi
else
  echo "There are no soft-deleted contact records that need to be trashed." >&2
fi

exit 0
