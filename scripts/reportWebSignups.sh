#!/bin/sh
#
# reportWebSignups.sh - Generate reports (typically scheduled weekly) for
#                       nysenate.gov web signups and send to Senators.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2012-03-14
# Revised: 2012-03-14
#

prog=`basename $0`
script_dir=`dirname $0`
base_dir=`cd $script_dir/..; echo $PWD`
signup_dir="$base_dir/civicrm/scripts/signup_reports"
readConfig=$script_dir/readConfig.sh

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--help|-h] [--verbose|-v] [--dry-run|-n]" >&2
}

logmsg() {
  

verbose=0
passthru_args=
tdate=`date +%Y%m%d`

if [ $# -lt 1 ]; then
  usage
  exit 1
fi

while [ $# -gt 0 ]; do
  case "$1" in
    --help|-h) usage; exit 0 ;;
    --verbose|-v) verbose=1 ;;
    --dry*|-n) passthru_args="$passthru_args --dryrun" ;;
    *) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
  esac
  shift
done

logdt "Retrieving signups from NYSenate.gov website"
php $signup_dir/ingest.php $passthru_args --signups
logdt "Geocoding signup records to determine Senate Districts"
php $signup_dir/ingest.php $passthru_args --geocode

logdt "Generating reports for all senators in the 'signups' instance-set"
$script_dir/iterateInstances.sh --signups "php $signup_dir/generate.php $passthru_args -S{} -d$tdate"

logdt "E-mailing reports to all senators in the 'signups' instance-set"
$script_dir/iterateInstances.sh --signups "php $signup_dir/email.php -S{} -d$tdate"

