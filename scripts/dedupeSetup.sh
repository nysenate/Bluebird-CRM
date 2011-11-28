#!/bin/sh
#
# dedupeSetup.sh - All-in-one dedupe setup script.
#
# Project: BluebirdCRM
# Author: Graylin Kim
# Organization: New York State Senate
# Date: 2011-11-03
# Revised: 2011-11-28
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
dedupe_dir=$script_dir/../modules/nyss_dedupe

usage () {
  echo "Usage: $prog [--help|-h] [--rebuild-all|-a] [--rebuild-tables|-t] [--rebuild-rule-groups|-r] instance"
}

if [ $# -eq 0 ]; then
  usage
  exit 1
fi

rebuildTables=0
rebuildRuleGroups=0

while [ $# -gt 0 ]; do
  case "$1" in
    -h|--help) usage; exit 0 ;;
    -t|--rebuild-tables) rebuildTables=1 ;;
    -r|--rebuild-rule-groups) rebuildRuleGroups=1 ;;
    -a|--rebuild-all) rebuildTables=1; rebuildRuleGroups=1 ;;
    -*) echo "$prog: $1: Invalid option"; usage; exit 1 ;;
    *) instance="$1";;
  esac
  shift
done

if [ $rebuildTables -eq 0 -a $rebuildRuleGroups -eq 0 ]; then
  echo "$prog: You must specify at least one setup action."
  usage
  exit 1
fi

if ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi


