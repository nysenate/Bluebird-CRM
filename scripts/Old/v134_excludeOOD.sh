#!/bin/sh
#
# v134_excludeOOD.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2012-01-26
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
webdir=`$readConfig --global drupal.rootdir` || webdir="$DEFAULT_DRUPAL_ROOTDIR"
base_domain=`$readConfig --ig $instance base.domain` || base_domain="$DEFAULT_BASE_DOMAIN"
formal_name=`$readConfig --ig $instance senator.name.formal` || formal_name="Senator"

###### Begin Upgrade Scripts ######

### CiviCRM ###

# 4879 Exclude out of district option
excludeood="ALTER TABLE civicrm_mailing ADD exclude_ood TINYINT( 4 ) NULL ;"
$execSql -i $instance -c "$excludeood"

# add undeliverable:do not postal mail to batch update profile
batch="INSERT INTO civicrm_uf_field (uf_group_id, field_name, is_active, is_view, is_required, weight, help_post, help_pre, visibility, in_selector, is_searchable, location_type_id, phone_type_id, label, field_type, is_reserved) VALUES
(10, 'do_not_trade', 1, 0, 0, 8, '', '', 'User and User Admin Only', 0, 0, NULL, NULL, 'Undeliverable: Do Not Mail', 'Contact', NULL);"
$execSql -i $instance -c "$batch"

# alter search results profile
search="UPDATE civicrm_domain SET config_backend = REPLACE(config_backend, '\"defaultSearchProfileID\";s:2:\"11\"', '\"defaultSearchProfileID\";s:0:\"\"') WHERE id = 1;"
$execSql -i $instance -c "$search"
