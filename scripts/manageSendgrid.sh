#!/bin/bash
#
# manageSendgrid.sh - Perform operations on Sendgrid subusers via the API
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2011-12-30
# Revised: 2012-01-26
# Revised: 2013-12-04 - added Event API v3 functionality; use JSON by default
# Revised: 2016-05-05 - added Subuser API management options
# Revised: 2016-05-09 - added options to override config file
# Revised: 2021-01-14 - fix mismatched option --subpassword
#

prog=`basename $0`
script_dir=`dirname $0`
readConfig=$script_dir/readConfig.sh
apiUrlBase="https://api.sendgrid.com/apiv2"
# Hard-coding the EventNotify version since v1 and v2 are no longer supported
eventNotifyVersion=3

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
  --delete-blocks
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
  --get-app-settings|-gas appName
  --enable-eventnotify|-ee
  --disable-eventnotify|-de
  --get-eventnotify-settings|-ges
  --enable-subscriptiontrack|-es
  --disable-subscriptiontrack|-ds
  --get-subscriptiontrack-settings|-gss
  --get-event-url|-geu
  --set-event-url|-seu url
  --delete-event-url|-deu
  --list-all-subusers|-las
  --get-subuser|-gsu [username]
  --set-email|-se email
  --set-password|-sp [password]
  --username|-U username (overrides sendgrid.username)
  --password|-P password (overrides sendgrid.password)
  --subusername|-SU) username (overrides smtp.username)
  --subpassword|-SP) password (overrides smtp.password)
  --api-category|-ac apiCategory ['customer'|'user']
  --cmd|-c apiCommand
  --task|-t task
  --param|-p attr=val
  --json|-j
  --xml|-x
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

format=json
instance=
oper=
acat=customer
cmd=
task=
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
    --get-block*|-gbl) cmd="blocks"; task="get"; params="$params&date=1" ;;
    --get-bounce*|-gb) cmd="bounces"; task="get"; params="$params&date=1" ;;
    --get-invalid*|-gi) cmd="invalidemails"; task="get"; params="$params&date=1" ;;
    --get-spam*|-gs) cmd="spamreports"; task="get"; params="$params&date=1" ;;
    --get-unsub*|-gu) cmd="unsubscribes"; task="get"; params="$params&date=1" ;;
    --delete-block*) cmd="bounces"; task="delete" ;;
    --delete-bounce*) cmd="bounces"; task="delete" ;;
    --delete-invalid*) cmd="invalidemails"; task="delete" ;;
    --delete-spam*) cmd="spamreports"; task="delete" ;;
    --delete-unsub*) cmd="unsubscribes"; task="delete" ;;
    --list-app*|-l) cmd="apps"; task="getAvailable" ;;
    --list-active|-la) cmd="apps" task="getAvailable"; format=json; postproc=active ;;
    --list-inactive|-li) cmd="apps"; task="getAvailable"; format=json; postproc=inactive ;;
    --activate*|-aa) shift; cmd="apps"; task="activate"; params="$params&name=$1" ;;
    --deactivate*|-da) shift; cmd="apps"; task="deactivate"; params="$params&name=$1" ;;
    --setup*|-sa) shift; cmd="apps"; task="setup"; params="$params&name=$1"; use_http_post=1 ;;
    --get-app-settings|-gas) shift; cmd="apps"; task="getsettings"; params="$params&name=$1" ;;
    --enable-event*|-ee) multicmd=enable_eventnotify ;;
    --disable-event*|-de) multicmd=disable_eventnotify ;;
    --get-event*|-ges) cmd="apps"; task="getsettings"; params="$params&name=eventnotify" ;;
    --enable-sub*|-es) multicmd=enable_subscriptiontrack ;;
    --disable-sub*|-ds) multicmd=disable_subscriptiontrack ;;
    --get-sub*|-gss) cmd="apps"; task="getsettings"; params="$params&name=subscriptiontrack" ;;
    --get-event-url|-geu) cmd="eventposturl"; task="get" ;;
    --set-event-url|-seu) cmd="eventposturl"; task="set";
        if [ -n "$2" -a "${2:0:1}" != "-" ]; then
          shift
          params="$params&url=$1"
        else
          params="$params&url=%EVENTNOTIFYURL%"
        fi ;;
    --delete-event-url|-deu) cmd="eventposturl"; task="delete" ;;
    --list-all-subusers|-las) cmd="profile"; task="get" ;;
    --get-subuser|-gsu) cmd="profile"; task="get"
        if [ -n "$2" -a "${2:0:1}" != "-" ]; then
          shift
          params="$params&username=$1"
        else
          params="$params&username=%SUBUSERNAME%"
        fi ;;
    --set-email|-se) shift; cmd="profile"; task="setEmail"; params="$params&email=$1" ;;
    --set-password|-sp) cmd="password"
        if [ -n "$2" -a "${2:0:1}" != "-" ]; then
          shift
          params="$params&password=$1&confirm_password=$1"
        else
          params="$params&password=%SUBUSERPASS%&confirm_password=%SUBUSERPASS%"
        fi ;;
    --user*|-U) shift; sgusername="$1" ;;
    --pass*|-P) shift; sguserpass="$1" ;;
    --subuser*|-SU) shift; subusername="$1" ;;
    --subpass*|-SP) shift; subuserpass="$1" ;;
    --api-category|-ac) shift; acat="$1" ;;
    --cmd|-c) shift; cmd="$1" ;;
    --task|-t) shift; task="$1" ;;
    --param|-p) shift; params="$params&$1" ;;
    --json|-j) format=json; passthru_args="$passthru_args $1" ;;
    --xml|-x) format=xml; passthru_args="$passthru_args $1" ;;
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

