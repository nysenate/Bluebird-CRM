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

echo "$prog: create nyss_account table"
sql="
  DROP TABLE IF EXISTS nyss_web_account;
  CREATE TABLE IF NOT EXISTS nyss_web_account (
    id int(10) unsigned NOT NULL,
    contact_id int(10) unsigned NOT NULL,
    action varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
    created_date datetime DEFAULT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1;

  ALTER TABLE nyss_web_account
    ADD PRIMARY KEY (id),
    ADD KEY FK_nyss_web_account_contact_id (contact_id);
  ALTER TABLE nyss_web_account
    MODIFY id int(10) unsigned NOT NULL AUTO_INCREMENT;
  ALTER TABLE nyss_web_account
    ADD CONSTRAINT FK_nyss_web_account_contact_id FOREIGN KEY (contact_id) REFERENCES civicrm_contact (id) ON DELETE NO ACTION ON UPDATE NO ACTION;
"
$execSql $instance -c "$sql" -q

