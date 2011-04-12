#!/bin/sh
#
# v120.sh
#
# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2011-03-11
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

#######

## create site directories and symlink to data folder; set variable
drupal_filesdir="$data_rootdir/$instance.$base_domain/drupal"
sitedir="$webdir/sites/$instance.$base_domain"
mkdir -p "$drupal_filesdir"
mkdir -p "$sitedir"
ln -s "$drupal_filesdir" "$sitedir/files"
filesdir="sites/$instance.$base_domain/files"
$drush $instance vset file_directory_path $filesdir -y

## run civicrm db upgrade using drush
$drush $instance civicrm-upgrade-db

### Drupal

## create new roles
mailing_roles="INSERT INTO role VALUES (16, 'Mailing Approver'), (14, 'Mailing Creator'), (15, 'Mailing Scheduler');"
$execSql -i $instance -c "$mailing_roles" --drupal

## update permissions
perms_upd="UPDATE permission SET perm = 'create users, delete users with role Analytics User, delete users with role Conference Services, delete users with role Data Entry, delete users with role Office Administrator, delete users with role Office Manager, delete users with role Print Production, delete users with role SOS, delete users with role Staff, delete users with role Volunteer, edit users with role Analytics User, edit users with role Conference Services, edit users with role Data Entry, edit users with role Office Administrator, edit users with role Office Manager, edit users with role Print Production, edit users with role SOS, edit users with role Staff, edit users with role Volunteer, access CiviCRM, access CiviReport, access Contact Dashboard, access Report Criteria, access all cases and activities, access all custom data, access deleted contacts, access my cases and activities, access uploaded files, add contacts, administer CiviCRM, administer Reports, administer dedupe rules, delete activities, delete contacts, delete in CiviCase, edit all contacts, edit groups, import contacts, merge duplicate contacts, profile listings, profile listings and forms, profile view, view all activities, view all contacts, export print production files, assign roles, access administration pages, administer users' WHERE rid = 4;
UPDATE permission SET perm = 'access CiviCRM, access CiviReport, access Report Criteria, access all custom data, administer Reports, profile listings, profile view, view all activities, view all contacts' WHERE rid = 8;
UPDATE permission SET perm = 'access content' WHERE rid = 1;
UPDATE permission SET perm = 'access content, change own e-mail, change own openid, change own password' WHERE rid = 2;
UPDATE permission SET perm = 'access CiviCRM, access CiviReport, access Report Criteria, access all custom data, administer Reports, edit all contacts, profile listings, profile view, view all activities, view all contacts' WHERE rid = 5;
UPDATE permission SET perm = 'access CiviCRM, access all custom data, access uploaded files, add contacts, edit all contacts, profile listings, profile listings and forms, profile view, view all activities, view all contacts' WHERE rid = 12;
UPDATE permission SET perm = 'create users, delete users with role Analytics User, delete users with role Conference Services, delete users with role Data Entry, delete users with role Office Manager, delete users with role Print Production, delete users with role SOS, delete users with role Staff, delete users with role Volunteer, edit users with role Analytics User, edit users with role Conference Services, edit users with role Data Entry, edit users with role Mailing Approver, edit users with role Mailing Creator, edit users with role Mailing Scheduler, edit users with role Office Manager, edit users with role Print Production, edit users with role SOS, edit users with role Staff, edit users with role Volunteer, access CiviCRM, access CiviReport, access Contact Dashboard, access Report Criteria, access all cases and activities, access all custom data, access deleted contacts, access my cases and activities, access uploaded files, add contacts, administer CiviCRM, administer Reports, delete activities, delete contacts, delete in CiviCase, edit all contacts, edit groups, import contacts, merge duplicate contacts, profile listings, profile listings and forms, profile view, view all activities, view all contacts, assign roles, access administration pages, administer users' WHERE rid = 9;
UPDATE permission SET perm = 'access CiviCRM, access CiviReport, access Contact Dashboard, access Report Criteria, access all cases and activities, access all custom data, access deleted contacts, access my cases and activities, access uploaded files, add contacts, administer Reports, delete activities, delete contacts, delete in CiviCase, edit all contacts, edit groups, import contacts, profile listings, profile listings and forms, profile view, view all activities, view all contacts' WHERE rid = 10;
UPDATE permission SET perm = 'access CiviCRM, access CiviReport, access all custom data, edit groups, import contacts, profile listings, profile view, view all contacts, export print production files, administer site configuration' WHERE rid = 7;
UPDATE permission SET perm = 'access CiviCRM, access CiviReport, access Report Criteria, access all custom data, access uploaded files, add contacts, administer Reports, delete contacts, edit all contacts, edit groups, profile listings, profile view, view all activities, view all contacts' WHERE rid = 6;
UPDATE permission SET perm = 'access CiviCRM, access CiviReport, access Contact Dashboard, access Report Criteria, access all cases and activities, access all custom data, access deleted contacts, access my cases and activities, access uploaded files, add contacts, administer Reports, delete activities, delete contacts, delete in CiviCase, edit all contacts, edit groups, profile listings, profile view, view all activities, view all contacts' WHERE rid = 11;
UPDATE permission SET perm = 'create users, delete users with role Administrator, delete users with role Analytics User, delete users with role Conference Services, delete users with role Data Entry, delete users with role Office Administrator, delete users with role Office Manager, delete users with role Print Production, delete users with role SOS, delete users with role Staff, delete users with role Volunteer, edit users with role Administrator, edit users with role Analytics User, edit users with role Conference Services, edit users with role Data Entry, edit users with role Mailing Approver, edit users with role Mailing Creator, edit users with role Mailing Scheduler, edit users with role Office Administrator, edit users with role Office Manager, edit users with role Print Production, edit users with role SOS, edit users with role Staff, edit users with role Volunteer, administer blocks, use PHP for block visibility, access CiviCRM, access CiviReport, access Contact Dashboard, access Report Criteria, access all cases and activities, access all custom data, access deleted contacts, access my cases and activities, access uploaded files, add contacts, administer CiviCRM, administer CiviCase, administer Reports, administer Tagsets, administer dedupe rules, administer reserved tags, delete activities, delete contacts, delete in CiviCase, edit all contacts, edit groups, import contacts, merge duplicate contacts, profile create, profile edit, profile listings, profile listings and forms, profile view, translate CiviCRM, view all activities, view all contacts, export print production files, assign roles, access administration pages, access user profiles, administer permissions, administer users, administer userprotect' WHERE rid = 3;
UPDATE permission SET perm = 'access CiviCRM, access all custom data, access my cases and activities, access uploaded files, add contacts, profile listings, profile view, view all activities, view all contacts' WHERE rid = 13;"
$execSql -i $instance -c "$perms_upd" --drupal

