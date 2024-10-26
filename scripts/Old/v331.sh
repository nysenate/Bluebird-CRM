#!/bin/sh
#
# v331.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2021-11-01
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

data_rootdir=`$readConfig --ig $instance data.rootdir` || data_rootdir="$DEFAULT_DATA_ROOTDIR"
pubfiles_dir="$data_rootdir/$instance/pubfiles"

if ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

## 14355 create new case type
echo "$prog: create new case type"
sql="
  DELETE FROM civicrm_case_type WHERE name = 'government_service_problem_federal';
  INSERT INTO civicrm_case_type
  (name, title, description, is_active, is_reserved, weight, definition)
  VALUES
  ('government_service_problem_federal', 'Government Service Problem - Federal', 'Problem with a federal government entity.', 1, 0, 3, NULL);
  UPDATE civicrm_case_type SET weight = 4 WHERE name = 'government_service_problem_local';
  UPDATE civicrm_case_type SET weight = 5 WHERE name = 'government_service_problem_state';
  UPDATE civicrm_case_type SET weight = 6 WHERE name = 'letter_of_support';
  UPDATE civicrm_case_type SET weight = 7 WHERE name = 'other';
  UPDATE civicrm_case_type SET weight = 8 WHERE name = 'request_for_assistance';
  UPDATE civicrm_case_type SET weight = 9 WHERE name = 'request_for_information';
"
$execSql $instance -c "$sql" -q

## 14356 add activity type
echo "$prog: create Request Assistance activity type"
sql="
  SELECT @optGroup := id FROM civicrm_option_group WHERE name = 'activity_type';
  DELETE FROM civicrm_option_value WHERE option_group_id = @optGroup AND name = 'Request Assistance';
  SELECT @maxVal := max(value+0) FROM civicrm_option_value WHERE option_group_id = @optGroup;
  INSERT INTO civicrm_option_value (option_group_id, label, value, name, grouping, filter, is_default, weight, description, is_optgroup, is_reserved, is_active, component_id, domain_id, visibility_id)
  VALUES (@optGroup, 'Request Assistance', @maxVal + 1, 'Request Assistance', NULL, '0', NULL, @maxVal + 1, NULL, '0', '0', '1', NULL, NULL, NULL);
"
$execSql $instance -c "$sql" -q

## 5335 add bmp to safe file extensions
sql="
  SELECT @safe:= id FROM civicrm_option_group WHERE name = 'safe_file_extension';
  DELETE FROM civicrm_option_value WHERE option_group_id = @safe AND name = 'webp';
  SELECT @maxval:= MAX(CAST(value AS UNSIGNED)) FROM civicrm_option_value WHERE option_group_id = @safe;
  INSERT INTO civicrm_option_value (
    option_group_id, label, value, name, grouping, filter, is_default, weight, description, is_optgroup, is_reserved,
    is_active, component_id, domain_id, visibility_id )
  VALUES (
    @safe, 'webp', @maxval+1, 'webp' , NULL , '0', '0', @maxval+1, NULL , '0', '0', '1', NULL , NULL , NULL
  );
"
$execSql -i $instance -c "$sql" -q

echo "disable SAGE module; enable SAGE extension..."
$drush $instance pm-disable nyss_sage -y
$execSql -i $instance -c "DELETE FROM system WHERE type='module' AND name='nyss_sage';" --drupal -q
$drush $instance cvapi extension.enable key=gov.nysenate.sage --quiet

echo "remove old BOE module (previously disabled)"
$execSql -i $instance -c "DELETE FROM system WHERE type='module' AND name='nyss_boe';" --drupal -q

## record completion
echo "$prog: upgrade process is complete."
