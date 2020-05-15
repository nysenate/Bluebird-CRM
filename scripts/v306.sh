#!/bin/sh
#
# v306.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2020-05-15
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

echo "$prog: enable ode extension"
$drush $instance cvapi extension.install key=biz.jmaconsulting.ode --quiet

echo "$prog: ode setting"
sql="
  INSERT IGNORE INTO civicrm_setting
  (name, value, domain_id, is_domain, created_date, created_id)
  VALUES
  ('ode_from_allowed', 's:1:\"1\";', 1, 1, NOW(), 1)
"
$execSql $instance -c "$sql" -q

## record completion
echo "$prog: upgrade process is complete."
