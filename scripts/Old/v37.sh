#!/bin/sh
#
# v37.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2023-05-14
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

## upgrade drupal db
echo "running drupal db upgrade..."
$drush $instance updb -y -q

## cleanup dates
echo "cleanup date fields..."
$drush $instance cvapi nyss.cleandates dryrun=0 --quiet

echo "uninstall tutorial extension..."
$drush $instance cvapi extension.disable key=org.civicrm.tutorial --quiet
$drush $instance cvapi extension.uninstall key=org.civicrm.tutorial --quiet

## modify activity table FK
echo "modify activity table FK"
sql="
  ALTER TABLE civicrm_activity DROP FOREIGN KEY FK_civicrm_activity_parent_id;
  ALTER TABLE civicrm_activity ADD CONSTRAINT FK_civicrm_activity_parent_id FOREIGN KEY (parent_id) REFERENCES civicrm_activity (id) ON DELETE SET NULL;
"
$execSql -i $instance -c "$sql" -q

## upgrade civicrm db
echo "running civicrm db upgrade..."
$drush $instance civicrm-upgrade-db -y -q

echo "upgrade extensions..."
$drush $instance cvapi extension.upgrade --quiet

## activity table cleanup
echo "activity table cleanup"
sql="DELETE FROM civicrm_activity WHERE is_current_revision = 0"
$execSql -i $instance -c "$sql" -q

php $script_dir/../civicrm/scripts/logUpdateSchema.php -S $instance

## record completion
echo "$prog: upgrade process is complete."
