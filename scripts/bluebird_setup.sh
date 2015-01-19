#!/bin/sh
#
# bluebird_setup.sh - Initialize ("spin up") a Bluebird CRM instance.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-01
# Revised: 2011-04-12
#

prog=`basename $0`
script_dir=`dirname $0`
script_dir=`cd "$script_dir"; echo $PWD`
base_dir=`cd "$script_dir/.."; echo $PWD`
readConfig="$script_dir/readConfig.sh"
geoCoder="$base_dir/civicrm/scripts/updateAddresses.php"
app_rootdir=`$readConfig --global app.rootdir` || app_rootdir="$DEFAULT_APP_ROOTDIR"
data_rootdir=`$readConfig --global data.rootdir` || data_rootdir="$DEFAULT_DATA_ROOTDIR"
import_rootdir=`$readConfig --global import.rootdir` || data_rootdir="$DEFAULT_IMPORT_ROOTDIR"
iscript_dir=$app_rootdir/civicrm/scripts/importData
tempdir=/tmp/bluebird_imports


usage() {
  echo "Usage: $prog [--no-init] [--no-unzip] [--no-import] [--no-ldapconfig] [--no-cc] [--no-fixperms] [--geocode] [--force-unzip] [--keep] [--temp-dir tempdir] [--use-importdir] instance_name" >&2
}

create_instance() {
  instance="$1"
  (
    set -x
    cd $script_dir
    $script_dir/copyInstance.sh --delete template $instance
    $script_dir/manageCiviConfig.sh --update $instance
    $script_dir/hitInstance.sh $instance
    $script_dir/fixFileSystemPath.sh $instance
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


add_ldap_group() {
  instance="$1"
  group="$2"
  (
    set -x
    $script_dir/manageLdapConfig.sh --add-entry "$group" $instance
    $script_dir/manageLdapConfig.sh --add-group "$group" $instance
  )
}

clear_cache() {
  instance="$1"
  (
    set -x
    $script_dir/clearCache.sh --all $instance
  )
}


fix_permissions() {
  (
    set -x
    $script_dir/fixPermissions.sh
  )
}


geocode_instance() {
  instance="$1"
  (
    set -x
    php $geoCoder -S$instance -g
  )
}


stage=$default_stage
no_init=0
no_unzip=0
no_import=0
no_ldapcfg=0
no_clearcache=0
no_fixperms=0
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

db_name=`$readConfig --instance $instance db.name`
datasets=`$readConfig --instance $instance datasets`
ldap_groups=`$readConfig --instance $instance ldap.groups`
# Not using imap_user, imap_pass yet...
imap_user=`$readConfig --instance $instance imap.user`
imap_pass=`$readConfig --instance $instance imap.pass`

if [ $no_init -eq 1 ]; then
  echo "==> Skipping initialization of instance [$instance]"
else
  echo "==> About to create CRM instance [$instance]"
  create_instance $instance
fi

datasets=`echo $datasets | tr , " "`
ldap_groups=`echo $ldap_groups | tr , " "`
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

if [ $no_ldapcfg -eq 1 ]; then
  echo "==> Skipping LDAP config for instance [$instance]"
else
  echo "==> About to configure LDAP groups for CRM instance [$instance]"
  for ldap_group in $ldap_groups; do
    add_ldap_group $instance $ldap_group
  done
fi

if [ $no_clearcache -eq 1 ]; then
  echo "==> Skipping cache clear for instance [$instance]"
else
  echo "==> About to clear all caches for CRM instance [$instance]"
  clear_cache $instance
fi

if [ $no_fixperms -eq 1 ]; then
  echo "==> Skipping permission fixups for instance [$instance]"
else
  echo "==> About to fix permissions for CRM instance [$instance]"
  fix_permissions
fi

if [ $geocode -eq 1 ]; then
  echo "==> About to geocode CRM instance [$instance]"
  geocode_instance $instance
else
  echo "==> Skipping geocode process for CRM instance [$instance]"
fi

exit 0
