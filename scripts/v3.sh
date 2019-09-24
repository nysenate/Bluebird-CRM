#!/bin/sh
#
# v3.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2018-06-26
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
drush=$script_dir/drush.sh
readConfig=$script_dir/readConfig.sh

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

## set new default theme
echo "set default theme to BluebirdSeven..."
$drush $instance pm-enable BluebirdSeven -y
$drush $instance vset theme_default BluebirdSeven -y
$drush $instance vset admin_theme BluebirdSeven -y

## enable menu/admin modules
echo "enable menu/admin modules..."
$drush $instance pm-enable admin_menu -y
$drush $instance pm-enable adminimal_admin_menu -y
$drush $instance pm-enable module_filter -y
$drush $instance pm-enable chain_menu_access -y
$drush $instance pm-enable ldap_user -y
$drush $instance pm-enable ctools -y

## install extensions
echo "$prog: install extensions"
$drush $instance cvapi extension.install key=gov.nysenate.contact --quiet
$drush $instance cvapi extension.install key=gov.nysenate.deceased --quiet
$drush $instance cvapi extension.install key=gov.nysenate.inlinehelp --quiet
$drush $instance cvapi extension.install key=gov.nysenate.navigation --quiet
$drush $instance cvapi extension.install key=gov.nysenate.recentitems --quiet
$drush $instance cvapi extension.install key=gov.nysenate.search --quiet
$drush $instance cvapi extension.install key=gov.nysenate.dashboard --quiet

$drush $instance cvapi extension.install key=org.civicrm.angularprofiles --quiet
$drush $instance cvapi extension.install key=org.civicrm.api4 --quiet
#$drush $instance cvapi extension.install key=org.civicrm.civicase --quiet
$drush $instance cvapi extension.install key=org.civicrm.contactlayout --quiet
$drush $instance cvapi extension.install key=org.civicrm.districtstats --quiet
$drush $instance cvapi extension.install key=org.civicrm.doctorwhen --quiet
##$drush $instance cvapi extension.install key=org.civicrm.flexmailer --quiet
$drush $instance cvapi extension.install key=org.civicrm.shoreditch --quiet
$drush $instance cvapi extension.install key=org.civicrm.tutorial --quiet

##$drush $instance cvapi extension.install key=uk.co.vedaconsulting.mosaico --quiet

$drush $instance cvapi DoctorWhen.run tasks=* --quiet

## upgrade drupal db
echo "running drupal db upgrade..."
$drush $instance updb -y -q

## upgrade civicrm db
echo "running civicrm db upgrade..."
$drush $instance civicrm-upgrade-db -y -q

## configure blocks for BluebirdSeven theme
echo "$prog: configure blocks for BluebirdSeven theme"
sql="
  UPDATE block
  SET status = 1, region = 'content'
  WHERE module = 'user'
    AND delta = 'login'
    AND theme = 'BluebirdSeven';
  UPDATE block
  SET status = 0
  WHERE module = 'civicrm'
    AND theme = 'BluebirdSeven'
"
$execSql -i $instance -c "$sql" --drupal -q

## adjust custom field settings
echo "$prog: set file attachments to default open"
sql="
  UPDATE civicrm_custom_group
  SET collapse_display = 0
  WHERE name = 'Attachments';
"
$execSql $instance -c "$sql" -q

## DISABLE: some problem with this change...
echo "$prog: update location type display labels"
sql="
  UPDATE civicrm_location_type
  SET display_name = 'Home 2'
  WHERE name = 'Home2';
  UPDATE civicrm_location_type
  SET display_name = 'Home 2'
  WHERE name = 'Home2';
  UPDATE civicrm_location_type
  SET display_name = 'Home 2'
  WHERE name = 'Home2';
  UPDATE civicrm_location_type
  SET display_name = 'Home 2'
  WHERE name = 'Home2';
"
#$execSql $instance -c "$sql" -q

## TODO implement contact-summary config

