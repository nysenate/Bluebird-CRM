#!/bin/sh
#
# v136.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2012-05-12
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

# 5253 remove create users perm
roles="UPDATE permission SET perm = REPLACE(perm, 'create users, ', '') WHERE rid IN (4,9);"
$execSql -i $instance -c "$roles" --drupal

### CiviCRM ###
## 4911/5251 create the civicrm_import_jobs table
impjobs="CREATE TABLE IF NOT EXISTS civicrm_import_jobs (
      id int(10) unsigned NOT NULL AUTO_INCREMENT,
      name varchar(255) NOT NULL,
      table_name varchar(255) NOT NULL,
      source_file varchar(255) NOT NULL,
      file_type varchar(255) NOT NULL,
      field_separator varchar(10) NOT NULL,
      contact_group_id int(10) unsigned NOT NULL,
      created_on timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      created_by int(10) unsigned NOT NULL,
      PRIMARY KEY (id),
      KEY name (name)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;"
$execSql -i $instance -c "$impjobs"


### Cleanup ###

$script_dir/clearCache.sh $instance
