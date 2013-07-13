#!/bin/sh
#
# deleteExports.sh - Delete print production export files
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2012-05-04
# Revised: 2012-05-04
#

prog=`basename $0`
script_dir=`dirname $0`
readConfig=$script_dir/readConfig.sh
EXPORT_PREFIXES="districtExport_ printExport_ printExportTagStats_"

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--ok] [--leave N] instanceName" >&2
  echo "Use the --leave option to keep the most recent N exports of each type" >&2
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

base_domain=`$readConfig --ig $instance base.domain` || domain="$DEFAULT_BASE_DOMAIN"
data_rootdir=`$readConfig --ig $instance data.rootdir` || data_rootdir="$DEFAULT_DATA_ROOTDIR"
data_basename=`$readConfig --ig $instance data.basename` || data_basename="$instance"
data_dirname="$data_basename.$base_domain"
export_dir="$data_rootdir/$data_dirname/civicrm/upload/printProduction"
line_num=$(($leave + 1))

cd "$export_dir" || exit 1

if [ $force_ok -eq 0 ]; then
  file_list=
  for f in $EXPORT_PREFIXES; do
    fsublist=`ls -t $f* 2>/dev/null | tail -n +$line_num`
    [ "$fsublist" ] && file_list="$file_list\n$fsublist"
  done
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

for f in $EXPORT_PREFIXES; do
  ls -t $f* 2>/dev/null | tail -n +$line_num | xargs -I'{}' rm -v '{}'
done

exit 0
