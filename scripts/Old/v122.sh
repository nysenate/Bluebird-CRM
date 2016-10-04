#!/bin/sh
#
# v122.sh
#
# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2011-05-30
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
drush=$script_dir/drush.sh

. $script_dir/defaults.sh

if [ $# -ne 1 ]; then
  echo "Usage: $prog instanceName" >&2
  exit 1
fi

instance="$1"

if ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

###### Begin Upgrade Scripts ######

### Drupal ###

## disable/enable drupal modules
echo "disabling/enabling modules for: $instance"
$drush $instance dis nyss_import -y
$drush $instance en nyss_import -y
$drush $instance dis nyss_backup -y
$drush $instance en nyss_backup -y


### CiviCRM ###

## drop/create dedupe index ##
dedupeindexdrop="DROP INDEX individualStrict1 ON civicrm_contact;"
$execSql -i $instance -c "$dedupeindexdrop"

dedupeindex="CREATE INDEX individualStrict1 ON civicrm_contact(first_name,middle_name,last_name,suffix_id);"
$execSql -i $instance -c "$dedupeindex"

## update dedupe rules ##
dedupegroup="UPDATE civicrm_dedupe_rule_group SET threshold = 5, name = 'Level 3 (street + lname + fname + city + suffix)' WHERE id = 1; UPDATE civicrm_dedupe_rule_group SET threshold = 5, name = 'Level 1 (fname + mname + lname + suffix + street + postal)' WHERE id = 4;"
$execSql -i $instance -c "$dedupegroup"

deduperule="DELETE FROM civicrm_dedupe_rule WHERE dedupe_rule_group_id = 1; DELETE FROM civicrm_dedupe_rule WHERE dedupe_rule_group_id = 4; INSERT INTO civicrm_dedupe_rule (dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight) VALUES (1, 'civicrm_contact', 'suffix_id', NULL, 1), (1, 'civicrm_address', 'city', NULL, 1), (1, 'civicrm_contact', 'first_name', NULL, 1), (1, 'civicrm_contact', 'last_name', NULL, 1), (1, 'civicrm_address', 'street_address', NULL, 1), (4, 'civicrm_address', 'street_address', NULL, 1), (4, 'civicrm_contact', 'suffix_id', NULL, 1), (4, 'civicrm_contact', 'middle_name', NULL, 1), (4, 'civicrm_contact', 'first_name', NULL, 1), (4, 'civicrm_contact', 'last_name', NULL, 1);"
$execSql -i $instance -c "$deduperule"

## new location type ##
loctype="INSERT IGNORE INTO civicrm_location_type VALUES (13, 'BOEmailing', '', 'Board of Election mailing address, if available and different from the physical address.', NULL, 1, 0);"
$execSql -i $instance -c "$loctype"

## new relationship type ##
reltype="INSERT INTO civicrm_relationship_type VALUES (18, 'Tenant of', 'Tenant of', 'Landlord of', 'Landlord of', NULL, NULL, NULL, NULL, NULL, NULL, 1);"
$execSql -i $instance -c "$reltype"

## create organization postal/email greeting defaults ##
orggreeting="INSERT INTO civicrm_option_value ( option_group_id, label, value, name, grouping, filter, is_default, weight, description, is_optgroup, is_reserved, is_active, component_id, domain_id, visibility_id ) VALUES ( 42, 'Dear {contact.organization_name}', '11', 'Dear {contact.organization_name}', NULL, 3, 1, 11, NULL, 0, 0, 1, NULL, NULL, NULL ), ( 41, 'Dear {contact.organization_name}', '11', 'Dear {contact.organization_name}', NULL, 3, 1, 10, NULL, 0, 0, 1, NULL, NULL, NULL );"
$execSql -i $instance -c "$orggreeting"

## add backup/restore menu item ##
navbackup="INSERT INTO civicrm_navigation (domain_id, label, name, url, permission, permission_operator, parent_id, is_active, has_separator, weight) VALUES (1, 'Backup/Restore', 'Backup/Restore', 'backupdata', 'administer CiviCRM,export print production files', 'OR', 201, 1, 1, 0);"
$execSql -i $instance -c "$navbackup"

## create phone extension field ##
phoneext="ALTER TABLE civicrm_phone ADD phone_ext VARCHAR( 16 ) NULL;"
$execSql -i $instance -c "$phoneext"

## alter prefix order ##
prefix="UPDATE civicrm_option_value SET weight = 1 WHERE id = 46 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 2 WHERE id = 44 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 3 WHERE id = 45 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 4 WHERE id = 964 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 5 WHERE id = 47 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 6 WHERE id = 1024 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 7 WHERE id = 1025 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 8 WHERE id = 1004 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 9 WHERE id = 1026 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 10 WHERE id = 1015 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 11 WHERE id = 1006 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 12 WHERE id = 1007 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 13 WHERE id = 977 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 14 WHERE id = 985 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 15 WHERE id = 986 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 16 WHERE id = 971 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 17 WHERE id = 993 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 18 WHERE id = 988 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 19 WHERE id = 989 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 20 WHERE id = 994 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 21 WHERE id = 970 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 22 WHERE id = 983 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 23 WHERE id = 997 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 24 WHERE id = 980 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 25 WHERE id = 981 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 26 WHERE id = 1010 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 27 WHERE id = 1022 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 28 WHERE id = 961 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 29 WHERE id = 995 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 30 WHERE id = 974 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 31 WHERE id = 538 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 32 WHERE id = 969 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 33 WHERE id = 539 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 34 WHERE id = 987 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 35 WHERE id = 991 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 36 WHERE id = 984 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 37 WHERE id = 992 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 38 WHERE id = 1005 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 39 WHERE id = 996 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 40 WHERE id = 982 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 41 WHERE id = 976 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 42 WHERE id = 1014 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 43 WHERE id = 978 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 44 WHERE id = 990 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 45 WHERE id = 1002 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 46 WHERE id = 1009 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 47 WHERE id = 1017 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 48 WHERE id = 960 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 49 WHERE id = 1021 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 50 WHERE id = 1012 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 51 WHERE id = 1013 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 52 WHERE id = 979 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 53 WHERE id = 1027 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 54 WHERE id = 1000 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 55 WHERE id = 1008 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 56 WHERE id = 975 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 57 WHERE id = 1001 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 58 WHERE id = 540 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 59 WHERE id = 965 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 60 WHERE id = 966 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 61 WHERE id = 1023 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 62 WHERE id = 1018 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 63 WHERE id = 999 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 64 WHERE id = 1016 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 65 WHERE id = 1114 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 66 WHERE id = 972 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 67 WHERE id = 1020 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 68 WHERE id = 962 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 69 WHERE id = 967 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 70 WHERE id = 1019 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 71 WHERE id = 963 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 72 WHERE id = 968 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 73 WHERE id = 973 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 74 WHERE id = 1011 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 75 WHERE id = 1003 AND option_group_id = 6;
UPDATE civicrm_option_value SET weight = 76 WHERE id = 998 AND option_group_id = 6;"
$execSql -i $instance -c "$prefix"

## alter language order ##
languages="UPDATE civicrm_option_value SET weight = 1 WHERE option_group_id = 49 AND id = 337;
UPDATE civicrm_option_value SET weight = 2 WHERE option_group_id = 49 AND id = 1102;
UPDATE civicrm_option_value SET weight = 3 WHERE option_group_id = 49 AND id = 297;
UPDATE civicrm_option_value SET weight = 4 WHERE option_group_id = 49 AND id = 298;
UPDATE civicrm_option_value SET weight = 5 WHERE option_group_id = 49 AND id = 299;
UPDATE civicrm_option_value SET weight = 6 WHERE option_group_id = 49 AND id = 300;
UPDATE civicrm_option_value SET weight = 7 WHERE option_group_id = 49 AND id = 301;
UPDATE civicrm_option_value SET weight = 8 WHERE option_group_id = 49 AND id = 302;
UPDATE civicrm_option_value SET weight = 9 WHERE option_group_id = 49 AND id = 303;
UPDATE civicrm_option_value SET weight = 10 WHERE option_group_id = 49 AND id = 304;
UPDATE civicrm_option_value SET weight = 11 WHERE option_group_id = 49 AND id = 305;
UPDATE civicrm_option_value SET weight = 12 WHERE option_group_id = 49 AND id = 306;
UPDATE civicrm_option_value SET weight = 13 WHERE option_group_id = 49 AND id = 307;
UPDATE civicrm_option_value SET weight = 14 WHERE option_group_id = 49 AND id = 308;
UPDATE civicrm_option_value SET weight = 15 WHERE option_group_id = 49 AND id = 309;
UPDATE civicrm_option_value SET weight = 16 WHERE option_group_id = 49 AND id = 310;
UPDATE civicrm_option_value SET weight = 17 WHERE option_group_id = 49 AND id = 311;
UPDATE civicrm_option_value SET weight = 18 WHERE option_group_id = 49 AND id = 312;
UPDATE civicrm_option_value SET weight = 19 WHERE option_group_id = 49 AND id = 313;
UPDATE civicrm_option_value SET weight = 20 WHERE option_group_id = 49 AND id = 314;
UPDATE civicrm_option_value SET weight = 21 WHERE option_group_id = 49 AND id = 315;
UPDATE civicrm_option_value SET weight = 22 WHERE option_group_id = 49 AND id = 316;
UPDATE civicrm_option_value SET weight = 23 WHERE option_group_id = 49 AND id = 317;
UPDATE civicrm_option_value SET weight = 24 WHERE option_group_id = 49 AND id = 318;
UPDATE civicrm_option_value SET weight = 25 WHERE option_group_id = 49 AND id = 319;
UPDATE civicrm_option_value SET weight = 26 WHERE option_group_id = 49 AND id = 320;
UPDATE civicrm_option_value SET weight = 27 WHERE option_group_id = 49 AND id = 321;
UPDATE civicrm_option_value SET weight = 28 WHERE option_group_id = 49 AND id = 1109;
UPDATE civicrm_option_value SET weight = 29 WHERE option_group_id = 49 AND id = 322;
UPDATE civicrm_option_value SET weight = 30 WHERE option_group_id = 49 AND id = 323;
UPDATE civicrm_option_value SET weight = 31 WHERE option_group_id = 49 AND id = 324;
UPDATE civicrm_option_value SET weight = 32 WHERE option_group_id = 49 AND id = 325;
UPDATE civicrm_option_value SET weight = 33 WHERE option_group_id = 49 AND id = 326;
UPDATE civicrm_option_value SET weight = 34 WHERE option_group_id = 49 AND id = 1096;
UPDATE civicrm_option_value SET weight = 35 WHERE option_group_id = 49 AND id = 327;
UPDATE civicrm_option_value SET weight = 36 WHERE option_group_id = 49 AND id = 328;
UPDATE civicrm_option_value SET weight = 37 WHERE option_group_id = 49 AND id = 329;
UPDATE civicrm_option_value SET weight = 38 WHERE option_group_id = 49 AND id = 330;
UPDATE civicrm_option_value SET weight = 39 WHERE option_group_id = 49 AND id = 331;
UPDATE civicrm_option_value SET weight = 40 WHERE option_group_id = 49 AND id = 332;
UPDATE civicrm_option_value SET weight = 41 WHERE option_group_id = 49 AND id = 333;
UPDATE civicrm_option_value SET weight = 42 WHERE option_group_id = 49 AND id = 334;
UPDATE civicrm_option_value SET weight = 43 WHERE option_group_id = 49 AND id = 335;
UPDATE civicrm_option_value SET weight = 44 WHERE option_group_id = 49 AND id = 336;
UPDATE civicrm_option_value SET weight = 45 WHERE option_group_id = 49 AND id = 1097;
UPDATE civicrm_option_value SET weight = 46 WHERE option_group_id = 49 AND id = 1098;
UPDATE civicrm_option_value SET weight = 48 WHERE option_group_id = 49 AND id = 1099;
UPDATE civicrm_option_value SET weight = 50 WHERE option_group_id = 49 AND id = 338;
UPDATE civicrm_option_value SET weight = 51 WHERE option_group_id = 49 AND id = 339;
UPDATE civicrm_option_value SET weight = 52 WHERE option_group_id = 49 AND id = 340;
UPDATE civicrm_option_value SET weight = 53 WHERE option_group_id = 49 AND id = 341;
UPDATE civicrm_option_value SET weight = 54 WHERE option_group_id = 49 AND id = 342;
UPDATE civicrm_option_value SET weight = 55 WHERE option_group_id = 49 AND id = 343;
UPDATE civicrm_option_value SET weight = 56 WHERE option_group_id = 49 AND id = 1100;
UPDATE civicrm_option_value SET weight = 57 WHERE option_group_id = 49 AND id = 344;
UPDATE civicrm_option_value SET weight = 58 WHERE option_group_id = 49 AND id = 345;
UPDATE civicrm_option_value SET weight = 59 WHERE option_group_id = 49 AND id = 346;
UPDATE civicrm_option_value SET weight = 60 WHERE option_group_id = 49 AND id = 347;
UPDATE civicrm_option_value SET weight = 61 WHERE option_group_id = 49 AND id = 348;
UPDATE civicrm_option_value SET weight = 62 WHERE option_group_id = 49 AND id = 349;
UPDATE civicrm_option_value SET weight = 63 WHERE option_group_id = 49 AND id = 350;
UPDATE civicrm_option_value SET weight = 64 WHERE option_group_id = 49 AND id = 351;
UPDATE civicrm_option_value SET weight = 65 WHERE option_group_id = 49 AND id = 352;
UPDATE civicrm_option_value SET weight = 66 WHERE option_group_id = 49 AND id = 353;
UPDATE civicrm_option_value SET weight = 67 WHERE option_group_id = 49 AND id = 354;
UPDATE civicrm_option_value SET weight = 68 WHERE option_group_id = 49 AND id = 355;
UPDATE civicrm_option_value SET weight = 69 WHERE option_group_id = 49 AND id = 356;
UPDATE civicrm_option_value SET weight = 70 WHERE option_group_id = 49 AND id = 357;
UPDATE civicrm_option_value SET weight = 71 WHERE option_group_id = 49 AND id = 358;
UPDATE civicrm_option_value SET weight = 72 WHERE option_group_id = 49 AND id = 366;
UPDATE civicrm_option_value SET weight = 73 WHERE option_group_id = 49 AND id = 365;
UPDATE civicrm_option_value SET weight = 74 WHERE option_group_id = 49 AND id = 363;
UPDATE civicrm_option_value SET weight = 75 WHERE option_group_id = 49 AND id = 360;
UPDATE civicrm_option_value SET weight = 76 WHERE option_group_id = 49 AND id = 359;
UPDATE civicrm_option_value SET weight = 77 WHERE option_group_id = 49 AND id = 361;
UPDATE civicrm_option_value SET weight = 78 WHERE option_group_id = 49 AND id = 368;
UPDATE civicrm_option_value SET weight = 79 WHERE option_group_id = 49 AND id = 364;
UPDATE civicrm_option_value SET weight = 80 WHERE option_group_id = 49 AND id = 362;
UPDATE civicrm_option_value SET weight = 81 WHERE option_group_id = 49 AND id = 367;
UPDATE civicrm_option_value SET weight = 82 WHERE option_group_id = 49 AND id = 369;
UPDATE civicrm_option_value SET weight = 83 WHERE option_group_id = 49 AND id = 370;
UPDATE civicrm_option_value SET weight = 84 WHERE option_group_id = 49 AND id = 371;
UPDATE civicrm_option_value SET weight = 85 WHERE option_group_id = 49 AND id = 372;
UPDATE civicrm_option_value SET weight = 86 WHERE option_group_id = 49 AND id = 373;
UPDATE civicrm_option_value SET weight = 87 WHERE option_group_id = 49 AND id = 374;
UPDATE civicrm_option_value SET weight = 88 WHERE option_group_id = 49 AND id = 375;
UPDATE civicrm_option_value SET weight = 89 WHERE option_group_id = 49 AND id = 376;
UPDATE civicrm_option_value SET weight = 90 WHERE option_group_id = 49 AND id = 377;
UPDATE civicrm_option_value SET weight = 91 WHERE option_group_id = 49 AND id = 378;
UPDATE civicrm_option_value SET weight = 92 WHERE option_group_id = 49 AND id = 379;
UPDATE civicrm_option_value SET weight = 93 WHERE option_group_id = 49 AND id = 428;
UPDATE civicrm_option_value SET weight = 94 WHERE option_group_id = 49 AND id = 380;
UPDATE civicrm_option_value SET weight = 95 WHERE option_group_id = 49 AND id = 381;
UPDATE civicrm_option_value SET weight = 96 WHERE option_group_id = 49 AND id = 382;
UPDATE civicrm_option_value SET weight = 97 WHERE option_group_id = 49 AND id = 383;
UPDATE civicrm_option_value SET weight = 98 WHERE option_group_id = 49 AND id = 384;
UPDATE civicrm_option_value SET weight = 99 WHERE option_group_id = 49 AND id = 390;
UPDATE civicrm_option_value SET weight = 100 WHERE option_group_id = 49 AND id = 385;
UPDATE civicrm_option_value SET weight = 101 WHERE option_group_id = 49 AND id = 393;
UPDATE civicrm_option_value SET weight = 102 WHERE option_group_id = 49 AND id = 388;
UPDATE civicrm_option_value SET weight = 103 WHERE option_group_id = 49 AND id = 389;
UPDATE civicrm_option_value SET weight = 104 WHERE option_group_id = 49 AND id = 391;
UPDATE civicrm_option_value SET weight = 105 WHERE option_group_id = 49 AND id = 392;
UPDATE civicrm_option_value SET weight = 106 WHERE option_group_id = 49 AND id = 387;
UPDATE civicrm_option_value SET weight = 107 WHERE option_group_id = 49 AND id = 386;
UPDATE civicrm_option_value SET weight = 108 WHERE option_group_id = 49 AND id = 395;
UPDATE civicrm_option_value SET weight = 109 WHERE option_group_id = 49 AND id = 396;
UPDATE civicrm_option_value SET weight = 110 WHERE option_group_id = 49 AND id = 397;
UPDATE civicrm_option_value SET weight = 111 WHERE option_group_id = 49 AND id = 398;
UPDATE civicrm_option_value SET weight = 112 WHERE option_group_id = 49 AND id = 399;
UPDATE civicrm_option_value SET weight = 113 WHERE option_group_id = 49 AND id = 394;
UPDATE civicrm_option_value SET weight = 114 WHERE option_group_id = 49 AND id = 400;
UPDATE civicrm_option_value SET weight = 115 WHERE option_group_id = 49 AND id = 401;
UPDATE civicrm_option_value SET weight = 116 WHERE option_group_id = 49 AND id = 402;
UPDATE civicrm_option_value SET weight = 117 WHERE option_group_id = 49 AND id = 403;
UPDATE civicrm_option_value SET weight = 118 WHERE option_group_id = 49 AND id = 404;
UPDATE civicrm_option_value SET weight = 119 WHERE option_group_id = 49 AND id = 405;
UPDATE civicrm_option_value SET weight = 120 WHERE option_group_id = 49 AND id = 409;
UPDATE civicrm_option_value SET weight = 121 WHERE option_group_id = 49 AND id = 408;
UPDATE civicrm_option_value SET weight = 122 WHERE option_group_id = 49 AND id = 407;
UPDATE civicrm_option_value SET weight = 123 WHERE option_group_id = 49 AND id = 434;
UPDATE civicrm_option_value SET weight = 124 WHERE option_group_id = 49 AND id = 411;
UPDATE civicrm_option_value SET weight = 125 WHERE option_group_id = 49 AND id = 406;
UPDATE civicrm_option_value SET weight = 126 WHERE option_group_id = 49 AND id = 410;
UPDATE civicrm_option_value SET weight = 127 WHERE option_group_id = 49 AND id = 412;
UPDATE civicrm_option_value SET weight = 128 WHERE option_group_id = 49 AND id = 414;
UPDATE civicrm_option_value SET weight = 129 WHERE option_group_id = 49 AND id = 415;
UPDATE civicrm_option_value SET weight = 130 WHERE option_group_id = 49 AND id = 416;
UPDATE civicrm_option_value SET weight = 131 WHERE option_group_id = 49 AND id = 418;
UPDATE civicrm_option_value SET weight = 132 WHERE option_group_id = 49 AND id = 417;
UPDATE civicrm_option_value SET weight = 133 WHERE option_group_id = 49 AND id = 419;
UPDATE civicrm_option_value SET weight = 134 WHERE option_group_id = 49 AND id = 421;
UPDATE civicrm_option_value SET weight = 135 WHERE option_group_id = 49 AND id = 420;
UPDATE civicrm_option_value SET weight = 136 WHERE option_group_id = 49 AND id = 424;
UPDATE civicrm_option_value SET weight = 137 WHERE option_group_id = 49 AND id = 422;
UPDATE civicrm_option_value SET weight = 138 WHERE option_group_id = 49 AND id = 423;
UPDATE civicrm_option_value SET weight = 139 WHERE option_group_id = 49 AND id = 1101;
UPDATE civicrm_option_value SET weight = 140 WHERE option_group_id = 49 AND id = 425;
UPDATE civicrm_option_value SET weight = 141 WHERE option_group_id = 49 AND id = 426;
UPDATE civicrm_option_value SET weight = 142 WHERE option_group_id = 49 AND id = 429;
UPDATE civicrm_option_value SET weight = 143 WHERE option_group_id = 49 AND id = 427;
UPDATE civicrm_option_value SET weight = 144 WHERE option_group_id = 49 AND id = 430;
UPDATE civicrm_option_value SET weight = 145 WHERE option_group_id = 49 AND id = 435;
UPDATE civicrm_option_value SET weight = 146 WHERE option_group_id = 49 AND id = 436;
UPDATE civicrm_option_value SET weight = 147 WHERE option_group_id = 49 AND id = 431;
UPDATE civicrm_option_value SET weight = 148 WHERE option_group_id = 49 AND id = 432;
UPDATE civicrm_option_value SET weight = 149 WHERE option_group_id = 49 AND id = 438;
UPDATE civicrm_option_value SET weight = 150 WHERE option_group_id = 49 AND id = 437;
UPDATE civicrm_option_value SET weight = 151 WHERE option_group_id = 49 AND id = 439;
UPDATE civicrm_option_value SET weight = 152 WHERE option_group_id = 49 AND id = 433;
UPDATE civicrm_option_value SET weight = 153 WHERE option_group_id = 49 AND id = 440;
UPDATE civicrm_option_value SET weight = 154 WHERE option_group_id = 49 AND id = 441;
UPDATE civicrm_option_value SET weight = 155 WHERE option_group_id = 49 AND id = 442;
UPDATE civicrm_option_value SET weight = 156 WHERE option_group_id = 49 AND id = 443;
UPDATE civicrm_option_value SET weight = 158 WHERE option_group_id = 49 AND id = 413;
UPDATE civicrm_option_value SET weight = 159 WHERE option_group_id = 49 AND id = 444;
UPDATE civicrm_option_value SET weight = 161 WHERE option_group_id = 49 AND id = 445;
UPDATE civicrm_option_value SET weight = 162 WHERE option_group_id = 49 AND id = 446;
UPDATE civicrm_option_value SET weight = 163 WHERE option_group_id = 49 AND id = 447;
UPDATE civicrm_option_value SET weight = 164 WHERE option_group_id = 49 AND id = 448;
UPDATE civicrm_option_value SET weight = 165 WHERE option_group_id = 49 AND id = 449;
UPDATE civicrm_option_value SET weight = 166 WHERE option_group_id = 49 AND id = 457;
UPDATE civicrm_option_value SET weight = 167 WHERE option_group_id = 49 AND id = 464;
UPDATE civicrm_option_value SET weight = 168 WHERE option_group_id = 49 AND id = 452;
UPDATE civicrm_option_value SET weight = 169 WHERE option_group_id = 49 AND id = 450;
UPDATE civicrm_option_value SET weight = 170 WHERE option_group_id = 49 AND id = 462;
UPDATE civicrm_option_value SET weight = 171 WHERE option_group_id = 49 AND id = 451;
UPDATE civicrm_option_value SET weight = 172 WHERE option_group_id = 49 AND id = 453;
UPDATE civicrm_option_value SET weight = 173 WHERE option_group_id = 49 AND id = 455;
UPDATE civicrm_option_value SET weight = 174 WHERE option_group_id = 49 AND id = 454;
UPDATE civicrm_option_value SET weight = 175 WHERE option_group_id = 49 AND id = 459;
UPDATE civicrm_option_value SET weight = 176 WHERE option_group_id = 49 AND id = 461;
UPDATE civicrm_option_value SET weight = 177 WHERE option_group_id = 49 AND id = 458;
UPDATE civicrm_option_value SET weight = 178 WHERE option_group_id = 49 AND id = 460;
UPDATE civicrm_option_value SET weight = 179 WHERE option_group_id = 49 AND id = 456;
UPDATE civicrm_option_value SET weight = 180 WHERE option_group_id = 49 AND id = 463;
UPDATE civicrm_option_value SET weight = 181 WHERE option_group_id = 49 AND id = 465;
UPDATE civicrm_option_value SET weight = 182 WHERE option_group_id = 49 AND id = 466;
UPDATE civicrm_option_value SET weight = 183 WHERE option_group_id = 49 AND id = 467;
UPDATE civicrm_option_value SET weight = 184 WHERE option_group_id = 49 AND id = 468;
UPDATE civicrm_option_value SET weight = 185 WHERE option_group_id = 49 AND id = 469;
UPDATE civicrm_option_value SET weight = 186 WHERE option_group_id = 49 AND id = 470;
UPDATE civicrm_option_value SET weight = 187 WHERE option_group_id = 49 AND id = 471;
UPDATE civicrm_option_value SET weight = 188 WHERE option_group_id = 49 AND id = 472;
UPDATE civicrm_option_value SET weight = 189 WHERE option_group_id = 49 AND id = 473;
UPDATE civicrm_option_value SET weight = 190 WHERE option_group_id = 49 AND id = 475;
UPDATE civicrm_option_value SET weight = 191 WHERE option_group_id = 49 AND id = 474;
UPDATE civicrm_option_value SET weight = 192 WHERE option_group_id = 49 AND id = 476;
UPDATE civicrm_option_value SET weight = 192 WHERE option_group_id = 49 AND id = 477;
UPDATE civicrm_option_value SET weight = 193 WHERE option_group_id = 49 AND id = 478;
UPDATE civicrm_option_value SET weight = 194 WHERE option_group_id = 49 AND id = 479;
UPDATE civicrm_option_value SET weight = 195 WHERE option_group_id = 49 AND id = 480;"
$execSql -i $instance -c "$languages"


### Cleanup ###

$script_dir/fixPermissions.sh
$script_dir/clearCache.sh $instance
