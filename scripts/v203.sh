#!/bin/sh
#
# v203.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2017-11-22
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

## install new extensions
echo "$prog: install new extensions..."
$drush $instance cvapi extension.install key=gov.nysenate.case --quiet
$drush $instance cvapi extension.install key=nz.co.fuzion.innodbtriggers --quiet

## remove CiviCRM sample email templates
echo "$prog: remove CiviCRM sample email templates..."
sql="
  DELETE FROM civicrm_msg_template
  WHERE msg_title LIKE '%Sample CiviMail%'
    OR msg_title LIKE '%Sample Responsive%';
"
$execSql $instance -c "$sql" -q

## install new extensions
echo "$prog: trigger updates for logging tables..."
$drush $instance cvapi system.updatelogtables --quiet

## record completion
echo "$prog: upgrade process is complete."
