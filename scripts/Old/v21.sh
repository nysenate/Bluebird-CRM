#!/bin/sh
#
# v21.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2017-11-18
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

## migrate messages matched_to and activity_id column to separate table
echo "$prog: create messages_matched table"
sql="
  CREATE TABLE IF NOT EXISTS nyss_inbox_messages_matched (
    id int(10) NOT NULL AUTO_INCREMENT,
    message_id int(10) NOT NULL,
    matched_id int(10) NOT NULL,
    activity_id int(10) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY idx_message_matched_unique (message_id, matched_id),
    KEY message_id (message_id),
    KEY matched_id (matched_id),
    KEY activity_id (activity_id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
"
$execSql $instance -c "$sql" -q

echo "$prog: migrate data to messages_matched table"
sql="
  INSERT IGNORE INTO nyss_inbox_messages_matched
  (message_id, matched_id, activity_id)
  SELECT message_id, matched_to, ANY_VALUE(activity_id) FROM nyss_inbox_messages GROUP BY message_id, matched_to;
"
$execSql $instance -c "$sql" -q

echo "$prog: remove matched_to and activity_id columns"
sql="
  ALTER TABLE nyss_inbox_messages DROP matched_to;
  ALTER TABLE nyss_inbox_messages DROP activity_id;
"
$execSql $instance -c "$sql" -q

echo "$prog: remove duplicate message rows"
sql="
  DELETE FROM nyss_inbox_messages
  USING nyss_inbox_messages, nyss_inbox_messages im2
  WHERE nyss_inbox_messages.id > im2.id
    AND nyss_inbox_messages.message_id = im2.message_id
"
$execSql $instance -c "$sql" -q

echo "$prog: insert birth date quicksearch option"
sql="
  SELECT @option_group_id_acsOpt := max(id) from civicrm_option_group where name = 'contact_autocomplete_options';
  SELECT @option_group_id_acConRef := max(id) from civicrm_option_group where name = 'contact_reference_options';

  DELETE FROM civicrm_option_value
  WHERE name = 'birth_date'
    AND option_group_id IN (@option_group_id_acsOpt, @option_group_id_acConRef);

  INSERT INTO civicrm_option_value
  (option_group_id, label, value, name, grouping, filter, is_default, weight, description, is_optgroup, is_reserved, is_active, component_id, visibility_id, icon)
  VALUES
  (@option_group_id_acsOpt, 'Birth Date', 9, 'birth_date', NULL, 0, NULL, 9, NULL, 0, 0, 1, NULL, NULL, NULL),
  (@option_group_id_acConRef, 'Birth Date', 9, 'birth_date', NULL, 0, NULL, 9, NULL, 0, 0, 1, NULL, NULL, NULL);
"
$execSql $instance -c "$sql" -q

## record completion
echo "$prog: upgrade process is complete."
