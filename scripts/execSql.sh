#!/bin/sh
#
# execSql.sh - Execute SQL statement using Bluebird config file for credentials
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-23
#

prog=`basename $0`
script_dir=`dirname $0`
readConfig=$script_dir/readConfig.sh

. $script_dir/defaults.sh

if [ $# -lt 1 ]; then
  echo "Usage: $prog dbName [-f sqlFile | -c sqlCommand] [-d] [-h host] [-u user] [-p password]" >&2
  exit 1
fi

sqlfile=
sqlcmd=
dump_db=0
dbname=
dbhost=`$readConfig --group globals db.host`
dbuser=`$readConfig --group globals db.user`
dbpass=`$readConfig --group globals db.pass`

while [ $# -gt 0 ]; do
  case "$1" in
    -f|--sqlfile) shift; sqlfile="$1" ;;
    -c|--cmd) shift; sqlcmd="$1" ;;
    -d|--dump) dump_db=1 ;;
    -h|--host) shift; dbhost="$1" ;;
    -u|--user) shift; dbuser="$1" ;;
    -p|--pass*) shift; dbpass="$1" ;;
    -*) echo "$prog: $1: Invalid option" >&2; exit 1 ;;
    *) dbname="$1" ;;
  esac
  shift
done

[ "$dbhost" ] || dbhost=$DEFAULT_DB_HOST
[ "$dbuser" ] || dbhost=$DEFAULT_DB_USER
[ "$dbpass" ] || dbhost=$DEFAULT_DB_PASS

if [ $dump_db -eq 1 ]; then
  mysqldump -h $dbhost -u $dbuser -p$dbpass $dbname
elif [ "$sqlfile" ]; then
  set -x
  cat $sqlfile | mysql -h $dbhost -u $dbuser -p$dbpass $dbname
else
  set -x
  mysql -h $dbhost -u $dbuser -p$dbpass -e "$sqlcmd" --batch $dbname
fi
