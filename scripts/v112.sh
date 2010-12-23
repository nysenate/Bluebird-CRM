#!/bin/sh
#
# v112.sh
#
# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2010-12-19
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

data_rootdir=`$readConfig --ig $instance data.rootdir` || data_rootdir="$DEFAULT_DATA_ROOTDIR"
webdir=`$readConfig --global drupal.rootdir` || webdir="$DEFAULT_DRUPAL_ROOTDIR"
base_domain=`$readConfig --ig $instance base.domain` || base_domain="$DEFAULT_BASE_DOMAIN"

drupal_filesdir="$data_rootdir/$instance.$base_domain/drupal"
sitedir="$webdir/sites/$instance.$base_domain"

# create directories and symlink
mkdir -p "$drupal_filesdir"
mkdir -p "$sitedir"
ln -s "$drupal_filesdir" "$sitedir/files"

# cleanup
$script_dir/fixPermissions.sh
$script_dir/clearCache.sh $instance
