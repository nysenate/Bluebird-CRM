#!/bin/sh
#
# cv.sh - A Bluebird-aware wrapper around the CiviCRM "cv" utility.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2025-06-04
# Revised: 2025-06-04
#

prog=`basename $0`
script_dir=`dirname $0`
readConfig=$script_dir/readConfig.sh

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog instanceName cv_command [cv_arg ...]" >&2
}

if [ $# -lt 2 ]; then
  usage
  exit 1
fi

instance="$1"
shift

if ! $readConfig --instance $instance --quiet; then
  echo "$prog: Instance [$instance] not found in config" >&2
  exit 1
fi

drupal_rootdir=`$readConfig --ig $instance drupal.rootdir` || drupal_rootdir="$DEFAULT_DRUPAL_ROOTDIR"
base_domain=`$readConfig --ig $instance base.domain`

if [ "$base_domain" ]; then
  full_uri="http://$instance.$base_domain/"
else
  full_uri="http://$instance/"
fi

CIVICRM_BOOT="Drupal:$drupal_rootdir" cv --url="$full_uri" "$@"

exit $?
