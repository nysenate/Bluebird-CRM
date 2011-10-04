#!/bin/sh
#
# v121_bithdayDNM.sh
#
# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2011-04-20
#
# set any contacts with a birthday before 1901 to do not mail
# #3661
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
drush=$script_dir/drush.sh

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

###### Begin Update Scripts ######

## set do not mail if birthdate < 1901 ##
birthdayDNM="UPDATE civicrm_contact SET do_not_mail = 1 WHERE birth_date < '1901-01-01';"
$execSql -i $instance -c "$birthdayDNM"
