#!/bin/sh
#
# v121.sh
#
# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2011-04-09
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

###### Begin Upgrade Scripts ######

### Drupal ###

## disable/enable drupal modules
echo "disabling/enabling modules for: $instance"
$drush $instance dis nyss_civihooks -y
$drush $instance en nyss_civihooks -y
$drush $instance dis nyss_dedupe -y
$drush $instance en nyss_dedupe -y

## set some variables
$drush $instance vset cache 1 -y
$drush $instance vset ldapauth_login_conflict 1 -y

## update permissions
perms_upd="UPDATE permission SET perm = 'create users, delete users with role Administrator, delete users with role Analytics User, delete users with role Conference Services, delete users with role Data Entry, delete users with role Office Administrator, delete users with role Office Manager, delete users with role Print Production, delete users with role SOS, delete users with role Staff, delete users with role Volunteer, edit users with role Administrator, edit users with role Analytics User, edit users with role Conference Services, edit users with role Data Entry, edit users with role Mailing Approver, edit users with role Mailing Creator, edit users with role Mailing Scheduler, edit users with role Office Administrator, edit users with role Office Manager, edit users with role Print Production, edit users with role SOS, edit users with role Staff, edit users with role Volunteer, administer blocks, use PHP for block visibility, access CiviCRM, access CiviReport, access Contact Dashboard, access Report Criteria, access all cases and activities, access all custom data, access deleted contacts, access my cases and activities, access uploaded files, add contacts, administer CiviCRM, administer CiviCase, administer Reports, administer Tagsets, administer dedupe rules, administer reserved tags, delete activities, delete contacts, delete in CiviCase, edit all contacts, edit groups, import contacts, merge duplicate contacts, profile create, profile edit, profile listings, profile listings and forms, profile view, translate CiviCRM, view all activities, view all contacts, delete contacts permanently, export print production files, assign roles, access administration pages, access user profiles, administer permissions, administer users, administer userprotect' WHERE rid = 3;
UPDATE permission SET perm = 'create users, delete users with role Analytics User, delete users with role Conference Services, delete users with role Data Entry, delete users with role Office Administrator, delete users with role Office Manager, delete users with role Print Production, delete users with role SOS, delete users with role Staff, delete users with role Volunteer, edit users with role Analytics User, edit users with role Conference Services, edit users with role Data Entry, edit users with role Office Administrator, edit users with role Office Manager, edit users with role Print Production, edit users with role SOS, edit users with role Staff, edit users with role Volunteer, access CiviCRM, access CiviReport, access Contact Dashboard, access Report Criteria, access all cases and activities, access all custom data, access deleted contacts, access my cases and activities, access uploaded files, add contacts, administer CiviCRM, administer Reports, administer dedupe rules, delete activities, delete contacts, delete in CiviCase, edit all contacts, edit groups, import contacts, merge duplicate contacts, profile listings, profile listings and forms, profile view, view all activities, view all contacts, delete contacts permanently, export print production files, assign roles, access administration pages, administer users' WHERE rid = 4;
UPDATE permission SET perm = 'create users, delete users with role Analytics User, delete users with role Conference Services, delete users with role Data Entry, delete users with role Office Manager, delete users with role Print Production, delete users with role SOS, delete users with role Staff, delete users with role Volunteer, edit users with role Analytics User, edit users with role Conference Services, edit users with role Data Entry, edit users with role Mailing Approver, edit users with role Mailing Creator, edit users with role Mailing Scheduler, edit users with role Office Manager, edit users with role Print Production, edit users with role SOS, edit users with role Staff, edit users with role Volunteer, access CiviCRM, access CiviReport, access Contact Dashboard, access Report Criteria, access all cases and activities, access all custom data, access deleted contacts, access my cases and activities, access uploaded files, add contacts, administer CiviCRM, administer Reports, delete activities, delete contacts, delete in CiviCase, edit all contacts, edit groups, merge duplicate contacts, profile listings, profile listings and forms, profile view, view all activities, view all contacts, delete contacts permanently, assign roles, access administration pages, administer users' WHERE rid = 9;
UPDATE permission SET perm = 'access CiviCRM, access CiviReport, access Contact Dashboard, access Report Criteria, access all cases and activities, access all custom data, access deleted contacts, access my cases and activities, access uploaded files, add contacts, administer Reports, delete activities, delete contacts, delete in CiviCase, edit all contacts, edit groups, profile listings, profile listings and forms, profile view, view all activities, view all contacts' WHERE rid = 10;"
$execSql -i $instance -c "$perms_upd" --drupal

