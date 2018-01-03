#!/bin/sh
#
# iterateInstances.sh - Perform a command for one or more CRM instances
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-12-03
# Revised: 2013-03-07
# Revised: 2018-01-02 - add ability to run commands as non-root user
#

prog=`basename $0`
script_dir=`dirname $0`
script_dir=`cd $script_dir; echo $PWD`
readConfig=$script_dir/readConfig.sh
execSql=$script_dir/execSql.sh
pid=$$
piddir=/var/run

. "$script_dir/defaults.sh"

usage() {
  echo "Usage: $prog [--quiet] [--all] [--live] [--live-fast] [--locked] [--civimail] [--signups] [--training] [--set instanceSet] [--instance instanceName] [--exclude instanceName [--exclude ...]] [--exclude-set instanceSet [--exclude-set ...]] [--bg] [--no-wait] [--serial uniq-id] [--timing] [--run-as user] [cmd]" >&2
  echo "Note: Any occurrence of '%%INSTANCE%%' or '{}' in the command will be replaced by the current instance name." >&2
}

cleanup_and_exit() {
  [ "$pidfile" ] && rm $pidfile
  exit $1
}


cmd=
cmdfile=
use_all=0
use_live=0
fast_live=0
instance_sets=
instances=
excludes=
exclude_sets=
quiet_mode=0
bg_jobs=
no_wait=0
serial_id=
show_timing=0
run_as=

while [ $# -gt 0 ]; do
  case "$1" in
    --help|-h) usage; exit 0 ;;
    --all) use_all=1 ;;
    --live) use_live=1 ;;
    --live-fast) use_live=1; fast_live=1 ;;
    --locked) instance_sets="$instance_sets LOCKED" ;;
    --civimail) instance_sets="$instance_sets civimail" ;;
    --signups) instance_sets="$instance_sets signups" ;;
    --training) instance_sets="$instance_sets training" ;;
    --set|-s) shift; instance_sets="$instance_sets $1" ;;
    --instance|-i) shift; instances="$instances $1" ;;
    --exclude|-e) shift; excludes="$excludes $1" ;;
    --exclude-set|-E) shift; exclude_sets="$exclude_sets $1" ;;
    --quiet|-q) quiet_mode=1 ;;
    --bg) bg_jobs="&" ;;
    --no-wait) no_wait=1 ;;
    --serial) shift; serial_id="$1" ;;
    --timing|-t) show_timing=1 ;;
    --run-as|-r) shift; run_as="$1" ;;
    -*) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
    *) cmd="$1" ;;
  esac
  shift
done

pidfile=

if [ "$serial_id" ]; then
  pidfile="$piddir/$prog-$serial_id.pid"
  if [ -f "$pidfile" ]; then
    oldpid=`cat $pidfile`
    if ps -p $oldpid >/dev/null; then
      echo "$prog: Another instance is already running with serial ID [$serial_id] and pid [$oldpid]; aborting" >&2
      exit 0
    else
      echo "$prog: Removing leftover pid file [$pidfile]" >&2
      rm $pidfile
    fi
  fi
  trap "rm -f $pidfile; exit 0" 1 2 3 10 15
  echo $pid >$pidfile
fi

if [ $use_all -eq 1 -o $use_live -eq 1 ]; then
  if [ "$instances" -o "$instance_sets" ]; then
    echo "$prog: Cannot use --all or --live if instances have been specified">&2
    cleanup_and_exit 1
  else
    instances=`$readConfig --list-all-instances | sed "s;^instance:;;"`
    if [ $use_live -eq 1 ]; then
      [ $quiet_mode -eq 0 ] && echo "Calculating live CRM instances..." >&2
      live_instances=
      if [ $fast_live -eq 0 ]; then
        # This is the "slow" live check, where each instance is checked for
        # a live database backing it.  This mode must be used if databases
        # do not all reside on the same database server.
        #
        # Iterate over all instances and probe for "live" instances by checking
        # for the existence of a CiviCRM DB.  We cannot simply execute
        # "show databases" on the server, since each instance can have its own
        # database config.  Thus, we iterate over each instance and attempt to
        # establish a quick connection with its DB to determine if it is "live".
        for instance in $instances; do
          if $execSql $instance 2>/dev/null; then
            live_instances="$live_instances $instance"
          fi
        done
      else
        # This is the "fast" live check, which assumes that all databases
        # reside on the same database server.  The only connection made to
        # the database server is to make a single "show databases" call.
        db_civi_prefix=`$readConfig --global db.civicrm.prefix` || db_civi_prefix=$DEFAULT_DB_CIVICRM_PREFIX
        dbs=`$execSql -q --no-db -c "show databases" | sed -n "s;^$db_civi_prefix;;p"`
        for instance in $instances; do
          dbbasename=`$readConfig --instance $instance db.basename` || dbbasename="$instance"
          if echo "$dbs" | grep -x -q "$dbbasename"; then
            live_instances="$live_instances $instance"
          fi
        done
      fi
      instances="$live_instances"
    fi
  fi
elif [ "$instance_sets" ]; then
  for iset in $instance_sets; do
    ival=`$readConfig --instance-set "$iset"`
    if [ ! "$ival" ]; then
      echo "$prog: Instance set [$iset] not found" >&2
      cleanup_and_exit 1
    fi
    instances="$instances $ival"
  done
fi

# Now remove excluded instances.
if [ "$exclude_sets" ]; then
  for iset in $exclude_sets; do
    ival=`$readConfig --instance-set "$iset"`
    if [ ! "$ival" ]; then
      echo "$prog: Instance set [$iset] not found for exclusion" >&2
      cleanup_and_exit 1
    fi
    excludes="$excludes $ival"
  done
fi

tmp_instances=
for instance in $instances; do
  if echo "$excludes" | egrep -q "(^| )$instance( |$)"; then
    :
  else
    tmp_instances="$tmp_instances $instance"
  fi
done
instances="$tmp_instances"


if [ ! "$cmd" ]; then
  echo $instances
  cleanup_and_exit 0
elif [ ! "$instances" ]; then
  echo "$prog: No instances were specified" >&2
  cleanup_and_exit 1
fi

if [ "$run_as" ]; then
  if ! id -u $run_as >/dev/null 2>&1; then
    echo "$prog: Unable to run as '$run_as'; user does not exist" >&2
    cleanup_and_exit 1
  fi
fi

for instance in $instances; do
  if $readConfig --instance $instance --quiet; then
    realcmd=`echo "$cmd" | sed -e "s;%%INSTANCE%%;$instance;g" -e "s;{};$instance;g"`
    [ $quiet_mode -eq 0 ] && logdt "[$run_as@$instance] About to exec: $realcmd $bg_jobs" >&2
    [ $show_timing -eq 1 ] && echo "==> START [`date +%H:%M:%S.%N`]"
    if [ "$run_as" ]; then
      su $run_as -s /bin/sh -c "$realcmd $bg_jobs"
    else
      eval $realcmd $bg_jobs
    fi
    [ $show_timing -eq 1 ] && echo "<== FINISH [`date +%H:%M:%S.%N`]"
  else
    echo "$prog: $instance: Instance not found in config file; skipping" >&2
  fi
done

[ $no_wait -eq 0 ] && wait

cleanup_and_exit 0