# Read the master SendGrid user credentials from the BBcfg [global] section
[ "$sgusername" ] || sgusername=`$readConfig --global sendgrid.username`
[ "$sguserpass" ] || sguserpass=`$readConfig --global sendgrid.password`
# Read the subuser credentials for the current instance from the BB config
[ "$subusername" ] || subusername=`$readConfig --ig $instance smtp.username`
[ "$subuserpass" ] || subuserpass=`$readConfig --ig $instance smtp.password`
# Read the Event Notify URL from the BB config
event_url=`$readConfig --ig $instance sendgrid.eventnotify_url`

if [ ! "$sgusername" -o ! "sguserpass" ]; then
  echo "$prog: SendGrid master account info (username or password) must be set in config file" >&2
  exit 1
elif [ ! "$subusername" -o ! "$subuserpass" ]; then
  echo "$prog: SendGrid subuser account info (username or password) must be set in config file" >&2
  exit 1
elif [ ! "$event_url" ]; then
  echo "$prog: SendGrid Event Notify URL must be set in config file" >&2
  exit 1
fi

params=`echo $params | sed -e "s;%SUBUSERNAME%;$subusername;g" -e "s;%SUBUSERPASS%;$subuserpass;g" -e "s;%EVENTNOTIFYURL%;$event_url;g"`

if [ "$multicmd" ]; then
  case "$multicmd" in
    bluebird_setup)
      $0 $passthru_args --enable-eventnotify $instance
      $0 $passthru_args --enable-subscriptiontrack $instance
      $0 $passthru_args --activate-app clicktrack $instance
      $0 $passthru_args --activate-app opentrack $instance
      $0 $passthru_args --activate-app dkim $instance
      ;;
    enable_eventnotify)
      # Note: url= parameter is required. All others are optional.
      $0 $passthru_args --activate-app eventnotify $instance && \
        $0 $passthru_args --setup-app eventnotify -p url="$event_url" -p version="$eventNotifyVersion" -p processed=1 -p dropped=1 -p deferred=1 -p delivered=1 -p bounce=1 -p click=1 -p open=1 -p unsubscribe=1 -p spamreport=1 -p batch=1 $instance
      rc=$?
      ;;
    disable_eventnotify)
      # Only need to specify url=%00.  The others will be set to blank.
      $0 $passthru_args --setup-app eventnotify -p url="%00" $instance && \
        $0 $passthru_args --deactivate-app eventnotify $instance
      rc=$?
      ;;
    enable_subscriptiontrack)
      $0 $passthru_args --activate-app subscriptiontrack $instance && \
        $0 $passthru_args --setup-app subscriptiontrack -p "text/plain=If you would like to stop receiving emails from your senator, click here: <% %>." -p 'text/html=<p style="text-align:center;font-size:10px;">If you would like to stop receiving emails from your senator, <% click here %>.</p>' $instance
      ;;
    disable_subscriptiontrack)
      $0 $passthru_args --setup-app subscriptiontrack -p "text/plain=" -p "text/html=" -p "url=%00" $instance && \
        $0 $passthru_args --deactivate-app subscriptiontrack $instance
      ;;
    *) echo "$prog: $multicmd: Unknown multi-command" >&2; rc=1 ;;
  esac
  exit $rc
elif [ ! "$cmd" ]; then
  echo "$prog: No command was specified" >&2
  exit 1
fi


params="api_user=$sgusername&api_key=$sguserpass&user=$subusername&task=$task$params"
apiUrl="$apiUrlBase/$acat.$cmd.$format"

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
