#!/bin/sh
#
# v142b.sh - org import cleanup
#
# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2013-10-25
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

## 7287
echo "setting organization_name value if empty..."
sql="
  UPDATE civicrm_contact
  SET organization_name = display_name
  WHERE organization_name IS NULL
    AND contact_type = 'Organization'
    AND display_name IS NOT NULL;
"
$execSql $instance -c "$sql"

echo "finished."
