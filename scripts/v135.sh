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
webdir=`$readConfig --global drupal.rootdir` || webdir="$DEFAULT_DRUPAL_ROOTDIR"
base_domain=`$readConfig --ig $instance base.domain` || base_domain="$DEFAULT_BASE_DOMAIN"
formal_name=`$readConfig --ig $instance senator.name.formal` || formal_name="Senator"

###### Begin Upgrade Scripts ######

### Drupal ###



### CiviCRM ###

# Remove old triggers to make way for new CiviCRM triggers
$execSql -i $instance -c "DROP TRIGGER IF EXISTS shadow_address_update_trigger;"
$execSql -i $instance -c "DROP TRIGGER IF EXISTS shadow_address_delete_trigger;"
$execSql -i $instance -c "DROP TRIGGER IF EXISTS shadow_address_insert_trigger;"
$execSql -i $instance -c "DROP TRIGGER IF EXISTS shadow_contact_delete_trigger;"
$execSql -i $instance -c "DROP TRIGGER IF EXISTS shadow_contact_update_trigger;"
$execSql -i $instance -c "DROP TRIGGER IF EXISTS shadow_contact_insert_trigger;"

# TODO: enable change logging
# TODO: construct logging report instance [may not be necessary]
# TODO: set report instance to access civiReport

# 5036 create is_reserved group field and set reserved groups
res="ALTER TABLE civicrm_group ADD is_reserved TINYINT( 4 ) NULL DEFAULT '0'"
$execSql -i $instance -c "$res"

setGroups="UPDATE civicrm_group SET is_reserved = 1 WHERE name = 'Case_Resources' OR name = 'Office_Staff' OR name = 'Mailing_Exclusions' OR name = 'Mailing_Seeds' OR name = 'Bluebird_Mail_Subscription' OR name = 'Email_Seeds'"
$execSql -i $instance -c "$setGroups"

# TODO: set drupal roles with administer reserved groups perm

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

### Cleanup ###

$script_dir/clearCache.sh $instance