### CiviCRM ###

## navigation menu items
navigation="UPDATE civicrm_navigation SET parent_id = 172, permission = 'access CiviCRM' WHERE name = 'District Stats';
UPDATE civicrm_navigation SET label = 'BOE/3rd Party Import' WHERE url = 'importData';"
$execSql -i $instance -c "$navigation"

## custom fields ##
customfield="ALTER TABLE civicrm_value_constituent_information_1 ADD religion_63 VARCHAR( 255 ) NULL;
INSERT INTO civicrm_custom_field VALUES
(63, 1, 'Religion', 'Religion', 'String', 'Text', NULL, 0, 1, 0, 88, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, 255, NULL, NULL, NULL, NULL, 60, 4, 'religion_63', NULL);"
$execSql -i $instance -c "$customfield"

## update voter reg status ##
voterreg="UPDATE civicrm_value_constituent_information_1 SET voter_registration_status_23 = 'registered' WHERE contact_source_60 = 'boe';"
$execSql -i $instance -c "$voterreg"

## alter tag constraints ##
tagconstraints="ALTER TABLE civicrm_tag DROP INDEX UI_name ,
ADD UNIQUE UI_parent_id_name ( parent_id , name );"
$execSql -i $instance -c "$tagconstraints"

## rename export mapping ##
exportmap="UPDATE civicrm_mapping SET name = 'Email List Export' WHERE name = 'Bronto Email Export';"
$execSql -i $instance -c "$exportmap"

## create dedupe index ##
dedupeindex="CREATE INDEX individualStrict1 ON civicrm_contact(first_name,last_name,birth_date);"
$execSql -i $instance -c "$dedupeindex"

deduperules="UPDATE civicrm_dedupe_rule_group SET threshold = 1 WHERE id = 4;
DELETE FROM civicrm_dedupe_rule WHERE dedupe_rule_group_id = 4;
INSERT INTO civicrm_dedupe_rule VALUES ('', 4, 'civicrm_contact', 'last_name', NULL, 1);"
$execSql -i $instance -c "$deduperules"

## ensure records have only one primary address/phone/email ##
primaryrecords="UPDATE civicrm_address as address
INNER JOIN ( SELECT id, contact_id FROM civicrm_address WHERE is_primary = 1 GROUP BY contact_id HAVING count( id ) > 1 ) as dup_address 
         ON ( address.contact_id = dup_address.contact_id AND address.id != dup_address.id )
SET address.is_primary = 0;
UPDATE civicrm_email as email
INNER JOIN ( SELECT id, contact_id FROM civicrm_email WHERE is_primary = 1 GROUP BY contact_id HAVING count( id ) > 1 ) as dup_email
         ON ( email.contact_id = dup_email.contact_id AND email.id != dup_email.id )
SET email.is_primary = 0;
UPDATE civicrm_phone as phone
INNER JOIN ( SELECT id, contact_id FROM civicrm_phone WHERE is_primary = 1 GROUP BY contact_id HAVING count( id ) > 1 ) as dup_phone
         ON ( phone.contact_id = dup_phone.contact_id AND phone.id != dup_phone.id )
SET phone.is_primary = 0;"
$execSql -i $instance -c "$primaryrecords"

## set sort name formula ##
sortname="UPDATE civicrm_preferences SET sort_name_format='{contact.last_name}{, }{contact.individual_suffix}{, }{contact.first_name}{ }{contact.middle_name}' WHERE id=1;"
$execSql -i $instance -c "$sortname"

## rebuild all existing sort names ##
sortrebuild="UPDATE civicrm_contact
SET sort_name = TRIM(CONCAT(last_name, ', ', (SELECT label FROM civicrm_option_value WHERE value = suffix_id AND option_group_id = 7), ', ', IF(first_name IS NOT NULL, first_name, ''), ' ', IF(middle_name IS NOT NULL, middle_name, '')))
WHERE contact_type = 'Individual' AND suffix_id IS NOT NULL;"
$execSql -i $instance -c "$sortrebuild"

###### Cleanup ######

$script_dir/fixPermissions.sh
$script_dir/clearCache.sh $instance
