#!/bin/sh
#
# v160.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2016-01-01
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
drush=$script_dir/drush.sh
app_rootdir=`$readConfig --ig $instance app.rootdir` || app_rootdir="$DEFAULT_APP_ROOTDIR"

. $script_dir/defaults.sh

if [ $# -ne 1 ]; then
  echo "Usage: $prog instanceName" >&2
  exit 1
fi

instance="$1"

if ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

data_rootdir=`$readConfig --ig $instance data.rootdir` || data_rootdir="$DEFAULT_DATA_ROOTDIR"
app_rootdir=`$readConfig --ig $instance app.rootdir` || app_rootdir="$DEFAULT_APP_ROOTDIR"
webdir=`$readConfig --global drupal.rootdir` || webdir="$DEFAULT_DRUPAL_ROOTDIR"
base_domain=`$readConfig --ig $instance base.domain` || base_domain="$DEFAULT_BASE_DOMAIN"
db_basename=`$readConfig --ig $instance db.basename` || db_basename="$instance"
civi_db_prefix=`$readConfig --ig $instance db.civicrm.prefix` || civi_db_prefix="$DEFAULT_BASE_DOMAIN"
log_db_prefix=`$readConfig --ig $instance db.log.prefix` || log_db_prefix="$DEFAULT_BASE_DOMAIN"
cdb="$civi_db_prefix$db_basename"
ldb=$log_db_prefix$db_basename;

## upgrade civicrm db
echo "running civicrm db upgrade..."
$drush $instance civicrm-upgrade-db

## fix table column collations throughout
echo "$prog: fix table column collations throughout"
sql="
  ALTER SCHEMA $cdb DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_unicode_ci;
  ALTER TABLE address_abbreviations CONVERT TO CHARACTER SET utf8 COLLATE 'utf8_unicode_ci';
  ALTER TABLE civicrm_importer_jobs CONVERT TO CHARACTER SET utf8 COLLATE 'utf8_unicode_ci';
  ALTER TABLE civicrm_system_log CONVERT TO CHARACTER SET utf8 COLLATE 'utf8_unicode_ci';
  ALTER TABLE fn_group CONVERT TO CHARACTER SET utf8 COLLATE 'utf8_unicode_ci';
  ALTER TABLE fn_group_name CONVERT TO CHARACTER SET utf8 COLLATE 'utf8_unicode_ci';
  ALTER TABLE fn_group_contact CONVERT TO CHARACTER SET utf8 COLLATE 'utf8_unicode_ci';
  ALTER TABLE shadow_address CONVERT TO CHARACTER SET utf8 COLLATE 'utf8_unicode_ci';
  ALTER TABLE shadow_contact CONVERT TO CHARACTER SET utf8 COLLATE 'utf8_unicode_ci';
  ALTER TABLE nyss_schooldistricts CONVERT TO CHARACTER SET utf8 COLLATE 'utf8_unicode_ci';
  ALTER TABLE nyss_web_account CONVERT TO CHARACTER SET utf8 COLLATE 'utf8_unicode_ci';
  ALTER TABLE nyss_web_activity CONVERT TO CHARACTER SET utf8 COLLATE 'utf8_unicode_ci';
"
$execSql $instance -c "$sql" -q

sql="
  ALTER TABLE $ldb.log_civicrm_importer_jobs CONVERT TO CHARACTER SET utf8 COLLATE 'utf8_unicode_ci';
"
$execSql $instance -c "$sql" -q --log

sql="
  DROP TABLE IF EXISTS $ldb.log_civicrm_mailing_event_sendgrid_delivered;
"
$execSql $instance -c "$sql" -q --log

## rebuild shadow table functions
echo "$prog: rebuild shadow table functions"
$execSql $instance -f $script_dir/../modules/nyss_dedupe/shadow_func.sql

## update machine name for case types
echo "$prog: update case type machine names"
sql="
  UPDATE civicrm_case_type SET name = 'request_for_information' WHERE name = 'Request for Information';
  UPDATE civicrm_case_type SET name = 'general_complaint' WHERE name = 'General Complaint';
  UPDATE civicrm_case_type SET name = 'request_for_assistance' WHERE name = 'Request for Assistance';
  UPDATE civicrm_case_type SET name = 'event_invitation' WHERE name = 'Event Invitation';
  UPDATE civicrm_case_type SET name = 'government_service_problem_local' WHERE name = 'Government Service Problem - Local';
  UPDATE civicrm_case_type SET name = 'government_service_problem_state' WHERE name = 'Government Service Problem - State';
  UPDATE civicrm_case_type SET name = 'letter_of_support' WHERE name = 'Letter of Support';
  UPDATE civicrm_case_type SET name = 'other' WHERE name = 'Other';
"
$execSql $instance -c "$sql" -q

## collapse custom group attachments
echo "$prog: collapse custom group attachments"
sql="
  UPDATE civicrm_custom_group SET collapse_display = 1 WHERE name = 'Attachments';
"
$execSql $instance -c "$sql" -q

## enable new nyss_reports module
$drush $instance en nyss_reports -y

## disable various unused activity types
echo "$prog: disable unused activity types"
sql="
  UPDATE civicrm_option_value
  SET is_active = 0
  WHERE component_id = 2
    AND option_group_id = 2
"
$execSql $instance -c "$sql" -q

## 10491 remove dashlet
sql="
  DELETE FROM civicrm_dashboard
  WHERE name = 'getting-started'
"
$execSql $instance -c "$sql" -q

## set default config values for all existing mailings
sql="
  SELECT @reply:=id FROM civicrm_mailing_component WHERE component_type = 'Reply' AND is_default = 1;
  SELECT @unsubscribe:=id FROM civicrm_mailing_component WHERE component_type = 'Unsubscribe' AND is_default = 1;
  SELECT @resubscribe:=id FROM civicrm_mailing_component WHERE component_type = 'Resubscribe' AND is_default = 1;
  SELECT @optout:=id FROM civicrm_mailing_component WHERE component_type = 'OptOut' AND is_default = 1;
  UPDATE civicrm_mailing
  SET reply_id = @reply, unsubscribe_id = @unsubscribe, resubscribe_id = @resubscribe, optout_id = @optout;
"
$execSql $instance -c "$sql" -q

## install gov.nysenate.resources extension
$drush $instance cvapi extension.install key=gov.nysenate.resources

## fix collation
echo "$prog: fix collations"
$script_dir/changeCollation.sh $instance

## rebuild triggers
echo "$prog: rebuild triggers"
php $app_rootdir/civicrm/scripts/rebuildTriggers.php -S $instance
