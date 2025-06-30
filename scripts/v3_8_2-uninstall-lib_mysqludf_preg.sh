#!/bin/sh
#
# v3_8_2.sh
# Removes lib_mysqludf_preg UDF libraries from database
#
# Project: BluebirdCRM
# Authors: Nathan Frank
# Organization: New York State Senate
# Date: 2025-06-30
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh

. $script_dir/defaults.sh

## rebuild shadow table functions
echo "$prog: rebuild shadow table functions\n"
$execSql -c "$(cat <<EOF
-- deprecated functions
DROP FUNCTION IF EXISTS ghpcre_capture;
DROP FUNCTION IF EXISTS ghpcre_rlike;
DROP FUNCTION IF EXISTS preg_offset;
-- current functions
DROP FUNCTION IF EXISTS lib_mysqludf_preg_info;
DROP FUNCTION IF EXISTS preg_capture;
DROP FUNCTION IF EXISTS preg_check;
DROP FUNCTION IF EXISTS preg_position;
DROP FUNCTION IF EXISTS preg_rlike;
DROP FUNCTION IF EXISTS preg_replace;
EOF
)"

## verify that the SQL functions work
echo "\n$prog: verifying BB_ADDR_REPLACE and BB_NORMALIZE_ADDR functions\n"
$execSql -c "select cast(LIB_MYSQLUDF_PREG_INFO() as char);"
$execSql -c "select cast(preg_capture('/^\\d/', '3sdfsdf') as char);"
echo "\n"
## record completion
echo "$prog: lib_mysqludf_preg functions dropped from database.\n"
