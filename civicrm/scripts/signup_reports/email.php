#!/usr/bin/php
<?php

// Bootstrap the script and progress the command line arguments
require_once realpath(dirname(__FILE__).'/../script_utils.php');
add_packages_to_include_path();
$optList = get_options();

// Load the instance configuration
require_once realpath(dirname(__FILE__).'/../bluebird_config.php');
$config = get_bluebird_instance_config($optList['site']);


// Format our inputs
require_once 'utils.php';
$attachment = get_report_path($config, $optList);
$recipients = fix_emails($config['signups.email.to']);


// Create our email
require_once 'Mail/mime.php';
$msg = new Mail_mime();
$msg->setTXTBody("Your weekly signups report.");
$msg->addAttachment($attachment,'application/vnd.ms-excel');


// Create our mailer
require_once 'Mail.php';
$mailer =  Mail::Factory('smtp',array(
    'host' => $config['smtp.host'],
    'port' => $config['smtp.port'],
    'auth' => True,
    'username' => $config['smtp.subuser'],
    'password' => $config['smtp.subpass']
));

// Assemble headers
$headers = $msg->headers(array(
    'Bcc'     => $config['signups.email.bcc'],
    'From'    => $config['signups.email.from'],
    'To'      => $recipients,
    "Subject" =>"[SignupsReport] ".basename($attachment),
));

// Run it!
if(!$optList['dryrun']) {
    // Send the mail
    $result = $mailer->send($recipients, $headers, $msg->get());

    // Verify Success
    if($result !== TRUE ) {
        echo "PEAR_ERROR: $result->message\n";
        foreach($result->backtrace as $frame) {
            echo "{$frame['file']}\t{$frame['class']}::{$frame['function']} line {$frame['line']}\n";
        }
    } else {
        echo "Report sent to $recipients\n";
    }

} else {
    echo "RECIPIENTS:\n";
    foreach(explode(',',$recipients) as $email)
        echo "\t$email\n";
    echo "ATTACHMENT:\n\t$attachment\n";
    echo "HEADERS:\n";
    foreach($headers as $key => $value)
        echo "\t$key: $value\n";
    echo "MESSAGE:\n\t{$msg->_txtbody}\n";
}


function fix_emails($list) {
    $emails = array();
    foreach(explode(',',$list) as $to) {
        if(!strpos($to,'@')) {
            $to .= "@nysenate.gov";
        }
        $emails[] = trim($to);
    }
    return implode(',',$emails);
}


function get_options() {
    $prog = basename(__FILE__);
    $short_opts = 'hS:D:r';
    $long_opts = array('help', 'site=', 'date=', 'dryrun');
    $usage = "[--help|-h] --site|-S SITE --date|-d FORMATTED_DATE [--dryrun]";
    if(! $optList = process_cli_args($short_opts, $long_opts)) {
        die("$prog $usage\n");

    } else if(!$optList['site']) {
        echo "Site name is required.\n";
        die("$prog $usage\n");
    } else if(!$optList['date']) {
        echo "Date is required.\n";
        die("$prog $usage\n");
    }

    return $optList;
}

?>