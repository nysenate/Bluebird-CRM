#!/bin/sh
#
# fixPrefixes.sh - For records without a prefix, set prefix according to gender.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-12-15
# Revised: 2010-12-28
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
app_rootdir=`$readConfig --global app.rootdir` || app_rootdir="$DEFAULT_APP_ROOTDIR"
force_ok=0

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--ok] instanceName" >&2
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

# prefix_id values:
#   2 = "Ms."
#   3 = "Mr."
# gender_id values:
#   1 = female
#   2 = male

sql="select count(*) from civicrm_contact"
rec_count=`$execSql -i $instance -c "$sql;"`
sql="$sql where prefix_id is null"
null_count=`$execSql -i $instance -c "$sql;"`
female_count=`$execSql -i $instance -c "$sql and gender_id=1;"`
male_count=`$execSql -i $instance -c "$sql and gender_id=2;"`
fixable_count=`expr $female_count + $male_count`

echo "Total contact records = $rec_count"
echo "Records with no prefix = $null_count"
echo "Female records with no prefix = $female_count"
echo "Male records with no prefix = $male_count"
echo "Fixable records with no prefix = $fixable_count (female + male)"
echo

if [ $force_ok -eq 0 ]; then
  echo -n "Are you sure that you wish to proceed ([N]/y)? "
  read ch
  case "$ch" in
    [yY]*) ;;
    *) echo "Aborted."; exit 0 ;;
  esac
fi

echo "Setting prefixes for all fixable records without one..."

sql="update civicrm_contact set prefix_id=2 where prefix_id is null and gender_id=1; update civicrm_contact set prefix_id=3 where prefix_id is null and gender_id=2;"

( set -x
  $execSql -i $instance -c "$sql"
)

php $app_rootdir/civicrm/scripts/updateAllGreetings.php -S $instance -f

$script_dir/rebuildCachedValues.sh $instance --field-displayname --rebuild-all --ok

exit 0
