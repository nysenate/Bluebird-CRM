#!/bin/sh
#
# execSql.sh - Execute SQL statement using Bluebird config file for credentials
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-23
# Revised: 2010-09-27
#

prog=`basename $0`
script_dir=`dirname $0`
readConfig=$script_dir/readConfig.sh

. $script_dir/defaults.sh

if [ $# -lt 1 ]; then
  echo "Usage: $prog dbName [-f sqlFile | -c sqlCommand] [-d] [-i instance] [-h host] [-u user] [-p password] [--drupal]" >&2
  exit 1
fi

sqlfile=
sqlcmd=
dump_db=0
instance=
dbhost=
dbuser=
dbpass=
dbname=
db_prefix_keyname=db.civicrm.prefix
default_db_prefix="$DEFAULT_DB_CIVICRM_PREFIX"

while [ $# -gt 0 ]; do
  case "$1" in
    -f|--sqlfile) shift; sqlfile="$1" ;;
    -c|--cmd) shift; sqlcmd="$1" ;;
    -d|--dump) dump_db=1 ;;
    -i|--instance) shift; instance="$1" ;;
    -h|--host) shift; dbhost="$1" ;;
    -u|--user) shift; dbuser="$1" ;;
    -p|--pass*) shift; dbpass="$1" ;;
    --drupal) db_prefix_keyname=db.drupal.prefix; default_db_prefix="$DEFAULT_DB_DRUPAL_PREFIX" ;;
    -*) echo "$prog: $1: Invalid option" >&2; exit 1 ;;
    *) dbname="$1" ;;
  esac
  shift
done

ig_opt="--global"

if [ "$instance" ]; then
  ig_opt="--ig $instance"
  if [ ! "$dbname" ]; then
    db_basename=`$readConfig --instance $instance db.basename`
    if [ $? -ne 0 ]; then
      echo "$prog: $instance: Instance not found" >&2
      exit 1
    fi
    db_prefix=`$readConfig $ig_opt $db_prefix_keyname` || db_prefix=$default_db_prefix
    dbname="$db_prefix$db_basename"
  fi
fi
 
[ "$dbhost" ] || dbhost=`$readConfig $ig_opt db.host` || dbhost=$DEFAULT_DB_HOST
[ "$dbuser" ] || dbuser=`$readConfig $ig_opt db.user` || dbhost=$DEFAULT_DB_USER
[ "$dbpass" ] || dbpass=`$readConfig $ig_opt db.pass` || dbhost=$DEFAULT_DB_PASS

if [ $dump_db -eq 1 ]; then
  mysqldump -h $dbhost -u $dbuser -p$dbpass $dbname
elif [ "$sqlfile" ]; then
  set -x
  cat $sqlfile | mysql -h $dbhost -u $dbuser -p$dbpass $dbname
else
  set -x
  mysql -h $dbhost -u $dbuser -p$dbpass -e "$sqlcmd" --batch $dbname
fi
