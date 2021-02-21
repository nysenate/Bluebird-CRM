#!/bin/sh
#
# v311b.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2021-02-09
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

## set mail extension to load last
echo "$prog: set mail extension to load last"
sql="
  SELECT @maxid := max(id) FROM civicrm_extension;

  DELETE FROM civicrm_extension
  WHERE full_name = 'gov.nysenate.mail' AND id != @maxid;

  INSERT INTO civicrm_extension
  (type, full_name, name, label, file, is_active)
    SELECT 'module', 'gov.nysenate.mail', 'NYSS: Mailing Customizations', 'NYSS: Mailing Customizations', 'mail', 1
    FROM DUAL
    WHERE NOT EXISTS (
      SELECT full_name FROM civicrm_extension
      WHERE full_name = 'gov.nysenate.mail'
    );
"
$execSql $instance -c "$sql" -q

## 13807 setup scheduled jobs
echo "$prog: setup scheduled jobs"
sql="
   UPDATE civicrm_job
   SET is_active = 0
   WHERE api_action = 'version_check';

   UPDATE civicrm_job
   SET is_active = 1, run_frequency = 'Hourly', parameters = 'limit=5'
   WHERE api_action = 'group_rebuild';

   UPDATE civicrm_job
   SET is_active = 1, run_frequency = 'Daily'
   WHERE api_action = 'disable_expired_relationships';

   UPDATE civicrm_job
   SET is_active = 1, run_frequency = 'Daily', parameters = 'minDays=3\nmaxDays=15'
   WHERE api_action = 'update_email_resetdate';
"
$execSql $instance -c "$sql" -q

## record completion
echo "$prog: upgrade process is complete."
