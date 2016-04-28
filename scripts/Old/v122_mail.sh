#!/bin/sh
#
# v122_mail.sh
#
# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2011-06-14
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

###### Begin Upgrade Scripts ######

### Drupal ###

## disable/enable drupal modules
echo "disabling/enabling modules for: $instance"
$drush $instance dis nyss_mail -y
$drush $instance en nyss_mail -y
$drush $instance en rules_admin -y
$drush $instance en rules_forms -y
$drush $instance dis imce -y

## update permissions
updateperms="UPDATE permission SET perm = 'create users, delete users with role Analytics User, delete users with role Conference Services, delete users with role Data Entry, delete users with role Mailing Approver, delete users with role Mailing Creator, delete users with role Mailing Scheduler, delete users with role Office Administrator, delete users with role Office Manager, delete users with role Print Production, delete users with role SOS, delete users with role Staff, delete users with role Volunteer, edit users with role Analytics User, edit users with role Conference Services, edit users with role Data Entry, edit users with role Mailing Approver, edit users with role Mailing Creator, edit users with role Mailing Scheduler, edit users with role Office Administrator, edit users with role Office Manager, edit users with role Print Production, edit users with role SOS, edit users with role Staff, edit users with role Volunteer, access CiviCRM, access CiviMail, access CiviReport, access Contact Dashboard, access Report Criteria, access all cases and activities, access all custom data, access deleted contacts, access my cases and activities, access uploaded files, add contacts, administer CiviCRM, administer Reports, administer dedupe rules, approve mailings, create mailings, delete activities, delete contacts, delete in CiviCase, delete in CiviMail, edit all contacts, edit groups, import contacts, merge duplicate contacts, profile listings, profile listings and forms, profile view, schedule mailings, view all activities, view all contacts, delete contacts permanently, export print production files, assign roles, access administration pages, administer users' WHERE rid = 4;
UPDATE permission SET perm = 'approve mailings' WHERE rid = 16;
UPDATE permission SET perm = 'create mailings, delete in CiviMail' WHERE rid = 14;
UPDATE permission SET perm = 'schedule mailings' WHERE rid = 15;
UPDATE permission SET perm = 'create users, delete users with role Analytics User, delete users with role Conference Services, delete users with role Data Entry, delete users with role Office Manager, delete users with role Print Production, delete users with role SOS, delete users with role Staff, delete users with role Volunteer, edit users with role Analytics User, edit users with role Conference Services, edit users with role Data Entry, edit users with role Office Manager, edit users with role Print Production, edit users with role SOS, edit users with role Staff, edit users with role Volunteer, access CiviCRM, access CiviReport, access Contact Dashboard, access Report Criteria, access all cases and activities, access all custom data, access deleted contacts, access my cases and activities, access uploaded files, add contacts, administer CiviCRM, administer Reports, delete activities, delete contacts, delete in CiviCase, delete in CiviMail, edit all contacts, edit groups, import contacts, merge duplicate contacts, profile listings, profile listings and forms, profile view, view all activities, view all contacts, delete contacts permanently, assign roles, access administration pages, administer users' WHERE rid = 9;
UPDATE permission SET perm = 'access CiviCRM, access CiviReport, access Contact Dashboard, access Report Criteria, access all cases and activities, access all custom data, access deleted contacts, access my cases and activities, access uploaded files, add contacts, administer Reports, delete activities, delete contacts, delete in CiviCase, delete in CiviMail, edit all contacts, edit groups, import contacts, profile listings, profile listings and forms, profile view, view all activities, view all contacts' WHERE rid = 10;"
$execSql -i $instance -c "$updateperms" --drupal

## set role assign values
roleassign="UPDATE variable SET value = 'a:14:{i:8;i:8;i:5;i:5;i:12;i:12;i:16;i:16;i:14;i:14;i:15;i:15;i:9;i:9;i:10;i:10;i:7;i:7;i:6;i:6;i:11;i:11;i:13;i:13;i:4;i:0;i:3;i:0;}' WHERE name = 'roleassign_roles';"
$execSql -i $instance -c "$roleassign" --drupal

