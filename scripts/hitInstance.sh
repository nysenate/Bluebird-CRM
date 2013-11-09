#!/bin/sh
#
# hitInstance.sh
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-30
# Revised: 2010-09-30
# Revised: 2013-05-14 - made HTTP auth optional (it is deprecated)
#

prog=`basename $0`
script_dir=`dirname $0`
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

http_user=`$readConfig --ig $instance http.user`
http_pass=`$readConfig --ig $instance http.pass`
base_domain=`$readConfig --ig $instance base.domain` || base_domain="$DEFAULT_BASE_DOMAIN"

http_auth=
if [ "$http_user" ]; then
  http_auth="$http_user:$http_pass@"
fi

echo "Making an HTTP connection to instance [$instance]"
set -x
wget -O /dev/null http://$http_auth$instance.$base_domain;

exit $?
