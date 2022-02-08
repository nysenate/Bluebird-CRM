#!/bin/sh
#
# v34.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2022-01-24
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

## upgrade civicrm db
echo "running civicrm db upgrade..."
$drush $instance civicrm-upgrade-db -y -q

php $script_dir/../civicrm/scripts/logUpdateSchema.php -S $instance

echo "enable/disable/install/uninstall various extensions..."
$drush $instance cvapi extension.disable key=com.ginkgostreet.mosaicotoolbarconfig --quiet
$drush $instance cvapi extension.install key=mosaicoextras --quiet
$drush $instance cvapi extension.install key=org.civicrm.search_kit --quiet
$drush $instance cvapi extension.install key=org.civicrm.afform --quiet
$drush $instance cvapi extension.uninstall key=nz.co.fuzion.innodbtriggers --quiet
$drush $instance cvapi extension.uninstall key=org.civicrm.doctorwhen --quiet

echo "remove previously disabled extension..."
$execSql -i $instance -c "DELETE FROM civicrm_extension WHERE full_name = 'org.civicrm.api4';" -q

echo "$prog: disable/uninstall old modules"
$drush $instance pm-disable nyss_backup -y
$drush $instance pm-uninstall nyss_backup -y

echo "remove previously disabled modules..."
$execSql -i $instance -c "DELETE FROM system WHERE type='module' AND name='nyss_sage';" --drupal -q

echo "upgrade extensions..."
$drush $instance cvapi extension.upgrade --quiet

echo "move note entity_table selections to option group..."
sql="
  SELECT @optgrp:=id FROM civicrm_option_group WHERE name = 'note_used_for';
  DELETE FROM civicrm_option_value WHERE option_group_id = @optgrp AND (value = 'nyss_directmsg' OR value = 'nyss_contextmsg');
  SELECT @maxval:=max(cast(weight as unsigned)) FROM civicrm_option_value WHERE option_group_id = @optgrp;
  INSERT INTO civicrm_option_value
    (option_group_id, label, value, name, grouping, filter, is_default, weight, is_optgroup, is_reserved, is_active, component_id, domain_id, visibility_id)
  VALUES
    (@optgrp, 'NYSS Direct Message', 'nyss_directmsg', 'NYSS Direct Message', NULL, 0, 0, @maxval + 1, 0, 1, 1, NULL, NULL, NULL),
    (@optgrp, 'NYSS Contextual Message', 'nyss_contextmsg', 'NYSS Contextual Message', NULL, 0, 0, @maxval + 2, 0, 1, 1, NULL, NULL, NULL);
"
$execSql $instance -c "$sql" -q

## record completion
echo "$prog: upgrade process is complete."
