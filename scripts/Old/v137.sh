#!/bin/sh
#
# v137.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2012-06-12
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


### CiviCRM ###

## 5371 add petition activity type
petitionAct="
SELECT @optGroup := id FROM civicrm_option_group WHERE name = 'activity_type';
SELECT @maxVal := max(value) FROM civicrm_option_value WHERE option_group_id = @optGroup;
INSERT INTO civicrm_option_value (id, option_group_id, label, value, name, grouping, filter, is_default, weight, description, is_optgroup, is_reserved, is_active, component_id, domain_id, visibility_id)
VALUES (NULL, @optGroup, 'Petition', @maxVal + 1, 'Petition', NULL, '0', NULL, @maxVal + 1, NULL, '0', '0', '1', NULL, NULL, NULL);
"
$execSql -i $instance -c "$petitionAct"


### Cleanup ###

# $script_dir/clearCache.sh $instance
