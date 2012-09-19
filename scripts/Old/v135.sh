#!/bin/sh
#
# v135.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2012-03-13
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

###### Begin Upgrade Scripts ######

### Drupal ###

# set drupal roles with administer reserved groups perm
roles="
UPDATE permission SET perm = 'create users, delete users with role Analytics User, delete users with role Conference Services, delete users with role Data Entry, delete users with role Mailing Approver, delete users with role Mailing Creator, delete users with role Mailing Scheduler, delete users with role Office Administrator, delete users with role Office Manager, delete users with role Print Production, delete users with role SOS, delete users with role Staff, delete users with role Volunteer, edit users with role Analytics User, edit users with role Conference Services, edit users with role Data Entry, edit users with role Mailing Approver, edit users with role Mailing Creator, edit users with role Mailing Scheduler, edit users with role Office Administrator, edit users with role Office Manager, edit users with role Print Production, edit users with role SOS, edit users with role Staff, edit users with role Volunteer, access CiviCRM, access CiviMail, access CiviReport, access Contact Dashboard, access Report Criteria, access all cases and activities, access all custom data, access deleted contacts, access my cases and activities, access uploaded files, add contacts, administer CiviCRM, administer Reports, administer dedupe rules, administer reserved tags, approve mailings, create mailings, delete activities, delete contacts, delete in CiviCase, delete in CiviMail, edit all contacts, edit groups, import contacts, merge duplicate contacts, profile listings, profile listings and forms, profile view, schedule mailings, view all activities, view all contacts, view all notes, administer reserved groups, delete contacts permanently, export print production files, assign roles, access administration pages, administer users' WHERE rid = 4;
UPDATE permission SET perm = 'access CiviCRM, access CiviReport, access all custom data, edit groups, import contacts, profile listings, profile view, view all contacts, administer reserved groups, export print production files, administer site configuration' WHERE rid = 7;
UPDATE permission SET perm = 'create users, delete users with role Administrator, delete users with role Analytics User, delete users with role Conference Services, delete users with role Data Entry, delete users with role Office Administrator, delete users with role Office Manager, delete users with role Print Production, delete users with role SOS, delete users with role Staff, delete users with role Volunteer, edit users with role Administrator, edit users with role Analytics User, edit users with role Conference Services, edit users with role Data Entry, edit users with role Mailing Approver, edit users with role Mailing Creator, edit users with role Mailing Scheduler, edit users with role Office Administrator, edit users with role Office Manager, edit users with role Print Production, edit users with role SOS, edit users with role Staff, edit users with role Volunteer, administer blocks, use PHP for block visibility, access CiviCRM, access CiviReport, access Contact Dashboard, access Report Criteria, access all cases and activities, access all custom data, access deleted contacts, access my cases and activities, access uploaded files, add contacts, administer CiviCRM, administer CiviCase, administer Reports, administer Tagsets, administer dedupe rules, administer reserved tags, delete activities, delete contacts, delete in CiviCase, edit all contacts, edit groups, import contacts, merge duplicate contacts, profile create, profile edit, profile listings, profile listings and forms, profile view, translate CiviCRM, view all activities, view all contacts, administer reserved groups, delete contacts permanently, export print production files, assign roles, access administration pages, access user profiles, administer permissions, administer users, administer userprotect' WHERE rid = 3;"
$execSql -i $instance -c "$roles" --drupal


### CiviCRM ###

# Remove old triggers to make way for new CiviCRM triggers
$execSql -i $instance -c "DROP TRIGGER IF EXISTS shadow_address_update_trigger;"
$execSql -i $instance -c "DROP TRIGGER IF EXISTS shadow_address_delete_trigger;"
$execSql -i $instance -c "DROP TRIGGER IF EXISTS shadow_address_insert_trigger;"
$execSql -i $instance -c "DROP TRIGGER IF EXISTS shadow_contact_delete_trigger;"
$execSql -i $instance -c "DROP TRIGGER IF EXISTS shadow_contact_update_trigger;"
$execSql -i $instance -c "DROP TRIGGER IF EXISTS shadow_contact_insert_trigger;"

