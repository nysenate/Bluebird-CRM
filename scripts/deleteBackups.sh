#!/bin/sh
#
# deleteBackups.sh - Delete instance backup files (data dumps)
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2012-08-10
# Revised: 2012-08-10
# Revised: 2016-04-28 - removed data.basename; using data.dirname instead
#

prog=`basename $0`
script_dir=`dirname $0`
readConfig=$script_dir/readConfig.sh

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--ok] [--leave N] instanceName" >&2
  echo "Use the --leave option to keep the most recent N backup files" >&2
}

force_ok=0
leave=0
instance=

while [ $# -gt 0 ]; do
  case "$1" in
    --help|-h) usage; exit 0 ;;
    --ok) force_ok=1 ;;
    --leave|-l) shift; leave="$1" ;;
    -*) echo "$prog: $1: Invalid option" >&2 ; usage ; exit 1 ;;
    *) instance="$1" ;;
  esac
  shift
done

if [ ! "$instance" ]; then
  echo "$prog: Must specify an instance to delete" >&2
  exit 1
elif ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

data_rootdir=`$readConfig --ig $instance data.rootdir` || data_rootdir="$DEFAULT_DATA_ROOTDIR"
data_dirname=`$readConfig --ig $instance data.dirname` || data_dirname="$instance"
backup_dir="$data_rootdir/$data_dirname/nyss_backup"
line_num=$(($leave + 1))

cd "$backup_dir" || exit 1

if [ $force_ok -eq 0 ]; then
  file_list=`ls -t *.zip | tail -n +$line_num`
  if [ "$file_list" ]; then
    echo "The following files will be deleted:"
    echo "$file_list"
    echo
    confirm_yes_no "Are you sure that you want to delete these files" || exit 0
  else
    echo "There are no files to be deleted."
    exit 0
  fi
fi

ls -t *.zip 2>/dev/null | tail -n +$line_num | xargs -I'{}' rm -v '{}'

exit 0
