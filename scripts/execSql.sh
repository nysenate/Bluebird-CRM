#!/bin/bash
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
# Revised: 2014-05-20 - Added --insecure-login command line switch
# Revised: 2014-07-22 - Allow hyphens in instance names by backquoting db name
# Revised: 2014-08-05 - Added --replace-macros option, which replaces the
#                       macros @CIVIDB@, @DRUPDB@, and @LOGDB@ with the
#                       corresponding database names
# Revised: 2014-08-07 - Allow no database to be specified using --no-db
# Revised: 2015-11-11 - Added --get-db-name command line switch
# Revised: 2015-11-12 - Don't dump routines when dumping specific tables
# Revised: 2023-01-03 - Enhance processing of options with parameters
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
  echo "Usage: $prog [--help] [-f {sqlFile|-} | -c sqlCommand] [--dump|-d] [--dump-table|-t table] [--skip-table|-e table] [--schemas-only|-s] [-l login-path] [-h host] [-u user] [-p password] [--insecure-login|-i] [--replace-macros|-r] [--column-names] [--force] [--quiet|-q] [--create] [[--civicrm|-C] | [--drupal|-D] | [--log|-L]] [--no-db] [--get-db-name|-g] [--db-name|-n dbName] [instance]" >&2
}

get_next_arg() {
  opt="$1"
  if [ $# -lt 2 -o "${2:0:1}" = "-" ]; then
    echo "$prog: Option $opt requires a non-option parameter" >&2
    return 1
  else
    echo "$2"
    return 0
  fi
}

filter_replace_macros() {
  sed -e "s;@CIVIDB@;$civi_dbname;g" \
      -e "s;@DRUPDB@;$drup_dbname;g" \
      -e "s;@LOGDB@;$log_dbname;g" $@
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
get_dbname=0
insecure_login=0
replace_macros=0
db_type=civi
be_quiet=0
colname_arg="--skip-column_names"
create_db=0
force_arg=
no_db=0

while [ $# -gt 0 ]; do
  case "$1" in
    --help) usage; exit 0 ;;
    -f|--sqlfile) sqlfile=`get_next_arg "$@"` && shift || exit 1 ;;
    -c|--cmd) sqlcmd=`get_next_arg "$@"` && shift || exit 1;;
    -d|--dump) dump_db=1 ;;
    -e|--skip-table) arg=`get_next_arg "$@"` || exit 1
                     skip_tabs="$skip_tabs $arg"; shift ;;
    -t|--dump-table) arg=`get_next_arg "$@"` || exit 1
                     dump_tabs="$dump_tabs $arg"; dump_db=1; shift ;;
    -s|--schema*) nodata_arg="--no-data" ;;
    -l|--login-path) dbloginpath=`get_next_arg "$@"` && shift || exit 1 ;;
    -h|--host) dbhost=`get_next_arg "$@"` && shift || exit 1 ;;
    -n|--db*) dbname=`get_next_arg "$@"` && shift || exit 1 ;;
    -u|--user) dbuser=`get_next_arg "$@"` && shift || exit 1 ;;
    -p|--pass*) dbpass=`get_next_arg "$@"` && shift || exit 1 ;;
    -g|--get-db*) get_dbname=1 ;;
    -i|--insec*) insecure_login=1 ;;
    -r|--replace*) replace_macros=1 ;;
    -q|--quiet) be_quiet=1 ;;
    --col*) colname_arg="--column-names" ;;
    --create) create_db=1 ;;
    --force) force_arg="--force" ;;
    --no-db) no_db=1 ;;
    -C|--civi*) db_type=civi ;;
    -D|--drup*) db_type=drup ;;
    -L|--log) db_type=log ;;
    -*) echo "$prog: $1: Invalid option" >&2; exit 1 ;;
    *) instance="$1" ;;
  esac
  shift
done

if [ "$instance" -a "$dbname" ]; then
  echo "$prog: Please specify either an instance or a dbname, but not both" >&2
  exit 1
elif [ ! "$instance" -a ! "$dbname" -a $no_db -eq 0 ]; then
  echo "$prog: Must specify either an instance or a dbname" >&2
  exit 1
elif [ $replace_macros -eq 1 -a ! "$instance" ]; then
  echo "$prog: An instance must be specified when using --replace-macros" >&2
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
#
# Furthermore, the macros @CIVIDB@, @DRUPDB@, and @LOGDB@ will get
# replaced in SQL (both command line and from a file) if the --replace-macros
# option is used.

if [ "$instance" ]; then
  if ! $readConfig --instance $instance --quiet; then
    echo "$prog: $instance: Instance not found" >&2
    exit 1
  fi

  ig_opt="--ig $instance"

  civi_prefix=`$readConfig $ig_opt db.civicrm.prefix` || civi_prefix="$DEFAULT_DB_CIVICRM_PREFIX"
  drup_prefix=`$readConfig $ig_opt db.drupal.prefix` || drup_prefix="$DEFAULT_DB_DRUPAL_PREFIX"
  log_prefix=`$readConfig $ig_opt db.log.prefix` || log_prefix="$DEFAULT_DB_LOG_PREFIX"

  db_basename=`$readConfig --instance $instance db.basename` || db_basename="$instance"

  # Formulate all three db names, since macro expansion might be used.
  # Typically, only one of these three dbs will actually be used.
  civi_dbname="$civi_prefix$db_basename"
  drup_dbname="$drup_prefix$db_basename"
  log_dbname="$log_prefix$db_basename"

  dbname_var=${db_type}_dbname
  dbname=${!dbname_var}
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

if [ $get_dbname -eq 1 ]; then
  echo $dbname
elif [ $dump_db -eq 1 ]; then
  dump_args="--single-transaction --quick"
  if [ "$skip_tabs" ]; then
    for tab in $skip_tabs; do
      dump_args="$dump_args --ignore-table $dbname.$tab"
    done
  fi
  if [ ! "$dump_tabs" ]; then
    # Dump routines (functions & procedures) if the entire db is being dumped
    dump_args="$dump_args --routines"
  fi

  # Do not use 'set -x' here, since mysqldump writes to stdout
  mysqldump $common_args $nodata_arg $dump_args $dbname $dump_tabs
elif [ $create_db -eq 1 ]; then
  if [ ! "$dbname" ]; then
    echo "$prog: Cannot create a database without specifying its name or instance." >&2
    exit 1
  fi
  [ $be_quiet -eq 0 ] && set -x
  mysql $mysql_args -e "create database \`$dbname\`"
elif [ "$sqlfile" ]; then
  if [ $replace_macros -eq 1 ]; then
    sql_filter=filter_replace_macros
  else
    sql_filter=cat
  fi
  [ $be_quiet -eq 0 ] && set -x
  $sql_filter "$sqlfile" | mysql $mysql_args $dbname
else
  if [ $replace_macros -eq 1 ]; then
    sqlcmd=`echo $sqlcmd | filter_replace_macros`
  fi
  [ $be_quiet -eq 0 ] && set -x
  mysql $mysql_args -e "$sqlcmd" $dbname
fi
