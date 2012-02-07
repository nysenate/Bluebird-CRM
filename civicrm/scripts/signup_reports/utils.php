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


function get_report_path($config, $options)
{
    $directory = $config['data.rootdir'].'/'.$config['servername'].'/'.$config['signups.reports.dirname'];

    $date = $options['date'];
    if(!$date) {
        $date = date($config['signups.reports.date_format']);
    }

    if(!is_dir($directory)) {
        if(!mkdir($directory, 0777, true)) {
            die("Could not create $directory\n");
        }
    }

    $template_options = array(
        '<date>' => $date,
        '<instance>' => $options['site']
    );

    return str_replace(
        array_keys($template_options),
        array_values($template_options),
        "$directory/{$config['signups.reports.name_template']}"
    );
}

?>
