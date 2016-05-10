#!/bin/sh
#
# v121_deduperules.sh
#
# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2011-04-20
#
# alter default indiv fuzzy rule to include suffix
# #3657
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
drush=$script_dir/drush.sh

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

###### Begin Update Scripts ######

## add rule and update rule group ##
duperule="INSERT INTO civicrm_dedupe_rule VALUES (186, 1, 'civicrm_contact', 'suffix_id', NULL, 5);
UPDATE civicrm_dedupe_rule_group SET threshold = 21, name = 'Level 3 (street + lname + fname + city + suffix)' WHERE id = 1;"
$execSql -i $instance -c "$duperule"
