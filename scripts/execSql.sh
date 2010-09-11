#!/bin/sh
#
# execSql.sh
#

prog=`basename $0`
script_dir=`dirname $0`
readConfig=$script_dir/readConfig.sh

if [ $# -lt 1 ]; then
  echo "Usage: $prog dbName [-f sqlFile | -c sqlCommand]" >&2
  exit 1
fi

sqlfile=
sqlcmd=

while [ $# -gt 0 ]; do
  case "$1" in
    -f) shift; sqlfile="$1" ;;
    -c) shift; sqlcmd="$1" ;;
    -*) echo "$prog: $1: Invalid option" >&2; exit 1 ;;
    *) dbname="$1" ;;
  esac
  shift
done

dbhost=`$readConfig --group global:db --key host`
dbuser=`$readConfig --group global:db --key user`
dbpass=`$readConfig --group global:db --key pass`

if [ "$sqlfile" ]; then
  set -x
  cat $sqlfile | mysql -h $dbhost -u $dbuser -p$dbpass $dbname
else
  set -x
  mysql -h $dbhost -u $dbuser -p$dbpass -e "$sqlcmd" --batch $dbname
fi
