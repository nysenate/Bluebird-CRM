#!/bin/sh
#
# v331c.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2021-11-01
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

echo "14283 install afform/searchkit"
$drush $instance cvapi extension.install key=org.civicrm.search_kit --quiet -u 1

echo "$prog: install search_display record"
sql="
  SELECT @savedSearchId := id FROM civicrm_saved_search WHERE name = 'Mosaico_Template_List';
  INSERT INTO civicrm_search_display (name , label , saved_search_id , type , settings)
  VALUES ('Mosaico_Template_List' , 'Mosaico Template List' ,  @savedSearchId , 'table' , '{\"actions\":true,\"limit\":50,\"classes\":[\"table\",\"table-striped\"],\"pager\":{\"show_count\":true,\"expose_limit\":true},\"sort\":[],\"columns\":[{\"type\":\"field\",\"key\":\"title\",\"dataType\":\"String\",\"label\":\"Title\",\"sortable\":true},{\"type\":\"field\",\"key\":\"base\",\"dataType\":\"String\",\"label\":\"Base Template\",\"sortable\":true},{\"type\":\"field\",\"key\":\"category_id:label\",\"dataType\":\"Integer\",\"label\":\"Category\",\"sortable\":true},{\"size\":\"btn-xs\",\"links\":[{\"path\":\"civicrm\\/\",\"icon\":\"fa-eye\",\"text\":\"Preview\",\"style\":\"default\",\"target\":\"crm-popup\"},{\"path\":\"civicrm\\/\",\"icon\":\"fa-pencil\",\"text\":\"Edit\",\"style\":\"default\",\"target\":\"crm-popup\"},{\"path\":\"civicrm\\/\",\"icon\":\"fa-wrench\",\"text\":\"Settings\",\"style\":\"default\",\"target\":\"crm-popup\"},{\"path\":\"civicrm\\/\",\"icon\":\"fa-clone\",\"text\":\"Copy\",\"style\":\"default\",\"target\":\"crm-popup\"},{\"path\":\"civicrm\\/\",\"icon\":\"fa-trash\",\"text\":\"Delete\",\"style\":\"default\",\"target\":\"crm-popup\"}],\"type\":\"buttons\",\"alignment\":\"text-right\"}]}' );
"
$execSql $instance -c "$sql" -q

$drush $instance cvapi extension.upgrade --quiet -u 1
$drush $instance cvapi extension.install key=org.civicrm.afform --quiet -u 1

## record completion
echo "$prog: upgrade process is complete."
