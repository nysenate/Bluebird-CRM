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

## create blockedips table manually to avoid upgrade script issues
blockedips="DROP TABLE IF EXISTS blocked_ips;
            CREATE TABLE IF NOT EXISTS `blocked_ips` (
              iid int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key: unique ID for IP addresses.',
              ip varchar(40) NOT NULL DEFAULT '' COMMENT 'IP address',
              PRIMARY KEY (iid),
              KEY blocked_ip (ip)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores blocked IP addresses.' AUTO_INCREMENT=1 ;"
$execSql -i $instance -c "$blockedips" --drupal

## disable some modules we are not using
echo "disabling modules for: $instance"
$drush $instance dis color -y
$drush $instance dis comment -y
$drush $instance dis help -y
$drush $instance dis taxonomy -y
$drush $instance dis update -y
$drush $instance dis admin_menu -y
$drush $instance dis imce -y

echo "enabling modules for: $instance"
$drush $instance en apc -y
$drush $instance en ldap -y

## run drupal upgrade


### CiviCRM ###

## run civicrm upgrade
$drush $instance civicrm-upgrade-db


### Cleanup ###

$script_dir/clearCache.sh $instance
