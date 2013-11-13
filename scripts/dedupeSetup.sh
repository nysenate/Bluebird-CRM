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
    -r|--rebuild-rules) rebuildRuleGroups=1 ;;
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
    $execSql $instance --quiet -c "
      SET foreign_key_checks = 0;
      TRUNCATE TABLE civicrm_dedupe_rule;
      TRUNCATE TABLE civicrm_dedupe_rule_group;
      SET foreign_key_checks = 1;
    "
    $execSql $instance -f $dedupe_dir/rules.sql
fi


if [ $rebuildTables -eq 1 ]; then
    #Update suffixes tables
    echo "Rebuilding Suffix tables."
    $execSql $instance -f $dedupe_dir/output/suffixes.sql

    #Update nickname tables
    echo "Rebuilding Nickname tables."
    $execSql $instance -f $dedupe_dir/output/nicknames.sql

    ## Add/Drop the tables, functions, and triggers
    ## We might want to split some of this out in the future
    echo "Rebuilding shadow tables, triggers, and functions."
    $execSql $instance -f $dedupe_dir/shadow.sql

    ## Force an update on all the relevant fields
    ## This is a bit of a hack right now banking on us not having other contact types
    ## in the system and never using the name portion of the civicrm_address. In the
    ## future we might want to take a better approach and be more change proof.
    echo "Populating dedupe tables from civicrm tables."
    $execSql $instance -c "
        UPDATE civicrm_contact SET contact_type='Individual' WHERE contact_type='Individual';
        UPDATE civicrm_contact SET contact_type='Organization' WHERE contact_type='Organization';
        UPDATE civicrm_contact SET contact_type='Household' WHERE contact_type='Household';
        UPDATE civicrm_address SET name=NULL WHERE name IS NULL;
    "
fi

echo "Actions Successfully completed."
exit 0
