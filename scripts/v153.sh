#!/bin/sh
#
# v153.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2014-03-24
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

app_rootdir=`$readConfig --ig $instance app.rootdir` || app_rootdir="$DEFAULT_APP_ROOTDIR"

#echo "upgrade CiviCRM core to v4.4.4..."
#$drush $instance civicrm-upgrade-db

echo "5581: create fields for mailing subscription..."
sql="ALTER TABLE civicrm_email ADD mailing_categories VARCHAR(765) NULL DEFAULT NULL, ADD INDEX (mailing_categories);"
$execSql $instance -c "$sql" -q

sql="ALTER TABLE civicrm_mailing ADD category VARCHAR(255) NULL DEFAULT NULL, ADD INDEX (category);"
$execSql $instance -c "$sql" -q

sql="
  DELETE FROM civicrm_option_group WHERE name = 'mailing_categories';
  INSERT INTO civicrm_option_group (name, title, description, is_reserved, is_active)
    VALUES ('mailing_categories', 'Mailing Categories', 'Mailing Categories', '1', '1');
  SELECT @optGrp:=id FROM civicrm_option_group WHERE name = 'mailing_categories';
  INSERT INTO civicrm_option_value
    (option_group_id, label, value, name, grouping, filter, is_default, weight, description, is_optgroup, is_reserved, is_active, component_id, domain_id, visibility_id)
    VALUES
      (@optGrp, 'Budget Updates', '1', 'Budget Updates', NULL, '0', NULL, '1', '', '0', '0', '1', NULL, NULL, NULL),
      (@optGrp, 'Community and Special Event Notices', '2', 'Community and Special Event Notices', NULL, '0', NULL, '2', '', '0', '0', '1', NULL, NULL, NULL),
      (@optGrp, 'Emergency and Public Safety Alerts', '3', 'Emergency and Public Safety Alerts', NULL, '0', NULL, '3', '', '0', '0', '1', NULL, NULL, NULL),
      (@optGrp, 'Issue/Bill Updates', '4', 'Issue/Bill Updates', NULL, '0', NULL, '4', '', '0', '0', '1', NULL, NULL, NULL),
      (@optGrp, 'Newsletters', '5', 'Newsletters', NULL, '0', NULL, '5', '', '0', '0', '1', NULL, NULL, NULL),
      (@optGrp, 'Press Releases', '6', 'Press Releases', NULL, '0', NULL, '6', '', '0', '0', '1', NULL, NULL, NULL),
      (@optGrp, 'All Other Messages', '7', 'All Other Messages', NULL, '0', NULL, '7', '', '0', '0', '1', NULL, NULL, NULL);
"
$execSql $instance -c "$sql" -q

echo "5581: create mailing subscription self-management group..."
sql="
  DELETE FROM civicrm_group WHERE name = 'Mailing_Subscription_Self_Manage';
  INSERT INTO civicrm_group
  (name, title, description, source, saved_search_id, is_active, visibility, group_type, cache_date, refresh_date, parents, children, is_hidden, is_reserved, created_id)
  VALUES
  ('Mailing_Subscription_Self_Manage', 'Mailing Subscription Self-Management', 'Contacts who have used the mailing subscription self-management form.', NULL, NULL, 1, 'User and User Admin Only', '2', NULL, NULL, NULL, NULL, 0, 1, 1);
"
$execSql $instance -c "$sql" -q

echo "5581: create mailing subscription profile and fields..."
sql="
  SELECT @grp:=id FROM civicrm_group WHERE name = 'Mailing_Subscription_Self_Manage';
  SELECT @ufgrp:=id FROM civicrm_uf_group WHERE name = 'Mass_Email_Subscriptions';
  DELETE FROM civicrm_uf_field WHERE uf_group_id = @ufgrp;
  DELETE FROM civicrm_uf_join WHERE uf_group_id = @ufgrp;
  DELETE FROM civicrm_uf_group WHERE name = 'Mass_Email_Subscriptions';
  INSERT INTO civicrm_uf_group
    (id, is_active, group_type, title, description, help_pre, help_post, limit_listings_group_id, post_URL, add_to_group_id, add_captcha, is_map, is_edit_link, is_uf_link, is_update_dupe, cancel_URL, is_cms_user, notify, is_reserved, name, created_id, created_date, is_proximity_search)
    VALUES
    (19, 1, 'Individual,Contact', 'Mass Email Subscriptions', NULL, '<p>Please review your mailing subscription options below. Note that your selections are specific to each email address on file. If you would like to leave a note with additional communication preference requests, please do so below.</p>', NULL, NULL, NULL, @grp, 0, 0, 0, 0, 1, 'http://www.nysenate.gov', 0, NULL, 1, 'Mass_Email_Subscriptions', 1, NOW(), 0);
  SELECT @ufgrp:=id FROM civicrm_uf_group WHERE name = 'Mass_Email_Subscriptions';
  INSERT INTO civicrm_uf_field
    (uf_group_id, field_name, is_active, is_view, is_required, weight, help_post, help_pre, visibility, in_selector, is_searchable, location_type_id, phone_type_id, label, field_type, is_reserved, is_multi_summary)
    VALUES
    (@ufgrp, 'first_name', 1, 1, 0, 1, '', '', 'User and User Admin Only', 0, 0, NULL, NULL, 'First Name', 'Individual', NULL, 0),
    (@ufgrp, 'last_name', 1, 1, 0, 2, '', '', 'User and User Admin Only', 0, 0, NULL, NULL, 'Last Name', 'Individual', NULL, 0),
    (@ufgrp, 'note', 1, 0, 0, 3, '', '', 'User and User Admin Only', 0, 0, NULL, NULL, 'Note', 'Contact', NULL, 0);
  INSERT INTO civicrm_uf_join (is_active, module, weight, uf_group_id)
    VALUES (1, 'Profile', 1, @ufgrp);
"
$execSql $instance -c "$sql" -q

echo "5581: set all existing mailings to public visibility..."
sql="UPDATE civicrm_mailing SET visibility = 'Public Pages'"
$execSql $instance -c "$sql" -q

echo "7723: create hash column in mailing table..."
sql="
  ALTER TABLE civicrm_mailing
  ADD COLUMN hash varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Key for validating requests related to this mailing.',
  ADD INDEX index_hash (hash);
"
$execSql $instance -c "$sql" -q

echo "resetting roles and permissions..."
$script_dir/resetRolePerms.sh $instance

echo "7761: rebuilding word replacement list..."
$execSql $instance -f $app_rootdir/scripts/sql/wordReplacement.sql -q

echo "5581: enable public theme..."
$drush $instance pm-enable BluebirdPublic -y

echo "7799: set geocode through interface to Google..."
sql="
  UPDATE civicrm_domain
  SET config_backend = REPLACE(config_backend, 's:4:\"SAGE\";', 's:0:\"\";')
  WHERE id = 1;
"
$execSql $instance -c "$sql" -q

echo "7830: add open status for activities..."
sql="
  SELECT @actStatus:=id FROM civicrm_option_group WHERE name = 'activity_status';
  SELECT @maxval:=max(cast(value as unsigned)) FROM civicrm_option_value WHERE option_group_id = @actStatus;
  UPDATE civicrm_option_value
    SET weight = weight + 1, is_default = 0
    WHERE option_group_id = @actStatus;
  INSERT INTO civicrm_option_value
    (option_group_id, label, value, name, is_default, weight, is_active)
  VALUES
    (@actStatus, 'Open', @maxval + 1, 'Open', 1, 1, 1);
"
$execSql $instance -c "$sql" -q
