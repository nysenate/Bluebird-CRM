#!/bin/bash
#

# self reference and command to read bluebird.cfg
prog=`basename $0`
script_dir=`dirname $0`
script_dir=`cd $script_dir; echo $PWD`
readConfig=$script_dir/../../../scripts/readConfig.sh

# set all script defaults
default_config_group=globals
default_config_prefix=integration
default_socket_file=bbintegrator
default_ssh_host=localhost
default_ssh_user=no_user
default_tunnel_host=localhost
default_tunnel_port=7777
persistent_tunnel=0
exit_only=0
use_debug=0


usage() {
  echo "
Usage: $prog <options>

Options not provided on the command line will be read from the [globals] group
in bluebird.cfg.  Some options may fall back to a hard-coded default if not
present on either the command line or bluebird.cfg.  If any required option
cannot be resolved, the script will exit with return code 1.

Options in bluebird.cfg follow the same general naming conventions, with
all '-' characters replaced with '.', and an added prefix of 'integration.'.

For example, the socket-file command line option corresponds to the
integration.socket.file configuration setting.

Available options with [defaults] in brackets are:

--config-group NAME : the group in bluebird.cfg containing all other options [$default_config_group]
--config-prefix VAL : the prefix to use when reading from bluebird.cfg [$default_config_prefix]
--socket-file PATH  : path to socket file used to control SSH tunnel [$default_socket_file]
--ssh-host HOST     : hostname for SSH tunnel endpoint [$default_ssh_host]
--ssh-user USER     : username to use for SSH tunnel [$default_ssh_user]
--tunnel-host HOST  : hostname for recipient of remote port forwarding [$default_tunnel_host]
--tunnel-port PORT  : local port to forward through SSH tunnel for MySQL connection [$default_tunnel_port]; must be greater than 1024
--persistent        : keep the SSH tunnel open after script completion
--exit-only         : attempts to kill the tunnel, then exits
--debug             : causes debug text to echo to console
--help              : prints this message and exits

Additionally, config-group and config-prefix may be read from bluebird.cfg.
The values will only be recognized when found in the globals section, and must
be named integration.config.group and integration.config.prefix, respectively.  Command line values for these options will override any
found in the config file.
" >&2
}

# Function to set a parameter variable to the proper cascaded value.
# The cascade, in order of greatest to least precedence:
#   (command line)->(config file)->(script default)
set_param() {
  # expects one parameter - the name of the variable to populate
  # if the variable is empty, try reading it from the config file
  if [ $use_debug -eq 1 ]; then
    echo "Inside set_param for $1"
  fi
  if [ -z "${!1}" ]; then
    tmpvar=${i//_/.}
    eval "$1=`${readAlias}${tmpvar}`"
    if [ $use_debug -eq 1 ]; then
      echo "Value read from config, $tmpvar = ${!1}"
    fi
  fi
  # if the variable is STILL empty, set to default value
  if [ -z "${!1}" ]; then
    if [ $use_debug -eq 1 ]; then
      echo "Value still blank, using default"
    fi
    eval "$1=\$default_$1"
  fi
  if [ $use_debug -eq 1 ]; then
    echo "Final value $1 = ${!1}"
  fi
}

# read any optional group config from bluebird
config_group=`${readConfig} --group globals integration.config.group`
config_prefix=`${readConfig} --group globals integration.config.prefix`

# read in the command line config
while [ $# -gt 0 ]; do
  case "$1" in
    --help|-h) usage; exit 0 ;;
    --config-group) shift; config_group=$1 ;;
    --config-prefix) shift; config_prefix=$1 ;;
    --socket-file) shift; socket_file=$1 ;;
    --ssh-user) shift; ssh_user=$1 ;;
    --ssh-host) shift; ssh_host=$1 ;;
    --tunnel-host) shift; tunnel_host=$1 ;;
    --tunnel-port) shift; tunnel_port=$1 ;;
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

# set the config_group, and an easy alias for reading the config
config_group=${config_group:-$default_config_group}
config_prefix=${config_prefix:-$default_config_prefix}
readAlias="$readConfig --group $config_group $config_prefix."

if [ $use_debug -eq 1 ]; then
  echo "readAlias set to: $readAlias"
fi

# set up the variables to use command line, OR config values, OR default values
for i in ssh_host ssh_user tunnel_host tunnel_port socket_file; do
  set_param $i
  if [ $use_debug -eq 1 ]; then
    echo "Setting $i = ${!i}"
  fi
done

if [ $exit_only -eq 0 ]; then
  if [ "$ssh_host" != "localhost" -a "$ssh_host" != "127.0.0.1" ]; then
    if [ $tunnel_port -lt 1024 ]; then
      echo "Cannot start a tunnel with privileged port $tunnel_port"
      exit 2
    elif [ -z "$ssh_user" ]; then
      echo "A tunnel is required, but no user name was passed.  See --ssh_user option."
      usage
      exit 3
    fi

    echo "Starting Tunnel . . ."
    if [ $use_debug -eq 1 ]; then
      echo "ssh -M -S $socket_file -fnNT -L $tunnel_port:$tunnel_host:3306 $ssh_user@$ssh_host"
    fi
    ssh -M -S $socket_file -fnNT -L $tunnel_port:$tunnel_host:3306 $ssh_user@$ssh_host
    if [ $? -ne 0 ]; then
      echo "ERROR: Unable to establish SSH tunnel"
      exit 4
    fi
  else
    echo "No tunnel required for connection to $ssh_host"
  fi
fi

# commands to run for the import process
php $script_dir/import_integration_messages.php
if [ $? -eq 0 ]; then
  rc=0
else
  echo "ERROR: Unable to import messages from remote" >&2
  rc=1
fi

if [ $persistent_tunnel -eq 0 -o $exit_only -eq 1 ]; then
  echo "Closing Tunnel . . ."
  ssh -S $socket_file -O exit $ssh_user@$ssh_host
fi

exit $rc
