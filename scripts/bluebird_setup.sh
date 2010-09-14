#!/bin/sh
#
# bluebird_setup.sh - Initialize one or more Bluebird CRM instances
#
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-01
# Revised: 2010-09-13
#

prog=`basename $0`
script_dir=`dirname $0`
script_dir=`cd $script_dir; echo $PWD`
readConfig=$script_dir/readConfig.sh
default_config_env=prod
default_config_file=/etc/bluebird.ini
import_dir=/data/importData
iscript_dir=/data/senateProduction/civicrmSharedDirectories/scripts/importData
tempdir=/tmp/bluebird_imports


usage() {
  echo "Usage: $prog [--all] [--set instanceSet] [--no-init] [--no-import] [--no-fixperms] [-e config_env] [-f config_file] [--keep] instance_name [instance_name ...]" >&2
}

create_instance() {
  instance="$1"
  config_env="$2"
  (
    set -x
    cd $script_dir
    php civiSetup.php $config_env deletesite $instance
    php civiSetup.php $config_env deletesite $instance
    php civiSetup.php $config_env copysite template $instance
    php civiSetup.php $config_env copysite template $instance
  )
}

import_data() {
  instance="$1"
  import="$2"
  srcdesc="$3"
  importzip="$import_dir/$import.zip"
  if [ ! -r "$importzip" ]; then
    echo "$prog: $importzip: Unable to locate import zip file" >&2
    return
  fi

  unzipdir=$tempdir/$import
  (
    iu=`echo $import | tr [:lower:] [:upper:]`
    cd $tempdir
    rm -rf $import/
    unzip $importzip
    cd $import
    $script_dir/filesToUpper.sh *
    # Convert issue code file into extended format if it hasn't been done yet
    if [ ! -f ${iu}ISSCONV.TXT ]; then
      set -x
      $script_dir/convert_issue_codes.sh ${iu}ISS.TXT > ${iu}ISSCONV.TXT
    fi
  )

  (
    set -x
    cd $iscript_dir
    php importData.inc.php $instance $import -d $unzipdir -s $srcdesc
  )

  # Clean up converted import data left over by importData.inc.php
  if [ $keep_tempdir -eq 0 ]; then
    rm -f /tmp/$import-*.tsv
    rm -rf $unzipdir/
  fi
}


fix_permissions() {
  instance="$1"
  config_env="$2"
  (
    set -x
    $script_dir/fixPermissions.sh
  )
}


use_all=0
instance_set=
config_env=$default_config_env
config_file=$default_config_file
stage=$default_stage
no_import=0
no_init=0
no_fixperms=0
keep_tempdir=0
instances=

while [ $# -gt 0 ]; do
  case "$1" in
    --all) use_all=1 ;;
    --set|-s) shift; instance_set="$1" ;;
    --config-env|-e) shift; config_env="$1" ;;
    --config-file|-f) shift; config_file="$1" ;;
    --no-import) no_import=1 ;;
    --no-init) no_init=1 ;;
    --no-fixperms) no_fixperms=1 ;;
    --keep|-k) keep_tempdir=1 ;;
    -*) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
    *) instances="$instances $1" ;;
  esac
  shift
done

if [ ! "$config_file" ]; then
  echo "$prog: Must specify a configuration file" >&2
  usage
  exit 1
elif [ ! -r "$config_file" ]; then
  echo "$prog: $config_file: File not found" >&2
  exit 1
fi

if [ $use_all -eq 1 ]; then
  if [ "$instances" -o "$instance_set" ]; then
    echo "$prog: Cannot use --all if instances have been specified" >&2
    exit 1
  else
    instances=`$readConfig -f "$config_file" --groups "instance:" | sed "s;^instance:;;"`
  fi
elif [ "$instance_set" ]; then
  ival=`$readConfig -f "$config_file" --group "instance_sets" --key "$instance_set"`
  if [ ! "$ival" ]; then
    echo "$prog: Instance set $instance_set not found" >&2
    exit 1
  fi
  instances="$instances $ival"
fi

if [ ! "$instances" ]; then
  echo "$prog: No instances were specified" >&2
  exit 1
fi


mkdir -p "$tempdir"

for instance in $instances; do
  igroup="instance:$instance"
  instance_config=`$readConfig -f "$config_file" --group $igroup`
  if [ ! "$instance_config" ]; then
    echo "$prog: Warning: CRM instance [$instance] not found in config file" >&2
    continue
  fi

  instance_name=`$readConfig -f "$config_file" --group $igroup --key name`
  datasets=`$readConfig -f "$config_file" --group $igroup --key datasets`
  # Not using is_majority and ldap_group yet...
  is_majority=`$readConfig -f "$config_file" --group $igroup --key majority`
  ldap_group=`$readConfig -f "$config_file" --group $igroup --key ldap.group`
  imap_user=`$readConfig -f "$config_file" --group $igroup --key imap.user`
  imap_pass=`$readConfig -f "$config_file" --group $igroup --key imap.pass`

  if [ $no_init -eq 1 ]; then
    echo "==> Skipping initialization of instance [$instance_name]"
  else
    echo "==> About to create CRM instance [$instance_name]"
    create_instance $instance_name $config_env
  fi

  if [ $no_import -eq 1 ]; then
    echo "==> Skipping data importation for instance [$instance_name]"
  else
    echo "==> About to import data into CRM instance [$instance_name]"
    datasets=`echo $datasets | tr , " "`
    sourcedesc=omis
    for ds in $datasets; do
      import_data $instance_name $ds $sourcedesc
      sourcedesc=ext
    done
  fi

    if [ $no_fixperms -eq 1 ]; then
      echo "==> Skipping permission fixups for instance [$instance_name]"
    else
      echo "==> About to fix permissions for CRM instance [$instance_name]"
      fix_permissions $instance_name $config_env
    fi
done

if [ $keep_tempdir -eq 0 ]; then
  rm -rf "$tempdir"
fi

exit 0
