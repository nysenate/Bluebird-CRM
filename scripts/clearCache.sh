#!/bin/sh
#
# clearCache.sh
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-15
# Revised: 2010-09-27
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh

. $script_dir/defaults.sh

if [ $# -ne 1 ]; then
  echo "Usage: $prog instanceName" >&2
  exit 1
fi

db_civicrm_prefix=`$readConfig --group globals db.civicrm.prefix`
db_drupal_prefix=`$readConfig --group globals db.drupal.prefix`

instance="$1"

[ "$db_civicrm_prefix" ] || db_civicrm_prefix="$DEFAULT_DB_CIVICRM_PREFIX"
[ "$db_drupal_prefix" ] || db_drupal_prefix="$DEFAULT_DB_DRUPAL_PREFIX"

echo "Clearing CiviCRM database caches"
sql="truncate civicrm_cache; truncate civicrm_menu; truncate civicrm_uf_match;"
( set -x
  $execSql "$db_civicrm_prefix$instance" -c "$sql"
)

echo "Clearing CiviCRM filesystem caches"
( set -x
  rm -rf /data/files/$instance.crm.nysenate.gov/civicrm/templates_c/*
  rm -rf /data/files/$instance.crm.nysenate.gov/civicrm/css/*
  rm -rf /data/files/$instance.crm.nysenate.gov/civicrm/js/*
)

echo "Clearing Drupal database caches"
sql="truncate cache; truncate cache_page; truncate cache_form; truncate cache_update; truncate cache_menu; truncate cache_block; truncate cache_filter; truncate sessions;"
( set -x
  $execSql "$db_drupal_prefix$instance" -c "$sql"
)

exit 0
