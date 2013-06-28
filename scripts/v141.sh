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

## 6698 data cleanup
## remove all bulk email activities
echo "removing all bulk email activity records and disabling activity type..."
sql="
  SELECT @at:=id FROM civicrm_option_group WHERE name = 'activity_type';
  SELECT @be:=value FROM civicrm_option_value WHERE option_group_id = @at AND name = 'Bulk Email';
  DELETE FROM civicrm_activity WHERE activity_type_id = @be;
  UPDATE civicrm_option_value SET is_active = 0 WHERE option_group_id = @at AND value = @be;
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

## 6722 add quick search index
echo "adding index for quick search..."
sql="ALTER TABLE civicrm_contact ADD INDEX index_quick_search(is_deleted, sort_name, id);"
$execSql -i $instance -c "$sql" -q

## 6698 insert contact view option
sql="
  SELECT @option_group_id_cvOpt := max(id) from civicrm_option_group where name = 'contact_view_options';
  INSERT INTO civicrm_option_value (option_group_id, label, value, name, grouping, filter, is_default, weight, description, is_optgroup, is_reserved, is_active, component_id)
  VALUES (@option_group_id_cvOpt, 'Mailings', 14, 'CiviMail', NULL, 0, NULL, 14, NULL, 0, 0, 1, NULL);
"
$execSql -i $instance -c "$sql" -q

sql="
  UPDATE civicrm_setting
  SET value = 's:19:\"1234561014\";'
  WHERE name = 'contact_view_options';
"
$execSql -i $instance -c "$sql" -q

## 6798 set logging report perms
sql="
  UPDATE civicrm_report_instance
  SET permission = 'access CiviCRM'
  WHERE report_id LIKE 'logging/contact%'
"
$execSql -i $instance -c "$sql" -q

## 6833 make sure mailing component settings are set
sql="
  DELETE FROM civicrm_setting
  WHERE group_name = 'Mailing Preferences'
    AND name IN ( 'profile_double_optin', 'profile_add_to_group_double_optin', 'track_civimail_replies',
      'civimail_workflow', 'civimail_server_wide_lock', 'civimail_multiple_bulk_emails', 'include_message_id',
      'write_activity_record' );
  INSERT INTO civicrm_setting (group_name, name, value, domain_id, is_domain, created_date, created_id)
  VALUES
    ('Mailing Preferences', 'profile_double_optin', 'i:0;', 1, 1, NOW(), 1),
    ('Mailing Preferences', 'profile_add_to_group_double_optin', 'i:0;', 1, 1, NOW(), 1),
    ('Mailing Preferences', 'track_civimail_replies', 'i:0;', 1, 1, NOW(), 1),
    ('Mailing Preferences', 'civimail_workflow', 'i:1;', 1, 1, NOW(), 1),
    ('Mailing Preferences', 'civimail_server_wide_lock', 'i:0;', 1, 1, NOW(), 1),
    ('Mailing Preferences', 'civimail_multiple_bulk_emails', 'i:1;', 1, 1, NOW(), 1),
    ('Mailing Preferences', 'include_message_id', 'i:0;', 1, 1, NOW(), 1),
    ('Mailing Preferences', 'write_activity_record', 'i:0;', 1, 1, NOW(), 1);
"
$execSql -i $instance -c "$sql" -q
