#!/bin/sh
#
# fixSpaces.sh - Remove extraneous spaces from various databases fields.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2011-04-09
# Revised: 2011-04-10
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
force_ok=0

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog instanceName" >&2
}


if [ $# -lt 1 ]; then
  usage
  exit 1
fi

while [ $# -gt 0 ]; do
  case "$1" in
    --ok) force_ok=1 ;;
    -*) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
    *) instance="$1" ;;
  esac
  shift
done

if ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

echo "==> Examining CRM instance [$instance]" >&2

echo "Checking for leading and trailing spaces in address fields" >&2
where="street_address like ' %' or street_address like '% ' or street_number like ' %' or street_number like '% ' or street_name like ' %' or street_name like '% ' or street_unit like ' %' or street_unit like '% ' or supplemental_address_1 like ' %' or supplemental_address_1 like '% ' or supplemental_address_2 like ' %' or supplemental_address_2 like '% ' or city like ' %' or city like '% '"
sql="select count(*) from civicrm_address where $where"

cnt=`$execSql -q -i $instance -c "$sql;"`
echo "Records to be trimmed: $cnt"

if [ $cnt -gt 0 ]; then
  if [ $force_ok -eq 0 ]; then
    echo -n "Proceed with trim operation (N/y)? "
    read ch
    case "$ch" in
      [yY]*) ;;
      *) echo "Aborting."; exit 0 ;;
    esac
  fi

  sql="update civicrm_address set street_address=trim(street_address), street_number=trim(street_number), street_name=trim(street_name), street_unit=trim(street_unit), supplemental_address_1=trim(supplemental_address_1), supplemental_address_2=trim(supplemental_address_2), city=trim(city) where $where"
  $execSql -i $instance -c "$sql;"
fi

echo "Checking for extra space within address fields" >&2
where="street_address like '%  %' or street_number like '%  %' or street_name like '%  %' or street_unit like '%  %' or supplemental_address_1 like '%  %' or supplemental_address_2 like '%  %' or city like '%  %'"
sql="select count(*) from civicrm_address where $where"

cnt=`$execSql -q -i $instance -c "$sql;"`
echo "Records to be compressed: $cnt"

if [ $cnt -gt 0 ]; then
  if [ $force_ok -eq 0 ]; then
    echo -n "Proceed with space compression operation (N/y)? "
    read ch
    case "$ch" in
      [yY]*) ;;
      *) echo "Aborting."; exit 0 ;;
    esac
  fi

  while [ $cnt -gt 0 ]; do
    sql="update civicrm_address set street_address=replace(street_address,'  ',' '), street_number=replace(street_number,'  ',' '), street_name=replace(street_name,'  ',' '), street_unit=replace(street_unit,'  ',' '), supplemental_address_1=replace(supplemental_address_1,'  ',' '), supplemental_address_2=replace(supplemental_address_2,'  ',' '), city=replace(city,'  ',' ') where $where"
    $execSql -i $instance -c "$sql;"

    sql="select count(*) from civicrm_address where $where"
    cnt=`$execSql -q -i $instance -c "$sql;"`
    echo "Records remaining to be compressed: $cnt"
  done
fi

exit 0
