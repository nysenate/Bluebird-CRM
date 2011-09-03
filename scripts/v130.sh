#!/bin/sh
#
# v130.sh
#
# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2011-08-22
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

data_rootdir=`$readConfig --ig $instance data.rootdir` || data_rootdir="$DEFAULT_DATA_ROOTDIR"
webdir=`$readConfig --global drupal.rootdir` || webdir="$DEFAULT_DRUPAL_ROOTDIR"
base_domain=`$readConfig --ig $instance base.domain` || base_domain="$DEFAULT_BASE_DOMAIN"

###### Begin Upgrade Scripts ######

## run civicrm db upgrade using drush
$drush $instance civicrm-upgrade-db


### Drupal ###

## add reserved tag perm to admin, office admin
perms_upd="UPDATE permission SET perm = 'create users, delete users with role Analytics User, delete users with role Conference Services, delete users with role Data Entry, delete users with role Mailing Approver, delete users with role Mailing Creator, delete users with role Mailing Scheduler, delete users with role Office Administrator, delete users with role Office Manager, delete users with role Print Production, delete users with role SOS, delete users with role Staff, delete users with role Volunteer, edit users with role Analytics User, edit users with role Conference Services, edit users with role Data Entry, edit users with role Mailing Approver, edit users with role Mailing Creator, edit users with role Mailing Scheduler, edit users with role Office Administrator, edit users with role Office Manager, edit users with role Print Production, edit users with role SOS, edit users with role Staff, edit users with role Volunteer, access CiviCRM, access CiviMail, access CiviReport, access Contact Dashboard, access Report Criteria, access all cases and activities, access all custom data, access deleted contacts, access my cases and activities, access uploaded files, add contacts, administer CiviCRM, administer Reports, administer dedupe rules, approve mailings, create mailings, delete activities, delete contacts, delete in CiviCase, delete in CiviMail, edit all contacts, edit groups, import contacts, merge duplicate contacts, profile listings, profile listings and forms, profile view, schedule mailings, view all activities, view all contacts, delete contacts permanently, export print production files, assign roles, access administration pages, administer users' WHERE rid = 4;
UPDATE permission SET perm = 'create users, delete users with role Analytics User, delete users with role Conference Services, delete users with role Data Entry, delete users with role Office Manager, delete users with role Print Production, delete users with role SOS, delete users with role Staff, delete users with role Volunteer, edit users with role Analytics User, edit users with role Conference Services, edit users with role Data Entry, edit users with role Office Manager, edit users with role Print Production, edit users with role SOS, edit users with role Staff, edit users with role Volunteer, access CiviCRM, access CiviReport, access Contact Dashboard, access Report Criteria, access all cases and activities, access all custom data, access deleted contacts, access my cases and activities, access uploaded files, add contacts, administer district, administer Reports, delete activities, delete contacts, delete in CiviCase, delete in CiviMail, edit all contacts, edit groups, merge duplicate contacts, profile listings, profile listings and forms, profile view, view all activities, view all contacts, delete contacts permanently, assign roles, access administration pages, administer users' WHERE rid = 9;"
$execSql -i $instance -c "$perms_upd" --drupal

