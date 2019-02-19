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

echo "$prog: #12439 - extend tags to cases/activities"
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

echo "$prog: #12497 - new activity types"
sql="
  SELECT @optgrp:=id FROM civicrm_option_group WHERE name = 'activity_type';
  SELECT @maxval:=max(cast(value as unsigned)) FROM civicrm_option_value WHERE option_group_id = @optgrp;

  DELETE FROM civicrm_option_value
  WHERE option_group_id = @optgrp
    AND (name = 'Speaking Engagement' OR name = 'Tour' OR name = 'Press Conference');

  INSERT INTO civicrm_option_value
    (option_group_id, label, value, name, grouping, filter, is_default, weight, is_optgroup, is_reserved, is_active, component_id, domain_id, visibility_id)
  VALUES
    (@optgrp, 'Speaking Engagement', @maxval + 1, 'Speaking Engagement', NULL, 0, NULL, @maxval + 1, 0, 1, 1, NULL, NULL, NULL),
    (@optgrp, 'Tour', @maxval + 2, 'Tour', NULL, 0, NULL, @maxval + 2, 0, 1, 1, NULL, NULL, NULL),
    (@optgrp, 'Press Conference', @maxval + 3, 'Press Conference', NULL, 0, NULL, @maxval + 3, 0, 1, 1, NULL, NULL, NULL);
"
$execSql $instance -c "$sql" -q

## record completion
echo "$prog: Finished the v2.2.4 upgrade process"
