#!/bin/sh
#
# fixPermissions.sh - Set Bluebird directory permissions appropriately.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-27
#

prog=`basename $0`
script_dir=`dirname $0`
readConfig=$script_dir/readConfig.sh

webdir=`$readConfig --group globals www.rootdir`
owner_user=`$readConfig --group globals owner.user`
owner_group=`$readConfig --group globals owner.group`

if [ ! "$webdir" -o ! "$owner_user" -o ! "$owner_group" ]; then
  echo "$prog: Please set www.rootdir, owner.user, and owner.group in the Bluebird config file." >&2
  exit 1
fi

set -x
chown -R $owner_user:$owner_group $webdir
chmod -R ug+rw,o-w $webdir

exit 0