## 4240 reset rules
rules="TRUNCATE TABLE rules_rules;
INSERT INTO rules_rules (name, data) VALUES
('rules_notify_creator_of_approval', 0x613a393a7b733a353a222374797065223b733a343a2272756c65223b733a343a2223736574223b733a32323a226576656e745f6d61696c696e675f617070726f766564223b733a363a22236c6162656c223b733a32363a224e6f746966792043726561746f72206f6620417070726f76616c223b733a373a2223616374697665223b693a313b733a373a2223776569676874223b733a313a2230223b733a31313a222363617465676f72696573223b613a303a7b7d733a373a2223737461747573223b733a363a22637573746f6d223b733a31313a2223636f6e646974696f6e73223b613a313a7b693a313b613a353a7b733a353a222374797065223b733a393a22636f6e646974696f6e223b733a393a222373657474696e6773223b613a313a7b733a31333a2223617267756d656e74206d6170223b613a313a7b733a31343a22617070726f76616c737461747573223b733a373a226d61696c696e67223b7d7d733a353a22236e616d65223b733a34303a2272756c65735f636f6e646974696f6e5f6369766963726d5f6d61696c696e675f617070726f766564223b733a353a2223696e666f223b613a333a7b733a353a226c6162656c223b733a32353a22417070726f76616c205374617475733a20417070726f766564223b733a393a22617267756d656e7473223b613a313a7b733a31343a22617070726f76616c737461747573223b613a323a7b733a343a2274797065223b733a373a226d61696c696e67223b733a353a226c6162656c223b733a383a22417070726f766564223b7d7d733a363a226d6f64756c65223b733a31353a224369766943524d204d61696c696e67223b7d733a373a2223776569676874223b643a303b7d7d733a383a2223616374696f6e73223b613a313a7b693a303b613a353a7b733a353a222374797065223b733a363a22616374696f6e223b733a393a222373657474696e6773223b613a353a7b733a323a22746f223b733a32323a227b6d61696c696e672e63726561746f72456d61696c7d223b733a343a2266726f6d223b733a303a22223b733a373a227375626a656374223b733a35323a225374617475733a207b6d61696c696e672e617070726f76616c5374617475737d20287b6d61696c696e672e7375626a6563747d29223b733a373a226d657373616765223b733a3437393a223c703e54686520666f6c6c6f77696e6720656d61696c20686173206265656e203c7374726f6e673e7b6d61696c696e672e617070726f76616c5374617475737d3c2f7374726f6e673e3a207b6d61696c696e672e6e616d657d3c2f703e0d0a0d0a3c703e54686520666f6c6c6f77696e6720656d61696c20617070726f76616c2f72656a656374696f6e206d65737361676520686173206265656e20696e636c756465643a3c6272202f3e0d0a7b6d61696c696e672e617070726f76616c4e6f74657d3c2f703e0d0a0d0a3c703e596f752068617665206e6f206675727468657220737465707320746f2074616b652e2054686520656d61696c2077696c6c20656e74657220746865206d61696c696e6720717565756520616e642062652064656c6976657265642073686f72746c792e204e6f7465207468617420656d61696c73206d617920657870657269656e636520736f6d652064656c6179206261736564206f6e207468652073697a65206f662074686520656d61696c20616e6420766f6c756d65206f6620726563697069656e74732e3c2f703e0d0a0d0a3c703e54686520636f6e74656e74206f662074686520656d61696c2069733a3c2f703e0d0a3c6469763e0d0a7b6d61696c696e672e68746d6c7d0d0a3c2f6469763e223b733a31333a2223617267756d656e74206d6170223b613a313a7b733a373a226d61696c696e67223b733a373a226d61696c696e67223b7d7d733a353a22236e616d65223b733a33393a2272756c65735f616374696f6e5f6369766963726d5f6d61696c696e675f73656e645f656d61696c223b733a353a2223696e666f223b613a333a7b733a353a226c6162656c223b733a31303a2253656e6420456d61696c223b733a393a22617267756d656e7473223b613a313a7b733a373a226d61696c696e67223b613a323a7b733a343a2274797065223b733a373a226d61696c696e67223b733a353a226c6162656c223b733a31303a2253656e6420456d61696c223b7d7d733a363a226d6f64756c65223b733a31353a224369766943524d204d61696c696e67223b7d733a373a2223776569676874223b643a303b7d7d7d),
('rules_notify_creator_of_rejection', 0x613a393a7b733a353a222374797065223b733a343a2272756c65223b733a343a2223736574223b733a32323a226576656e745f6d61696c696e675f617070726f766564223b733a363a22236c6162656c223b733a32373a224e6f746966792043726561746f72206f662052656a656374696f6e223b733a373a2223616374697665223b693a313b733a373a2223776569676874223b733a313a2230223b733a31313a222363617465676f72696573223b613a303a7b7d733a373a2223737461747573223b733a363a22637573746f6d223b733a31313a2223636f6e646974696f6e73223b613a313a7b693a313b613a353a7b733a353a222374797065223b733a393a22636f6e646974696f6e223b733a393a222373657474696e6773223b613a313a7b733a31333a2223617267756d656e74206d6170223b613a313a7b733a31343a22617070726f76616c737461747573223b733a373a226d61696c696e67223b7d7d733a353a22236e616d65223b733a34303a2272756c65735f636f6e646974696f6e5f6369766963726d5f6d61696c696e675f72656a6563746564223b733a353a2223696e666f223b613a333a7b733a353a226c6162656c223b733a32353a22417070726f76616c205374617475733a2052656a6563746564223b733a393a22617267756d656e7473223b613a313a7b733a31343a22617070726f76616c737461747573223b613a323a7b733a343a2274797065223b733a373a226d61696c696e67223b733a353a226c6162656c223b733a383a2252656a6563746564223b7d7d733a363a226d6f64756c65223b733a31353a224369766943524d204d61696c696e67223b7d733a373a2223776569676874223b643a303b7d7d733a383a2223616374696f6e73223b613a313a7b693a303b613a353a7b733a353a2223696e666f223b613a333a7b733a353a226c6162656c223b733a31303a2253656e6420456d61696c223b733a393a22617267756d656e7473223b613a313a7b733a373a226d61696c696e67223b613a323a7b733a343a2274797065223b733a373a226d61696c696e67223b733a353a226c6162656c223b733a31303a2253656e6420456d61696c223b7d7d733a363a226d6f64756c65223b733a31353a224369766943524d204d61696c696e67223b7d733a353a22236e616d65223b733a33393a2272756c65735f616374696f6e5f6369766963726d5f6d61696c696e675f73656e645f656d61696c223b733a393a222373657474696e6773223b613a353a7b733a323a22746f223b733a32323a227b6d61696c696e672e63726561746f72456d61696c7d223b733a343a2266726f6d223b733a303a22223b733a373a227375626a656374223b733a35323a225374617475733a207b6d61696c696e672e617070726f76616c5374617475737d20287b6d61696c696e672e7375626a6563747d29223b733a373a226d657373616765223b733a3530373a223c703e54686520666f6c6c6f77696e6720656d61696c20686173206265656e203c7374726f6e673e7b6d61696c696e672e617070726f76616c5374617475737d3c2f7374726f6e673e3a207b6d61696c696e672e6e616d657d3c2f703e0d0a0d0a3c703e54686520666f6c6c6f77696e6720656d61696c20617070726f76616c2f72656a656374696f6e206d65737361676520686173206265656e20696e636c756465643a3c6272202f3e0d0a7b6d61696c696e672e617070726f76616c4e6f74657d3c2f703e0d0a0d0a3c703e596f752077696c6c2066696e64207468652072656a656374656420656d61696c20696e20426c75656269726420756e6465722074686520647261667420656d61696c206d616e6167656d656e7420706167652e20596f752063616e2072657669657720616e64206564697420746865206d61696c20686572653a207b6d61696c696e672e6564697455726c7d2e204f6e636520796f7527766520757064617465642074686520656d61696c20796f752077696c6c206e65656420746f2072657363686564756c6520697420616e64207375626d697420666f7220617070726f76616c2e3c2f703e0d0a0d0a3c703e54686520636f6e74656e74206f662074686520656d61696c2069733a3c2f703e0d0a3c6469763e0d0a7b6d61696c696e672e68746d6c7d0d0a3c2f6469763e223b733a31333a2223617267756d656e74206d6170223b613a313a7b733a373a226d61696c696e67223b733a373a226d61696c696e67223b7d7d733a353a222374797065223b733a363a22616374696f6e223b733a373a2223776569676874223b643a303b7d7d7d);"
$execSql -i $instance -c "$rules" --drupal


