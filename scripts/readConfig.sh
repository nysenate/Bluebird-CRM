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
# Note:
#   The configuration file is searched for using the following methods:
#   1. If the config file is specified on the command line, then use that.
#   2. If a file named ".bluebird.cfg" is found in the user's home directory,
#      then use that.
#   3. If a file named "bluebird.cfg" is found in the root application
#      directory (the parent dir of the scripts/ dir), then use that.
#   4. If the BLUEBIRD_CONFIG_FILE environment variable is set, use its value.
#   5. Otherwise, use the DEFAULT_CONFIG_FILE as specified in defaults.sh.
#

prog=`basename $0`
script_dir=`dirname $0`
base_dir=`cd $script_dir/..; echo $PWD`
group_name=
group_pattern=
key_name=

. $script_dir/defaults.sh

# Start by using the default value.
cfgfile=$DEFAULT_CONFIG_FILE

# Next, set the config file if the BLUEBIRD_CONFIG_FILE env variable is set.
[ "$BLUEBIRD_CONFIG_FILE" ] && cfgfile="$BLUEBIRD_CONFIG_FILE"

# Next, look in the application base directory.
if [ -r "$base_dir/bluebird.cfg" ]; then
  cfgfile="$base_dir/bluebird.cfg"
fi

# Next, look in the user's home directory.
if [ -r "$HOME/.bluebird.cfg" ]; then
  cfgfile="$HOME/.bluebird.cfg"
fi

# Finally, set the config file if provided on the command line.

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

