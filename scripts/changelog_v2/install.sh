#!/bin/sh
#
# install.sh - Install the new changelog summary/detail layer.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2014-08-05
# Revised: 2014-08-07
#

prog=`basename $0`
this_dir=`dirname $0`
script_dir=`cd $this_dir/..; echo $PWD`
readConfig=$script_dir/readConfig.sh
execSql=$script_dir/execSql.sh

usage() {
  echo "Usage: $prog instance" >&2
}

log() {
  echo "[`date +%F\ %T`] $@"
}

if [ $# -lt 1 ]; then
  usage
  exit 1
fi

while [ $# -gt 0 ]; do
  case "$1" in
    --help) usage; exit 0 ;;
    -*) echo "$prog: $1: Invalid option" >&2; exit 1 ;;
    *) instance="$1" ;;
  esac
  shift
done

if [ ! "$instance" ]; then
  echo "$prog: Please specify a CRM instance" >&2
  exit 1
fi

if ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

cd $this_dir

log "Creating the summary and detail tables"
$execSql -q $instance -f create_tables.sql || exit 1

log "Creating the detail conversion trigger"
$execSql -q $instance -f create_detail_conversion_trigger.sql || exit 1

log "Creating the temporary staging tables"

for f in create_temp_staging_*.sql; do
  log "-> Running $f"
  $execSql -q $instance --log --replace-macros -f $f || exit 1
done

log "Importing log data into detail table"

for f in import_*.sql; do
  log "-> Running $f"
  $execSql -q $instance --replace-macros -f $f || exit 1
done

log "Dropping the temporary staging tables"
$execSql -q $instance --log -f drop_temp_staging_tables.sql || exit 1

log "Altering the summary and detail tables"
$execSql -q $instance -f alter_tables.sql || exit 1

log "Replacing detail conversion trigger with runtime trigger"
$execSql -q $instance -f create_detail_runtime_trigger.sql || exit 1

log "Creating the summary trigger"
$execSql -q $instance -f create_summary_trigger.sql || exit 1

log "Conversion of instance [$instance] is complete"
