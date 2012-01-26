#!/usr/bin/php
<?php

//Bootstrap the script and progress the command line arguments
require_once realpath(dirname(__FILE__).'/../script_utils.php');
add_packages_to_include_path();
$prog = basename(__FILE__);
$script_dir = dirname(__FILE__);
$short_opts = 'h';
$long_opts = array('help');

if(!($optList = process_cli_args($short_opts, $long_opts)) || $optList['help'] ) {
    die("$prog [--help|-h]");
}

if(! $config = parse_ini_file("$script_dir/reports.cfg", true)) {
    die("$prog: config file reports.cfg not found.");
}

$conn = get_connection($config['database']);
$ingest_script = $script_dir . PATH_SEPARATOR . "ingest.php";
$generate_script = $script_dir . PATH_SEPARATOR . "generate.php";
$email_script = $script_dir . PATH_SEPARATOR . "email.php";
$report_dir = realpath("$script_dir/{$config['reports']['directory']}/".date($config['reports']['date_format']));

if($optList['ingest'] || $optList['all']) {
    // Update the signups database, this involves geocoding and may take a while
    shell_exec("php $ingest_script --all");
}

if($optList['generate'] || $optList['all']) {
    shell_exec("mkdir $report_dir");
    foreach($config['districts'] as $key => $value) {
        list($district, $instance) = explode('_',$key);
        shell_exec("php $generate_script --site $instance --district $district --folder $report_dir");
    }
}

if($optList['email'] || $optList['all']) {
    foreach($config['districts'] as $key => $value) {
        list($district, $instance) = explode('_',$key);
        shell_exec("php $email_script --site $instance --district $district --folder $report_dir");
    }
}

?>