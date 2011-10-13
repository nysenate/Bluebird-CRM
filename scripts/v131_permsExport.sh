#!/bin/sh
#
# v131_permsExport.sh
#
# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2011-10-04
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

## add access CiviMail perm to admin
perms="
UPDATE permission SET perm = 'create users, delete users with role Analytics User, delete users with role Conference Services, delete users with role Data Entry, delete users with role Mailing Approver, delete users with role Mailing Creator, delete users with role Mailing Scheduler, delete users with role Office Administrator, delete users with role Office Manager, delete users with role Print Production, delete users with role SOS, delete users with role Staff, delete users with role Volunteer, edit users with role Analytics User, edit users with role Conference Services, edit users with role Data Entry, edit users with role Mailing Approver, edit users with role Mailing Creator, edit users with role Mailing Scheduler, edit users with role Office Administrator, edit users with role Office Manager, edit users with role Print Production, edit users with role SOS, edit users with role Staff, edit users with role Volunteer, access CiviCRM, access CiviMail, access CiviReport, access Contact Dashboard, access Report Criteria, access all cases and activities, access all custom data, access deleted contacts, access my cases and activities, access uploaded files, add contacts, administer CiviCRM, administer Reports, administer dedupe rules, administer reserved tags, delete activities, delete contacts, delete in CiviCase, edit all contacts, edit groups, import contacts, merge duplicate contacts, profile listings, profile listings and forms, profile view, view all activities, view all contacts, delete contacts permanently, export print production files, assign roles, access administration pages, administer users' WHERE rid = 4;"
$execSql -i $instance -c "$perms" --drupal


### CiviCRM ###

## 4403 set do not mail for all contacts in Email Only group
emgroup="
SELECT @emailOnlyGroupID := id FROM civicrm_group WHERE name = 'Email_Only';
UPDATE civicrm_contact c
  JOIN civicrm_group_contact gc ON ( gc.contact_id = c.id AND gc.group_id = @emailOnlyGroupID )
SET do_not_mail = 1;"
$execSql -i $instance -c "$emgroup"

## 3812 set news dashlet on all contacts
newsdash="
SELECT @newsID := id FROM civicrm_dashboard WHERE label = 'Bluebird News';
INSERT INTO civicrm_dashboard_contact (dashboard_id, contact_id, column_no, is_active)
  SELECT @newsID, uf.contact_id, 1, 1
  FROM civicrm_uf_match uf
    LEFT JOIN civicrm_dashboard_contact dc ON (uf.contact_id = dc.contact_id AND dc.dashboard_id = @newsID)
  WHERE dc.id IS NULL;
UPDATE civicrm_dashboard_contact dc
  SET column_no = 1, is_active = 1
  WHERE dc.dashboard_id = @newsID;"
$execSql -i $instance -c "$newsdash"


### Cleanup ###

$script_dir/clearCache.sh $instance
