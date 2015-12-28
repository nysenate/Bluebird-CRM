#!/bin/bash
#

prog=`basename $0`
script_dir=`dirname $0`
script_dir=`cd $script_dir; echo $PWD`
readConfig=$script_dir/../../../scripts/readConfig.sh

# set all script defaults
default_config_group=globals
default_config_prefix=website
default_socket_file=bbintegrator
default_ssh_host=localhost
default_ssh_user=no_user
default_tunnel_local_port=7777
default_tunnel_remote_host=localhost
default_tunnel_remote_port=3306
default_use_tunnel=0
persistent_tunnel=0
exit_only=0
use_debug=0


usage() {
  echo "
Usage: $prog <options>

Options not provided on the command line will be read from the [globals] group
in bluebird.cfg (which can be overrideen with the --config-group option).
Some options may fall back to a hard-coded default if not present on either
the command line or bluebird.cfg.  If any required option cannot be resolved,
the script will exit with return code 1.

Options in bluebird.cfg follow the same general naming conventions, with
all '-' characters replaced with '.', and an added prefix of 'website.'.
The prefix can be overriden with the --config-prefix option.

For example, the socket-file command line option corresponds to the
website.socket.file configuration setting.

Available options with [defaults] in brackets are:

--config-group NAME : the group in bluebird.cfg containing all other options [$default_config_group]
--config-prefix VAL : the prefix to use when reading from bluebird.cfg [$default_config_prefix]
--use-tunnel        : use an SSH tunnel to connect to the database
--socket-file PATH  : path to socket file used to control SSH tunnel [$default_socket_file]
--ssh-host HOST     : hostname for SSH tunnel endpoint [$default_ssh_host]
--ssh-user USER     : username to use for SSH tunnel [$default_ssh_user]
--tunnel-local-port PORT  : local port to forward through SSH tunnel [$default_tunnel_local_port]; must be greater than 1024
--tunnel-remote-host HOST : remote host of the SSH tunnel [$default_tunnel_remote_host]
--tunnel-remote-port PORT : remote port of the SSH tunnel [$default_tunnel_remote_port]; must be greater than 1024
--persistent        : keep the SSH tunnel open after script completion
--exit-only         : attempts to kill the tunnel, then exits
--debug             : causes debug text to echo to console
--help              : prints this message and exits
" >&2
}

# Function to set a parameter variable to the proper cascaded value.
# The cascade, in order of greatest to least precedence:
#   (command line)->(config file)->(script default)
set_param() {
  # expects one parameter - the name of the variable to populate
  # if the variable is empty, try reading it from the config file
  if [ -z "${!1}" ]; then
    if $readAlias$1 --quiet; then
      # Try both website.my_nice_param and website.my.nice.param
      cfgparam="$1"
    else
      cfgparam=${1//_/.}
    fi
    eval "$1=`$readAlias$cfgparam`"
    if [ $use_debug -eq 1 ]; then
      echo "Setting $1 using config parameter $config_prefix.$cfgparam = ${!1}"
    fi
  fi
  # if the variable is STILL empty, set to default value
  if [ -z "${!1}" ]; then
    defvarname=default_$1
    if [ $use_debug -eq 1 ]; then
      echo "Setting $1 using $defvarname = ${!defvarname}"
    fi
    eval "$1=${!defvarname}"
  fi
  if [ $use_debug -eq 1 ]; then
    echo "Final value: $1 = ${!1}"
  fi
}

config_group=$default_config_group
config_prefix=$default_config_prefix

# read in the command line config
while [ $# -gt 0 ]; do
  case "$1" in
    --help|-h) usage; exit 0 ;;
    --config-group) shift; config_group=$1 ;;
    --config-prefix) shift; config_prefix=$1 ;;
    --use-tunnel) use_tunnel=1 ;;
    --socket-file) shift; socket_file=$1 ;;
    --ssh-user) shift; ssh_user=$1 ;;
    --ssh-host) shift; ssh_host=$1 ;;
    --tunnel-local-port) shift; tunnel_local_port=$1 ;;
    --tunnel-remote-host) shift; tunnel_remote_host=$1 ;;
    --tunnel-remote-port) shift; tunnel_remote_port=$1 ;;
    --persistent) persistent_tunnel=1 ;;
    --exit-only) exit_only=1 ;;
    --debug) use_debug=1 ;;
    *) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
  esac
  shift
done

if [ $use_debug -eq 1 ]; then
  echo "Option --debug detected"
fi

readAlias="$readConfig --group $config_group $config_prefix."

if [ $use_debug -eq 1 ]; then
  echo "readAlias set to: $readAlias"
fi

# set up the variables to use command line, OR config values, OR default values
for i in use_tunnel ssh_host ssh_user tunnel_local_port tunnel_remote_host tunnel_remote_port socket_file; do
  set_param $i
  if [ $use_debug -eq 1 ]; then
    echo "Setting $i = ${!i}"
  fi
done

rc=0

if [ $exit_only -eq 0 ]; then
  if [ $use_tunnel -eq 1 ]; then
    if [ $tunnel_local_port -lt 1024 ]; then
      echo "Cannot start a tunnel with privileged port $tunnel_local_port"
      exit 2
    elif [ -z "$ssh_host" ]; then
      echo "A tunnel is required, but no SSH hostname was provided.  See --ssh_host option."
      usage
      exit 3
    elif [ -z "$ssh_user" ]; then
      echo "A tunnel is required, but no SSH username was provided.  See --ssh_user option."
      usage
      exit 3
    fi

    echo "Starting tunnel..."
    if [ $use_debug -eq 1 ]; then
      echo "ssh -M -S $socket_file -fnNT -L $tunnel_local_port:$tunnel_remote_host:$tunnel_remote_port $ssh_user@$ssh_host"
    fi
    ssh -M -S $socket_file -fnNT -L $tunnel_local_port:$tunnel_remote_host:$tunnel_remote_port $ssh_user@$ssh_host
    if [ $? -ne 0 ]; then
      echo "ERROR: Unable to establish SSH tunnel"
      exit 4
    fi
  else
    echo "No tunnel required for database connection"
  fi

  # commands to run for the import process
  echo "Importing event messages from website..."
  if [ $use_debug -eq 1 ]; then
    echo "php $script_dir/import_integration_messages.php"
  fi
  php $script_dir/import_integration_messages.php
  if [ $? -ne 0 ]; then
    echo "ERROR: Unable to import messages from remote" >&2
    rc=1
  fi
fi


if [ $use_tunnel -eq 1 ]; then
  if [ $persistent_tunnel -eq 0 -o $exit_only -eq 1 ]; then
    echo "Closing Tunnel..."
    ssh -S $socket_file -O exit $ssh_user@$ssh_host
  fi
fi

exit $rc
