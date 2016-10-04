#!/bin/sh
#
# v121_navsort.sh
#
# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2011-04-12
#
# alter permissions on district stats navigation
# alter sort name construction and rebuild existing values
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

###### Begin Update Scripts ######

## navigation menu items
navigation="UPDATE civicrm_navigation SET permission = 'access CiviCRM' WHERE name = 'District Stats';"
$execSql -i $instance -c "$navigation"

## set sort name formula ##
sortname="UPDATE civicrm_preferences SET sort_name_format='{contact.last_name}{, }{contact.first_name}{ }{contact.middle_name}{, }{contact.individual_suffix}' WHERE id=1;"
$execSql -i $instance -c "$sortname"

## rebuild all existing sort names ##
sortrebuild="UPDATE civicrm_contact
SET sort_name = TRIM(CONCAT(last_name, ', ', IF(first_name IS NOT NULL, first_name, ''), IF(middle_name IS NOT NULL, ' ', ''), IF(middle_name IS NOT NULL, middle_name, ''), ', ', (SELECT label FROM civicrm_option_value WHERE value = suffix_id AND option_group_id = 7) ))
WHERE contact_type = 'Individual' AND suffix_id IS NOT NULL;"
$execSql -i $instance -c "$sortrebuild"

###### Cleanup ######

$script_dir/clearCache.sh $instance
