#!/bin/sh
#
# fixEntityTags.sh - Analyze the civicrm_entity_tag table and fix problems.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2011-04-02
# Revised: 2011-04-03
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
force_ok=0
notag_only=0
dups_only=0

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--ok] instanceName" >&2
}


if [ $# -lt 1 ]; then
  usage
  exit 1
fi

notag_only=0;
dups_only=0;
nocontact_only=0;

while [ $# -gt 0 ]; do
  case "$1" in
    --ok) force_ok=1 ;;
    --notag-only) notag_only=1 ;;
    --dups-only) dups_only=1 ;;
    --nocontact-only) nocontact_only=1 ;;
    -*) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
    *) instance="$1" ;;
  esac
  shift
done

if ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

sql="select count(*) from civicrm_entity_tag"
total_count=`$execSql -q $instance -c "$sql;"`
echo "Total entity-tag mappings: $total_count"
# Don't check for foreign key mappings unless we successfully clean up
# any entity-tag mappings that reference non-existent tags.
fk_chk=0

if [ $dups_only -ne 1 ] && [ $nocontact_only -ne 1 ]
then
  echo "Checking for entity-tag mappings to non-existent tags..."
  sql="select count(*) from civicrm_entity_tag where tag_id not in ( select id from civicrm_tag )"
  notag_count=`$execSql -q $instance -c "$sql;"`
  echo "Number of mappings to non-existent tags: $notag_count"

  if [ $notag_count -gt 0 ]; then
    if [ $force_ok -eq 0 ]; then
      echo -n "Are you sure that you wish to proceed ([N]/y)? "
      read ch
      case "$ch" in
        [yY]*) ;;
        *) echo "Aborted."; exit 0 ;;
      esac
    fi

    echo "Deleting all mappings to non-existent tags..."
    sql="delete from civicrm_entity_tag where tag_id not in ( select id from civicrm_tag )"
    $execSql $instance -c "$sql;"

    if [ $? -eq 0 ]; then
      echo "Successfully deleted mappings to non-existent tags"
      fk_chk=1
    else
      echo "$prog: Error deleting mappings to non-existent tags" >&2
      exit 1
    fi
  fi
fi

sql="select count(*) from civicrm_entity_tag"
total_count=`$execSql -q $instance -c "$sql;"`
echo "Now, total entity-tag mappings: $total_count"


if [ $notag_only -ne 1 -a $nocontact_only -ne 1 ]; then
  echo "Checking for duplicate entity-tag mappings..."
  sql="create table civicrm_entity_tag2 as select * from civicrm_entity_tag group by entity_table,entity_id,tag_id"
  $execSql $instance -c "$sql;"

  sql="select count(*) from civicrm_entity_tag2"
  new_total_count=`$execSql -q $instance -c "$sql;"`
  dup_count=`expr $total_count - $new_total_count`

  echo "Number of duplicate mappings: $dup_count"

  if [ $dup_count -gt 0 ]; then
    if [ $force_ok -eq 0 ]; then
      echo -n "Are you sure that you wish to proceed ([N]/y)? "
      read ch
      case "$ch" in
        [yY]*) ;;
        *) echo "Aborted."
           $execSql -q $instance -c "drop table civicrm_entity_tag2;"
           exit 0
           ;;
      esac
    fi

    echo "Deleting all duplicate entity tag records..."

    sql="truncate table civicrm_entity_tag; set foreign_key_checks=$fk_chk; insert into civicrm_entity_tag select * from civicrm_entity_tag2; drop table civicrm_entity_tag2"
    $execSql $instance -c "$sql;"

    if [ $? -eq 0 ]; then
      echo "Successfully completed entity tag cleanup for instance [$instance]"
    else
      echo "$prog: Error deleting duplicate entity-tag mappings" >&2
      exit 1
    fi
  else
    echo "There are no duplicates to delete."
    $execSql -q $instance -c "drop table civicrm_entity_tag2;"
  fi
fi

sql="select count(*) from civicrm_entity_tag"
total_count=`$execSql -q $instance -c "$sql;"`
echo "Now, total entity-tag mappings: $total_count"


if [ $dups_only -ne 1 -a $notag_only -ne 1 ]; then
  echo "Checking for entity-tag mappings to non-existent contacts..."
  sql="SELECT COUNT(*)
    FROM civicrm_entity_tag
    LEFT JOIN civicrm_contact
      ON civicrm_entity_tag.entity_id = civicrm_contact.id
    WHERE entity_table = 'civicrm_contact'
      AND civicrm_contact.id IS NULL"
  nocontact_count=`$execSql -q $instance -c "$sql;"`
  echo "Number of mappings to non-existent contacts: $nocontact_count"

  if [ $nocontact_count -gt 0 ]; then
    if [ $force_ok -eq 0 ]; then
      echo -n "Are you sure that you wish to proceed ([N]/y)? "
      read ch
      case "$ch" in
        [yY]*) ;;
        *) echo "Aborted."; exit 0 ;;
      esac
    fi

    echo "Deleting all mappings to non-existent contacts..."
    sql="DELETE civicrm_entity_tag
      FROM civicrm_entity_tag
      LEFT JOIN civicrm_contact
        ON civicrm_entity_tag.entity_id = civicrm_contact.id
      WHERE entity_table = 'civicrm_contact'
        AND civicrm_contact.id IS NULL"
    $execSql $instance -c "$sql;"

    if [ $? -eq 0 ]; then
      echo "Successfully deleted mappings to non-existent contacts"
      fk_chk=1
    else
      echo "$prog: Error deleting mappings to non-existent contacts" >&2
      exit 1
    fi
  fi
fi

sql="select count(*) from civicrm_entity_tag"
total_count=`$execSql -q $instance -c "$sql;"`
echo "Finally, total entity-tag mappings: $total_count"
exit 0
