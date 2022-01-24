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

echo "enable/disable/install/uninstall various extensions..."
$drush $instance cvapi extension.disable key=com.ginkgostreet.mosaicotoolbarconfig --quiet
$drush $instance cvapi extension.install key=mosaicoextras --quiet
$drush $instance cvapi extension.install key=org.civicrm.search_kit --quiet
$drush $instance cvapi extension.install key=org.civicrm.afform --quiet
$drush $instance cvapi extension.uninstall key=nz.co.fuzion.innodbtriggers --quiet
$drush $instance cvapi extension.uninstall key=org.civicrm.doctorwhen --quiet

echo "$prog: disable/uninstall old modules"
$drush $instance pm-disable nyss_backup -y
$drush $instance pm-uninstall nyss_backup -y

echo "remove previously disabled modules..."
$execSql -i $instance -c "DELETE FROM system WHERE type='module' AND name='nyss_sage';" --drupal -q

## upgrade drupal db
echo "running drupal db upgrade..."
$drush $instance updb -y -q

## upgrade civicrm db
echo "running civicrm db upgrade..."
$drush $instance civicrm-upgrade-db -y -q

echo "upgrade extensions..."
$drush $instance cvapi extension.upgrade --quiet

## record completion
echo "$prog: upgrade process is complete."
