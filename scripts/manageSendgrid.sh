#!/bin/bash
#
# manageSendgrid.sh - Perform operations on SendGrid subusers via the API
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
# Revised: 2021-03-17 - convert to API v3
#

#
# NOTE: The SendGrid v3 API uses API keys instead of username/password
# credentials.  The following Bluebird configuration parameters are now
# deprecated as a result of the conversion from v2 to v3:
#   sendgrid.username
#   sendgrid.password
#   smtp.password
# These have been replaced by:
#   sendgrid.api.key
#   smtp.api.key
# which are the API keys attached to the SendGrid parent account and each
# subuser account, respectively.
#
# The smtp.username parameter is still used.
#

#
# When sending SMTP email, Bluebird previously used the smtp.username and
# smtp.password parameters to authenticate to the SendGrid SMTP server.
# While a username and password are still required to send SMTP email, they
# should now be set as follows:
#   SMTP username:  "apikey"
#   SMTP password:  <smtp.api.key paraemter>
#

prog=`basename $0`
script_dir=`dirname $0`
readConfig=$script_dir/readConfig.sh
apiUrlBase="https://sendgrid.com/v3"

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [options] instanceName
where [options] are:
  [Authentication]
  --api-key|-A <api_key>  (overrides sendgrid.api.key or smtp.api.key)
  --use-master-key|-M  (use sendgrid.api.key instead of smtp.api.key)
  --use-on-behalf-of|-O  (set the on-behalf-of header to the SMTP username)
  --username|-U <username>  (overrides smtp.username; only works in master mode)
  --password|-P <password>  (overrides smtp.password; only works in master mode)
  --event-webhook-url|-E <url>  (overrides sendgrid.event_webhook.url)
  [API Key Management]
  --list-keys|-lk  (retrieve all API keys belonging to authenticated user)
  --get-key|-gk <api_key_id>  (retrieve a single API key with given key ID)
  --create-key|-ck  (create new key for user with name 'Bluebird API Key')
  --delete-key <api_key_id>  (delete API key with given key ID)
  --list-scopes|-ls  (get list of all API permissions the user has access to)
  [IP Access Management]
  --list-ip-activity|-lia  (get list of IP addresses that accessed account)
  --list-ip-whitelist|-liw  (get list of allowed IP addresses)
  [Mail Settings and Teammates]
  --get-mail-settings|-gms  (retrieve a list of all mail settings)
  --get-address-whitelist|-gaw  (get email address whitelist settings)
  --get-footer-settings|-gfs  (get current custom Footer mail settings)
  --get-spam-settings|-gss  (get current Forward Spam mail settings)
  --get-template-settings|-gts  (get current legacy template settings)
  --get-purge-settings|-gps  (get current bounce purge settings)
  --get-bounce-settings|-gbs  (get current bounce forwarding mail settings)
  --list-partner-settings|-lps  (retrieve list of all partner settings)
  --list-teammates|-lt  (retrieve list of all current teammates)
  --list-pending-teammates|-lpt  (retrieve list of pending teammate invitations)
  [Alerts]
  --get-usage-alerts|-gua  (retrieve list of all alerts)
  [Users API]
  --get-profile|-gp  (get profile info, such as first/last name, address, etc)
  --get-account|-ga  (get account information, such as reputation)
  --get-email|-ge  (get the email address of the user)
  --set-email|-se <email_addr>  (set the email address of the user)
  --get-username|-gu  (get the username for the current API key)
  --get-credits|-gc  (get the credit balance for the user)
  --set-password|-sp  (set password for subuser; use -P or smtp.password)
  [Subusers API]
  --list-all-subusers|-las  (list all subuser accounts)
  --get-subuser|-gsu  (list a single subuser; use -U or smtp.username)
  --get-subuser-monitor|-gsum  (retrieve monitor settings for a subuser)
  --get-subuser-reputations|-gsur  (retrieve subuser reputations)
  --get-subuser-stats|-gsus <YYYY-MM-DD>  (retrieve monthly subuser stats)
  --get-subuser-totals|-gsut <YYYY-MM-DD>  (retrieve monthly subuser totals)
  [Deliverability]
  --get-branded-links|-gbl  (retrieve all branded links)
  --get-default-branded-link|-gdbl  (retrieve the default branded link)
  --get-subuser-branded-links|-gsbl  (get associated subuser branded links)
  --get-warmup-ips|-gwi  (retrieve all IP addresses that are warming up)
  --get-reverse-dns|-grd  (retrieve all of the Reverse DNS records for account)
  --validate-email-address|-vea <email_addr>  (validate an email address)
  --list-ip-pools|-lip  (retrieve all IP pools)
  --get-ip-pool|-gip <pool_name>  (get IP addresses in the given IP pool)
  --list-ips|-li  (retrieve list of all assigned and unassigned IPs)
  --get-ip-count|-gic  (get count of remaining IP addresses that can be created)
  --list-assigned-ips|-lai  (retrieve list of assigned IP addresses)
  --get-pools-for-ip|-gpfi <ip_addr>  (get all pools that contain IP addr)
  --list-authenticated-domains|-lad  (get list of all authenticated domains)
  --get-default-auth-domain|-gdad  (retrieve default authentication for domain)
  --get-subuser-domains|-gsud  (retrieve authenticated domains for subuser)
  --list-all-verified-senders|-lavs  (retrieve all Sender Identities for user)
  --get-verified-sender-domains|-gvsd  (return list of DMARC domains)
  --get-verified-sender-status|-gvss  (display verification status for account)
  [Event Tracking]
  --get-event-webhook-settings|-gews  (retrieve current event webhook settings)
  --enable-event-webhook|-eew  (enable all standard event webhook event types)
  --disable-event-webhook|-dew  (disable event webhook and all event types)
  --set-event-webhook-url|-sewu  (set the URL for the event webhook endpoint)
  --get-event-webhook-key|-gewk  (retrieve signed public key for webhook)
  --get-parse-webhook-settings|-gpws  (retrieve inbound parse webhook settings)
  --get-parse-webhook-stats|-gpwt <YYYY-MM-DD>  (retrieve parse webhook stats)
  --list-tracking-settings|-lts  (retrieve list of available tracking settings)
  --get-click-tracking-setting|-gcts  (retrieve click tracking setting)
  --enable-click-tracking|-ect  (enable click tracking for this account)
  --disable-click-tracking|-dct  (disable click tracking for this account)
  --get-google-tracking-setting|-ggts  (retrieve Google Analytics track setting)
  --enable-google-tracking|-egt  (enable Google Analytics tracking for account)
  --disable-google-tracking|-dgt  (disable Google Analytics tracking for acct)
  --get-open-tracking-setting|-gots  (retrieve open tracking setting)
  --enable-open-tracking|-eot  (enable open tracking for this account)
  --disable-open-tracking|-dot  (disable open tracking for this account)
  --get-subscription-tracking-setting|-gsts  (retrieve subscription tracking setting)
  --enable-subscription-tracking|-est  (enable subscription tracking for this account)
  --disable-subscription-tracking|-dst  (disable subscription tracking for this account)
  [Stats]
  --get-global-stats|-ggs <YYYY-MM-DD>  (retrieve global email stats)
  --get-stats-by-geo|-gsbg <YYYY-MM-DD>  (retrieve email stats by geolocation)
  --get-stats-by-dev|-gsbd <YYYY-MM-DD>  (retrieve email stats by device type)
  --get-stats-by-client|-gsbc <YYYY-MM-DD>  (retrieve email stats by client)
  --get-stats-by-browser|-gsbb <YYYY-MM-DD>  (retrieve email stats by browser)
  [Suppression Management]
  --list-bounces|-lb <start_time>  (retrieve all bounces from Unix start_time)
  --get-bounce|-gb <email>  (retrieve bounce using email address)
  --delete-bounce <email>  (remove an email address from the bounce list)
  --list-blocks|-lbk <start_time>  (retrieve all blocks from Unix start_time)
  --get-block|-gbk <email>  (retrieve block using email address)
  --delete-block <email>  (remove an email address from the block list)
  --list-spam-reports|-lsr <start_time>  (retrieve all spam reports)
  --get-spam-report|-gsr <email>  (retrieve spam report using email address)
  --delete-spam-report <email>  (delete a spam report by email address)
  --list-invalid-emails|-lie <start_time>  (retrieve all invalid emails)
  --get-invalid-email|-gie <email>  (retrieve invalid email using email addr)
  --delete-invalid-email <email>  (delete an invalid email address)
  --list-unsubscribes|-lus <start_time>  (retrieve all globally suppressed emails)
  --get-unsubscribe|-gus <email>  (retrieve global suppression using email)
  --delete-unsubscribe <email>  (remove email from global suppressions group)
  --get-all-suppressions|-gas)  (retrieve list of all suppressions)
  --get-suppressions-using-group|-gsug) <group_id>  (retrieve suppressions using group id)
  --get-suppressed-groups|-gsg) <email>  (retrieve suppression groups for email)
  [Other]
  --bluebird-setup|-bs
  --cmd|-c apiCommand
  --param|-p attr=val
  --json|-j
  --xml|-x
  --pretty-print|-pp
  --http-headers|-hh
  --verbose|-v" >&2
}

