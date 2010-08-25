#reset memcache stats
echo stats reset | nc 127.0.0.1 11211n

#restart apache2
service apache2 restart

#nagios reset
service nagios stop
/usr/local/nagios/var/status.dat
service nagios start


