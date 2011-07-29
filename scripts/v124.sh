#!/bin/sh
#
# v124.sh
#
# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2011-07-27
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

###### Begin Upgrade Scripts ######

### CiviCRM ###

## 3837 update suffix values
suffix="
UPDATE civicrm_option_value SET value = 'MD',  name = 'MD'  WHERE option_group_id = 7 AND name = 'M.D.';
UPDATE civicrm_option_value SET value = 'PhD', name = 'PhD' WHERE option_group_id = 7 AND name = 'Ph.D.';
UPDATE civicrm_option_value SET value = 'DDS', name = 'DDS' WHERE option_group_id = 7 AND name = 'D.D.S.';
UPDATE civicrm_option_value SET value = 'RN',  name = 'RN'  WHERE option_group_id = 7 AND name = 'R.N.';
UPDATE civicrm_option_value SET value = 'DC',  name = 'DC'  WHERE option_group_id = 7 AND name = 'D.C.';
UPDATE civicrm_option_value SET value = 'PE',  name = 'PE'  WHERE option_group_id = 7 AND name = 'P.E.';
UPDATE civicrm_option_value SET value = 'DVM', name = 'DVM' WHERE option_group_id = 7 AND name = 'D.V.M.';
;"
$execSql -i $instance -c "$suffix"

## 3869 add college
loctype="INSERT IGNORE INTO civicrm_location_type VALUES (14, 'College', '', '', NULL, 1, 0);"
$execSql -i $instance -c "$loctype"