# Confirm that the "jq" JSON parser is installed.
if which jq >/dev/null 2>&1; then
  format_json() {
    jq
  }
else
  echo "$prog: jq is not installed; JSON pretty-printing will be not be very pretty" >&2
  format_json() {
    sed -e 's;^\[;;' -e 's;\]$;;' -e 's;},;},\n;g'
  }
fi

replace_macros() {
  sed -e "s;%SUBUSERNAME%;$subusername;g" -e "s;%SUBUSERPASS%;$subuserpass;g" -e "s;%EVENTURL%;$event_url;g"
}


if [ $# -lt 1 ]; then
  usage
  exit 1
fi

instance=
sgapikey=
apikey_param="smtp.api.key"
use_obo=0
subusername=
subuserpass=
format=json
cmd=
limit=100
postproc=
verbose=0
curl_args="--insecure --fail --silent --show-error"
method="GET"
content_type="application/json"
# For POST, PUT, and PATCH requests, $json will contain the request body
json=
# For GET and DELETE requests, $params will contain the HTTP query parameters
params=
multicmd=
passthru_args=

while [ $# -gt 0 ]; do
  case "$1" in
    # Authentication
    --api-key|--key|-A) shift; sgapikey="$1" ;;
    --use-master-key|--master|-M) apikey_param="sendgrid.api.key" ;;
    --use-on-behalf-of|--on-behalf-of|-O) use_obo=1 ;;
    --user*|-U) shift; subusername="$1" ;;
    --pass*|-P) shift; subuserpass="$1" ;;
    --event-webhook-url|-E) shift; event_url="$1" ;;
    # API Key Management
    --list-keys|-lk) cmd="api_keys" ;;
    --get-key|-gk) shift; cmd="api_keys/$1" ;;
    --create-key|-ck) method="POST"; cmd="api_keys"; json='{"name":"Bluebird API Key for %SUBUSERNAME%"}' ;;
    --delete-key) shift; method="DELETE"; cmd="api_keys/$1" ;;
    --list-scopes|-ls) cmd="scopes" ;;
    # IP Access Management
    --list-ip-activity|-lia) cmd="access_settings/activity" ;;
    --list-ip-whitelist|-liw) cmd="access_settings/whitelist" ;;
    # Mail Settings and Teammates
    --get-mail-settings|-gms) cmd="mail_settings" ;;
    --get-address-whitelist|-gaw) cmd="mail_settings/address_whitelist" ;;
    --get-footer-settings|-gfs) cmd="mail_settings/footer" ;;
    --get-spam-settings|-gss) cmd="mail_settings/forward_spam" ;;
    --get-template-settings|-gts) cmd="mail_settings/template" ;;
    --get-purge-settings|-gps) cmd="mail_settings/bounce_purge" ;;
    --get-bounce-settings|-gbs) cmd="mail_settings/forward_bounce" ;;
    --list-partner-settings|-lps) cmd="partner_settings" ;;
    --list-teammates|-lt) cmd="teammates" ;;
    --list-pending-teammates|-lpt) cmd="teammates/pending" ;;
    # Alerts
    --get-usage-alerts|-gua) cmd="alerts" ;;
    # Users API
    --get-profile|-gp) cmd="user/profile" ;;
    --get-account|-ga) cmd="user/account" ;;
    --get-email|-ge) cmd="user/email" ;;
    --set-email|-se) shift; method="PUT"; cmd="user/email"; params="$params&email=$1" ;;
    --get-username|-gu) cmd="user/username" ;;
    --get-credits|-gc) cmd="user/credits" ;;
    --set-password|-sp) method="PUT"; cmd="user/password"; params="$params&new_password=%SUBUSERPASS%" ;;
    # Subusers API
    --list-all-subusers|-las) cmd="subusers" ;;
    --get-subuser|-gsu) cmd="subusers"; params="$params&username=%SUBUSERNAME%" ;;
    --get-subuser-monitor|-gsum) cmd="subusers/%SUBUSERNAME%/monitor" ;;
    --get-subuser-reputations|-gsur) cmd="subusers/reputations"; params="$params&usernames=%SUBUSERNAME%" ;;
    --get-subuser-stats|-gsus) shift; cmd="subusers/stats/monthly"; params="$params&date=$1&limit=$limit" ;;
    --get-subuser-totals|-gsut) shift; cmd="subusers/stats/sums"; params="$params&start_date=$1&limit=$limit" ;;
    # Deliverability
    --get-branded-links|-gbl) cmd="whitelabel/links" ;;
    --get-default-branded-link|-gdbl) cmd="whitelabel/links/default" ;;
    --get-subuser-branded-links|-gsbl) cmd="whitelabel/links/subuser"; params="$params&username=%SUBUSERNAME%" ;;
    --get-warmup-ips|-gwi) cmd="ips/warmup" ;;
    --get-reverse-dns|-grd) cmd="whitelabel/ips" ;;
    --validate-email-address|-vea) shift; method="POST"; apikey_param="sendgrid.validation.api.key"; cmd="validations/email"; json="{\"email\":\"$1\"}" ;;
    --list-ip-pools|-lip) cmd="ips/pools" ;;
    --get-ip-pool|-gip) shift; cmd="ips/pools/$1" ;;
    --list-ips|-li) cmd="ips" ;;
    --get-ip-count|-gic) cmd="ips/remaining" ;;
    --list-assigned-ips|-lai) cmd="ips/assigned" ;;
    --get-pools-for-ip|-gpfi) shift; cmd="ips/$1" ;;
    --list-authenticated-domains|-lad) cmd="whitelabel/domains" ;;
    --get-subuser-domains|-gsud) cmd="whitelabel/domains/subuser"; params="$params&username=%SUBUSERNAME%" ;;
    --get-default-auth-domain|-gdad) cmd="whitelabel/domains/default" ;;
    --list-all-verified-senders|-lavs) cmd="verified_senders" ;;
    --get-verified-sender-domains|-gvsd) cmd="verified_senders/domains" ;;
    --get-verified-sender-status|-gvss) cmd="verified_senders/steps_completed" ;;
    # Event Tracking
    --get-event-webhook-settings|-gews) cmd="user/webhooks/event/settings" ;;
    --enable-event-webhook|-eew) method="PATCH"; cmd="user/webhooks/event/settings"; json='{"enabled":true,"url":"%EVENTURL%","bounce":true,"click":true,"deferred":true,"delivered":true,"dropped":true,"open":true,"processed":true,"spam_report":true,"unsubscribe":true}' ;;
    --disable-event-webhook|-dew) method="PATCH"; cmd="user/webhooks/event/settings"; json='{"enabled":false,"url":null,"bounce":false,"click":false,"deferred":false,"delivered":false,"dropped":false,"group_resubscribe":false,"group_unsubscribe":false,"open":false,"processed":false,"spam_report":false,"unsubscribe":false}' ;;
    --set-event-webhook-url|-sewu) method="PATCH"; cmd="user/webhooks/event/settings"; json='{"url":"%EVENTURL%"}' ;;
    --get-event-webhook-key|-gewk) cmd="user/webhooks/event/settings/signed" ;;
    --get-parse-webhook-settings|-gpws) cmd="user/webhooks/parse/settings" ;;
    --get-parse-webhook-stats|-gpwt) shift; cmd="user/webhooks/parse/stats"; params="$params&start_date=$1&limit=$limit" ;;
    --list-tracking-settings|-lts) cmd="tracking_settings" ;;
    --get-click-tracking-setting|-gcts) cmd="tracking_settings/click" ;;
    --enable-click-tracking|-ect) method="PATCH"; cmd="tracking_settings/click"; json='{"enabled":true}' ;;
    --disable-click-tracking|-dct) method="PATCH"; cmd="tracking_settings/click"; json='{"enabled":false}' ;;
    --get-google-tracking-setting|-ggts) cmd="tracking_settings/google_analytics" ;;
    --enable-google-tracking|-egt) method="PATCH"; cmd="tracking_settings/google_analytics"; json='{"enabled":true}' ;;
    --disable-google-tracking|-dgt) method="PATCH"; cmd="tracking_settings/google_analytics"; json='{"enabled":false}' ;;
    --get-open-tracking-setting|-gots) cmd="tracking_settings/open" ;;
    --enable-open-tracking|-eot) method="PATCH"; cmd="tracking_settings/open"; json='{"enabled":true}' ;;
    --disable-open-tracking|-dot) method="PATCH"; cmd="tracking_settings/open"; json='{"enabled":false}' ;;
    --get-subscription-tracking-setting|-gsts) cmd="tracking_settings/subscription" ;;
    --enable-subscription-tracking|-est) method="PATCH"; cmd="tracking_settings/subscription"; json='{"enabled":true,"html_content":"<p style=\"text-align:center;font-size:10px;\">If you would like to stop receiving emails from your senator, <% click here %>.</p>","plain_content":"If you would like to stop receiving emails from your senator, click here: <% %>."}' ;;
    --disable-subscription-tracking|-dst) method="PATCH"; cmd="tracking_settings/subscription"; json='{"enabled":false}' ;;
    # Stats
    --get-global-stats|-ggs) shift; cmd="/stats"; params="$params&start_date=$1&limit=$limit" ;;
    --get-stats-by-geo|-gsbg) shift; cmd="/geo/stats"; params="$params&start_date=$1&limit=$limit" ;;
    --get-stats-by-dev*|-gsbd) shift; cmd="/devices/stats"; params="$params&start_date=$1&limit=$limit" ;;
    --get-stats-by-client|-gsbc) shift; cmd="/clients/stats"; params="$params&start_date=$1&limit=$limit" ;;
    --get-stats-by-browser|-gsbb) shift; cmd="/browsers/stats"; params="$params&start_date=$1&limit=$limit" ;;
    # Suppression Management
    --list-bounces*|-lb) shift; cmd="/suppression/bounces"; params="$params&start_time=$1" ;;
    --get-bounce|-gb) shift; cmd="/suppression/bounces/$1" ;;
    --delete-bounce) shift; method="DELETE"; cmd="/suppression/bounces/$1" ;;
    --list-blocks|-lbk) shift; cmd="/suppression/blocks"; params="$params&start_time=$1" ;;
    --get-block|-gbk) shift; cmd="/suppression/blocks/$1" ;;
    --delete-block) shift; method="DELETE"; cmd="/suppression/blocks/$1" ;;
    --list-spam-reports|-lsr) shift; cmd="/suppression/spam_reports"; params="$params&start_time=$1" ;;
    --get-spam-report|-gsr) shift; cmd="/suppression/spam_reports/$1" ;;
    --delete-spam-report) shift; method="DELETE"; cmd="/suppression/spam_reports/$1" ;;
    --list-invalid-emails|-lie) shift; cmd="/suppression/invalid_emails"; params="$params&start_time=$1" ;;
    --get-invalid-email|-gie) shift; cmd="/suppression/invalid_emails/$1" ;;
    --delete-invalid-email) shift; method="DELETE"; cmd="/suppression/invalid_emails/$1" ;;
    --list-unsubscribes|-lus) shift; cmd="/suppression/unsubscribes"; params="$params&start_time=$1" ;;
    --get-unsubscribe|-gus) shift; cmd="/asm/suppressions/global/$1" ;;
    --delete-unsubscribe) shift; method="DELETE"; cmd="/asm/suppressions/global/$1" ;;
    --get-all-suppressions|-gas) cmd="/asm/suppressions" ;;
    --get-suppressions-using-group|-gsug) shift; cmd="/asm/groups/$1/suppressions" ;;
    --get-suppressed-groups|-gsg) shift; cmd="/asm/suppressions/$1" ;;
    # Other options
    --bluebird-setup|-bs) multicmd=bluebird_setup ;;

    --cmd|-c) shift; cmd="$1" ;;
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
[ "$sgapikey" ] || sgapikey=`$readConfig --ig $instance $apikey_param`
# Read the subuser credentials for the current instance from the BB config
[ "$subusername" ] || subusername=`$readConfig --ig $instance smtp.username`
[ "$subuserpass" ] || subuserpass=`$readConfig --ig $instance smtp.password`
# Read the Event Notify URL from the BB config
[ "$event_url" ] || event_url=`$readConfig --ig $instance sendgrid.event_webhook.url`

