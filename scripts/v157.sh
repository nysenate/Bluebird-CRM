#!/bin/sh
#
# v157.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2015-04-14
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

app_rootdir=`$readConfig --ig $instance app.rootdir` || app_rootdir="$DEFAULT_APP_ROOTDIR"

echo "$prog: create website user id field in contacts table"
sql="
  ALTER TABLE civicrm_contact ADD web_user_id INT(10) NULL AFTER modified_date, ADD INDEX index_web_user_id (web_user_id);
"
$execSql $instance -c "$sql" -q

echo "$prog: create issues/committee/bill tagsets"
sql="
  SET FOREIGN_KEY_CHECKS=0;
  DELETE FROM civicrm_tag
  WHERE ( name = 'Website Issues' OR name = 'Website Committees' OR name = 'Website Bills' OR 'Website Petitions' )
    AND is_tagset = 1;
  INSERT INTO civicrm_tag (name, description, parent_id, is_selectable, is_reserved, is_tagset, used_for, created_id, created_date)
  VALUES
    ('Website Issues', 'Tagset for issues generated from nysenate.gov', NULL, 1, 1, 1, 'civicrm_contact', NULL, NULL),
    ('Website Committees', 'Tagset for committees generated from nysenate.gov', NULL, 1, 1, 1, 'civicrm_contact', NULL, NULL),
    ('Website Bills', 'Tagset for bills generated from nysenate.gov', NULL, 1, 1, 1, 'civicrm_contact', NULL, NULL),
    ('Website Petitions', 'Tagset for petitions generated from nysenate.gov', NULL, 1, 1, 1, 'civicrm_contact', NULL, NULL);
  SET FOREIGN_KEY_CHECKS=1;
"
$execSql $instance -c "$sql" -q

