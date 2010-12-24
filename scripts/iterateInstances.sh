#!/bin/sh
#
# iterateInstances.sh - Perform a command for one or more CRM instances
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-12-03
# Revised: 2010-12-23
#

prog=`basename $0`
script_dir=`dirname $0`
script_dir=`cd $script_dir; echo $PWD`
readConfig=$script_dir/readConfig.sh


usage() {
  echo "Usage: $prog [--all] [--live] [--locked] [--training] [--set instanceSet] [--instance instance_name] [cmd]" >&2
}

cmd=
cmdfile=
use_all=0
instance_set=
instances=

while [ $# -gt 0 ]; do
  case "$1" in
    --all) use_all=1 ;;
    --live) ;;
    --locked) instance_set="LOCKED" ;;
    --training) instance_set="training" ;;
    --set|-s) shift; instance_set="$1" ;;
    --instance|-i) shift; instances="$instances $1" ;;
    -*) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
    *) cmd="$1" ;;
  esac
  shift
done

if [ $use_all -eq 1 ]; then
  if [ "$instances" -o "$instance_set" ]; then
    echo "$prog: Cannot use --all if instances have been specified" >&2
    exit 1
  else
    instances=`$readConfig --list-all-instances | sed "s;^instance:;;"`
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
  realcmd=`echo "$cmd" | sed -e "s;%%INSTANCE%%;$instance;g"`
  echo "about to exec: $realcmd"
  $realcmd
done

exit 0
