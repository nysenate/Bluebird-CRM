#!/bin/sh
#
# v154.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2014-05-27
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

## run drupal upgrade
echo "7887: run drupal db upgrade..."
$drush $instance updb -y -q

echo "7888: upgrade CiviCRM core to v4.4.5..."
$drush $instance civicrm-upgrade-db -y -q

echo "7747/7746: register custom search..."
sql="
  SELECT @optGrp:=id FROM civicrm_option_group WHERE name = 'custom_search';
  DELETE FROM civicrm_option_value
    WHERE option_group_id = @optGrp
    AND name = 'CRM_Contact_Form_Search_Custom_TagGroupLog';
  INSERT INTO civicrm_option_value
    (option_group_id, label, value, name, grouping, filter, is_default, weight, description, is_optgroup, is_reserved, is_active, component_id, domain_id, visibility_id)
    VALUES
    (@optGrp, 'CRM_Contact_Form_Search_Custom_TagGroupLog', '17', 'CRM_Contact_Form_Search_Custom_TagGroupLog', NULL, 0, 0, 17, 'Tag/Group Log Search', 0, 0, 1, NULL, NULL, NULL);
"
$execSql $instance -c "$sql" -q

## 6721 create FTS indices
php $app_rootdir/civicrm/scripts/ftsIndexUpdate.php -S $instance

echo "7949: rebuild shadow tables..."
dedupe_dir=$script_dir/../modules/nyss_dedupe
$execSql $instance -f $dedupe_dir/shadow_sync.sql
