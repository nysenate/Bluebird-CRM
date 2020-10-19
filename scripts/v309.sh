#!/bin/sh
#
# v309.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2020-10-19
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

## 13600
echo "create friend/neighbor relationship type"

sql="
  DELETE FROM civicrm_relationship_type WHERE name_a_b = 'friend_is';
  INSERT INTO civicrm_relationship_type
  (name_a_b, label_a_b, name_b_a, label_b_a, contact_type_a, contact_type_b, is_active)
  VALUES
  ('friend_is', 'Friend is', 'friend_is', 'Friend is', 'Individual', 'Individual', 1);

  DELETE FROM civicrm_relationship_type WHERE name_a_b = 'neighbor_is';
  INSERT INTO civicrm_relationship_type
  (name_a_b, label_a_b, name_b_a, label_b_a, contact_type_a, contact_type_b, is_active)
  VALUES
  ('neighbor_is', 'Neighbor is', 'neighbor_is', 'Neighbor is', 'Individual', 'Individual', 1);
"
$execSql $instance -c "$sql" -q

## record completion
echo "$prog: upgrade process is complete."
