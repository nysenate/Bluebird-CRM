#!/bin/sh
#
# v34-post.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2022-04-12
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

$execSql -i $instance -c "
  UPDATE civicrm_search_display
  SET settings = '{\"actions\":false,\"limit\":30,\"classes\":[\"table\",\"table-striped\"],\"pager\":{\"show_count\":true,\"expose_limit\":true},\"sort\":[[\"title\",\"ASC\"]],\"columns\":[{\"type\":\"field\",\"key\":\"title\",\"dataType\":\"String\",\"label\":\"Title\",\"sortable\":true,\"editable\":true},{\"type\":\"field\",\"key\":\"base\",\"dataType\":\"String\",\"label\":\"Base Template\",\"sortable\":true},{\"type\":\"field\",\"key\":\"category_id:label\",\"dataType\":\"Integer\",\"label\":\"Category\",\"sortable\":true,\"editable\":true},{\"path\":\"~\\/crmMosaico\\/SearchTemplateListButtonsColumn.html\",\"type\":\"include\",\"alignment\":\"text-right\"}],\"cssRules\":[]}'
  WHERE name = 'Mosaico_Template_List';
" -q

echo "clear cache..."
$script_dir/clearCache.sh $instance

## record completion
echo "$prog: upgrade process is complete."
