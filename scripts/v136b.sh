#!/bin/sh
#
# v136b.sh
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

# 5303 add print prod staff role
ppsrole="
INSERT INTO role (rid, name) VALUES (18, 'Print Production Staff');
UPDATE permission SET rid = 18 WHERE rid = 17 AND perm = 'access CiviCRM, access CiviReport, access all custom data, edit groups, import contacts, profile listings, profile view, view all contacts, administer reserved groups, export print production files, administer site configuration';"
$execSql -i $instance -c "$ppsrole" --drupal


### CiviCRM ###

## fix log group is_reserved field
fixLog="ALTER TABLE log_civicrm_group ADD is_reserved TINYINT( 4 ) NOT NULL DEFAULT '0' AFTER is_hidden"
$execSql -i $instance -c "$fixLog" --log

### Cleanup ###

$script_dir/clearCache.sh $instance
