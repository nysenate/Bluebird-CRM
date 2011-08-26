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


### CiviCRM ###

## 4210 autocomplete search options
autocomplete="UPDATE civicrm_preferences SET contact_autocomplete_options = '125' WHERE id = 1;"
$execSql -i $instance -c "$autocomplete"

## 4214 add access CiviMail to nav perm
navigation="UPDATE civicrm_navigation SET permission = 'create mailings,schedule mailings,access CiviMail' WHERE name = 'Archived Emails';"
$execSql -i $instance -c "$navigation"

## 3814 create privacy option note
customgroup="INSERT INTO civicrm_custom_group (id, name, title, extends, extends_entity_column_id, extends_entity_column_value, style, collapse_display, help_pre, help_post, weight, is_active, table_name, is_multiple, min_multiple, max_multiple, collapse_adv_display, created_id, created_date) VALUES
(8, 'Contact_Details', 'Contact Details', 'Contact', NULL, NULL, 'Inline', 0, '', '', 6, 1, 'civicrm_value_contact_details_8', 0, NULL, NULL, 1, 1, '2011-08-22 23:21:02');"
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


### Cleanup ###

$script_dir/clearCache.sh $instance
