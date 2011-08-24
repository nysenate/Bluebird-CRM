#!/bin/sh
#
# countDupEmails.sh - Provides statistics on number of duplicate e-mails in CRM.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2011-08-24
# Revised: 2011-08-24
#
# Stats are produced as follows:
#   <instance> TAB <uniqueDupes> TAB <totalDupes> TAB <totalEmails>
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh

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

dup_emails=`./execSql.sh -q -i "$instance" -c "select count(*),sum(c) from ( select email, count(*) as c from civicrm_email group by email having count(*) > 1) as kz"`

total_emails=`./execSql.sh -q -i "$instance" -c "select count(*) from civicrm_email where email<>''"`

echo "$instance	$dup_emails	$total_emails"
