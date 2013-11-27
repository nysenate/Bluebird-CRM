#!/bin/sh
#
# manageSendgrid.sh - Perform operations on Sendgrid subusers via the API
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2011-12-30
# Revised: 2012-01-26
#

prog=`basename $0`
script_dir=`dirname $0`
readConfig=$script_dir/readConfig.sh
apiUrlBase="https://sendgrid.com/api"
nyssEventCallbackUrl="http://sendgrid.nysenate.gov/callback.php"

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [options] instanceName
where [options] are:
  --bluebird-setup|-bs
  --get-blocks|-gbl
  --get-bounces|-gb
  --get-invalidemails|-gi
  --get-spamreports|-gs
  --get-unsubscribes|-gu
  --delete-bounces
  --delete-invalidemails
  --delete-spamreports
  --delete-unsubscribes
  --list-apps|-l
  --list-active|-la
  --list-inactive|-li
  --activate-app|-aa appName
  --deactivate-app|-da appName
  --setup-app|-sa appName
  --enable-eventnotify|-ee
  --disable-eventnotify
  --enable-subscriptiontrack|-es
  --disable-subscriptiontrack|-ds
  --get-app-settings|-gas appName
  --get-event-url|-geu
  --set-event-url|-seu url
  --delete-event-url|-deu
  --cmd|-c apiCommand
  --param|-p attr=val
  --json|-j
  --pretty-print|-pp
  --http-headers|-hh
  --verbose|-v" >&2
}

format_json() {
  sed -e 's;\[;;' -e 's;\];;' -e 's;},;}\n;g'
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
postproc=
verbose=0
curl_args="--insecure --fail --silent --show-error"
use_http_post=0
multicmd=
passthru_args=

while [ $# -gt 0 ]; do
  case "$1" in
    --bluebird-setup|-bs) multicmd=bluebird_setup ;;
    --get-block*|-gbl) cmd="blocks.get"; params="$params&date=1" ;;
    --get-bounce*|-gb) cmd="bounces.get"; params="$params&date=1" ;;
    --get-invalid*|-gi) cmd="invalidemails.get"; params="$params&date=1" ;;
    --get-spam*|-gs) cmd="spamreports.get"; params="$params&date=1" ;;
    --get-unsub*|-gu) cmd="unsubscribes.get"; params="$params&date=1" ;;
    --delete-bounce*) cmd="bounces.delete" ;;
    --delete-inv*) cmd="invalidemails.delete" ;;
    --delete-spam*) cmd="spamreports.delete" ;;
    --delete-unsub*) cmd="unsubscribes.delete" ;;
    --list-app*|-l) cmd="filter.getavailable" ;;
    --list-active|-la) cmd="filter.getavailable"; format=json; postproc=active ;;
    --list-inactive|-li) cmd="filter.getavailable"; format=json; postproc=inactive ;;
    --activate*|-aa) shift; cmd="filter.activate"; params="$params&name=$1" ;;
    --deactivate*|-da) shift; cmd="filter.deactivate"; params="$params&name=$1" ;;
    --setup*|-sa) shift; cmd="filter.setup"; params="$params&name=$1"; use_http_post=1 ;;
    --enable-event*|-ee) multicmd=enable_eventnotify ;;
    --disable-event*|-de) multicmd=disable_eventnotify ;;
    --enable-sub*|-es) multicmd=enable_subscriptiontrack ;;
    --disable-sub*|-ds) multicmd=disable_subscriptiontrack ;;
    --get-app-settings|-gas) shift; cmd="filter.getsettings"; params="$params&name=$1" ;;
    --get-event-url|-geu) cmd="eventposturl.get" ;;
    --set-event-url|-seu) shift; cmd="eventposturl.set"; params="$params&url=$1" ;;
    --delete-event-url|-deu) cmd="eventposturl.delete" ;;
    --cmd|-c) shift; cmd="$1" ;;
    --param|-p) shift; params="$params&$1" ;;
    --json|-j) format=json; passthru_args="$passthru_args $1" ;;
    --pretty*|-pp) postproc=pretty; passthru_args="$passthru_args $1" ;;
    --http*|-hh) curl_args="$curl_args --include"; passthru_args="$passthru_args $1" ;;
    --verbose|-v) verbose=1; passthru_args="$passthru_args $1" ;;
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

if [ ! "$subusername" -o ! "$subuserpass" ]; then
  echo "$prog: Sendgrid subuser account info (username or password) not found" >&2
  exit 1
fi


if [ "$multicmd" ]; then
  case "$multicmd" in
    bluebird_setup)
      $0 $passthru_args --enable-eventnotify $instance
      $0 $passthru_args --enable-subscriptiontrack $instance
      $0 $passthru_args --activate-app clicktrack $instance
      $0 $passthru_args --activate-app opentrack $instance
      $0 $passthru_args --activate-app dkim $instance
      # Kludge: Activate domainkeys before deactivating it, because the
      # Sendgrid app deactivation is actually a toggle.  So if an app is
      # already deactivated, then deactivating it will actually activate it.
      # Yeah, real intuitive.
      $0 $passthru_args --activate-app domainkeys $instance
      $0 $passthru_args --deactivate-app domainkeys $instance
      ;;
    enable_eventnotify)
      # Note: url= parameter is required. All others are optional.
      $0 $passthru_args --activate-app eventnotify $instance && \
        $0 $passthru_args --setup-app eventnotify -p url="$nyssEventCallbackUrl" -p processed=1 -p dropped=1 -p deferred=1 -p delivered=1 -p bounce=1 -p click=1 -p open=1 -p unsubscribe=1 -p spamreport=1 $instance
      rc=$?
      echo "$prog: Warning: The Batch Event Notifications setting is not accessible via the Sendgrid API.  It must be set manually via the Web interface."
      ;;
    disable_eventnotify)
      # Only need to specify url=%00.  The others will be set to blank.
      $0 $passthru_args --setup-app eventnotify -p url="%00" $instance && \
        $0 $passthru_args --deactivate-app eventnotify $instance
      rc=$?
      ;;
    enable_subscriptiontrack)
      $0 $passthru_args --activate-app subscriptiontrack $instance && \
        $0 $passthru_args --setup-app subscriptiontrack -p "text/plain=If you would like to stop receiving emails from your Senator, click here: <% %>." -p 'text/html=<p style="text-align: center;font-size:10px;">If you would like to stop receiving emails from your Senator, %26lt;%25 click here %25%26gt;.</p>' $instance
      ;;
    disable_subscriptiontrack)
      $0 $passthru_args --setup-app subscriptiontrack -p "text/plain=" -p "text/html=" -p "url=%00" $instance && \
        $0 $passthru_args --deactivate-app subscriptiontrack $instance
      ;;
    *) echo "$prog: $multicmd: Unknown multi-command" >&2; rc=1 ;;
  esac
  exit $rc
fi


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

case "$postproc" in
  pretty)
    case $format in
      json) echo "$result" | format_json ;;
      *) echo "$result" | sed 's;\(<[^/]\);\n\1;g' ;;
    esac
    ;;
  active|inactive)
    [ "$postproc" = "active" ] && astr="true" || astr="false"
    echo "$result" | format_json | grep "activated.:$astr" | sed 's;.*"name":"\([^"]*\)".*;\1;' | sort | tr '\n' ' '
    echo
    ;;
  *) echo "$result" ;;
esac

exit $rc
