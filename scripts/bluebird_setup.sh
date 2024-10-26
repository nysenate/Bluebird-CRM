#!/bin/sh
#
# bluebird_setup.sh - Initialize ("spin up") a Bluebird CRM instance.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-01
# Revised: 2011-04-12
# Revised: 2017-01-04 - enable v2 changelog; miscellaneous cleanup
# Revised; 2021-01-26 - add setup of Mosaico "Standard Office Template"
#

prog=`basename $0`
script_dir=`dirname $0`
script_dir=`cd "$script_dir"; echo $PWD`
readConfig="$script_dir/readConfig.sh"
drush="$script_dir/drush.sh"

app_rootdir=`$readConfig --global app.rootdir` || app_rootdir="$DEFAULT_APP_ROOTDIR"
data_rootdir=`$readConfig --global data.rootdir` || data_rootdir="$DEFAULT_DATA_ROOTDIR"
import_rootdir=`$readConfig --global import.rootdir` || data_rootdir="$DEFAULT_IMPORT_ROOTDIR"

if [ ! "$app_rootdir" -o ! "$data_rootdir" -o ! "$import_rootdir" ]; then
  echo "$prog: app, data, and import directories must be configured" >&2
  exit 1
fi

cscript_dir="$app_rootdir/civicrm/scripts"
iscript_dir="$cscript_dir/importData"
tempdir=/tmp/bluebird_imports

usage() {
  echo "Usage: $prog [--no-init] [--no-unzip] [--no-import] [--no-ldapconfig] [--no-cc] [--no-fixperms] [--no-template-setup] [--geocode] [--force-unzip] [--keep] [--temp-dir tempdir] [--use-importdir] instance_name" >&2
}

create_instance() {
  instance="$1"
  (
    set -x
    cd $script_dir
    $script_dir/copyInstance.sh --delete template $instance
    $script_dir/manageCiviConfig.sh --update --all $instance
    php $cscript_dir/logEnable.php -S$instance
    $script_dir/hitInstance.sh $instance
  )
}


unzip_data() {
  dataset="$1"

  if [ $force_unzip -eq 1 -o ! -d $tempdir/$dataset ]; then
    importzip="$import_rootdir/$dataset.zip"
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
  unzipdir="$tempdir/$dataset"
  [ "$OSTYPE" = "cygwin" ] && unzipdir=`cygpath --mixed $unzipdir`
  (
    cd "$iscript_dir"
    set -x
    php importData.inc.php $instance $dataset -d "$unzipdir" -s $srcdesc
  )

  # Clean up converted import data left over by importData.inc.php
  if [ $keep_tempdir -eq 0 ]; then
    ( set -x
      rm -rf "$unzipdir/"
    )
  fi
}


stage=$default_stage
no_init=0
no_unzip=0
no_import=0
no_ldapcfg=0
no_clearcache=0
no_fixperms=0
no_tplsetup=0
geocode=0
force_unzip=0
keep_tempdir=0
instance=

while [ $# -gt 0 ]; do
  case "$1" in
    --no-init|--no-create) no_init=1 ;;
    --no-unzip|--no-unarchive) no_unzip=1 ;;
    --no-import) no_import=1 ;;
    --no-ldap*) no_ldapcfg=1 ;;
    --no-clear-cache|--no-cc) no_clearcache=1 ;;
    --no-fixperm*) no_fixperms=1 ;;
    --no-template*|--no-tpl*) no_tplsetup=1 ;;
    --geocode) geocode=1 ;;
    --force-unzip) force_unzip=1 ;;
    --keep|-k) keep_tempdir=1 ;;
    --temp-dir|-t) shift; tempdir="$1" ;;
    --use-importdir) keep_tempdir=1; tempdir="$import_rootdir" ;;
    -*) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
    *) instance="$1" ;;
  esac
  shift
done

if [ ! "$instance" ]; then
  echo "$prog: No CRM instance was specified" >&2
  exit 1
fi

locked_instances=`$readConfig --instance-set "LOCKED"`
mkdir -p "$tempdir"

if ! $readConfig --instance $instance --quiet ; then
  echo "$prog: Error: CRM instance [$instance] not found in config file." >&2
  exit 1
elif echo "$locked_instances" | egrep -q "(^|[ ]+)$instance([ ]+|$)"; then
  echo "$prog: NOTICE: CRM instance [$instance] is LOCKED; aborting." >&2
  exit 1
fi

datasets=`$readConfig --instance $instance datasets`
ldap_groups=`$readConfig --instance $instance ldap.groups`

if [ $no_init -eq 1 ]; then
  echo "==> Skipping initialization of instance [$instance]"
else
  echo "==> About to create CRM instance [$instance]"
  create_instance $instance
fi

# The first imported data set is always OMIS data.  All subsequent
# imports are considered to be "external" data.
sourcedesc=omis

if [ "$datasets" ]; then
  datasets=`echo $datasets | tr , " "`
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
fi

if [ $no_ldapcfg -eq 1 ]; then
  echo "==> Skipping LDAP config for instance [$instance]"
elif [ "$ldap_groups" ]; then
  ldap_groups=`echo $ldap_groups | tr , " "`
  echo "==> About to configure LDAP groups for CRM instance [$instance]"
  for ldap_group in $ldap_groups; do
    $script_dir/manageLdapConfig.sh --add-entry "$group" $instance
    $script_dir/manageLdapConfig.sh --add-group "$group" $instance
  done
fi

if [ $no_clearcache -eq 1 ]; then
  echo "==> Skipping cache clear for instance [$instance]"
else
  echo "==> About to clear all caches for CRM instance [$instance]"
  $script_dir/clearCache.sh --all $instance
fi

if [ $no_fixperms -eq 1 ]; then
  echo "==> Skipping permission fixups for instance [$instance]"
else
  echo "==> About to fix permissions for CRM instance [$instance]"
  $script_dir/fixPermissions.sh
fi

if [ $no_tplsetup -eq 1 ]; then
  echo "==> Skipping Mosaico template setup for instance [$instance]"
else
  echo "==> About to setup Mosaico template for CRM instance [$instance]"
  $drush $instance cvapi nyss.generatemailtemplate addupdate="Update" --quiet
fi

if [ $geocode -eq 1 ]; then
  echo "==> About to geocode CRM instance [$instance]"
  php $cscript_dir/updateAddresses2.php -S$instance -g
else
  echo "==> Skipping geocode process for CRM instance [$instance]"
fi

exit 0
