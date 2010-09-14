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
dump_db=0

while [ $# -gt 0 ]; do
  case "$1" in
    -f|--sqlfile) shift; sqlfile="$1" ;;
    -c|--cmd) shift; sqlcmd="$1" ;;
    -d|--dump) dump_db=1 ;;
    -*) echo "$prog: $1: Invalid option" >&2; exit 1 ;;
    *) dbname="$1" ;;
  esac
  shift
done

dbhost=`$readConfig --group globals --key db.host`
dbuser=`$readConfig --group globals --key db.user`
dbpass=`$readConfig --group globals --key db.pass`

if [ $dump_db -eq 1 ]; then
  mysqldump -h $dbhost -u $dbuser -p$dbpass $dbname
elif [ "$sqlfile" ]; then
  set -x
  cat $sqlfile | mysql -h $dbhost -u $dbuser -p$dbpass $dbname
else
  set -x
  mysql -h $dbhost -u $dbuser -p$dbpass -e "$sqlcmd" --batch $dbname
fi
