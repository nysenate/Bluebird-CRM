#!/bin/bash

prog=`basename $0`
script_dir=`dirname $0`
script_dir=`cd $script_dir; echo $PWD`
readConfig=$script_dir/readConfig.sh
execSql=$script_dir/execSql.sh
pid=$$
piddir=/var/run

# set all script defaults
default_ssh_host=staging-4850.prod.hosting.acquia.com
default_ssh_user=nysenate
default_tunnel_port=7777
default_socket_file=bbintegrator
persistent_tunnel=0


usage() {
  echo "
  Usage:
    $prog <options>

    Unless otherwise  noted, command  line options  must be  provided in  the form  '--<option_name>
    <option_value>'. Options not provided on the command line will be read from the globals group in
    bluebird.cfg.  Some options may  fallback to a  hard-coded default (where shown)  if not present
    on either the command line or bluebird.cfg.  If any required option is  not able to be resolved,
    the script will exit with return code 1.

    Options in bluebird.cfg follow the same general naming conventions, with all '-' characters
    replaced with '.', and an added prefix of 'publicint.'.  For example, the socket-file command
    line option corresponds to the publicint.socket.file configuration setting.

    Available options (defaults) are:

      socket-file : path to socket file used to control SSH tunnel (bbintegrator)
      ssh-user    : username to use for SSH tunnel
      ssh-host    : hostname for SSH tunnel endpoint
      tunnel-port : local port to forward through SSH tunnel for MySQL connection (7777)
                    The tunnel port must be greater than 1024
      config-group: the bluebird.cfg group to read (globals)
                    the config-group option is only recognized on the command line
      persistent  : if present, this option causes the SSH tunnel to left open after
                    the script completes.  This option does not require an option_value.

    " >&2
}

# function to set a parameter variable to the proper cascaded value
# cascade, in order of greatest precedence, is (command line)->(config file)->(script default)
set_param() {
  # expects one parameter - the name of the variable to populate
  # if the variable is empty, try reading it from the config file
  if [[ -z "${!1}" ]]; then
    tmpvar=${i//_/.}
    echo $1" is blank, reading config with "$tmpvar
    eval "$1=`${readAlias}${tmpvar}`"
    echo "i = "$1", val i = "${!1}
  fi
  # if the variable is STILL empty, set to default value
  if [[ -z "${!1}" ]]; then
    echo "value still blank, using default"
    eval "$1=\$default_"$1
  fi
  echo "final value i = "$1", val i = "${!1}
}

# read in the command line config
while [ $# -gt 0 ]; do
  case "$1" in
    --help|-h) usage; exit 0 ;;
    --socket-file) shift; socket_file=$1 ;;
    --tunnel-port) shift; tunnel_port=$1 ;;
    --ssh-user) shift; ssh_user=$1 ;;
    --ssh-host) shift; ssh_host=$1 ;;
    --persistent) persistent_tunnel=1 ;;
    --config-group) shift; config_group=$1 ;;
    -*) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
    *) cmd="$1" ;;
  esac
  shift
done

# set the config_group, and an easy alias for reading the config
config_group=${config_group:-globals}
readAlias=$readConfig" --group "$config_group" publicint."

# set up the variables to use command line, OR config values, OR default values
for i in ssh_host ssh_user tunnel_port socket_file; do
  set_param $i
  echo "final value i = "$i", val i = "${!i}
done

need_tunnel=0
if [[ "$ssh_host" != "localhost" && "$ssh_host" != "127.0.0.1" ]]
then
  need_tunnel=1
fi

if [[ "$need_tunnel" > 0 ]]
then
  if [[ "$tunnel_port" -le 1024 ]]
  then
    echo "Cannot start a tunnel with port low-range ports ("$tunnel_port")"
    exit 1
  fi
  if [[ -z "$ssh_user" ]]
  then
    echo "A tunnel is required, but no user name was passed.  See --ssh_user option."
    exit 2
  fi
  echo "Starting Tunnel . . ."
  ssh -M -S $socket_file -fnNT -L $tunnel_port:localhost:3306 $ssh_user"@"$ssh_host
else
  echo "No tunnel required for connection to "$ssh_host
fi

echo "Doing all mah stuff"

if [[ "$persistent_tunnel" -eq 0 ]]
then
  ssh -S $socket_file -O exit $ssh_user"@"$ssh_host
fi

exit 0
