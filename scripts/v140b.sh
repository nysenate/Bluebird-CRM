#!/bin/sh
#
# v140b.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2013-05-06
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

data_rootdir=`$readConfig --ig $instance data.rootdir` || data_rootdir="$DEFAULT_DATA_ROOTDIR"
app_rootdir=`$readConfig --ig $instance app.rootdir` || app_rootdir="$DEFAULT_APP_ROOTDIR"
webdir=`$readConfig --global drupal.rootdir` || webdir="$DEFAULT_DRUPAL_ROOTDIR"
base_domain=`$readConfig --ig $instance base.domain` || base_domain="$DEFAULT_BASE_DOMAIN"
db_basename=`$readConfig --ig $instance db.basename` || db_basename="$instance"
log_db_prefix=`$readConfig --ig $instance db.log.prefix` || log_db_prefix="$DEFAULT_BASE_DOMAIN"
civi_db_prefix=`$readConfig --ig $instance db.civicrm.prefix` || civi_db_prefix="$DEFAULT_BASE_DOMAIN"
cdb="$civi_db_prefix$db_basename"

## allow admin/office admin to assign manage inbox role
sql="
  INSERT IGNORE INTO role_permission (rid, permission, module) VALUES
    (9, 'edit users with role ManageBluebirdInbox', 'administerusersbyrole'),
    (9, 'edit users with role ManageBluebirdInbox and other roles', 'administerusersbyrole'),
    (4, 'edit users with role ManageBluebirdInbox', 'administerusersbyrole'),
    (4, 'edit users with role ManageBluebirdInbox and other roles', 'administerusersbyrole');
"
$execSql -i $instance -c "$sql" --drupal -q

### Cleanup ###
$script_dir/clearCache.sh $instance
