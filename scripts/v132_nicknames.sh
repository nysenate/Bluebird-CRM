#!/bin/sh
#
# shadowTable.sh
#
# Project: BluebirdCRM
# Author: Graylin Kim
# Organization: New York State Senate
# Date: 2011-10-12
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
update_shadows=$script_dir/shadowTable.sh


if [ $# -ne 1 ]; then
    echo "Usage: $prog instanceName" >&2
    exit 1
fi

instance="$1"

if ! $readConfig --instance $instance --quiet; then
    echo "$prog: $instance: Instance not found in config file" >&2
    exit 1
fi


#Create the lookup tables
$execSql -i $instance -f $script_dir/output/nicknames/nicknames.sql

#Update the shadow.sql functions and regenerate the tables
$update_shadows $instance
