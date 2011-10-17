#!/bin/sh
#
# v131_permsExport.sh
#
# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2011-10-04
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

## 4403 set do not mail for all contacts in Email Only group
emgroup="
SELECT @emailOnlyGroupID := id FROM civicrm_group WHERE name = 'Email_Only';
UPDATE civicrm_contact c
  JOIN civicrm_group_contact gc ON ( gc.contact_id = c.id AND gc.group_id = @emailOnlyGroupID )
SET do_not_mail = 1;"
$execSql -i $instance -c "$emgroup"

## 3812 set news dashlet on all contacts
newsdash="
SELECT @newsID := id FROM civicrm_dashboard WHERE label = 'Bluebird News';
INSERT INTO civicrm_dashboard_contact (dashboard_id, contact_id, column_no, is_active)
  SELECT @newsID, uf.contact_id, 1, 1
  FROM civicrm_uf_match uf
    LEFT JOIN civicrm_dashboard_contact dc ON (uf.contact_id = dc.contact_id AND dc.dashboard_id = @newsID)
  WHERE dc.id IS NULL;
UPDATE civicrm_dashboard_contact dc
  SET column_no = 1, is_active = 1
  WHERE dc.dashboard_id = @newsID;"
$execSql -i $instance -c "$newsdash"


### Cleanup ###

$script_dir/clearCache.sh $instance
