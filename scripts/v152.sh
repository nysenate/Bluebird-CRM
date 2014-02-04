#!/bin/sh
#
# v152.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2014-01-09
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

app_rootdir=`$readConfig --ig $instance app.rootdir` || app_rootdir="$DEFAULT_APP_ROOTDIR"

echo "upgrade CiviCRM core to v4.4.3..."
$drush $instance civicrm-upgrade-db

echo "cleaning up contact subrecords..."
sql="
  DELETE FROM civicrm_email WHERE contact_id IS NULL AND id != 1;
  DELETE FROM civicrm_address WHERE contact_id IS NULL;
  DELETE FROM civicrm_phone WHERE contact_id IS NULL;
  DELETE FROM civicrm_im WHERE contact_id IS NULL;
  DELETE FROM civicrm_website WHERE contact_id IS NULL;
"
$execSql $instance -c "$sql" -q

echo "disabling changelog panel in advanced search..."
sql="
  UPDATE civicrm_setting
  SET value = 's:29:\"12345101316171819\";'
  WHERE name = 'advanced_search_options'
"
$execSql $instance -c "$sql" -q

echo "7614: add new activity types..."
sql="
  SELECT @optgrp:=id FROM civicrm_option_group WHERE name = 'activity_type';
  SELECT @maxval:=max(cast(value as unsigned)) FROM civicrm_option_value WHERE option_group_id = @optgrp;
  DELETE FROM civicrm_option_value WHERE option_group_id = @optgrp AND (name = 'Certificate (outgoing)' OR name = 'Proclamation (outgoing)');
  INSERT INTO civicrm_option_value
    (option_group_id, label, value, name, grouping, filter, is_default, weight, is_optgroup, is_reserved, is_active, component_id, domain_id, visibility_id)
  VALUES
    (@optgrp, 'Certificate (outgoing)', @maxval + 1, 'Certificate (outgoing)', NULL, 0, NULL, @maxval + 1, 0, 1, 1, NULL, NULL, NULL),
    (@optgrp, 'Proclamation (outgoing)', @maxval + 2, 'Proclamation (outgoing)', NULL, 0, NULL, @maxval + 2, 0, 1, 1, NULL, NULL, NULL);
"
$execSql $instance -c "$sql" -q
