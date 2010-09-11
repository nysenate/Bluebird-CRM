#!/bin/sh
#
# dumpInstance.sh
#

prog=`basename $0`
script_dir=`dirname $0`
readConfig=$script_dir/readConfig.sh

if [ $# -ne 1 ]; then
  echo "Usage: $prog instanceName" >&2
  exit 1
fi

instance="$1"
dbhost=`$readConfig --group global:db --key host`
dbuser=`$readConfig --group global:db --key user`
dbpass=`$readConfig --group global:db --key pass`

echo "Dumping Drupal database for instance [$instance]"
set -x
mysqldump -h $dbhost -u $dbuser -p$dbpass senate_d_$instance > senate_d_$instance.sql
set +x


echo "Dumping Drupal database for instance [$instance]"
set -x
mysqldump -h $dbhost -u $dbuser -p$dbpass senate_c_$instance > senate_c_$instance.sql

exit 0
