<?php
// Project: BluebirdCRM
// Authors: Stefan Crain, Graylin Kim, Ken Zalewski
// Organization: New York State Senate
// Date: 2012-10-26
// Revised: 2012-11-21

// ./Redistricting.php -S skelos --batch 2000 --log 5 --max 10000
error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

// Parse the following user options
require_once realpath(dirname(__FILE__)).'/../script_utils.php';
$shortopts = "i:el:d";
$longopts = array("import=","export","log=","dryrun");
$stdusage = civicrm_script_usage();
$usage = '[--import FILENAME] [--export] [--log] [--dryrun]';
$optlist = civicrm_script_init($shortopts, $longopts);

if ($optlist === null) {
    error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
    exit(1);
}

// Parse the options and spit them out on debug
$BB_LOG_LEVEL = $LOG_LEVELS[strtoupper(get($optlist, 'log', 'info'))][0];
$BB_DRY_RUN = get($optlist, 'dryrun', FALSE);
bbscript_log(LL::DEBUG, "Option: LOG_LEVEL=$BB_LOG_LEVEL");
bbscript_log(LL::DEBUG, "Option: DRY_RUN=".($BB_DRY_RUN ? "TRUE" : "FALSE"));

// Get CiviCRM database connection
require_once 'CRM/Core/Config.php';
require_once 'CRM/Core/DAO.php';
$config =& CRM_Core_Config::singleton();
$dao = new CRM_Core_DAO();
$db = $dao->getDatabaseConnection()->connection;


if ($optlist['import']) {
    do_import($db, $optlist['import'], $BB_DRY_RUN);
} else if ($optlist['export']) {
    do_export($db);
} else {
    error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
    exit(1);
}


