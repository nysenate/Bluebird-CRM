opts="-av --delete --delete-excluded";
extbackupserver="ip-10-242-198-118.ec2.internal";
mysqlhost="cividb01";
#mysqlhost="cividb01";
mysqluser="loadsenate";
mysqlpwd="char12tree*!";
backupdir="/senatevolume/backup/"`uname -n`;

mkdir $backupdir;

backupdir=$backupdir/$1;

mkdir $backupdir;
mkdir $backupdir"databases";
mkdir $backupdir"/data";

echo backup individual databases
for database in $(mysql -u$mysqluser -p$mysqlpwd -h$mysqlhost -e "show databases" | grep "^\|" | grep -v Database); do echo -n "backing up $database ... "; mysqldump --opt -u$mysqluser -p$mysqlpwd -h$mysqlhost $database > $backupdir/databases/$database.sql && echo "ok" || echo "failed"; done

echo backing up etc
rsync $opts /etc $backupdir/;

echo backing up home 
rsync $opts /home $backupdir/;

echo backing up data
#CAREFUL: need to start in dir to ensure no symlink issues since /data is a symlink
cd /data
rsync $opts /data/scripts $backupdir/data/;
rsync $opts /data/civiSVN $backupdir/data/;
rsync $opts /data/senateDevelopment $backupdir/data/;
rsync $opts /data/senateProduction $backupdir/data/;
rsync $opts /data/loadTesting $backupdir/data/;
rsync $opts /data/installFiles $backupdir/data/;
rsync $opts /data/www $backupdir/data/;
rsync $opts /data/importData $backupdir/data/;

#echo taring file
#rm -rf $backupdir/backup.tgz
#tar --exclude=backup.tgz -cvzf $backupdir/backup.tgz $backupdir;

#rsync /mnt/backup.tgz backup@ip-10-242-198-118.ec2.internal:/data/backup/backupHiLoad.tgz;
rdiff-backup -v5 /senatevolume/backup /senatevolume/backupRDiff

