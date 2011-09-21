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
(1, 'Bluebird News', 'civicrm/dashlet/news&reset=1&snippet=4', 'access CiviCRM', NULL, 0, 1, 0, 1, 1, 1, 'civicrm/dashlet/news&reset=1&snippet=4&context=dashletFullscreen');"
$execSql -i $instance -c "$news"

## add reserved tag perm to admin, office admin
perms_upd="create users, delete users with role Analytics User, delete users with role Conference Services, delete users with role Data Entry, delete users with role Mailing Approver, delete users with role Mailing Creator, delete users with role Mailing Scheduler, delete users with role Office Administrator, delete users with role Office Manager, delete users with role Print Production, delete users with role SOS, delete users with role Staff, delete users with role Volunteer, edit users with role Analytics User, edit users with role Conference Services, edit users with role Data Entry, edit users with role Mailing Approver, edit users with role Mailing Creator, edit users with role Mailing Scheduler, edit users with role Office Administrator, edit users with role Office Manager, edit users with role Print Production, edit users with role SOS, edit users with role Staff, edit users with role Volunteer, access CiviCRM, access CiviReport, access Contact Dashboard, access Report Criteria, access all cases and activities, access all custom data, access deleted contacts, access my cases and activities, access uploaded files, add contacts, administer CiviCRM, administer Reports, administer dedupe rules, administer reserved tags, delete activities, delete contacts, delete in CiviCase, edit all contacts, edit groups, import contacts, merge duplicate contacts, profile listings, profile listings and forms, profile view, view all activities, view all contacts, delete contacts permanently, export print production files, assign roles, access administration pages, administer users' WHERE rid = 4;
UPDATE permission SET perm = 'create users, delete users with role Analytics User, delete users with role Conference Services, delete users with role Data Entry, delete users with role Office Manager, delete users with role Print Production, delete users with role SOS, delete users with role Staff, delete users with role Volunteer, edit users with role Analytics User, edit users with role Conference Services, edit users with role Data Entry, edit users with role Office Manager, edit users with role Print Production, edit users with role SOS, edit users with role Staff, edit users with role Volunteer, access CiviCRM, access CiviReport, access Contact Dashboard, access Report Criteria, access all cases and activities, access all custom data, access deleted contacts, access my cases and activities, access uploaded files, add contacts, administer Reports, administer reserved tags, delete activities, delete contacts, delete in CiviCase, edit all contacts, edit groups, merge duplicate contacts, profile listings, profile listings and forms, profile view, view all activities, view all contacts, administer district, delete contacts permanently, assign roles, access administration pages, administer users' WHERE rid = 9;"
$execSql -i $instance -c "$perms_upd" --drupal


### Cleanup ###

$script_dir/clearCache.sh $instance
