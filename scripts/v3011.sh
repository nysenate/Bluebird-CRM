#!/bin/sh
#
# v3011.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2018-06-26
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
sql="
  SELECT @optGroup := id FROM civicrm_option_group WHERE name = 'activity_type';
  SELECT @maxVal := max(value) FROM civicrm_option_value WHERE option_group_id = @optGroup;
  DELETE FROM civicrm_option_value
  WHERE option_group_id = @optGroup
    AND (name = 'Letter of Support' OR name = 'Letter of Opposition');
  INSERT INTO civicrm_option_value
  (id, option_group_id, label, value, name, grouping, filter, is_default, weight, description, is_optgroup, is_reserved, is_active, component_id, domain_id, visibility_id)
  VALUES
  (NULL, @optGroup, 'Letter of Support', @maxVal + 1, 'Letter of Support', NULL, '0', NULL, @maxVal + 1, NULL, '0', '0', '1', NULL, NULL, NULL),
  (NULL, @optGroup, 'Letter of Opposition', @maxVal + 1, 'Letter of Opposition', NULL, '0', NULL, @maxVal + 1, NULL, '0', '0', '1', NULL, NULL, NULL);
"
$execSql $instance -c "$sql" -q

## record completion
echo "$prog: upgrade process is complete."
