#!/bin/sh
#
# v134.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2012-02-09
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

### CiviCRM ###

# 4781 Register Activity Tag report
acttag="INSERT INTO civicrm_option_value (option_group_id, label, value, name, grouping, filter, is_default, weight, description, is_optgroup, is_reserved, is_active, component_id, domain_id, visibility_id) VALUES
(40, 'Activity Tag Report', 'activity/tag', 'CRM_Report_Form_ActivityTag', NULL, 0, 0, 201, 'View activities grouped by activity tags.', 0, 0, 1, NULL, NULL, NULL);"
$execSql -i $instance -c "$acttag"



