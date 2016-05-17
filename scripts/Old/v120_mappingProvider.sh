#!/bin/sh
#
# v120.sh
#
# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2011-03-11
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
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


## set mapping provider
mapping="
UPDATE civicrm_domain SET config_backend = REPLACE( config_backend, 's:11:\"geoProvider\";s:0:\"\";s:9:\"geoAPIKey\";s:0:\"\";', '' );
UPDATE civicrm_domain SET config_backend = REPLACE( config_backend, 's:11:\"mapProvider\";s:6:\"Google\";s:9:\"mapAPIKey\";s:86:\"ABQIAAAAzY9VyTEuublBDc-Htl9EvhQuOUP8hd2qhSL-nJEVisOKANWd3xQi7-zJ-V3SB2GbiDzS7GSEa0pZeg\";', 's:11:\"mapProvider\";s:6:\"Google\";s:9:\"mapAPIKey\";s:86:\"ABQIAAAAzY9VyTEuublBDc-Htl9EvhQuOUP8hd2qhSL-nJEVisOKANWd3xQi7-zJ-V3SB2GbiDzS7GSEa0pZeg\";s:11:\"geoProvider\";s:4:\"SAGE\";s:9:\"geoAPIKey\";s:31:\"SQ0lzOepSH3qnh2r4kN1QeRCMAAan2u\";');"
$execSql -i $instance -c "$mapping"


