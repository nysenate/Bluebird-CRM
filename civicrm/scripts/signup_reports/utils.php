<?php

function get_connection($config) {
    $conn_str = "{$config['signups.db.user']}:{$config['signups.db.pass']}@{$config['signups.db.host']}:{$config['signups.db.port']}";

    if(! $conn = mysql_connect("{$config['signups.db.host']}:{$config['signups.db.port']}",$config['signups.db.user'],$config['signups.db.pass']))
        die("Could not establish connection to $conn_str\n".mysql_error());

    if(!mysql_select_db($config['signups.db.name'], $conn))
        die("Could not use database {$config['signups.db.name']} at $conn_str\n".mysql_error($conn));

    return $conn;
}

function get_report_name($district, $site, $template) {
    $file_name = str_replace(
        array('<district>', '<site>'),
        array($district,    $site),
        $template
    );
    return "$file_name.xls";
}

?>
