#!/bin/bash
#
# v3.10.1.sh
# Minor upgrade
# - enables new extension, nyss_greeting
# - fixes NYSS #18009 - Add .msg file extension to safe_file_extension option group
#
# Project: BluebirdCRM
# Authors: Nate Frank
# Organization: New York State Senate
# Date: 2026-03-15
#

prog=`basename $0`
script_dir=`dirname $0`
drush=$script_dir/drush.sh
cv=$script_dir/cv.sh
clearCache=$script_dir/clearCache.sh
readConfig=$script_dir/readConfig.sh
execSql=$script_dir/execSql.sh

. $script_dir/defaults.sh

if [ $# -ne 1 ]; then
  echo "Usage: $prog instanceName" >&2
  exit 1
fi

instance="$1"

data_rootdir=`$readConfig --ig $instance data.rootdir` || data_rootdir="$DEFAULT_DATA_ROOTDIR"
pubfiles_dir="$data_rootdir/$instance/pubfiles"

if ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

## enable nyss_greeting
echo "enabling extension nyss_greeting"
$cv $instance en nyss_greeting -n -q

## NYSS #18009 - Add .msg files to safe_file_extension option.
echo "adding .msg file extension to safe_file_extension option group"

## Get option_group_id
option_group_id=`$cv $instance api4 OptionGroup.get +s id +w 'name = "safe_file_extension"' +l --out=list`

if [[ -n $option_group_id ]]; then

  ## make sure .msg extension is not already in the list
  current_val=`$cv $instance api4 OptionValue.get +s value +w "option_group_id = $option_group_id" +w 'label = "msg"' +l --out=list`
  if [[ -z $current_val ]]; then

    ## get the next value, which is the max value + 1
    next_value=`$execSql  -q -c "SELECT MAX(CAST(value as UNSIGNED))+1 FROM civicrm_option_value WHERE option_group_id = $option_group_id" $instance`

    ## create new record
    $cv $instance api4 OptionValue.create '{"values":{"value": "'$next_value'","option_group_id": '$option_group_id',"label": "msg"}}'

  else
    echo "msg already in safe_file_extension list."
  fi
else
  echo "option_group_id not found. abort safe_file_extension update." >&2
fi
## END NYSS #18009

## clear cache again
$clearCache $instance

echo "$prog: upgrade process is complete for $instance."
