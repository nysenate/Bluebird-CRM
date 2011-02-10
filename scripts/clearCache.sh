#!/bin/sh
#
# clearCache.sh
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-15
# Revised: 2010-09-30
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
drush=$script_dir/drush.sh
clear_all=0

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--all] instanceName" >&2
}

if [ $# -lt 1 ]; then
  usage
  exit 1
fi

while [ $# -gt 0 ]; do
  case "$1" in
    --all) clear_all=1 ;;
    -*) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
    *) instance="$1" ;;
  esac
  shift
done

if ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

data_rootdir=`$readConfig --ig $instance data.rootdir` || data_rootdir="$DEFAULT_DATA_ROOTDIR"
base_domain=`$readConfig --ig $instance base.domain` || base_domain="$DEFAULT_BASE_DOMAIN"

echo "Clearing CiviCRM database caches"
sql="truncate civicrm_acl_cache; truncate civicrm_acl_contact_cache; truncate civicrm_cache; truncate civicrm_group_contact_cache; truncate civicrm_menu; truncate civicrm_uf_match; truncate civicrm_task_action_temp; update civicrm_preferences set navigation=null;"
[ $clear_all -eq 1 ] && sql="truncate civicrm_log; $sql"
( set -x
  $execSql -i $instance -c "$sql"
)

echo "Clearing CiviCRM filesystem caches"
( set -x
  rm -rf $data_rootdir/$instance.$base_domain/civicrm/templates_c/*
  rm -rf $data_rootdir/$instance.$base_domain/civicrm/css/*
  rm -rf $data_rootdir/$instance.$base_domain/civicrm/js/*
)

echo "Clearing Drupal database caches"
sql="truncate cache; truncate cache_page; truncate cache_form; truncate cache_update; truncate cache_menu; truncate cache_block; truncate cache_filter; truncate sessions;"
[ $clear_all -eq 1 ] && sql="truncate watchdog; $sql"
( set -x
  $execSql -i $instance -c "$sql" --drupal
)

echo "Running Drupal clear-cache for js/css compression clean"
$drush $instance cc css+js

exit 0
