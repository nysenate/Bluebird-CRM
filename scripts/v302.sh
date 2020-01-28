#!/bin/sh
#
# v302.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2020-01-28
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
drush=$script_dir/drush.sh
readConfig=$script_dir/readConfig.sh

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

echo "$prog: 13251 new activity types"
for act_type in "Letter of Support" "Letter of Opposition"; do
  sql="SELECT id FROM civicrm_option_value
       WHERE name='$act_type'
         AND option_group_id = (SELECT id FROM civicrm_option_group
                                WHERE name='activity_type')"
  id=`$execSql $instance -q -c "$sql"`
  if [ "$id" ]; then
    echo "$prog: Activity type [$act_type] already exists"
  else
    echo "$prog: Creating new activity type [$act_type]"
    sql="
      SELECT @optGroup := id FROM civicrm_option_group WHERE name = 'activity_type';
      SELECT @maxVal := max(cast(value as unsigned)) FROM civicrm_option_value WHERE option_group_id = @optGroup;
      INSERT INTO civicrm_option_value
      (id, option_group_id, label, value, name, grouping, filter, is_default, weight, description, is_optgroup, is_reserved, is_active, component_id, domain_id, visibility_id)
      VALUES
      (NULL, @optGroup, '$act_type', @maxVal + 1, '$act_type', NULL, '0', NULL, @maxVal + 1, NULL, '0', '0', '1', NULL, NULL, NULL);
    "
    $execSql $instance -q -c "$sql"
  fi
done


echo "$prog: #13259 update non-admin menu block"
sql="
  UPDATE block
  SET pages = 'admin/people\r\nuser/*\r\nimportData\r\nadmin/config/development/maintenance\r\nbackupdata'
  WHERE delta = 'menu-non-admin-drupal-menu';
"
$execSql $instance -c "$sql" --drupal -q

echo "$prog: #13210 enable backup extension"
$drush $instance cvapi extension.install key=gov.nysenate.backup --quiet

## record completion
echo "$prog: upgrade process is complete."
