#!/usr/bin/php
<?php

// Bootstrap the script and progress the command line arguments
require_once realpath(dirname(__FILE__).'/../script_utils.php');
add_packages_to_include_path();
$optList = get_options();


// Load the global configuration
require_once realpath(dirname(__FILE__).'/../bluebird_config.php');
$config = get_bluebird_config();
$instances = explode(' ',$config['instance_sets']['signups']);


// Secure a databsae connection
require_once 'utils.php';
$conn = get_connection($config['globals']);

// Preload pathing shortcuts
$report_dir = dirname(__FILE__);
$script_dir = dirname(dirname(__FILE__));
$ingest_script = "$report_dir/ingest.php";
$generate_script = "$report_dir/generate.php";
$email_script = "$report_dir/email.php";
$date = date($config['globals']['signups.reports.date_format']);


$dryrun = '';
if($optList['dryrun']) {
    $dryrun = '--dryrun';
}

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

    foreach($instances as $instance) {
        log_("  Creating $instance report.");
        log_(`php $generate_script --site $instance --date $date $dryrun`, true);
    }
}


if($optList['email'] || $optList['all']) {
    log_("Emailing Reports...");
    foreach($instances as $instance) {
        log_("  Emailing report to $instance.");
        log_(`php $email_script --site $instance --date $date $dryrun`, true);
    }
}


function get_options() {
    $prog = basename(__FILE__);
    $short_opts = 'hiugead:n';
    $long_opts = array('help', 'ingest', 'update', 'generate', 'email', 'all', 'date=', 'dryrun');

    if(!($optList = process_cli_args($short_opts, $long_opts)) || $optList['help'] ) {
        die("$prog [--help|-h] [--ingest [--update]] [--generate] [--email ] [--all] [--date FORMATTED_DATE] [--dryrun|-n]");
    }

    return $optList;
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
