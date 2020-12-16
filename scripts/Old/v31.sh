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

$drush $instance cvapi extension.install key=org.civicrm.flexmailer --quiet
$drush $instance cvapi extension.install key=uk.co.vedaconsulting.mosaico --quiet
$drush $instance cvapi extension.install key=biz.lcdservices.mosaicoimageeditor --quiet

# create Mosaico image folders
mkdir "$pubfiles_dir/images/uploads"
mkdir "$pubfiles_dir/images/uploads/thumbnails"
mkdir "$pubfiles_dir/images/uploads/static"

chown apache:bluebird "$pubfiles_dir/images/uploads"
chown apache:bluebird "$pubfiles_dir/images/uploads/thumbnails"
chown apache:bluebird "$pubfiles_dir/images/uploads/static"

# copy header/footer to uploads path
if test -e "$pubfiles_dir/images/template/header.png"; then
  cp `readlink -f "$pubfiles_dir/images/template/header.png"` "$pubfiles_dir/images/uploads/header.png"
  chown apache:bluebird "$pubfiles_dir/images/uploads/header.png"
  chmod 664 "$pubfiles_dir/images/uploads/header.png"
else
  echo "Unable to locate instance header.png file. Using generic default."

  cp "$data_rootdir/common/pubfiles/images/template/header.png" "$pubfiles_dir/images/uploads/header.png"
  chown apache:bluebird "$pubfiles_dir/images/uploads/header.png"
  chmod 664 "$pubfiles_dir/images/uploads/header.png"
fi

if test -e "$pubfiles_dir/images/template/footer.png"; then
  cp `readlink -f "$pubfiles_dir/images/template/footer.png"` "$pubfiles_dir/images/uploads/footer.png"
  chown apache:bluebird "$pubfiles_dir/images/uploads/footer.png"
  chmod 664 "$pubfiles_dir/images/uploads/footer.png"
else
  echo "Unable to locate instance footer.png file. Using generic default."

  cp "$data_rootdir/common/pubfiles/images/template/footer.png" "$pubfiles_dir/images/uploads/footer.png"
  chown apache:bluebird "$pubfiles_dir/images/uploads/footer.png"
  chmod 664 "$pubfiles_dir/images/uploads/footer.png"
fi

echo "$prog: header/footer images copied to Mosaico image folder."

# generate mosaico template for instance
$drush $instance cvapi nyss.generatemailtemplate addupdate="Add" --quiet

# 13567
## 5335 add bmp to safe file extensions
sql="
SELECT @safe:= id FROM civicrm_option_group WHERE name = 'safe_file_extension';
SELECT @maxval:= MAX(CAST(value AS UNSIGNED)) FROM civicrm_option_value WHERE option_group_id = @safe;
INSERT INTO civicrm_option_value (
  option_group_id, label, value, name, grouping, filter, is_default, weight, description, is_optgroup, is_reserved,
  is_active, component_id, domain_id, visibility_id )
VALUES (
  @safe, 'jfif', @maxval+1, NULL , NULL , '0', '0', @maxval+1, NULL , '0', '0', '1', NULL , NULL , NULL
);"
$execSql -i $instance -c "$sql" -q

## 13600
echo "create friend/neighbor relationship type"
sql="
  DELETE FROM civicrm_relationship_type WHERE name_a_b = 'friend_is';
  INSERT INTO civicrm_relationship_type
  (name_a_b, label_a_b, name_b_a, label_b_a, contact_type_a, contact_type_b, is_active)
  VALUES
  ('friend_is', 'Friend is', 'friend_is', 'Friend is', 'Individual', 'Individual', 1);

  DELETE FROM civicrm_relationship_type WHERE name_a_b = 'neighbor_is';
  INSERT INTO civicrm_relationship_type
  (name_a_b, label_a_b, name_b_a, label_b_a, contact_type_a, contact_type_b, is_active)
  VALUES
  ('neighbor_is', 'Neighbor is', 'neighbor_is', 'Neighbor is', 'Individual', 'Individual', 1);
"
$execSql $instance -c "$sql" -q

## record completion
echo "$prog: upgrade process is complete."
