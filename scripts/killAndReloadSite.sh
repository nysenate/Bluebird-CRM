#!/bin/bash
tmpCiviSite=$1
tmpCiviDataSource=$2
cd /data/scripts
php civiSetup.php prod deletesite $tmpCiviSite
php civiSetup.php prod deletesite $tmpCiviSite
php civiSetup.php prod copysite template $tmpCiviSite
php civiSetup.php prod copysite template $tmpCiviSite
cd /data/www/nyss/sites/all/modules/civicrm/tools/scripts/importData
php importData.inc.php import $tmpCiviSite $tmpCiviDataSource 
wget -O /dev/null http://USER:PASS@$tmpCiviSite.crm.nysenate.gov
cd /data/scripts
php civiSetup.php prod fp $tmpCiviSite;

