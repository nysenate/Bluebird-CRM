<?php

// Mailbox settings common to all CRM instances
define('DEFAULT_IMAP_SERVER', 'webmail.senate.state.ny.us');
define('DEFAULT_IMAP_OPTS', '/imap/ssl/notls');
define('DEFAULT_IMAP_MAILBOX', 'Inbox');
define('DEFAULT_IMAP_ARCHIVEBOX', 'Archive');
define('IMAP_PROCESS_UNREAD_ONLY', false);
define('IMAP_MOVE_MAIL_TO_ARCHIVE', true);

define('IMAP_CMD_POLL', 1);
define('IMAP_CMD_LIST', 2);
define('IMAP_CMD_DELETE', 3);

//email address of the contact to file unknown emails against.
define('UNKNOWN_CONTACT_EMAIL', 'unknown.contact@nysenate.gov');

error_reporting(E_ERROR | E_PARSE | E_WARNING);

//no limit
set_time_limit(0);

$prog = basename(__FILE__);

require_once '../../core/bin/script_utils.php';
$stdusage = civicrm_script_usage();
$usage = "[--server|-s imap_server]  [--opts|-o imap_options]  [--cmd|-c <poll|list|delarchive>]  [--mailbox|-m name]  [--archivebox|-a name]";
$shortopts = "s:o:c:m:a:";
$longopts = array("server=", "opts=", "cmd=", "mailbox=", "archivebox=");
$optlist = civicrm_script_init($shortopts, $longopts);
if ($optlist === null) {
  error_log("Usage: $prog  $stdusage  $usage\n");
  exit(1);
}

require_once 'CRM/Core/Config.php';
$config =& CRM_Core_Config::singleton();
$session =& CRM_Core_Session::singleton();

require_once 'CRM/Contact/BAO/Contact.php';
require_once 'CRM/Activity/BAO/Activity.php';
require_once 'CRM/Core/Transaction.php';
require_once 'CRM/Core/BAO/CustomValueTable.php';
require_once 'CRM/Core/PseudoConstant.php';
require_once 'CRM/Core/Error.php';

$imap_accounts = CIVICRM_IMAP_ACCOUNTS;
$site = $optlist['site'];
$cmd = $optlist['cmd'];
$imap_server = DEFAULT_IMAP_SERVER;
$imap_opts = DEFAULT_IMAP_OPTS;
$imap_mailbox = DEFAULT_IMAP_MAILBOX;
$imap_archivebox = DEFAULT_IMAP_ARCHIVEBOX;

if (!empty($optlist['server'])) {
  $imap_server = $optlist['server'];
}
if (!empty($optlist['opts'])) {
  $imap_opts = $optlist['opts'];
}
if (!empty($optlist['mailbox'])) {
  $imap_mailbox = $optlist['mailbox'];
}
if (!empty($optlist['archivebox'])) {
  $imap_archivebox = $optlist['archivebox'];
}
if ($cmd == 'list') {
  $cmd = IMAP_CMD_LIST;
}
else if ($cmd == 'delarchive') {
  $cmd = IMAP_CMD_DELETE;
}
else if ($cmd == 'poll' || !$cmd) {
  $cmd = IMAP_CMD_POLL;
}
else {
  error_log("$prog: $cmd: Invalid script command.");
  exit(1);
}

global $activityPriority, $activityStatus, $activityType;

$aActivityPriority = CRM_Core_PseudoConstant::priority();
$aActivityStatus = CRM_Core_PseudoConstant::activityStatus();
$aActivityType = CRM_Core_PseudoConstant::activityType();

$activityPriority = array_search('Normal', $aActivityPriority);
$activityStatus = array_search('Not Required', $aActivityStatus);
$activityType = array_search('Email (incoming)', $aActivityType);

//set the session ID for who created the activity
$session->set('userID', 1);


// Iterate over all IMAP accounts associated with the current CRM instance.

foreach (explode(",", $imap_accounts) as $imap_account) {
  list($imapUser, $imapPass) = explode("|", $imap_account);
  $imap_params = array(
    'site' => $site,
    'server' => $imap_server,
    'opts' => $imap_opts,
    'user' => $imapUser,
    'pass' => $imapPass,
    'mailbox' => $imap_mailbox,
    'archivebox' => $imap_archivebox
  );

  $rc = processMailboxCommand($cmd, $imap_params);
  if ($rc == false) {
    echo "$prog: Failed to process IMAP account $imapUser@$imap_server\n";
    print_r(imap_errors());
  }
}

echo "Finished processing all mailboxes for CRM instance [$site]\n";
exit(0);



