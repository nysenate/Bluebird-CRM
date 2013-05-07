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
    (4, 'edit users with role SOS', 'administerusersbyrole'),
    (4, 'edit users with role Staff', 'administerusersbyrole'),
    (4, 'edit users with role DataEntry', 'administerusersbyrole'),
    (4, 'edit users with role Volunteer', 'administerusersbyrole'),
    (4, 'edit users with role MailingViewer', 'administerusersbyrole'),
    (4, 'edit users with role OfficeManager', 'administerusersbyrole'),
    (4, 'edit users with role MailingCreator', 'administerusersbyrole'),
    (4, 'edit users with role MailingApprover', 'administerusersbyrole'),
    (4, 'edit users with role MailingScheduler', 'administerusersbyrole'),
    (4, 'edit users with role ManageBluebirdInbox', 'administerusersbyrole'),
    (4, 'edit users with role OfficeAdministrator', 'administerusersbyrole'),
    (9, 'edit users with role SOS', 'administerusersbyrole'),
    (9, 'edit users with role Staff', 'administerusersbyrole'),
    (9, 'edit users with role DataEntry', 'administerusersbyrole'),
    (9, 'edit users with role Volunteer', 'administerusersbyrole'),
    (9, 'edit users with role MailingViewer', 'administerusersbyrole'),
    (9, 'edit users with role OfficeManager', 'administerusersbyrole'),
    (9, 'edit users with role MailingCreator', 'administerusersbyrole'),
    (9, 'edit users with role MailingApprover', 'administerusersbyrole'),
    (9, 'edit users with role MailingScheduler', 'administerusersbyrole'),
    (9, 'edit users with role ManageBluebirdInbox', 'administerusersbyrole');
"
$execSql -i $instance -c "$sql" --drupal -q

sql="
  DELETE FROM role_permission
  WHERE module = ''
    AND permission != 'use PHP for settings';
"
$execSql -i $instance -c "$sql" --drupal -q

## set role assign values
sql="
  UPDATE variable
  SET value = 0x613a31373a7b693a383b733a313a2238223b693a353b733a313a2235223b693a31323b733a323a223132223b693a31363b733a323a223136223b693a31343b733a323a223134223b693a31353b733a323a223135223b693a31373b733a323a223137223b693a31393b733a323a223139223b693a393b733a313a2239223b693a31303b733a323a223130223b693a373b733a313a2237223b693a363b733a313a2236223b693a31313b733a323a223131223b693a31333b733a323a223133223b693a343b693a303b693a31383b693a303b693a333b693a303b7d
  WHERE name = 'roleassign_roles';
"
$execSql -i $instance -c "$sql" --drupal
