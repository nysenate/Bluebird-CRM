#!/bin/sh
#
# manageSolrConfig.sh - Handle setup of SOLR integration
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2015-01-28
# Revised: 2015-01-31
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
drush=$script_dir/drush.sh

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--list] [--update | --clear] [--enable-module | --disable-module | --uninstall-module] [--reindex] [--bluebird-setup | -bs] instanceName" >&2
}

if [ $# -lt 1 ]; then
  usage
  exit 1
fi

op=list

while [ $# -gt 0 ]; do
  case "$1" in
    --list|-l) op=list ;;
    --update|-u) op=update;;
    --clear) op=clear ;;
    --en*) op=enable ;;
    --dis*) op=disable ;;
    --uninstall*) op=uninstall ;;
    --rein*) op=reindex ;;
    --bluebird-setup|-bs) op=bs ;;
    -*) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
    *) instance="$1" ;;
  esac
  shift
done


if [ ! "$instance" ]; then
  echo "$prog: Must specify an instance to manage" >&2
  usage
  exit 1
elif ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

app_rootdir=`$readConfig --ig $instance app.rootdir` || app_rootdir="$DEFAULT_APP_ROOTDIR"
solr_url=`$readConfig --ig $instance solr.url` || solr_url="$DEFAULT_SOLR_URL"
solr_ro=`$readConfig --ig $instance solr.read_only` || solr_ro=0
solr_dc=`$readConfig --ig $instance solr.direct_commit` || solr_dc=1
solr_sc=`$readConfig --ig $instance solr.soft_commit` || solr_sc=1

if [ $op = list ]; then
  sql="SHOW TABLES LIKE 'apachesolr%';"
  tabs=`$execSql $instance -c "$sql" --drupal -q`
  if [ "$tabs" ]; then
    sql="SELECT * FROM apachesolr_environment;"
    solr_env=`$execSql $instance -c "$sql" --drupal -q`
    sql="SELECT * FROM apachesolr_environment_variable;"
    solr_env_var=`$execSql $instance -c "$sql" --drupal -q`
    sql="SELECT * FROM apachesolr_index_bundles;"
    solr_index_bundles=`$execSql $instance -c "$sql" --drupal -q`
    sql="SELECT * FROM variable where name like 'apachesolr%';"
    solr_var=`$execSql $instance -c "$sql" --drupal -q`
    echo "Apache SOLR environment:"
    echo "$solr_env"
    echo "Apache SOLR environment variables:"
    echo "$solr_env_var"
    echo "Apache SOLR index bundles:"
    echo "$solr_index_bundles"
    echo "Apache SOLR settings in the Drupal variable table:"
    echo "$solr_var"
  else
    echo "Apache SOLR environment is not configured"
  fi
fi

if [ $op = enable -o $op = bs ]; then
  echo "Enabling apachesolr and apachesolr_civiAttachments modules"
  $drush $instance en -y apachesolr apachesolr_civiAttachments
elif [ $op = disable ]; then
  echo "Disabling apachesolr and apachesolr_civiAttachments modules"
  $drush $instance dis -y apachesolr apachesolr_civiAttachments
elif [ $op = uninstall ]; then
  echo "Uninstalling Apache Solr modules"
  $drush $instance dis -y apachesolr_civiAttachments apachesolr
  $drush $instance pm-uninstall -y apachesolr_civiAttachments apachesolr
fi

if [ $op = update -o $op = bs ]; then
  echo "Storing configuration data into apachesolr tables"
  sql="TRUNCATE TABLE apachesolr_environment;
       INSERT INTO apachesolr_environment (env_id, name, url)
       VALUES ('solr', 'Apache Solr server', '$solr_url');
       TRUNCATE TABLE apachesolr_environment_variable;
       INSERT INTO apachesolr_environment_variable (env_id, name, value)
       VALUES ('solr', 'apachesolr_read_only', 'i:$solr_ro;'),
              ('solr', 'apachesolr_direct_commit', 'i:$solr_dc;'),
              ('solr', 'apachesolr_soft_commit', 'i:$solr_sc;');
       TRUNCATE TABLE apachesolr_index_bundles;
       INSERT INTO apachesolr_index_bundles (env_id, entity_type, bundle)
       VALUES ('solr', 'civiFile', 'civiFile');"
  $execSql $instance -c "$sql" --drupal -q
  $drush $instance vset apachesolr_civiAttachments_extract_using solr
elif [ $op = clear ]; then
  echo "Clearing apachesolr tables"
  sql="TRUNCATE TABLE apachesolr_environment;
       TRUNCATE TABLE apachesolr_environment_variable;
       TRUNCATE TABLE apachesolr_index_bundles;"
  $execSql $instance -c "$sql" --drupal -q
fi

if [ $op = reindex -o $op = bs ]; then
  echo "Archiving orphaned attachments"
  php $app_rootdir/civicrm/scripts/fileCleanup.php -S$instance --action=archive
  echo "Marking all attachments for processing"
  $drush $instance solr-mark-all
  echo "Indexing all attachments"
  $drush $instance solr-index
fi

exit $?