function do_export($db) {
    $result = bb_mysql_query("
        SELECT  address.id as ID,
                IFNULL(street_address,'') as STREET_ADDRESS,
                IFNULL(city,'') as CITY,
                state.abbreviation as STATE,
                IFNULL(postal_code,'') as ZIP5,
                IFNULL(postal_code_suffix,'') as ZIP4
        FROM civicrm_address as address
        JOIN civicrm_state_province as state ON state.id=address.state_province_id",$db);

    echo "id\tDelivery Address\tCity\tState\tZIP\tPlus 4\n";
    while (($row = mysqli_fetch_assoc($result)) != null) {
        echo implode("\t",array_values($row))."\n";
    }
}


function do_import($db, $filename, $BB_DRY_RUN) {
    require_once 'CRM/Core/BAO/Address.php';

    $aStates = array_flip(ioGetStates());

    if (($handle = fopen($filename, 'r')) === FALSE) {
        bbscript_log(LL::FATAL,"Could not open `$filename` for reading.");
        exit(1);
    }

    // TODO: CRM_Core_BAO_Address::parseStreetAddress has a fuller list of these
    $units_full = array('unit', 'super', 'lot', 'fl', 'ste', 'rm', 'pvt', 'sup', 'supt', 'bsmt', '#', 'apt');

    $count = 0;
    bb_mysql_query("BEGIN", $db);
    $header = fgets($handle);
    while ( ($line = fgets($handle)) !== FALSE) {
        bbscript_log(LL::TRACE, $line);
        $parts = explode("\t",$line);

        // Basic Info, don't use the town for fear of destroying Don's hard work
        $address_id = $parts[0];
        $state_province_id = $aStates[$parts[3]];
        $postal_code = count($parts) >= 5 ? $parts[4] : "";
        $postal_code_suffix = count($parts) == 6 ? $parts[5] : "";

        // Part the street address into its components
        $parsedFields = CRM_Core_BAO_Address::parseStreetAddress(strtoupper(trim($parts[1])));
        $street_number = trim($parsedFields['street_number']);
        $street_number_suffix = trim($parsedFields['street_number_suffix']);
        $street_name   = convertProperCase(trim($parsedFields['street_name']));
        $street_unit   = trim($parsedFields['street_unit']);

        if ( $street_unit ) {
            $unit_parts = explode(' ',$street_unit);
            $unit = strtolower(trim($unit_parts[0],'.'));

            // if there is no "apt" text, prepend to value
            if (!in_array($unit, $units_full)) {
                array_unshift($unit_parts, "APT");
            }

            // fix casing up, be really careful about what casing is affected
            $new_parts = array();
            foreach ($unit_parts as $part) {
                if (!preg_match('/^[0-9]+(?!ST|ND|RD|TH)/',$part)) {
                    $part = ucwords(strtolower($part));
                }
                $new_parts[] = $part;
                
            }

            $street_unit = implode(' ',$new_parts);
        }

        // Build the street address from the finalized, formatted components
        if ($street_number_suffix == '1/2') {
            $street_number_suffix = ' 1/2';
        }
        $street_address = ($street_number!=0 ? $street_number : '')."$street_number_suffix $street_name $street_unit";

        // Format the old and new values for comparison
        $result = bb_mysql_query("SELECT street_address, street_number, street_number_suffix, street_name, street_unit, postal_code from civicrm_address WHERE id=$address_id",$db);
        $old_address = mysqli_fetch_assoc($result);
        $old_address['street_address'] = mysqli_escape_string($old_address['street_address']);
        $old_address['street_name'] = mysqli_escape_string($old_address['street_name']);
        $new_address = array(
                'street_address' => clean($street_address),
                'street_number' => clean($street_number),
                'street_number_suffix' => clean($street_number_suffix),
                'street_name' => clean($street_name),
                'street_unit' => ($street_unit ? substr(clean($street_unit),0,16) : ""), // varchar(16) in database
                'postal_code' => clean($postal_code),
            );
        $diff = array_diff_assoc($old_address, $new_address);
        if (count($diff) != 0) {
            if ($GLOBALS['BB_LOG_LEVEL'] <= $GLOBALS['LOG_LEVELS']['DEBUG'][0]) {
                echo "#{$parts[0]} - {$parts[1]}\n";
                echo $parsedFields['street_number'].' | '.$parsedFields['street_number_suffix'].' | '.$parsedFields['street_name'].' | '.$parsedFields['street_unit']."\n";
                print_r($old_address);
                print_r($new_address);
                echo "Differences: ".implode(', ',array_keys($diff));
                echo "\n\n=====================================================================================\n";
            }
            $changed[$address_id] = $diff;
        }

        // Make sure to escape all the values before they hit the db
        $query = "UPDATE civicrm_address
                  SET state_province_id=".clean($state_province_id).",
                      postal_code='".clean($postal_code)."',
                      postal_code_suffix='".clean($postal_code_suffix)."',
                      street_address = '".clean($street_address)."',
                      street_number = ".clean($street_number).",
                      street_number_suffix = '".clean($street_number_suffix)."',
                      street_name = '".clean($street_name)."',
                      street_unit = '".clean($street_unit)."'
                  WHERE id=$address_id";

        bbscript_log(LL::TRACE, $query);
        if (!$BB_DRY_RUN) {
           bb_mysql_query($query, $db);
        }

        // Just to show progres while running
        if (++$count % 10000 == 0) {
            bbscript_log(LL::INFO,"$count addresses imported. ".count($changed)." changed.");
            bb_mysql_query("COMMIT",$db);
            bb_mysql_query("BEGIN", $db);
        }
    }
    bb_mysql_query("COMMIT", $db);
}


function get($array, $key, $default) {
    // blank, null, and 0 values are bad.
    return isset($array[$key]) && $array[$key]!=NULL && $array[$key]!=="" && $array[$key]!==0 && $array[$key]!=="000" ? $array[$key] : $default;
}


function clean($value) {
    require_once 'CRM/Utils/String.php';
    if (is_string($value)) {
        $value = CRM_Utils_String::stripSpaces($value);
    }
    return mysqli_real_escape_string($value);
}


function convertProperCase( $string, $skipMixed = false, $skipSpecial = false ) {
    require_once 'CRM/Utils/String.php';

    //if mixed case, don't do anything
    if ($skipMixed && preg_match('/[a-z]/', $string)) return $string;

    $string = CRM_Utils_String::stripSpaces( ucwords(strtolower($string)) );

    //if we skip special words processing, return now
    if ($skipSpecial) return $string;

    // list of words we want to force
    $forceWords = array('of', 'the', 'and', 'an', 'or', 'nor', 'but', 'is', 'if', 'then',
                    'else', 'when', 'at', 'from', 'by', 'on', 'off', 'for', 'in', 'out', 'over', 'to',
                    'into', 'with', 'II', 'IV', 'UK', 'VI', 'III', 'VII', 'PO', 'McDonald');

    // punctuation used to determine that the following letter
    // should be capitalised
    $punctuation = array('.', '-', ':', '!', '\'', '-', '?');

    $words = explode(' ', $string);

    foreach ($words as $word) {
        $replace = array();

        //trim any non-word chars and replace with nothing for easier matching
        $cleanWord = preg_replace("/[^\w]/", '', $word);
        if (!empty($cleanWord)) $replace = preg_grep( "/\b{$cleanWord}\b/i", $forceWords);
        $replace = array_values($replace);
        if (isset($replace[0])) $word = str_replace($cleanWord,$replace[0],$word);

        $fixedWords[] = $word;
    }

    $string = implode(' ',$fixedWords);

    return $string;
}


function ioGetStates()
{
  $session =& CRM_Core_Session::singleton();

  $dao = &CRM_Core_DAO::executeQuery("SELECT id, abbreviation FROM civicrm_state_province WHERE country_id = 1228", CRM_Core_DAO::$_nullArray); //lookup US states only

  $options = array();

  while ($dao->fetch()) {
    $options[$dao->id] = $dao->abbreviation;
  }

  return $options;
} // ioGetStates()

?>
