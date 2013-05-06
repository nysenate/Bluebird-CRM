#
# custom service scripts
#
# modusinc.com
#
if [ -z $1 ]
then
  echo "restart specific services for civicrm: mysql, apache, varnish, solr (uses tomcat6)\n\n";
  echo "usage: appservices [all|mysql|apache] [start|stop|restart]";
elif [ -n $1 ]
then
  serv=$1;
  function=$2;
fi

case $serv in
   "all") 
	echo "restarting all...";
	service mysql $function;
	service apache2 $function;
	service varnish $function;
	service tomcat6 $function;;
   "apache") 
	echo "restarting apache...";
        service apache2 $function;;
   "mysql") 
	echo "restarting mysql...";
        service mysql $function;;
   "solr") 
	echo "restarting SOLR...";
        service tomcat6 $function;;
   "varnish") 
	echo "restarting varnish...";
        service varnish $function;;
   "clearcache")
	echo "clearing cache";

	#reset memcache stats
	echo stats reset | nc 127.0.0.1 11211n;;

   *) echo "Sorry, didn't understand";;
esac
