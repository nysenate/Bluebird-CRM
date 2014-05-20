#!/bin/sh
#
# execSql.sh - Execute SQL statement using Bluebird config file for credentials
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-23
# Revised: 2012-12-21 (End of the World Day - Will Bluebird survive?)
# Revised: 2013-01-07 (The world continued)
# Revised: 2013-05-10 (Implement --force pass-through option.)
# Revised: 2013-07-12 - Major interface change: removed -i option, added -n
# Revised: 2013-10-17 - Added ability to skip tables when dumping a db
# Revised: 2013-11-01 - Added --login-path to support MySQL 5.6 logins
# Revised: 2013-11-13 - mysql/mysqldump need --login-path first
#                     - using MYSQL_TEST_LOGIN_PATH to set mylogin.cnf location
# Revised: 2013-11-15 - Added db.insecure_cli_login to revert to old behavior
# Revised: 2013-11-21 - Make sure login-path is not used if insecure login
# Revised: 2014-03-14 - Added option --schemas-only to inhibit dumping row data
# Revised: 2014-05-20 - Added --insecure-login command line switch.
#

prog=`basename $0`
script_dir=`dirname $0`
readConfig=$script_dir/readConfig.sh
DEFAULT_MYSQL_ARGS="--batch --raw"

if [ ! -r $HOME/.mylogin.cnf ]; then
  export MYSQL_TEST_LOGIN_FILE=/etc/mysql/bluebird_mylogin.cnf
fi

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--help] [-f {sqlFile|-} | -c sqlCommand] [--dump|-d] [--dump-table|-t table] [--skip-table|-e table] [--schemas-only|-s] [-l login-path] [-h host] [-u user] [-p password] [--insecure-login|-i] [--column-names] [--force] [--quiet|-q] [--create] [[--civicrm|-C] | [--drupal|-D] | [--log|-L]] [--db-name|-n dbName] [instance]" >&2
}

if [ $# -lt 1 ]; then
  usage
  exit 1
fi

sqlfile=
sqlcmd=
dump_db=0
dump_tabs=
skip_tabs=
nodata_arg=
instance=
dbloginpath=
dbhost=
dbuser=
dbpass=
dbname=
insecure_login=0
create_db=0
be_quiet=0
colname_arg="--skip-column_names"
db_prefix_keyname=db.civicrm.prefix
default_db_prefix="$DEFAULT_DB_CIVICRM_PREFIX"

while [ $# -gt 0 ]; do
  case "$1" in
    --help) usage; exit 0 ;;
    -f|--sqlfile) shift; sqlfile="$1" ;;
    -c|--cmd) shift; sqlcmd="$1" ;;
    -d|--dump) dump_db=1 ;;
    -e|--skip-table) shift; skip_tabs="$skip_tabs $1" ;;
    -t|--dump-table) shift; dump_tabs="$dump_tabs $1"; dump_db=1 ;;
    -s|--schema*) nodata_arg="--no-data" ;;
    -l|--login-path) shift; dbloginpath="$1" ;;
    -h|--host) shift; dbhost="$1" ;;
    -n|--db*) shift; dbname="$1" ;;
    -u|--user) shift; dbuser="$1" ;;
    -p|--pass*) shift; dbpass="$1" ;;
    -i|--insec*) insecure_login=1 ;;
    -q|--quiet) be_quiet=1 ;;
    --col*) colname_arg="--column-names" ;;
    --create) create_db=1 ;;
    --force) force_arg="--force" ;;
    -C|--civi*) db_prefix_keyname=db.civicrm.prefix; default_db_prefix="$DEFAULT_DB_CIVICRM_PREFIX" ;;
    -D|--drup*) db_prefix_keyname=db.drupal.prefix; default_db_prefix="$DEFAULT_DB_DRUPAL_PREFIX" ;;
    -L|--log) db_prefix_keyname=db.log.prefix; default_db_prefix="$DEFAULT_DB_LOG_PREFIX" ;;
    -*) echo "$prog: $1: Invalid option" >&2; exit 1 ;;
    *) instance="$1" ;;
  esac
  shift
done

if [ "$instance" -a "$dbname" ]; then
  echo "$prog: Please specify either an instance or a dbname, but not both" >&2
  exit 1
fi

ig_opt="--global"

# When specifying the instance, this script can calculate the database
# prefix and the database basename (which are concatenated to form the
# actual database name).  However, there are 3 databases for each instance:
#    civicrm ("c"), drupal ("d"), and log "l")
# This script defaults to the CiviCRM database.
# Use the --drupal (or -D) option to execute SQL on the Drupal DB instead.
# Use the --log (or -L) option to execute SQL on the Log DB instead.

if [ "$instance" ]; then
  if ! $readConfig --instance $instance --quiet; then
    echo "$prog: $instance: Instance not found" >&2
    exit 1
  fi
  ig_opt="--ig $instance"
  db_basename=`$readConfig --instance $instance db.basename` || db_basename="$instance"
  db_prefix=`$readConfig $ig_opt $db_prefix_keyname` || db_prefix=$default_db_prefix
  dbname="$db_prefix$db_basename"
fi
 
# MySQL (as of 5.6.6) no longer supports passing login credentials on the
# command line.  The login-path value, in conjunction with the .mylogin.cnf
# file, is used instead.  If the db.login_path parameter is not specified
# in the config file (and not on the command line), then the default value
# of "bluebird" will be used.
# To disable this behavior and revert to the older, less secure method,
# set db.insecure_cli_login to 1 in the config file.

insecure_cli_login=`$readConfig $ig_opt db.insecure_cli_login`

if [ $? -eq 0 -a "$insecure_cli_login" = "1" -o $insecure_login -eq 1 ]; then
  [ "$dbhost" ] || dbhost=`$readConfig $ig_opt db.host`
  [ "$dbuser" ] || dbuser=`$readConfig $ig_opt db.user`
  [ "$dbpass" ] || dbpass=`$readConfig $ig_opt db.pass`
else
  [ "$dbloginpath" ] || dbloginpath=`$readConfig $ig_opt db.login_path` || dbloginpath=$DEFAULT_DB_LOGIN_PATH
fi

common_args=
[ "$dbloginpath" ] && common_args="$common_args --login-path=$dbloginpath"
[ "$dbhost" ] && common_args="$common_args --host=$dbhost"
[ "$dbuser" ] && common_args="$common_args --user=$dbuser"
[ "$dbpass" ] && common_args="$common_args --password=$dbpass"
mysql_args="$common_args $DEFAULT_MYSQL_ARGS $colname_arg $force_arg"

if [ $dump_db -eq 1 ]; then
  # Do not use 'set -x' here, since mysqldump writes to stdout
  ignore_tabs_arg=
  if [ "$skip_tabs" ]; then
    for tab in $skip_tabs; do
      ignore_tabs_arg="$ignore_tabs_arg --ignore-table $dbname.$tab"
    done
  fi
  mysqldump $common_args $nodata_arg $ignore_tabs_arg --routines --single-transaction --quick $dbname $dump_tabs
elif [ $create_db -eq 1 ]; then
  if [ ! "$dbname" ]; then
    echo "$prog: Cannot create a database without specifying its name or instance." >&2
    exit 1
  fi
  [ $be_quiet -eq 0 ] && set -x
  mysql $mysql_args -e "create database $dbname"
elif [ "$sqlfile" ]; then
  [ $be_quiet -eq 0 ] && set -x
  cat $sqlfile | mysql $mysql_args $dbname
else
  [ $be_quiet -eq 0 ] && set -x
  mysql $mysql_args -e "$sqlcmd" $dbname
fi