function processMailboxCommand($cmd, $params)
{
  $serverspec = '{'.$params['server'].$params['opts'].'}';
  echo "Opening IMAP connection to {$params['user']}@$serverspec\n";
  $imap_conn = imap_open($serverspec, $params['user'], $params['pass']);

  if ($imap_conn === false) {
    echo "Error: Unable to open IMAP connection to $serverspec\n";
    return false;
  }

  if ($cmd == IMAP_CMD_POLL) {
    $rc = checkImapAccount($imap_conn, $params);
  }
  else if ($cmd == IMAP_CMD_LIST) {
    $rc = listMailboxes($imap_conn, $params);
  }
  else if ($cmd == IMAP_CMD_DELETE) {
    $rc = deleteArchiveBox($imap_conn, $params);
  }
  else {
    echo "Error: Invalid command [$cmd], params=".print_r($params, true)."\n";
    $rc = false;
  }

  //clean up moved/deleted messages
  // Using CL_EXPUNGE is same as calling imap_expunge().
  imap_close($imap_conn, CL_EXPUNGE);
  return $rc;
} // processMailboxCommand()



// Check the given IMAP account for new messages, and process them.

function checkImapAccount($conn, $params)
{
  echo "Polling CRM [".$params['site']."] using IMAP account ".
       $params['user'].'@'.$params['server'].$params['opts']."\n";

  $crm_archivebox = '{'.$params['server'].'}'.$params['archivebox'];

  //create archive box in case it doesn't exist
  //don't report errors since it will almost always fail
  if (IMAP_MOVE_MAIL_TO_ARCHIVE == true) {
    $rc = imap_createmailbox($conn, imap_utf7_encode($crm_archivebox));
    if ($rc) {
      echo "Created new mailbox: $crm_archivebox\n";
    }
    else {
      echo "Archive mailbox $crm_archivebox already exists.\n";
    }
  }

  $msg_count = imap_num_msg($conn);
  echo "Number of messages: $msg_count\n";

  for ($msg_num = 1; $msg_num <= $msg_count; $msg_num++) {
    echo "Retrieving message $msg_num / $msg_count\n";
    $email = retrieveMessage($conn, $msg_num);
    // retrieved msg, now store to Civi and if successful move to archive
    if (civiProcessEmail($email, null) == true) {
      //mark as read
      imap_setflag_full($conn, $email->uid, '\\Seen', ST_UID);
      //move to folder if necessary
      if (IMAP_MOVE_MAIL_TO_ARCHIVE == true) {
        imap_mail_move($conn, $msg_num, $params['archivebox']);
      }
    }
  }

  echo "Finished checking IMAP account ".$params['user'].'@'.$params['server'].$params['opts']."\n";
  return true;
} // checkImapAccount()



function retrieveMessage($conn, $idx)
{
  //get the header
  $header = imap_headerinfo($conn, $idx);

  $email = new stdClass();
  $email->id = $header->message_id;
  $email->subject = $header->subject;
  $reply_to = $header->reply_to[0];
  $email->replyTo = $reply_to->mailbox.'@'.$reply_to->host;
  $email->date = $header->MailDate;
  $email->uid = imap_uid($conn, $idx);

  echo "Message #$idx (uid={$email->uid}): {$email->id} from {$email->replyTo}\n";

  // Skip message if we are processing unread only.
  if ($header->Unseen != "U" && IMAP_PROCESS_UNREAD_ONLY) {
    echo "Skipping (IMAP_PROCESS_UNREAD_ONLY flag set): {$email->id} {$email->replyTo} {$email->subject}\n";
    return null;
  }

  //get the structure
  $msg_struct = imap_fetchstructure($conn, $idx);

  // Extract the body using one of two functions, depending on the message type.
  if ($msg_struct->type == TYPEMULTIPART) {
    $email->body = imap_fetchbody($conn, $idx, "1");
  }
  else {
    $email->body = imap_body($conn, $idx);
  }
 
  //get the attachments
  if (isset($msg_struct->parts) && count($msg_struct->parts > 0)) {
    $email->attachments = getAttachments($conn, $idx, $msg_struct->parts);
  }
  else {
    $email->attachments = null;
  }

  return $email;
} // retrieveMessage()



