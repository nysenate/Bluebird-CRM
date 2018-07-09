#!/bin/sh
#
# v3.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2018-06-26
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

## set new default theme
echo "set default theme to BluebirdSeven..."
$drush $instance pm-enable BluebirdSeven -y
$drush $instance vset theme_default BluebirdSeven -y
$drush $instance vset admin_theme BluebirdSeven -y

## enable menu/admin modules
echo "enable menu/admin modules..."
$drush $instance pm-enable admin_menu -y
$drush $instance pm-enable adminimal_admin_menu -y
$drush $instance pm-enable module_filter -y

## install extensions
echo "$prog: install extensions"
$drush $instance cvapi extension.install key=gov.nysenate.navigation --quiet
$drush $instance cvapi extension.install key=org.civicrm.angularprofiles --quiet
$drush $instance cvapi extension.install key=org.civicrm.api4 --quiet
$drush $instance cvapi extension.install key=org.civicrm.shoreditch --quiet
$drush $instance cvapi extension.install key=org.civicrm.contactsummary --quiet
$drush $instance cvapi extension.install key=org.civicrm.civicase --quiet

## configure blocks for BluebirdSeven theme
echo "$prog: configure blocks for BluebirdSeven theme"
sql="
  UPDATE block
  SET status = 1, region = 'content'
  WHERE module = 'user'
    AND delta = 'login'
    AND theme = 'BluebirdSeven';
  UPDATE block
  SET status = 0
  WHERE module = 'civicrm'
    AND theme = 'BluebirdSeven'
"
$execSql -i $instance -c "$sql" --drupal -q

##
echo "$prog: set file attachments to default open"
sql="
  UPDATE civicrm_custom_group
  SET collapse_display = 0
  WHERE name = 'Attachments';
"
$execSql $instance -c "$sql" -q

## TODO implement contact-summary config

## record completion
echo "$prog: upgrade process is complete."