## add perms for new roles
perms_ins="INSERT INTO permission VALUES ('' , 14, 'create mailings', 0), ('' , 15, 'schedule mailings', 0), ('' , 16, 'approve mailings', 0);"
$execSql -i $instance -c "$perms_ins" --drupal

## disable/enable drupal modules
echo "disabling/enabling modules for: $instance"
$drush $instance dis nyss_dashboards -y
$drush $instance en nyss_dashboards -y
$drush $instance dis imce -y
$drush $instance en imce -y
$drush $instance dis nyss_tags -y
$drush $instance en nyss_tags -y
$drush $instance dis rules -y
$drush $instance en rules -y
$drush $instance dis civicrm_rules -y
$drush $instance en civicrm_rules -y

## set some variables
$drush $instance vset error_level 0 -y

### CiviCRM

## set birth date range
birth_date="UPDATE civicrm_preferences_date SET start = 120 WHERE name = 'birth';"
$execSql -i $instance -c "$birth_date"

## update mailing address format, quick search setting, smtp server
mailing_format="UPDATE civicrm_preferences SET mailing_format = '{contact.addressee}
{contact.supplemental_address_2}
{contact.street_address}
{contact.supplemental_address_1}
{contact.city}{, }{contact.state_province}{ }{contact.postal_code}', mailing_backend = 'a:9:{s:5:\"qfKey\";s:37:\"842832eeff4a43a1e7c5a7de762a10d6_7866\";s:15:\"outBound_option\";s:1:\"0\";s:13:\"sendmail_path\";s:0:\"\";s:13:\"sendmail_args\";s:0:\"\";s:10:\"smtpServer\";s:26:\"senapps.senate.state.ny.us\";s:8:\"smtpPort\";s:2:\"25\";s:8:\"smtpAuth\";s:1:\"0\";s:12:\"smtpUsername\";s:0:\"\";s:12:\"smtpPassword\";s:0:\"\";}', contact_autocomplete_options = '125' WHERE id = 1;"
$execSql -i $instance -c "$mailing_format"

## alter tags table for logging
alter_tags="ALTER TABLE civicrm_tag ADD created_id INT( 10 ) UNSIGNED NULL DEFAULT NULL, ADD created_date DATETIME NULL DEFAULT NULL;"
$execSql -i $instance -c "$alter_tags"

## ensure all base tags are marked reserved
reserved_tags="UPDATE civicrm_tag SET is_reserved = 1 WHERE id <= 296;"
$execSql -i $instance -c "$reserved_tags"

## reset report instance headers/footers
report="UPDATE civicrm_report_instance SET header = '<html>
  <head>
    <title>CiviCRM Report</title>
    <style type=\"text/css\">@import url(/sites/all/modules/civicrm/css/print.css);</style>
  </head>
  <body><div id=\"crm-container\">', footer = '<p>New York State Senate :: BlueBird</p></div></body>
</html>';"
$execSql -i $instance -c "$report"

## update addressee fields for organizations
org_addressee="UPDATE civicrm_contact SET addressee_id = 3, addressee_display = organization_name WHERE addressee_id IS NULL AND addressee_display IS NULL AND contact_type = 'Organization';"
$execSql -i $instance -c "$org_addressee"

## update word replacements
wordreplacement="UPDATE civicrm_domain SET locale_custom_strings = 'a:1:{s:5:\"en_US\";a:2:{s:7:\"enabled\";a:2:{s:13:\"wildcardMatch\";a:15:{s:7:\"CiviCRM\";s:8:\"Bluebird\";s:9:\"Full-text\";s:13:\"Find Anything\";s:16:\"Addt\'l Address 1\";s:15:\"Mailing Address\";s:16:\"Addt\'l Address 2\";s:8:\"Building\";s:73:\"Supplemental address info, e.g. c/o, department name, building name, etc.\";s:70:\"Department name, building name, complex, or extension of company name.\";s:7:\"deatils\";s:7:\"details\";s:11:\"sucessfully\";s:12:\"successfully\";s:40:\"groups, contributions, memberships, etc.\";s:27:\"groups, relationships, etc.\";s:18:\"email OR an OpenID\";s:5:\"email\";s:6:\"Client\";s:11:\"Constituent\";s:6:\"client\";s:11:\"constituent\";s:9:\"Job title\";s:9:\"Job Title\";s:9:\"Nick Name\";s:8:\"Nickname\";s:8:\"CiviMail\";s:12:\"BluebirdMail\";s:18:\"CiviCase Dashboard\";s:14:\"Case Dashboard\";}s:10:\"exactMatch\";a:6:{s:8:\"Position\";s:9:\"Job Title\";s:2:\"Id\";s:2:\"ID\";s:6:\"Client\";s:11:\"Constituent\";s:6:\"client\";s:11:\"constituent\";s:10:\"CiviReport\";s:7:\"Reports\";s:8:\"CiviCase\";s:5:\"Cases\";}}s:8:\"disabled\";a:2:{s:13:\"wildcardMatch\";a:0:{}s:10:\"exactMatch\";a:0:{}}}}\"' WHERE id = 1;"
$execSql -i $instance -c "$wordreplacement"

## option list updates
optionvalues="UPDATE civicrm_option_value SET label = 'Chinese (China/Mandarin)' WHERE name = 'zh_CN'; 
UPDATE civicrm_option_value SET name = 'ce_RU' WHERE label = 'Chechen'; 
INSERT INTO civicrm_option_value VALUES
('', 49, 'Cantonese', 'zh', 'zh_CT', NULL, NULL, 0, 192, NULL, 0, 0, 1, NULL, NULL, NULL),
('', 67, 'Community Leader', 'community_leader', 'Community_Leader', NULL, NULL, 0, 10, NULL, 0, 0, 1, NULL, NULL, NULL),
('', 67, 'Intern/Volunteer', 'intern_volunteer', 'Intern_Volunteer', NULL, NULL, 0, 11, NULL, 0, 0, 1, NULL, NULL, NULL),
('', 64, 'Business Card', 'business_card', 'Business_Card', NULL, NULL, 0, 20, NULL, 0, 0, 1, NULL, NULL, NULL),
('', 66, 'School', 'school', 'School', NULL, NULL, 0, 10, NULL, 0, 0, 1, NULL, NULL, NULL),
('', 6, 'Sheriff', '76', 'Sheriff', NULL, 0, 0, 76, NULL, 0, 0, 1, NULL, NULL, NULL);"
$execSql -i $instance -c "$optionvalues"

## relationship types
relationshiptypes="INSERT INTO civicrm_relationship_type VALUES
(16, 'Agency Staff is', 'Agency Staff is', 'Agency Staff', 'Agency Staff', NULL, NULL, 'Individual', NULL, NULL, NULL, 1),
(17, 'Owner is', 'Owner is', 'Owner of', 'Owner of', NULL, 'Organization', 'Individual', NULL, NULL, NULL, 1);"
$execSql -i $instance -c "$relationshiptypes"

## location types
locationtypes="INSERT INTO civicrm_location_type VALUES
(8, 'Home2', '', '', NULL, 1, 0),
(9, 'Home3', '', '', NULL, 1, 0),
(10, 'Work2', '', '', NULL, 1, 0),
(11, 'Other2', '', '', NULL, 1, 0),
(12, 'Main2', '', '', NULL, 1, 0);"
$execSql -i $instance -c "$locationtypes"

## recreate all dedupe rules
deduperules="SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
DROP TABLE IF EXISTS civicrm_dedupe_rule;
CREATE TABLE civicrm_dedupe_rule (
  id int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique dedupe rule id',
  dedupe_rule_group_id int(10) unsigned NOT NULL COMMENT 'The id of the rule group this rule belongs to',
  rule_table varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The name of the table this rule is about',
  rule_field varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The name of the field of the table referenced in rule_table',
  rule_length int(10) unsigned DEFAULT NULL COMMENT 'The lenght of the matching substring',
  rule_weight int(11) NOT NULL COMMENT 'The weight of the rule',
  PRIMARY KEY (id),
  KEY FK_civicrm_dedupe_rule_dedupe_rule_group_id (dedupe_rule_group_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=177 ;
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(100, 4, 'civicrm_contact', 'birth_date', NULL, 1);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(101, 4, 'civicrm_address', 'postal_code', NULL, 5);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(102, 4, 'civicrm_contact', 'last_name', NULL, 5);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(103, 4, 'civicrm_contact', 'first_name', NULL, 5);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(113, 8, 'civicrm_contact', 'birth_date', NULL, 1);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(114, 8, 'civicrm_contact', 'last_name', NULL, 5);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(115, 8, 'civicrm_contact', 'first_name', NULL, 5);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(116, 8, 'civicrm_address', 'city', NULL, 5);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(121, 1, 'civicrm_address', 'street_address', NULL, 1);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(122, 1, 'civicrm_contact', 'last_name', NULL, 5);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(123, 1, 'civicrm_contact', 'first_name', NULL, 5);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(124, 1, 'civicrm_address', 'city', NULL, 5);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(125, 9, 'civicrm_contact', 'first_name', NULL, 10);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(126, 9, 'civicrm_contact', 'last_name', NULL, 10);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(127, 9, 'civicrm_email', 'email', NULL, 10);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(138, 6, 'civicrm_contact', 'household_name', NULL, 10);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(139, 6, 'civicrm_address', 'street_address', NULL, 10);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(140, 6, 'civicrm_address', 'city', NULL, 10);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(141, 6, 'civicrm_email', 'email', NULL, 10);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(142, 7, 'civicrm_contact', 'household_name', NULL, 10);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(143, 7, 'civicrm_address', 'street_address', NULL, 10);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(144, 7, 'civicrm_address', 'city', NULL, 10);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(145, 3, 'civicrm_contact', 'household_name', NULL, 10);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(146, 3, 'civicrm_address', 'street_address', NULL, 10);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(147, 3, 'civicrm_address', 'postal_code', NULL, 10);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(152, 5, 'civicrm_contact', 'organization_name', NULL, 10);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(153, 5, 'civicrm_address', 'street_address', NULL, 10);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(154, 5, 'civicrm_address', 'city', NULL, 10);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(155, 5, 'civicrm_email', 'email', NULL, 10);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(170, 2, 'civicrm_contact', 'organization_name', NULL, 4);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(171, 2, 'civicrm_address', 'street_address', NULL, 3);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(172, 2, 'civicrm_address', 'postal_code', NULL, 2);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(173, 2, 'civicrm_address', 'city', NULL, 1);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(174, 10, 'civicrm_contact', 'organization_name', NULL, 3);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(175, 10, 'civicrm_address', 'street_address', NULL, 2);
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES(176, 10, 'civicrm_address', 'city', NULL, 1);

DROP TABLE IF EXISTS civicrm_dedupe_rule_group;
CREATE TABLE civicrm_dedupe_rule_group (
  id int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique dedupe rule group id',
  contact_type enum('Individual','Organization','Household') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'The type of contacts this group applies to',
  threshold int(11) NOT NULL COMMENT 'The weight threshold the sum of the rule weights has to cross to consider two contacts the same',
  level enum('Strict','Fuzzy') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Whether the rule should be used for cases where strict maching of the given contact type is required or a fuzzy one',
  is_default tinyint(4) DEFAULT NULL COMMENT 'Is this a default rule (one rule for every contact type + level combination should be default)',
  name varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Name of the rule group',
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=12 ;
INSERT INTO civicrm_dedupe_rule_group (id, contact_type, threshold, level, is_default, name) VALUES(1, 'Individual', 16, 'Fuzzy', 1, 'Level 3 (street + lname + fname + city)');
INSERT INTO civicrm_dedupe_rule_group (id, contact_type, threshold, level, is_default, name) VALUES(2, 'Organization', 10, 'Fuzzy', 1, 'Level 2 (name + street + city + zip)');
INSERT INTO civicrm_dedupe_rule_group (id, contact_type, threshold, level, is_default, name) VALUES(3, 'Household', 30, 'Fuzzy', 1, 'Level 2 (name + street + zip)');
INSERT INTO civicrm_dedupe_rule_group (id, contact_type, threshold, level, is_default, name) VALUES(4, 'Individual', 16, 'Strict', 1, 'Level 1 (fname + lname + birth + postal)');
INSERT INTO civicrm_dedupe_rule_group (id, contact_type, threshold, level, is_default, name) VALUES(5, 'Organization', 40, 'Strict', 1, 'Level 1 (name + address + city + email)');
INSERT INTO civicrm_dedupe_rule_group (id, contact_type, threshold, level, is_default, name) VALUES(6, 'Household', 40, 'Strict', 1, 'Level 1 (name + street + city + email)');
INSERT INTO civicrm_dedupe_rule_group (id, contact_type, threshold, level, is_default, name) VALUES(7, 'Household', 30, 'Fuzzy', 0, 'Level 3 (name + street + city)');
INSERT INTO civicrm_dedupe_rule_group (id, contact_type, threshold, level, is_default, name) VALUES(8, 'Individual', 16, 'Strict', 0, 'Level 2 (fname + lname + city + birth)');
INSERT INTO civicrm_dedupe_rule_group (id, contact_type, threshold, level, is_default, name) VALUES(9, 'Individual', 30, 'Fuzzy', 0, 'Level 4 (fname + lname + email)');
INSERT INTO civicrm_dedupe_rule_group (id, contact_type, threshold, level, is_default, name) VALUES(10, 'Organization', 6, 'Fuzzy', 0, 'Level 3 (name + street + city)');
ALTER TABLE civicrm_dedupe_rule
  ADD CONSTRAINT FK_civicrm_dedupe_rule_dedupe_rule_group_id FOREIGN KEY (dedupe_rule_group_id) REFERENCES civicrm_dedupe_rule_group (id);
SET FOREIGN_KEY_CHECKS=1;"
$execSql -i $instance -c "$deduperules"

## navigation menu items
navigation="SELECT @navlast := id FROM civicrm_navigation WHERE name = 'Campaigns';
DELETE FROM civicrm_navigation WHERE id >= @navlast;
DELETE FROM civicrm_navigation WHERE name = 'Bookkeeping Transactions Report';
DELETE FROM civicrm_navigation WHERE name = 'Grant Report (Detail)';
INSERT INTO civicrm_navigation VALUES
  ('', 1, 'Mailings', 'Mailings', NULL, 'access CiviMail,create mailings,approve mailings,schedule mailings', 'OR', NULL, 1, 0, 3);
SELECT @navmail := id FROM civicrm_navigation WHERE name = 'Mailings';
INSERT INTO civicrm_navigation VALUES
  ('', 1, 'New Mailing', 'New Mailing', 'civicrm/mailing/send&reset=1', 'create mailings,schedule mailings', 'OR', @navmail, 1, 0, 1),
  ('', 1, 'Draft and Unscheduled Mailings', 'Draft and Unscheduled Mailings', 'civicrm/mailing/browse/unscheduled&reset=1&scheduled=false', 'access CiviMail,create mailings,schedule mailings', 'OR', @navmail, 1, 0, 2),
  ('', 1, 'Scheduled and Sent Mailings', 'Scheduled and Sent Mailings', 'civicrm/mailing/browse/scheduled&reset=1&scheduled=true', 'access CiviMail,approve mailings,create mailings,schedule mailings', 'OR', @navmail, 1, 0, 3),
  ('', 1, 'Archived Mailings', 'Archived Mailings', 'civicrm/mailing/browse/archived&reset=1', 'create mailings,schedule mailings', 'OR', @navmail, 1, 0, 4),
  ('', 1, 'Message Templates', 'Message Templates', 'civicrm/admin/messageTemplates&reset=1', 'create mailings,schedule mailings', 'OR', @navmail, 1, 1, 5),
  ('', 1, 'Mail Bounce Report', 'Mail Bounce Report ', 'civicrm/report/instance/27&reset=1', 'access CiviMail', '', @navmail, 1, NULL, 29),
  ('', 1, 'Mail Summary Report', 'Mail Summary Report', 'civicrm/report/instance/28&reset=1', 'access CiviMail', '', @navmail, 1, NULL, 30),
  ('', 1, 'Mail Opened Report', 'Mail Opened Report', 'civicrm/report/instance/29&reset=1', 'access CiviMail', '', @navmail, 1, NULL, 31),
  ('', 1, 'Mail Clickthrough Report', 'Mail Clickthrough Report', 'civicrm/report/instance/30&reset=1', 'access CiviMail', '', @navmail, 1, NULL, 32);
SELECT @navmanage := id FROM civicrm_navigation WHERE name = 'Manage';
INSERT INTO civicrm_navigation VALUES
  ('', 1, 'District Stats', 'District Stats', 'civicrm/dashlet/districtstats', 'access CiviCRM,administer CiviCRM', 'AND', @navmanage, 1, 0, 0);
UPDATE civicrm_navigation SET label = 'Birthday Search' WHERE name = 'CRM_Contact_Form_Search_Custom_BirthdayByMonth';"
$execSql -i $instance -c "$navigation"

### cleanup
$script_dir/fixPermissions.sh
$script_dir/clearCache.sh $instance
