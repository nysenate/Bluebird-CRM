#!/bin/sh
#
# changeCollation.sh - Wrapper around manageCiviConfig.php
#
# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2011-10-14
#

prog=`basename $0`
script_dir=`dirname $0`
readConfig=$script_dir/readConfig.sh

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog instanceName collation" >&2
}

if [ $# -lt 1 ]; then
  usage
  exit 1
fi

instance=
collation=

while [ $# -gt 0 ]; do
  case "$1" in
    *) instance="$1" ;;
    *) collation="$2" ;;
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

dbhost=`$readConfig --ig $instance db.host` || dbhost="$DEFAULT_DB_HOST"
dbuser=`$readConfig --ig $instance db.user` || dbhost="$DEFAULT_DB_USER"
dbpass=`$readConfig --ig $instance db.pass` || dbhost="$DEFAULT_DB_PASS"
dbciviprefix=`$readConfig --ig $instance db.civicrm.prefix` || dbciviprefix="$DEFAULT_DB_CIVICRM_PREFIX"
dbbasename=`$readConfig -i $instance db.basename` || dbbasename="$instance"
dbname=$dbciviprefix$dbbasename
app_rootdir=`$readConfig --ig $instance app.rootdir` || app_rootdir="$DEFAULT_APP_ROOTDIR"
data_rootdir=`$readConfig --ig $instance data.rootdir` || data_rootdir="$DEFAULT_DATA_ROOTDIR"
base_domain=`$readConfig --ig $instance base.domain` || base_domain="$DEFAULT_BASE_DOMAIN"
inc_email=`$readConfig --ig $instance search.include_email_in_name` || inc_email="$DEFAULT_INCLUDE_EMAIL_IN_NAME"
inc_wildcard=`$readConfig --ig $instance search.include_wildcard_in_name` || inc_wildcard="$DEFAULT_INCLUDE_WILDCARD_IN_NAME"

# Passing a cygwin path to PHP won't work, so expand it to Win32 on Cygwin.
[ "$OSTYPE" = "cygwin" ] && script_dir=`cygpath --mixed $script_dir`

php "$script_dir/changeCollation.php" $collation "$dbhost" "$dbuser" "$dbpass" "$dbname" "$instance.$base_domain" "$app_rootdir" "$data_rootdir" "$inc_email" "$inc_wildcard"
exit $?
