#!/bin/sh
#
# copyArchive.sh - Copy an instance archive file (zip file containing two
#                  SQL dump files) to a new file, prevserving proper naming.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2011-06-29
# Revised: 2011-06-29
#

prog=`basename $0`

if [ $# -ne 2 ]; then
  echo "Usage: $prog archive_file target_instance" >&2
  exit 1
fi

arcfile="$1"
tgtinst="$2"

if [ ! -r "$arcfile" ]; then
  echo "$prog: $arcfile: File not found" >&2
  exit 1
fi

srcinst=`echo $arcfile | egrep -o '^[^_]+'`
srcbase=`echo $arcfile | sed 's;^[^_]*;;'`

unzip $arcfile || exit 1

for f in senate_*_$srcinst.sql; do
  fnamebase=`echo $f | sed "s;_$srcinst.sql;;"`
  mv $f ${fnamebase}_$tgtinst.sql
done

zip $tgtinst$srcbase senate_*_$tgtinst.sql

rm -f senate_*_$tgtinst.sql

exit 0
