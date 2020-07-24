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

## record completion
echo "$prog: upgrade process is complete."
