<?php

function get_connection($config) {
    $conn_str = "{$config['user']}:{$config['pass']}@{$config['host']}:{$config['port']}";

    if(! $conn = mysql_connect("{$config['host']}:{$config['port']}",$config['user'],$config['pass']))
        die("Could not establish connection to $conn_str\n".mysql_error());

    if(!mysql_select_db($config['name'], $conn))
        die("Could not use database {$config['name']} at $conn_str\n".mysql_error($conn));

    return $conn;
}

function get_report_name($district, $site, $config) {
    $file_name = str_replace(
        array('<district>', '<site>'),
        array($district,    $site),
        $config['name_template']
    );
    return "$file_name.xls";
}

?>