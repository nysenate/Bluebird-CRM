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
#    - Filtered by: names appearing in more then one year
#    - result = 87,928
# cat *.txt | tr '[:upper:]' '[:lower:]' | sed -e 's/[^a-zA-Z]//g' | awk 'length>=4' | sort | uniq --count | sort -nr | while read -r n d ; do [ $n -gt 9 ] && printf '%s\n' "$d" ; done | sort > ss_output.txt

# NYS DMV First-Name dump
#    - Filtered by: names appearing in more then 9 times
#    - result = 450,398
# cat DMV_fn.txt | tr '[:upper:]' '[:lower:]' | sed -e 's/[^a-zA-Z]//g' | awk 'length>=4' | sort | uniq --count | sort -nr | while read -r n d ; do [ $n -gt 9 ] && printf '%s\n' "$d" ; done | sort > dmv_output.txt

# merge the 3 -
# cat *_output.txt | sed 's|\r$||' | sort -u | uniq > firstNames_DMV_SS_BLUEBIRD.txt

# Hopefully the filtering eliminates some data entry typos

# From there we filter by:
# - removing hyphens
# - removing words less then 4 characters
# - forcing everything to lowercase
# - only allowing unique
# - sorted alphabetically
# cat firstNames_DMV_SS_BLUEBIRD.txt | sed 's|\r$||' | sed 's|-||' | sed 's|-||' |  awk 'length>=4' | tr '[:upper:]' '[:lower:]' | sort -u | uniq > firstNames_DMV_SS_BLUEBIRD.txt

# remove words that are problematic
# cat '/home/stefan/Desktop/names_final/firstNames_DMV_SS_BLUEBIRD.txt' | sed 's|\r$||' | sed 's|-||' | sed 's|-||' |  awk 'length>=4' | tr '[:upper:]' '[:lower:]' | sort -u | uniq | sed 's/^first$//g;s/^second$//g;s/^third$//g;s/^fourth$//g;s/^fifth$//g;s/^sixth$//g;s/^seventh$//g;s/^eighth$//g;s/^ninth$//g;s/^tenth$//g;s/^eleventh$//g;s/^twelfth$//g;s/^thirteenth$//g;s/^fourteenth$//g;s/^fifteenth$//g;s/^sixteenth$//g;s/^seventeenth$//g;s/^eighteenth$//g;s/^nineteenth$//g;s/^twentieth$//g;s/^dear$//g;s/^adjutant$//g;s/^administrative$//g;s/^admiral$//g;s/^assemblyman$//g;s/^assemblymember$//g;s/^assemblywoman$//g;s/^bishop$//g;s/^brigadier$//g;s/^brother$//g;s/^cadet$//g;s/^cantor$//g;s/^captain$//g;s/^chancellor$//g;s/^chief$//g;s/^warrant$//g;s/^officer$//g;s/^colonel$//g;s/^commander$//g;s/^congressman$//g;s/^ensign$//g;s/^first$//g;s/^lieutenant$//g;s/^lieutenant$//g;s/^colonel$//g;s/^commander$//g;s/^junior$//g;s/^grade$//g;s/^major$//g;s/^general$//g;s/^mayor$//g;s/^monsignor$//g;s/^reverend$//g;s/^justice$//g;s/^pastor$//g;s/^professor$//g;s/^rabbi$//g;s/^admiral$//g;s/^reverend$//g;s/^monsignor$//g;s/^mother$//g;s/^lieutenant$//g;s/^senator$//g;s/^sergeant$//g;s/^sheriff$//g;s/^sister$//g;s/^chief$//g;s/^justice$//g;s/^honorable$//g;s/^vice$//g;s/^admiral$//g;s/^warrant$//g;s/^officer$//g;s/^state$//g;s/^new$//g;s/^doctor$//g;s/^mister$//g;s/^canon$//g;s/^dame$//g;s/^chief$//g;s/^sister$//g;s/^other$//g;s/^viscount$//g;s/^viscountess$//g;s/^baroness$//g;s/^master$//g;s/^revd$//g;s/^lady$//g;s/^king$//g;s/^lord$//g;s/^queen$//g;s/^east$//g;s/^west$//g;s/^north$//g;s/^south$//g;s/^Vermont$//g;' | sort -u | uniq > firstNames_DMV_SS_BLUEBIRD.txt



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
