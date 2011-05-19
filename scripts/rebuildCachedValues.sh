#!/bin/sh
#
# rebuildCachedValues.sh - Rebuild certain CiviCRM cached values.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2011-04-12
# Revised: 2011-05-19
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
force_ok=0

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--ok] instanceName" >&2
}

if [ $# -lt 1 ]; then
  usage
  exit 1
fi

while [ $# -gt 0 ]; do
  case "$1" in
    --ok) force_ok=1 ;;
    -*) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
    *) instance="$1" ;;
  esac
  shift
done

if ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

echo "About to re-cache all sort_name and display_name fields in CRM instance [$instance]."

if [ $force_ok -eq 0 ]; then
  echo -n "Are you sure that you wish to proceed ([N]/y)? "
  read ch
  case "$ch" in
    [yY]*) ;;
    *) echo "Aborted."; exit 0 ;;
  esac
fi

sql="update civicrm_contact set
  sort_name = trim(concat(
                ifnull(last_name,''), ',',
                if(first_name<>'',concat(' ',first_name),''),
                if(middle_name<>'',concat(' ',middle_name),''),
                ifnull(
                  (select if(label<>'',concat(', ', label),'')
                  from civicrm_option_value
                  where value=suffix_id and option_group_id=7),'')
              )),
  display_name = trim(concat(
                ifnull(
                  (select if(label<>'',label,'') from civicrm_option_value
                   where value=prefix_id and option_group_id=6),''),
                if(first_name<>'',concat(' ',first_name),''),
                if(middle_name<>'',concat(' ',middle_name),''),
                if(last_name<>'',concat(' ',last_name),''),
                ifnull(
                  (select if(label<>'',concat(', ', label),'')
                  from civicrm_option_value
                  where value=suffix_id and option_group_id=7),'')
                ))
where contact_type='Individual'"

$execSql -i $instance -c "$sql;"

exit $?