## setup workflow rule
rules="TRUNCATE TABLE rules_rules;
INSERT INTO rules_rules (name, data) VALUES
('rules_notify_creator_of_approval_rejection', 0x613a393a7b733a353a222374797065223b733a343a2272756c65223b733a343a2223736574223b733a32323a226576656e745f6d61696c696e675f617070726f766564223b733a363a22236c6162656c223b733a33363a224e6f746966792043726561746f72206f6620417070726f76616c2f52656a656374696f6e223b733a373a2223616374697665223b693a313b733a373a2223776569676874223b733a313a2230223b733a31313a222363617465676f72696573223b613a303a7b7d733a373a2223737461747573223b733a363a22637573746f6d223b733a31313a2223636f6e646974696f6e73223b613a303a7b7d733a383a2223616374696f6e73223b613a313a7b693a303b613a353a7b733a373a2223776569676874223b643a303b733a353a2223696e666f223b613a333a7b733a353a226c6162656c223b733a31303a2253656e6420456d61696c223b733a393a22617267756d656e7473223b613a313a7b733a373a226d61696c696e67223b613a323a7b733a343a2274797065223b733a373a226d61696c696e67223b733a353a226c6162656c223b733a31303a2253656e6420456d61696c223b7d7d733a363a226d6f64756c65223b733a31353a224369766943524d204d61696c696e67223b7d733a353a22236e616d65223b733a33393a2272756c65735f616374696f6e5f6369766963726d5f6d61696c696e675f73656e645f656d61696c223b733a393a222373657474696e6773223b613a353a7b733a323a22746f223b733a32323a227b6d61696c696e672e63726561746f72456d61696c7d223b733a343a2266726f6d223b733a34373a2222426c756562697264204d61696c657222203c626c7565626972642e61646d696e406e7973656e6174652e676f763e223b733a373a227375626a656374223b733a35323a225374617475733a207b6d61696c696e672e617070726f76616c5374617475737d20287b6d61696c696e672e7375626a6563747d29223b733a373a226d657373616765223b733a3735353a223c703e54686520666f6c6c6f77696e6720656d61696c20686173206265656e203c7374726f6e673e7b6d61696c696e672e617070726f76616c5374617475737d3c2f7374726f6e673e3a207b6d61696c696e672e6e616d657d3c2f703e0d0a0d0a3c703e54686520666f6c6c6f77696e6720656d61696c20617070726f76616c2f72656a656374696f6e206d65737361676520686173206265656e20696e636c756465643a3c6272202f3e0d0a7b6d61696c696e672e617070726f76616c4e6f74657d3c2f703e0d0a0d0a3c703e49662074686520656d61696c2077617320617070726f7665642c20796f752068617665206e6f206675727468657220737465707320746f2074616b652e2054686520656d61696c2077696c6c20656e74657220746865206d61696c696e6720717565756520616e642062652064656c6976657265642073686f72746c792e204e6f7465207468617420656d61696c73206d617920657870657269656e636520736f6d652064656c6179206261736564206f6e207468652073697a65206f662074686520656d61696c20616e6420766f6c756d65206f6620726563697069656e74732e3c2f703e0d0a0d0a3c703e49662074686520656d61696c207761732072656a65637465642c20796f752077696c6c2066696e6420697420696e20426c75656269726420756e6465722074686520647261667420656d61696c206d616e6167656d656e7420706167652e20596f752063616e2072657669657720616e64206564697420746865206d61696c20686572653a207b6d61696c696e672e6564697455726c7d2e204f6e636520796f7527766520757064617465642074686520656d61696c20796f752077696c6c206e65656420746f2072657363686564756c6520697420616e64207375626d697420666f7220617070726f76616c2e3c2f703e0d0a0d0a3c703e54686520636f6e74656e74206f662074686520656d61696c2069733a3c2f703e0d0a3c6469763e0d0a7b6d61696c696e672e68746d6c7d0d0a3c2f6469763e223b733a31333a2223617267756d656e74206d6170223b613a313a7b733a373a226d61696c696e67223b733a373a226d61696c696e67223b7d7d733a353a222374797065223b733a363a22616374696f6e223b7d7d7d);"
$execSql -i $instance -c "$rules" --drupal


