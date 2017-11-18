#!/bin/sh
#
# v161.sh
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
drush=$script_dir/drush.sh
app_rootdir=`$readConfig --ig $instance app.rootdir` || app_rootdir="$DEFAULT_APP_ROOTDIR"

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

data_rootdir=`$readConfig --ig $instance data.rootdir` || data_rootdir="$DEFAULT_DATA_ROOTDIR"
app_rootdir=`$readConfig --ig $instance app.rootdir` || app_rootdir="$DEFAULT_APP_ROOTDIR"
webdir=`$readConfig --global drupal.rootdir` || webdir="$DEFAULT_DRUPAL_ROOTDIR"
base_domain=`$readConfig --ig $instance base.domain` || base_domain="$DEFAULT_BASE_DOMAIN"
db_basename=`$readConfig --ig $instance db.basename` || db_basename="$instance"
civi_db_prefix=`$readConfig --ig $instance db.civicrm.prefix` || civi_db_prefix="$DEFAULT_BASE_DOMAIN"
log_db_prefix=`$readConfig --ig $instance db.log.prefix` || log_db_prefix="$DEFAULT_BASE_DOMAIN"
cdb="$civi_db_prefix$db_basename"
ldb=$log_db_prefix$db_basename;

## migrate messages matched_to and activity_id column to separate table
echo "$prog: create messages_matched table"
sql="
  CREATE TABLE nyss_inbox_messages_matched (
    id int(10) NOT NULL,
    message_id int(10) NOT NULL,
    matched_id int(10) NOT NULL,
    activity_id int(10) NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
  ALTER TABLE nyss_inbox_messages_matched
    ADD PRIMARY KEY (id),
    ADD KEY message_id (message_id),
    ADD KEY matched_id (matched_id),
    ADD KEY activity_id (activity_id);
  ALTER TABLE nyss_inbox_messages_matched
    MODIFY id int(10) NOT NULL AUTO_INCREMENT;
  ALTER TABLE nyss_inbox_messages_matched
    ADD UNIQUE idx_message_matched_unique (message_id, matched_id);
"
$execSql $instance -c "$sql" -q

echo "$prog: migrate data to messages_matched table"
sql="
  INSERT INTO nyss_inbox_messages_matched
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

## record completion
echo "$prog: upgrade process is complete."
