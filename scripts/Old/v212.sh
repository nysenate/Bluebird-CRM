#!/bin/sh
#
# v211.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2018-02-02
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
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

## 11723 relationship types
echo "$prog: add/update relationship types"
sql="
  INSERT IGNORE INTO civicrm_relationship_type
  (name_a_b, label_a_b, name_b_a, label_b_a, description, contact_type_a, contact_type_b, contact_sub_type_a, contact_sub_type_b, is_reserved, is_active)
  VALUES
  ('Client of', 'Client of', 'Client for', 'Client for', NULL, NULL, NULL, NULL, NULL, NULL, 1);

  UPDATE civicrm_relationship_type
  SET contact_type_b = NULL
  WHERE name_a_b = 'Organization of';
"
$execSql $instance -c "$sql" -q

## record completion
echo "$prog: upgrade process is complete."
