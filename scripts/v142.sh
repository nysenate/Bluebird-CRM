#!/bin/sh
#
# v142.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2013-07-24
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
echo "Enabling nyss_deletetrashed module"
$drush $instance en nyss_deletetrashed -y -q
echo "Enabling nyss_exportpermissions module"
$drush $instance en nyss_exportpermissions -y -q
echo "Enabling nyss_loadsampledata module"
$drush $instance en nyss_loadsampledata -y -q

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

### Cleanup ###
echo "Cleaning up by performing clearCache"
$script_dir/clearCache.sh $instance
