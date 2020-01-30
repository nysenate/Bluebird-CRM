#!/bin/sh
#
# deleteEmptyEmailPhone.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2020-01-07
# Revised: 2020-01-29
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--ok] instanceName" >&2
}

force_ok=0
instance=

while [ $# -gt 0 ]; do
  case "$1" in
    --help|-h) usage; exit 0 ;;
    --ok) force_ok=1 ;;
    -*) echo "$prog: $1: Invalid option" >&2 ; usage ; exit 1 ;;
    *) instance="$1" ;;
  esac
  shift
done

if [ ! "$instance" ]; then
  echo "$prog: Must specify an instance to process cleanup" >&2
  exit 1
elif ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

email_sql="FROM civicrm_email WHERE email IS NULL OR email = ''"

phone_sql="FROM civicrm_phone WHERE (phone IS NULL OR phone = '')
                                AND (phone_ext IS NULL OR phone_ext = '')"

for tab in email phone; do
  sqlvar="${tab}_sql"
  base_sql="${!sqlvar}"

  sql="SELECT count(*) $base_sql"
  cnt=`$execSql $instance -c "$sql" -q`

  echo "$prog: [@$instance] Number of empty $tab records: $cnt"

  if [ $cnt -eq 0 ]; then
    echo "$prog: [@$instance] No $tab records need to be deleted."
  else
    if [ $force_ok -eq 0 ]; then
      echo
      echo -n "Are you sure that you want to delete $cnt $tab records ([N]/y)? "
      read ch
      case "$ch" in
        [yY]*) ;;
        *) echo "Aborting."; continue ;;
      esac
    fi

    sql="DELETE $base_sql"
    $execSql $instance -c "$sql" -q
    rc=$?
    echo "$prog: [@$instance] Deleted $cnt empty $tab records"
  fi
done

exit $rc
