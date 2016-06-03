#!/bin/sh
#
# v1571.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2015-10-02
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

echo "$prog: 9523: link verification field to option group"
sql="
  SELECT @optgrp:=id FROM civicrm_option_group WHERE name = 'web_verification';
  UPDATE civicrm_custom_field SET option_group_id = @optgrp WHERE name = 'Verification';
"
$execSql $instance -c "$sql" -q

echo "$prog: 9515: make recent website contacts available to dashboard"
sql="
  DELETE FROM civicrm_dashboard WHERE name = 'recent_website_contacts';
  SELECT @rpt:=id FROM civicrm_report_instance WHERE title = 'Recent Website Contacts';
  INSERT INTO civicrm_dashboard (domain_id, name, label, url, permission, permission_operator, column_no, is_minimized, is_fullscreen, is_active, is_reserved, weight, fullscreen_url) VALUES
(1, 'recent_website_contacts', 'Recent Website Contacts', CONCAT('civicrm/report/instance/', @rpt, '&reset=1&section=2&snippet=5&context=dashlet'), 'access CiviReport', NULL, 0, 0, 1, 1, 1, 0, CONCAT('civicrm/report/instance/', @rpt, '&reset=1&section=2&snippet=5&context=dashlet'));

"
$execSql $instance -c "$sql" -q
