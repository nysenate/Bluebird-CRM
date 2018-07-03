#!/bin/sh
#
# v22.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2018-05-15
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
drush=$script_dir/drush.sh
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

## upgrade civicrm db
echo "running civicrm db upgrade..."
$drush $instance civicrm-upgrade-db

## 8034
echo "$prog: alter activity assignment email subject"
sql="
  UPDATE civicrm_msg_template
  SET msg_subject = '[Bluebird] {if $isCaseActivity}Case{else}Constituent{/if} Activity: {contact.display_name}'
  WHERE msg_title = 'Cases - Send Copy of an Activity';
"
$execSql $instance -c "$sql" -q

## 7362
echo "$prog: install activity extension"
$drush $instance cvapi extension.install key=gov.nysenate.activity --quiet

## 11725
echo "$prog: update role sort order"
sql="
  UPDATE role SET weight = 51 WHERE role.rid = 4;
  UPDATE role SET weight = 31 WHERE role.rid = 8;
  UPDATE role SET weight = 0 WHERE role.rid = 1;
  UPDATE role SET weight = 0 WHERE role.rid = 2;
  UPDATE role SET weight = 32 WHERE role.rid = 5;
  UPDATE role SET weight = 4 WHERE role.rid = 12;
  UPDATE role SET weight = 11 WHERE role.rid = 16;
  UPDATE role SET weight = 12 WHERE role.rid = 14;
  UPDATE role SET weight = 13 WHERE role.rid = 15;
  UPDATE role SET weight = 14 WHERE role.rid = 17;
  UPDATE role SET weight = 21 WHERE role.rid = 19;
  UPDATE role SET weight = 1 WHERE role.rid = 9;
  UPDATE role SET weight = 2 WHERE role.rid = 10;
  UPDATE role SET weight = 33 WHERE role.rid = 7;
  UPDATE role SET weight = 52 WHERE role.rid = 18;
  UPDATE role SET weight = 34 WHERE role.rid = 6;
  UPDATE role SET weight = 3 WHERE role.rid = 11;
  UPDATE role SET weight = 53 WHERE role.rid = 3;
  UPDATE role SET weight = 5 WHERE role.rid = 13;
"
$execSql -i $instance -c "$sql" --drupal -q

## 11887
echo "$prog: install reports extension"
$drush $instance cvapi extension.install key=gov.nysenate.reports --quiet

## record completion
echo "$prog: upgrade process is complete."
