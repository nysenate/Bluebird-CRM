#!/bin/sh
#
# readConfig.sh - Read the Bluebird config file and return values
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-11
#

prog=`basename $0`
cfgfile="/etc/bluebird.ini"
group_name=
key_name=

while [ $# -gt 0 ]; do
  case "$1" in
    --group) shift; group_name="$1" ;;
    --key) shift; key_name="$1" ;;
    *) echo "Usage: $prog [--group] [--key]" >&2; exit 1 ;;
  esac
  shift
done

if [ "$group_name" -a "$key_name" ]; then
  sed -n -e "/^\[$group_name\]/,/^\[/p" $cfgfile | grep "^$key_name[ =]" | sed -e "s;^[^=]*=[ ]*;;"
elif [ "$group_name" ]; then
  sed -n -e "/^\[$group_name\]/,/^\[/p" $cfgfile | egrep -v "(^[[;]|^$)"
elif [ "$key_name" ]; then
  grep "^$key_name[ =]" $cfgfile
else
  cat $cfgfile
fi

