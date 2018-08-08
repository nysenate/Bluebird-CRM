#!/bin/sh
#
# deleteEntityFileDupes.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2018-08-07
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

echo "$prog: About to delete duplicate entity_file records"

sql="
  DELETE ef1
  FROM civicrm_entity_file ef1
  INNER JOIN civicrm_entity_file ef2
  WHERE ef1.id > ef2.id
    AND ef1.entity_table = ef2.entity_table
    AND ef1.entity_id = ef2.entity_id
    AND ef1.file_id = ef2.file_id;
"
$execSql $instance -c "$sql" -q
rc=$?

echo "$prog: Finished deleting duplicate entity_file records"

exit $rc
