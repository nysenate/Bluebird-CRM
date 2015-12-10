#!/bin/sh
#
# v1574.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2015-11-24
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

echo "$prog: 9709: on delete cascade contact account records"
sql="ALTER TABLE civicrm_tag CHANGE name name VARCHAR(128);"
$execSql $instance -c "$sql" -q

echo "$prog: 9651: alter web_account FKs"
sql="ALTER TABLE nyss_web_account DROP FOREIGN KEY FK_nyss_web_account_contact_id;
  ALTER TABLE nyss_web_account ADD CONSTRAINT FK_nyss_web_account_contact_id FOREIGN KEY (contact_id) REFERENCES civicrm_contact(id) ON DELETE CASCADE ON UPDATE NO ACTION;"
$execSql $instance -c "$sql" -q
