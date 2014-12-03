#!/bin/sh
#
# v1541.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2014-12-02
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

echo "8246: ensure Matched Inbound Email report exists..."
sql="SELECT id FROM civicrm_report_instance WHERE title = 'Matched Inbound Emails, Last 7 Days'"
rptid=`$execSql $instance -c "$sql" -q`

if [ $rptid -eq 0 ]; then
  echo "The Inbound Email report could not be found."
  exit 1
fi

sql="SELECT id FROM civicrm_dashboard WHERE label = 'Matched Inbound Emails, Last 7 Days'"
dashid=`$execSql $instance -c "$sql" -q`

if [ $rptid -ne 0 ]; then
  echo "The Inbound Email dashlet already exists."
  exit 1
fi

sql="
  INSERT INTO civicrm_dashboard (domain_id, name, label, url, permission, permission_operator, column_no, is_minimized, is_fullscreen, is_active, is_reserved, weight, fullscreen_url)
  VALUES
  (1, 'report/$rptid', 'Matched Inbound Emails, Last 7 Days', 'civicrm/report/instance/$rptid&reset=1&section=2&snippet=5&context=dashlet', 'access CiviReport', NULL, 0, 0, 1, 1, 1, 0, 'civicrm/report/instance/$rptid&reset=1&section=2&snippet=5&context=dashletFullscreen');
"
$execSql $instance -c "$sql" -q

echo "Inbound Email dashlet has been added."
