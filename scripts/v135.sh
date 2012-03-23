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

# TODO: construct logging report instance
# TODO: set report instance to access civiReport

### Cleanup ###

$script_dir/clearCache.sh $instance
