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

echo "$prog: 9515: add recently created contacts dashlet"
sql="
  SELECT @dashid:=id FROM civicrm_dashboard WHERE name = 'recentlyCreatedContacts';
  DELETE FROM civicrm_dashboard_contact WHERE dashboard_id = @dashid;
  DELETE FROM civicrm_dashboard WHERE id = @dashid;
  INSERT INTO civicrm_dashboard (domain_id, name, label, url, permission, permission_operator, column_no, is_minimized, is_fullscreen, is_active, is_reserved, weight, fullscreen_url) VALUES
(1, 'recentlyCreatedContacts', 'Recently Created Contacts', 'civicrm/dashlet/newcontacts&reset=1&snippet=4', 'access CiviCRM', NULL, 0, 1, 1, 1, 1, 1, 'civicrm/dashlet/newcontacts&reset=1&snippet=4&context=dashletFullscreen');

  DELETE FROM civicrm_dashboard WHERE name = 'recent_website_contacts';
"
$execSql $instance -c "$sql" -q
