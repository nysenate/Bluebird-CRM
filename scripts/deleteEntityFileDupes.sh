#!/bin/sh
#
# deleteEntityFileDupes.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2018-08-07
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
  echo "$prog: Must specify an instance to delete" >&2
  exit 1
elif ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

base_sql="
  FROM civicrm_entity_file ef1
  INNER JOIN civicrm_entity_file ef2
  WHERE ef1.id > ef2.id
    AND ef1.entity_table = ef2.entity_table
    AND ef1.entity_id = ef2.entity_id
    AND ef1.file_id = ef2.file_id;
"

sql="SELECT count(*) $base_sql"
cnt=`$execSql $instance -c "$sql" -q`
echo "$prog: [@$instance] Number of duplicate entity_file records: $cnt"

if [ $cnt -eq 0 ]; then
  echo "$prog: [@$instance] No records need to be deleted; exiting"
  exit 0
fi

if [ $force_ok -eq 0 ]; then
  echo
  echo -n "Are you sure that you want to delete $cnt records ([N]/y)? "
  read ch
  case "$ch" in
    [yY]*) ;;
    *) echo "Aborting."; exit 0 ;;
  esac
fi

sql="DELETE ef1 $base_sql"
$execSql $instance -c "$sql" -q
rc=$?

echo "$prog: [@$instance] Deleted $cnt duplicate entity_file records"

exit $rc
