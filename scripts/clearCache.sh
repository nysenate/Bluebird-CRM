#!/bin/sh
#
# clearCache.sh
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh

if [ $# -ne 1 ]; then
  echo "Usage: $prog instanceName" >&2
  exit 1
fi

instance="$1"
dbhost=`$readConfig --group global:db --key host`
dbuser=`$readConfig --group global:db --key user`
dbpass=`$readConfig --group global:db --key pass`

echo "Clearing Drupal database caches"
sql="truncate cache; truncate cache_page; truncate cache_form; truncate cache_update; truncate cache_menu; truncate cache_block; truncate cache_filter; truncate sessions;"
$execSql senate_d_$instance "$sql"

echo "Clearing CiviCRM database caches"
sql="truncate civicrm_cache; truncate civicrm_menu; truncate civicrm_uf_match";
set -x
$execSql senate_c_$instance "$sql"

echo "Clearing CiviCRM filesystem caches"
rm -rf /data/files/$instance.crm.nysenate.gov/civicrm/templates_c/*
rm -rf /data/files/$instance.crm.nysenate.gov/civicrm/css/*
rm -rf /data/files/$instance.crm.nysenate.gov/civicrm/js/*
set +x

exit 0
