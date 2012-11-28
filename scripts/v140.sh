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

## disable logging and run civicrm upgrade
echo "disabling logging manually..."

triggersql="
SELECT trigger_name
FROM information_schema.triggers
WHERE trigger_schema = '$cdb'
AND trigger_name LIKE 'civicrm_%';"
triggers=`$execSql -c "$triggersql" -q`

echo "removing triggers..."
for trigger in $triggers; do
  $execSql -i $instance -c "DROP TRIGGER IF EXISTS $trigger" -q
done

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
('nyss_ldap', 1, 'NY Senate LDAP Server', 1, 'openldap', 'webmail.nysenate.gov', 389, 0, 4, '', '', 'a:1:{i:0;s:0:"";}', 'uid', '', 'mail', '', 0, '', '', '', '', 'groupOfNames', 0, 1000, 0);
"
$execSql -i $instance -c "$sql" --drupal -q

# kz - the authentication module name has changed
sql="update authmap set module='ldap_authentication' where module='ldapauth';"
$execSql -i $instance -c "$sql" --drupal -q

sql="
TRUNCATE ldap_authorization;
INSERT INTO ldap_authorization (numeric_consumer_conf_id, sid, consumer_type, consumer_module, status, only_ldap_authenticated, derive_from_dn, derive_from_dn_attr, derive_from_attr, derive_from_attr_attr, derive_from_attr_use_first_attr, derive_from_attr_nested, derive_from_entry, derive_from_entry_nested, derive_from_entry_entries, derive_from_entry_entries_attr, derive_from_entry_attr, derive_from_entry_search_all, derive_from_entry_use_first_attr, derive_from_entry_user_ldap_attr, mappings, use_filter, synch_to_ldap, synch_on_logon, revoke_ldap_provisioned, create_consumers, regrant_ldap_provisioned) VALUES
(1, 'nyss_ldap', 'drupal_role', 'ldap_authorization_drupal_role', 1, 1, 0, '', 0, '', 0, 0, 1, 0, 'CRMAnalytics\nCRMAdministrator\nCRMOfficeAdministrator\nCRMOfficeDataEntry\nCRMOfficeManager\nCRMOfficeStaff\nCRMOfficeVolunteer\nCRMPrintProduction\nCRMSOS', 'cn', 'member', 0, 0, 'dn', 'CRMAnalytics|Analytics User\nCRMAdministrator|Administrator\nCRMOfficeAdministrator|Office Administrator\nCRMOfficeDataEntry|Data Entry\nCRMOfficeManager|Office Manager\nCRMOfficeStaff|Staff\nCRMOfficeVolunteer|Volunteer\nCRMPrintProduction|Print Production\nCRMSOS|SOS\nCRMDConferenceServices|Conference Services\nCRMRConferenceServices|Conference Services\n', 1, 0, 1, 1, 0, 1);
"
$execSql -i $instance -c "$sql" --drupal -q

sql="
UPDATE variable
SET value = 0x613a31383a7b733a343a2273696473223b613a313a7b733a393a226e7973735f6c646170223b733a393a226e7973735f6c646170223b7d733a31383a2261757468656e7469636174696f6e4d6f6465223b693a323b733a32303a226c6f67696e436f6e666c6963745265736f6c7665223b693a323b733a31323a22616363744372656174696f6e223b693a343b733a31383a226c6f67696e5549557365726e616d65547874223b4e3b733a31383a226c6f67696e554950617373776f7264547874223b4e3b733a31393a226c6461705573657248656c704c696e6b55726c223b4e3b733a32303a226c6461705573657248656c704c696e6b54657874223b733a31303a224c6f676f6e2048656c70223b733a31313a22656d61696c4f7074696f6e223b693a333b733a31313a22656d61696c557064617465223b693a313b733a31393a22616c6c6f774f6e6c79496654657874496e446e223b613a303a7b7d733a31373a226578636c756465496654657874496e446e223b613a303a7b7d733a31323a22616c6c6f7754657374506870223b733a303a22223b733a32353a226578636c75646549664e6f417574686f72697a6174696f6e73223b4e3b733a32383a2273736f52656d6f7465557365725374726970446f6d61696e4e616d65223b4e3b733a31333a227365616d6c6573734c6f67696e223b4e3b733a31383a226c646170496d706c656d656e746174696f6e223b4e3b733a31323a22636f6f6b6965457870697265223b4e3b7d WHERE name = 'ldap_authentication_conf';
"
$execSql -i $instance -c "$sql" --drupal -q

sql="DROP TABLE IF EXISTS ldapauth; UPDATE users SET data = null;"
$execSql -i $instance -c "$sql" --drupal -q

sql="DELETE FROM system WHERE name IN ('ldapauth', 'ldapdata', 'ldapgroups');"
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
rs="
SELECT @act:= id FROM civicrm_option_group WHERE name = 'activity_type';
SELECT @rs1:= value FROM civicrm_option_value WHERE option_group_id = @act AND name = 'Reminder Sent' AND is_reserved = 0;
SELECT @rs2:= value FROM civicrm_option_value WHERE option_group_id = @act AND name = 'Reminder Sent' AND is_reserved = 1;
UPDATE civicrm_activity SET activity_type_id = @rs2 WHERE activity_type_id = @rs1;
UPDATE civicrm_option_value SET is_active = 0 WHERE option_group_id = @act AND value = @rs1;
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
VALUES (4 ,'administer inbox polling'), (9 ,'administer inbox polling'), (10 ,'administer inbox polling'), (3 ,'administer inbox polling');
"
$execSql -i $instance -c "$sql" --drupal -q

## 5832 remove changelog from advanced search
sql="
UPDATE civicrm_setting
SET value = 's:29:"12345101316171819";'
WHERE name = 'advanced_search_options';
"
$execSql -i $instance -c "$sql" -q

## 5914 changelog report permission
changeRpt="
UPDATE civicrm_report_instance
SET permission = 'access CiviCRM'
WHERE report_id = 'logging/contact/detail' OR report_id = 'logging/contact/summary';
"
$execSql -i $instance -c "$changeRpt"

### Cleanup ###

$script_dir/clearCache.sh $instance
