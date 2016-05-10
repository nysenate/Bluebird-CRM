#!/usr/bin/php
<?php

// Bootstrap the script and process the command line arguments
$prog = basename(__FILE__);
$this_dir = dirname(__FILE__);
require_once realpath("$this_dir/../script_utils.php");

// Process the command line arguments
$shortopts = 'hS:d:e:n';
$longopts = array('help', 'site=', 'date=', 'email=', 'dryrun');
$stdusage = civicrm_script_usage();
$usage = "[--help|-h] --date|-d FORMATTED_DATE [--email RECIPIENTS] [--dryrun|-n]";
$optList = civicrm_script_init($shortopts, $longopts);
if ($optList === null) {
  echo "Usage: $prog  $stdusage  $usage\n";
  exit(1);
}
else if (!$optList['date']) {
  echo "$prog: Must provide a date\n";
  echo "Usage: $prog  $stdusage  $usage\n";
  exit(1);
}

// Load the instance configuration
$bbcfg = get_bluebird_instance_config($optList['site']);
if ($bbcfg == null) {
  die("Unable to continue without a valid configuration.\n");
}

require_once $bbcfg['app.rootdir'].'/modules/nyss_mail/SmtpApiHeader.php';
require_once 'Mail/mime.php';
require_once 'utils.php';

// Format our inputs
$report_date = $optList['date'];
$attachment = get_report_path($bbcfg, $report_date);

if (!file_exists($attachment)) {
  die("Report file [$attachment] not found\n");
}

if ($optList['email']) {
  $recipients = $optList['email'];
  $bcc = '';
}
else {
  $recipients = fix_emails($bbcfg);
  $bcc = $bbcfg['signups.email.bcc'];
}


// Create our email

// Start with some Sendgrid-specific customization, using the X-SMTPAPI header.
$smtpApiHdr = new SmtpApiHeader();
$smtpApiHdr->setCategory("Web Signups Report");
$smtpApiHdr->setUniqueArgs(array('instance' => $bbcfg['shortname'],
                 'install_class' => $bbcfg['install_class'],
                 'servername' => $bbcfg['servername']));
$smtpApiHdr->addFilterSetting('subscriptiontrack', 'enable', 0);
$smtpApiHdr->addFilterSetting('clicktrack', 'enable', 0);
$smtpApiHdr->addFilterSetting('opentrack', 'enable', 0);
$smtpApiHdr->addFilterSetting('bypass_list_management', 'enable', 1);

$msg = new Mail_mime();
$report_type = ($report_date == 'bronto') ? 'Bronto' : 'NYSenate.gov weekly';
$report_filename = basename($attachment);
$msg->setTXTBody(
  "THIS IS AN AUTOMATED MESSAGE. PLEASE DO NOT REPLY.\n\n"
 ."Attached to this e-mail message, please find your $report_type signups report.\n"
 ."The file is in Excel format and the filename is $report_filename.\n\n"
 ."If you have any problems or questions, please contact the STS Help Line at helpline@nysenate.gov or x2011.");
$msg->addAttachment($attachment, 'application/vnd.ms-excel');


// Create our mailer
require_once 'Mail.php';
$mailer = Mail::Factory('smtp', array(
  'host' => $bbcfg['smtp.host'],
  'port' => $bbcfg['smtp.port'],
  'auth' => true,
  'username' => $bbcfg['smtp.username'],
  'password' => $bbcfg['smtp.password']
));

// Assemble headers
$headers = $msg->headers(array(
  'Bcc' => $bcc,
  'From' => $bbcfg['signups.email.from'],
  'To' => $recipients,
  "Subject" => '[SignupsReport] '.basename($attachment),
  "X-SMTPAPI" => $smtpApiHdr->asJSON()
));

// Need to combine the to and bcc fields for recipients...
$recipients = "$recipients,{$bbcfg['signups.email.bcc']}";

// Run it!
if (!$optList['dryrun']) {
  // Send the mail
  $result = $mailer->send($recipients, $headers, $msg->get());

  // Verify Success
  if ($result !== TRUE ) {
    echo "PEAR_ERROR: $result->message\n";
    foreach ($result->backtrace as $frame) {
      echo "{$frame['file']}\t{$frame['class']}::{$frame['function']} line {$frame['line']}\n";
    }
  }
  else {
    echo "Report sent to $recipients\n";
  }
}
else {
  echo "RECIPIENTS:\n";
  foreach (explode(',',$recipients) as $email) {
    echo "\t$email\n";
  }
  echo "ATTACHMENT:\n\t$attachment\n";
  echo "HEADERS:\n";
  foreach ($headers as $key => $value) {
    echo "\t$key: $value\n";
  }
  echo "MESSAGE:\n\t{$msg->_txtbody}\n";
}


function fix_emails($bbcfg)
{
  if (isset($bbcfg['signups.email.to'])) {
    $recip_emails = $bbcfg['signups.email.to'];
  }
  else if (isset($bbcfg['senator.email'])) {
    $recip_emails = $bbcfg['senator.email'];
  }
  else {
    return null;
  }

  $smtp_domain = (isset($bbcfg['smtp.domain'])) ? $bbcfg['smtp.domain'] : 'nysenate.gov';
  $emails = array();
  foreach (explode(',', $recip_emails) as $to) {
    if (!strpos($to, '@')) {
      $to .= '@'.$smtp_domain;
    }
    $emails[] = trim($to);
  }
  return implode(',', $emails);
} // fix_emails()

?>
