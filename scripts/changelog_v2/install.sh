#!/bin/sh
#
# install.sh - Install the new changelog summary/detail layer.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2014-08-05
# Revised: 2014-08-08 - Added --skip-XXXX options to control conversion.
#

prog=`basename $0`
this_dir=`dirname $0`
script_dir=`cd $this_dir/..; echo $PWD`
logging_dir=`cd $this_dir/../../civicrm/custom/php/CRM/Logging; echo $PWD`
readConfig=$script_dir/readConfig.sh
execSql=$script_dir/execSql.sh

usage() {
  echo "Usage: $prog [--skip-STAGE [--skip-STAGE ...]] instance" >&2
  echo "  where STAGE is one of:" >&2
  echo "create-tables, create-detail-conversion-trigger," >&2
  echo "create-temp-staging-tables, import-data, drop-temp-tables," >&2
  echo "alter-tables, create-detail-runtime-trigger, create-summary-trigger" >&2
  echo
  echo "Use --skip-post-import as shorthand for --skip-drop-temp-tables," >&2
  echo "--skip-alter-tables, --skip-create-detail-runtime-trigger, and" >&2
  echo "--skip-create-summary-trigger" >&2
}

log() {
  echo "[`date +%F\ %T`] $@"
}

if [ $# -lt 1 ]; then
  usage
  exit 1
fi

skip_create_tables=0
skip_create_detail_conversion_trigger=0
skip_create_temp_staging_tables=0
skip_import_data=0
skip_drop_temp_tables=0
skip_alter_tables=0
skip_create_detail_runtime_trigger=0
skip_create_summary_trigger=0
skip_post_import=0

while [ $# -gt 0 ]; do
  case "$1" in
    --help) usage; exit 0 ;;
    --skip-create-tab*) skip_create_tables=1 ;;
    --skip-create-detail-conv*) skip_create_detail_conversion_trigger=1 ;;
    --skip-create-temp*) skip_create_temp_staging_tables=1 ;;
    --skip-import*) skip_import_data=1 ;;
    --skip-drop*) skip_drop_temp_tables=1 ;;
    --skip-alter*) skip_alter_tables=1 ;;
    --skip-create-detail-run*) skip_create_detail_runtime_trigger=1 ;;
    --skip-create-sum*) skip_create_summary_trigger=1 ;;
    --skip-post-import) skip_drop_temp_tables=1
                        skip_alter_tables=1
                        skip_create_detail_runtime_trigger=1
                        skip_create_summary_trigger=1 ;;
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

if [ $skip_create_tables -eq 1 ]; then
  log "Skipping creation of summary and detail tables"
else
  log "Creating the summary and detail tables"
  $execSql -q $instance -f create_tables.sql || exit 1
fi

if [ $skip_create_detail_conversion_trigger -eq 1 ]; then
  log "Skipping creation of the detail conversion trigger"
else
  log "Creating the detail conversion trigger"
  $execSql -q $instance -f create_detail_conversion_trigger.sql || exit 1
fi

if [ $skip_create_temp_staging_tables -eq 1 ]; then
  log "Skipping creation of the temporary staging tables"
else
  log "Creating the temporary staging tables"

  for f in create_temp_staging_*.sql; do
    log "-> Running $f"
    $execSql -q $instance --log --replace-macros -f $f || exit 1
  done
fi

if [ $skip_import_data -eq 1 ]; then
  log "Skipping importation of log data into detail table"
else
  log "Importing log data into detail table"

  for f in import_*.sql; do
    log "-> Running $f"
    $execSql -q $instance --replace-macros -f $f || exit 1
  done
fi

if [ $skip_drop_temp_tables -eq 1 ]; then
  log "Skipping the dropping of temporary staging tables"
else
  log "Dropping the temporary staging tables"
  $execSql -q $instance --log -f drop_temp_staging_tables.sql || exit 1
fi

if [ $skip_alter_tables -eq 1 ]; then
  log "Skipping the alteration of summary and details tables"
else
  log "Altering the summary and detail tables"
  $execSql -q $instance -f alter_tables.sql || exit 1
fi

if [ $skip_create_detail_runtime_trigger -eq 1 ]; then
  log "Skipping the creation of the detail runtime trigger"
else
  log "Replacing detail conversion trigger with runtime trigger"
  $execSql -q $instance -f $logging_dir/create_detail_runtime_trigger.sql || exit 1
fi

if [ $skip_create_summary_trigger -eq 1 ]; then
  log "Skipping the creation of the summary trigger"
else
  log "Creating the summary trigger"
  $execSql -q $instance -f $logging_dir/create_summary_trigger.sql || exit 1
fi

log "Conversion of instance [$instance] is complete"
