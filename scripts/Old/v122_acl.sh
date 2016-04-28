#!/bin/sh
#
# v122_acl.sh
#
# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2011-06-21
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

## update permissions
updateperms="UPDATE permission SET perm = 'create users, delete users with role Analytics User, delete users with role Conference Services, delete users with role Data Entry, delete users with role Office Manager, delete users with role Print Production, delete users with role SOS, delete users with role Staff, delete users with role Volunteer, edit users with role Analytics User, edit users with role Conference Services, edit users with role Data Entry, edit users with role Office Manager, edit users with role Print Production, edit users with role SOS, edit users with role Staff, edit users with role Volunteer, access CiviCRM, access CiviReport, access Contact Dashboard, access Report Criteria, access all cases and activities, access all custom data, access deleted contacts, access my cases and activities, access uploaded files, add contacts, administer district, administer Reports, delete activities, delete contacts, delete in CiviCase, delete in CiviMail, edit all contacts, edit groups, merge duplicate contacts, profile listings, profile listings and forms, profile view, view all activities, view all contacts, delete contacts permanently, assign roles, access administration pages, administer users' WHERE rid = 9;
UPDATE permission SET perm = 'access CiviCRM, access CiviReport, access Contact Dashboard, access Report Criteria, access all cases and activities, access all custom data, access deleted contacts, access my cases and activities, access uploaded files, add contacts, administer Reports, delete activities, delete contacts, delete in CiviCase, delete in CiviMail, edit all contacts, edit groups, profile listings, profile listings and forms, profile view, view all activities, view all contacts' WHERE rid = 10;"
$execSql -i $instance -c "$updateperms" --drupal


### CiviCRM ###

## update navigation items
navigation="UPDATE civicrm_navigation SET permission = 'administer CiviCRM,administer district',permission_operator = 'OR' WHERE id = 28;
UPDATE civicrm_navigation SET permission = 'administer CiviCRM,administer district',permission_operator = 'OR' WHERE id = 29;
UPDATE civicrm_navigation SET permission = 'administer CiviCRM,administer district',permission_operator = 'OR' WHERE id = 202;
UPDATE civicrm_navigation SET permission = 'administer CiviCRM,administer district',permission_operator = 'OR' WHERE id = 203;
UPDATE civicrm_navigation SET permission = 'administer CiviCRM,administer district',permission_operator = 'OR' WHERE id = 212;"
$execSql -i $instance -c "$navigation"


### Cleanup ###

$script_dir/fixPermissions.sh
$script_dir/clearCache.sh $instance