## 8439 cleanup safe file extensions; add new options
echo "$prog: cleanup safe file extensions; add new options"
sql="
   SELECT @optgrp:=id FROM civicrm_option_group WHERE name = 'safe_file_extension';
   INSERT INTO civicrm_option_value (option_group_id, label, value, name, filter, weight, is_active)
   VALUES (@optgrp, 'mp3', '16', 'mp3', 0, 16, 1),
     (@optgrp, 'wav', '17', 'wav', 0, 17, 1),
     (@optgrp, 'accdb', '18', 'accdb', 0, 18, 1),
     (@optgrp, 'one', '19', 'one', 0, 19, 1),
     (@optgrp, 'pptx', '20', 'pptx', 0, 20, 1),
     (@optgrp, 'pub', '21', 'pub', 0, 21, 1),
     (@optgrp, 'xsf', '22', 'xsf', 0, 22, 1),
     (@optgrp, '123', '23', '123', 0, 23, 1),
     (@optgrp, 'lwp', '24', 'lwp', 0, 24, 1),
     (@optgrp, 'apr', '25', 'apr', 0, 25, 1),
     (@optgrp, 'html', '26', 'html', 0, 26, 1),
     (@optgrp, '3gp', '27', '3gp', 0, 27, 1),
     (@optgrp, 'avi', '28', 'avi', 0, 28, 1),
     (@optgrp, 'm4v', '29', 'm4v', 0, 29, 1),
     (@optgrp, 'mp4', '30', 'mp4', 0, 30, 1),
     (@optgrp, 'mpeg', '31', 'mpeg', 0, 31, 1),
     (@optgrp, 'wma', '32', 'wma', 0, 32, 1),
     (@optgrp, 'wmv', '33', 'wmv', 0, 33, 1),
     (@optgrp, 'flv', '34', 'flv', 0, 34, 1),
     (@optgrp, 'psd', '35', 'psd', 0, 35, 1),
     (@optgrp, 'tif', '36', 'tif', 0, 36, 1);
   DELETE ov1
   FROM civicrm_option_value ov1
   INNER JOIN civicrm_option_value ov2
   WHERE ov1.id < ov2.id
     AND ov1.name = ov2.name
     AND ov1.option_group_id = @optgrp
     AND ov2.option_group_id = @optgrp;
"
$execSql $instance -c "$sql" -q

## set default state
echo "$prog: set default state"
sql="
  INSERT INTO civicrm_setting
  (name, value, domain_id, contact_id, is_domain, component_id, created_id)
  VALUES
  ('defaultContactStateProvince', 's:4:\"1031\";', 1, NULL, 1, NULL, 1);
"
$execSql $instance -c "$sql" -q

## create Test Email List group type
echo "$prog: create Test Email List group type"
sql="
  SELECT @optgrp:=id FROM civicrm_option_group WHERE name = 'group_type';
  DELETE FROM civicrm_option_value
  WHERE option_group_id = @optgrp
    AND name = 'test_email_list';
  INSERT INTO civicrm_option_value
  (option_group_id, label, value, name, grouping, filter, is_default, weight, description, is_optgroup, is_reserved, is_active, component_id, domain_id, visibility_id, icon, color)
  VALUES
  (@optgrp, 'Test Email List', 3, 'test_email_list', NULL, 0, NULL, 3, NULL, 0, 1, 1, NULL, NULL, NULL, NULL, NULL);
"
$execSql $instance -c "$sql" -q

## cleanup group type options
echo "$prog: cleanup group type options"
sql="
  SELECT @optgrp:=id FROM civicrm_option_group WHERE name = 'group_type';
  DELETE ov1 FROM civicrm_option_value ov1
  JOIN civicrm_option_value ov2
  WHERE ov1.option_group_id = @optgrp
    AND ov2.option_group_id = @optgrp
    AND ov1.id < ov2.id
    AND ov1.name = ov2.name;
"
$execSql $instance -c "$sql" -q

## remove news dashlet
echo "$prog: remove news dashlet"
sql="
  SELECT @dashid:=id FROM civicrm_dashboard WHERE name = 'news';
  DELETE FROM civicrm_dashboard_contact WHERE dashboard_id = @dashid;
  DELETE FROM civicrm_dashboard WHERE id = @dashid;
"
$execSql $instance -c "$sql" -q

## non-admin menu + block
echo "$prog: insert non-admin menu and block"
sql="
  DELETE FROM menu_custom WHERE menu_name = 'menu-non-admin-drupal-menu';
  INSERT INTO menu_custom (menu_name, title, description)
    VALUES ('menu-non-admin-drupal-menu', 'Non-Admin Drupal Menu', '');

  DELETE FROM menu_links WHERE menu_name = 'menu-non-admin-drupal-menu';
  INSERT INTO menu_links
    (menu_name, mlid, plid, link_path, router_path, link_title, options, module, hidden, external, has_children, expanded, weight, depth, customized, p1, p2, p3, p4, p5, p6, p7, p8, p9, updated)
    VALUES
    ('menu-non-admin-drupal-menu', 704, 0, 'civicrm', 'civicrm', 'Bluebird Dashboard', 0x613a313a7b733a31303a2261747472696275746573223b613a313a7b733a353a227469746c65223b733a33373a2252657475726e20746f20746865206d61696e20426c7565626972642044617368626f617264223b7d7d, 'menu', 0, 0, 0, 0, 0, 1, 1, 704, 0, 0, 0, 0, 0, 0, 0, 0, 0);

  DELETE FROM block WHERE delta = 'menu-non-admin-drupal-menu';
  INSERT INTO block (module, delta, theme, status, weight, region, custom, visibility, pages, title, cache)\
    VALUES
    ('menu', 'menu-non-admin-drupal-menu', 'BluebirdSeven', 1, 0, 'content', 0, 1, 'admin/people', '<none>', -1);
"
$execSql $instance -c "$sql" --drupal -q

echo "$prog: resetting roles and permissions..."
$script_dir/resetRolePerms.sh $instance

## record completion
echo "$prog: upgrade process is complete."
