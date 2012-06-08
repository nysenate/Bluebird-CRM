#!/bin/sh
#
# v136.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2012-05-12
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
drush=$script_dir/drush.sh

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
log_db_prefix=`$readConfig --ig $instance db.log.prefix` || log_db_prefix="$DEFAULT_BASE_DOMAIN"

###### Begin Upgrade Scripts ######

### Drupal ###

# 5253 remove create users perm
roles="UPDATE permission SET perm = REPLACE(perm, 'create users, ', '') WHERE rid IN (4,9);"
$execSql -i $instance -c "$roles" --drupal

# 5303 add print prod staff role and amend existing pp role
ppsrole="INSERT INTO role (rid, name) VALUES (18, 'Print Production Staff');"
$execSql -i $instance -c "$ppsrole" --drupal

ppsperm="INSERT INTO permission (rid, perm, tid) SELECT 18, perm, tid FROM permission WHERE rid = 7;"
$execSql -i $instance -c "$ppsperm" --drupal

pprole="UPDATE permission SET perm = 'access CiviCRM, access CiviReport, access all custom data, edit groups, import contacts, profile listings, profile view, view all contacts, administer reserved groups, export print production files, import print production, administer site configuration' WHERE rid = 7;"
$execSql -i $instance -c "$pprole" --drupal

ppnav="UPDATE civicrm_navigation SET permission = REPLACE(permission, 'export print production files', 'import print production') WHERE url = 'importData';"
$execSql -i $instance -c "$ppnav"


### CiviCRM ###

## 4911/5251 create the civicrm_import_jobs table
impjobs="CREATE TABLE IF NOT EXISTS civicrm_importer_jobs (
      id int(10) unsigned NOT NULL AUTO_INCREMENT,
      name varchar(255) NOT NULL,
      table_name varchar(255) NOT NULL,
      source_file varchar(255) NOT NULL,
      file_type varchar(255) NOT NULL,
      field_separator varchar(10) NOT NULL,
      contact_group_id int(10) unsigned NOT NULL,
      created_on timestamp NULL DEFAULT NULL,
      created_by int(10) unsigned NOT NULL,
      PRIMARY KEY (id),
      KEY name (name)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;"
$execSql -i $instance -c "$impjobs"

impjobslog="CREATE TABLE IF NOT EXISTS log_civicrm_importer_jobs (
      id int(10) unsigned NOT NULL,
      name varchar(255) NOT NULL,
      table_name varchar(255) NOT NULL,
      source_file varchar(255) NOT NULL,
      file_type varchar(255) NOT NULL,
      field_separator varchar(10) NOT NULL,
      contact_group_id int(10) unsigned NOT NULL,
      created_on timestamp NULL DEFAULT NULL,
      created_by int(10) unsigned NOT NULL,
      log_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      log_conn_id INTEGER,
      log_user_id INTEGER,
      log_action  ENUM('Initialization', 'Insert', 'Update', 'Delete'),
      log_job_id VARCHAR (64) null
    ) ENGINE=ARCHIVE DEFAULT CHARSET=utf8;"
$execSql -i $instance -c "$impjobslog" --log

## 4718 ##
maildetail="
SELECT @option_group_id_report := MAX(id)     FROM civicrm_option_group WHERE name = 'report_template';
SELECT @weight                 := MAX(weight) FROM civicrm_option_value WHERE option_group_id = @option_group_id_report;
SELECT @mailCompId             := MAX(id)     FROM civicrm_component where name = 'CiviMail';
INSERT INTO civicrm_option_value
(option_group_id, label, value, name, weight, description, is_active, component_id) VALUES
(@option_group_id_report, 'Mass Email Detail Report', 'mailing/detail', 'CRM_Report_Form_Mailing_Detail', @weight := @weight + 1, 'Provides reporting on Intended and Successful Deliveries, Unsubscribes and Opt-outs, Replies and Forwards.', 1, @mailCompId);

