#!/bin/sh
#
# v223.sh
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

echo "$prog: Starting v2.2.3 upgrade process"

## 12239
echo "$prog: nyss #12239 - remove relationship type restrictions"
sql="
  UPDATE civicrm_relationship_type
  SET contact_type_a = NULL, contact_type_b = NULL
  WHERE name_a_b = 'Supervised by'
"
$execSql $instance -c "$sql" -q

## 12288
echo "$prog: nyss #12288 - create founder relationship"
sql="
  DELETE FROM civicrm_relationship_type WHERE name_a_b = 'founder_of';
  INSERT INTO civicrm_relationship_type
  (name_a_b, label_a_b, name_b_a, label_b_a, contact_type_a, contact_type_b, is_active)
  VALUES
  ('founder_of', 'Founder of', 'founded_by', 'Founded By', 'Individual', 'Organization', 1);
"
$execSql $instance -c "$sql" -q

## record completion
echo "$prog: Finished the v2.2.3 upgrade process"
