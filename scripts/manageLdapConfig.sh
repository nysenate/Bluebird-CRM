#!/bin/sh
#
# manageLdapConfig.sh - Wrapper around manageLdapConfig.php
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-12-02
# Revised: 2010-12-14
#

prog=`basename $0`
script_dir=`dirname $0`
readConfig=$script_dir/readConfig.sh

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [cmd [param]] instanceName" >&2
  echo "  where [cmd] is one of:" >&2
  echo "    --list-all, --list-entries, --list-groups, --list-mappings" >&2
  echo "    --set-name [name], --set-server [host], --set-port [port]" >&2
  echo "    --add-entry [entry], --add-group [group], --add-mapping [group|role]" >&2
  echo "    --delete-entry [entry], --delete-group [group], --delete-mapping [group]" >&2
  echo "    --clear-entries, --clear-groups, --clear-mappings" >&2
}

if [ $# -lt 1 ]; then
  usage
  exit 1
fi

instance=
cmd=listAll
param=

while [ $# -gt 0 ]; do
  case "$1" in
    --list-all) cmd=listAll ;;
    --list-entries) cmd=listEntries ;;
    --list-groups) cmd=listGroups ;;
    --list-mappings) cmd=listMappings ;;
    --set-name) shift; cmd=setName; param="$1" ;;
    --set-server) shift; cmd=setServer; param="$1" ;;
    --set-port) shift; cmd=setPort; param="$1" ;;
    --add-entry) shift; cmd=addEntry; param="cn=$1" ;;
    --add-group) shift; cmd=addGroup; param="CN=$1" ;;
    --add-mapping) shift; cmd=addMapping; param="cn=$1" ;;
    --delete-entry) shift; cmd=delEntry; param="cn=$1" ;;
    --delete-group) shift; cmd=delGroup; param="CN=$1" ;;
    --delete-mapping) shift; cmd=delMapping; param="cn=$1|" ;;
    --clear-entries) cmd=clearEntries ;;
    --clear-groups) cmd=clearGroups ;;
    --clear-mappings) cmd=clearMappings ;;
    --help) usage; exit 0 ;;
    -*) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
    *) instance="$1" ;;
  esac
  shift
done

if [ ! "$instance" ]; then
  echo "$prog: Must specify an instance to manage" >&2
  usage
  exit 1
elif ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

dbhost=`$readConfig --ig $instance db.host` || dbhost="$DEFAULT_DB_HOST"
dbuser=`$readConfig --ig $instance db.user` || dbhost="$DEFAULT_DB_USER"
dbpass=`$readConfig --ig $instance db.pass` || dbhost="$DEFAULT_DB_PASS"
dbdrupprefix=`$readConfig --ig $instance db.drupal.prefix` || dbdrupprefix="$DEFAULT_DB_CIVICRM_PREFIX"
dbbasename=`$readConfig -i $instance db.basename` || dbbasename="$instance"
dbname=$dbdrupprefix$dbbasename

php $script_dir/manageLdapConfig.php $cmd $dbhost $dbuser $dbpass $dbname "$param"
exit $?