INSERT INTO civicrm_report_instance
( domain_id, title, report_id, description, permission, form_values)
VALUES 
( 1, 'Mass Email Detail Report', 'mailing/detail', 'Provides reporting on Intended and Successful Deliveries, Unsubscribes and Opt-outs, Replies and Forwards.', 'access CiviReport', 'a:30:{s:6:\"fields\";a:6:{s:9:\"sort_name\";s:1:\"1\";s:12:\"mailing_name\";s:1:\"1\";s:11:\"delivery_id\";s:1:\"1\";s:14:\"unsubscribe_id\";s:1:\"1\";s:9:\"optout_id\";s:1:\"1\";s:5:\"email\";s:1:\"1\";}s:12:\"sort_name_op\";s:3:\"has\";s:15:\"sort_name_value\";s:0:\"\";s:6:\"id_min\";s:0:\"\";s:6:\"id_max\";s:0:\"\";s:5:\"id_op\";s:3:\"lte\";s:8:\"id_value\";s:0:\"\";s:13:\"mailing_id_op\";s:2:\"in\";s:16:\"mailing_id_value\";a:0:{}s:18:\"delivery_status_op\";s:2:\"eq\";s:21:\"delivery_status_value\";s:0:\"\";s:18:\"is_unsubscribed_op\";s:2:\"eq\";s:21:\"is_unsubscribed_value\";s:0:\"\";s:12:\"is_optout_op\";s:2:\"eq\";s:15:\"is_optout_value\";s:0:\"\";s:13:\"is_replied_op\";s:2:\"eq\";s:16:\"is_replied_value\";s:0:\"\";s:15:\"is_forwarded_op\";s:2:\"eq\";s:18:\"is_forwarded_value\";s:0:\"\";s:6:\"gid_op\";s:2:\"in\";s:9:\"gid_value\";a:0:{}s:9:\"order_bys\";a:1:{i:1;a:2:{s:6:\"column\";s:9:\"sort_name\";s:5:\"order\";s:3:\"ASC\";}}s:11:\"description\";s:21:\"Mailing Detail Report\";s:13:\"email_subject\";s:0:\"\";s:8:\"email_to\";s:0:\"\";s:8:\"email_cc\";s:0:\"\";s:10:\"permission\";s:1:\"0\";s:9:\"parent_id\";s:0:\"\";s:6:\"groups\";s:0:\"\";s:9:\"domain_id\";i:1;}');

SELECT @reportlastID       := MAX(id) FROM civicrm_navigation where name = 'Mass Email';
SELECT @nav_max_weight     := MAX(ROUND(weight)) from civicrm_navigation WHERE parent_id = @reportlastID;

SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
( 1, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'), 'Mass Email Detail Report', 'Mass Email Detail Report', 'administer CiviMail', 'OR', @reportlastID, '1', NULL, @nav_max_weight+1 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;"

$execSql -i $instance -c "$maildetail"

## 5304 pdf page option ##
pdfpage="
SELECT @optGroup := id FROM civicrm_option_group WHERE name = 'pdf_format';
INSERT INTO civicrm_option_value (option_group_id, label, value, name, grouping, filter, is_default, weight, description, is_optgroup, is_reserved, is_active, component_id, domain_id, visibility_id) 
VALUES
(@optGroup, 'Letter Landscape', '{\"paper_size\":\"letter\",\"orientation\":\"landscape\",\"metric\":\"in\",\"margin_top\":0.5,\"margin_bottom\":0.5,\"margin_left\":0.5,\"margin_right\":0.5}', 'Letter Landscape', NULL, NULL, NULL, 1, NULL, NULL, NULL, 1, NULL, NULL, NULL);"

$execSql -i $instance -c "$pdfpage"

## fix log group is_reserved field
fixLog="ALTER TABLE log_civicrm_group ADD is_reserved TINYINT( 4 ) NOT NULL DEFAULT '0' AFTER is_hidden"
$execSql -i $instance -c "$fixLog" --log


### Cleanup ###

$script_dir/clearCache.sh $instance