if [ ! "$sgapikey" ]; then
  echo "$prog: SendGrid API key must be set in config file" >&2
  exit 1
elif [ ! "$subusername" ]; then
  echo "$prog: SendGrid subusername must be set either via -U or smtp.username" >&2
  exit 1
elif [ ! "$event_url" ]; then
  echo "$prog: SendGrid Event Webhook URL must be set in config file" >&2
  exit 1
fi


# Handle multi-part commands first.

if [ "$multicmd" ]; then
  case "$multicmd" in
    bluebird_setup)
      $0 $passthru_args --enable-event-webhook $instance
      $0 $passthru_args --enable-subscription-tracking $instance
      $0 $passthru_args --enable-click-tracking $instance
      $0 $passthru_args --enable-open-tracking $instance
      ;;
    *) echo "$prog: $multicmd: Unknown multi-command" >&2; rc=1 ;;
  esac
  exit $rc
elif [ ! "$cmd" ]; then
  echo "$prog: No command was specified" >&2
  exit 1
fi


# Replace macros in the command, the query params, and the JSON request body.
cmd=`echo $cmd | replace_macros`
json=`echo $json | replace_macros`
params=`echo $params | sed -e 's;^\&;;' | replace_macros`

api_url="$apiUrlBase/$cmd"

[ "$json" ] && curl_args="$curl_args --data '$json'"
[ "$params" ] && api_url="$api_url?$params"

[ $verbose -eq 1 ] && echo "About to perform HTTP $method to URL [$api_url] using API key [$sgapikey]" >&2 && set -x

obo_arg=
[ $use_obo -eq 1 ] && obo_arg="-H 'On-Behalf-Of: $subusername'"

result=`eval curl -X "$method" "$curl_args" -H \"Authorization: Bearer $sgapikey\" -H \"Content-Type: $content_type\" "$obo_arg" "$api_url"`
rc=$?

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
