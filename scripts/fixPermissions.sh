#!/bin/sh
#
# fixPermissions.sh - Set Bluebird directory permissions appropriately.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-13
# Revised: 2011-12-09
# Revised: 2014-02-26 - enforce read-only group access on template/ directory
# Revised: 2014-04-22 - enforce read-only group access on common/ directory
#

prog=`basename $0`
script_dir=`dirname $0`
readConfig=$script_dir/readConfig.sh

if [ `id -u` -ne 0 ]; then
  echo "$prog: This script must be run by root." >&2
  exit 1
fi

. $script_dir/defaults.sh

appdir=`$readConfig --global app.rootdir` || appdir="$DEFAULT_APP_ROOTDIR"
datdir=`$readConfig --global data.rootdir` || datdir="$DEFAULT_DATA_ROOTDIR"
impdir=`$readConfig --global import.rootdir` || impdir="$DEFAULT_IMPORT_ROOTDIR"
webdir=`$readConfig --global drupal.rootdir` || webdir="$DEFAULT_DRUPAL_ROOTDIR"

appowner=`$readConfig --global app.rootdir.owner`
datowner=`$readConfig --global data.rootdir.owner`
impowner=`$readConfig --global import.rootdir.owner`
webowner=`$readConfig --global drupal.rootdir.owner`

appperms=`$readConfig --global app.rootdir.perms`
datperms=`$readConfig --global data.rootdir.perms`
impperms=`$readConfig --global import.rootdir.perms`
webperms=`$readConfig --global drupal.rootdir.perms`

set -x

[ "$appowner" ] && chown -R "$appowner" "$appdir/"
[ "$appperms" ] && chmod -R "$appperms" "$appdir/"

[ "$datowner" ] && chown -R "$datowner" "$datdir/"
[ "$datperms" ] && chmod -R "$datperms" "$datdir/"
# kz: Kludge Alert: The images/template directory must be read-only so that
# Senators cannot delete their own header and footer images.  I am chowning
# the directory to "root" so that only root can modify images there.
# In addition, the common/ directory must be locked down.
chown -R root "$datdir"/*/pubfiles/images/template
chmod -R go-w "$datdir"/*/pubfiles/images/template
chown -R root "$datdir"/common/
chmod -R go-w "$datdir"/common/

[ "$impowner" ] && chown -R "$impowner" "$impdir/"
[ "$impperms" ] && chmod -R "$impperms" "$impdir/"

[ "$webowner" ] && chown -R "$webowner" "$webdir/"
[ "$webperms" ] && chmod -R "$webperms" "$webdir/"

# The Bluebird config file should have the strictest permissions.
cfgpath=`$readConfig`
chmod g-wx,o= "$cfgpath"

exit 0
