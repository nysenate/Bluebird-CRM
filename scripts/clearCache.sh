#!/bin/sh
#
# clearCache.sh
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-15
# Revised: 2011-12-20
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
drush=$script_dir/drush.sh
clear_all=0
tmp_only=0
tpl_only=0
wd_only=0

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--all] [--tmp-only] [--tpl-only] [--wd-only] instanceName" >&2
}

drop_temp_tables() {
  inst="$1"
  tmptabs=`$execSql -i $inst -c "show tables like 'civicrm\_%temp\_%'"`
  if [ "$tmptabs" ]; then
    tmptabs=`echo $tmptabs | tr " " ,`
    echo "Temporary tables to drop: $tmptabs"
    ( set -x
      $execSql -i $inst -c "drop table $tmptabs"
    )
  else
    echo "There are no temporary tables to be dropped."
  fi
}


if [ $# -lt 1 ]; then
  usage
  exit 1
fi

while [ $# -gt 0 ]; do
  case "$1" in
    --all) clear_all=1 ;;
    --tmp*) tmp_only=1 ;;
    --tpl*) tpl_only=1 ;;
    --wd*) wd_only=1 ;;
    -*) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
    *) instance="$1" ;;
  esac
  shift
done

if [ $clear_all -eq 1 -a $tmp_only -eq 1 ]; then
  echo "$prog: Cannot specify --all and --tmp-only at the same time." >&2
  exit 1
elif [ $clear_all -eq 1 -a $tpl_only -eq 1 ]; then
  echo "$prog: Cannot specify --all and --tpl-only at the same time." >&2
  exit 1
elif [ $clear_all -eq 1 -a $wd_only -eq 1 ]; then
  echo "$prog: Cannot specify --all and --wd-only at the same time." >&2
  exit 1
elif [ $tpl_only -eq 1 -a $wd_only -eq 1 ]; then
  echo "$prog: Cannot specify --tpl-only and --wd-only at the same time." >&2
  exit 1
fi

if ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

data_rootdir=`$readConfig --ig $instance data.rootdir` || data_rootdir="$DEFAULT_DATA_ROOTDIR"
base_domain=`$readConfig --ig $instance base.domain` || base_domain="$DEFAULT_BASE_DOMAIN"
data_basename=`$readConfig --ig $instance data.basename` || data_basename="$instance"

if [ -z "$base_domain" ]; then
  data_dirname="$data_basename"
else
  data_dirname="$data_basename.$base_domain"
fi


if [ $tmp_only -eq 1 ]; then
  drop_temp_tables $instance
  exit $?
elif [ $wd_only -eq 1 ]; then
  sql="TRUNCATE watchdog"
  ( set -x
    $execSql -i $instance -c "$sql" --drupal
  )
  exit $?
fi


echo "Clearing CiviCRM filesystem caches"
( set -x
  rm -rf $data_rootdir/$data_dirname/civicrm/templates_c/*
  rm -rf $data_rootdir/$data_dirname/civicrm/css/*
  rm -rf $data_rootdir/$data_dirname/civicrm/js/*
)

[ $tpl_only -eq 1 ] && exit 0

drop_temp_tables $instance

echo "Clearing CiviCRM database caches"
sql="
  TRUNCATE civicrm_acl_cache;
  TRUNCATE civicrm_acl_contact_cache;
  TRUNCATE civicrm_cache;
  TRUNCATE civicrm_group_contact_cache;
  TRUNCATE civicrm_menu;
  DROP TABLE IF EXISTS civicrm_task_action_temp;
  UPDATE civicrm_preferences SET navigation=null;
  UPDATE civicrm_setting SET value = null WHERE name = 'navigation';
"
[ $clear_all -eq 1 ] && sql="TRUNCATE civicrm_log; $sql"
( set -x
  $execSql -i $instance -c "$sql"
)

echo "Run Civi clear cache via drush to cover our bases"
$drush $instance cache-clear civicrm

echo "Clearing Drupal database caches"
sql="
  TRUNCATE cache;
  TRUNCATE cache_page;
  TRUNCATE cache_form;
  TRUNCATE cache_update;
  TRUNCATE cache_menu;
  TRUNCATE cache_block;
  TRUNCATE cache_filter;
  TRUNCATE sessions;
"
[ $clear_all -eq 1 ] && sql="truncate watchdog; $sql"
( set -x
  $execSql -i $instance -c "$sql" --drupal
)

echo "Running Drupal clear-cache for js/css compression clean"
$drush $instance cc css-js

echo "Clearing dashboard content"
sql="UPDATE civicrm_dashboard_contact SET content=null;"
( set -x
  $execSql -i $instance -c "$sql"
)

exit 0
