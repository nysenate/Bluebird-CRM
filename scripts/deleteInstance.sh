#!/bin/sh
#
# deleteInstance.sh - Delete the databases and files for a CRM instance
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-14
# Revised: 2010-09-27
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--ok] [--files-only] [--db-only] [--domain domain] instanceName" >&2
}

force_ok=0
files_only=0
db_only=0
instance=
domain=

while [ $# -gt 0 ]; do
  case "$1" in
    --ok) force_ok=1 ;;
    --files-only) files_only=1 ;;
    --db-only) db_only=1 ;;
    --domain|-d) shift; domain="$1" ;;
    -*) echo "$prog: $1: Invalid option" >&2 ; usage ; exit 1 ;;
    *) instance="$1" ;;
  esac
  shift
done

if [ ! "$instance" ]; then
  echo "$prog: Must specify an instance to delete" >&2
  usage
  exit 1
fi

[ "$domain" ] || domain=`$readConfig --ig $instance base.domain` || domain=$DEFAULT_BASE_DOMAIN
db_civicrm_prefix=`$readConfig --ig $instance db.civicrm.prefix` || db_civicrm_prefix=$DEFAULT_DB_CIVICRM_PREFIX
db_drupal_prefix=`$readConfig --ig $instance db.drupal.prefix` || db_drupal_prefix=$DEFAULT_DB_DRUPAL_PREFIX
www_rootdir=`$readConfig --ig $instance www.rootdir` || www_rootdir=$DEFAULT_WWW_ROOTDIR
errcode=0

if [ $force_ok -eq 0 ]; then
  echo "Please review before deleting:"
  echo
  echo "Domain: $domain"
  echo "CiviCRM DB Prefix: $db_civicrm_prefix"
  echo "Drupal DB Prefix: $db_drupal_prefix"
  echo "WWW Root Directory: $www_rootdir"
  echo
  echo -n "Are you sure that you want to delete instance $instance ([N]/y)? "
  read ch
  case "$ch" in
    [yY]*) ;;
    *) echo "Aborting."; exit 0 ;;
  esac
fi

if [ $db_only -ne 1 ]; then
  instance_dir="$www_rootdir/sites/$instance.$domain"
  echo "Deleting site files for instance [$instance]"
  if [ -d "$instance_dir" ]; then
    ( set -x
      rm -rf "$instance_dir"
    ) || errcode=$(($errcode | 1))
  else
    echo "$prog: $instance_dir: Instance directory not found" >&2
    errcode=$(($errcode | 1))
  fi
fi

if [ $files_only -ne 1 ]; then
  echo "Deleting Drupal database for instance [$instance]"
  ( set -x
    $execSql -c "drop database $db_drupal_prefix$instance"
  ) || errcode=$(($errcode | 2))
  set +x

  echo "Deleting CiviCRM database for instance [$instance]"
  ( set -x
    $execSql -c "drop database $db_civicrm_prefix$instance"
  ) || errcode=$(($errcode | 4))
  set +x
fi

exit $errcode
