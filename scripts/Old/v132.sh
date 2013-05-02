#!/bin/sh
#
# v132.sh
#
# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2011-10-18
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

## run civicrm db upgrade using drush
$drush $instance civicrm-upgrade-db


### Drupal ###


### CiviCRM ###

## remove v3.4.6 nav items
nav="
UPDATE civicrm_navigation
SET is_active = 0
WHERE label = 'New Price Set' OR label = 'Manage Price Sets' OR label = 'Survey Report (Detail)';"
$execSql -i $instance -c "$nav"

## 3439 report permissions by role
rpt="ALTER TABLE civicrm_report_instance ADD grouprole VARCHAR( 1020 ) NULL AFTER permission;"
$execSql -i $instance -c "$rpt"

## 4254 full screen navigation
fsn="UPDATE civicrm_dashboard SET url = REPLACE( url, 'snippet=4', 'snippet=5' ), fullscreen_url = REPLACE( fullscreen_url, 'snippet=4', 'snippet=5' );"
$execSql -i $instance -c "$fsn"

## 3976 create civicrm symlink and set image url
civicrm_filesdir="$data_rootdir/$instance.$base_domain/civicrm"
sitedir="$webdir/sites/$instance.$base_domain"
ln -s "$civicrm_filesdir" "$sitedir/files"

url="http://$instance.$base_domain/sites/$instance.$base_domain/files/civicrm/images/"
imgurl="UPDATE civicrm_option_value SET value = '$url' WHERE name = 'imageUploadURL';"
$execSql -i $instance -c "$imgurl"

## 4352 max attachments
ma="UPDATE civicrm_domain SET config_backend = REPLACE( config_backend,'\"maxAttachments\";s:1:\"3\"','\"maxAttachments\";s:1:\"5\"' ) WHERE id = 1;"
$execSql -i $instance -c "$ma"

## 4645 set all reports to permission access CiviReport
racl="UPDATE civicrm_navigation SET permission = 'access CiviReport' WHERE url LIKE 'civicrm/report/instance/%';"
$execSql -i $instance -c "$racl"

## 4335 source fields in profile overlay
source="SELECT @overlay_id := id FROM civicrm_uf_group WHERE title = 'Summary Overlay';
INSERT INTO civicrm_uf_field (uf_group_id, field_name, is_active, is_view, is_required, weight, help_post, help_pre, visibility, in_selector, is_searchable, location_type_id, phone_type_id, label, field_type, is_reserved) VALUES
(@overlay_id, 'custom_60', 1, 0, 0, 12, '', '', 'User and User Admin Only', 0, 0, NULL, NULL, 'Contact Source', 'Individual', NULL),
(@overlay_id, 'contact_source', 1, 0, 0, 13, '', '', 'User and User Admin Only', 0, 0, NULL, NULL, 'Other Source', 'Contact', NULL);"
$execSql -i $instance -c "$source"

## 4522 remove empty addresses and phone
empty="
DELETE FROM civicrm_address
WHERE street_address IS NULL AND 
  supplemental_address_1 IS NULL AND
  city IS NULL AND
  state_province_id IS NULL;
DELETE FROM civicrm_phone 
WHERE phone IS NULL;"
$execSql -i $instance -c "$empty"

## 4911 create the civicrm_import_jobs table
$execSql -i $instance -c "
    CREATE TABLE civicrm_import_jobs (
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
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
"

### Cleanup ###

$script_dir/clearCache.sh $instance
