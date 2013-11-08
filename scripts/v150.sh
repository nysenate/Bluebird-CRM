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
echo "cleaning up word replacements list..."
sql="
  UPDATE civicrm_domain
  SET locale_custom_strings = 'a:1:{s:5:\"en_US\";a:2:{s:7:\"enabled\";a:2:{s:13:\"wildcardMatch\";a:15:{s:7:\"CiviCRM\";s:8:\"Bluebird\";s:9:\"Full-text\";s:13:\"Find Anything\";s:16:\"Addt\'l Address 1\";s:15:\"Mailing Address\";s:16:\"Addt\'l Address 2\";s:8:\"Building\";s:73:\"Supplemental address info, e.g. c/o, department name, building name, etc.\";s:70:\"Department name, building name, complex, or extension of company name.\";s:7:\"deatils\";s:7:\"details\";s:11:\"sucessfully\";s:12:\"successfully\";s:40:\"groups, contributions, memberships, etc.\";s:27:\"groups, relationships, etc.\";s:18:\"email OR an OpenID\";s:5:\"email\";s:6:\"Client\";s:11:\"Constituent\";s:6:\"client\";s:11:\"constituent\";s:9:\"Job title\";s:9:\"Job Title\";s:9:\"Nick Name\";s:8:\"Nickname\";s:8:\"CiviMail\";s:12:\"BluebirdMail\";s:18:\"CiviCase Dashboard\";s:14:\"Case Dashboard\";}s:10:\"exactMatch\";a:8:{s:8:\"Position\";s:9:\"Job Title\";s:2:\"Id\";s:2:\"ID\";s:10:\"CiviReport\";s:7:\"Reports\";s:8:\"CiviCase\";s:5:\"Cases\";s:12:\"Do not trade\";s:26:\"Undeliverable: Do not mail\";s:11:\"Do not mail\";s:18:\"Do not postal mail\";}}s:8:\"disabled\";a:2:{s:13:\"wildcardMatch\";a:0:{}s:10:\"exactMatch\";a:0:{}}}}'
  WHERE id = 1;
"
$execSql -i $instance -c "$sql"

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
$execSql -i $instance -c "$sql"

## rebuild dedupe rules
echo "rebuilding dedupe rules..."
$script_dir/dedupeSetup.sh $instance -r

## remove civicrm blog dashlet
$sql="
  DELETE FROM civicrm_dashboard
  WHERE url = 'civicrm/dashlet/blog&reset=1&snippet=5';
"
$execSql -i $instance -c "$sql"

### Cleanup ###
echo "Cleaning up by performing clearCache"
$script_dir/clearCache.sh $instance
