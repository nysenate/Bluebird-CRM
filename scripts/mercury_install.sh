#!/bin/sh
#
# mercury_install.sh - Automate the process of installing Pantheon/Mercury
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-07
#

prog=`basename $0`
skip_apt=0
skip_bcfg2=0
skip_drush=0
skip_solr=0

while [ $# -gt 0 ]; do
  case "$1" in
    --no-apt) skip_apt=1 ;;
    --no-bcfg2) skip_bcfg2=1 ;;
    --no-drush) skip_drush=1 ;;
    --no-solr) skip_solr=1 ;;
    *) echo "$prog: $1: Unknown option" >&2; exit 1 ;;
  esac
  shift
done

if [ $skip_apt -eq 0 ]; then
  echo "Configuring apt sources and retrieving updates"
  echo 'APT::Install-Recommends "0";' | sudo tee /etc/apt/apt.conf
  sudo wget http://pantheon-storage.chapterthree.com/mercury.list.1.1 -O /etc/apt/sources.list.d/mercury.list
  sudo wget http://pantheon-storage.chapterthree.com/aws.list -O /etc/apt/sources.list.d/aws.list
  sudo wget http://pantheon-storage.chapterthree.com/lucid -O /etc/apt/preferences.d/lucid
  wget http://pantheon-storage.chapterthree.com/gpgkeys.txt -O /tmp/keys.txt
  sudo apt-key add /tmp/keys.txt
  sudo apt-get update
  sudo apt-get -y upgrade
  sudo apt-get -y dist-upgrade
fi

if [ $skip_bcfg2 -eq 0 ]; then
  echo "Installing and configuring BCFG2"
  sudo apt-get -y install bzr bcfg2-server gamin python-gamin python-genshi
  sudo bcfg2-admin init

  echo "Downloading BCFG2 config files from Launchpad"
  sudo rm -rf /var/lib/bcfg2/
  sudo bzr branch lp:pantheon/1.1 /var/lib/bcfg2
  echo "<Clients version=\"3.0\">\n</Clients>" | sudo tee -a /var/lib/bcfg2/Metadata/clients.xml
  sudo sed -i "s/^plugins = .*$/plugins = Bundler,Cfg,Metadata,Packages,Probes,Rules,TGenshi\nfilemonitor = gamin/" /etc/bcfg2.conf

  echo "Starting the BCFG2 server"
  pidfile=/var/run/bcfg2-server.pid
  [ -f $pidfile ] && sudo kill -9 `cat $pidfile`
  sudo rm -f $pidfile
  sudo /etc/init.d/bcfg2-server start
  tail -0f /var/log/syslog | while read line; do
    if echo $line | grep "serve_forever() .start."; then
      echo "BCFG2 has started properly"
      killall tail
    fi
  done

  echo "Starting the BCFG2 client"
  #sudo bcfg2-admin xcmd Packages.Refresh
  #for non-AWS servers, replace the following with sudo bcfg2 -vqed
  #sudo bcfg2 -vqed -p 'mercury-aws'
  sudo bcfg2 -vqed
fi

if [ $skip_drush -eq 0 ]; then
  echo "Installing Drush"
  if [ ! -e /usr/local/bin/drush ]; then
    wget http://ftp.drupal.org/files/projects/drush-6.x-3.3.tar.gz
    tar zxvf drush-6.x-3.3.tar.gz
    sudo chmod 555 drush/drush
    sudo chown -R root:root drush
    sudo mv drush /usr/local/
    sudo ln -s /usr/local/drush/drush /usr/local/bin/drush
  fi
  sudo drush dl drush_make
fi

echo "Installing Mercury"
sudo rm -rf /var/www
sudo drush make --working-copy /etc/mercury/mercury.make /var/www/
#sudo bzr branch lp:pressflow /var/www
#sudo mkdir /var/www/profiles/mercury
#sudo wget -P /var/www/profiles/mercury http://pantheon-storage.chapterthree.com/mercury.profile
#if /var/www/sites/all/modules doesn't exist, the following command will ask you if it can create it - say "yes"
#sudo drush dl --destination=/var/www/sites/all/modules varnish
#sudo drush dl --destination=/var/www/sites/all/modules apachesolr
#sudo drush dl --destination=/var/www/sites/all/modules memcache

if [ $skip_solr -eq 0 ]; then
  echo "Installing ApacheSolr"
  wget http://apache.osuosl.org/lucene/solr/1.4.0/apache-solr-1.4.0.tgz
  tar xvzf apache-solr-1.4.0.tgz
  sudo mkdir /var/solr
  sudo mv apache-solr-1.4.0/dist/apache-solr-1.4.0.war /var/solr/solr.war
  sudo mv apache-solr-1.4.0/example/solr /var/solr/default
  #wget http://solr-php-client.googlecode.com/files/SolrPhpClient.r22.2009-11-09.tgz
  #cat SolrPhpClient.r22.2009-11-09.tgz | (cd /var/www/sites/all/modules/apachesolr/ && sudo tar xvzf -)
  sudo mv /var/www/sites/all/modules/apachesolr/schema.xml /var/solr/default/conf/
  sudo mv /var/www/sites/all/modules/apachesolr/solrconfig.xml /var/solr/default/conf/
  sudo chown -R tomcat6:root /var/solr/
fi

echo "Preparing Pressflow files and directories"
sudo mkdir /var/www/sites/default/files
sudo cp /var/www/sites/default/default.settings.php /var/www/sites/default/settings.php
sudo chown -R root:www-data /var/www/*
sudo chown www-data:www-data /var/www/sites/default/settings.php
sudo chmod 660 /var/www/sites/default/settings.php
sudo chmod 775 /var/www/sites/default/files

echo "Adding Hudson to sudoers and restarting Hudson"
echo "hudson ALL = NOPASSWD: /usr/local/bin/drush, /etc/mercury/init.sh, /usr/bin/fab, /usr/sbin/bcfg2" | sudo tee -a /etc/sudoers
sudo usermod -a -G shadow hudson
sudo /etc/init.d/hudson restart

echo "Done with basic installation.  Either reboot now, or go to http://localhost:8081/, or run:  sudo /etc/mercury/init.sh"