# Create log database
ldb=$log_db_prefix$db_basename;
create="CREATE DATABASE IF NOT EXISTS $ldb"
$execSql -i $instance -c "$create"

# Enable change logging and build triggers
php "$app_rootdir/civicrm/scripts/enableLogging.php" "-S$instance"

# set log report instances to access civiReport
rptperm="UPDATE civicrm_report_instance SET permission = 'access CiviReport' WHERE report_id = 'logging/contact/detail' OR report_id = 'logging/contact/summary';";
$execSql -i $instance -c "$rptperm"

# 5036 create is_reserved group field and set reserved groups
res="ALTER TABLE civicrm_group ADD is_reserved TINYINT( 4 ) NULL DEFAULT '0'"
$execSql -i $instance -c "$res"

setGroups="UPDATE civicrm_group SET is_reserved = 1 WHERE name = 'Case_Resources' OR name = 'Office_Staff' OR name = 'Mailing_Exclusions' OR name = 'Mailing_Seeds' OR name = 'Bluebird_Mail_Subscription' OR name = 'Email_Seeds'"
$execSql -i $instance -c "$setGroups"

# 5113 alter on hold thresholds
thresh="
UPDATE civicrm_mailing_bounce_type SET hold_threshold = 1 WHERE id = 1;
UPDATE civicrm_mailing_bounce_type SET hold_threshold = 2 WHERE id = 2;
UPDATE civicrm_mailing_bounce_type SET hold_threshold = 2 WHERE id = 3;
UPDATE civicrm_mailing_bounce_type SET hold_threshold = 2 WHERE id = 4;
UPDATE civicrm_mailing_bounce_type SET hold_threshold = 1 WHERE id = 5;
UPDATE civicrm_mailing_bounce_type SET hold_threshold = 1 WHERE id = 6;
UPDATE civicrm_mailing_bounce_type SET hold_threshold = 2 WHERE id = 7;
UPDATE civicrm_mailing_bounce_type SET hold_threshold = 2 WHERE id = 8;
UPDATE civicrm_mailing_bounce_type SET hold_threshold = 2 WHERE id = 9;
UPDATE civicrm_mailing_bounce_type SET hold_threshold = 2 WHERE id = 10;
UPDATE civicrm_mailing_bounce_type SET hold_threshold = 2 WHERE id = 11;"
$execSql -i $instance -c "$thresh"

# 5166 rename logging reports
logreport="
UPDATE civicrm_report_instance SET title = 'Database Log (Enhanced)' WHERE title = 'Contact Logging Report (Summary)';
UPDATE civicrm_report_instance SET title = 'Database Log (Archived)' WHERE title = 'Database Log Report';
UPDATE civicrm_report_instance SET title = 'Database Log Detail (Enhanced)' WHERE title = 'Contact Logging Report (Detail)';
UPDATE civicrm_option_value SET label = 'Database Log (Enhanced)', description = 'This report displays a log of database changes from April 14, 2012 forward, when the enhanced logging capabilities were enabled. Older log records may still be accessed using the \"Database Log (Archived)\" report, which may be accessed from Reports > Create Reports from Templates.' WHERE label = 'Contact Logging Report (Summary)' AND option_group_id = 40;
UPDATE civicrm_option_value SET label = 'Database Log (Archived)', description = 'This report displays a basic log of database changes prior to April 14, 2012 forward, before the enhanced logging capabilities were enabled. For more recent changelog data go to Reports > Create Reports from Templates and select Database Log (Enhanced).' WHERE label = 'Database Log Report' AND option_group_id = 40;
UPDATE civicrm_option_value SET weight = 4 WHERE value = 'activitySummary' AND option_group_id = 40;"
$execSql -i $instance -c "$logreport"

### Cleanup ###

$script_dir/clearCache.sh $instance
