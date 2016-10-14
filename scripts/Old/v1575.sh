#!/bin/sh
#
# v1575.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2015-11-30
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh

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

app_rootdir=`$readConfig --ig $instance app.rootdir` || app_rootdir="$DEFAULT_APP_ROOTDIR"

echo "$prog: 9526: web activity stream search"
sql="
  SELECT @optgrp:=id FROM civicrm_option_group WHERE name = 'custom_search';
  DELETE FROM civicrm_option_value WHERE label = 'CRM_Contact_Form_Search_Custom_WebActivityStream';
  INSERT INTO civicrm_option_value
  (option_group_id, label, value, name, grouping, filter, is_default, weight, description, is_optgroup, is_reserved, is_active, component_id, domain_id, visibility_id)
  VALUES
  (@optgrp, 'CRM_Contact_Form_Search_Custom_WebActivityStream', '18', 'CRM_Contact_Form_Search_Custom_WebActivityStream', NULL, 0, 0, 18, 'Website Activity Stream Search', 0, 0, 1, NULL, NULL, NULL);
"
$execSql $instance -c "$sql" -q
