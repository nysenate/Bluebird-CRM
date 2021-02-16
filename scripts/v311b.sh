#!/bin/sh
#
# v311b.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2021-02-09
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

data_rootdir=`$readConfig --ig $instance data.rootdir` || data_rootdir="$DEFAULT_DATA_ROOTDIR"
pubfiles_dir="$data_rootdir/$instance/pubfiles"

if ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

## set mail extension to load last
echo "$prog: set mail extension to load last"
sql="
   DELETE FROM civicrm_extension
   WHERE full_name = 'gov.nysenate.mail';

   INSERT INTO civicrm_extension
   (type, full_name, name, label, file, is_active)
   VALUES
   ('module', 'gov.nysenate.mail', 'NYSS: Mailing Customizations', 'NYSS: Mailing Customizations', 'mail', 1);
"
$execSql $instance -c "$sql" -q

## record completion
echo "$prog: upgrade process is complete."
