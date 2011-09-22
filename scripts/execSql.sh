#!/bin/sh
#
# execSql.sh - Execute SQL statement using Bluebird config file for credentials
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-23
# Revised: 2011-03-08
#

prog=`basename $0`
script_dir=`dirname $0`
readConfig=$script_dir/readConfig.sh

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [-f sqlFile | -c sqlCommand] [-d] [-t table] [-i instance] [-h host] [-u user] [-p password] [--quiet|-q] [--create] [--drupal] [dbName]" >&2
}

if [ $# -lt 1 ]; then
  usage
  exit 1
fi

sqlfile=
sqlcmd=
dump_db=0
tabname=
instance=
dbhost=
dbuser=
dbpass=
dbname=
create_db=0
be_quiet=0
db_prefix_keyname=db.civicrm.prefix
default_db_prefix="$DEFAULT_DB_CIVICRM_PREFIX"

while [ $# -gt 0 ]; do
  case "$1" in
    -f|--sqlfile) shift; sqlfile="$1" ;;
    -c|--cmd) shift; sqlcmd="$1" ;;
    -d|--dump) dump_db=1 ;;
    -t|--dump-table) shift; tabname="$1"; dump_db=1 ;;
    -i|--instance) shift; instance="$1" ;;
    -h|--host) shift; dbhost="$1" ;;
    -u|--user) shift; dbuser="$1" ;;
    -p|--pass*) shift; dbpass="$1" ;;
    -q|--quiet) be_quiet=1 ;;
    --create) create_db=1 ;;
    --drupal) db_prefix_keyname=db.drupal.prefix; default_db_prefix="$DEFAULT_DB_DRUPAL_PREFIX" ;;
    -*) echo "$prog: $1: Invalid option" >&2; exit 1 ;;
    *) dbname="$1" ;;
  esac
  shift
done

ig_opt="--global"

# By using the --instance option, this script can calculate the database
# prefix and the database basename (which are concatenated to form the
# actual database name).  However, there are two databases for each instance.
# This script defaults to the CiviCRM database.
# Use the --drupal option to execute SQL on the Drupal DB instead.

if [ "$instance" ]; then
  if ! $readConfig --instance $instance --quiet; then
    echo "$prog: $instance: Instance not found" >&2
    exit 1
  fi
  ig_opt="--ig $instance"
  if [ ! "$dbname" ]; then
    db_basename=`$readConfig --instance $instance db.basename` || db_basename="$instance"
    db_prefix=`$readConfig $ig_opt $db_prefix_keyname` || db_prefix=$default_db_prefix
    dbname="$db_prefix$db_basename"
  fi
fi
 
[ "$dbhost" ] || dbhost=`$readConfig $ig_opt db.host` || dbhost=$DEFAULT_DB_HOST
[ "$dbuser" ] || dbuser=`$readConfig $ig_opt db.user` || dbhost=$DEFAULT_DB_USER
[ "$dbpass" ] || dbpass=`$readConfig $ig_opt db.pass` || dbhost=$DEFAULT_DB_PASS

if [ $dump_db -eq 1 ]; then
  # Do not use 'set -x' here, since mysqldump writes to stdout
  mysqldump -R -h $dbhost -u $dbuser -p$dbpass $dbname $tabname
elif [ $create_db -eq 1 ]; then
  if [ ! "$dbname" ]; then
    echo "$prog: Cannot create a database without specifying its name or instance." >&2
    exit 1
  fi
  [ $be_quiet -eq 0 ] && set -x
  mysql -h $dbhost -u $dbuser -p$dbpass -e "create database $dbname" --batch
elif [ "$sqlfile" ]; then
  [ $be_quiet -eq 0 ] && set -x
  cat $sqlfile | mysql -h $dbhost -u $dbuser -p$dbpass $dbname
else
  [ $be_quiet -eq 0 ] && set -x
  mysql -h $dbhost -u $dbuser -p$dbpass -e "$sqlcmd" --batch --skip-column-names $dbname
fi
