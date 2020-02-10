#!/bin/sh
#

script_dir=`dirname $0`
script_dir=`cd $script_dir; echo $PWD`
conv=$script_dir/convert_issue_codes.sh
import_dir=/data/importData

cd $import_dir

for d in sd?? sd??ext; do
  du=`echo $d | tr [:lower:] [:upper:]`
  (
    cd $d
    $conv ${du}ISS.TXT > ${du}ISSCONV.TXT
  )
done

