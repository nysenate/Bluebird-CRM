#!/bin/sh
#
# v132_omis_rules.sh
#
# Project: BluebirdCRM
# Author: Graylin Kim
# Organization: New York State Senate
# Date: 2011-10-07
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

###### Begin Upgrade Scripts ######

$execSql -i $instance -c "
    INSERT INTO civicrm_dedupe_rule_group
      (contact_type, threshold, level, is_default, name)
    VALUES
      ('Individual', 15, 'Strict', 0, 'Individual Omis');

    -- This user variable lets us be more flexible instead
    -- of chosing a specific id and hoping for the best.
    SELECT @last_dedupe_rule_id:=LAST_INSERT_ID();

    INSERT INTO civicrm_dedupe_rule
      (dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight)
    VALUES
      (@last_dedupe_rule_id, 'civicrm_contact', 'first_name', NULL, 5),
      (@last_dedupe_rule_id, 'civicrm_contact', 'last_name', NULL, 5),
      (@last_dedupe_rule_id, 'civicrm_address', 'street_address', NULL, 5);
"
