#!/bin/sh
#
# v308.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2020-07-23
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

## 13498
echo "fix relationship type machine names..."

# fix prefix/suffix fields
sql="
  UPDATE civicrm_relationship_type
  SET name_a_b = 'Case Manager is'
  WHERE id = 13;

  UPDATE civicrm_relationship_type
  SET name_a_b = 'Non-District Staff is'
  WHERE id = 14;

  UPDATE civicrm_relationship_type
  SET name_a_b = 'Support Staff is'
  WHERE id = 15;
"
$execSql $instance -c "$sql" -q

## 13569
echo "create roommate relationship type"

sql="
  DELETE FROM civicrm_relationship_type WHERE name_a_b = 'roommate_is';
  INSERT INTO civicrm_relationship_type
  (name_a_b, label_a_b, name_b_a, label_b_a, contact_type_a, contact_type_b, is_active)
  VALUES
  ('roommate_is', 'Roommate is', 'roommate_is', 'Roommate is', 'Individual', 'Individual', 1);
"
$execSql $instance -c "$sql" -q

echo "$prog: resetting roles and permissions..."
$script_dir/resetRolePerms.sh $instance

## record completion
echo "$prog: upgrade process is complete."
