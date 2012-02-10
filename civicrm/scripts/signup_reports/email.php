#!/usr/bin/php
<?php

// Bootstrap the script and progress the command line arguments
require_once realpath(dirname(__FILE__).'/../script_utils.php');
add_packages_to_include_path();
$optList = get_options();

// Load the instance configuration
require_once realpath(dirname(__FILE__).'/../bluebird_config.php');
$config = get_bluebird_instance_config($optList['site']);
if ($config == null) {
  die("Unable to continue without a valid configuration.\n");
}

// Format our inputs
require_once 'utils.php';
$attachment = get_report_path($config, $optList['date']);

if (!file_exists($attachment)) {
  die("Report file [$attachment] not found\n");
}

$recipients = fix_emails($config);


// Create our email

// Start with some Sendgrid-specific customization, using the X-SMTPAPI header.
require_once realpath(dirname(__FILE__).'/../../../modules/nyss_mail/SmtpApiHeader.php');

$smtpApiHdr = new SmtpApiHeader();
$smtpApiHdr->setCategory("Web Signups Report");
$smtpApiHdr->setUniqueArgs(array('instance' => $config['shortname'],
                                 'install_class' => $config['install_class'],
                                 'servername' => $config['servername']));
$smtpApiHdr->addFilterSetting('subscriptiontrack', 'enable', 0);
$smtpApiHdr->addFilterSetting('clicktrack', 'enable', 0);
$smtpApiHdr->addFilterSetting('opentrack', 'enable', 0);
$smtpApiHdr->addFilterSetting('bypass_list_management', 'enable', 1);

require_once 'Mail/mime.php';
$msg = new Mail_mime();
$msg->setTXTBody("Attached to this e-mail message, please find your nysenate.gov weekly signups report.");
$msg->addAttachment($attachment, 'application/vnd.ms-excel');


// Create our mailer
require_once 'Mail.php';
$mailer = Mail::Factory('smtp', array(
    'host' => $config['smtp.host'],
    'port' => $config['smtp.port'],
    'auth' => True,
    'username' => $config['smtp.subuser'],
    'password' => $config['smtp.subpass']
));

// Assemble headers
$headers = $msg->headers(array(
    'Bcc' => $config['signups.email.bcc'],
    'From' => $config['signups.email.from'],
    'To' => $recipients,
    "Subject" => '[SignupsReport] '.basename($attachment),
    "X-SMTPAPI" => $smtpApiHdr->asJSON()
));

// Need to combine the to and bcc fields for recipients...
$recipients = "$recipients,{$config['signups.email.bcc']}";

// Run it!
if (!$optList['dryrun']) {
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


function fix_emails($bbcfg)
{
    if (!isset($bbcfg['signups.email.to'])) {
        return null;
    }
    $list = $bbcfg['signups.email.to'];
    $smtp_domain = (isset($bbcfg['smtp.domain'])) ? $bbcfg['smtp.domain'] : 'nysenate.gov';
    $emails = array();
    foreach (explode(',', $list) as $to) {
        if (!strpos($to, '@')) {
            $to .= '@'.$smtp_domain;
        }
        $emails[] = trim($to);
    }
    return implode(',', $emails);
} // fix_emails()


function get_options() {
    $prog = basename(__FILE__);
    $short_opts = 'hS:d:n';
    $long_opts = array('help', 'site=', 'date=', 'dryrun');
    $usage = "[--help|-h] --site|-S SITE --date|-d FORMATTED_DATE [--dryrun|-n]";
    if(! $optList = process_cli_args($short_opts, $long_opts)) {
        die("$prog $usage\n");
    } else if(!$optList['site']) {
        die("Site name is required.\n$prog $usage\n");
    } else if(!$optList['date']) {
        die("Date is required.\n$prog $usage\n");
    }

    return $optList;
}

?>
