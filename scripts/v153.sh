#!/bin/sh
#
# v153.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2014-03-24
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

app_rootdir=`$readConfig --ig $instance app.rootdir` || app_rootdir="$DEFAULT_APP_ROOTDIR"

#echo "upgrade CiviCRM core to v4.4.4..."
#$drush $instance civicrm-upgrade-db

echo "5581: create fields for mailing subscription..."
sql="
  ALTER TABLE `civicrm_email` ADD `mailing_categories` VARCHAR(765) NULL DEFAULT NULL, ADD INDEX (`mailing_categories`);
  ALTER TABLE `civicrm_mailing` ADD `category` VARCHAR(255) NULL DEFAULT NULL, ADD INDEX (`category`);
  DELETE FROM `civicrm_option_group` WHERE name = 'Mailing Categories';
  INSERT INTO `civicrm_option_group` (`name`, `title`, `description`, `is_reserved`, `is_active`)
    VALUES ('mailing_categories', 'Mailing Categories', 'Mailing Categories', '1', '1');
  SELECT @optGrp:=id FROM civicrm_option_group WHERE name = 'mailing_categories';
  INSERT INTO `civicrm_option_value`
    (`option_group_id`, `label`, `value`, `name`, `grouping`, `filter`, `is_default`, `weight`, `description`, `is_optgroup`, `is_reserved`, `is_active`, `component_id`, `domain_id`, `visibility_id`)
    VALUES (@optGrp, 'Newsletter', '1', 'Newsletter', NULL, '0', NULL, '1', '', '0', '1', '1', NULL, NULL, NULL),
      (@optGrp, 'Legislative Alert', '2', 'Legislative Alert', NULL, '0', NULL, '2', '', '0', '1', '1', NULL, NULL, NULL),
      (@optGrp, 'Local News', '3', 'Local News', NULL, '0', NULL, '3', '', '0', '1', '1', NULL, NULL, NULL);
"
$execSql $instance -c "$sql" -q
