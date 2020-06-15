#!/bin/sh
#
# v307.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2020-06-01
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
drush=$script_dir/drush.sh
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

## upgrade drupal db
echo "running drupal db upgrade..."
$drush $instance updb -y -q

## upgrade civicrm db
echo "running civicrm db upgrade..."
$drush $instance civicrm-upgrade-db -y -q

## field mapping cleanup
echo "cleaning up saved export field mappings..."

# fix prefix/suffix fields
sql="
  UPDATE civicrm_mapping_field
  JOIN civicrm_mapping
    ON civicrm_mapping_field.mapping_id = civicrm_mapping.id
    AND civicrm_mapping.mapping_type_id = 7
  SET civicrm_mapping_field.name = 'prefix_id'
  WHERE civicrm_mapping_field.name = 'individual_prefix'
"
$execSql $instance -c "$sql" -q

sql="
  UPDATE civicrm_mapping_field
  JOIN civicrm_mapping
    ON civicrm_mapping_field.mapping_id = civicrm_mapping.id
    AND civicrm_mapping.mapping_type_id = 7
  SET civicrm_mapping_field.name = 'suffix_id'
  WHERE civicrm_mapping_field.name = 'individual_suffix'
"
$execSql $instance -c "$sql" -q

# update contact_type column
sql="
  UPDATE civicrm_mapping_field
  JOIN civicrm_mapping
    ON civicrm_mapping_field.mapping_id = civicrm_mapping.id
    AND civicrm_mapping.mapping_type_id = 7
  SET civicrm_mapping_field.contact_type = 'Contact'
  WHERE civicrm_mapping_field.contact_type IN ('Individual', 'Household', 'Organization')
"
$execSql $instance -c "$sql" -q

# remove duplicates
sql="
  DELETE t1
  FROM civicrm_mapping_field t1
  JOIN civicrm_mapping_field t2
  WHERE t1.id < t2.id
    AND t1.mapping_id = t2.mapping_id
    AND t1.name = t2.name
    AND (t1.location_type_id = t2.location_type_id OR (t1.location_type_id IS NULL AND t2.location_type_id IS NULL))
    AND (t1.phone_type_id = t2.phone_type_id OR (t1.phone_type_id IS NULL AND t2.phone_type_id IS NULL))
    AND (t1.relationship_type_id = t2.relationship_type_id OR (t1.relationship_type_id IS NULL AND t2.relationship_type_id IS NULL))
    AND (t1.website_type_id = t2.website_type_id OR (t1.website_type_id IS NULL AND t2.website_type_id IS NULL))
"
$execSql $instance -c "$sql" -q

## record completion
echo "$prog: upgrade process is complete."
