#!/bin/sh
#
# resetLdapConfig.sh - Set LDAP config according to config file for an instance.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2011-01-05
# Revised: 2011-01-06
#

prog=`basename $0`
script_dir=`dirname $0`
readConfig=$script_dir/readConfig.sh

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog instanceName" >&2
}

if [ $# -lt 1 ]; then
  usage
  exit 1
fi

instance=

while [ $# -gt 0 ]; do
  case "$1" in
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

g_ldap_entries=`$readConfig --global ldap.entries | tr , " "`
g_ldap_groups=`$readConfig --global ldap.groups | tr , " "`
g_ldap_mappings=`$readConfig --global ldap.mappings | sed -e 's; *, *;,;g' | tr " " "~" | tr , " "`

if [ $? -ne 0 ]; then
  echo "$prog: Unable to retrieve global LDAP configuration" >&2
  exit 1
fi

ldap_groups=`$readConfig --instance $instance ldap.groups | tr , " "`

set -x
$script_dir/manageLdapConfig.sh --clear-entries $instance
$script_dir/manageLdapConfig.sh --clear-groups $instance
$script_dir/manageLdapConfig.sh --clear-mappings $instance

for ldapitem in $g_ldap_entries $ldap_groups; do
  $script_dir/manageLdapConfig.sh --add-entry "$ldapitem" $instance
done

for ldapitem in $g_ldap_groups $ldap_groups; do
  $script_dir/manageLdapConfig.sh --add-group "$ldapitem" $instance
done

for ldapitem in $g_ldap_mappings; do
  ldapitem=`echo $ldapitem | tr "~" " "`
  $script_dir/manageLdapConfig.sh --add-mapping "$ldapitem" $instance
done

exit $?
