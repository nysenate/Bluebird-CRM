#!/bin/sh
#
# setupCiviMail.sh
#
# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2011-05-27
# Revised: 2011-05-27
# Setup users and config for CiviMail testing
#

prog=`basename $0`
script_dir=`dirname $0`
readConfig=$script_dir/readConfig.sh
drush=$script_dir/drush.sh
clear_all=0

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog instanceName" >&2
}

if [ $# -lt 1 ]; then
  usage
  exit 1
fi

while [ $# -gt 0 ]; do
  case "$1" in
    -*) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
    *) instance="$1" ;;
  esac
  shift
done

if [ ! "$instance" ]; then
  echo "$prog: Must specify an instance to process" >&2
  usage
  exit 1
fi

## change ldap setting to allow local users
$drush $instance vset --yes ldapauth_login_process 0 

## create drupal test users
$drush $instance user-create mailing_creator   --mail="nyss.mailing.creator@gmail.com"   --password="create"
$drush $instance user-create mailing_scheduler --mail="nyss.mailing.scheduler@gmail.com" --password="schedule"
$drush $instance user-create mailing_approver  --mail="nyss.mailing.approver@gmail.com"  --password="approve"
$drush $instance user-create mailing_cron      --mail="nyss.mailing.bounce@gmail.com"    --password="cronsend"

## assign roles to users
$drush $instance user-add-role "Mailing Creator"   mailing_creator
$drush $instance user-add-role "Staff"             mailing_creator
$drush $instance user-add-role "Mailing Scheduler" mailing_scheduler
$drush $instance user-add-role "Staff"             mailing_scheduler
$drush $instance user-add-role "Mailing Approver"  mailing_approver
$drush $instance user-add-role "Staff"             mailing_approver
$drush $instance user-add-role "Administrator"     mailing_cron

exit 0
