#!/bin/sh
#
# v1543.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2014-12-16
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

app_rootdir=`$readConfig --ig $instance app.rootdir` || app_rootdir="$DEFAULT_APP_ROOTDIR"

#determine which solr core to use
envHost=`$readConfig --global db.host`
if [ $envHost == "crmdbprod" ]; then
  echo "implementing on production environment..."
  solrCore="BluebirdCRM_Prod"
else
  echo "implementing on dev/test/staging environment..."
  solrCore="BluebirdCRM_Dev"
fi

echo "implementing solr attachment search..."

$drush $instance en apachesolr -y
$drush $instance en apachesolr_civiAttachments -y

sql="
  TRUNCATE TABLE apachesolr_environment;
  INSERT INTO apachesolr_environment (env_id, name, url, service_class)
  VALUES
    ('solr', 'Apache Solr server', 'http://doral.nysenate.gov:8080/solr/$solrCore', '');

  TRUNCATE TABLE apachesolr_environment_variable;
  INSERT INTO apachesolr_environment_variable (env_id, name, value)
  VALUES
    ('solr', 'apachesolr_direct_commit', 0x693a313b),
    ('solr', 'apachesolr_read_only', 0x733a313a2230223b),
    ('solr', 'apachesolr_soft_commit', 0x693a313b);

  TRUNCATE TABLE apachesolr_index_bundles;
  INSERT INTO apachesolr_index_bundles (env_id, entity_type, bundle)
  VALUES
    ('solr', 'civiFile', 'civiFile');
"
$execSql $instance -c "$sql" --drupal -q

#add civi attachment variables
$drush $instance vset apachesolr_civiAttachments_extract_using solr

echo "finished configuring solr. now cleanup file attachments..."
php $app_rootdir/civicrm/scripts/fileCleanup.php -S $instance --action=archive

echo "mark all attachments for processing and begin indexing..."
$drush $instance solr-mark-all
$drush $instance solr-index

echo "finished solr setup."
