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


### Cleanup ###

$script_dir/clearCache.sh $instance
