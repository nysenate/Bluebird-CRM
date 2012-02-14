#!/bin/sh
#
# manageMailingSettings.sh - Wrapper around manageMailingSettings.php
# based on manageCiviConfig.sh script
#
# Project: BluebirdCRM
# Author: Ken Zalewski, Brian Shaughnessy
# Organization: New York State Senate
# Date: 2011-06-24
#

prog=`basename $0`
script_dir=`dirname $0`
readConfig=$script_dir/readConfig.sh

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--list] [--update-config] [--update-template] [--update-all] [--update-from] [--update-reports] instanceName" >&2
}

if [ $# -lt 1 ]; then
  usage
  exit 1
fi

instance=
civi_op=list

while [ $# -gt 0 ]; do
  case "$1" in
    --list) civi_op=list ;;
    --update-config) civi_op=update-config ;;
    --update-template) civi_op=update-template ;;
    --update-from) civi_op=update-from ;;
    --update-reports) civi_op=update-reports ;;
    --update-all) civi_op=update-all ;;
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

dbhost=`$readConfig --ig $instance db.host` || dbhost="$DEFAULT_DB_HOST"
dbuser=`$readConfig --ig $instance db.user` || dbhost="$DEFAULT_DB_USER"
dbpass=`$readConfig --ig $instance db.pass` || dbhost="$DEFAULT_DB_PASS"
dbciviprefix=`$readConfig --ig $instance db.civicrm.prefix` || dbciviprefix="$DEFAULT_DB_CIVICRM_PREFIX"
dbbasename=`$readConfig -i $instance db.basename` || dbbasename="$instance"
dbname=$dbciviprefix$dbbasename
smtp_host=`$readConfig --ig $instance smtp.host` || smtp_host="$DEFAULT_SMTP_HOST"
smtp_port=`$readConfig --ig $instance smtp.port` || smtp_port="$DEFAULT_SMTP_PORT"
smtp_auth=`$readConfig --ig $instance smtp.auth` || smtp_auth="$DEFAULT_SMTP_AUTH"
smtp_subuser=`$readConfig --ig $instance smtp.subuser` || smtp_subuser="$DEFAULT_SMTP_USER"
smtp_subpass=`$readConfig --ig $instance smtp.subpass` || smtp_subpass="$DEFAULT_SMTP_PASS"
formal_name=`$readConfig --ig $instance senator.name.formal` || formal_name="$DEFAULT_SENATOR_FORMAL_NAME"
email_from=`$readConfig --ig $instance senator.email` || email_from="$DEFAULT_SENATOR_EMAIL_FROM"
email_replyto=`$readConfig --ig $instance senator.email.replyto` || email_replyto="$DEFAULT_SENATOR_EMAIL_REPLYTO"
app_rootdir=`$readConfig --ig $instance app.rootdir` || app_rootdir="$DEFAULT_APP_ROOTDIR"
base_domain=`$readConfig --ig $instance base.domain` || base_domain="$DEFAULT_BASE_DOMAIN"

# Passing a cygwin path to PHP won't work, so expand it to Win32 on Cygwin.
[ "$OSTYPE" = "cygwin" ] && script_dir=`cygpath --mixed $script_dir`

php "$script_dir/manageMailingSettings.php" $civi_op "$dbhost" "$dbuser" "$dbpass" "$dbname" "$smtp_host" "$smtp_port" "$smtp_auth" "$smtp_subuser" "$smtp_subpass" $instance "$formal_name" "$email_from" "$email_replyto" "$instance.$base_domain" "$app_rootdir"
exit $?
