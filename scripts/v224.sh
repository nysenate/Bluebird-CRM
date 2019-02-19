#!/bin/sh
#
# v224.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2019-02-19
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

echo "$prog: Starting v2.2.4 upgrade process"

## 12239
echo "$prog: nyss #12439 - extend tags to cases/activities"
sql="
  SELECT @wb:=id FROM civicrm_tag WHERE name = 'Website Bills';
  SELECT @wc:=id FROM civicrm_tag WHERE name = 'Website Committees';
  SELECT @wi:=id FROM civicrm_tag WHERE name = 'Website Issues';
  SELECT @wp:=id FROM civicrm_tag WHERE name = 'Website Petitions';
  UPDATE civicrm_tag
  SET used_for = 'civicrm_contact,civicrm_activity,civicrm_case'
  WHERE id NOT IN (@wb, @wc, @wi, @wp)
    AND (parent_id NOT IN (@wb, @wc, @wi, @wp) OR parent_id IS NULL);
"
$execSql $instance -c "$sql" -q

## record completion
echo "$prog: Finished the v2.2.4 upgrade process"
