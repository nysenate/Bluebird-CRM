#!/bin/sh
#
# v131.sh
#
# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2011-09-15
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
formal_name=`$readConfig --ig $instance senator.name.formal` || formal_name="Senator"

###### Begin Upgrade Scripts ######

### Drupal ###

## remove old module entry ##
module="DELETE FROM system WHERE name = 'nyss_contactlistquery';"
$execSql -i $instance -c "$module" --drupal

## run drupal db upgrade using drush
$drush $instance updb -y

## add reserved tag perm to admin, office admin
perms_upd="
UPDATE permission SET perm = 'create users, delete users with role Analytics User, delete users with role Conference Services, delete users with role Data Entry, delete users with role Mailing Approver, delete users with role Mailing Creator, delete users with role Mailing Scheduler, delete users with role Office Administrator, delete users with role Office Manager, delete users with role Print Production, delete users with role SOS, delete users with role Staff, delete users with role Volunteer, edit users with role Analytics User, edit users with role Conference Services, edit users with role Data Entry, edit users with role Mailing Approver, edit users with role Mailing Creator, edit users with role Mailing Scheduler, edit users with role Office Administrator, edit users with role Office Manager, edit users with role Print Production, edit users with role SOS, edit users with role Staff, edit users with role Volunteer, access CiviCRM, access CiviReport, access Contact Dashboard, access Report Criteria, access all cases and activities, access all custom data, access deleted contacts, access my cases and activities, access uploaded files, add contacts, administer CiviCRM, administer Reports, administer dedupe rules, administer reserved tags, delete activities, delete contacts, delete in CiviCase, edit all contacts, edit groups, import contacts, merge duplicate contacts, profile listings, profile listings and forms, profile view, view all activities, view all contacts, delete contacts permanently, export print production files, assign roles, access administration pages, administer users' WHERE rid = 4;
UPDATE permission SET perm = 'create users, delete users with role Analytics User, delete users with role Conference Services, delete users with role Data Entry, delete users with role Office Manager, delete users with role Print Production, delete users with role SOS, delete users with role Staff, delete users with role Volunteer, edit users with role Analytics User, edit users with role Conference Services, edit users with role Data Entry, edit users with role Office Manager, edit users with role Print Production, edit users with role SOS, edit users with role Staff, edit users with role Volunteer, access CiviCRM, access CiviReport, access Contact Dashboard, access Report Criteria, access all cases and activities, access all custom data, access deleted contacts, access my cases and activities, access uploaded files, add contacts, administer Reports, administer reserved tags, delete activities, delete contacts, delete in CiviCase, edit all contacts, edit groups, merge duplicate contacts, profile listings, profile listings and forms, profile view, view all activities, view all contacts, administer district, delete contacts permanently, assign roles, access administration pages, administer users' WHERE rid = 9;"
$execSql -i $instance -c "$perms_upd" --drupal


### CiviCRM ###

## 3698 insert contact merge addressee/postal greeting options
mergegreetings="
INSERT INTO civicrm_option_value ( option_group_id, label, value, name, filter, weight ) VALUES
  ( 43, 'Constituents of $formal_name', 6, 'Constituents of $formal_name', 4, 6 ),
  ( 43, 'Friends of $formal_name', 7, 'Friends of $formal_name', 4, 7 );
INSERT INTO civicrm_option_value ( option_group_id, label, value, name, filter, weight ) VALUES
  ( 42, 'Dear Constituents', 12, 'Dear Constituents', 4, 12 ),
  ( 42, 'Dear Friends', 13, 'Dear Friends', 4, 13 );"
$execSql -i $instance -c "$mergegreetings"

## 3812 news dashlet
news="
INSERT INTO civicrm_dashboard (domain_id, label, url, permission, permission_operator, column_no, is_minimized, is_fullscreen, is_active, is_reserved, weight, fullscreen_url) VALUES
(1, 'Bluebird News', 'civicrm/dashlet/news&reset=1&snippet=4', 'access CiviCRM', NULL, 0, 1, 1, 1, 1, 1, 'civicrm/dashlet/news&reset=1&snippet=4&context=dashletFullscreen');"
$execSql -i $instance -c "$news"

