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
    contact_id int(10) unsigned NOT NULL,
    type varchar(50) NOT NULL,
    created_date datetime NOT NULL,
    details varchar(510) DEFAULT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1;

  ALTER TABLE nyss_web_activity
    ADD PRIMARY KEY (id),
    ADD KEY type (type),
    ADD KEY contact_id (contact_id);
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
    verification_78 varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
    last_modified_79 datetime DEFAULT NULL
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
    ADD KEY INDEX_verification_78 (verification_78),
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
(78, 9, 'Verification', 'Verification', 'String', 'Multi-Select', NULL, 0, 1, 0, 14, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 255, NULL, NULL, NULL, NULL, 60, 4, 'verification_78', NULL, NULL),
(79, 9, 'Last_Modified', 'Last Modified', 'Date', 'Select Date', NULL, 0, 0, 0, 15, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 255, NULL, NULL, 'mm/dd/yy', 1, 60, 4, 'last_modified_79', NULL, NULL);

  SET FOREIGN_KEY_CHECKS=1;
"
$execSql $instance -c "$sql" -q

echo "$prog: create verification option list"
sql="
  DELETE FROM civicrm_option_group
  WHERE name = 'web_verification';

  INSERT INTO civicrm_option_group
  (name, title, description, is_reserved, is_active)
  VALUES
  ('web_verification', 'Verification', NULL, 1, 1);

  SELECT @optgrp:=id FROM civicrm_option_group WHERE name = 'web_verification';

  INSERT INTO civicrm_option_value
  (option_group_id, label, value, name, grouping, filter, is_default, weight, description, is_optgroup, is_reserved, is_active, component_id, domain_id, visibility_id)
  VALUES
  (@optgrp, 'Email', 'Email', 'Email', NULL, NULL, 0, 1, NULL, 0, 0, 1, NULL, NULL, NULL),
  (@optgrp, 'Facebook', 'Facebook', 'Facebook', NULL, NULL, 0, 2, NULL, 0, 0, 1, NULL, NULL, NULL),
  (@optgrp, 'Postcard', 'Postcard', 'Postcard', NULL, NULL, 0, 3, NULL, 0, 0, 1, NULL, NULL, NULL);
"
$execSql $instance -c "$sql" -q

echo "$prog: create web survey activity type"
sql="
  DELETE FROM civicrm_option_value
  WHERE option_group_id = 2
    AND name = 'Website Survey';

  INSERT INTO civicrm_option_value (option_group_id, label, value, name, grouping, filter, is_default, weight, description, is_optgroup, is_reserved, is_active, component_id, domain_id, visibility_id) VALUES
(2, 'Website Survey', '90', 'Website Survey', NULL, 0, 0, 87, '<p>Survey/Questionnaire responses that were received through the public website.</p>', 0, 0, 1, NULL, NULL, NULL);
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
    survey_name_80 varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
    survey_id_81 int(11) DEFAULT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

  ALTER TABLE civicrm_value_website_survey_10
    ADD PRIMARY KEY (id),
    ADD UNIQUE KEY unique_entity_id (entity_id),
    ADD KEY INDEX_survey_name_80 (survey_name_80);

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
  (80, 10, 'Survey_Name', 'Survey Name', 'String', 'Text', NULL, 0, 1, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 255, NULL, NULL, NULL, NULL, 60, 4, 'survey_name_80', NULL, NULL),
  (81, 10, 'Survey_ID', 'Survey ID', 'Int', 'Text', NULL, 0, 0, 0, 2, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 255, NULL, NULL, NULL, NULL, 60, 4, 'survey_id_81', NULL, NULL);

  SET FOREIGN_KEY_CHECKS=1;
"
$execSql $instance -c "$sql" -q

echo "$prog: 9395: add web activity stream dashlet"
sql="
  SELECT @dashid:=id FROM civicrm_dashboard WHERE name = 'websiteActivityStream';
  DELETE FROM civicrm_dashboard_contact WHERE dashboard_id = @dashid;
  DELETE FROM civicrm_dashboard WHERE id = @dashid;
  INSERT INTO civicrm_dashboard (domain_id, name, label, url, permission, permission_operator, column_no, is_minimized, is_fullscreen, is_active, is_reserved, weight, fullscreen_url) VALUES
(1, 'websiteActivityStream', 'Website Activity Stream', 'civicrm/dashlet/webactivitystream&reset=1&snippet=4', 'access CiviCRM', NULL, 0, 1, 1, 1, 1, 1, 'civicrm/dashlet/webactivitystream&reset=1&snippet=4&context=dashletFullscreen');
"
$execSql $instance -c "$sql" -q

echo "$prog: 9487: add Web Account contact source"
sql="
  SELECT @optgrp:=id FROM civicrm_option_group WHERE name = 'voter_registration_status_20100309194635';
  DELETE FROM civicrm_option_value
    WHERE option_group_id = @optgrp
      AND name = 'website_account';
  INSERT INTO civicrm_option_value
  (option_group_id, label, value, name, grouping, filter, is_default, weight, description, is_optgroup, is_reserved, is_active, component_id, domain_id, visibility_id)
  VALUES
  (@optgrp, 'Website Account', 'Website Account', 'website_account', NULL, NULL, 0, 21, NULL, 0, 0, 1, NULL, NULL, NULL);
"
$execSql $instance -c "$sql" -q

echo "$prog: 9515: create recent website contacts report"
sql="
  DELETE FROM civicrm_report_instance WHERE title='Recent Website Contacts';
  INSERT INTO civicrm_report_instance (domain_id, title, report_id, description, permission, grouprole, form_values, is_active, email_subject, email_to, email_cc, header, footer, navigation_id, is_reserved, drilldown_id, name, args)
  VALUES
