#!/bin/sh
#
# v141.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2013-05-08
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
drush=$script_dir/drush.sh

. $script_dir/defaults.sh

if [ $# -ne 1 ]; then
  echo "Usage: $prog instanceName" >&2
  exit 1
fi

instance="$1"

if ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

data_rootdir=`$readConfig --ig $instance data.rootdir` || data_rootdir="$DEFAULT_DATA_ROOTDIR"
app_rootdir=`$readConfig --ig $instance app.rootdir` || app_rootdir="$DEFAULT_APP_ROOTDIR"
webdir=`$readConfig --global drupal.rootdir` || webdir="$DEFAULT_DRUPAL_ROOTDIR"
base_domain=`$readConfig --ig $instance base.domain` || base_domain="$DEFAULT_BASE_DOMAIN"
db_basename=`$readConfig --ig $instance db.basename` || db_basename="$instance"
log_db_prefix=`$readConfig --ig $instance db.log.prefix` || log_db_prefix="$DEFAULT_BASE_DOMAIN"
civi_db_prefix=`$readConfig --ig $instance db.civicrm.prefix` || civi_db_prefix="$DEFAULT_BASE_DOMAIN"
cdb="$civi_db_prefix$db_basename"

## data cleanup related to 6698
## remove all bulk email activities
echo "removing all bulk email activity records..."
sql="
  SELECT @at:=id FROM civicrm_option_group WHERE name = 'activity_type';
  SELECT @be:=value FROM civicrm_option_value WHERE option_group_id = @at AND name = 'Bulk Email';
  DELETE FROM civicrm_activity WHERE activity_type_id = @be;
"
$execSql -i $instance -c "$sql" -q

## run convertLogInnoDB.sh to ensure activity tables are converted to InnoDB
echo "converting activity log tables to InnoDB if necessary"
$script_dir/convertLogInnoDB.sh $instance

## remove all activity log records
echo "removing all bulk email activity log records..."
sql="
  SELECT @at:=id FROM log_civicrm_option_group WHERE name = 'activity_type' LIMIT 1;
  SELECT @be:=value FROM log_civicrm_option_value WHERE option_group_id = @at AND name = 'Bulk Email' LIMIT 1;
  DELETE FROM log_civicrm_activity WHERE activity_type_id = @be;
  DELETE log_civicrm_activity_assignment
  FROM log_civicrm_activity_assignment
  LEFT JOIN log_civicrm_activity
    ON log_civicrm_activity_assignment.activity_id = log_civicrm_activity.id
  WHERE log_civicrm_activity.id IS NULL;
  DELETE log_civicrm_activity_target
  FROM log_civicrm_activity_target
  LEFT JOIN log_civicrm_activity
    ON log_civicrm_activity_target.activity_id = log_civicrm_activity.id
  WHERE log_civicrm_activity.id IS NULL;
"
$execSql -i $instance -c "$sql" --log -q
