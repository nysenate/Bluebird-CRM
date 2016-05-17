#!/bin/sh
#
# v134.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2012-02-09
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

###### Begin Upgrade Scripts ######

### Drupal ###

# 4933 Mailing Viewer role/perm
role="INSERT INTO role (rid, name) VALUES (17, 'Mailing Viewer');"
$execSql -i $instance -c "$role" --drupal

perm="INSERT INTO permission (rid, perm) VALUES (17, 'view mass email');"
$execSql -i $instance -c "$perm" --drupal

# 4432 re-enable rules module
$drush $instance en civicrm_rules -y


### CiviCRM ###

# 4781 Register Activity Tag report
acttag="INSERT INTO civicrm_option_value (option_group_id, label, value, name, grouping, filter, is_default, weight, description, is_optgroup, is_reserved, is_active, component_id, domain_id, visibility_id) VALUES
(40, 'Activity Tag Report', 'activity/tag', 'CRM_Report_Form_ActivityTag', NULL, 0, 0, 201, 'View activities grouped by activity tags.', 0, 0, 1, NULL, NULL, NULL);"
$execSql -i $instance -c "$acttag"

# 4939 country
country="UPDATE civicrm_preferences SET address_options = '123456813' WHERE id = 1;"
$execSql -i $instance -c "$country"

# Remove old triggers to make way for new CiviCRM triggers
#$execSql -i $instance -c "DROP TRIGGER IF EXISTS shadow_address_update_trigger;"
#$execSql -i $instance -c "DROP TRIGGER IF EXISTS shadow_address_delete_trigger;"
#$execSql -i $instance -c "DROP TRIGGER IF EXISTS shadow_address_insert_trigger;"
#$execSql -i $instance -c "DROP TRIGGER IF EXISTS shadow_contact_delete_trigger;"
#$execSql -i $instance -c "DROP TRIGGER IF EXISTS shadow_contact_update_trigger;"
#$execSql -i $instance -c "DROP TRIGGER IF EXISTS shadow_contact_insert_trigger;"

# 5091 disable survey report
svy="UPDATE civicrm_option_value SET is_active = 1 WHERE name = 'CRM_Report_Form_Campaign_SurveyDetails'";
$execSql -i $instance -c "$svy"


### Cleanup ###

$script_dir/clearCache.sh $instance
