#!/bin/sh
#
# dumpInstance.sh - Perform a MySQL dump for a CRM instance
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-12
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh

if [ $# -ne 1 ]; then
  echo "Usage: $prog instanceName" >&2
  exit 1
fi

instance="$1"

echo "Dumping Drupal database for instance [$instance]"
set -x
$execSql --dump senate_d_$instance > senate_d_$instance.sql
set +x

echo "Dumping CiviCRM database for instance [$instance]"
set -x
$execSql --dump senate_c_$instance > senate_c_$instance.sql

exit 0