echo "$prog: create nyss_web_account table"
sql="
  DROP TABLE IF EXISTS nyss_web_account;
  CREATE TABLE IF NOT EXISTS nyss_web_account (
    id int(10) unsigned NOT NULL,
    contact_id int(10) unsigned NOT NULL,
    action varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
    created_date datetime DEFAULT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1;

  ALTER TABLE nyss_web_account
    ADD PRIMARY KEY (id),
    ADD KEY FK_nyss_web_account_contact_id (contact_id);
  ALTER TABLE nyss_web_account
    MODIFY id int(10) unsigned NOT NULL AUTO_INCREMENT;
  ALTER TABLE nyss_web_account
    ADD CONSTRAINT FK_nyss_web_account_contact_id FOREIGN KEY (contact_id) REFERENCES civicrm_contact (id) ON DELETE NO ACTION ON UPDATE NO ACTION;
"
$execSql $instance -c "$sql" -q

echo "$prog: create nyss_web_activity table"
sql="
  DROP TABLE IF EXISTS nyss_web_activity;
  CREATE TABLE IF NOT EXISTS nyss_web_activity (
    id int(10) unsigned NOT NULL,
    type varchar(50) NOT NULL,
    created_date datetime NOT NULL,
    details varchar(510) DEFAULT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1;

  ALTER TABLE nyss_web_activity
    ADD PRIMARY KEY (id),
    ADD KEY type (type);
  ALTER TABLE nyss_web_activity
    MODIFY id int(10) unsigned NOT NULL AUTO_INCREMENT;
"
$execSql $instance -c "$sql" -q

echo "$prog: create profile custom data set"
sql="
  SET FOREIGN_KEY_CHECKS=0;
  DROP TABLE IF EXISTS civicrm_value_website_profile_9;
  DELETE FROM civicrm_custom_group WHERE name = 'Website_Profile';
  DELETE FROM civicrm_custom_field WHERE custom_group_id = 9;

  CREATE TABLE IF NOT EXISTS civicrm_value_website_profile_9 (
    id int(10) unsigned NOT NULL COMMENT 'Default MySQL primary key',
    entity_id int(10) unsigned NOT NULL COMMENT 'Table that this extends',
    first_name_65 varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
    last_name_66 varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
    address_1_67 varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
    address_2_68 varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
    city_69 varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
    state_70 varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
    postal_code_71 varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
    email_72 varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
    birth_date_73 datetime DEFAULT NULL,
    gender_74 varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
    contact_me_75 tinyint(4) DEFAULT NULL,
    top_issue_76 varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
    status_77 varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
    last_modified_78 datetime DEFAULT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
  DELIMITER $$
  CREATE TRIGGER civicrm_value_website_profile_9_after_delete AFTER DELETE ON civicrm_value_website_profile_9
   FOR EACH ROW BEGIN  UPDATE civicrm_contact SET modified_date = CURRENT_TIMESTAMP WHERE id = OLD.entity_id; END
  $$
  DELIMITER ;
  DELIMITER $$
  CREATE TRIGGER civicrm_value_website_profile_9_after_insert AFTER INSERT ON civicrm_value_website_profile_9
   FOR EACH ROW BEGIN  UPDATE civicrm_contact SET modified_date = CURRENT_TIMESTAMP WHERE id = NEW.entity_id; END
  $$
  DELIMITER ;
  DELIMITER $$
  CREATE TRIGGER civicrm_value_website_profile_9_after_update AFTER UPDATE ON civicrm_value_website_profile_9
   FOR EACH ROW BEGIN  UPDATE civicrm_contact SET modified_date = CURRENT_TIMESTAMP WHERE id = NEW.entity_id; END
  $$
  DELIMITER ;

  ALTER TABLE civicrm_value_website_profile_9
    ADD PRIMARY KEY (id),
    ADD UNIQUE KEY unique_entity_id (entity_id),
    ADD KEY INDEX_contact_me_75 (contact_me_75),
    ADD KEY INDEX_top_issue_76 (top_issue_76),
  ADD KEY INDEX_status_77 (status_77);

  ALTER TABLE civicrm_value_website_profile_9
    MODIFY id int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key';

  ALTER TABLE civicrm_value_website_profile_9
    ADD CONSTRAINT FK_civicrm_value_website_profile_9_entity_id FOREIGN KEY (entity_id) REFERENCES civicrm_contact (id) ON DELETE CASCADE;

  INSERT INTO civicrm_custom_group
  (id, name, title, extends, extends_entity_column_id, extends_entity_column_value, style, collapse_display, help_pre, help_post, weight, is_active, table_name, is_multiple, min_multiple, max_multiple, collapse_adv_display, created_id, created_date, is_reserved)
  VALUES
  (9, 'Website_Profile', 'Website Profile', 'Individual', NULL, NULL, 'Tab', 0, '<p>This custom data set details the user&#39;s profile fields on the public web site.</p>', '', 7, 1, 'civicrm_value_website_profile_9', 0, NULL, NULL, 1, 2, '2015-05-07 11:50:17', 0);

  INSERT INTO civicrm_custom_field
  (id, custom_group_id, name, label, data_type, html_type, default_value, is_required, is_searchable, is_search_range, weight, help_pre, help_post, mask, attributes, javascript, is_active, is_view, options_per_line, text_length, start_date_years, end_date_years, date_format, time_format, note_columns, note_rows, column_name, option_group_id, filter)
  VALUES
(65, 9, 'First_Name', 'First Name', 'String', 'Text', NULL, 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 255, NULL, NULL, NULL, NULL, 60, 4, 'first_name_65', NULL, NULL),
(66, 9, 'Last_Name', 'Last Name', 'String', 'Text', NULL, 0, 0, 0, 2, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 255, NULL, NULL, NULL, NULL, 60, 4, 'last_name_66', NULL, NULL),
(67, 9, 'Address_1', 'Address 1', 'String', 'Text', NULL, 0, 0, 0, 3, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 255, NULL, NULL, NULL, NULL, 60, 4, 'address_1_67', NULL, NULL),
(68, 9, 'Address_2', 'Address 2', 'String', 'Text', NULL, 0, 0, 0, 4, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 255, NULL, NULL, NULL, NULL, 60, 4, 'address_2_68', NULL, NULL),
(69, 9, 'City', 'City', 'String', 'Text', NULL, 0, 0, 0, 5, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 255, NULL, NULL, NULL, NULL, 60, 4, 'city_69', NULL, NULL),
(70, 9, 'State', 'State', 'String', 'Text', NULL, 0, 0, 0, 6, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 255, NULL, NULL, NULL, NULL, 60, 4, 'state_70', NULL, NULL),
(71, 9, 'Postal_Code', 'Postal Code', 'String', 'Text', NULL, 0, 0, 0, 7, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 255, NULL, NULL, NULL, NULL, 60, 4, 'postal_code_71', NULL, NULL),
(72, 9, 'Email', 'Email', 'String', 'Text', NULL, 0, 0, 0, 8, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 255, NULL, NULL, NULL, NULL, 60, 4, 'email_72', NULL, NULL),
(73, 9, 'Birth_Date', 'Birth Date', 'Date', 'Select Date', NULL, 0, 0, 0, 9, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 255, NULL, NULL, 'mm/dd/yy', NULL, 60, 4, 'birth_date_73', NULL, NULL),
(74, 9, 'Gender', 'Gender', 'String', 'Text', NULL, 0, 0, 0, 10, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 255, NULL, NULL, NULL, NULL, 60, 4, 'gender_74', NULL, NULL),
(75, 9, 'Contact_Me', 'Contact Me', 'Boolean', 'Radio', NULL, 0, 1, 0, 11, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 255, NULL, NULL, NULL, NULL, 60, 4, 'contact_me_75', NULL, NULL),
(76, 9, 'Top_Issue', 'Top Issue', 'String', 'Text', NULL, 0, 1, 0, 12, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 255, NULL, NULL, NULL, NULL, 60, 4, 'top_issue_76', NULL, NULL),
(77, 9, 'Status', 'Status', 'String', 'Text', NULL, 0, 1, 0, 13, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 255, NULL, NULL, NULL, NULL, 60, 4, 'status_77', NULL, NULL),
(78, 9, 'Last_Modified', 'Last Modified', 'Date', 'Select Date', NULL, 0, 0, 0, 14, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 255, NULL, NULL, 'mm/dd/yy', 1, 60, 4, 'last_modified_78', NULL, NULL);

  SET FOREIGN_KEY_CHECKS=1;
"
$execSql $instance -c "$sql" -q

echo "$prog: create web survey activity type"
sql="
  DELETE FROM civicrm_option_value
  WHERE option_group_id = 2
    AND name = 'Website Survey';

  INSERT INTO civicrm_option_value (id, option_group_id, label, value, name, grouping, filter, is_default, weight, description, is_optgroup, is_reserved, is_active, component_id, domain_id, visibility_id) VALUES
(1363, 2, 'Website Survey', '90', 'Website Survey', NULL, 0, 0, 87, '<p>Survey/Questionnaire responses that were received through the public website.</p>', 0, 0, 1, NULL, NULL, NULL);
"
$execSql $instance -c "$sql" -q


echo "$prog: create profile custom data set"
sql="
  SET FOREIGN_KEY_CHECKS=0;
  DROP TABLE IF EXISTS civicrm_value_website_survey_10;
  DELETE FROM civicrm_custom_group WHERE name = 'Website_Survey';
  DELETE FROM civicrm_custom_field WHERE custom_group_id = 10;

  DROP TABLE IF EXISTS civicrm_value_website_survey_10;
  CREATE TABLE IF NOT EXISTS civicrm_value_website_survey_10 (
    id int(10) unsigned NOT NULL COMMENT 'Default MySQL primary key',
    entity_id int(10) unsigned NOT NULL COMMENT 'Table that this extends',
    survey_name_79 varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
    survey_id_80 int(11) DEFAULT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

  ALTER TABLE civicrm_value_website_survey_10
    ADD PRIMARY KEY (id),
    ADD UNIQUE KEY unique_entity_id (entity_id),
    ADD KEY INDEX_survey_name_79 (survey_name_79);

  ALTER TABLE civicrm_value_website_survey_10
    MODIFY id int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key';

  ALTER TABLE civicrm_value_website_survey_10
    ADD CONSTRAINT FK_civicrm_value_website_survey_10_entity_id FOREIGN KEY (entity_id) REFERENCES civicrm_activity (id) ON DELETE CASCADE;

  INSERT INTO civicrm_custom_group
  (id, name, title, extends, extends_entity_column_id, extends_entity_column_value, style, collapse_display, help_pre, help_post, weight, is_active, table_name, is_multiple, min_multiple, max_multiple, collapse_adv_display, created_id, created_date, is_reserved)
  VALUES
  (10, 'Website_Survey', 'Website Survey', 'Activity', NULL, '90', 'Inline', 1, '', '', 8, 1, 'civicrm_value_website_survey_10', 0, NULL, NULL, 1, 2, '2015-05-26 13:04:26', 0);

  INSERT INTO civicrm_custom_field
  (id, custom_group_id, name, label, data_type, html_type, default_value, is_required, is_searchable, is_search_range, weight, help_pre, help_post, mask, attributes, javascript, is_active, is_view, options_per_line, text_length, start_date_years, end_date_years, date_format, time_format, note_columns, note_rows, column_name, option_group_id, filter)
  VALUES
  (79, 10, 'Survey_Name', 'Survey Name', 'String', 'Text', NULL, 0, 1, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 255, NULL, NULL, NULL, NULL, 60, 4, 'survey_name_79', NULL, NULL),
  (80, 10, 'Survey_ID', 'Survey ID', 'Int', 'Text', NULL, 0, 0, 0, 2, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 255, NULL, NULL, NULL, NULL, 60, 4, 'survey_id_80', NULL, NULL);

  SET FOREIGN_KEY_CHECKS=1;
"


php $script_dir/../civicrm/scripts/logUpdateSchema.php -S $instance
php $script_dir/../civicrm/scripts/logUpdateIndexes.php -S $instance
