#!/bin/sh
#
# manageSendgrid.sh - Perform operations on Sendgrid subusers via the API
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2011-12-30
# Revised: 2012-01-16
#

prog=`basename $0`
script_dir=`dirname $0`
readConfig=$script_dir/readConfig.sh
apiUrlBase="https://sendgrid.com/api"
nyssEventCallbackUrl="http://sendgrid.nysenate.gov/callback.php"

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--get-blocks|-gbs] [--get-bounces|-gb] [--get-invalidemails|-gi] [--get-spamreports|-gs] [--get-unsubscribes|-gs] [--delete-bounces|-db] [--delete-invalidemails|-di] [--delete-spamreports|-ds] [--delete-unsubscribes|-du] [--list-apps|-la] [--activate-app|-aa appName] [--deactivate-app|-da appName] [--setup-app|-sa appName] [--enable-eventnotify|-ee] [--disable-eventnotify] [--get-app-settings|-gas appName] [--get-event-url|-geu] [--set-event-url|-seu url] [--delete-event-url|-deu] [--cmd|-c apiCommand] [--param|-p attr=val] [--json|-j] [--pretty-print|-pp] [--http-headers|-hh] [--verbose|-v] instanceName" >&2
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
curl_args="--fail --silent --show-error"
use_http_post=0
multicmd=

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
    --deactivate*|-da) shift; cmd="filter.deactivate"; params="$params&name=$1" ;;
    --setup*|-sa) shift; cmd="filter.setup"; params="$params&name=$1"; use_http_post=1 ;;
    --enable-eventnotify|-ee) multicmd=enable_eventnotify ;;
    --disable-eventnotify|-de) multicmd=disable_eventnotify ;;
    --get-app-settings|-gas) shift; cmd="filter.getsettings"; params="$params&name=$1" ;;
    --get-event-url|-geu) cmd="eventposturl.get" ;;
    --set-event-url|-seu) shift; cmd="eventposturl.set"; params="$params&url=$1" ;;
    --delete-event-url|-deu) cmd="eventposturl.delete" ;;
    --cmd|-c) shift; cmd="$1" ;;
    --param|-p) shift; params="$params&$1" ;;
    --json|-j) format=json ;;
    --pretty*|-pp) pretty_print=1 ;;
    --http*|-hh) curl_args="$curl_args --include" ;;
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

if [ "$multicmd" ]; then
  case "$multicmd" in
    enable_eventnotify)
      # Note: url= parameter is required. All others are optional.
      $0 --activate-app eventnotify $instance && \
        $0 --setup-app eventnotify -p url="$nyssEventCallbackUrl" -p processed=1 -p dropped=1 -p deferred=1 -p delivered=1 -p bounce=1 -p click=1 -p open=1 -p unsubscribe=1 -p spamreport=1 $instance
      rc=$?
      echo "$prog: Warning: The Batch Event Notifications setting is not accessible via the Sendgrid API.  It must be set manually via the Web interface."
      ;;
    disable_eventnotify)
      $0 --setup-app eventnotify -p url="NULL" -p processed=0 -p dropped=0 -p deferred=0 -p delivered=0 -p bounce=0 -p click=0 -p open=0 -p unsubscribe=0 -p spamreport=0 $instance && \
        $0 --deactivate-app eventnotify $instance
      rc=$?
      ;;
    *) echo "$prog: $multicmd: Unknown multi-command" >&2; rc=1 ;;
  esac
  exit $rc
fi

subusername=`$readConfig --ig $instance smtp.subuser`
subuserpass=`$readConfig --ig $instance smtp.subpass`
params="api_user=$subusername&api_key=$subuserpass$params"
apiUrl="$apiUrlBase/$cmd.$format"

if [ $use_http_post -eq 1 ]; then
  [ $verbose -eq 1 ] && echo "About to post to URL [$apiUrl] with data [$params]" >&2 && set -x
  result=`curl $curl_args --data "$params" "$apiUrl"`
  rc=$?
else
  [ $verbose -eq 1 ] && echo "About to get from URL [$apiUrl?$params]" >&2 && set -x
  result=`curl $curl_args "$apiUrl?$params"`
  rc=$?
fi

if [ $pretty_print -eq 1 ]; then
  case $format in
    json) echo "$result" | sed 's;};}\n;g' ;;
    *) echo "$result" | sed 's;\(<[^/]\);\n\1;g' ;;
  esac
else
  echo "$result"
fi

exit $rc
