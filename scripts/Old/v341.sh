#!/bin/sh
#
# v341.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2022-06-27
# scripts/iterateInstances.sh --all "scripts/v34b.sh {}"
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

echo "#14779 update mosaico template link styles..."
sql="
  UPDATE civicrm_mosaico_template
  SET html = REPLACE(html, 'a:hover{\n      color: #3f3f3f;\n      color: #3f3f3f;', 'a:hover{\n      color: #1f497d;');
  UPDATE civicrm_mosaico_template
  SET content = REPLACE(content, '\"linksColor\":\"#3f3f3f\"', '\"linksColor\":\"#1f497d\"');
"
$execSql $instance -c "$sql" -q

## record completion
echo "$prog: upgrade process is complete."