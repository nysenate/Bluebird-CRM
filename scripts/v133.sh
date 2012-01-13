#!/bin/sh
#
# v133.sh
#
# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2011-12-28
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
webdir=`$readConfig --global drupal.rootdir` || webdir="$DEFAULT_DRUPAL_ROOTDIR"
base_domain=`$readConfig --ig $instance base.domain` || base_domain="$DEFAULT_BASE_DOMAIN"
formal_name=`$readConfig --ig $instance senator.name.formal` || formal_name="Senator"

###### Begin Upgrade Scripts ######

### Drupal ###

# enable the massmerge module
echo "enable massmerge module for: $instance"
$drush $instance en nyss_massmerge -y


### CiviCRM ###

# 4795 change do not mail
wordreplace="
UPDATE civicrm_domain
SET locale_custom_strings = 'a:1:{s:5:\"en_US\";a:2:{s:7:\"enabled\";a:2:{s:13:\"wildcardMatch\";a:15:{s:7:\"CiviCRM\";s:8:\"Bluebird\";s:9:\"Full-text\";s:13:\"Find Anything\";s:16:\"Addt\'l Address 1\";s:15:\"Mailing Address\";s:16:\"Addt\'l Address 2\";s:8:\"Building\";s:73:\"Supplemental address info, e.g. c/o, department name, building name, etc.\";s:70:\"Department name, building name, complex, or extension of company name.\";s:7:\"deatils\";s:7:\"details\";s:11:\"sucessfully\";s:12:\"successfully\";s:40:\"groups, contributions, memberships, etc.\";s:27:\"groups, relationships, etc.\";s:18:\"email OR an OpenID\";s:5:\"email\";s:6:\"Client\";s:11:\"Constituent\";s:6:\"client\";s:11:\"constituent\";s:9:\"Job title\";s:9:\"Job Title\";s:9:\"Nick Name\";s:8:\"Nickname\";s:8:\"CiviMail\";s:12:\"BluebirdMail\";s:18:\"CiviCase Dashboard\";s:14:\"Case Dashboard\";}s:10:\"exactMatch\";a:7:{s:11:\"Do not mail\";s:18:\"Do not postal mail\";s:8:\"Position\";s:9:\"Job Title\";s:2:\"Id\";s:2:\"ID\";s:6:\"Client\";s:11:\"Constituent\";s:6:\"client\";s:11:\"constituent\";s:10:\"CiviReport\";s:7:\"Reports\";s:8:\"CiviCase\";s:5:\"Cases\";s:12:"Do not trade";s:26:"Undeliverable: Do not mail";}}s:8:\"disabled\";a:2:{s:13:\"wildcardMatch\";a:0:{}s:10:\"exactMatch\";a:0:{}}}}'
WHERE id = 1";
$execSql -i $instance -c "$wordreplace"

# Rebuild the dedupe module
$script_dir/dedupeSetup.sh --rebuild-all $instance

# 4696 Find Mailings menu item
findmail="
SELECT @findm := id FROM civicrm_navigation WHERE name = 'Find Mailings';
SELECT @parent := id FROM civicrm_navigation WHERE name = 'Mass Email';
REPLACE INTO civicrm_navigation (id, domain_id, label, name, url, permission, permission_operator, parent_id, is_active, has_separator, weight) VALUES
(@findm, 1, 'Find Mailings', 'Find Mailings', 'civicrm/mailing/browse&reset=1', 'access CiviMail,approve mailings,create mailings,schedule mailings', 'OR', @parent, 1, 0, 4);";
$execSql -i $instance -c "$findmail"

# 4628 Send to all emails
allemail="ALTER TABLE civicrm_mailing ADD all_emails TINYINT( 4 ) NULL ;"
$execSql -i $instance -c "$allemail"


### Rebuild dedupe shadow tables ###

$script_dir/dedupeSetup.sh $instance --rebuild-all


### Cleanup ###

$script_dir/clearCache.sh $instance
