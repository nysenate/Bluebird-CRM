#!/bin/sh
#
# iterateInstances.sh - Perform a command for one or more CRM instances
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-12-03
# Revised: 2011-03-16
#

prog=`basename $0`
script_dir=`dirname $0`
script_dir=`cd $script_dir; echo $PWD`
readConfig=$script_dir/readConfig.sh
execSql=$script_dir/execSql.sh


usage() {
  echo "Usage: $prog [--all] [--live] [--locked] [--training] [--set instanceSet] [--instance instance_name] [cmd]" >&2
  echo "Note: Any occurrence of '%%INSTANCE%%' or '{}' in the command will be replaced by the current instance name." >&2
}

cmd=
cmdfile=
use_all=0
use_live=0
instance_set=
instances=

while [ $# -gt 0 ]; do
  case "$1" in
    --all) use_all=1 ;;
    --live) use_live=1 ;;
    --locked) instance_set="LOCKED" ;;
    --training) instance_set="training" ;;
    --set|-s) shift; instance_set="$1" ;;
    --instance|-i) shift; instances="$instances $1" ;;
    -*) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
    *) cmd="$1" ;;
  esac
  shift
done

if [ $use_all -eq 1 -o $use_live -eq 1 ]; then
  if [ "$instances" -o "$instance_set" ]; then
    echo "$prog: Cannot use --all or --live if instances have been specified">&2
    exit 1
  else
    instances=`$readConfig --list-all-instances | sed "s;^instance:;;"`
    if [ $use_live -eq 1 ]; then
      # Iterate over all instances and probe for "live" instances by checking
      # for the existence of a CiviCRM DB.  We cannot simply execute
      # "show databases" on the server, since each instance can have its own
      # database config.  Thus, we iterate over each instance and attempt to
      # establish a quick connection with its DB to determine if it is "live".
      echo "Calculating live CRM instances..." >&2
      live_instances=
      for instance in $instances; do
        if $execSql -i $instance 2>/dev/null; then
          live_instances="$live_instances $instance"
        fi
      done
      instances="$live_instances"
    fi
  fi
elif [ "$instance_set" ]; then
  ival=`$readConfig --instance-set "$instance_set"`
  if [ ! "$ival" ]; then
    echo "$prog: Instance set $instance_set not found" >&2
    exit 1
  fi
  instances="$instances $ival"
fi

if [ ! "$cmd" ]; then
  echo $instances
  exit 0
elif [ ! "$instances" ]; then
  echo "$prog: No instances were specified" >&2
  exit 1
fi

for instance in $instances; do
  if $readConfig --instance $instance --quiet; then
    realcmd=`echo "$cmd" | sed -e "s;%%INSTANCE%%;$instance;g" -e "s;{};$instance;g"`
    echo "About to exec: $realcmd" >&2
    eval $realcmd
  else
    echo "$prog: $instance: Instance not found in config file; skipping" >&2
  fi
done

exit 0