### CiviCRM ###

## 4210 autocomplete search options
autocomplete="UPDATE civicrm_preferences SET contact_autocomplete_options = '125' WHERE id = 1;"
$execSql -i $instance -c "$autocomplete"

## 4214 add access CiviMail to nav perm
navigation="UPDATE civicrm_navigation SET permission = 'create mailings,schedule mailings,access CiviMail' WHERE name = 'Archived Emails';"
$execSql -i $instance -c "$navigation"

## 3814 create privacy option note
customgroup="INSERT INTO civicrm_custom_group (id, name, title, extends, extends_entity_column_id, extends_entity_column_value, style, collapse_display, help_pre, help_post, weight, is_active, table_name, is_multiple, min_multiple, max_multiple, collapse_adv_display, created_id, created_date) VALUES
(8, 'Contact_Details', 'Contact Details', 'Contact', NULL, NULL, 'Inline', 0, '', '', 6, 1, 'civicrm_value_contact_details_8', 0, NULL, NULL, 0, 1, '2011-08-22 23:21:02');"
$execSql -i $instance -c "$customgroup"

customfield="INSERT INTO civicrm_custom_field (id, custom_group_id, name, label, data_type, html_type, default_value, is_required, is_searchable, is_search_range, weight, help_pre, help_post, mask, attributes, javascript, is_active, is_view, options_per_line, text_length, start_date_years, end_date_years, date_format, time_format, note_columns, note_rows, column_name, option_group_id) VALUES
(64, 8, 'Privacy_Options_Note', 'Privacy Options Note', 'Memo', 'TextArea', NULL, 0, 1, 0, 1, NULL, NULL, NULL, 'rows=4, cols=60', NULL, 1, 0, NULL, 255, NULL, NULL, NULL, NULL, 60, 3, 'privacy_options_note_64', NULL);"
$execSql -i $instance -c "$customfield"

