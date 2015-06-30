#!/bin/bash

# self reference
prog=`basename $0`

# set all script defaults
run_import=1
run_process=1
use_debug=0


usage() {
  echo "
  Usage:
    $prog <options>

    Each option must be provided in the form '--<option_name>'.

      no-import   : skip running the import section
      no-process  : skip running the message processing section
      with-debug  : include verbose debugging information
      help        : prints this message and exits

    " >&2
}

# read in the command line config
while [ $# -gt 0 ]; do
  case "$1" in
    --help|-h) usage; exit 0 ;;
    --no-import) run_import=0 ;;
    --no-process) run_process=0 ;;
    --with-debug) use_debug=1 ;;
    *) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
  esac
  shift
done

if [[ "$use_debug" -gt 0 ]]; then echo -e "\nOption --with-debug detected\n"; fi

# set the config_group, and an easy alias for reading the config
if [[ "$run_import" > 0 ]]
then
  if [[ "$use_debug" -gt 0 ]]; then echo -e "Running import section"; fi
  echo "Doing all import stuff"
  . integrationImportMessages.sh
else
  if [[ "$use_debug" -gt 0 ]]; then echo -e "Option --no-import detected, skipping import section"; fi
fi
if [[ "$run_process" > 0 ]]
then
  if [[ "$use_debug" -gt 0 ]]; then echo -e "Running processing section"; fi
  echo "Doing all process stuff"
else
  if [[ "$use_debug" -gt 0 ]]; then echo -e "Option --no-process detected, skipping process section"; fi
fi

exit 0
