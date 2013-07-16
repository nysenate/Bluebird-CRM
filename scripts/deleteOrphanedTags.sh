#!/bin/sh
#
# deleteOrphanedTags.sh - Delete Bluebird tags that are not attached to any
#                         entities (contacts, cases, or activities).
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2011-02-25
# Revised: 2011-02-25
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
CATEGORY_PARENT_ID=291
KEYWORD_PARENT_ID=296
POSITION_PARENT_ID=292

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--ok] [--keywords] [--positions] instanceName" >&2
}

force_ok=0
del_keywords=0
del_positions=0
instance=

while [ $# -gt 0 ]; do
  case "$1" in
    --ok) force_ok=1 ;;
    --keywords|-k) del_keywords=1 ;;
    --positions|-p) del_positions=1 ;;
    -*) echo "$prog: $1: Invalid option" >&2 ; usage ; exit 1 ;;
    *) instance="$1" ;;
  esac
  shift
done

if [ ! "$instance" ]; then
  echo "$prog: Must specify an instance to search" >&2
  usage
  exit 1
fi

parent_ids=

[ $del_keywords -eq 1 ] && parent_ids="$parent_ids,$KEYWORD_PARENT_ID"
[ $del_positions -eq 1 ] && parent_ids="$parent_ids,$POSITION_PARENT_ID"

if [ ! "$parent_ids" ]; then
  echo "$prog: Must specify --keywords, --positions, or both." >&2
  usage
  exit 1
fi

parent_ids=`echo $parent_ids | sed 's;^,;;'`
sql_body="from civicrm_tag where parent_id in ( $parent_ids ) and id not in ( select tag_id from civicrm_entity_tag )"
tag_count=`$execSql $instance -c "select count(*) $sql_body"`

[ $? -eq 0 ] || exit 1

echo

if [ $tag_count -eq 0 ]; then
  echo "There are no orphaned tags to be deleted."
  exit 0
fi

if [ $force_ok -eq 0 ]; then
  echo "There are $tag_count tags that will be deleted."
  echo
  while : ; do
    echo -n "Do you want to [V]iew the tags, [D]elete them, or [C]ancel? "
    read ch
    case "$ch" in
      [dD]*) break ;;
      [vV]*) $execSql $instance -c "select id,name,description $sql_body" ;;
      *) echo "Aborting."; exit 0 ;;
    esac
  done
fi

echo "Deleting $tag_count orphaned tags of type(s) [$parent_ids] in instance [$instance]"
( set -x
  $execSql $instance -c "delete $sql_body"
)

exit $?
