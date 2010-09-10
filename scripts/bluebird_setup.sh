#!/bin/sh
#
# bluebird_setup.sh - Initialize one or more Bluebird CRM instances
#
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-01
# Revised: 2010-09-10
#

prog=`basename $0`
default_config_env=prod
default_config_file=bluebird_all_instances.cfg
script_dir=/data/scripts
import_dir=/data/importData
iscript_dir=/data/senateProduction/civicrmSharedDirectories/scripts/importData
tempdir=/tmp/bluebird_imports


usage() {
  echo "Usage: $prog [--all] [--no-init] [--no-import] [--no-fixperms] [-e config_env] [-f config_file] [--keep] instance_name [instance_name ...]" >&2
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
  extended="$3"
  if [ "$extended" = "Y" ]; then
    srcdesc="ext"
  else
    srcdesc="omis"
  fi

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
    cd $script_dir
    php civiSetup.php $config_env fixpermissions $instance
  )
}


use_all=0
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
elif [ ! "$instances" -a $use_all -eq 0 ]; then
  echo "$prog: No instances were specified" >&2
  exit 1
fi

if [ $use_all -eq 1 ]; then
  if [ "$instances" ]; then
    echo "$prog: Cannot use --all if instances have been specified" >&2
    exit 1
  else
    instances=`grep "^[^#]" $config_file | cut -d, -f1 | uniq`
  fi
fi

mkdir -p "$tempdir"

for instance in $instances; do
  ilines=`grep "^$instance," $config_file`
  if [ ! "$ilines" ]; then
    echo "$prog: Warning: CRM instance [$instance] not found in config file" >&2
    continue
  fi

  for iline in $ilines; do
    instance_name=`echo $iline | cut -d, -f1`
    import_name=`echo $iline | cut -d, -f2`
    is_extended=`echo $iline | cut -d, -f3`
    # Not using is_majority and ldap_group yet...
    is_majority=`echo $iline | cut -d, -f4`
    ldap_group=`echo $iline | cut -d, -f5`

    if [ $no_init -eq 1 ]; then
      echo "==> Skipping initialization of instance [$instance_name]"
    else
      if [ "$is_extended" = "N" ]; then
        echo "==> About to create CRM instance [$instance_name]"
        create_instance $instance_name $config_env
      else
        echo "==> About to load extended data into instance [$instance_name]"
      fi
    fi

    if [ $no_import -eq 1 ]; then
      echo "==> Skipping data importation for instance [$instance_name]"
    else
      echo "==> About to import data into CRM instance [$instance_name]"
      import_data $instance_name $import_name $is_extended
    fi

    if [ $no_fixperms -eq 1 ]; then
      echo "==> Skipping permission fixups for instance [$instance_name]"
    else
      echo "==> About to fix permissions for CRM instance [$instance_name]"
      fix_permissions $instance_name $config_env
    fi
  done
done

if [ $keep_tempdir -eq 0 ]; then
  rm -rf "$tempdir"
fi

exit 0
