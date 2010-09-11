#!/bin/sh
#
# execSql.sh
#

prog=`basename $0`
script_dir=`dirname $0`
readConfig=$script_dir/readConfig.sh

if [ $# -ne 2 ]; then
  echo "Usage: $prog dbName sqlQuery" >&2
  exit 1
fi

dbname="$1"
sqlquery="$2"

dbhost=`$readConfig --group global:db --key host`
dbuser=`$readConfig --group global:db --key user`
dbpass=`$readConfig --group global:db --key pass`

set -x
mysql -h $dbhost -u $dbuser -p$dbpass -e "$sqlquery" $dbname

