#!/bin/sh
#
# shadowTable.sh
#
# Project: BluebirdCRM
# Author: Graylin Kim
# Organization: New York State Senate
# Date: 2011-09-16
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh

if [ $# -ne 1 ]; then
  echo "Usage: $prog instanceName" >&2
  exit 1
fi

instance="$1"

if ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

## Add/Drop the tables, functions, and triggers
$execSql $instance -f $script_dir/../modules/nyss_dedupe/shadow.sql

## Force an update on all the relevant fields
## This is a bit of a hack right now banking on us not having other contact types
## in the system and never using the name portion of the civicrm_address. In the
## future we might want to take a better approach and be more change proof.
$execSql $instance -c "
    UPDATE civicrm_contact SET contact_type='Individual' WHERE contact_type='Individual';
    UPDATE civicrm_contact SET contact_type='Organization' WHERE contact_type='Organization';
    UPDATE civicrm_contact SET contact_type='Household' WHERE contact_type='Household';
    UPDATE civicrm_address SET name=NULL WHERE name IS NULL;
"
