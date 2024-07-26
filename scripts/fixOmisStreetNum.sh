#!/bin/sh
#
# fixOmisStreetNum.sh - Use OMIS saved data to split HOUSE field into
#                       street_number and street_number_suffix
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2011-08-26
# Revised: 2011-08-26
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
force_ok=0
dry_run=0
verbose=0

usage() {
  echo "Usage: $prog [--dry-run] [--verbose] [--ok] instanceName" >&2
}

deprecated() {
  echo "This script has been deprecated due to it's dependency on the mysqludf_preg library." >&2
  echo "Should you ever need to run it again, first rewrite all calls to preg_capture() with" >&2
  echo "mysql built-in functions" >&2
}

deprecated;
exit 1

if [ $# -lt 1 ]; then
  usage
  exit 1
fi

while [ $# -gt 0 ]; do
  case "$1" in
    --ok) force_ok=1 ;;
    -n|--dry-run) dry_run=1 ;;
    -v|--verbose) verbose=1 ;;
    -*) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
    *) instance="$1" ;;
  esac
  shift
done

if ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi


cond="c.id=a.contact_id and c.id=n.entity_id
and a.location_type_id!=6 and a.location_type_id!=13
and c.external_identifier like 'omis%'
and n.subject='OMIS DATA' and n.note not rlike 'HOUSE: ([0-9]+)?\n'"

tabs="civicrm_address a, civicrm_contact c, civicrm_note n"

##
# preg_capture along with the mysqludf_preg library has been deprecated (or removed).
# If you ever need to use this script again, then this code
# will need to be rewritten using built-in mysql functions
##
preg_house="preg_capture('/HOUSE: ([^\n]*)/', n.note, 1)"
preg_num="preg_capture('/HOUSE: ([0-9]+)/', n.note, 1)"
preg_suffix="preg_capture('/HOUSE: [0-9]*([^\n]*)/', n.note, 1)"
setv="a.street_number=$preg_num, a.street_number_suffix=$preg_suffix"

cnt1=`$execSql -q "$instance" -c "select count(*) from civicrm_address"`
cnt2=`$execSql -q "$instance" -c "select count(*) from $tabs where $cond"`

echo "Total address records: $cnt1" >&2
echo "Total address records to be updated: $cnt2" >&2

if [ $verbose -eq 1 ]; then
  $execSql -q "$instance" -c "
    select a.street_number, a.street_number_suffix,
      $preg_house, $preg_num, $preg_suffix
    from $tabs
    where $cond"
fi

if [ $cnt2 -gt 0 -a $dry_run -eq 0 ]; then
  if [ $force_ok -eq 0 ]; then
    echo -n "Proceed with clean-up operation (N/y)? "
    read ch
    case "$ch" in
      [yY]*) ;;
      *) echo "Aborting."; exit 0 ;;
    esac
  fi

  echo "Updating $cnt2 address records with new street_number[_suffix]" >&2
  $execSql "$instance" -c "update $tabs set $setv where $cond"
fi

exit 0
