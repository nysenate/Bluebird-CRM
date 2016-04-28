#!/bin/sh
#
# v130_emailDedupe.sh
#
# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2011-08-20
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

###### Begin Upgrade Scripts ######

### CiviCRM ###

## 4175 email dedupe rule
dedupeGroup="
INSERT INTO civicrm_dedupe_rule_group (id, contact_type, threshold, level, is_default, name) VALUES
(11, 'Individual', 1, 'Strict', 0, 'NYSS - Strict Email');
INSERT INTO civicrm_dedupe_rule (dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES
(11, 'civicrm_email', 'email', NULL, 1);
;"
$execSql -i $instance -c "$dedupeGroup"
