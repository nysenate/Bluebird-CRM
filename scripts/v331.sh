#!/bin/sh
#
# v331.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2021-11-01
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

data_rootdir=`$readConfig --ig $instance data.rootdir` || data_rootdir="$DEFAULT_DATA_ROOTDIR"
pubfiles_dir="$data_rootdir/$instance/pubfiles"

if ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

## 14355 create new case type
echo "$prog: create new case type"
sql="
  INSERT INTO civicrm_case_type
  (name, title, description, is_active, is_reserved, weight, definition)
  VALUES
  ('government_service_problem_federal', 'Government Service Problem - Federal', 'Problem with a federal government entity.', 1, 0, 9, NULL);
"
$execSql $instance -c "$sql" -q

## record completion
echo "$prog: upgrade process is complete."
