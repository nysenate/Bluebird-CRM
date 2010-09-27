#!/bin/sh
#
# readConfig.sh - Read the Bluebird config file and return values
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-11
# Revised: 2010-09-27
#
# Notes:
#   The configuration file is searched for using the following methods:
#   1. If the config file is specified on the command line, then use that.
#   2. If a file named ".bluebird.cfg" is found in the user's home directory,
#      then use that.
#   3. If a file named "bluebird.cfg" is found in the root application
#      directory (the parent dir of the scripts/ dir), then use that.
#   4. If the BLUEBIRD_CONFIG_FILE environment variable is set, use its value.
#   5. Otherwise, use the DEFAULT_CONFIG_FILE as specified in defaults.sh.
#
#   The "--global" option is an alias for "--group globals"
#   The "--instance <name>" option is an alias for "--group instance:<name>"
#   The "--instance-or-global <name>" option is an alias for:
#     "--group instance:<name> --group globals", which searches the given
#     instance by name, and if that instance does not exist, it searches
#     the "globals" group.  This is a powerful method of overriding global
#     variables by setting them within a specific instance group.
#

prog=`basename $0`
script_dir=`dirname $0`
base_dir=`cd $script_dir/..; echo $PWD`
group_names=
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
    --global*) group_names="$group_names globals" ;;
    --group) shift; group_names="$group_names $1" ;;
    --instance) shift; group_names="$group_names instance:$1" ;;
    --instance-or-global) shift; group_names="$group_names instance:$1 globals" ;;
    --list-all-groups) group_pattern="[^]]" ;;
    --list-all-instances) group_pattern="instance:" ;;
    --list-matching-groups) shift; group_pattern="$1" ;;
    -*) echo "Usage: $prog [--config-file file] [--global] [--group name] [--instance name] [--instance-or-global name] [--list-all-groups] [--list-all-instances] [--list-matching-groups pattern] [key]" >&2; exit 1 ;;
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

errcode=0

# If a group pattern is given (one of the --list options), then simply
# print out all matching group names.
#
# If a group name(s) is given with a key name, then attempt to find the
# key in one of the groups.  The first group in which the key name is found
# provides the key value.  If the key is not found in any of the named
# groups, then return an error code of 1.
#
# If only a group name(s) is given, then display all key-value pairs for
# the first group that matches the group name exactly, or return an error
# code of 1 if no group name is matched.
#
# If only a key name is given, then print out all matching key-value pairs
# that have the provided key name, across all groups.

if [ "$group_pattern" ]; then
  sed -n -e "s;^\[\([^]]*$group_pattern[^]]*\)\]$;\1;p" $cfgfile
elif [ "$group_names" -a "$key_name" ]; then
  errcode=1
  for group_name in $group_names; do
    key_line=`sed -n -e "/^\[$group_name\]/,/^\[/p" $cfgfile | grep "^$key_name[ =]"`
    if [ $? -eq 0 ]; then
      echo "$key_line" | sed -e "s;^[^=]*=[ ]*;;"
      errcode=0
      break
    fi
  done
elif [ "$group_names" ]; then
  errcode=1
  for group_name in $group_names; do
    sed -n -e "/^\[$group_name\]/,/^\[/p" $cfgfile | egrep -v "(^[[;]|^$)"
    [ $? -eq 0 ] && errcode=0 && break
  done
elif [ "$key_name" ]; then
  key_lines=`grep "^$key_name[ =]" $cfgfile`
  [ $? -eq 0 ] && echo "$key_lines" | sed -e "s;^[^=]*=[ ]*;;" || errcode=1
else
  cat $cfgfile
fi

exit $errcode