### CiviCRM ###

## update navigation items
navigation="UPDATE civicrm_navigation SET label = 'Mass Email', name = 'Mass Email', permission = 'access CiviCRM' WHERE id = 234;
UPDATE civicrm_navigation SET label = 'New Mass Email', name = 'New Mass Email', permission = 'access CiviMail,create mailings,schedule mailings' WHERE id = 235;
UPDATE civicrm_navigation SET label = 'Draft and Unscheduled Emails', name = 'Draft and Unscheduled Emails' WHERE id = 236;
UPDATE civicrm_navigation SET label = 'Scheduled and Sent Emails', name = 'Scheduled and Sent Emails' WHERE id = 237;
UPDATE civicrm_navigation SET label = 'Archived Emails', name = 'Archived Emails', has_separator = 1 WHERE id = 238;
DELETE FROM civicrm_navigation WHERE id = 239;
UPDATE civicrm_navigation SET label = 'Mass Email Bounce Report', name = 'Mass Email Bounce Report', permission = 'access CiviReport', weight = 6, is_active = 0 WHERE id = 240;
UPDATE civicrm_navigation SET label = 'Mass Email Summary Report', name = 'Mass Email Summary Report', permission = 'access CiviReport', weight = 5, is_active = 0 WHERE id = 241;
UPDATE civicrm_navigation SET label = 'Mass Email Opened Report', name = 'Mass Email Opened Report', permission = 'access CiviReport', weight = 7, is_active = 0 WHERE id = 242;
UPDATE civicrm_navigation SET label = 'Mass Email Clickthrough Report', name = 'Mass Email Clickthrough Report', permission = 'access CiviReport', weight = 8, is_active = 0 WHERE id = 243;"
$execSql -i $instance -c "$navigation"

## create base group for mailings
basegroup="INSERT INTO civicrm_group (name, title, description, source, saved_search_id, is_active, visibility, where_clause, select_tables, where_tables, group_type, cache_date, parents, children, is_hidden) VALUES
('Bluebird_Mail_Subscription', 'Bluebird Mail Subscription', NULL, NULL, NULL, 1, 'User and User Admin Only', NULL, NULL, NULL, '2', NULL, NULL, NULL, 1);"
$execSql -i $instance -c "$basegroup"

## strip CiviCRM from report header column
report="UPDATE civicrm_report_instance SET header = '<html>
  <head>
    <title>Bluebird Report</title>
    <style type=\"text/css\">@import url(/sites/all/modules/civicrm/css/print.css);</style>
  </head>
  <body><div id=\"crm-container\">', footer = '<p>New York State Senate :: BlueBird</p></div></body>
</html>';"
$execSql -i $instance -c "$report"

## alter mailing report instance names
reportinstance="UPDATE civicrm_report_instance SET title = 'Mass Email Bounce Report' WHERE title = 'Mail Bounce Report';
UPDATE civicrm_report_instance SET title = 'Mass Email Summary Report' WHERE title = 'Mail Summary Report';
UPDATE civicrm_report_instance SET title = 'Mass Email Opened Report' WHERE title = 'Mail Opened Report';
UPDATE civicrm_report_instance SET title = 'Mass Email Clickthrough Report' WHERE title = 'Mail Clickthrough Report';"
$execSql -i $instance -c "$reportinstance"

