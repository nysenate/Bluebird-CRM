#!/bin/sh
#
# fixFileSystemPath.sh
#
# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2011-04-18
#
# Create drupal file system path
# Create symlink from sites folder to path location (data directory)
# Update drupal config with value
#

prog=`basename $0`
script_dir=`dirname $0`
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
filepath="sites/$instance.$base_domain/files"

drupal_filesdir="$data_rootdir/$instance.$base_domain/drupal"
civicrm_filesdir="$data_rootdir/$instance.$base_domain/civicrm"
sitedir="$webdir/sites/$instance.$base_domain"

# create directories and symlink
mkdir -p "$drupal_filesdir"
mkdir -p "$sitedir"
ln -s "$drupal_filesdir" "$sitedir/files"
ln -s "$civicrm_filesdir" "$sitedir/files"

# set Drupal variable
$drush $instance vset file_directory_path $filepath -y

# cleanup
$script_dir/clearCache.sh $instance
$script_dir/fixPermissions.sh
