#!/bin/sh
#
# sageSetup.sh - The nyss_sage setup script
#
# Project: BluebirdCRM
# Author: Graylin Kim
# Organization: New York State Senate
# Date: 2011-11-22
# Revised: 2011-11-22
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh

usage () {
  echo "Usage: $prog [--help|-h] [-b|--backup filename] instance"
}

# Process the cli arguments
if [ $# -eq 0 ]; then
  usage; exit 1;
fi

backup=
instance=
while [ $# -gt 0 ]; do
  case "$1" in
    -h|--help) usage; exit 0 ;;
    -b|--backup) shift; backup="$1" ;;
    -*) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
    *) instance="$1" ;;
  esac
  shift
done

# Ensure a valid instance name.
if ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

if [ "$backup" ]; then
    $execSql $instance -c "SELECT config_backend FROM civicrm_domain WHERE id=1" > $backup
    echo "Configuration backed up to $backup"
fi

echo "Enabling the nyss_sage module."
$execSql $instance --drupal -c "UPDATE system SET status=1 WHERE name='nyss_sage'"

echo "Resetting the address standardization configuration."
$execSql $instance -c "
    UPDATE civicrm_preferences SET
        address_standardization_provider=NULL,
        address_standardization_userid=NULL,
        address_standardization_url=NULL
    WHERE id=1"

echo "Resetting the geocoding configuration."
php -r '
    # Pretend we are in a web request
    $_SERVER["HTTP_HOST"] = $argv[1];

    # Bootstrap the dedupe module
    $root = dirname(dirname(__FILE__));
    require_once "$root/drupal/sites/default/civicrm.settings.php";
    require_once "$root/modules/nyss_dedupe/nyss_dedupe.module";
    require_once "CRM/Core/Config.php";
    require_once "CRM/Core/DAO.php";
    $config = CRM_Core_Config::singleton();

    # Get the config data and check for die conditions.
    $dao = CRM_Core_DAO::executeQuery("SELECT config_backend FROM civicrm_domain WHERE id=1");
    if(!$dao->fetch()) {
        echo "[error] config_backend not found!\n"; exit();

    } elseif (!$data = unserialize($dao->config_backend)) {
        echo "[error] config_backend could not be unserialized.\n"; exit();

    # Clear out the geo configurations.
    } else {
        $data["geoProvider"]=$data["geoAPIKey"]="";
        $params = array( 1 => array( serialize($data), "String"));
        CRM_Core_DAO::executeQuery("UPDATE civicrm_domain SET config_backend=%1 WHERE id=1", $params);
        echo "[success] geoProvider configuration successfully reset.\n";
    }
' $instance
