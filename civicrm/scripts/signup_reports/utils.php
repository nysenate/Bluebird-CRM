<?php

function get_connection($config)
{
    $dbuser = $config['signups.db.user'];
    $dbpass = $config['signups.db.pass'];
    $dbhost = $config['signups.db.host'];
    $dbport = (isset($config['signups.db.port'])) ? ':'.$config['signups.db.port'] : '';
    $dbname = $config['signups.db.name'];

    $conn_str = "$dbuser@$dbhost$dbport";
    $conn = mysql_connect("$dbhost$dbport", $dbuser, $dbpass);
    if (!$conn) {
        die("Could not establish connection to $conn_str\n".mysql_error());
    }

    if (!mysql_select_db($dbname, $conn)) {
        die("Could not use database $dbname at $conn_str\n".mysql_error($conn));
    }

    return $conn;
} // get_connection()


function get_report_path($bbcfg, $target_date)
{
    $datadir = $bbcfg['data.rootdir'];
    $sitedir = $bbcfg['servername'];
    $instance = $bbcfg['shortname'];
    $dirname = $bbcfg['signups.reports.dirname'];

    $dirpath = "$datadir/$sitedir/$dirname"

    if ($target_date == null) {
        if (isset($bbcfg['signups.reports.date_format'])) {
            $datefmt = $bbcfg['signups.reports.date_format'];
        }
        else {
            $datefmt = "Y.m.d";
        }
        $target_date = date($datefmt);
    }

    if (!is_dir($dirpath)) {
        if (!mkdir($dirpath, 0777, true)) {
            die("Could not create $dirpath\n");
        }
    }

    $template_options = array(
        '<date>' => $date,
        '<instance>' => $instance
    );

    if (isset($bbcfg['signups.reports.name_template'])) {
        $name_template = $bbcfg['signups.reports.name_template'];
    }
    else {
        $name_template = 'signups_<instance>_<date>.xls';
    }

    $filename = str_replace(array_keys($template_options),
                            array_values($template_options),
                            $name_template);
    return "$dirpath/$filename";
}

?>