if [ $rebuildRuleGroups -eq 1 ]; then
    #Check for an existing default rules to remove, we'll recreate them
    default_strict_rule_id=`$execSql -i $instance --quiet -c "
      SELECT id
      FROM civicrm_dedupe_rule_group
      WHERE name='Individual Default Strict (fn+mn+ln+suffix+dob+addr+zip)'"`

    default_fuzzy_rule_id=`$execSql -i $instance --quiet -c "
      SELECT id
      FROM civicrm_dedupe_rule_group
      WHERE name='Individual Default Fuzzy (fn+mn+ln+suffix+dob+(addr+zip)|email)'"`

    if [ ! $default_strict_rule_id ]; then
      echo "Creating new Default Strict Rule..."
      #Unset the existing default
      $execSql -i $instance -c "
          UPDATE civicrm_dedupe_rule_group
          SET is_default=0
          WHERE contact_type='Individual' AND is_default=1 AND level='Strict'"

      #Insert the new default fuzzy rule
      $execSql -i $instance -c "
          INSERT INTO civicrm_dedupe_rule_group
            (contact_type, threshold, level, is_default, name)
          VALUES
            ('Individual', 15, 'Strict', 1, 'Individual Default Strict (fn+mn+ln+suffix+dob+addr+zip)');

          -- This user variable lets us be more flexible instead
          -- of chosing a specific id and hoping for the best.
          SET @last_dedupe_rule_id:=LAST_INSERT_ID();

          INSERT INTO civicrm_dedupe_rule
            (dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight)
          VALUES
            (@last_dedupe_rule_id, 'civicrm_contact', 'first_name', NULL, 5),
            (@last_dedupe_rule_id, 'civicrm_contact', 'middle_name', NULL, 2),
            (@last_dedupe_rule_id, 'civicrm_contact', 'last_name', NULL, 5),
            (@last_dedupe_rule_id, 'civicrm_contact', 'suffix_id', NULL, 2),
            (@last_dedupe_rule_id, 'civicrm_address', 'street_address', NULL, 5);"
    else
        echo "Default Strict rule found. Skipping..."
    fi

    if [ ! $default_fuzzy_rule_id ]; then
      echo "Creating new Default Fuzzy Rule..."
      #Unset the existing default
      $execSql -i $instance -c "
          UPDATE civicrm_dedupe_rule_group
          SET is_default=0
          WHERE contact_type='Individual' AND is_default=1 AND level='Fuzzy'"

      #Insert the new default strict rule
      $execSql -i $instance -c "
          INSERT INTO civicrm_dedupe_rule_group
            (contact_type, threshold, level, is_default, name)
          VALUES
            ('Individual', 15, 'Fuzzy', 1, 'Individual Default Fuzzy (fn+mn+ln+suffix+dob+(addr+zip)|email)');

          -- This user variable lets us be more flexible instead
          -- of chosing a specific id and hoping for the best.
          SET @last_dedupe_rule_id:=LAST_INSERT_ID();

          INSERT INTO civicrm_dedupe_rule
            (dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight)
          VALUES
            (@last_dedupe_rule_id, 'civicrm_contact', 'first_name', NULL, 5),
            (@last_dedupe_rule_id, 'civicrm_contact', 'middle_name', NULL, 2),
            (@last_dedupe_rule_id, 'civicrm_contact', 'last_name', NULL, 5),
            (@last_dedupe_rule_id, 'civicrm_contact', 'suffix_id', NULL, 2),
            (@last_dedupe_rule_id, 'civicrm_address', 'street_address', NULL, 5);"
    else
        echo "Default Fuzzy rule found. Skipping..."
    fi


    #Check for the old Omis rule
    omis_rule_id=`$execSql -i $instance --quiet -c "
      SELECT id
      FROM civicrm_dedupe_rule_group
      WHERE name='Individual Omis'"`

    if [ "$omis_rule_id" ]; then
      #Remove the existing OMIS rule
      echo "OMIS rule detected. Removing..."
      $execSql -i $instance --quiet -c "
          DELETE FROM civicrm_dedupe_rule
          WHERE dedupe_rule_group_id=$omis_rule_id"

      $execSql -i $instance --quiet -c "
          DELETE FROM civicrm_dedupe_rule_group
          WHERE id=$omis_rule_id"
    fi


    #Check for the old Default rule
    old_default_rule_id=`$execSql -i $instance --quiet -c "
      SELECT id
      FROM civicrm_dedupe_rule_group
      WHERE name='Individual Default (fname+mname+lname+suffix+street+birth)'"`

    if [ "$old_default_rule_id" ]; then
      #Remove the existing Default rule
      echo "Old Default rule detected. Removing..."
      $execSql -i $instance --quiet -c "
          DELETE FROM civicrm_dedupe_rule
          WHERE dedupe_rule_group_id=$old_default_rule_id"

      $execSql -i $instance --quiet -c "
          DELETE FROM civicrm_dedupe_rule_group
          WHERE id=$old_default_rule_id"
    fi
fi



if [ $rebuildTables -eq 1 ]; then
    #Update suffixes tables
    echo "Rebuilding Suffix tables."
    $execSql -i $instance -f $dedupe_dir/output/suffixes.sql

    #Update nickname tables
    echo "Rebuilding Nickname tables."
    $execSql -i $instance -f $dedupe_dir/output/nicknames.sql

    ## Add/Drop the tables, functions, and triggers
    ## We might want to split some of this out in the future
    echo "Rebuilding shadow tables, triggers, and functions."
    $execSql -f $dedupe_dir/shadow.sql -i $instance

    ## Force an update on all the relevant fields
    ## This is a bit of a hack right now banking on us not having other contact types
    ## in the system and never using the name portion of the civicrm_address. In the
    ## future we might want to take a better approach and be more change proof.
    echo "Populating dedupe tables from civicrm tables."
    $execSql -i $instance -c "
        UPDATE civicrm_contact SET contact_type='Individual' WHERE contact_type='Individual';
        UPDATE civicrm_contact SET contact_type='Organization' WHERE contact_type='Organization';
        UPDATE civicrm_contact SET contact_type='Household' WHERE contact_type='Household';
        UPDATE civicrm_address SET name=NULL WHERE name IS NULL;
    "
fi

echo "Actions Successfully completed."
exit 0
