#!/bin/sh
#
# v132.sh
#
# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2011-10-18
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

## run civicrm db upgrade using drush
$drush $instance civicrm-upgrade-db


### Drupal ###


### CiviCRM ###

## remove v3.4.6 nav items
nav="
UPDATE civicrm_navigation
SET is_active = 0
WHERE label = 'New Price Set' OR label = 'Manage Price Sets';"
$execSql -i $instance -c "$nav"

## 3439 report permissions by role
rpt="ALTER TABLE civicrm_report_instance ADD grouprole VARCHAR( 1020 ) NULL AFTER permission;"
$execSql -i $instance -c "$rpt"

### Cleanup ###

$script_dir/clearCache.sh $instance
