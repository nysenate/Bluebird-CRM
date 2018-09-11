#!/bin/sh
#
# backup.sh - Back up Bluebird code and data to a local or non-local directory.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-23
# Revised: 2012-04-18
# Revised: 2013-04-24 - added --skip-log-dbs option
# Revised: 2018-09-11 - added --skip-civi-dbs and --skip-drupal-dbs options
#                     - added instance name command line option
#
# Note: When backing up to a non-local directory, SSH is used to make the
#       connection.  The account under which this script is running should
#       provide its SSH public key to the remote host for password-less
#       access.
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
default_rsync_opts="-av --delete --delete-excluded";
backup_host=`$readConfig --global backup.cron.host`
backup_dir=`$readConfig --global backup.cron.rootdir`
db_civicrm_prefix=`$readConfig --global db.civicrm.prefix`
db_drupal_prefix=`$readConfig --global db.drupal.prefix`
db_log_prefix=`$readConfig --global db.log.prefix`
app_rootdir=`$readConfig --global app.rootdir`
data_rootdir=`$readConfig --global data.rootdir`
import_rootdir=`$readConfig --global import.rootdir`
no_dbdump=0
dry_run=0
skip_civi_dbs=0
skip_drup_dbs=0
skip_log_dbs=0
instances=""

usage() {
  echo "Usage: $prog [-h backup-host] [-d backup-dir] [-n|--dry-run] [--local] [--no-dbdump] [--skip-civi-dbs] [--skip-drupal-dbs] [--skip-log-dbs] [instance [instance ...]]" >&2
}

while [ $# -gt 0 ]; do
  case "$1" in
    -d|--backup-dir) shift; backup_dir="$1" ;;
    -h|--host) shift; backup_host="$1" ;;
    --local) backup_host="" ;;
    --no-dbdump) no_dbdump=1 ;;
    -n|--dry-run) dry_run=1 ;;
    --skip-civi*) skip_civi_dbs=1 ;;
    --skip-drup*) skip_drup_dbs=1 ;;
    --skip-log*) skip_log_dbs=1 ;;
    --help) usage; exit 0 ;;
    -*) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
    *) instances="$instances|$1" ;;
  esac
  shift
done

if [ ! "$backup_dir" ]; then
  echo "$prog: Backup directory must be set using backup.rootdir in the Bluebird config file, or the -d command line option." >&2
  exit 1
fi

if [ "$instances" ]; then
  instance_pattern=`echo $instances | cut -c2-`
else
  instance_pattern="."
fi

echo "CRM BACKUP STARTED on `date`"

if [ "$backup_host" ]; then
  if ssh $backup_host "test -d $backup_dir"; then
    echo "Backup directory [$backup_dir] found on host [$backup_host]"
    rsync_host_prefix="$backup_host:"
  else
    echo "$prog: Error: Unable to access $backup_host:$backup_dir" >&2
    exit 1
  fi
else
  if [ -d "$backup_dir" ]; then
    echo "Backup directory is $backup_dir"
    rsync_host_prefix=""
  else
    echo "$prog: $backup_dir: Directory not found; please create the directory before running this script." >&2
    exit 1
  fi
fi

# Databases are backed up using mysqldump, not rsync.  The DB dump is sent
# directly to the backup host.
# The code, data, and config are backed up using rsync.

db_backup_dir="$backup_dir/database_dumps"
code_backup_dir="$backup_dir/application"
data_backup_dir="$backup_dir/data"
other_backup_dir="$backup_dir/other"
rsync_opts="$default_rsync_opts"
[ $dry_run -eq 1 ] && rsync_opts="$rsync_opts -n"

if [ "$backup_host" ]; then
  ssh $backup_host "mkdir -p '$db_backup_dir' '$code_backup_dir' '$data_backup_dir' '$other_backup_dir'"
else
  mkdir -p "$db_backup_dir" "$code_backup_dir" "$data_backup_dir" "$other_backup_dir"
fi
[ $? -eq 0 ] || exit 1

if [ $no_dbdump -eq 0 ]; then
  echo "Calculating databases to be backed up"
  db_pattern="invalid_db_prefix"
  [ $skip_civi_dbs -eq 0 ] && db_pattern="$db_pattern|$db_civicrm_prefix"
  [ $skip_drup_dbs -eq 0 ] && db_pattern="$db_pattern|$db_drupal_prefix"
  [ $skip_log_dbs -eq 0 ] && db_pattern="$db_pattern|$db_log_prefix"
    
  dbs=`$execSql --no-db -c "show databases" | egrep "^($db_pattern)($instance_pattern)"`
  echo "Databases to be dumped: " $dbs

  if [ $dry_run -eq 0 ]; then
    echo "Dumping databases"
    for dbname in $dbs; do
      echo "Backing up $dbname"
      if [ "$backup_host" ]; then
        $execSql --dump --db-name $dbname | ssh $backup_host "cat > $db_backup_dir/$dbname.sql"
      else
        $execSql --dump --db-name $dbname > $db_backup_dir/$dbname.sql
      fi
      [ $? -eq 0 ] && echo "OK" || echo "Failed"
    done
  fi
fi

echo "Backing up /etc"
rsync $rsync_opts /etc $rsync_host_prefix$other_backup_dir/;

echo "Backing up source code"
[ -d $app_rootdir ] && rsync $rsync_opts $app_rootdir/* $rsync_host_prefix$code_backup_dir/
[ -d $data_rootdir ] && rsync $rsync_opts $data_rootdir/* $rsync_host_prefix$data_backup_dir/
[ -d $import_rootdir ] && rsync $rsync_opts $import_rootdir $rsync_host_prefix$data_backup_dir/

echo "CRM BACKUP COMPLETED on `date`"

exit 0
