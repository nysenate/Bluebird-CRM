#!/bin/sh
#
# fixPrefixes.sh - For records without a prefix, set prefix according to gender.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-12-15
# Revised: 2011-12-15
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
recache=$script_dir/rebuildCachedValues.sh
greetings="$script_dir/../civicrm/scripts/updateAllGreetings.php"
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

selcnt="select count(*) from civicrm_contact"
rec_count=`$execSql $instance -c "$selcnt;"`
cond_noprefix="prefix_id is null"
cond_female="gender_id=1"
cond_male="gender_id=2"
sql="$selcnt where $cond_noprefix"
null_count=`$execSql $instance -c "$sql;"`
female_count=`$execSql $instance -c "$sql and $cond_female;"`
male_count=`$execSql $instance -c "$sql and $cond_male;"`
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

upd_female="prefix_id=2"
upd_male="prefix_id=3"
upd_cached="display_name=null,email_greeting_id=null,email_greeting_custom=null,email_greeting_display=null,postal_greeting_id=null,postal_greeting_custom=null,postal_greeting_display=null,addressee_id=null,addressee_custom=null,addressee_display=null"
sql="update civicrm_contact set $upd_female, $upd_cached where $cond_noprefix and $cond_female; update civicrm_contact set $upd_male, $upd_cached where $cond_noprefix and $cond_male;"

$execSql $instance -c "$sql"
$recache --field-displayname --ok $instance
php $greetings -S $instance 


exit 0
