<?php

function get_connection($config) {
    $conn_str = "{$config['signups.db.user']}:{$config['signups.db.pass']}@{$config['signups.db.host']}:{$config['signups.db.port']}";

    if(! $conn = mysql_connect("{$config['signups.db.host']}:{$config['signups.db.port']}",$config['signups.db.user'],$config['signups.db.pass']))
        die("Could not establish connection to $conn_str\n".mysql_error());

    if(!mysql_select_db($config['signups.db.name'], $conn))
        die("Could not use database {$config['signups.db.name']} at $conn_str\n".mysql_error($conn));

    return $conn;
}

function get_report_path($config, $options) {
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
