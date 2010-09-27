#!/bin/sh
#
# readConfig.sh - Read the Bluebird config file and return values
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-11
# Revised: 2010-09-15
#

prog=`basename $0`
default_cfgfile="/etc/bluebird.ini"
cfgfile=$default_cfgfile
group_name=
group_pattern=
key_name=

while [ $# -gt 0 ]; do
  case "$1" in
    --config-file|-f) shift; cfgfile="$1" ;;
    --group) shift; group_name="$1" ;;
    --groups) shift; group_pattern="$1" ;;
    --all-groups) group_pattern="[^]]" ;;
    -*) echo "Usage: $prog [--config-file file] [--group] [--groups pattern] [--all-groups] [key]" >&2; exit 1 ;;
    *) key_name="$1"
  esac
  shift
done

if [ ! "$cfgfile" ]; then
  echo "$prog: Config file must be set" >&2
  exit 1
elif [ ! -r "$cfgfile" ]; then
  echo "$prog: $cfgfile: File not found" >&2
  exit 1
fi

if [ "$group_pattern" ]; then
  sed -n -e "s;^\[\([^]]*$group_pattern[^]]*\)\]$;\1;p" $cfgfile
elif [ "$group_name" -a "$key_name" ]; then
  sed -n -e "/^\[$group_name\]/,/^\[/p" $cfgfile | grep "^$key_name[ =]" | sed -e "s;^[^=]*=[ ]*;;"
elif [ "$group_name" ]; then
  sed -n -e "/^\[$group_name\]/,/^\[/p" $cfgfile | egrep -v "(^[[;]|^$)"
elif [ "$key_name" ]; then
  grep "^$key_name[ =]" $cfgfile | sed -e "s;^[^=]*=[ ]*;;"
else
  cat $cfgfile
fi

