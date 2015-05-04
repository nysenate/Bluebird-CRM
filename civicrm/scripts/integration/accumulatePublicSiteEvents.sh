#!/bin/bash

prog=`basename $0`
script_dir=`dirname $0`
script_dir=`cd $script_dir; echo $PWD`
readConfig=$script_dir/../../../scripts/readConfig.sh
execSql=$script_dir/../../../scripts/execSql.sh
pid=$$
piddir=/var/run

# set all script defaults
default_ssh_host=localhost
default_ssh_user=no_user
default_tunnel_port=7777
default_tunnel_host=localhost
default_socket_file=bbintegrator
config_prefix=integration
persistent_tunnel=0
exit_only=0
run_import=1
run_process=1
use_debug=0


usage() {
  echo "
  Usage:
    $prog <options>

    Unless otherwise  noted, command  line options  must be  provided in  the form  '--<option_name>
    <option_value>'. Options not provided on the command line will be read from the globals group in
    bluebird.cfg.  Some options may  fallback to a  hard-coded default (where shown)  if not present
    on either the command line or bluebird.cfg.  If any required option is  not able to be resolved,
    the script will exit with return code 1.

    Options in  bluebird.cfg  follow the same  general naming  conventions, with  all '-' characters
    replaced with '.', and an added  prefix of 'integration.'.  For example, the socket-file command
    line option corresponds to the integration.socket.file configuration setting.

    Available options (defaults) are:

      socket-file  : path to socket file used to control SSH tunnel (bbintegrator)
      ssh-user     : username to use for SSH tunnel
      ssh-host     : hostname for SSH tunnel endpoint
      tunnel-host  : hostname for recipient of remote port forwarding
      tunnel-port  : local port to forward through SSH tunnel for MySQL connection (7777)
                     The tunnel port must be greater than 1024
      config-group : the bluebird.cfg group to read (globals)
                     the config-group option is only recognized on the command line
      config-prefix: the prefix to use while searching bluebird.cfg for values (integration.)
                     the config-prefix option is only recognized on the command line
      persistent   : if present, this option causes the SSH tunnel to be left open after
                     the script completes.  Does not use an option_value.
      exit-only    : attempts to kill the tunnel, then exits.  Does not use an option_value.
      no-import    : prevents the import section from running.  Does not use an option_value.
                     The no-import option is only recognized on the command line
      no-process   : prevents the message processing section from running.  Does not use
                     an option_value.  The no-process option is only recognized on the
                     command line
      with-debug   : causes debug text to echo to console.  Does not use an option_value.

    " >&2
}

# Function to set a parameter variable to the proper cascaded value.
# The cascade, in order of greatest to least precedence:
#   (command line)->(config file)->(script default)
set_param() {
  # expects one parameter - the name of the variable to populate
  # if the variable is empty, try reading it from the config file
  if [[ "$use_debug" -gt 0 ]]; then echo "Inside set_param for "$1; fi
  if [[ -z "${!1}" ]]; then
    tmpvar=${i//_/.}
    eval "$1=`${readAlias}${tmpvar}`"
    if [[ "$use_debug" -gt 0 ]]; then echo "Value read from config, "$tmpvar" = "${!1}; fi
  fi
  # if the variable is STILL empty, set to default value
  if [[ -z "${!1}" ]]; then
    if [[ "$use_debug" -gt 0 ]]; then echo -e "Value still blank, using default"; fi
    eval "$1=\$default_"$1
  fi
  if [[ "$use_debug" -gt 0 ]]; then echo -e "Final value "$1" = "${!1}; fi
}

# read in the command line config
while [ $# -gt 0 ]; do
  case "$1" in
    --help|-h) usage; exit 0 ;;
    --socket-file) shift; socket_file=$1 ;;
    --tunnel-port) shift; tunnel_port=$1 ;;
    --tunnel-host) shift; tunnel_host=$1 ;;
    --ssh-user) shift; ssh_user=$1 ;;
    --ssh-host) shift; ssh_host=$1 ;;
    --persistent) persistent_tunnel=1 ;;
    --config-group) shift; config_group=$1 ;;
    --config-prefix) shift; config_prefix=$1 ;;
    --exit-only) exit_only=1 ;;
    --no-import) run_import=0 ;;
    --no-process) run_process=0 ;;
    --with-debug) use_debug=1 ;;
    -*) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
    *) cmd="$1" ;;
  esac
  shift
done

if [[ "$use_debug" -gt 0 ]]; then echo -e "\nOption --with-debug detected\n"; fi

# set the config_group, and an easy alias for reading the config
config_group=${config_group:-globals}
readAlias=$readConfig" --group "$config_group" "$config_prefix"."
if [[ "$use_debug" -gt 0 ]]; then echo -e "readAlias set to: "$readAlias"\n"; fi

# set up the variables to use command line, OR config values, OR default values
for i in ssh_host ssh_user tunnel_host tunnel_port socket_file; do
  set_param $i
  if [[ "$use_debug" -gt 0 ]]; then echo -e "Setting "$i" = "${!i}"\n"; fi
done

if [[ "$exit_only" -eq 0 ]]
then
  need_tunnel=0
  if [[ "$ssh_host" != "localhost" && "$ssh_host" != "127.0.0.1" ]]
  then
    need_tunnel=1
  fi

  if [[ "$need_tunnel" > 0 ]]
  then
    if [[ "$tunnel_port" -le 1024 ]]
    then
      echo "Cannot start a tunnel with low-range ports ("$tunnel_port")"
      exit 1
    fi
    if [[ -z "$ssh_user" ]]
    then
      echo "A tunnel is required, but no user name was passed.  See --ssh_user option."
      usage
      exit 2
    fi
    echo "Starting Tunnel . . ."
    if [[ "$use_debug" -gt 0 ]]; then echo -e ssh -M -S $socket_file -fnNT -L $tunnel_port:$tunnel_host:3306 $ssh_user"@"$ssh_host; fi
    ssh -M -S $socket_file -fnNT -L $tunnel_port:$tunnel_host:3306 $ssh_user"@"$ssh_host
  else
    echo "No tunnel required for connection to "$ssh_host
  fi

  if [[ "$run_import" > 0 ]]
  then
    echo "Doing all mah import stuff"
  fi
  if [[ "$run_process" > 0 ]]
  then
    echo "Doing all mah process stuff"
  fi
fi

if [[ "$persistent_tunnel" -eq 0 || "$exit_only" -gt 0 ]]
then
  ssh -S $socket_file -O exit $ssh_user"@"$ssh_host
fi

exit 0
