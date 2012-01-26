#!/usr/bin/php
<?php

//Bootstrap the script and progress the command line arguments
require_once 'utils.php';
require_once realpath(dirname(__FILE__).'/../script_utils.php');
add_packages_to_include_path();
$prog = basename(__FILE__);
$script_dir = dirname(__FILE__);
$short_opts = 'hiugeaf:';
$long_opts = array('help','ingest','update','generate','email','all', 'folder=');

if(!($optList = process_cli_args($short_opts, $long_opts)) || $optList['help'] ) {
    die("$prog [--help|-h] [--ingest [--update]] [--generate] [--email ] [--all] [--folder FOLDER]");
}

if(! $config = parse_ini_file("$script_dir/reports.cfg", true)) {
    die("$prog: config file reports.cfg not found.");
}

$conn = get_connection($config['database']);
$ingest_script = "$script_dir/ingest.php";
$generate_script = "$script_dir/generate.php";
$email_script = "$script_dir /email.php";

if(!$optList['folder'])
    $report_dir = realpath("$script_dir/{$config['reports']['directory']}/".date($config['reports']['date_format']));
else
    $report_dir = $optList['folder'];

if($optList['ingest'] || $optList['all']) {
    // Update the signups database, this involves geocoding and may take a while
    log_("Starting Ingest...");
    if($optList['update']) {
        log_("  Updating Senators");
        log_(`php $ingest_script --senators`, true);
        log_("  Updating Committees");
        log_(`php $ingest_script --committees`, true);
    }

    log_("  Fetching Signups");
    log_(`php $ingest_script --signups`, true);
    log_("  Gecoding Addresses");
    log_(`php $ingest_script --geocode`, true);
    log_("Ingest Completed.");
}

if($optList['generate'] || $optList['all']) {
    log_("Generating Reports...");
    if(!is_dir($report_dir)) {
        log_("  Creating directory $report_dir.");
        mkdir($report_dir,0777,true);
    }

    foreach($config['districts'] as $key => $value) {
        list($district, $instance) = explode('_',$key);
        log_("  Created {$district}_{$instance}.xls");
        log_(`php $generate_script --site $instance --district $district --folder $report_dir`, true);
    }
}

if($optList['email'] || $optList['all']) {
    log_("Emailing Reports...");
    foreach($config['districts'] as $key => $value) {
        list($district, $instance) = explode('_',$key);
        log_("  Emailing {$district}_{$instance}.xls to $value");
        log_(`php $email_script --site $instance --district $district --folder $report_dir`, true);
    }
}

function log_($message,$subprocess=false) {
    if(!$message = rtrim($message)) return;

    if($subprocess) {
        foreach(explode("\n",$message) as $line) {
            echo "                           $line\n";
        }
    } else {
        $date = date("Y.m.d H:i:s");
        echo "[$date] $message\n";
    }
}

?>