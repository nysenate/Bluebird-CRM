#!/bin/sh
#
# v372.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2024-11-26
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

echo "$prog: disable/uninstall various Drupal modules and install CiviCRM extensions."
$drush $instance pm-disable nyss_403 -y
$drush $instance pm-uninstall nyss_403 -y

$drush $instance pm-disable nyss_dedupe -y
$drush $instance pm-uninstall nyss_dedupe -y
$drush $instance cvapi extension.enable key=gov.nysenate.dedupe --quiet

$drush $instance pm-uninstall nyss_backup -y
$drush $instance pm-uninstall nyss_deletetrashed -y
$drush $instance pm-uninstall nyss_import -y

$drush $instance pm-disable nyss_contact -y
$drush $instance pm-uninstall nyss_contact -y

$drush $instance pm-disable nyss_dashboards -y
$drush $instance pm-uninstall nyss_dashboards -y

$drush $instance pm-disable nyss_imapper -y
$drush $instance pm-uninstall nyss_imapper -y

$drush $instance pm-disable nyss_loadsampledata -y
$drush $instance pm-uninstall nyss_loadsampledata -y

$drush $instance pm-disable nyss_mail -y
$drush $instance pm-uninstall nyss_mail -y

$drush $instance pm-disable nyss_reports -y
$drush $instance pm-uninstall nyss_reports -y

$drush $instance pm-disable nyss_signupreport -y
$drush $instance pm-uninstall nyss_signupreport -y

## record completion
echo "$prog: upgrade process is complete."
