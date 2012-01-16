#!/bin/sh
#
# manageAccumulator.sh - Perform operations on the Sendgrid Stats Accumulator
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2012-01-11
# Revised: 2012-01-15
#
# This script will target only those records in the Accumulator which match
# the given instance/install_class.  For example, if this script is run on
# crmdev with the sd99 instance, then only Accumulator records with a
# servername field of sd99.crmdev.nysenate.gov will be matched.
#
# Use the --server-name option to override the default servername.  Note that
# this is a dangerous option to use.  You will be targeting Accumulator
# records that are not within the scope of the current install_class and
# instance.
#

prog=`basename $0`
script_dir=`dirname $0`
readConfig=$script_dir/readConfig.sh

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--help|-h] [--verbose|-v] [--list|-l] [--delete] [--ok] [--event|-e event] [--date-start|-d date] [--date-end|-D date] [--mailing-id|-m id] [--job-id|-j id] [--queue-id|-q id] [--processed|-p] [--column-names|-c] [--server-name host] instance" >&2
}

verbose=0
instance=
servername=
oper=
force_ok=0
colname_arg="--skip-column-names"
cond="1"

if [ $# -lt 1 ]; then
  usage
  exit 1
fi

while [ $# -gt 0 ]; do
  case "$1" in
    --help|-h) usage; exit 0 ;;
    --verbose|-v) verbose=1 ;;
    --list|-l) oper=list ;;
    --delete) oper=delete ;;
    --ok) force_ok=1 ;;
    --col*|-c) colname_arg="--column-names" ;;
    --event|-e) shift; cond="$cond and event='$1'" ;;
    --date-start|-d) shift; cond="$cond and cast(timestamp as date)>='$1'" ;;
    --date-end|-D) shift; cond="$cond and cast(timestamp as date)<='$1'" ;;
    --mailing*|-m) shift; cond="$cond and mailing_id=$1" ;;
    --job*|-j) shift; cond="$cond and job_id=$1" ;;
    --queue*|-q) shift; cond="$cond and queue_id=$1" ;;
    --processed|-p) cond="$cond and processed<>0" ;;
    --server*) shift; servername="$1" ;;
    -*) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
    *) instance="$1" ;;
  esac
  shift
done

if [ ! "$instance" ]; then
  echo "$prog: Must specify an instance to manage" >&2
  usage
  exit 1
elif ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

accum_host=`$readConfig --ig $instance accumulator.host`
accum_port=`$readConfig --ig $instance accumulator.port`
accum_name=`$readConfig --ig $instance accumulator.name`
accum_user=`$readConfig --ig $instance accumulator.user`
accum_pass=`$readConfig --ig $instance accumulator.pass`
base_domain=`$readConfig --ig $instance base.domain`
[ ! "$servername" ] && servername="$instance.$base_domain"
if echo "$servername" | grep -q '%'; then
  cond="$cond and servername like '$servername'"
else
  cond="$cond and servername='$servername'"
fi
port_arg=

if [ ! "$accum_host" -o ! "$accum_name" -o ! "$accum_user" -o ! "$accum_pass" ]; then
  echo "$prog: Config parameters accumulator.{host,name,user,pass} must be set." >&2
  exit 2
fi

[ "$accum_port" ] && port_arg="-P$accum_port"

if [ "$oper" = "list" ]; then
  sql="select * from event where $cond"
elif [ "$oper" = "delete" ]; then
  if [ $force_ok -eq 0 ]; then
    confirm_yes_no "Proceed with event deletion operation" || exit 0
  fi
  sql="delete from event where $cond"
else
  sql="select count(*) from event where $cond"
fi

[ $verbose -eq 1 ] && set -x
mysql -h"$accum_host" $port_arg -u"$accum_user" -p"$accum_pass" "$accum_name" -e "$sql;" --batch $colname_arg

exit $?