## reset dedupe rules
deduperules="
SET FOREIGN_KEY_CHECKS=0;
TRUNCATE civicrm_dedupe_rule_group;
INSERT INTO civicrm_dedupe_rule_group (id, contact_type, threshold, level, is_default, name) VALUES
(1, 'Individual', 5, 'Fuzzy', 1, 'Individual 3 (street + lname + fname + city + suffix)'),
(2, 'Organization', 4, 'Fuzzy', 1, 'Organization 2 (name + street + city + zip)'),
(3, 'Household', 4, 'Fuzzy', 1, 'Household 2 (name + street + city + zip)'),
(4, 'Individual', 5, 'Strict', 1, 'Individual 1 (fname + mname + lname + suffix + street + postal)'),
(5, 'Organization', 4, 'Strict', 1, 'Organization 1 (name + street + city + email)'),
(6, 'Household', 4, 'Strict', 1, 'Household 1 (name + street + city + email)'),
(7, 'Household', 3, 'Fuzzy', 0, 'Household 3 (name + street + city)'),
(8, 'Individual', 4, 'Strict', 0, 'Individual 2 (fname + lname + city + birth)'),
(9, 'Individual', 3, 'Fuzzy', 0, 'Individual 4 (fname + lname + email)'),
(10, 'Organization', 3, 'Fuzzy', 0, 'Organization 3 (name + street + city)'),
(11, 'Individual', 5, 'Strict', 0, 'Individual 5 (email | street + lname)');
TRUNCATE civicrm_dedupe_rule;
INSERT INTO civicrm_dedupe_rule (id, dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES
(198, 6, 'civicrm_contact', 'household_name', NULL, 1),
(199, 6, 'civicrm_address', 'street_address', NULL, 1),
(200, 6, 'civicrm_address', 'city', NULL, 1),
(201, 6, 'civicrm_email', 'email', NULL, 1),
(205, 7, 'civicrm_contact', 'household_name', NULL, 1),
(206, 7, 'civicrm_address', 'street_address', NULL, 1),
(207, 7, 'civicrm_address', 'city', NULL, 1),
(208, 4, 'civicrm_address', 'street_address', NULL, 1),
(209, 4, 'civicrm_contact', 'suffix_id', NULL, 1),
(210, 4, 'civicrm_contact', 'middle_name', NULL, 1),
(211, 4, 'civicrm_contact', 'first_name', NULL, 1),
(212, 4, 'civicrm_contact', 'last_name', NULL, 1),
(213, 11, 'civicrm_email', 'email', NULL, 2),
(214, 11, 'civicrm_address', 'street_address', NULL, 2),
(215, 11, 'civicrm_contact', 'last_name', NULL, 3),
(220, 8, 'civicrm_contact', 'birth_date', NULL, 1),
(221, 8, 'civicrm_contact', 'last_name', NULL, 1),
(222, 8, 'civicrm_contact', 'first_name', NULL, 1),
(223, 8, 'civicrm_address', 'city', NULL, 1),
(224, 1, 'civicrm_contact', 'suffix_id', NULL, 1),
(225, 1, 'civicrm_address', 'city', NULL, 1),
(226, 1, 'civicrm_contact', 'first_name', NULL, 1),
(227, 1, 'civicrm_contact', 'last_name', NULL, 1),
(228, 1, 'civicrm_address', 'street_address', NULL, 1),
(229, 9, 'civicrm_contact', 'first_name', NULL, 1),
(230, 9, 'civicrm_contact', 'last_name', NULL, 1),
(231, 9, 'civicrm_email', 'email', NULL, 1),
(236, 2, 'civicrm_contact', 'organization_name', NULL, 1),
(237, 2, 'civicrm_address', 'street_address', NULL, 1),
(238, 2, 'civicrm_address', 'postal_code', NULL, 1),
(239, 2, 'civicrm_address', 'city', NULL, 1),
(240, 10, 'civicrm_contact', 'organization_name', NULL, 1),
(241, 10, 'civicrm_address', 'street_address', NULL, 1),
(242, 10, 'civicrm_address', 'city', NULL, 1),
(243, 5, 'civicrm_contact', 'organization_name', NULL, 1),
(244, 5, 'civicrm_address', 'street_address', NULL, 1),
(245, 5, 'civicrm_address', 'city', NULL, 1),
(246, 5, 'civicrm_email', 'email', NULL, 1),
(251, 3, 'civicrm_contact', 'household_name', NULL, 1),
(252, 3, 'civicrm_address', 'street_address', NULL, 1),
(253, 3, 'civicrm_address', 'postal_code', NULL, 1),
(254, 3, 'civicrm_address', 'city', NULL, 1);
SET FOREIGN_KEY_CHECKS=1;"
$execSql -i $instance -c "$deduperules"

## enable mailing panel in advanced search
advsearch="
UPDATE civicrm_preferences 
SET advanced_search_options = '123456101316171819'
WHERE id = 1;"
$execSql -i $instance -c "$advsearch"

### Cleanup ###

$script_dir/clearCache.sh $instance
