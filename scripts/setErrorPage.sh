#!/bin/sh
#
# setErrorPage.sh
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2019-01-24
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
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

echo "Setting the error page content"

sql="
  UPDATE field_data_body
  SET body_value = '
    <div style=\"background-color: #FFFFFF; padding: 20px 10px 10px; border-radius: 4px; margin-top: 10px;\">
      <div style=\"float:left;\"><img src=\"/sites/default/themes/Bluebird/images/seal-bluebird.png\" style=\"float:left;margin-left:10px;\" /></div>
      <div style=\"float:left; margin-left:30px;width:700px;\">
        <p>The page you are trying to reach does not exist. Please check and make sure you have the correct URL.<br />
        <a href=\"/\" title=\"Bluebird Home\"><strong>Click here</strong></a> to return to the Bluebird dashboard.</p>
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
      <div style=\"float:left;\"><img src=\"/sites/default/themes/Bluebird/images/seal-bluebird.png\" style=\"float:left;margin-left:10px;\" /></div>
      <div style=\"float:left; margin-left:30px;width:700px;\">
        <p>The page you are trying to reach does not exist. Please check and make sure you have the correct URL.<br />
        <a href=\"/\" title=\"Bluebird Home\"><strong>Click here</strong></a> to return to the Bluebird dashboard.</p>
        <p>If you feel this page was received in error, please copy the URL from your browser\'s address bar and email with additional details to your technical support staff.</p>
      </div>
      <div style=\"clear:both;\"></div>
    </div>'
  WHERE entity_type = 'node'
    AND bundle = 'page'
    AND entity_id = 2;
"

$execSql $instance -c "$sql" --drupal -q

exit $?
