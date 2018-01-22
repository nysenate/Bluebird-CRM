#!/bin/sh
#
# fixDupePrimary.sh
# 
# Check if there are multiple addresses/phone/email flagged as primary;
# for address and phone, assume the earlier id (thus earlier record) should be primary;
# for email, assume the latest id (most recent addition) should be primary.
#
# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2011-03-01
# Revised: 2018-01-20
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh

if [ $# -ne 1 ]; then
  echo "Usage: $prog instance" >&2
  exit 1
fi

instance="$1"

. $script_dir/defaults.sh

primaryrecords="
  UPDATE civicrm_address as address
  INNER JOIN (
    SELECT MIN(id) id, contact_id
    FROM civicrm_address
    WHERE is_primary = 1
    GROUP BY contact_id
    HAVING count(id) > 1
  ) as earliest_address
    ON address.contact_id = earliest_address.contact_id
    AND address.id != earliest_address.id
  SET address.is_primary = 0;

  UPDATE civicrm_email as email
  INNER JOIN (
    SELECT MIN(id) id, contact_id
    FROM civicrm_email
    WHERE is_primary = 1
    GROUP BY contact_id
    HAVING count(id) > 1
  ) as earliest_email
    ON email.contact_id = earliest_email.contact_id
    AND email.id != earliest_email.id
  SET email.is_primary = 0;

  UPDATE civicrm_phone as phone
  INNER JOIN (
    SELECT MIN(id) id, contact_id
    FROM civicrm_phone
    WHERE is_primary = 1
    GROUP BY contact_id
    HAVING count(id) > 1
  ) as earliest_phone
    ON phone.contact_id = earliest_phone.contact_id
    AND phone.id != earliest_phone.id
  SET phone.is_primary = 0;"

echo "Fixing duplicate primary records for instance [$instance]"
$execSql $instance -c "$primaryrecords" -q