(1, 'Recent Website Contacts', 'contact/summary', 'List of new contacts created as a result of a website account within the last 30 days.', 'access CiviReport', NULL, 'a:75:{s:8:\"entryURL\";s:72:\"http://skelos.crmdev.nysenate.gov/civicrm/report/contact/summary?reset=1\";s:6:\"fields\";a:5:{s:9:\"sort_name\";s:1:\"1\";s:12:\"created_date\";s:1:\"1\";s:5:\"email\";s:1:\"1\";s:4:\"city\";s:1:\"1\";s:5:\"phone\";s:1:\"1\";}s:12:\"sort_name_op\";s:3:\"has\";s:15:\"sort_name_value\";s:0:\"\";s:9:\"source_op\";s:3:\"has\";s:12:\"source_value\";s:0:\"\";s:6:\"id_min\";s:0:\"\";s:6:\"id_max\";s:0:\"\";s:5:\"id_op\";s:3:\"lte\";s:8:\"id_value\";s:0:\"\";s:21:\"created_date_relative\";s:12:\"ending.month\";s:17:\"created_date_from\";s:0:\"\";s:15:\"created_date_to\";s:0:\"\";s:20:\"state_province_id_op\";s:2:\"in\";s:23:\"state_province_id_value\";a:0:{}s:6:\"gid_op\";s:2:\"in\";s:9:\"gid_value\";a:0:{}s:8:\"tagid_op\";s:2:\"in\";s:11:\"tagid_value\";a:0:{}s:12:\"custom_18_op\";s:2:\"eq\";s:15:\"custom_18_value\";s:0:\"\";s:12:\"custom_17_op\";s:2:\"eq\";s:15:\"custom_17_value\";s:0:\"\";s:12:\"custom_19_op\";s:2:\"eq\";s:15:\"custom_19_value\";s:0:\"\";s:12:\"custom_23_op\";s:2:\"in\";s:15:\"custom_23_value\";a:0:{}s:18:\"custom_24_relative\";s:0:\"\";s:14:\"custom_24_from\";s:0:\"\";s:12:\"custom_24_to\";s:0:\"\";s:12:\"custom_16_op\";s:3:\"has\";s:15:\"custom_16_value\";s:0:\"\";s:12:\"custom_20_op\";s:3:\"has\";s:15:\"custom_20_value\";s:0:\"\";s:12:\"custom_61_op\";s:2:\"in\";s:15:\"custom_61_value\";a:0:{}s:12:\"custom_42_op\";s:2:\"in\";s:15:\"custom_42_value\";a:0:{}s:12:\"custom_60_op\";s:2:\"in\";s:15:\"custom_60_value\";a:1:{i:0;s:15:\"Website Account\";}s:12:\"custom_58_op\";s:4:\"mhas\";s:15:\"custom_58_value\";a:0:{}s:12:\"custom_62_op\";s:3:\"has\";s:15:\"custom_62_value\";s:0:\"\";s:12:\"custom_63_op\";s:3:\"has\";s:15:\"custom_63_value\";s:0:\"\";s:12:\"custom_45_op\";s:3:\"has\";s:15:\"custom_45_value\";s:0:\"\";s:12:\"custom_25_op\";s:3:\"has\";s:15:\"custom_25_value\";s:0:\"\";s:12:\"custom_26_op\";s:3:\"has\";s:15:\"custom_26_value\";s:0:\"\";s:12:\"custom_41_op\";s:2:\"in\";s:15:\"custom_41_value\";a:0:{}s:12:\"custom_64_op\";s:3:\"has\";s:15:\"custom_64_value\";s:0:\"\";s:12:\"custom_75_op\";s:2:\"eq\";s:15:\"custom_75_value\";s:0:\"\";s:12:\"custom_76_op\";s:3:\"has\";s:15:\"custom_76_value\";s:0:\"\";s:12:\"custom_77_op\";s:3:\"has\";s:15:\"custom_77_value\";s:0:\"\";s:12:\"custom_78_op\";s:3:\"has\";s:15:\"custom_78_value\";s:0:\"\";s:9:\"order_bys\";a:1:{i:1;a:2:{s:6:\"column\";s:12:\"created_date\";s:5:\"order\";s:4:\"DESC\";}}s:11:\"description\";s:86:\"List of new contacts created as a result of a website account within the last 30 days.\";s:13:\"email_subject\";s:0:\"\";s:8:\"email_to\";s:0:\"\";s:8:\"email_cc\";s:0:\"\";s:14:\"addToDashboard\";s:1:\"1\";s:11:\"is_reserved\";s:1:\"1\";s:10:\"permission\";s:17:\"access CiviReport\";s:9:\"parent_id\";s:3:\"260\";s:6:\"groups\";s:0:\"\";s:11:\"instance_id\";s:2:\"39\";}', NULL, NULL, NULL, NULL, '<html>\r\n  <head>\r\n    <title>Bluebird Report</title>\r\n    <meta http-equiv=''Content-Type'' content=''text/html; charset=utf-8'' />\r\n    <style type=\"text/css\">@import url(http://skelos.crmdev.nysenate.gov/sites/all/modules/civicrm/css/print.css);</style>\r\n  </head>\r\n  <body><div id=\"crm-container\">', '</div></body></html>', NULL, 1, NULL, NULL, NULL);
"
$execSql $instance -c "$sql" -q

php $script_dir/../civicrm/scripts/logUpdateSchema.php -S $instance
php $script_dir/../civicrm/scripts/logUpdateIndexes.php -S $instance
