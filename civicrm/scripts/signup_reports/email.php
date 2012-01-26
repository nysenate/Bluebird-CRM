#!/usr/bin/php
<?php

//Bootstrap the script and progress the command line arguments
require_once 'utils.php';
require_once realpath(dirname(__FILE__).'/../script_utils.php');
add_packages_to_include_path();
$prog = basename(__FILE__);
$script_dir = dirname(__FILE__);
$short_opts = 'hS:D:F:';
$long_opts = array('help', 'site=', 'district=', 'folder=');
$usage = "[--help|-h] --site|-s SITE --district|-d DISTRICT --folder|-f FOLDER";

if(! $optList = process_cli_args($short_opts, $long_opts))
    die("$prog $usage\n");

if(! $config = parse_ini_file("$script_dir/reports.cfg", true)) {
    die("$prog: config file reports.cfg not found.");
}

$filename = get_report_name($optList['district'], $optList['site'], $config['reports']);
$attachment = "{$optList['folder']}/$filename";

require_once 'Mail.php';
require_once 'Mail/mime.php';
$mailer = Mail::Factory('mail');

$match = "{$optList['district']}_{$optList['site']}";
$recipients = "";
foreach($config['districts'] as $key => $value) {
    if($key == $match)
        $recipients = $value;
}

if(!$recipients)
    die("$match: No recipients listed in reports.cfg\n");

$msg = new Mail_mime();
$msg->setTXTBody("Your weekly signups report.");
$msg->addAttachment($attachment,'application/vnd.ms-excel');

$headers = $msg->headers(array(
        'Bcc'     => $config['email']['bcc'],
        'From'    => $config['email']['from'],
        'To'      => $recipients,
        "Subject" =>"[SignupsReport] ".basename($optList['folder']),
    ));

$result = $mailer->send($recipients, $headers, $msg->get());
if($result !== TRUE ) {
    echo "PEAR_ERROR: $result->message\n";
    foreach($result->backtrace as $frame) {
        echo "{$frame['file']}\t{$frame['class']}::{$frame['function']} line {$frame['line']}\n";
    }
    die();
} else {
    echo "Report sent to $recipients\n";
}


?>