## reset bounce report
bounce="UPDATE civicrm_report_instance SET permission = 'access CiviReport',form_values = 'a:60:{s:6:\"fields\";a:5:{s:2:\"id\";s:1:\"1\";s:10:\"first_name\";s:1:\"1\";s:9:\"last_name\";s:1:\"1\";s:13:\"bounce_reason\";s:1:\"1\";s:5:\"email\";s:1:\"1\";}s:12:\"sort_name_op\";s:3:\"has\";s:15:\"sort_name_value\";s:0:\"\";s:9:\"source_op\";s:3:\"has\";s:12:\"source_value\";s:0:\"\";s:6:\"id_min\";s:0:\"\";s:6:\"id_max\";s:0:\"\";s:5:\"id_op\";s:3:\"lte\";s:8:\"id_value\";s:0:\"\";s:15:\"mailing_name_op\";s:2:\"in\";s:18:\"mailing_name_value\";a:0:{}s:19:\"bounce_type_name_op\";s:2:\"eq\";s:22:\"bounce_type_name_value\";s:0:\"\";s:6:\"gid_op\";s:2:\"in\";s:9:\"gid_value\";a:0:{}s:8:\"tagid_op\";s:2:\"in\";s:11:\"tagid_value\";a:0:{}s:12:\"custom_16_op\";s:3:\"has\";s:15:\"custom_16_value\";s:0:\"\";s:12:\"custom_17_op\";s:2:\"eq\";s:15:\"custom_17_value\";s:0:\"\";s:12:\"custom_18_op\";s:2:\"eq\";s:15:\"custom_18_value\";s:0:\"\";s:12:\"custom_19_op\";s:2:\"eq\";s:15:\"custom_19_value\";s:0:\"\";s:12:\"custom_20_op\";s:3:\"has\";s:15:\"custom_20_value\";s:0:\"\";s:12:\"custom_23_op\";s:2:\"in\";s:15:\"custom_23_value\";a:0:{}s:18:\"custom_24_relative\";s:1:\"0\";s:14:\"custom_24_from\";s:0:\"\";s:12:\"custom_24_to\";s:0:\"\";s:12:\"custom_42_op\";s:2:\"in\";s:15:\"custom_42_value\";a:0:{}s:12:\"custom_45_op\";s:3:\"has\";s:15:\"custom_45_value\";s:0:\"\";s:12:\"custom_58_op\";s:4:\"mhas\";s:15:\"custom_58_value\";a:0:{}s:12:\"custom_60_op\";s:2:\"in\";s:15:\"custom_60_value\";a:0:{}s:12:\"custom_61_op\";s:2:\"in\";s:15:\"custom_61_value\";a:0:{}s:12:\"custom_62_op\";s:3:\"has\";s:15:\"custom_62_value\";s:0:\"\";s:12:\"custom_63_op\";s:3:\"has\";s:15:\"custom_63_value\";s:0:\"\";s:12:\"custom_25_op\";s:3:\"has\";s:15:\"custom_25_value\";s:0:\"\";s:12:\"custom_26_op\";s:3:\"has\";s:15:\"custom_26_value\";s:0:\"\";s:12:\"custom_41_op\";s:2:\"in\";s:15:\"custom_41_value\";a:0:{}s:11:\"description\";s:26:\"Bounce Report for mailings\";s:13:\"email_subject\";s:0:\"\";s:8:\"email_to\";s:0:\"\";s:8:\"email_cc\";s:0:\"\";s:10:\"permission\";s:17:\"access CiviReport\";s:6:\"groups\";s:0:\"\";s:6:\"charts\";s:0:\"\";s:9:\"domain_id\";i:1;}',is_active = NULL,email_subject = NULL,email_to = NULL,email_cc = NULL,header = '<html>\r\n  <head>\r\n    <title>Bluebird Report</title>\r\n    <style type=\"text/css\">@import url(/sites/all/modules/civicrm/css/print.css);</style>\r\n  </head>\r\n  <body><div id=\"crm-container\">',footer = '<p>New York State Senate :: BlueBird</p></div></body>\r\n</html>',navigation_id = 240 WHERE id = 27;"
$execSql -i $instance -c "$bounce"

## mail report perms
mailreport="UPDATE civicrm_report_instance SET permission = 'access CiviReport', navigation_id = 241 WHERE id = 28;
UPDATE civicrm_report_instance SET permission = 'access CiviReport', navigation_id = 242 WHERE id = 29;
UPDATE civicrm_report_instance SET permission = 'access CiviReport', navigation_id = 243 WHERE id = 30;"
$execSql -i $instance -c "$mailreport"

## 3866 rename mailing list group type
grouptype="UPDATE civicrm_option_value SET label = 'Email List' WHERE label = 'Mailing List' AND option_group_id = 20;"
$execSql -i $instance -c "$grouptype"


### Cleanup ###

$script_dir/fixPermissions.sh
$script_dir/clearCache.sh $instance
