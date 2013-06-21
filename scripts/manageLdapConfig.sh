#!/bin/sh
#
# manageLdapConfig.sh - Wrapper around manageLdapConfig.php
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-12-02
# Revised: 2013-06-21
#

prog=`basename $0`
script_dir=`dirname $0`
readConfig=$script_dir/readConfig.sh

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [cmd [param]] instanceName" >&2
  echo "  where [cmd] is one of:" >&2
  echo "    --bluebird-setup | -bs" >&2
  echo "    --list-all, --list-server, --list-authentication" >&2
  echo "    --list-authorization --list-entries, --list-mappings" >&2
  echo "    --set-name {name}, --set-host {host}, --set-port {port}" >&2
  echo "    --set-entries {groupList}, --set-mappings {mappingList}" >&2
  echo "    --set-php-auth {phpSnippet}" >&2
  echo "  {groupList} is of the form ldapGroup1,ldapGroup2,..." >&2
  echo "  {mappingList} is of the form ldapGroup1|role1, ldapGroup2|role2, ..." >&2
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
    --bluebird-setup|-bs) cmd=setup ;;
    --list-all|-a) cmd=listAll ;;
    --list-server|-ls) cmd=listServer ;;
    --list-authentication|-la) cmd=listAuthentication ;;
    --list-authorization|-lA) cmd=listAuthorization ;;
    --list-entries|-le) cmd=listEntries ;;
    --list-mappings|-lm) cmd=listMappings ;;
    --set-name|-sn) shift; cmd=setName; param="$1" ;;
    --set-host|-sh) shift; cmd=setHost; param="$1" ;;
    --set-port|-sp) shift; cmd=setPort; param="$1" ;;
    --set-entries|-se) shift; cmd=setEntries; param="$1" ;;
    --set-mappings|-sm) shift; cmd=setMappings; param="$1" ;;
    --set-php-auth|-spa) shift; cmd=setPhpAuth; param="$1" ;;
    --help|-h) usage; exit 0 ;;
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

if [ "$cmd" = "setup" ]; then
  ldap_entries=`$readConfig --ig $instance ldap.entries`
  ldap_mappings=`$readConfig --ig $instance ldap.mappings`
  $0 --set-entries "$ldap_entries" $instance
  $0 --set-mappings "$ldap_mappings" $instance
else
  dbhost=`$readConfig --ig $instance db.host` || dbhost="$DEFAULT_DB_HOST"
  dbuser=`$readConfig --ig $instance db.user` || dbhost="$DEFAULT_DB_USER"
  dbpass=`$readConfig --ig $instance db.pass` || dbhost="$DEFAULT_DB_PASS"
  dbdrupprefix=`$readConfig --ig $instance db.drupal.prefix` || dbdrupprefix="$DEFAULT_DB_CIVICRM_PREFIX"
  dbbasename=`$readConfig -i $instance db.basename` || dbbasename="$instance"
  dbname=$dbdrupprefix$dbbasename

php $script_dir/manageLdapConfig.php $cmd $dbhost $dbuser $dbpass $dbname "$param"
fi

exit $?
