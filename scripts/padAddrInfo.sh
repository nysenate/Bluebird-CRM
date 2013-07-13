#!/bin/sh
#
# padAddrInfo.sh - Left-pad ZIP+4, School District, and County with zeroes
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2011-10-18
# Revised: 2011-10-18
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
rebuildCache=$script_dir/rebuildCachedValues.sh
force_ok=0
dry_run=0

LEN_ZIP4=4
LEN_COUNTY=2
LEN_SDIST=3

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--dry-run] [--ok] instanceName" >&2
}


if [ $# -lt 1 ]; then
  usage
  exit 1
fi

while [ $# -gt 0 ]; do
  case "$1" in
    --ok) force_ok=1 ;;
    -n|--dry-run) dry_run=1 ;;
    -*) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
    *) instance="$1" ;;
  esac
  shift
done

if ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

echo "==> Processing CRM instance [$instance]" >&2

selacnt="select count(*) from civicrm_address"
condzip4="postal_code_suffix<>'' and length(postal_code_suffix)<$LEN_ZIP4"
seldcnt="select count(*) from civicrm_value_district_information_7"
condcnty="county_50<>'' and length(county_50)<$LEN_COUNTY"
condschd="school_district_54<>'' and length(school_district_54)<$LEN_SDIST"

echo "Counting addresses with incomplete ZIP+4" >&2
sql="$selacnt where $condzip4;"
cnt1=`$execSql -q $instance -c "$sql"`
echo "Counting district info with incomplete county" >&2
sql="$seldcnt where $condcnty;"
cnt2=`$execSql -q $instance -c "$sql"`
echo "Counting district info with incomplete school district" >&2
sql="$seldcnt where $condschd;"
cnt3=`$execSql -q $instance -c "$sql"`

echo "Address records with short ZIP+4: $cnt1" >&2
echo "District records with short county: $cnt2" >&2
echo "District records with short school district: $cnt3" >&2

if [ $cnt1 -gt 0 ]; then
  if [ $dry_run -eq 0 ]; then
    do_cleanup=1
    if [ $force_ok -eq 0 ]; then
      echo -n "Proceed with ZIP+4 clean-up operation (N/y)? "
      read ch
      case "$ch" in
        [yY]*) ;;
        *) do_cleanup=0 ;;
      esac
    fi

    if [ $do_cleanup -eq 1 ]; then
      echo "Left-padding ZIP+4 fields with zeroes" >&2
      sql="update civicrm_address
           set postal_code_suffix=lpad(postal_code_suffix,$LEN_ZIP4,'0')
           where $condzip4;"
      $execSql -q $instance -c "$sql" || exit 1
      $rebuildCache --ok --field-streetaddress $instance
    else
      echo "Skipping update for $cnt1 addresses" >&2
    fi
  fi
fi

if [ $cnt2 -gt 0 -o $cnt3 -gt 0 ]; then
  if [ $dry_run -eq 0 ]; then
    do_cleanup=1
    if [ $force_ok -eq 0 ]; then
      echo -n "Proceed with districtInfo clean-up operation (N/y)? "
      read ch
      case "$ch" in
        [yY]*) ;;
        *) do_cleanup=0 ;;
      esac
    fi

    if [ $do_cleanup -eq 1 ]; then
      echo "Left-padding county and school district fields with zeroes" >&2
      sql="update civicrm_value_district_information_7
           set county_50=lpad(county_50,$LEN_COUNTY,'0'),
               school_district_54=lpad(school_district_54,$LEN_SDIST,'0')
           where ($condcnty) or ($condschd);"
      $execSql -q $instance -c "$sql" || exit 1
    else
      echo "Skipping update for district information" >&2
    fi
  fi
fi

exit 0
