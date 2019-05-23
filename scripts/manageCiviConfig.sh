#!/bin/sh
#
# manageCiviConfig.sh - Wrapper around manageCiviConfig.php
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-30
# Revised: 2013-05-12
# Revised: 2013-07-29 - add --list-all and --update-all options
# Revised: 2013-07-30 - add scope options (--standard, --template, --all)
# Revised: 2016-06-28 - add --preview, --drupal
# Revised: 2019-05-23 - add --clear-settings
#

prog=`basename $0`
script_dir=`dirname $0`
readConfig=$script_dir/readConfig.sh

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--list | --preview/--pre | --update | --clear-settings] [ --standard | --template/--tpl | --drupal/--drup | --all ] instanceName" >&2
}

if [ $# -lt 1 ]; then
  usage
  exit 1
fi

instance=
op=list
scope=def

while [ $# -gt 0 ]; do
  case "$1" in
    --list) op=list ;;
    --preview|--pre) op=preview ;;
    --update) op=update ;;
    --clear-*) op=clear ;;
    --standard|--default) scope=def ;;
    --template|--tpl) scope=tpl ;;
    --drupal|--drup) scope=drup ;;
    --all) scope=all ;;
    -*) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
    *) instance="$1" ;;
  esac
  shift
done

if [ ! "$instance" ]; then
  echo "$prog: Must specify an instance to manage" >&2
  usage
  exit 1
elif ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

# Passing a cygwin path to PHP won't work, so expand it to Win32 on Cygwin.
[ "$OSTYPE" = "cygwin" ] && script_dir=`cygpath --mixed $script_dir`

php "$script_dir/manageCiviConfig.php" "$instance" "$op" "$scope"
exit $?
