#!/bin/sh
#
# defaults.sh - Shell defaults when using the Bluebird config file.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-30
# Revised: 2011-09-09
# Revised: 2013-11-02 - Added DEFAULT_DB_LOGIN_PATH; removed host/user/pass
#

DEFAULT_CONFIG_FILE=/etc/bluebird.cfg

DEFAULT_DB_LOGIN_PATH=bluebird
DEFAULT_DB_CIVICRM_PREFIX=senate_c_
DEFAULT_DB_DRUPAL_PREFIX=senate_d_
DEFAULT_DB_LOG_PREFIX=senate_l_

DEFAULT_APP_ROOTDIR=/opt/bluebird
DEFAULT_DATA_ROOTDIR=/var/bluebird
DEFAULT_DRUPAL_ROOTDIR=/var/www
DEFAULT_IMPORT_ROOTDIR=/data/importData

DEFAULT_BACKUP_HOST=localhost
DEFAULT_BACKUP_ROOTDIR=/crmbackups

DEFAULT_BASE_DOMAIN=crm.nysenate.gov

DEFAULT_SENATOR_FORMAL_NAME="Senator"

DEFAULT_INCLUDE_EMAIL_IN_NAME=0
DEFAULT_INCLUDE_WILDCARD_IN_NAME=0

DEFAULT_SOLR_URL="http://localhost:8080/solr"


confirm_yes_no() {
  [ "$1" ] && confirm_msg="$1" || confirm_msg="Proceed with the operation"
  echo -n "$confirm_msg (N/y)? "
  read ch
  case "$ch" in
    [yY]*) return 0 ;;
    *) echo "Aborting."; return 1 ;;
  esac
}


logdt() {
  echo "[`date +%Y-%m-%d\ %H:%M:%S`] $@"
}
