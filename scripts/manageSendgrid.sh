#!/bin/sh
#
# manageSendgrid.sh - Perform operations on Sendgrid subusers via the API
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2011-12-30
# Revised: 2012-01-09
#

prog=`basename $0`
script_dir=`dirname $0`
readConfig=$script_dir/readConfig.sh
apiUrlBase="https://sendgrid.com/api"

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--get-blocks|-gbs] [--get-bounces|-gb] [--get-invalidemails|-gi] [--get-spamreports|-gs] [--get-unsubscribes|-gs] [--delete-bounces|-db] [--delete-invalidemails|-di] [--delete-spamreports|-ds] [--delete-unsubscribes|-du] [--list-apps|-la] [--activate-app|-aa appName] [--deactivate-app|-da appName] [--get-app-settings|-gas appName] [--get-event-url|-geu] [--set-event-url|-seu url] [--delete-event-url|-deu] [--cmd|-c apiCommand] [--param|-p attr=val] [--json|-j] [--pretty-print|-pp] [--http-headers|-hh] [--verbose|-v] instanceName" >&2
}

if [ $# -lt 1 ]; then
  usage
  exit 1
fi

format=xml
instance=
oper=
cmd=
params=
pretty_print=0
verbose=0
wget_args=

while [ $# -gt 0 ]; do
  case "$1" in
    --get-block*|-gbl) cmd="blocks.get"; params="$params&date=1" ;;
    --get-bounce*|-gb) cmd="bounces.get"; params="$params&date=1" ;;
    --get-invalid*|-gi) cmd="invalidemails.get"; params="$params&date=1" ;;
    --get-spam*|-gs) cmd="spamreports.get"; params="$params&date=1" ;;
    --get-unsub*|-gu) cmd="unsubscribes.get"; params="$params&date=1" ;;
    --delete-bounce*|-db) cmd="bounces.delete" ;;
    --delete-invalid*|-di) cmd="invalidemails.delete" ;;
    --delete-spam*|-ds) cmd="spamreports.delete" ;;
    --delete-unsub*|-du) cmd="unsubscribes.delete" ;;
    --list-app*|-la) cmd="filter.getavailable" ;;
    --activate*|-aa) shift; cmd="filter.activate"; params="$params&name=$1" ;;
    --deactivate*|-da) shift; cmd="filter.deactivate"; params="$params&name=$1"
      ;;
    --get-app-settings|-gas)
      shift
      cmd="filter.getsettings"
      params="$params&name=$1"
      ;;
    --get-event-url|-geu) cmd="eventposturl.get" ;;
    --set-event-url|-seu) shift; cmd="eventposturl.set"; params="$params&url=$1"
      ;;
    --delete-event-url|-deu) cmd="eventposturl.delete" ;;
    --cmd|-c) shift; cmd="$1" ;;
    --param|-p) shift; params="$params&$1" ;;
    --json|-j) format=json ;;
    --pretty*|-pp) pretty_print=1 ;;
    --http*|-hh) wget_args="$wget_args -S" ;;
    --verbose|-v) verbose=1 ;;
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

subusername=`$readConfig --ig $instance smtp.subuser`
subuserpass=`$readConfig --ig $instance smtp.subpass`
apiUrl="$apiUrlBase/$cmd.$format?api_user=$subusername&api_key=$subuserpass$params"

[ $verbose -eq 1 ] && echo "About to access URL: $apiUrl" >&2
result=`wget $wget_args -q -O- "$apiUrl"`
rc=$?

if [ $pretty_print -eq 1 ]; then
  case $format in
    json) echo $result | sed 's;};}\n;g' ;;
    *) echo $result | sed 's;\(<[^/]\);\n\1;g' ;;
  esac
else
  echo $result
fi

exit $rc
