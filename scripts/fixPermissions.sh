#!/bin/sh
#
# fixPermissions.sh - Set Bluebird directory permissions appropriately.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-13
# Revised: 2010-09-30
#

prog=`basename $0`
script_dir=`dirname $0`
readConfig=$script_dir/readConfig.sh

. $script_dir/defaults.sh

datadir=`$readConfig --global data.rootdir` || datadir="$DEFAULT_DATA_ROOTDIR"
webdir=`$readConfig --global drupal.rootdir` || webdir="$DEFAULT_DRUPAL_ROOTDIR"
appdir=`$readConfig --global app.rootdir` || appdir="$DEFAULT_APP_ROOTDIR"
owner_user=`$readConfig --global owner.user` || owner_user="$DEFAULT_OWNER_USER"
owner_group=`$readConfig --global owner.group` || owner_group="$DEFAULT_OWNER_GROUP"

if [ ! "$datadir" -o ! "$webdir" -o ! "$owner_user" -o ! "$owner_group" ]; then
  echo "$prog: Please set drupal.rootdir, owner.user, and owner.group in the Bluebird config file." >&2
  exit 1
fi

set -x
chown -R $owner_user:$owner_group $datadir/
chmod -R ug+rw,o-w $datadir/
chown -R $owner_user:$owner_group $webdir/sites
chmod -R u+rw,go+r-w $webdir
chmod -R ug+rw,o-w $webdir/sites
chown -R $owner_user:$owner_group $appdir/civicrm
find $appdir/civicrm/. -type f -exec chmod 664 {} \;
find $appdir/civicrm/. -type d -exec chmod 775 {} \;
chown -R $owner_user:$owner_group $appdir/modules
find $appdir/modules/. -type f -exec chmod 664 {} \;
find $appdir/modules/. -type d -exec chmod 775 {} \;

exit 0