customtable="CREATE TABLE civicrm_value_contact_details_8 (
  id int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
  entity_id int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  privacy_options_note_64 text COLLATE utf8_unicode_ci,
  PRIMARY KEY (id),
  UNIQUE KEY unique_entity_id (entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
ALTER TABLE civicrm_value_contact_details_8
  ADD CONSTRAINT FK_civicrm_value_contact_details_8_entity_id FOREIGN KEY (entity_id) REFERENCES civicrm_contact (id) ON DELETE CASCADE;"
$execSql -i $instance -c "$customtable"

## 3983 add context to activity report
dashcontext="UPDATE civicrm_dashboard SET url = 'civicrm/report/instance/23&reset=1&section=2&snippet=4&context=dashlet' WHERE url = 'civicrm/report/instance/23&reset=1&section=2&snippet=4';
UPDATE civicrm_dashboard SET url = 'civicrm/report/instance/24&reset=1&section=2&snippet=4&context=dashlet' WHERE url = 'civicrm/report/instance/24&reset=1&section=2&snippet=4';"
$execSql -i $instance -c "$dashcontext"

## 4248 update full screen urls
dashreport="
UPDATE civicrm_dashboard
SET fullscreen_url = IF( url LIKE '%context=dashlet', CONCAT(url,'Fullscreen'), CONCAT(url,'&context=dashletFullscreen') )
WHERE fullscreen_url IS NULL;"
$execSql -i $instance -c "$dashreport"

## 4222 email seeds group
emailgroup="INSERT INTO civicrm_group ( name, title, description, is_active, visibility, group_type )
VALUES ( 'Email_Seeds', 'Email Seeds', 'Add contacts to this group to be automatically included in all broadcast emails sent from Bluebird.',  '1', 'User and User Admin Only', '2');"
$execSql -i $instance -c "$emailgroup"


### Cleanup ###

$script_dir/clearCache.sh $instance
