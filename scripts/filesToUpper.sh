#!/bin/sh
# uppercase any filenames with lowercase chars
for file in $*
 do
 if [ -f $file ]
 then
   ucfile=`echo $file | tr [:lower:] [:upper:]`
   if [ $file != $ucfile ]
   then
     mv -i $file $ucfile
   fi     
 fi
done
