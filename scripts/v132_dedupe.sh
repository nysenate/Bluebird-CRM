#!/bin/sh
#
# v131_dedupe.sh
#
# Project: BluebirdCRM
# Author: Graylin Kim
# Organization: New York State Senate
# Date: 2011-09-16
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

###### Begin Upgrade Scripts ######


## Create a new import jobs table that stores information for future access
## Currently supports a simple second pass dedupe process. Future expansion
## to import review and re-run is probable.
$execSql -i $instance -c "
    CREATE TABLE civicrm_import_jobs (
      id int(10) unsigned NOT NULL AUTO_INCREMENT,
      name varchar(255) NOT NULL,
      table_name varchar(255) NOT NULL,
      source_file varchar(255) NOT NULL,
      file_type varchar(255) NOT NULL,
      field_separator varchar(10) NOT NULL,
      contact_group_id int(10) unsigned NOT NULL,
      created_on timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      created_by int(10) unsigned NOT NULL,
      PRIMARY KEY (id),
      KEY name (name)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
"
