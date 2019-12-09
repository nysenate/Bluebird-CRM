#!/bin/sh
#
# v3.0.1.sh
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

echo "$prog: cancel all currently running mailings"
sql="
  UPDATE civicrm_mailing_job mjc
  JOIN civicrm_mailing_job mjp
    ON mjc.parent_id = mjp.id
  SET mjc.status = 'Canceled'
  WHERE mjp.status = 'Running';

  UPDATE civicrm_mailing_job
  SET status = 'Canceled'
  WHERE status = 'Running'
    AND parent_id IS NULL
    AND is_test = 0;
"
$execSql $instance -c "$sql" -q

echo "$prog: resetting roles and permissions..."
$script_dir/resetRolePerms.sh $instance

## record completion
echo "$prog: upgrade process is complete."
