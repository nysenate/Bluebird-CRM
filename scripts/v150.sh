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
sql="
  ALTER TABLE civicrm_word_replacement DROP INDEX UI_find;
"
$execSql $instance -c "$sql" -q
sql="
  ALTER TABLE civicrm_word_replacement DROP INDEX UI_domain_find, ADD INDEX UI_domain_find (domain_id, find_word) COMMENT  '';
"
$execSql $instance -c "$sql" -q
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

## remove mapping key
echo "removing google mapping key..."
sql="
UPDATE civicrm_domain
  SET config_backend = REPLACE(config_backend, '\"mapAPIKey\";s:86:\"ABQIAAAAOAfBnp7jqzymWnSA-s1NzxQuOUP8hd2qhSL-nJEVisOKANWd3xTc9jRNBXFpXOoJGkNnNxugAV8jqg\";', '\"mapAPIKey\";s:0:\"\";')
  WHERE id = 1;
"
$execSql -i $instance -c "$sql" -q

## 7397 remove version alert
echo "removing version alert notification..."
sql="
  UPDATE civicrm_setting
  SET value='s:1:\"0\";'
  WHERE name='versionAlert';
"
$execSql -i $instance -c "$sql" -q

### Cleanup ###
echo "Cleaning up by performing clearCache"
$script_dir/clearCache.sh $instance
