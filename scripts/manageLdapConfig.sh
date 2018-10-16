#!/bin/sh
#
# manageLdapConfig.sh - Wrapper around manageLdapConfig.php
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-12-02
# Revised: 2013-06-21 and 2013-11-08
# Revised: 2018-10-03 - Add more parameters to support ActiveDirectory LDAP
#

prog=`basename $0`
script_dir=`dirname $0`
readConfig=$script_dir/readConfig.sh

. $script_dir/defaults.sh

map_param_to_field() {
  p="$1"
  if [ "$p" = "host" ]; then
    fld="address"
  elif [ "$p" = "type" ]; then
    fld="ldap_type"
  elif [ "$p" = "anonymous" ]; then
    fld="bind_method"
  elif [ "$p" = "user" ]; then
    fld="binddn"
  elif [ "$p" = "pass" ]; then
    fld="bindpw"
  elif [ "$p" = "group_class" ]; then
    fld="group_object_category"
  else
    # port, basedn, user_attr, mail_attr
    fld="$p"
  fi
  echo "$fld"
}

usage() {
  echo "Usage: $prog [cmd [param]] instanceName" >&2
  echo "  where [cmd] is one of:" >&2
  echo "    --bluebird-setup | -bs" >&2
  echo "    --list-all, --list-server, --list-authentication" >&2
  echo "    --list-authorization --list-entries, --list-mappings" >&2
  echo "    --set-name {name}, --set-host {host}, --set-port {port}" >&2
  echo "    --set-server {serverParams}" >&2
  echo "    --set-entries {groupList}, --set-mappings {mappingList}" >&2
  echo "    --set-php-auth {phpSnippet}" >&2
  echo "  {serverParams} is of the form param1=value1 | param2=value2 | ..." >&2
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
    --set-server|-ss) shift; cmd=setServer; param="$1" ;;
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
  ldap_server=""
  for p in host port type anonymous user pass basedn user_attr mail_attr group_class; do
    fld=`map_param_to_field $p`
    val=`$readConfig --ig $instance ldap.$p`
    if [ $? -eq 0 ]; then
      if [ "$p" = "anonymous" ]; then
        [ "$val" = "true" -o "$val" = "1" ] && bind_method=3 || bind_method=1
        val=$bind_method
      fi
      ldap_server="$ldap_server|$fld=$val"
    fi
  done

  ldap_server=`echo "$ldap_server" | cut -c2-`
  ldap_entries=`$readConfig --ig $instance ldap.entries`
  ldap_mappings=`$readConfig --ig $instance ldap.mappings`

  echo "Setting LDAP server parameters: $ldap_server"
  $0 --set-server "$ldap_server" $instance
  echo "Setting LDAP entries: $ldap_entries"
  $0 --set-entries "$ldap_entries" $instance
  echo "Setting LDAP mappings: $ldap_mappings"
  $0 --set-mappings "$ldap_mappings" $instance
else
  php "$script_dir/manageLdapConfig.php" "$instance" "$cmd" "$param"
fi

exit $?
