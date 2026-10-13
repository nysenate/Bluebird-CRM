#!/bin/bash
#
# cv.sh - A Bluebird-aware wrapper around the CiviCRM "cv" utility.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2025-06-04
# Revised: 2025-06-04
# Last revised: 2026-04-13 - Ensure 
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

if ! $readConfig --instance $instance --quiet; then
  echo "$prog: Instance [$instance] not found in config" >&2
  exit 1
fi

drupal_rootdir=`$readConfig --ig $instance drupal.rootdir` || drupal_rootdir="$DEFAULT_DRUPAL_ROOTDIR"
base_domain=`$readConfig --ig $instance base.domain`
data_owner=`$readConfig --ig $instance data.rootdir.owner | cut -d: -f1` || data_owner="apache"

# Force this script to run as the apache user, rather than root.
# This prevents root-owned cache files from being created in the
# templates_c/ directory.

if [ $EUID -eq 0 ]; then
  echo "$prog: Running as root causes file permission problems; restarting as user $data_owner"
  exec su $data_owner -s /bin/bash -c "$0 $*"
fi

# cv tries to store state (e.g. "upgrade:db" progress) in $HOME/.cv.
# So, the current user must be able to write to their $HOME dir.
# If the user can't write to $HOME, then XDG_STATE_HOME can be set as a fallback.

if [ ! -w "$HOME" ]; then
  data_rootdir=`$readConfig --ig $instance data.rootdir` || data_rootdir="$DEFAULT_DATA_ROOTDIR"
  state_home_dir="$data_rootdir/tmp/cv_state"
  echo "$prog: Unable to write to $HOME. Using fallback cv state directory: $state_home_dir" >&2
  if ! mkdir -p "$state_home_dir"; then
    echo "$prog: $state_home_dir: Unable to create cv state directory; aborting" >&2
    exit 1
  fi
  export XDG_STATE_HOME="$state_home_dir"
fi

if [ "$base_domain" ]; then
  full_uri="http://$instance.$base_domain/"
else
  full_uri="http://$instance/"
fi

shift
CIVICRM_BOOT="Drupal:$drupal_rootdir" cv --url="$full_uri" "$@"

exit $?
