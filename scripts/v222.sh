#!/bin/sh
#
# v222.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2018-10-01
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

echo "$prog: Starting v2.2.2 upgrade process"

## 12190
echo "$prog: nyss #12190 - case medium"
sql="
  SELECT @option_group_id := max(id) FROM civicrm_option_group WHERE name = 'encounter_medium';

  DELETE FROM civicrm_option_value
  WHERE name = 'social_media' AND option_group_id IN (@option_group_id);

  INSERT INTO civicrm_option_value
  (option_group_id, label, value, name, grouping, filter, is_default, weight, description, is_optgroup, is_reserved, is_active, component_id, visibility_id, icon)
  VALUES
  (@option_group_id, 'Social Media', 6, 'social_media', NULL, 0, NULL, 6, NULL, 0, 0, 1, NULL, NULL, NULL);
"
$execSql $instance -c "$sql" -q

## record completion
echo "$prog: Finished the v2.2.2 upgrade process"
