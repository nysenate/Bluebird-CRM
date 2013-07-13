#!/bin/sh
#
# countIssueNotes.sh - Count number of "OMIS ISSUE CODES" notes in CRM.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2011-03-08
# Revised: 2011-03-08
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog instanceName" >&2
}

instance=

while [ $# -gt 0 ]; do
  case "$1" in
    -*) echo "$prog: $1: Invalid option" >&2 ; usage ; exit 1 ;;
    *) instance="$1" ;;
  esac
  shift
done

if [ ! "$instance" ]; then
  echo "$prog: Must specify an instance to search" >&2
  usage
  exit 1
fi

sql="select count(*) from civicrm_note where subject='OMIS ISSUE CODES'"

cnt=`$execSql -q $instance -c "$sql"`

if [ $? -eq 0 ]; then
  echo "$instance: $cnt"
  exit 0
else
  exit 1
fi
