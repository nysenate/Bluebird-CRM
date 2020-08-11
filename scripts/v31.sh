#!/bin/sh
#
# v31.sh
#
# Project: BluebirdCRM
# Authors: Brian Shaughnessy and Ken Zalewski
# Organization: New York State Senate
# Date: 2020-08-11
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
drush=$script_dir/drush.sh
readConfig=$script_dir/readConfig.sh

. $script_dir/defaults.sh

if [ $# -ne 1 ]; then
  echo "Usage: $prog instanceName" >&2
  exit 1
fi

instance="$1"

data_rootdir=`$readConfig --ig $instance data.rootdir` || data_rootdir="$DEFAULT_DATA_ROOTDIR"
pubfiles_dir="$data_rootdir/$instance/pubfiles"

if ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

# copy header/footer to uploads path
cp "$pubfiles_dir/common/images/template/header.png" "$pubfiles_dir/images/uploads/header.png"
cp "$pubfiles_dir/common/images/template/footer.png" "$pubfiles_dir/images/uploads/footer.png"
chown apache:bluebird "$pubfiles_dir/images/uploads/header.png"
chown apache:bluebird "$pubfiles_dir/images/uploads/footer.png"
chmod 664 "$pubfiles_dir/images/uploads/header.png"
chmod 664 "$pubfiles_dir/images/uploads/footer.png"

echo "$prog: header/footer images copied to Mosaico image folder."

# generate mosaico template for instance
$drush $instance cvapi nyss.generatemailtemplate addupdate="Add" --quiet

## record completion
echo "$prog: upgrade process is complete."
