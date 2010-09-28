#!/bin/sh
#
# dumpInstance.sh - Perform a MySQL dump for a CRM instance
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-12
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

instance="$1"
db_civicrm_prefix=`$readConfig --ig $instance db.civicrm.prefix` || db_civicrm_prefix="$DEFAULT_DB_CIVICRM_PREFIX"
db_drupal_prefix=`$readConfig --ig $instance db.drupal.prefix` || db_drupal_prefix="$DEFAULT_DB_DRUPAL_PREFIX"
errcode=0

echo "Dumping CiviCRM database for instance [$instance]"
( set -x
  $execSql --dump $db_civicrm_prefix$instance > $db_civicrm_prefix$instance.sql
) || errcode=$(($errcode | 1))

echo "Dumping Drupal database for instance [$instance]"
( set -x
  $execSql --dump $db_drupal_prefix$instance > $db_drupal_prefix$instance.sql
) || errcode=$(($errcode | 2))

exit $errcode
