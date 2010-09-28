#!/bin/sh
#
# bluebird_setup.sh - Initialize one or more Bluebird CRM instances
#
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-01
# Revised: 2010-09-27
#

prog=`basename $0`
script_dir=`dirname $0`
script_dir=`cd $script_dir; echo $PWD`
readConfig=$script_dir/readConfig.sh
app_rootdir=`$readConfig --global app.rootdir` || app_rootdir="$DEFAULT_APP_ROOTDIR"
data_rootdir=`$readConfig --global data.rootdir` || data_rootdir="$DEFAULT_DATA_ROOTDIR"
import_dir=$data_rootdir/importData
iscript_dir=$app_rootdir/senateProduction/civicrmSharedDirectories/scripts/importData
tempdir=/tmp/bluebird_imports


usage() {
  echo "Usage: $prog [--all] [--set instanceSet] [--no-init] [--no-unzip] [--no-import] [--no-fixperms] [--force-unzip] [--keep] [--temp-dir tempdir] [--use-importdir] instance_name [instance_name ...]" >&2
}

create_instance() {
  instance="$1"
  (
    set -x
    cd $script_dir
    $script_dir/deleteInstance.sh --ok $instance
    php civiSetup.php prod copysite template $instance
    php civiSetup.php prod copysite template $instance
  )
}


unzip_data() {
  dataset="$1"

  if [ $force_unzip -eq 1 -o ! -d $tempdir/$dataset ]; then
    importzip="$import_dir/$dataset.zip"
    if [ ! -r "$importzip" ]; then
      echo "$prog: $importzip: Unable to locate dataset zip file" >&2
      return 1
    fi

    (
      cd $tempdir/ || return 1
      rm -rf $dataset/
      unzip $importzip
      cd $dataset/
      $script_dir/filesToUpper.sh *
    )
  fi

  (
    cd $tempdir/$dataset/
    iu=`echo $dataset | tr [:lower:] [:upper:]`
    # Convert issue code file into extended format if it hasn't been done yet
    if [ ! -f ${iu}ISSCONV.TXT ]; then
      echo "Need to convert issue code file ${iu}ISS.TXT"
      set -x
      $script_dir/convert_issue_codes.sh ${iu}ISS.TXT > ${iu}ISSCONV.TXT
    fi
  )
}


import_data() {
  instance="$1"
  dataset="$2"
  srcdesc="$3"
  unzipdir=$tempdir/$dataset
  (
    cd $iscript_dir
    set -x
    php importData.inc.php $instance $dataset -d $unzipdir -s $srcdesc
  )

  # Clean up converted import data left over by importData.inc.php
  if [ $keep_tempdir -eq 0 ]; then
    ( set -x
      rm -f /tmp/$dataset-*.tsv
      rm -rf $unzipdir/
    )
  fi
}


fix_permissions() {
  (
    set -x
    $script_dir/fixPermissions.sh
  )
}


use_all=0
instance_set=
stage=$default_stage
no_init=0
no_unzip=0
no_import=0
no_fixperms=0
force_unzip=0
keep_tempdir=0
instances=

while [ $# -gt 0 ]; do
  case "$1" in
    --all) use_all=1 ;;
    --set|-s) shift; instance_set="$1" ;;
    --no-init|--no-create) no_init=1 ;;
    --no-unzip|--no-unarchive) no_unzip=1 ;;
    --no-import) no_import=1 ;;
    --no-fixperm*) no_fixperms=1 ;;
    --force-unzip) force_unzip=1 ;;
    --keep|-k) keep_tempdir=1 ;;
    --temp-dir|-t) shift; tempdir="$1" ;;
    --use-importdir) keep_tempdir=1; tempdir="$import_dir" ;;
    -*) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
    *) instances="$instances $1" ;;
  esac
  shift
done

if [ $use_all -eq 1 ]; then
  if [ "$instances" -o "$instance_set" ]; then
    echo "$prog: Cannot use --all if instances have been specified" >&2
    exit 1
  else
    instances=`$readConfig --list-all-instances | sed "s;^instance:;;"`
  fi
elif [ "$instance_set" ]; then
  ival=`$readConfig --instance-set "$instance_set"`
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

locked_instances=`$readConfig --instance-set "LOCKED"`
mkdir -p "$tempdir"

for instance in $instances; do
  instance_config=`$readConfig --instance $instance`
  if [ ! "$instance_config" ]; then
    echo "$prog: Warning: CRM instance [$instance] not found in config file" >&2
    continue
  elif echo "$locked_instances" | egrep -q "(^|[ ]+)$instance([ ]+|$)"; then
    echo "$prog: NOTICE: CRM instance [$instance] is LOCKED; skipping" >&2
    continue
  fi

  db_name=`$readConfig --instance $instance db.name`
  datasets=`$readConfig --instance $instance datasets`
  # Not using is_majority, ldap_group, imap_user, imap_pass yet...
  is_majority=`$readConfig --instance $instance majority`
  ldap_group=`$readConfig --instance $instance ldap.group`
  imap_user=`$readConfig --instance $instance imap.user`
  imap_pass=`$readConfig --instance $instance imap.pass`

  if [ $no_init -eq 1 ]; then
    echo "==> Skipping initialization of instance [$instance]"
  else
    echo "==> About to create CRM instance [$instance]"
    create_instance $instance
  fi

  datasets=`echo $datasets | tr , " "`
  sourcedesc=omis

  for ds in $datasets; do
    if [ $no_unzip -eq 1 ]; then
      echo "==> Skipping data unzip for instance [$instance]"
    else
      unzip_data $ds
    fi

    if [ $no_import -eq 1 ]; then
      echo "==> Skipping data importation for instance [$instance]"
    else
      echo "==> About to import data into CRM instance [$instance]"
      import_data $instance $ds $sourcedesc
    fi


    sourcedesc=ext
  done

  if [ $no_fixperms -eq 1 ]; then
    echo "==> Skipping permission fixups for instance [$instance]"
  else
    echo "==> About to fix permissions for CRM instance [$instance]"
    fix_permissions
  fi
done

exit 0
