#!/bin/sh
#
# install.sh - Install the new changelog summary/detail layer.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2014-08-05
#

prog=`basename $0`
this_dir=`dirname $0`
script_dir=`cd $this_dir/..; echo $PWD`
readConfig=$script_dir/readConfig.sh
execSql=$script_dir/execSql.sh

usage() {
  echo "Usage: $prog instance" >&2
}

if [ $# -lt 1 ]; then
  usage
  exit 1
fi

while [ $# -gt 0 ]; do
  case "$1" in
    --help) usage; exit 0 ;;
    -*) echo "$prog: $1: Invalid option" >&2; exit 1 ;;
    *) instance="$1" ;;
  esac
  shift
done

if [ ! "$instance" ]; then
  echo "$prog: Please specify a CRM instance" >&2
  exit 1
fi

if ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

echo "Creating the summary and detail tables"
$execSql -q $instance -f $this_dir/create_tables.sql

echo "Creating the summary and detail table triggers"
$execSql -q $instance -f $this_dir/create_triggers.sql

