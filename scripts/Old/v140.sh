#!/bin/sh
#
# v140.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2012-08
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
app_rootdir=`$readConfig --ig $instance app.rootdir` || app_rootdir="$DEFAULT_APP_ROOTDIR"
webdir=`$readConfig --global drupal.rootdir` || webdir="$DEFAULT_DRUPAL_ROOTDIR"
base_domain=`$readConfig --ig $instance base.domain` || base_domain="$DEFAULT_BASE_DOMAIN"
db_basename=`$readConfig --ig $instance db.basename` || db_basename="$instance"
log_db_prefix=`$readConfig --ig $instance db.log.prefix` || log_db_prefix="$DEFAULT_BASE_DOMAIN"
civi_db_prefix=`$readConfig --ig $instance db.civicrm.prefix` || civi_db_prefix="$DEFAULT_BASE_DOMAIN"
cdb="$civi_db_prefix$db_basename"

###### Begin Upgrade Scripts ######

## clear some db caches before we begin
echo "clear some db caches before we begin..."
ccc="TRUNCATE civicrm_cache; TRUNCATE civicrm_menu;"
$execSql -i $instance -c "$ccc" -q

ccd="TRUNCATE cache; TRUNCATE cache_page; TRUNCATE cache_form; TRUNCATE cache_update; TRUNCATE cache_menu;
 TRUNCATE cache_block; TRUNCATE cache_filter; TRUNCATE sessions;"
$execSql -i $instance -c "$ccd" --drupal -q

## manually disable various modules before running drupal upgrade
echo "disable various modules before running upgrade scripts..."
dismods="
UPDATE system
SET status = 0
WHERE name IN
  ('civicrm_rules', 'userprotect', 'rules', 'rules_admin', 'apachesolr', 'apachesolr_search', 'color',
  'comment', 'help', 'taxonomy', 'update', 'admin_menu', 'imce', 'nyss_backup', 'nyss_boe',
  'nyss_dashboards', 'nyss_dedupe', 'nyss_export', 'nyss_import', 'nyss_io', 'nyss_mail', 'nyss_massmerge',
  'nyss_sage', 'nyss_tags', 'nyss_testing', 'nyss_civihooks');"
$execSql -i $instance -c "$dismods" --drupal -q

## cleanup nyss_403 module
echo "cleanup nyss_403 module..."
$execSql -i $instance -c "DELETE FROM system WHERE name = 'NYSS_403';" --drupal -q

## drop triggers and disable logging
$script_dir/dropCiviTriggers.sh $instance

echo "disabling logging manually..."
logging="
UPDATE civicrm_domain
  SET config_backend = REPLACE(config_backend, '\"logging\";i:1;', '\"logging\";i:0;')
  WHERE id = 1;
"
$execSql -i $instance -c "$logging" -q

## cleanup msg workflow templates
echo "cleanup msg workflow templates..."
msgtpl="
SELECT @optval := GROUP_CONCAT(cov.id)
 FROM civicrm_option_value cov
 JOIN civicrm_option_group cog
   ON cov.option_group_id = cog.id
 WHERE cov.name = 'contribution_online_receipt'
   AND cog.name = 'msg_tpl_workflow_contribution';
DELETE FROM civicrm_option_value
 WHERE name = 'contribution_online_receipt'
   AND id NOT IN (@optval);
"
$execSql -i $instance -c "$msgtpl" -q

