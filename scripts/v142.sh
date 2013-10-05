#!/bin/sh
#
# v142.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2013-10-04
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

app_rootdir=`$readConfig --ig $instance app.rootdir` || app_rootdir="$DEFAULT_APP_ROOTDIR"

## Enable new modules
echo "Enabling nyss_deletetrashed module..."
$drush $instance en nyss_deletetrashed -y -q
echo "Enabling nyss_exportpermissions module..."
$drush $instance en nyss_exportpermissions -y -q

case $instance in
  training*|sd99)
    echo "Enabling nyss_loadsampledata module..."
    $drush $instance en nyss_loadsampledata -y -q
    ;;
  *) echo "Skipping the enabling of nyss_loadsampledata module" ;;
esac

## 7022 create and populate long form school district table
echo "Creating and populating school district code lookup table..."
$execSql $instance -f $app_rootdir/scripts/sql/schoolDistrictCodes.sql -q

## 7062 theme updates
echo "Cleaning up some deprecated theme name references and improving page-not-found notification layout..."
sql="
  DELETE FROM block
  WHERE theme = 'rayCivicrm';

  UPDATE field_data_body
  SET body_value = '
    <div style=\"background-color: #FFFFFF; padding: 20px 10px 10px; border-radius: 4px; margin-top: 10px;\">
      <div style=\"float:left;\"><img src=\"/sites/default/themes/Bluebird/nyss_skin/images/seal-bluebird.png\" style=\"float:left;margin-left:10px;\" /></div>
      <div style=\"float:left; margin-left:30px;width:700px;\">
        <p>The page you are trying to reach does not exist. Please check and make sure you have the correct URL. <br />
        <a href=\"/\" title=\"Bluebird Home\"><strong>Click here</strong></a> to return to the Bluebird Dashboard. </p>
        <p>If you feel this page was received in error, please copy the URL from your browser\'s address bar and email with additional details to your technical support staff.</p>
      </div>
      <div style=\"clear:both;\"></div>
    </div>'
  WHERE entity_type = 'node'
    AND bundle = 'page'
    AND entity_id = 2;

  UPDATE field_revision_body
  SET body_value = '
    <div style=\"background-color: #FFFFFF; padding: 20px 10px 10px; border-radius: 4px; margin-top: 10px;\">
      <div style=\"float:left;\"><img src=\"/sites/default/themes/Bluebird/nyss_skin/images/seal-bluebird.png\" style=\"float:left;margin-left:10px;\" /></div>
      <div style=\"float:left; margin-left:30px;width:700px;\">
        <p>The page you are trying to reach does not exist. Please check and make sure you have the correct URL. <br />
        <a href=\"/\" title=\"Bluebird Home\"><strong>Click here</strong></a> to return to the Bluebird Dashboard. </p>
        <p>If you feel this page was received in error, please copy the URL from your browser\'s address bar and email with additional details to your technical support staff.</p>
      </div>
      <div style=\"clear:both;\"></div>
    </div>'
  WHERE entity_type = 'node'
    AND bundle = 'page'
    AND entity_id = 2;

  UPDATE block
  SET weight = '-11'
  WHERE theme = 'Bluebird'
    AND module = 'system'
    AND region = 'content';

  UPDATE block
  SET weight = '-10'
  WHERE theme = 'Bluebird'
    AND module = 'user'
    AND region = 'content';
"
$execSql $instance -c "$sql"  --drupal -q

## 7134
echo "Examining option_value table for Assembly prefixes..."
sql="select count(*) from civicrm_option_value where name like 'Assembly%' and option_group_id=(select id from civicrm_option_group where name='individual_prefix')"
cnt=`$execSql $instance -c "$sql" -q`

if [ $cnt -eq 0 ]; then
  echo "Adding Assembly values to individual prefix"
  sql="
SELECT @optgrp:=id FROM civicrm_option_group WHERE name = 'individual_prefix';
SELECT @maxval:=max(cast(value as unsigned)) FROM civicrm_option_value WHERE option_group_id = @optgrp;
SELECT @wght:=weight FROM civicrm_option_value WHERE option_group_id = @optgrp AND name = 'Admiral';
UPDATE civicrm_option_value SET weight = weight + 3 WHERE option_group_id = @optgrp AND weight > @wght;
INSERT INTO civicrm_option_value (option_group_id, label, value, name, weight, is_active)
VALUES (@optgrp, 'Assemblyman', @maxval+1, 'Assemblyman', @wght+1, 1),
  (@optgrp, 'Assemblymember', @maxval+2, 'Assemblymember', @wght+2, 1),
  (@optgrp, 'Assemblywoman', @maxval+3, 'Assemblywoman', @wght+3, 1);
"
  $execSql $instance -c "$sql" -q
elif [ $cnt -eq 3 ]; then
  echo "All 3 Assembly prefixes are already available"
else
  echo "ERROR: Found $cnt Assembly prefixes, but we are expecting 0 or 3; this must be manually fixed"
fi

### Cleanup ###
echo "Cleaning up by performing clearCache"
$script_dir/clearCache.sh $instance
