#!/bin/sh
#
# deleteOrphanedTags.sh - Delete Bluebird tags that are not associated with
#                         any entities (contacts, cases, or activities),
#                         and/or delete tag associations that reference
#                         non-existent entities.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2011-02-25
# Revised: 2011-02-25
# Revised: 2014-02-20 - add ability to delete tag associations that reference
#                       non-existent entities; make this the default behavior
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
  echo "Usage: $prog [--ok] [--verbose|-v] [--delete-tags|-D [--keywords|-k] [--positions|-p]] instanceName" >&2
}

force_ok=0
del_tags=0
del_keywords=0
del_positions=0
instance=
quiet_arg=-q

while [ $# -gt 0 ]; do
  case "$1" in
    --ok) force_ok=1 ;;
    --delete-tags|-D) del_tags=1 ;;
    --keywords|-k) del_keywords=1 ;;
    --positions|-p) del_positions=1 ;;
    --verbose|-v) quiet_arg= ;;
    -*) echo "$prog: $1: Invalid option" >&2 ; usage ; exit 1 ;;
    *) instance="$1" ;;
  esac
  shift
done

if [ ! "$instance" ]; then
  echo "$prog: Must specify an instance to search" >&2
  usage
  exit 1
elif ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
elif [ $del_tags -eq 0 -a \( $del_keywords -eq 1 -o $del_positions -eq 1 \) ]; then
  echo "$prog: Cannot specify --keywords or --positions without also specifying --delete-tags" >&2
  usage
  exit 1
elif [ $del_tags -eq 1 -a $del_keywords -eq 0 -a $del_positions -eq 0 ]; then
  echo "$prog: Must specify --keywords, --positions, or both when --delete-tags is set" >&2
  usage
  exit 1
fi

for entity in contact activity case; do
  sql_body="from civicrm_entity_tag where entity_table='civicrm_$entity' and entity_id not in ( select id from civicrm_$entity)"
  rec_count=`$execSql $quiet_arg $instance -c "select count(*) $sql_body"`

  if [ $rec_count -eq 0 ]; then
    echo "There are no orphaned $entity tag associations."
    continue
  fi

  if [ $force_ok -eq 0 ]; then
    echo "There are $rec_count $entity tag associations that will be deleted."
    confirm_yes_no "Are you sure you wish to delete these" || continue
  fi
  echo "Deleting $rec_count $entity tag associations."
  $execSql $quiet_arg $instance -c "delete $sql_body"
done


if [ $del_tags -eq 1 ]; then
  parent_ids=

  [ $del_keywords -eq 1 ] && parent_ids="$parent_ids,$KEYWORD_PARENT_ID"
  [ $del_positions -eq 1 ] && parent_ids="$parent_ids,$POSITION_PARENT_ID"

  parent_ids=`echo $parent_ids | sed 's;^,;;'`
  sql_body="from civicrm_tag where parent_id in ( $parent_ids ) and id not in ( select tag_id from civicrm_entity_tag )"
  tag_count=`$execSql $quiet_arg $instance -c "select count(*) $sql_body"`

  [ $? -eq 0 ] || exit 1

  if [ $tag_count -eq 0 ]; then
    echo "There are no orphaned tags to be deleted."
    exit 0
  fi

  if [ $force_ok -eq 0 ]; then
    echo "There are $tag_count tags that will be deleted."
    echo
    while : ; do
      echo -n "Do you want to [V]iew the tags, [D]elete them, or [C]ancel (C/v/d)? "
      read ch
      case "$ch" in
        [dD]*) break ;;
        [vV]*) $execSql $quiet_arg $instance -c "select id,name,description $sql_body" ;;
        *) echo "Aborting."; exit 0 ;;
      esac
    done
  fi

  echo "Deleting $tag_count orphaned tags of type(s) [$parent_ids] in instance [$instance]"
  $execSql $quiet_arg $instance -c "delete $sql_body"
fi

exit $?