## temporarily create setting table
echo "temporarily creating civicrm_setting table..."
settingtbl="
DROP TABLE IF EXISTS civicrm_setting;
CREATE TABLE civicrm_setting (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  group_name varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'group name for setting element, useful in caching setting elements',
  name varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Unique name for setting',
  value text COLLATE utf8_unicode_ci COMMENT 'data associated with this group / name combo',
  domain_id int(10) unsigned NOT NULL COMMENT 'Which Domain is this menu item for',
  contact_id int(10) unsigned DEFAULT NULL COMMENT 'FK to Contact ID if the setting is localized to a contact',
  is_domain tinyint(4) DEFAULT NULL COMMENT 'Is this setting a contact specific or site wide setting?',
  component_id int(10) unsigned DEFAULT NULL COMMENT 'Component that this menu item belongs to',
  created_date datetime DEFAULT NULL COMMENT 'When was the setting created',
  created_id int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_contact, who created this setting',
  PRIMARY KEY (id),
  KEY index_group_name (group_name,name),
  KEY FK_civicrm_setting_domain_id (domain_id),
  KEY FK_civicrm_setting_contact_id (contact_id),
  KEY FK_civicrm_setting_component_id (component_id),
  KEY FK_civicrm_setting_created_id (created_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
ALTER TABLE civicrm_setting
  ADD CONSTRAINT FK_civicrm_setting_domain_id FOREIGN KEY (domain_id) REFERENCES civicrm_domain (id) ON DELETE CASCADE,
  ADD CONSTRAINT FK_civicrm_setting_contact_id FOREIGN KEY (contact_id) REFERENCES civicrm_contact (id) ON DELETE CASCADE,
  ADD CONSTRAINT FK_civicrm_setting_component_id FOREIGN KEY (component_id) REFERENCES civicrm_component (id),
  ADD CONSTRAINT FK_civicrm_setting_created_id FOREIGN KEY (created_id) REFERENCES civicrm_contact (id) ON DELETE SET NULL;
"
$execSql -i $instance -c "$settingtbl" -q

## temporarily create managed table
echo "temporarily creating civicrm_managed table..."
managedtbl="
CREATE TABLE civicrm_managed (
  id int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Surrogate Key',
  module varchar(127) NOT NULL   COMMENT 'Name of the module which declared this object',
  name varchar(127)    COMMENT 'Symbolic name used by the module to identify the object',
  entity_type varchar(64) NOT NULL   COMMENT 'API entity type',
  entity_id int unsigned NOT NULL   COMMENT 'Foreign key to the referenced item.',
  PRIMARY KEY ( id ),
  INDEX UI_managed_module_name( module, name ),
  INDEX UI_managed_entity( entity_type, entity_id )
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
"
$execSql -i $instance -c "$managedtbl" -q

## run drupal upgrade
echo "run drupal db upgrade"
$drush $instance updb -y -q

## remove setting table
#echo "removing civicrm_setting table so civicrm upgrade can recreate..."
#settingrm="DROP TABLE IF EXISTS civicrm_setting;"
#$execSql -i $instance -c "$settingrm" -q

## remove managed table
echo "removing civicrm_managed table so civicrm upgrade can recreate..."
managedrm="DROP TABLE IF EXISTS civicrm_managed;"
$execSql -i $instance -c "$managedrm" -q

## manually re-enable civicrm so upgrade will run
echo "ensure civicrm module is enabled..."
cmod="UPDATE system SET status = 1 WHERE name = 'civicrm';"
$execSql -i $instance -c "$cmod" --drupal -q

## upgrade civicrm db
echo "running civicrm db upgrade..."
$drush $instance civicrm-upgrade-db

## enable modules
echo "enabling other modules for: $instance..."
$drush $instance en userprotect -y -q
$drush $instance en entity -y -q
$drush $instance en entity_token -y -q
$drush $instance en rules -y -q
$drush $instance en rules_admin -y -q
$drush $instance en apachesolr -y -q
$drush $instance en apachesolr_search -y -q
$drush $instance en ldap_servers -y -q
$drush $instance en ldap_authorization -y -q
$drush $instance en ldap_authentication -y -q
$drush $instance en ldap_authorization_drupal_role -y -q
#$drush $instance en apc -y -q

## enable civicrm modules
echo "make sure civicrm and nyss modules are enabled..."
$drush $instance dis civicrm -y -q
$drush $instance en civicrm -y -q
$drush $instance en civicrm_rules -y -q
$drush $instance en nyss_403 -y -q
$drush $instance en nyss_backup -y -q
$drush $instance en nyss_boe -y -q
$drush $instance en nyss_dashboards -y -q
$drush $instance en nyss_dedupe -y -q
$drush $instance en nyss_export -y -q
$drush $instance en nyss_import -y -q
$drush $instance en nyss_io -y -q
$drush $instance en nyss_mail -y -q
$drush $instance en nyss_massmerge -y -q
$drush $instance en nyss_sage -y -q
$drush $instance en nyss_tags -y -q
$drush $instance en nyss_civihooks -y -q
$drush $instance en nyss_contact -y -q
$drush $instance en nyss_imapper -y -q
$drush $instance en civicrm_error -y -q

## reenable logging
echo "re-enable civicrm logging..."
php $app_rootdir/civicrm/scripts/enableLogging.php -S $instance

## set theme
echo "setting theme for: $instance"
$drush $instance en Bluebird -y -q
$drush $instance vset theme_default Bluebird

## update front page module settings
echo "update front page module settings"
front="
UPDATE variable SET value = 0x693a313b
 WHERE name = 'front_page_enable';
UPDATE variable SET value = 0x733a303a22223b
 WHERE name = 'front_page_home_link_path';
UPDATE variable SET value = 0x733a32353a226369766963726d2f64617368626f6172643f72657365743d31223b
 WHERE name = 'site_frontpage';"
$execSql -i $instance -c "$front" --drupal -q

## move some newly added menu items
echo "move some newly added menu items"
navigation="
SELECT @admin := id FROM civicrm_navigation WHERE name = 'Administer';
UPDATE civicrm_navigation SET parent_id = @admin WHERE name = 'Batches' AND parent_id IS NULL;
UPDATE civicrm_navigation SET is_active = 0 WHERE name = 'New SMS';
UPDATE civicrm_navigation SET is_active = 0, has_separator = null WHERE name = 'Find Mass SMS';
UPDATE civicrm_navigation SET parent_id = @admin WHERE name = 'New SMS' AND parent_id IS NULL;
UPDATE civicrm_navigation SET parent_id = @admin WHERE name = 'CiviMail Component Settings' AND parent_id IS NULL;
UPDATE civicrm_navigation SET parent_id = @admin WHERE name = 'CiviEvent Component Settings' AND parent_id IS NULL;
UPDATE civicrm_navigation SET parent_id = @admin WHERE name = 'CiviMember Component Settings' AND parent_id IS NULL;
UPDATE civicrm_navigation SET parent_id = @admin WHERE name = 'Event Badge Formats' AND parent_id IS NULL;
UPDATE civicrm_navigation SET parent_id = @admin WHERE name = 'Personal Campaign Pages' AND parent_id IS NULL;
"
$execSql -i $instance -c "$navigation" -q

## change settings for district info data set
echo "change district info config settings"
distinfo="UPDATE civicrm_custom_group SET collapse_display = 0 WHERE name = 'District_Information';"
$execSql -i $instance -c "$distinfo" -q

## transfer ldap settings to new module
echo "transfer LDAP settings to new module..."

sql="
TRUNCATE ldap_servers;
INSERT INTO ldap_servers (sid, numeric_sid, name, status, ldap_type, address, port, tls, bind_method, binddn, bindpw, basedn, user_attr, account_name_attr, mail_attr, mail_template, allow_conflicting_drupal_accts, unique_persistent_attr, user_dn_expression, ldap_to_drupal_user, testing_drupal_username, group_object_category, search_pagination, search_page_size, weight) VALUES
('nyss_ldap', 1, 'NY Senate LDAP Server', 1, 'openldap', 'webmail.nysenate.gov', 389, 0, 4, '', '', 'a:1:{i:0;s:0:\"\";}', 'uid', '', 'mail', '', 0, '', '', '', '', 'groupOfNames', 0, 1000, 0);
"
$execSql -i $instance -c "$sql" --drupal -q

# kz - the authentication module name has changed
sql="UPDATE authmap SET module='ldap_authentication' WHERE module='ldapauth';"
$execSql -i $instance -c "$sql" --drupal -q

sql="
TRUNCATE ldap_authorization;
INSERT INTO ldap_authorization (numeric_consumer_conf_id, sid, consumer_type, consumer_module, status, only_ldap_authenticated, derive_from_dn, derive_from_dn_attr, derive_from_attr, derive_from_attr_attr, derive_from_attr_use_first_attr, derive_from_attr_nested, derive_from_entry, derive_from_entry_nested, derive_from_entry_entries, derive_from_entry_entries_attr, derive_from_entry_attr, derive_from_entry_search_all, derive_from_entry_use_first_attr, derive_from_entry_user_ldap_attr, mappings, use_filter, synch_to_ldap, synch_on_logon, revoke_ldap_provisioned, create_consumers, regrant_ldap_provisioned) VALUES
(1, 'nyss_ldap', 'drupal_role', 'ldap_authorization_drupal_role', 1, 1, 0, '', 0, '', 0, 0, 1, 0, 'CRMAnalytics\nCRMAdministrator\nCRMOfficeAdministrator\nCRMOfficeDataEntry\nCRMOfficeManager\nCRMOfficeStaff\nCRMOfficeVolunteer\nCRMPrintProduction\nCRMSOS', 'cn', 'member', 0, 0, 'dn', 'CRMAnalytics|Analytics User\nCRMAdministrator|Administrator\nCRMOfficeAdministrator|Office Administrator\nCRMOfficeDataEntry|Data Entry\nCRMOfficeManager|Office Manager\nCRMOfficeStaff|Staff\nCRMOfficeVolunteer|Volunteer\nCRMPrintProduction|Print Production\nCRMSOS|SOS\nCRMDConferenceServices|Conference Services\nCRMRConferenceServices|Conference Services\n', 1, 0, 1, 1, 0, 1);
"
$execSql -i $instance -c "$sql" --drupal -q

sql="
DELETE FROM variable WHERE name = 'ldap_authentication_conf';
INSERT INTO variable ( name, value ) VALUES
( 'ldap_authentication_conf', 0x613a31383a7b733a343a2273696473223b613a313a7b733a393a226e7973735f6c646170223b733a393a226e7973735f6c646170223b7d733a31383a2261757468656e7469636174696f6e4d6f6465223b693a323b733a32303a226c6f67696e436f6e666c6963745265736f6c7665223b693a323b733a31323a22616363744372656174696f6e223b693a343b733a31383a226c6f67696e5549557365726e616d65547874223b4e3b733a31383a226c6f67696e554950617373776f7264547874223b4e3b733a31393a226c6461705573657248656c704c696e6b55726c223b4e3b733a32303a226c6461705573657248656c704c696e6b54657874223b733a31303a224c6f676f6e2048656c70223b733a31313a22656d61696c4f7074696f6e223b693a333b733a31313a22656d61696c557064617465223b693a313b733a31393a22616c6c6f774f6e6c79496654657874496e446e223b613a303a7b7d733a31373a226578636c756465496654657874496e446e223b613a303a7b7d733a31323a22616c6c6f7754657374506870223b733a303a22223b733a32353a226578636c75646549664e6f417574686f72697a6174696f6e73223b4e3b733a32383a2273736f52656d6f7465557365725374726970446f6d61696e4e616d65223b4e3b733a31333a227365616d6c6573734c6f67696e223b4e3b733a31383a226c646170496d706c656d656e746174696f6e223b4e3b733a31323a22636f6f6b6965457870697265223b4e3b7d );
"
$execSql -i $instance -c "$sql" --drupal -q

sql="DROP TABLE IF EXISTS ldapauth; UPDATE users SET data = null;"
$execSql -i $instance -c "$sql" --drupal -q

sql="DELETE FROM system WHERE name IN ('ldapauth', 'ldapdata', 'ldapgroups');"
$execSql -i $instance -c "$sql" --drupal -q

## need to clear variables cache to force rebuild
sql="DELETE FROM cache_bootstrap WHERE cid = 'variables';"
$execSql -i $instance -c "$sql" --drupal -q

## misc adjustments
echo "take care of miscelleneous adjustments..."
attach="UPDATE civicrm_custom_group SET title = 'File Attachments' WHERE name = 'Attachments';"
$execSql -i $instance -c "$attach" -q

## reorder custom fields
order="
UPDATE civicrm_custom_field SET weight = 1 WHERE custom_group_id = 1 AND name = 'Active_Constituent_';
UPDATE civicrm_custom_field SET weight = 2 WHERE custom_group_id = 1 AND name = 'Interest_in_Volunteering_';
UPDATE civicrm_custom_field SET weight = 3 WHERE custom_group_id = 1 AND name = 'Friend_of_the_Senator_';
UPDATE civicrm_custom_field SET weight = 4 WHERE custom_group_id = 1 AND name = 'Voter_Registration_Status';
UPDATE civicrm_custom_field SET weight = 5 WHERE custom_group_id = 1 AND name = 'BOE_Date_of_Registration';
UPDATE civicrm_custom_field SET weight = 6 WHERE custom_group_id = 1 AND name = 'Professional_Accreditations';
UPDATE civicrm_custom_field SET weight = 7 WHERE custom_group_id = 1 AND name = 'Skills_Areas_of_Interest';
UPDATE civicrm_custom_field SET weight = 8 WHERE custom_group_id = 1 AND name = 'Honors_and_Awards';
UPDATE civicrm_custom_field SET weight = 9 WHERE custom_group_id = 1 AND name = 'Record_Type';
UPDATE civicrm_custom_field SET weight = 10 WHERE custom_group_id = 1 AND name = 'Individual_Category';
UPDATE civicrm_custom_field SET weight = 11 WHERE custom_group_id = 1 AND name = 'Contact_Source';
UPDATE civicrm_custom_field SET weight = 12 WHERE custom_group_id = 1 AND name = 'Ethnicity';
UPDATE civicrm_custom_field SET weight = 13 WHERE custom_group_id = 1 AND name = 'Other_Ethnicity';
UPDATE civicrm_custom_field SET weight = 14 WHERE custom_group_id = 1 AND name = 'Religion';
UPDATE civicrm_custom_field SET weight = 15 WHERE custom_group_id = 1 AND name = 'Other_Gender';
"
$execSql -i $instance -c "$order" -q

## set blocks for bluebird theme
blocks="
UPDATE block SET status = 1, region = 'content' WHERE module = 'system' AND delta = 'main' AND theme = 'Bluebird';
UPDATE block SET status = 1, region = 'content' WHERE module = 'user' AND delta = 'login' AND theme = 'Bluebird';
UPDATE block SET status = 1, region = 'footer' WHERE module = 'civicrm' AND delta = '2' AND theme = 'Bluebird';
"
$execSql -i $instance -c "$blocks" --drupal -q

## set timezone
timezone="UPDATE users SET timezone = 'America/New_York';"
$execSql -i $instance -c "$timezone" --drupal -q

$drush $instance vset date_default_timezone 'America/New_York' -y
$drush $instance vset configurable_timezones 0 -y
$drush $instance vset empty_timezone_message 0 -y

## disable contribution-type activities
contract="
SELECT @atgroup := id FROM civicrm_option_group WHERE name = 'activity_type';
UPDATE civicrm_option_value
SET is_active = 0
WHERE option_group_id = @atgroup
  AND name IN ('Update Recurring Contribution', 'Update Recurring Contribution Billing Details',
    'Cancel Recurring Contribution', 'BULK SMS', 'SMS');
"
$execSql -i $instance -c "$contract" -q

## 5652 remove dupe reminder sent activity type
## 6078 add social media type
rs="
SELECT @act:= id FROM civicrm_option_group WHERE name = 'activity_type';
SELECT @rs1:= value FROM civicrm_option_value WHERE option_group_id = @act AND name = 'Reminder Sent' AND is_reserved = 0;
SELECT @rs2:= value FROM civicrm_option_value WHERE option_group_id = @act AND name = 'Reminder Sent' AND is_reserved = 1;
UPDATE civicrm_activity SET activity_type_id = @rs2 WHERE activity_type_id = @rs1;
UPDATE civicrm_option_value SET is_active = 0 WHERE option_group_id = @act AND value = @rs1;
SELECT @maxval:= max(cast(value as unsigned)) FROM civicrm_option_value WHERE option_group_id = @act;
INSERT INTO civicrm_option_value (option_group_id, label, value, name, weight, is_active)
  VALUES (@act, 'Social Media', @maxval + 1, 'Social Media', @maxval + 1, 1);
"
$execSql -i $instance -c "$rs" -q

## 5686 update civimail component settings
mail="
UPDATE civicrm_setting
SET value = 'i:1;'
WHERE group_name = 'Mailing Preferences'
  AND name IN ('civimail_workflow', 'civimail_multiple_bulk_emails');
"
$execSql -i $instance -c "$mail" -q

## 5638 remove custom group help text
ch="UPDATE civicrm_custom_group SET help_pre = null, help_post = null;"
$execSql -i $instance -c "$ch" -q

## 5396 add help text to file attachments
fa="
UPDATE civicrm_custom_group
SET help_pre = 'Browse to the file you want to upload. Each file must be less than 2MB in size.'
WHERE name = 'Attachments';"
$execSql -i $instance -c "$fa" -q

## 4275 update dedupe rules
rules="
UPDATE civicrm_dedupe_rule_group
SET name = REPLACE(name, CONCAT('-', id), '');
UPDATE civicrm_dedupe_rule_group
SET title = name, is_reserved = 1
WHERE title IS NULL;
DELETE FROM civicrm_dedupe_rule
WHERE dedupe_rule_group_id IN (
SELECT id
FROM civicrm_dedupe_rule_group
WHERE name IN ('IndividualFuzzy', 'IndividualStrict', 'IndividualComplete') );
DELETE FROM civicrm_dedupe_rule_group
WHERE name IN ('IndividualFuzzy', 'IndividualStrict', 'IndividualComplete');
"
$execSql -i $instance -c "$rules" -q

## 5335 add bmp to safe file extensions
safe="
SELECT @safe:= id FROM civicrm_option_group WHERE name = 'safe_file_extension';
SELECT @maxval:= MAX(CAST(value AS UNSIGNED)) FROM civicrm_option_value WHERE option_group_id = @safe;
INSERT INTO civicrm_option_value (
  option_group_id, label, value, name, grouping, filter, is_default, weight, description, is_optgroup, is_reserved,
  is_active, component_id, domain_id, visibility_id )
VALUES (
  @safe, 'bmp', @maxval+1, NULL , NULL , '0', '0', @maxval+1, NULL , '0', '0', '1', NULL , NULL , NULL
);"
$execSql -i $instance -c "$safe" -q

## add inbox polling perm to admin/officeadmin/officemgr roles
sql="
INSERT INTO role_permission (rid, permission)
VALUES (4 ,'administer inbox polling'),
  (9 ,'administer inbox polling'),
  (10 ,'administer inbox polling'),
  (3 ,'administer inbox polling');
"
$execSql -i $instance -c "$sql" --drupal -q

## 6685 add case perm (admin, super, office admin, office mgr, staff
sql="
INSERT IGNORE INTO role_permission (rid, permission)
VALUES (4 ,'add cases'),
  (9 ,'add cases'),
  (10 ,'add cases'),
  (3 ,'add cases'),
  (11 ,'add cases');
"
$execSql -i $instance -c "$sql" --drupal -q

## 5832 remove changelog from advanced search
sql="
UPDATE civicrm_setting
SET value = 's:29:"12345101316171819";'
WHERE name = 'advanced_search_options';
"
## 6659 retain changelog panel
#$execSql -i $instance -c "$sql" -q

## 5914 changelog report permission
changeRpt="
UPDATE civicrm_report_instance
SET permission = 'access CiviCRM'
WHERE report_id = 'logging/contact/detail' OR report_id = 'logging/contact/summary';
"
$execSql -i $instance -c "$changeRpt" -q

## 5893
authfwd="
INSERT INTO civicrm_group (name, title, description, source, saved_search_id, is_active, visibility, group_type, cache_date, parents, children, is_hidden, is_reserved) VALUES
('Authorized_Forwarders', 'Authorized Forwarders', NULL, NULL, NULL, 1, 'User and User Admin Only', NULL, NULL, NULL, NULL, 0, 1);
"
$execSql -i $instance -c "$authfwd" -q

## 6053
indivcat="
SELECT @ic:=option_group_id FROM civicrm_custom_field WHERE name = 'Individual_Category';
SELECT @w:=weight FROM civicrm_option_value WHERE name = 'District_Staff' AND option_group_id = @ic;
INSERT INTO civicrm_option_value (option_group_id, label, value, name, weight, is_active)
VALUES (@ic, 'Albany Staff', 'albany_staff', 'Albany_Staff', @w, 1);
"
$execSql -i $instance -c "$indivcat" -q

## 5993
$drush $instance vset civicrm_error_to 'brian@lcdservices.biz,zalewski@nysenate.gov' -y

## 6062
mgusers="
UPDATE civicrm_navigation
SET url = 'admin/people'
WHERE url = 'admin/user/user';
"
$execSql -i $instance -c "$mgusers" -q

## 6055
sql="
SELECT @role:=rid FROM role WHERE name = 'Administrator';
INSERT INTO role_permission (rid, permission, module)
VALUES
  (@rid, 'edit users with role Administrator', 'administerusersbyrole'),
  (@rid, 'cancel users with role Administrator', 'administerusersbyrole'),
  (@rid, 'edit users with role Administrator and other roles', 'administerusersbyrole'),
  (@rid, 'cancel users with role Administrator and other roles', 'administerusersbyrole'),

  (@rid, 'edit users with no custom roles', 'administerusersbyrole'),
  (@rid, 'cancel users with no custom roles', 'administerusersbyrole'),

  (@rid, 'edit users with role DataEntry', 'administerusersbyrole'),
  (@rid, 'cancel users with role DataEntry', 'administerusersbyrole'),
  (@rid, 'edit users with role DataEntry and other roles', 'administerusersbyrole'),
  (@rid, 'cancel users with role DataEntry and other roles', 'administerusersbyrole'),

  (@rid, 'edit users with role MailingApprover', 'administerusersbyrole'),
  (@rid, 'cancel users with role MailingApprover', 'administerusersbyrole'),
  (@rid, 'edit users with role MailingApprover and other roles', 'administerusersbyrole'),
  (@rid, 'cancel users with role MailingApprover and other roles', 'administerusersbyrole'),

  (@rid, 'edit users with role MailingCreator', 'administerusersbyrole'),
  (@rid, 'cancel users with role MailingCreator', 'administerusersbyrole'),
  (@rid, 'edit users with role MailingCreator and other roles', 'administerusersbyrole'),
  (@rid, 'cancel users with role MailingCreator and other roles', 'administerusersbyrole'),

  (@rid, 'edit users with role MailingScheduler', 'administerusersbyrole'),
  (@rid, 'cancel users with role MailingScheduler', 'administerusersbyrole'),
  (@rid, 'edit users with role MailingScheduler and other roles', 'administerusersbyrole'),
  (@rid, 'cancel users with role MailingScheduler and other roles', 'administerusersbyrole'),

  (@rid, 'edit users with role MailingViewer', 'administerusersbyrole'),
  (@rid, 'cancel users with role MailingViewer', 'administerusersbyrole'),
  (@rid, 'edit users with role MailingViewer and other roles', 'administerusersbyrole'),
  (@rid, 'cancel users with role MailingViewer and other roles', 'administerusersbyrole'),

  (@rid, 'edit users with role OfficeAdministrator', 'administerusersbyrole'),
  (@rid, 'cancel users with role OfficeAdministrator', 'administerusersbyrole'),
  (@rid, 'edit users with role OfficeAdministrator and other roles', 'administerusersbyrole'),
  (@rid, 'cancel users with role OfficeAdministrator and other roles', 'administerusersbyrole'),

  (@rid, 'edit users with role OfficeManager', 'administerusersbyrole'),
  (@rid, 'cancel users with role OfficeManager', 'administerusersbyrole'),
  (@rid, 'edit users with role OfficeManager and other roles', 'administerusersbyrole'),
  (@rid, 'cancel users with role OfficeManager and other roles', 'administerusersbyrole'),

  (@rid, 'edit users with role PrintProduction', 'administerusersbyrole'),
  (@rid, 'cancel users with role PrintProduction', 'administerusersbyrole'),
  (@rid, 'edit users with role PrintProduction and other roles', 'administerusersbyrole'),
  (@rid, 'cancel users with role PrintProduction and other roles', 'administerusersbyrole'),

  (@rid, 'edit users with role PrintProductionStaff', 'administerusersbyrole'),
  (@rid, 'cancel users with role PrintProductionStaff', 'administerusersbyrole'),
  (@rid, 'edit users with role PrintProductionStaff and other roles', 'administerusersbyrole'),
  (@rid, 'cancel users with role PrintProductionStaff and other roles', 'administerusersbyrole'),

  (@rid, 'edit users with role SOS', 'administerusersbyrole'),
  (@rid, 'cancel users with role SOS', 'administerusersbyrole'),
  (@rid, 'edit users with role SOS and other roles', 'administerusersbyrole'),
  (@rid, 'cancel users with role SOS and other roles', 'administerusersbyrole'),

  (@rid, 'edit users with role Staff', 'administerusersbyrole'),
  (@rid, 'cancel users with role Staff', 'administerusersbyrole'),
  (@rid, 'edit users with role Staff and other roles', 'administerusersbyrole'),
  (@rid, 'cancel users with role Staff and other roles', 'administerusersbyrole'),

  (@rid, 'edit users with role Volunteer', 'administerusersbyrole'),
  (@rid, 'cancel users with role Volunteer', 'administerusersbyrole'),
  (@rid, 'edit users with role Volunteer and other roles', 'administerusersbyrole'),
  (@rid, 'cancel users with role Volunteer and other roles', 'administerusersbyrole')
ON DUPLICATE KEY UPDATE module = 'administerusersbyrole';
"
$execSql -i $instance -c "$sql" --drupal -q

## 6001 set workflow rules
rules="
DROP TABLE IF EXISTS rules_rules;
DROP TABLE IF EXISTS rules_scheduler_d6;
DROP TABLE IF EXISTS rules_sets;
TRUNCATE TABLE rules_config;
TRUNCATE TABLE rules_dependencies;
TRUNCATE TABLE rules_trigger;
INSERT INTO rules_config (id, name, label, plugin, active, weight, status, module, data, dirty, access_exposed) VALUES
(1, 'rules_notify_creator_of_approval', 'Notify Creator of Approval', 'reaction rule', 1, 0, 1, 'rules', 0x4f3a31373a2252756c65735265616374696f6e52756c65223a31343a7b733a393a22002a00706172656e74223b4e3b733a323a226964223b733a313a2231223b733a31323a22002a00656c656d656e744964223b693a313b733a363a22776569676874223b733a313a2230223b733a383a2273657474696e6773223b613a303a7b7d733a343a226e616d65223b733a33323a2272756c65735f6e6f746966795f63726561746f725f6f665f617070726f76616c223b733a363a226d6f64756c65223b733a353a2272756c6573223b733a363a22737461747573223b733a313a2231223b733a353a226c6162656c223b733a32363a224e6f746966792043726561746f72206f6620417070726f76616c223b733a343a2274616773223b613a303a7b7d733a31313a22002a006368696c6472656e223b613a313a7b693a303b4f3a31313a2252756c6573416374696f6e223a363a7b733a393a22002a00706172656e74223b723a313b733a323a226964223b4e3b733a31323a22002a00656c656d656e744964223b693a343b733a363a22776569676874223b693a303b733a383a2273657474696e6773223b613a343a7b733a323a22746f223b733a32323a227b6d61696c696e672e63726561746f72456d61696c7d223b733a373a227375626a656374223b733a35323a225374617475733a207b6d61696c696e672e617070726f76616c5374617475737d20287b6d61696c696e672e7375626a6563747d29223b733a373a226d657373616765223b733a3437393a223c703e54686520666f6c6c6f77696e6720656d61696c20686173206265656e203c7374726f6e673e7b6d61696c696e672e617070726f76616c5374617475737d3c2f7374726f6e673e3a207b6d61696c696e672e6e616d657d3c2f703e0d0a0d0a3c703e54686520666f6c6c6f77696e6720656d61696c20617070726f76616c2f72656a656374696f6e206d65737361676520686173206265656e20696e636c756465643a3c6272202f3e0d0a7b6d61696c696e672e617070726f76616c4e6f74657d3c2f703e0d0a0d0a3c703e596f752068617665206e6f206675727468657220737465707320746f2074616b652e2054686520656d61696c2077696c6c20656e74657220746865206d61696c696e6720717565756520616e642062652064656c6976657265642073686f72746c792e204e6f7465207468617420656d61696c73206d617920657870657269656e636520736f6d652064656c6179206261736564206f6e207468652073697a65206f662074686520656d61696c20616e6420766f6c756d65206f6620726563697069656e74732e3c2f703e0d0a0d0a3c703e54686520636f6e74656e74206f662074686520656d61696c2069733a3c2f703e0d0a3c6469763e0d0a7b6d61696c696e672e68746d6c7d0d0a3c2f6469763e223b733a343a2266726f6d223b4e3b7d733a31343a22002a00656c656d656e744e616d65223b733a31383a226d61696c696e675f73656e645f656d61696c223b7d7d733a373a22002a00696e666f223b613a303a7b7d733a31333a22002a00636f6e646974696f6e73223b4f3a383a2252756c6573416e64223a383a7b733a393a22002a00706172656e74223b723a313b733a323a226964223b4e3b733a31323a22002a00656c656d656e744964223b693a323b733a363a22776569676874223b693a303b733a383a2273657474696e6773223b613a303a7b7d733a31313a22002a006368696c6472656e223b613a313a7b693a303b4f3a31343a2252756c6573436f6e646974696f6e223a373a7b733a393a22002a00706172656e74223b723a32353b733a323a226964223b4e3b733a31323a22002a00656c656d656e744964223b693a333b733a363a22776569676874223b693a303b733a383a2273657474696e6773223b613a313a7b733a32313a22617070726f76616c7374617475733a73656c656374223b733a373a226d61696c696e67223b7d733a31343a22002a00656c656d656e744e616d65223b733a34303a226369766963726d5f72756c65735f636f6e646974696f6e5f6d61696c696e675f617070726f766564223b733a393a22002a006e6567617465223b623a303b7d7d733a373a22002a00696e666f223b613a303a7b7d733a393a22002a006e6567617465223b623a303b7d733a393a22002a006576656e7473223b613a313a7b693a303b733a31363a226d61696c696e675f617070726f766564223b7d7d, 0, 0),
(2, 'rules_notify_creator_of_rejection', 'Notify Creator of Rejection', 'reaction rule', 1, 0, 1, 'rules', 0x4f3a31373a2252756c65735265616374696f6e52756c65223a31343a7b733a393a22002a00706172656e74223b4e3b733a323a226964223b733a313a2232223b733a31323a22002a00656c656d656e744964223b693a313b733a363a22776569676874223b733a313a2230223b733a383a2273657474696e6773223b613a303a7b7d733a343a226e616d65223b733a33333a2272756c65735f6e6f746966795f63726561746f725f6f665f72656a656374696f6e223b733a363a226d6f64756c65223b733a353a2272756c6573223b733a363a22737461747573223b733a313a2231223b733a353a226c6162656c223b733a32373a224e6f746966792043726561746f72206f662052656a656374696f6e223b733a343a2274616773223b613a303a7b7d733a31313a22002a006368696c6472656e223b613a313a7b693a303b4f3a31313a2252756c6573416374696f6e223a363a7b733a393a22002a00706172656e74223b723a313b733a323a226964223b4e3b733a31323a22002a00656c656d656e744964223b693a343b733a363a22776569676874223b693a303b733a383a2273657474696e6773223b613a343a7b733a323a22746f223b733a32323a227b6d61696c696e672e63726561746f72456d61696c7d223b733a373a227375626a656374223b733a35323a225374617475733a207b6d61696c696e672e617070726f76616c5374617475737d20287b6d61696c696e672e7375626a6563747d29223b733a373a226d657373616765223b733a3533343a223c703e54686520666f6c6c6f77696e6720656d61696c20686173206265656e203c7374726f6e673e7b6d61696c696e672e617070726f76616c5374617475737d3c2f7374726f6e673e3a207b6d61696c696e672e6e616d657d3c2f703e0d0a0d0a3c703e54686520666f6c6c6f77696e6720656d61696c20617070726f76616c2f72656a656374696f6e206d65737361676520686173206265656e20696e636c756465643a3c6272202f3e0d0a3c656d3e7b6d61696c696e672e617070726f76616c4e6f74657d3c2f656d3e3c2f703e0d0a0d0a3c703e596f752077696c6c2066696e64207468652072656a656374656420656d61696c20696e20426c75656269726420756e6465722074686520647261667420656d61696c206d616e6167656d656e7420706167652e20596f752063616e2072657669657720616e64206564697420746865206d61696c20686572653a3c2f703e0d0a3c756c3e3c6c693e7b6d61696c696e672e6564697455726c7d3c2f6c693e3c2f756c3e0d0a0d0a3c703e4f6e636520796f7527766520757064617465642074686520656d61696c20796f752077696c6c206e65656420746f2072657363686564756c6520697420616e64207375626d697420666f7220617070726f76616c2e2054686520636f6e74656e74206f662074686520656d61696c2069733a3c2f703e0d0a3c6469763e0d0a7b6d61696c696e672e68746d6c7d0d0a3c2f6469763e223b733a31313a2266726f6d3a73656c656374223b733a303a22223b7d733a31343a22002a00656c656d656e744e616d65223b733a31383a226d61696c696e675f73656e645f656d61696c223b7d7d733a373a22002a00696e666f223b613a303a7b7d733a31333a22002a00636f6e646974696f6e73223b4f3a383a2252756c6573416e64223a383a7b733a393a22002a00706172656e74223b723a313b733a323a226964223b4e3b733a31323a22002a00656c656d656e744964223b693a323b733a363a22776569676874223b693a303b733a383a2273657474696e6773223b613a303a7b7d733a31313a22002a006368696c6472656e223b613a313a7b693a303b4f3a31343a2252756c6573436f6e646974696f6e223a373a7b733a393a22002a00706172656e74223b723a32353b733a323a226964223b4e3b733a31323a22002a00656c656d656e744964223b693a353b733a363a22776569676874223b693a303b733a383a2273657474696e6773223b613a313a7b733a32313a22617070726f76616c7374617475733a73656c656374223b733a373a226d61696c696e67223b7d733a31343a22002a00656c656d656e744e616d65223b733a34303a226369766963726d5f72756c65735f636f6e646974696f6e5f6d61696c696e675f72656a6563746564223b733a393a22002a006e6567617465223b623a303b7d7d733a373a22002a00696e666f223b613a303a7b7d733a393a22002a006e6567617465223b623a303b7d733a393a22002a006576656e7473223b613a313a7b693a303b733a31363a226d61696c696e675f617070726f766564223b7d7d, 0, 0);
INSERT INTO rules_dependencies (id, module) VALUES
(1, 'civicrm'), (2, 'civicrm'), (1, 'civicrm_rules'), (2, 'civicrm_rules');
INSERT INTO rules_trigger (id, event) VALUES
(1, 'mailing_approved'), (2, 'mailing_approved');
"
$execSql -i $instance -c "$rules" --drupal -q

## 6208
sqlprefix="
SELECT @optgrp:=id FROM civicrm_option_group WHERE name = 'individual_prefix';
SELECT @maxval:=max(cast(value as unsigned)) FROM civicrm_option_value WHERE option_group_id = @optgrp;
SELECT @wght:=weight FROM civicrm_option_value WHERE option_group_id = @optgrp AND name = 'Sergeant';
UPDATE civicrm_option_value SET weight = weight + 1 WHERE option_group_id = @optgrp AND weight >= @wght;
INSERT INTO civicrm_option_value (option_group_id, label, value, name, weight, is_active)
VALUES (@optgrp, 'Senator', @maxval+1, 'Senator', @wght, 1);
"
$execSql -i $instance -c "$sqlprefix" -q

## 5808 remove new tag menu item
sqlNewTag="
  UPDATE civicrm_navigation
  SET is_active = 0
  WHERE name = 'New Tag';
"
$execSql -i $instance -c "$sqlNewTag" -q

## create inbox polling tables
$execSql -i $instance -f $app_rootdir/scripts/sql/inbox_polling.sql -q

## 6564 update emails received report
sql="
UPDATE civicrm_report_instance
SET title = 'Matched Inbound Emails, Last 7 Days',description = 'Emails retrieved from inbox polling over the last 7 days.',permission = 'access CiviReport',form_values = 'a:50:{s:6:\"fields\";a:4:{s:14:\"contact_source\";s:1:\"1\";s:14:\"contact_target\";s:1:\"1\";s:16:\"activity_subject\";s:1:\"1\";s:18:\"activity_date_time\";s:1:\"1\";}s:17:\"contact_source_op\";s:3:\"has\";s:20:\"contact_source_value\";s:0:\"\";s:19:\"contact_assignee_op\";s:3:\"has\";s:22:\"contact_assignee_value\";s:0:\"\";s:17:\"contact_target_op\";s:3:\"has\";s:20:\"contact_target_value\";s:0:\"\";s:15:\"current_user_op\";s:2:\"eq\";s:18:\"current_user_value\";s:1:\"0\";s:27:\"activity_date_time_relative\";s:11:\"ending.week\";s:23:\"activity_date_time_from\";s:0:\"\";s:21:\"activity_date_time_to\";s:0:\"\";s:19:\"activity_subject_op\";s:3:\"has\";s:22:\"activity_subject_value\";s:0:\"\";s:19:\"activity_type_id_op\";s:2:\"in\";s:22:\"activity_type_id_value\";a:1:{i:0;s:2:\"12\";}s:12:\"status_id_op\";s:2:\"in\";s:15:\"status_id_value\";a:0:{}s:17:\"street_number_min\";s:0:\"\";s:17:\"street_number_max\";s:0:\"\";s:16:\"street_number_op\";s:3:\"lte\";s:19:\"street_number_value\";s:0:\"\";s:14:\"street_name_op\";s:3:\"has\";s:17:\"street_name_value\";s:0:\"\";s:15:\"postal_code_min\";s:0:\"\";s:15:\"postal_code_max\";s:0:\"\";s:14:\"postal_code_op\";s:3:\"lte\";s:17:\"postal_code_value\";s:0:\"\";s:7:\"city_op\";s:3:\"has\";s:10:\"city_value\";s:0:\"\";s:20:\"state_province_id_op\";s:2:\"in\";s:23:\"state_province_id_value\";a:0:{}s:8:\"tagid_op\";s:2:\"in\";s:11:\"tagid_value\";a:0:{}s:6:\"gid_op\";s:2:\"in\";s:9:\"gid_value\";a:0:{}s:12:\"custom_43_op\";s:2:\"in\";s:15:\"custom_43_value\";a:0:{}s:12:\"custom_44_op\";s:2:\"in\";s:15:\"custom_44_value\";a:0:{}s:9:\"order_bys\";a:1:{i:1;a:2:{s:6:\"column\";s:18:\"activity_date_time\";s:5:\"order\";s:4:\"DESC\";}}s:11:\"description\";s:57:\"Emails retrieved from inbox polling over the last 7 days.\";s:13:\"email_subject\";s:0:\"\";s:8:\"email_to\";s:0:\"\";s:8:\"email_cc\";s:0:\"\";s:14:\"addToDashboard\";s:1:\"1\";s:11:\"is_reserved\";s:1:\"1\";s:10:\"permission\";s:17:\"access CiviReport\";s:6:\"groups\";s:0:\"\";s:9:\"domain_id\";i:1;}',is_reserved = 1
WHERE title = 'Emails Received, Last 7 Days';
UPDATE civicrm_navigation
SET label = 'Matched Inbound Emails, Last 7 Days', name = 'Matched Inbound Emails, Last 7 Days'
WHERE name = 'Emails Received, Last 7 Days';
"
$execSql -i $instance -c "$sql" -q

## 6560 remove logging tables no longer to be used
sql="
  DROP TABLE IF EXISTS log_civicrm_action_log;
  DROP TABLE IF EXISTS log_civicrm_log;
  DROP TABLE IF EXISTS log_civicrm_membership_log;
  DROP TABLE IF EXISTS log_civicrm_menu;
  DROP TABLE IF EXISTS log_civicrm_mailing_event_bounce;
  DROP TABLE IF EXISTS log_civicrm_mailing_event_confirm;
  DROP TABLE IF EXISTS log_civicrm_mailing_event_delivered;
  DROP TABLE IF EXISTS log_civicrm_mailing_event_forward;
  DROP TABLE IF EXISTS log_civicrm_mailing_event_opened;
  DROP TABLE IF EXISTS log_civicrm_mailing_event_queue;
  DROP TABLE IF EXISTS log_civicrm_mailing_event_reply;
  DROP TABLE IF EXISTS log_civicrm_mailing_event_sendgrid_delivered;
  DROP TABLE IF EXISTS log_civicrm_mailing_event_subscribe;
  DROP TABLE IF EXISTS log_civicrm_mailing_event_trackable_url_open;
  DROP TABLE IF EXISTS log_civicrm_mailing_event_unsubscribe;
"
$execSql -i $instance -c "$sql" --log -q

## roles/perms updates
sql="
  INSERT INTO role (rid, name)
  VALUES (19, 'Manage Bluebird Inbox');
  INSERT INTO role_permission (rid, permission, module)
  VALUES (19, 'administer inbox polling', 'nyss_civihooks');
"
$execSql -i $instance -c "$sql" --drupal -q

## 6677 add new bounce regex for AOL
sql="
  INSERT INTO civicrm_mailing_bounce_pattern ( bounce_type_id, pattern )
  VALUES ( 5, 'recipient address rejected' );
"
$execSql -i $instance -c "$sql" -q

## 6564 update emails received report
sql="
UPDATE civicrm_report_instance
SET title = 'Matched Inbound Emails, Last 7 Days',description = 'Emails retrieved from inbox polling over the last 7 days.',permission = 'access CiviReport',form_values = 'a:50:{s:6:\"fields\";a:4:{s:14:\"contact_source\";s:1:\"1\";s:14:\"contact_target\";s:1:\"1\";s:16:\"activity_subject\";s:1:\"1\";s:18:\"activity_date_time\";s:1:\"1\";}s:17:\"contact_source_op\";s:3:\"has\";s:20:\"contact_source_value\";s:0:\"\";s:19:\"contact_assignee_op\";s:3:\"has\";s:22:\"contact_assignee_value\";s:0:\"\";s:17:\"contact_target_op\";s:3:\"has\";s:20:\"contact_target_value\";s:0:\"\";s:15:\"current_user_op\";s:2:\"eq\";s:18:\"current_user_value\";s:1:\"0\";s:27:\"activity_date_time_relative\";s:11:\"ending.week\";s:23:\"activity_date_time_from\";s:0:\"\";s:21:\"activity_date_time_to\";s:0:\"\";s:19:\"activity_subject_op\";s:3:\"has\";s:22:\"activity_subject_value\";s:0:\"\";s:19:\"activity_type_id_op\";s:2:\"in\";s:22:\"activity_type_id_value\";a:1:{i:0;s:2:\"12\";}s:12:\"status_id_op\";s:2:\"in\";s:15:\"status_id_value\";a:0:{}s:17:\"street_number_min\";s:0:\"\";s:17:\"street_number_max\";s:0:\"\";s:16:\"street_number_op\";s:3:\"lte\";s:19:\"street_number_value\";s:0:\"\";s:14:\"street_name_op\";s:3:\"has\";s:17:\"street_name_value\";s:0:\"\";s:15:\"postal_code_min\";s:0:\"\";s:15:\"postal_code_max\";s:0:\"\";s:14:\"postal_code_op\";s:3:\"lte\";s:17:\"postal_code_value\";s:0:\"\";s:7:\"city_op\";s:3:\"has\";s:10:\"city_value\";s:0:\"\";s:20:\"state_province_id_op\";s:2:\"in\";s:23:\"state_province_id_value\";a:0:{}s:8:\"tagid_op\";s:2:\"in\";s:11:\"tagid_value\";a:0:{}s:6:\"gid_op\";s:2:\"in\";s:9:\"gid_value\";a:0:{}s:12:\"custom_43_op\";s:2:\"in\";s:15:\"custom_43_value\";a:0:{}s:12:\"custom_44_op\";s:2:\"in\";s:15:\"custom_44_value\";a:0:{}s:9:\"order_bys\";a:1:{i:1;a:2:{s:6:\"column\";s:18:\"activity_date_time\";s:5:\"order\";s:4:\"DESC\";}}s:11:\"description\";s:57:\"Emails retrieved from inbox polling over the last 7 days.\";s:13:\"email_subject\";s:0:\"\";s:8:\"email_to\";s:0:\"\";s:8:\"email_cc\";s:0:\"\";s:14:\"addToDashboard\";s:1:\"1\";s:11:\"is_reserved\";s:1:\"1\";s:10:\"permission\";s:17:\"access CiviReport\";s:6:\"groups\";s:0:\"\";s:9:\"domain_id\";i:1;}',is_reserved = 1
WHERE title = 'Emails Received, Last 7 Days';
UPDATE civicrm_navigation
SET label = 'Matched Inbound Emails, Last 7 Days', name = 'Matched Inbound Emails, Last 7 Days'
WHERE name = 'Emails Received, Last 7 Days';
"
$execSql -i $instance -c "$sql"

##TODO review roles/perms updates

### Cleanup ###

$script_dir/clearCache.sh $instance
