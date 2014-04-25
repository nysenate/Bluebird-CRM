<?php
/* Author: Stefan Crain
 * Organization: New York State Senate
 * Date: 2014-04-22
 *
 * Import List of First names, especially useful in name detection
 *
 */

# firstNames_DMV_SS_BLUEBIRD.txt compiled from:

# Bluebird contact dump on 4-15-2014
#    - Filtered by: names appearing in more then four instances
#    - result = 74,360
# cat *.tsv | while read -r n d ; do printf '%s\n' "$d" ; done | tr '[:upper:]' '[:lower:]' | sed -e 's/[^a-zA-Z]//g' | awk 'length>=4' | sort | uniq --count | sort -nr | while read -r n d ; do [ $n -gt 4 ] && printf '%s\n' "$d" ; done | sort > bb_output.txt

# US Social security issued names - 1890 to 2013 - http://www.ssa.gov/oact/babynames/names.zip
#    - Filtered by: names appearing in more then four years
#    - result = 87,928
# cat *.txt | tr '[:upper:]' '[:lower:]' | sed -e 's/[^a-zA-Z]//g' | awk 'length>=4' | sort | uniq --count | sort -nr | while read -r n d ; do [ $n -gt 9 ] && printf '%s\n' "$d" ; done | sort > ss_output.txt

# NYS DMV First-Name dump
#    - Filtered by: names appearing in more then 9 times
#    - result = 450,398
# cat DMV_fn.txt | tr '[:upper:]' '[:lower:]' | sed -e 's/[^a-zA-Z]//g' | awk 'length>=4' | sort | uniq --count | sort -nr | while read -r n d ; do [ $n -gt 9 ] && printf '%s\n' "$d" ; done | sort > dmv_output.txt

# merge the 3 -
# cat *_output.txt | sed 's|\r$||' | sort -u | uniq > firstNames_DMV_SS_BLUEBIRD.txt

# Hopefully the filtering eliminates some data entry typos

$start = microtime(true);
error_reporting(E_ALL & ~E_NOTICE);
set_time_limit(0);
require_once 'script_utils.php';

// Parse th
$shortopts = "d";
$longopts = array("dryrun");
$usage = '[--dryrun]';
$optlist = civicrm_script_init($shortopts, $longopts, TRUE);
if ($optlist === null) {
  $stdusage = civicrm_script_usage();
  error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
  exit(1);
}
// load cli args
if (!empty($optlist['dryrun'])) {
  $dryrun = $optlist['dryrun'];
}

// get instance settings
// build a PDO object
$bbcfg = get_bluebird_instance_config($optlist['site']);
$db = new PDO('mysql:host='.$bbcfg['db.host'].';dbname='.$bbcfg['db.civicrm.prefix'].$bbcfg['db.basename'].';charset=utf8', $bbcfg['db.user'], $bbcfg['db.pass']);
$db->beginTransaction();

// parse a txt document
$filename = $bbcfg['app.rootdir']."/modules/nyss_dedupe/source/firstNames_DMV_SS_BLUEBIRD.txt";
$fp = @fopen($filename, 'r');
if ($fp) {
   $names = explode("\n", fread($fp, filesize($filename)));
}

// insert the updates
echo "Inserting ".count($names)." Found names into database \n";
// count out percents
$block = (count($names) / 10);
$count = 0;

foreach ($names as $id => $name) {
    if (!$dryrun) {
        $result = $db->exec("INSERT INTO `fn_group` (given,new) VALUES ('$name',2);");
    }
    if ($id > 0 && $id % $block == 0) {
        $count = $count + 10;
        echo $count."%\n";
    }
}
$db->commit();
echo "100%\n";

// do some reporting
$end = microtime(true);
$time = $end - $start;
echo "Completed Insert in ".round(($time),1)." seconds \n";
echo round((count($names) / $time),1)." Processed Records / second \n";
