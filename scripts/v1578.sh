#!/bin/sh
#
# v1578.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2016-04-18
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

echo "9975: register custom search..."
sql="
  SELECT @optGrp:=id FROM civicrm_option_group WHERE name = 'custom_search';
  DELETE FROM civicrm_option_value
    WHERE option_group_id = @optGrp
    AND name = 'CRM_Contact_Form_Search_Custom_TagContactLog';
  INSERT INTO civicrm_option_value
    (option_group_id, label, value, name, grouping, filter, is_default, weight, description, is_optgroup, is_reserved, is_active, component_id, domain_id, visibility_id)
    VALUES
    (@optGrp, 'CRM_Contact_Form_Search_Custom_TagContactLog', '19', 'CRM_Contact_Form_Search_Custom_TagContactLog', NULL, 0, 0, 19, 'Tag Count Search', 0, 0, 1, NULL, NULL, NULL);
"
$execSql $instance -c "$sql" -q
