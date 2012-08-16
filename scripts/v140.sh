#!/bin/sh
#
# v140.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2012-08
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

###### Begin Upgrade Scripts ######

### Drupal ###

## manually disable various modules before running drupal upgrade
dismods="
UPDATE system
SET status = 0
WHERE name IN
  ('civicrm', 'userprotect', 'civicrm_rules', 'rules', 'rules_admin', 'apachesolr', 'apachesolr_search', 'color',
  'comment', 'help', 'taxonomy', 'update', 'admin_menu', 'imce');"
$execSql -i $instance -c "$dismods" --drupal

## run drupal upgrade
$drush $instance updb

## enable modules
echo "enabling modules for: $instance"
$drush $instance en civicrm -y
$drush $instance en userprotect -y
$drush $instance en civicrm_rules -y
$drush $instance en rules -y
$drush $instance en rules_admin -y
$drush $instance en apachesolr -y
$drush $instance en apachesolr_search -y
$drush $instance en apc -y
$drush $instance en ldap -y

## set theme
echo "setting theme for: $instance"
$drush $instance en Blueprint
$drush $instance vset theme_default Blueprint


### CiviCRM ###

## run civicrm upgrade
$drush $instance civicrm-upgrade-db


### Cleanup ###

$script_dir/clearCache.sh $instance
