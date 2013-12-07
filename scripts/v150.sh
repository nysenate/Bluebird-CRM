#!/bin/sh
#
# v150.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2013-11-04
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

## cleanup word replacements before upgrade
echo "clearing word replacements list..."
sql="
  UPDATE civicrm_domain
  SET locale_custom_strings = NULL
  WHERE id = 1;
"
$execSql $instance -c "$sql" -q

## upgrade civicrm db
echo "running civicrm db upgrade..."
$drush $instance civicrm-upgrade-db

## enable the nyss_signupreport module
echo "enabling nyss_signupreport module"
$drush $instance en nyss_signupreport -y

## set mailing preferences
echo "setting mailing preferences..."
sql="
  DELETE FROM civicrm_setting
  WHERE name = 'write_activity_record' OR name = 'disable_mandatory_tokens_check';
  INSERT INTO civicrm_setting (group_name, name, value, domain_id, is_domain, created_date, created_id)
  VALUES ('Mailing Preferences', 'write_activity_record', 'i:0;', 1, 1, NOW(), 1),
    ('Mailing Preferences', 'disable_mandatory_tokens_check', 'i:1;', 1, 1, NOW(), 1);
"
$execSql $instance -c "$sql" -q

## rebuild dedupe rules
echo "rebuilding dedupe rules..."
$script_dir/dedupeSetup.sh $instance -r

## rebuild word replacement
echo "rebuilding word replacement list..."
$execSql $instance -f $app_rootdir/scripts/sql/wordReplacement.sql -q

## resetting component config
sql="
  UPDATE civicrm_setting
  SET value = 'a:3:{i:0;s:8:\"CiviMail\";i:1;s:10:\"CiviReport\";i:2;s:8:\"CiviCase\";}'
  WHERE name = 'enable_components'
"
$execSql $instance -c "$sql" -q

## UI cleanup
echo "cleaning up some UI and config changes..."

## remove civicrm blog dashlet
sql="
  DELETE FROM civicrm_dashboard
  WHERE url = 'civicrm/dashlet/blog&reset=1&snippet=5';
"
$execSql $instance -c "$sql" -q

## cleaning report instances
echo "cleaning report instances..."
sql="
  DELETE FROM civicrm_report_instance
  WHERE report_id LIKE 'contribute%'
    OR report_id LIKE 'event%'
    OR report_id LIKE 'grant%'
    OR report_id LIKE 'member%'
    OR report_id LIKE 'pledge%'
    OR report_id LIKE 'survey%'
"
$execSql $instance -c "$sql" -q

## 7397 remove version alert
echo "removing version alert notification..."
sql="
  UPDATE civicrm_setting
  SET value='s:1:\"0\";'
  WHERE name='versionAlert';
"
$execSql $instance -c "$sql" -q

## 5533 cleanup some activity types we don't need
echo "disabling some activity types we dont use..."
sql="
  SELECT @act:= id
  FROM civicrm_option_group
  WHERE name = 'activity_type';

  UPDATE civicrm_option_value
  SET is_active = 0
  WHERE option_group_id = @act
    AND ( component_id IN (1, 2, 3, 6, 9) OR name LIKE '%SMS%' OR name LIKE '%contribution%' );
"
$execSql $instance -c "$sql" -q

## wkhtmltopdf/remove mapping key
echo "setting wkhtmltopdf path and removing mapping key..."
$script_dir/manageCiviConfig.sh $instance --update --config-backend

## reset rules config
echo "setting rules config..."
$execSql $instance -f $app_rootdir/scripts/sql/rulesConfig.sql --drupal -q

### Cleanup ###
echo "Cleaning up by performing clearCache"
$script_dir/clearCache.sh $instance
