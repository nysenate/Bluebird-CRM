#!/bin/sh
#
# v225.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2018-12-17
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

echo "$prog: Starting v2.2.5 upgrade process"

## 12639
echo "$prog: nyss #12639 - create board member relationship"
sql="
  DELETE FROM civicrm_relationship_type WHERE name_a_b = 'board_member_of';
  INSERT INTO civicrm_relationship_type
  (name_a_b, label_a_b, name_b_a, label_b_a, contact_type_a, contact_type_b, is_active)
  VALUES
  ('board_member_of', 'Board Member of', 'board_member_is', 'Board Member is', 'Individual', 'Organization', 1);
"
$execSql $instance -c "$sql" -q

## record completion
echo "$prog: Finished the v2.2.5 upgrade process"