function getAttachments($conn, $num, $parts)
{
  $attachments = array();

  for ($i = 0; $i < count($parts); $i++) {
    $cur_att = array(
      'is_attachment' => false,
      'filename' => '',
      'name' => '',
      'attachment' => ''
    );
  
    if ($parts[$i]->ifdparameters == true) {
      foreach ($parts[$i]->dparameters as $object) {
        if (strtolower($object->attribute) == 'filename') {
          $cur_att['is_attachment'] = true;
          $cur_att['filename'] = $object->value;
        }
      }
    }
  
    if ($parts[$i]->ifparameters == true) {
      foreach ($parts[$i]->parameters as $object) {
        if (strtolower($object->attribute) == 'name') {
          $cur_att['is_attachment'] = true;
          $cur_att['name'] = $object->value;
        }
      }
    }
  
    if ($cur_att['is_attachment'] == true) {
      $cur_att['attachment'] = imap_fetchbody($conn, $num, $i + 1);
      if ($parts[$i]->encoding == ENCBASE64) {
        $cur_att['attachment'] = base64_decode($cur_att['attachment']);
      }
      elseif ($parts[$i]->encoding == ENCQUOTEDPRINTABLE) {
        $cur_att['attachment'] = quoted_printable_decode($cur_att['attachment']);
      }
      $attachments[] = $cur_att;
    }
  }

  return $attachments;
} // getAttachments()



//attaches email to civiCRM
//default: if no contact found, don't do anything
//returns true/false to move the email to archive or not

function civiProcessEmail($email, $customHandler)
{
  global $activityPriority, $activityType, $activityStatus;
  $session =& CRM_Core_Session::singleton();
  $bSuccess = false;

  //match against allowed email list
  //parse out the first email address which will be the "from" address of the forwarded email
  $matches = array();

  $qtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
  $dtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
  $atom = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c'.
          '\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
  $quoted_pair = '\\x5c[\\x00-\\x7f]';
  $domain_literal = "\\x5b($dtext|$quoted_pair)*\\x5d";
  $quoted_string = "\\x22($qtext|$quoted_pair)*\\x22";
  $domain_ref = $atom;
  $sub_domain = "($domain_ref|$domain_literal)";
  $word = "($atom|$quoted_string)";
  $domain = "$sub_domain(\\x2e$sub_domain)*";
  $local_part = "$word(\\x2e$word)*";
  $addr_spec = "$local_part\\x40$domain";
  $count = preg_match("/$addr_spec/i", $email->body, $matches);

  //use the forward address, otherwise use the direct from address
  if ($count > 0) {
    $email->body = "Forwarded by: " . $email->replyTo."\n\n".$email->body;
    $email->replyTo = $matches[0];
  } 

  // Force e-mail body to display cleanly in UI.
  $email->body = '<pre>'.$email->body.'</pre>';

  // Find a contact that matches by e-mail address.
  echo "Matching CiviCRM contact based on e-mail: {$email->replyTo}\n";
  $c = new CRM_Contact_BAO_Contact();
  $cobj = $c->matchContactOnEmail($email->replyTo);

  $contactID = null;
  if (isset($cobj->contact_id)) {
    $contactID = $cobj->contact_id;
  }

  if ($contactID) {
    $bSuccess = true;
  }
  else { 
    echo "No match on {$email->replyTo}; assigning to anonymous contact.\n";
    $cobj = $c->matchContactOnEmail(UNKNOWN_CONTACT_EMAIL);
    if (isset($cobj->contact_id)) {
      $contactID = $cobj->contact_id;
    }
  }
  
  //process any custom mailbox specific rules
  if ($customHandler != null) {
    include($customHandler);
  }
  //standard handling, add activity if we found a match
  elseif ($bSuccess) {
    echo "Adding standard activity to contact $contactID\n";
    $params = array(
                "source_contact_id" => $session->get("userID"),
                "subject" => $email->subject,
                "details" => $email->body,
                "activity_date_time" => date('YmdHis',strtotime($email->date)),
                "status_id" => $activityStatus,
                "priority_id" => $activityPriority,
                "activity_type_id" => $activityType,
                "target_contact_id" => array($contactID)
    );

    $activity = new CRM_Activity_BAO_Activity();

    $result = $activity->create($params);
    if (is_a($result, 'CRM_Core_Error')) {
      error_log("COULD NOT SAVE ACTIVITY!\n".print_r($params, true));
      return false;
    }
    else {
      echo "Created e-mail activity id=".$result->id." for contact id=".$contactID."\n";
    }
  }

  return $bSuccess;
} // civiProcessEmail()



function listMailboxes($conn, $params)
{
  $inboxes = imap_list($conn, '{'.$params['server'].'}', "*");
  foreach ($inboxes as $inbox) {
    echo "$inbox\n";
  }
  return true;
} // listMailboxes()



function deleteArchiveBox($conn, $params)
{
  $crm_archivebox = '{'.$params['server'].'}'.$params['archivebox'];
  echo "Deleting archive mailbox: $crm_archivebox\n";
  return imap_deletemailbox($conn, $crm_archivebox);
} // deleteArchiveBox()

?>
