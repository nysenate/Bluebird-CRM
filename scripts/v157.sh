#!/bin/sh
#
# v157.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2015-04-14
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
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

app_rootdir=`$readConfig --ig $instance app.rootdir` || app_rootdir="$DEFAULT_APP_ROOTDIR"

echo "$prog: create website user id field in contacts table"
sql="
  ALTER TABLE civicrm_contact ADD web_user_id INT(10) NULL AFTER modified_date, ADD INDEX index_web_user_id (web_user_id);
"
$execSql $instance -c "$sql" -q

echo "$prog: create issues/committee/bill tagsets"
sql="
  SET FOREIGN_KEY_CHECKS=0;
  DELETE FROM civicrm_tag
  WHERE ( name = 'Website Issues' OR name = 'Website Committees' OR name = 'Website Bills' OR 'Website Petitions' )
    AND is_tagset = 1;
  INSERT INTO civicrm_tag (name, description, parent_id, is_selectable, is_reserved, is_tagset, used_for, created_id, created_date)
  VALUES
    ('Website Issues', 'Tagset for issues generated from nysenate.gov', NULL, 1, 1, 1, 'civicrm_contact', NULL, NULL),
    ('Website Committees', 'Tagset for committees generated from nysenate.gov', NULL, 1, 1, 1, 'civicrm_contact', NULL, NULL),
    ('Website Bills', 'Tagset for bills generated from nysenate.gov', NULL, 1, 1, 1, 'civicrm_contact', NULL, NULL),
    ('Website Petitions', 'Tagset for petitions generated from nysenate.gov', NULL, 1, 1, 1, 'civicrm_contact', NULL, NULL);
  SET FOREIGN_KEY_CHECKS=1;
"
$execSql $instance -c "$sql" -q

#TODO create column in entity_tag for flag from website
