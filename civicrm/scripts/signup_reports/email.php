#!/usr/bin/php
<?php

$prog = basename(__FILE__);
$script_dir = dirname(__FILE__);

//Bootstrap the script and progress the command line arguments
require_once 'utils.php';


require_once realpath(dirname(__FILE__).'/../script_utils.php');
add_packages_to_include_path();
$optList = get_options();


require_once realpath(dirname(__FILE__).'/../bluebird_config.php');
$config = get_bluebird_instance_config($optList['site']);


$filename = get_report_name($config['district'], $optList['site'], $config['signups.reports.name_template']);
$attachment = "{$optList['folder']}/$filename";

require_once 'Mail.php';
require_once 'Mail/mime.php';
$params = array(
        'host' => $config['smtp.host'],
        'port' => $config['smtp.port'],
        'auth' => True,
        'username' => $config['smtp.subuser'],
        'password' => $config['smtp.subpass']
    );
var_dump($params);
$mailer = Mail::Factory('smtp',$params);


$emails = array();
foreach(explode(',',$config['signups.email.to']) as $to) {
    if(!strpos($to,'@')) {
        $to .= "@nysenate.gov";
    }
    $emails[] = trim($to);
}
$recipients = implode(',',$emails);


$msg = new Mail_mime();
$msg->setTXTBody("Your weekly signups report.");
$msg->addAttachment($attachment,'application/vnd.ms-excel');

$headers = $msg->headers(array(
        'Bcc'     => $config['signups.email.bcc'],
        'From'    => $config['signups.email.from'],
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


function get_options() {
    $short_opts = 'hS:F:';
    $long_opts = array('help', 'site=', 'folder=');
    $usage = "[--help|-h] --site|-S SITE --folder|-f FOLDER";
    if(! $optList = process_cli_args($short_opts, $long_opts)) {
        die("$prog $usage\n");

    } else if(!$optList['site']) {
        echo "Site name is required.\n";
        die("$prog $usage\n");
    } else if(!$optList['folder']) {
        echo "Folder is required.\n";
        die("$prog $usage\n");
    }

    return $optList;
}

?>