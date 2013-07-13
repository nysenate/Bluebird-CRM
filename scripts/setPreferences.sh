#!/bin/sh
#
# setPreferences.sh - Set CiviCRM preferences for the administrative user.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2011-04-12
# Revised: 2011-04-13
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh

. $script_dir/defaults.sh

if [ $# -ne 1 ]; then
  echo "Usage: $prog instanceName" >&2
  exit 1
fi

instance="$1"

if ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi


# The default format for contact sort names is:
#   lastname, firstname middlename, suffix
# Example: Zalewski, Ken J., Jr.

sformat='{contact.last_name}{, }{contact.first_name}{ }{contact.middle_name}{, }{contact.individual_suffix}'

# The default format for contact display names is:
#   prefix firstname middlename lastname, suffix
# Example: Mr. Ken J. Zalewski, Jr.

dformat='{contact.individual_prefix}{ }{contact.first_name}{ }{contact.middle_name}{ }{contact.last_name}{, }{contact.individual_suffix}'

$execSql $instance -c "update civicrm_preferences set sort_name_format='$sformat', display_name_format='$dformat' where id=1;"

