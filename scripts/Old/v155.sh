#!/bin/sh
#
# v155.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2014-12-16
# Revised: 2015-01-29
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

echo "$prog: Cleaning up dangling attachment files and DB records"
php $app_rootdir/civicrm/scripts/fileCleanup.php -S$instance --file-action=archive --db-action=delete

echo "$prog: Setting up SOLR"
$script_dir/manageSolrConfig.sh $instance --bluebird-setup
echo "$prog: Done with SOLR setup"

