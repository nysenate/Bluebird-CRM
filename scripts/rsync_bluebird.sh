#!/bin/sh
#
# rsync_bluebird.sh - Synchronize the local repo with the production codebase
#
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-07
#

prog=`basename $0`
script_dir=`dirname $0`

. $script_dir/defaults.sh

dry_run_opt=
delete_opt="--delete"
repo_dir="$HOME/Bluebird-CRM"
target_dir=

usage() {
  echo "Usage: $prog [--dry-run] [--no-delete] [--repo-dir dir] target-dir" >&2
}


while [ $# -gt 0 ]; do
  case "$1" in
    -n|--dry-run) dry_run_opt="-n" ;;
    --no-delete) delete_opt="" ;;
    --repo-dir|-r) shift; repo_dir="$1" ;;
    -*) echo "$prog: $1: Invalid option"; usage ; exit 1 ;;
    *) target_dir="$1" ;;
  esac
  shift
done

if [ ! "$target_dir" ]; then
  echo "$prog: Must specify the target directory for Bluebird installation" >&2
  exit 1
elif [ ! -d "$target_dir" ]; then
  echo "$prog: $target_dir: Target directory does not exist" >&2
  exit 1
elif [ ! -d "$repo_dir" ]; then
  echo "$prog: $repo_dir: Repository directory not found" >&2
  exit 1
fi

# rsync the scripts/, senateProduction/, and www/ dirs, but exclude sync
# for all sites/ directories that are of the form *.*.  This will skip
# <instance>.crm.nysenate.gov, but not "all" and "default".
set -x
rsync -rltOv $dry_run_opt $delete_opt --exclude sites/*.* $repo_dir/* "/$target_dir/"

