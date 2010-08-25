rm -rf /tmp/tmpsvn
svn export /data/civiSVN/3.2.alpha3 /tmp/tmpsvn
rsync -rlogc --progress /tmp/tmpsvn/ /data/senateProduction/modules/civicrm/

