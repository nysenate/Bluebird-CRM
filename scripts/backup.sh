#!/bin/sh
#
# backup.sh - Back up Bluebird code and data to a local or non-local directory.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-23
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
backup_host=`$readConfig --group globals backup.host`
backup_dir=`$readConfig --group globals backup.rootdir`
no_dbdump=0
dry_run_opt=

while [ $# -gt 0 ]; do
  case "$1" in
    -d|--backup-dir) shift; backup_dir="$1" ;;
    -h|--host) shift; backup_host="$1" ;;
    --local) backup_host="" ;;
    --no-dbdump) no_dbdump=1 ;;
    -n|--dry-run) dry_run_opt="-n" ;;
    *) echo "Usage: $prog [-h backup-host] [-d backup-dir] [-n] [--local] [--no-dbdump]" >&2 ; exit 1 ;;
  esac
  shift
done

if [ ! "$backup_dir" ]; then
  echo "$prog: Backup directory must be set using backup.rootdir in the Bluebird config file, or the -d command line option." >&2
  exit 1
fi

if [ "$backup_host" ]; then
  if ssh $backup_host "test -d $backup_dir"; then
    echo "Backup directory [$backup_dir] found on host [$backup_host]"
    rsync_host_prefix="$backup_host:"
  else
    echo "$prog: Error: Unable to access $backup_host:$backup_dir" >&2
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

# Databases are backed up using mysqldump, not rsync.  The dump is sent
# directly to the backup host.
# The code and config are backed up using rsync.

db_backup_dir="$backup_dir/database_dumps"
code_backup_dir="$rsync_host_prefix$backup_dir/application"
other_backup_dir="$rsync_host_prefix$backup_dir/other"
rsync_opts="$default_rsync_opts $dry_run_opt"

if [ "$backup_host" ]; then
  ssh $backup_host "mkdir -p '$db_backup_dir' '$code_backup_dir' '$other_backup_dir'"
else
  mkdir -p "$db_backup_dir" "$code_backup_dir" "$other_backup_dir"
fi
[ $? -eq 0 ] || exit 1

if [ $no_dbdump -eq 0 ]; then
  echo "Calculating databases to be backed up"
  dbs=`$execSql -c "show databases" | egrep -v "^Database|information_schema"`
  echo "Databases to be dumped: " $dbs

  echo "Dumping databases"
  for dbname in $dbs; do
    echo "Backing up $dbname"
    if [ "$backup_host" ]; then
      $execSql --dump $dbname | ssh $backup_host "cat > $db_backup_dir/$dbname.sql"
    else
      $execSql --dump $dbname > $db_backup_dir/$dbname.sql
    fi
    [ $? -eq 0 ] && echo "OK" || echo "Failed"
  done
fi

echo "Backing up /etc"
rsync $rsync_opts /etc $other_backup_dir/;

echo "Backing up source code"
rsync $rsync_opts /data/importData $code_backup_dir/
#rsync $rsync_opts /data/loadTesting $code_backup_dir/
rsync $rsync_opts /data/scripts $code_backup_dir/
rsync $rsync_opts /data/senateProduction $code_backup_dir/
rsync $rsync_opts /data/sql $code_backup_dir/
rsync $rsync_opts /data/www $code_backup_dir/

exit $?
