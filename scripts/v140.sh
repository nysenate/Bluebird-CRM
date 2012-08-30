#!/bin/sh
#
# v140.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2012-08
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
civi_db_prefix=`$readConfig --ig $instance db.civicrm.prefix` || civi_db_prefix="$DEFAULT_BASE_DOMAIN"
cdb="$civi_db_prefix$db_basename"

###### Begin Upgrade Scripts ######

### Drupal ###

## manually disable various modules before running drupal upgrade
dismods="
UPDATE system
SET status = 0
WHERE name IN
  ('civicrm', 'userprotect', 'civicrm_rules', 'rules', 'rules_admin', 'apachesolr', 'apachesolr_search', 'color',
  'comment', 'help', 'taxonomy', 'update', 'admin_menu', 'imce', 'nyss_403', 'nyss_backup', 'nyss_boe',
  'nyss_dashboards', 'nyss_dedupe', 'nyss_export', 'nyss_import', 'nyss_io', 'nyss_mail', 'nyss_massmerge',
  'nyss_sage', 'nyss_tags', 'nyss_testing', 'nyss_civihooks');"
$execSql -i $instance -c "$dismods" --drupal

## run drupal upgrade
$drush $instance updb -y

## enable modules
echo "enabling modules for: $instance"
$drush $instance en userprotect -y
$drush $instance en rules -y
$drush $instance en rules_admin -y
$drush $instance en apachesolr -y
$drush $instance en apachesolr_search -y
$drush $instance en ldap_servers -y
$drush $instance en ldap_authorization -y

## set theme
echo "setting theme for: $instance"
$drush $instance en Bluebird -y
$drush $instance vset theme_default Bluebird

## update front page module settings
front="
UPDATE variable SET value = 0x693a313b
 WHERE name = 'front_page_enable';
UPDATE variable SET value = 0x733a303a22223b
 WHERE name = 'front_page_home_link_path';
UPDATE variable SET value = 0x733a32353a226369766963726d2f64617368626f6172643f72657365743d31223b
 WHERE name = 'site_frontpage';"
$execSql -i $instance -c "$front" --drupal

### CiviCRM ###

## disable logging and run civicrm upgrade
## php ../civicrm/scripts/disableLogging.php -S $instance
echo "disabling logging manually..."

triggersql="
SELECT trigger_name
FROM information_schema.triggers
WHERE trigger_schema = '$cdb'
AND trigger_name LIKE 'civicrm_%';"
triggers=`$execSql -c "$triggersql"`
echo "Triggers to be dropped: " $triggers

for trigger in $triggers; do
  $execSql -i $instance -c "DROP TRIGGER IF EXISTS $trigger"
done

logging="
DROP TRIGGER IF EXISTS civicrm_domain_after_update;
DROP TRIGGER IF EXISTS civicrm_preferences_after_update;
DROP TRIGGER IF EXISTS civicrm_preferences_date_after_update;
DROP TRIGGER IF EXISTS civicrm_option_value_after_update;
DROP TRIGGER IF EXISTS civicrm_option_value_after_insert;
DROP TRIGGER IF EXISTS civicrm_uf_match_after_update;
DROP TRIGGER IF EXISTS civicrm_uf_match_after_insert;
DROP TRIGGER IF EXISTS civicrm_group_after_update;
DROP TRIGGER IF EXISTS civicrm_group_after_insert;
DROP TRIGGER IF EXISTS civicrm_menu_after_update;
DROP TRIGGER IF EXISTS civicrm_menu_after_insert;
UPDATE civicrm_domain
   SET config_backend = REPLACE(config_backend, '\"logging\";i:1;', '\"logging\";i:0;')
   WHERE id = 1;
"
$execSql -i $instance -c "$logging"

## upgrade civicrm db
echo "running civicrm db upgrade..."
$drush $instance civicrm-upgrade-db

## enable civicrm modules
$drush $instance en civicrm -y
$drush $instance en civicrm_rules -y
$drush $instance en nyss_403 -y
$drush $instance en nyss_backup -y
$drush $instance en nyss_boe -y
$drush $instance en nyss_dashboards -y
$drush $instance en nyss_dedupe -y
$drush $instance en nyss_export -y
$drush $instance en nyss_import -y
$drush $instance en nyss_io -y
$drush $instance en nyss_mail -y
$drush $instance en nyss_massmerge -y
$drush $instance en nyss_sage -y
$drush $instance en nyss_tags -y

## move some newly added menu items
navigation="
SELECT @admin := id FROM civicrm_navigation WHERE name = 'Administer';
UPDATE civicrm_navigation SET parent_id = @admin WHERE name = 'Batches' AND parent_id IS NULL;
UPDATE civicrm_navigation SET is_active = 0 WHERE name = 'New SMS';
UPDATE civicrm_navigation SET parent_id = @admin WHERE name = 'New SMS' AND parent_id IS NULL;
UPDATE civicrm_navigation SET parent_id = @admin WHERE name = 'CiviMail Component Settings' AND parent_id IS NULL;
UPDATE civicrm_navigation SET parent_id = @admin WHERE name = 'CiviEvent Component Settings' AND parent_id IS NULL;
UPDATE civicrm_navigation SET parent_id = @admin WHERE name = 'CiviMember Component Settings' AND parent_id IS NULL;
UPDATE civicrm_navigation SET parent_id = @admin WHERE name = 'Event Badge Formats' AND parent_id IS NULL;
UPDATE civicrm_navigation SET parent_id = @admin WHERE name = 'Personal Campaign Pages' AND parent_id IS NULL;
"
$execSql -i $instance -c "$navigation"

## change settings for district info data set
distinfo="UPDATE civicrm_custom_group SET collapse_display = 0 WHERE name = 'District_Information';"
$execSql -i $instance -c "$distinfo"


### Cleanup ###

$script_dir/clearCache.sh $instance
