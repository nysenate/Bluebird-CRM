#!/bin/sh
#
# rebuildCachedValues.sh - Rebuild certain CiviCRM cached values.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2011-04-12
# Revised: 2011-08-15
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
all_target_fields="sort_name display_name street_address"

# By default, re-cache all cache-able fields (sort_name, display_name,
# street_address).  Note that addressee/postal/email_greeting_display values
# are cached using the updateGreetings script.
target_fields=
# We normally rebuild only cached fields that are NULL.
rebuild_all_recs=0
# Only rebuild cached fields if a key component field is not NULL.
rebuild_smart=0
force_ok=0
dry_run=0


. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--field-sortname] [--field-displayname] [--field-streetaddress] [--rebuild-all] [--rebuild-smart] [--ok] [--dry-run] instanceName" >&2
}

if [ $# -lt 1 ]; then
  usage
  exit 1
fi

while [ $# -gt 0 ]; do
  case "$1" in
    -h|--help) usage; exit 0 ;;
    --field-sortname) target_fields="$target_fields sort_name" ;;
    --field-displayname) target_fields="$target_fields display_name" ;;
    --field-streetaddress) target_fields="$target_fields street_address" ;;
    --rebuild-all*) rebuild_all_recs=1 ;;
    --rebuild-smart) rebuild_smart=1 ;;
    --ok) force_ok=1 ;;
    -n|--dry-run) dry_run=1 ;;
    -*) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
    *) instance="$1" ;;
  esac
  shift
done

if [ ! "$instance" ]; then
  echo "$prog: Must specify a CRM instance" >&2
  exit 1
elif ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

[ "$target_fields" ] || target_fields="$all_target_fields"
[ $rebuild_all_recs -eq 1 ] && txt="all" || txt="NULL/empty"

echo "About to re-cache $txt $target_fields fields in CRM instance [$instance]."

if [ $force_ok -eq 0 -a $dry_run -eq 0 ]; then
  echo -n "Are you sure that you wish to proceed ([N]/y)? "
  read ch
  case "$ch" in
    [yY]*) ;;
    *) echo "Aborted."; exit 0 ;;
  esac
fi


if echo $target_fields | grep -q 'sort_name'; then
  echo "Re-caching $txt sort_name fields..."
  cond="where contact_type='Individual'"
  [ $rebuild_all_recs -eq 0 ] && cond="$cond and isnull(nullif(sort_name,''))"
  [ $rebuild_smart -eq 1 ] && cond="$cond and last_name<>''"
  newval="trim(if(last_name<>'' or first_name<>'',
               concat(
                 ifnull(last_name,''), ',',
                 if(first_name<>'',concat(' ',first_name),''),
                 if(middle_name<>'',concat(' ',middle_name),''),
                 ifnull(
                   (select if(label<>'',concat(', ', label),'')
                   from civicrm_option_value
                   where value=suffix_id and option_group_id=7),'') ),
               (select email from civicrm_email
                where contact_id=civicrm_contact.id and is_primary=1)))"

  if [ $dry_run -eq 1 ]; then
    sql="select id, sort_name, first_name, middle_name, last_name, $newval from civicrm_contact $cond"
  else
    sql="update civicrm_contact set sort_name = $newval $cond"
  fi

  $execSql $instance -c "$sql;"
  [ $? -ne 0 ] && echo "$prog: ERROR re-caching sort_name" >&2
fi
  

if echo $target_fields | grep -q 'display_name'; then
  echo "Re-caching $txt display_name fields..."
  cond="where contact_type='Individual'"
  [ $rebuild_all_recs -eq 0 ] && cond="$cond and isnull(nullif(display_name,''))"
  [ $rebuild_smart -eq 1 ] && cond="$cond and last_name<>''"
  newval="trim(if(last_name<>'' or first_name<>'',
               concat(
                 ifnull(
                   (select if(label<>'',label,'') from civicrm_option_value
                    where value=prefix_id and option_group_id=6),''),
                 if(first_name<>'',concat(' ',first_name),''),
                 if(middle_name<>'',concat(' ',middle_name),''),
                 if(last_name<>'',concat(' ',last_name),''),
                 ifnull(
                   (select if(label<>'',concat(', ', label),'')
                    from civicrm_option_value
                    where value=suffix_id and option_group_id=7),'') ),
               (select email from civicrm_email
                where contact_id=civicrm_contact.id and is_primary=1)))"

  if [ $dry_run -eq 1 ]; then
    sql="select id, display_name, first_name, middle_name, last_name, $newval from civicrm_contact $cond"
  else
    sql="update civicrm_contact set display_name = $newval $cond"
  fi

  $execSql $instance -c "$sql;"
  [ $? -ne 0 ] && echo "$prog: ERROR re-caching display_name" >&2
fi


if echo $target_fields | grep -q 'street_address'; then
  echo "Re-caching $txt street_address fields..."
  cond="where 1=1"
  [ $rebuild_all_recs -eq 0 ] && cond="$cond and isnull(nullif(street_address,''))"
  [ $rebuild_smart -eq 1 ] && cond="$cond and street_name<>''"
  newval="trim(concat(
               if(street_number>=0,street_number,''),
               if(street_number_suffix<>'',street_number_suffix,''),
               if(street_name<>'',concat(' ',street_name),''),
               if(street_unit<>'',concat(' ',street_unit),'') ))"

  if [ $dry_run -eq 1 ]; then
    sql="select id, street_address, street_number, street_name, street_unit, $newval from civicrm_address $cond"
  else
    sql="update civicrm_address set street_address = $newval $cond"
  fi

  $execSql $instance -c "$sql;"
  [ $? -ne 0 ] && echo "$prog: ERROR re-caching street_address" >&2
fi

exit $?
